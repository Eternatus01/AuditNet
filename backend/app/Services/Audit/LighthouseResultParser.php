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
            lcp: $this->getAuditValue($audits, 'largest-contentful-paint'),
            fid: $this->getAuditValue($audits, 'first-input-delay'),
            cls: $this->getAuditValue($audits, 'cumulative-layout-shift'),
            fcp: $this->getAuditValue($audits, 'first-contentful-paint'),
            tbt: $this->getAuditValue($audits, 'total-blocking-time'),
            speedIndex: $this->getAuditValue($audits, 'speed-index'),
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

    private function getAuditValue(array $audits, string $auditId): ?float
    {
        if (!isset($audits[$auditId]['numericValue'])) {
            return null;
        }

        $value = $audits[$auditId]['numericValue'];

        if (in_array($auditId, ['first-contentful-paint', 'largest-contentful-paint', 'speed-index', 'total-blocking-time'])) {
            return round($value / 1000, 2);
        }

        if ($auditId === 'cumulative-layout-shift') {
            return round($value, 3);
        }

        return $value;
    }
}

