<?php

namespace App\Jobs;

use App\Services\Security\SecurityAuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SecurityAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 2;

    public function __construct(
        public string $url,
        public string $cacheKey,
    ) {}

    public function handle(SecurityAuditService $securityAuditService): void
    {
        try {
            $result = $securityAuditService->auditWebsite($this->url);

            Cache::put($this->cacheKey, $result->toArray(), now()->addMinutes(10));

            Log::info('Security audit completed', ['url' => $this->url]);

        } catch (\Exception $e) {
            Log::error('Security audit job failed', [
                'url' => $this->url,
                'error' => $e->getMessage()
            ]);

            Cache::put($this->cacheKey, ['error' => $e->getMessage()], now()->addMinutes(2));

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put($this->cacheKey, [
            'error' => 'Не удалось выполнить аудит безопасности: ' . $exception->getMessage()
        ], now()->addMinutes(2));

        Log::error('Security audit job failed permanently', [
            'url' => $this->url,
            'error' => $exception->getMessage()
        ]);
    }
}


