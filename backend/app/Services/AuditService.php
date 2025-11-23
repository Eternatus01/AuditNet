<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditService
{
    public function analyzeAndStore(string $url, User $user): array
    {
        $results = $this->runLighthouse($url);

        Audit::create([
            'user_id' => $user->id,
            'url' => $url,
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

        return $results;
    }

    protected function runLighthouse(string $url): array
    {
        $lighthouseUrl = env('LIGHTHOUSE_SERVICE_URL', 'http://lighthouse:3000');

        try {
            $response = Http::timeout(120)->post($lighthouseUrl, ['url' => $url]);
        } catch (Exception $e) {
            Log::error('Ошибка соединения с Lighthouse сервисом', [
                'lighthouse_url' => $lighthouseUrl,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Не удалось подключиться к Lighthouse сервису: ' . $e->getMessage());
        }

        if (!$response->successful()) {
            $error = $response->json();
            $statusCode = $response->status();
            Log::error('Lighthouse сервис вернул ошибку', [
                'status' => $statusCode,
                'error' => $error,
            ]);
            throw new Exception('Lighthouse ошибка (HTTP ' . $statusCode . '): ' . ($error['error'] ?? 'Неизвестная ошибка'));
        }

        $lighthouseData = $response->json();

        if (!$lighthouseData || isset($lighthouseData['error'])) {
            throw new Exception('Lighthouse ошибка: ' . ($lighthouseData['error'] ?? 'Неизвестная ошибка'));
        }

        $categories = $lighthouseData['categories'] ?? [];
        $audits = $lighthouseData['audits'] ?? [];

        return [
            'url' => $url,
            'performance' => isset($categories['performance']['score']) 
                ? (int) ($categories['performance']['score'] * 100) 
                : null,
            'accessibility' => isset($categories['accessibility']['score']) 
                ? (int) ($categories['accessibility']['score'] * 100) 
                : null,
            'bestPractices' => isset($categories['best-practices']['score']) 
                ? (int) ($categories['best-practices']['score'] * 100) 
                : null,
            'seo' => isset($categories['seo']['score']) 
                ? (int) ($categories['seo']['score'] * 100) 
                : null,
            'lcp' => $this->getAuditValue($audits, 'largest-contentful-paint'),
            'fid' => $this->getAuditValue($audits, 'first-input-delay'),
            'cls' => $this->getAuditValue($audits, 'cumulative-layout-shift'),
            'fcp' => $this->getAuditValue($audits, 'first-contentful-paint'),
            'tbt' => $this->getAuditValue($audits, 'total-blocking-time'),
            'speedIndex' => $this->getAuditValue($audits, 'speed-index'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getAuditValue(array $audits, string $auditId): ?float
    {
        if (!isset($audits[$auditId]['numericValue'])) {
            return null;
        }
        $value = $audits[$auditId]['numericValue'];
        if (in_array($auditId, ['first-contentful-paint', 'largest-contentful-paint', 'speed-index'])) {
            return round($value / 1000, 2);
        }
        return $value;
    }
}
