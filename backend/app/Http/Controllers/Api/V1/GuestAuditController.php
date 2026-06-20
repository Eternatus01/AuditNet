<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\AuditRequest;
use App\Http\Requests\SecurityAuditRequest;
use App\Services\Audit\AuditService;
use App\Services\Audit\RecommendationParser;
use App\Services\Security\SecurityAuditService;
use Illuminate\Http\JsonResponse;

class GuestAuditController extends BaseApiController
{
    public function __construct(
        private AuditService $auditService,
        private SecurityAuditService $securityAuditService,
        private RecommendationParser $recommendationParser
    ) {}

    public function analyze(AuditRequest $request): JsonResponse
    {
        try {
            $url = $request->input('url');

            $fullAudit = $this->auditService->performFullAudit($url);
            $result = $fullAudit['result'];
            $rawData = $fullAudit['rawData'];

            $securityData = null;
            try {
                $securityResult = $this->securityAuditService->auditWebsite($url);
                $securityData = [
                    'headers' => $securityResult->headers,
                    'sensitive_files' => $securityResult->sensitiveFiles,
                    'directory_listing' => $securityResult->directoryListing,
                    'scripts_info' => $securityResult->scriptsInfo,
                    'robots_txt' => $securityResult->robotsTxt,
                    'sitemap_xml' => $securityResult->sitemapXml,
                        'https' => $securityResult->https,
                        'header_analysis' => $securityResult->headerAnalysis,
                        'cookie_flags' => $securityResult->cookieFlags,
                        'mixed_content' => $securityResult->mixedContent,
                        'script_integrity' => $securityResult->scriptIntegrity,
                        'server_exposure' => $securityResult->serverExposure,
                        'security_txt' => $securityResult->securityTxt,
                        'security_recommendations' => $securityResult->recommendations,
                ];
            } catch (\Exception) {
                // не блокируем основной аудит при сбое security-проверки
            }

            $recommendations = [];
            try {
                $recommendations = $this->recommendationParser->parse($rawData);
            } catch (\Exception) {
                // не блокируем основной аудит при сбое парсинга рекомендаций
            }

            return $this->successResponse([
                'url' => $url,
                'performance' => $result->performance,
                'accessibility' => $result->accessibility,
                'best_practices' => $result->bestPractices,
                'seo' => $result->seo,
                'lcp' => $result->lcp,
                'fid' => $result->fid,
                'cls' => $result->cls,
                'fcp' => $result->fcp,
                'tbt' => $result->tbt,
                'speed_index' => $result->speedIndex,
                'security_audit' => $securityData,
                'recommendations' => $recommendations,
            ], 'Анализ выполнен успешно.');

        } catch (\Exception $e) {
            $errorMessage = 'Ошибка при анализе';
            if (str_contains($e->getMessage(), 'Lighthouse')) {
                $errorMessage = 'Lighthouse сервис недоступен. Попробуйте через минуту (сервис "просыпается")';
            } elseif (str_contains($e->getMessage(), 'timeout')) {
                $errorMessage = 'Превышено время ожидания. Попробуйте ещё раз';
            }

            return $this->errorResponse($errorMessage . ': ' . $e->getMessage(), 500);
        }
    }

    public function securityAnalyze(SecurityAuditRequest $request): JsonResponse
    {
        try {
            $url = $request->input('url');

            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }

            $result = $this->securityAuditService->auditWebsite($url);

            return $this->successResponse([
                'checked_url' => $result->checkedUrl,
                'host' => $result->host,
                'headers' => $result->headers,
                'sensitive_files' => $result->sensitiveFiles,
                'directory_listing' => $result->directoryListing,
                'robots_txt' => (bool) $result->robotsTxt,
                'sitemap_xml' => (bool) $result->sitemapXml,
                'scripts_info' => $result->scriptsInfo,
                'https' => $result->https,
                'header_analysis' => $result->headerAnalysis,
                'cookie_flags' => $result->cookieFlags,
                'mixed_content' => $result->mixedContent,
                'script_integrity' => $result->scriptIntegrity,
                'server_exposure' => $result->serverExposure,
                'security_txt' => $result->securityTxt,
                'security_recommendations' => $result->recommendations,
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка при проверке безопасности: ' . $e->getMessage(), 500);
        }
    }
}
