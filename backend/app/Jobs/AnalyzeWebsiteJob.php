<?php

namespace App\Jobs;

use App\Enums\AuditStatus;
use App\Repositories\AuditRepository;
use App\Services\Audit\AuditService;
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

    public function handle(AuditService $auditService, AuditRepository $auditRepository): void
    {
        $audit = $auditRepository->findById($this->auditId);

        if (!$audit) {
            Log::error('Audit not found for job', ['auditId' => $this->auditId]);
            return;
        }

        try {
            $auditRepository->updateAuditStatus($this->auditId, AuditStatus::PROCESSING);

            $result = $auditService->performAuditForJob($this->url);

            $auditRepository->updateAuditWithResults($this->auditId, [
                'performance' => $result->performance,
                'accessibility' => $result->accessibility,
                'bestPractices' => $result->bestPractices,
                'seo' => $result->seo,
                'lcp' => $result->lcp,
                'fid' => $result->fid,
                'cls' => $result->cls,
                'fcp' => $result->fcp,
                'tbt' => $result->tbt,
                'speedIndex' => $result->speedIndex,
            ]);

            Log::info('Audit completed successfully', [
                'auditId' => $this->auditId,
                'url' => $this->url
            ]);

        } catch (\Exception $e) {
            $auditRepository->markAuditAsFailed($this->auditId, $e->getMessage());

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
        $auditRepository = app(AuditRepository::class);
        
        $auditRepository->markAuditAsFailed(
            $this->auditId,
            'Не удалось выполнить анализ после нескольких попыток: ' . $exception->getMessage()
        );

        Log::error('Audit job failed permanently', [
            'auditId' => $this->auditId,
            'url' => $this->url,
            'userId' => $this->userId,
            'error' => $exception->getMessage()
        ]);
    }
}


