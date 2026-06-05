<?php

namespace App\Services\Audit;

use App\Enums\AuditStatus;
use App\Models\Audit;
use App\Repositories\AuditRepository;
use App\Services\Security\SecurityAuditService;

class AuditRunnerService
{
    public function __construct(
        private AuditRepository $auditRepository,
        private AuditService $auditService,
        private SecurityAuditService $securityAuditService,
        private RecommendationParser $recommendationParser,
    ) {}

    public function runForUrl(string $url, int $userId, ?int $monitoredSiteId = null, string $source = 'manual'): Audit
    {
        $audit = $this->auditRepository->createPendingAudit($url, $userId, $monitoredSiteId, $source);

        try {
            $this->auditRepository->updateAuditStatus($audit->id, AuditStatus::PROCESSING);

            $fullAudit = $this->auditService->performFullAudit($url);
            $result = $fullAudit['result'];
            $rawData = $fullAudit['rawData'];

            $this->auditRepository->updateAuditWithResults($audit->id, [
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

            try {
                $securityResult = $this->securityAuditService->auditWebsite($url);

                $audit->securityAudit()->create([
                    'headers' => $securityResult->headers,
                    'sensitive_files' => $securityResult->sensitiveFiles,
                    'directory_listing' => $securityResult->directoryListing,
                    'scripts_info' => $securityResult->scriptsInfo,
                    'robots_txt' => $securityResult->robotsTxt,
                    'sitemap_xml' => $securityResult->sitemapXml,
                ]);
            } catch (\Exception $securityError) {
                // не блокируем основной аудит при сбое security-проверки
            }

            try {
                $recommendations = $this->recommendationParser->parse($rawData);

                foreach ($recommendations as $recommendation) {
                    $audit->recommendations()->create($recommendation);
                }
            } catch (\Exception $recommendationError) {
                // не блокируем основной аудит при сбое парсинга рекомендаций
            }

            return $this->auditRepository->findById($audit->id);
        } catch (\Exception $e) {
            $this->auditRepository->markAuditAsFailed($audit->id, $e->getMessage());

            throw $e;
        }
    }
}
