<?php

namespace App\Jobs;

use App\Models\Audit;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;
    public $tries = 2;
    public $backoff = 10;

    public function __construct(
        public string $url,
        public int $userId,
        public int $auditId,
    ) {}

    public function handle(AuditService $auditService): void
    {
        $user = User::find($this->userId);
        $audit = Audit::find($this->auditId);

        if (!$user || !$audit) {
            Log::error('User or Audit not found for job', [
                'userId' => $this->userId,
                'auditId' => $this->auditId
            ]);
            return;
        }

        try {
            $audit->update(['status' => 'processing']);

            $results = $auditService->runLighthouseForJob($this->url);

            $audit->update([
                'status' => 'completed',
                'performance' => $results['performance'],
                'accessibility' => $results['accessibility'],
                'best_practices' => $results['bestPractices'],
                'seo' => $results['seo'],
                'lcp' => $results['lcp'],
                'fid' => $results['fid'],
                'cls' => $results['cls'],
                'fcp' => $results['fcp'],
                'tbt' => $results['tbt'],
                'speed_index' => $results['speedIndex'],
                'audited_at' => now(),
            ]);

            Log::info('Audit completed successfully', [
                'auditId' => $this->auditId,
                'url' => $this->url
            ]);

        } catch (\Exception $e) {
            $audit->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            Log::error('Audit job failed', [
                'auditId' => $this->auditId,
                'url' => $this->url,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $audit = Audit::find($this->auditId);
        
        if ($audit) {
            $audit->update([
                'status' => 'failed',
                'error_message' => 'Не удалось выполнить анализ после нескольких попыток: ' . $exception->getMessage()
            ]);
        }

        Log::error('Audit job failed permanently', [
            'auditId' => $this->auditId,
            'url' => $this->url,
            'userId' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}

