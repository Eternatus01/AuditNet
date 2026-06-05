<?php

namespace App\Console\Commands;

use App\Jobs\RunSiteAuditJob;
use App\Models\MonitoredSite;
use Illuminate\Console\Command;

class RunScheduledSiteAudits extends Command
{
    protected $signature = 'sites:run-scheduled-audits';

    protected $description = 'Запускает еженедельный аудит для отслеживаемых сайтов, у которых сегодня назначенный день недели';

    public function handle(): int
    {
        $today = now()->dayOfWeekIso;

        $sites = MonitoredSite::query()
            ->where('is_active', true)
            ->where('schedule_day', $today)
            ->where(function ($query) {
                $query->whereNull('last_run_at')
                    ->orWhereDate('last_run_at', '<', now()->toDateString());
            })
            ->get();

        if ($sites->isEmpty()) {
            $this->info('Нет сайтов для запуска на сегодня.');

            return self::SUCCESS;
        }

        foreach ($sites as $site) {
            RunSiteAuditJob::dispatch($site);
            $this->info("Запланирован аудит: {$site->url} (site #{$site->id})");
        }

        $this->info("Поставлено в очередь аудитов: {$sites->count()}");

        return self::SUCCESS;
    }
}
