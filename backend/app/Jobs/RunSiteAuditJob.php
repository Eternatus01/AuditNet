<?php

namespace App\Jobs;

use App\Models\MonitoredSite;
use App\Services\Audit\AuditRunnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunSiteAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        private MonitoredSite $site
    ) {}

    public function handle(AuditRunnerService $runner): void
    {
        try {
            $audit = $runner->runForUrl(
                $this->site->url,
                $this->site->user_id,
                $this->site->id,
                'scheduled'
            );

            $this->site->update([
                'last_run_at' => now(),
                'last_audit_id' => $audit->id,
            ]);
        } catch (\Exception $e) {
            $this->site->update(['last_run_at' => now()]);

            Log::error('Scheduled site audit failed', [
                'site_id' => $this->site->id,
                'url' => $this->site->url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
