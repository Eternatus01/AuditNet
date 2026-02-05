<?php

namespace App\Services\Audit;

class RecommendationParser
{
    private const IMPORTANT_AUDITS = [
        'performance' => [
            'largest-contentful-paint',
            'first-contentful-paint',
            'speed-index',
            'total-blocking-time',
            'cumulative-layout-shift',
            'interactive',
            'bootup-time',
            'mainthread-work-breakdown',
            'dom-size',
            'redirects',
            'uses-text-compression',
            'uses-long-cache-ttl',
            'server-response-time',
            'render-blocking-resources',
            'unused-css-rules',
            'unused-javascript',
            'modern-image-formats',
            'uses-optimized-images',
            'uses-responsive-images',
            'offscreen-images',
            'unminified-css',
            'unminified-javascript',
            'efficient-animated-content',
            'duplicated-javascript',
            'legacy-javascript',
        ],
        'best-practices' => [
            'uses-http2',
            'uses-passive-event-listeners',
            'no-document-write',
            'geolocation-on-start',
            'notification-on-start',
            'deprecations',
            'errors-in-console',
            'image-aspect-ratio',
            'image-size-responsive',
        ],
        'accessibility' => [
            'color-contrast',
            'image-alt',
            'link-name',
            'button-name',
            'document-title',
            'html-has-lang',
            'meta-viewport',
        ],
        'seo' => [
            'meta-description',
            'link-text',
            'crawlable-anchors',
            'is-crawlable',
            'robots-txt',
            'hreflang',
            'canonical',
            'font-size',
            'tap-targets',
        ],
    ];

    public function parse(array $lighthouseData): array
    {
        $audits = $lighthouseData['audits'] ?? [];
        $categories = $lighthouseData['categories'] ?? [];
        
        $recommendations = [];

        foreach (self::IMPORTANT_AUDITS as $category => $auditKeys) {
            foreach ($auditKeys as $auditKey) {
                if (!isset($audits[$auditKey])) {
                    continue;
                }

                $audit = $audits[$auditKey];
                $score = $audit['score'] ?? null;

                if ($score === null || $score >= 0.9) {
                    continue;
                }

                $details = isset($audit['details']) ? $this->extractImportantDetails($audit['details']) : null;
                
                if (!$this->hasUsefulData($audit, $details)) {
                    continue;
                }

                $displayValue = $this->formatDisplayValue($audit, $details);

                $recommendation = [
                    'category' => $category,
                    'audit_id_key' => $auditKey,
                    'title' => $audit['title'] ?? '',
                    'description' => $audit['description'] ?? null,
                    'score' => $score,
                    'score_display_mode' => $audit['scoreDisplayMode'] ?? null,
                    'display_value' => $displayValue,
                    'numeric_value' => $audit['numericValue'] ?? null,
                    'numeric_unit' => $audit['numericUnit'] ?? null,
                    'details' => $details,
                ];

                $recommendations[] = $recommendation;
            }
        }

        usort($recommendations, function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        return $recommendations;
    }

    private function formatDisplayValue(array $audit, ?array $details): ?string
    {
        if ($details && isset($details['overallSavingsBytes']) && $details['overallSavingsBytes'] > 0) {
            $bytes = $details['overallSavingsBytes'];
            $kb = round($bytes / 1024, 1);
            $mb = round($bytes / (1024 * 1024), 1);
            
            $sizeStr = $mb >= 1 ? "{$mb} МБ" : "{$kb} КБ";
            
            if (isset($details['overallSavingsMs']) && $details['overallSavingsMs'] > 0) {
                $ms = round($details['overallSavingsMs']);
                $seconds = round($ms / 1000, 1);
                $timeStr = $seconds >= 1 ? "{$seconds}с" : "{$ms}мс";
                return "Потенциальная экономия: {$sizeStr} / {$timeStr}";
            }
            
            return "Потенциальная экономия: {$sizeStr}";
        }

        if ($details && isset($details['overallSavingsMs']) && $details['overallSavingsMs'] > 0) {
            $ms = round($details['overallSavingsMs']);
            $seconds = round($ms / 1000, 1);
            $timeStr = $seconds >= 1 ? "{$seconds}с" : "{$ms}мс";
            return "Потенциальная экономия: {$timeStr}";
        }

        return $audit['displayValue'] ?? null;
    }

    private function hasUsefulData(array $audit, ?array $details): bool
    {
        if (isset($audit['displayValue']) && $audit['displayValue']) {
            return true;
        }

        if (isset($audit['numericValue']) && $audit['numericValue'] > 0) {
            return true;
        }

        if ($details && isset($details['items']) && !empty($details['items'])) {
            foreach ($details['items'] as $item) {
                if (isset($item['wastedBytes']) || isset($item['wastedMs']) || isset($item['totalBytes'])) {
                    return true;
                }
            }
        }

        if ($details && (isset($details['overallSavingsMs']) || isset($details['overallSavingsBytes']))) {
            return true;
        }

        return false;
    }

    private function extractImportantDetails(array $details): ?array
    {
        if (empty($details)) {
            return null;
        }

        $extracted = [];

        if (isset($details['items']) && is_array($details['items'])) {
            $filteredItems = array_filter($details['items'], function($item) {
                return isset($item['wastedBytes']) || isset($item['wastedMs']) || isset($item['totalBytes']) || isset($item['url']);
            });
            
            if (!empty($filteredItems)) {
                $extracted['items'] = array_slice($filteredItems, 0, 15);
            }
        }

        if (isset($details['overallSavingsMs']) && $details['overallSavingsMs'] > 0) {
            $extracted['overallSavingsMs'] = $details['overallSavingsMs'];
        }

        if (isset($details['overallSavingsBytes']) && $details['overallSavingsBytes'] > 0) {
            $extracted['overallSavingsBytes'] = $details['overallSavingsBytes'];
        }

        return empty($extracted) ? null : $extracted;
    }
}
