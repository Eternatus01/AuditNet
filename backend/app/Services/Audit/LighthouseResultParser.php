<?php

namespace App\Services\Audit;

use App\DTOs\LighthouseResultDTO;

class LighthouseResultParser
{
    public function parse(array $lighthouseData, string $url): LighthouseResultDTO
    {
        $categories = $lighthouseData['categories'] ?? [];
        $audits = $lighthouseData['audits'] ?? [];

        return new LighthouseResultDTO(
            url: $url,
            performance: $this->getCategoryScore($categories, 'performance'),
            accessibility: $this->getCategoryScore($categories, 'accessibility'),
            bestPractices: $this->getCategoryScore($categories, 'best-practices'),
            seo: $this->getCategoryScore($categories, 'seo'),
            lcp: $this->getAuditValueMs($audits, 'largest-contentful-paint', divideBy1000: true),
            fid: $this->getAuditValueMs($audits, 'interaction-to-next-paint') 
                ?? $this->getAuditValueMs($audits, 'total-blocking-time'),
            cls: $this->getAuditValueCLS($audits, 'cumulative-layout-shift'),
            fcp: $this->getAuditValueMs($audits, 'first-contentful-paint', divideBy1000: true),
            tbt: $this->getAuditValueMs($audits, 'total-blocking-time'),
            speedIndex: $this->getAuditValueMs($audits, 'speed-index', divideBy1000: true),
            timestamp: now()->toIso8601String(),
        );
    }

    private function getCategoryScore(array $categories, string $categoryName): ?int
    {
        if (!isset($categories[$categoryName]['score'])) {
            return null;
        }

        return (int) ($categories[$categoryName]['score'] * 100);
    }

    private function getAuditValueMs(array $audits, string $auditId, bool $divideBy1000 = false): ?float
    {
        if (!isset($audits[$auditId]['numericValue'])) {
            return null;
        }

        $value = (float) $audits[$auditId]['numericValue'];

        if ($divideBy1000) {
            return round($value / 1000, 2);
        }

        return round($value, 2);
    }

    private function getAuditValueCLS(array $audits, string $auditId): ?float
    {
        if (!isset($audits[$auditId]['numericValue'])) {
            return null;
        }

        return round((float) $audits[$auditId]['numericValue'], 3);
    }
}

