<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\SecurityAuditRequest;
use App\Services\Security\SecurityAuditService;

class SecurityController extends BaseApiController
{
    public function __construct(
        private SecurityAuditService $securityAuditService
    ) {}

    public function analyze(SecurityAuditRequest $request): JsonResponse
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
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка при проверке безопасности: ' . $e->getMessage(), 500);
        }
    }
}