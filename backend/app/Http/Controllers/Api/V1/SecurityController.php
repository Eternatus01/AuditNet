<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\SecurityAuditRequest;
use App\Jobs\SecurityAuditJob;

class SecurityController extends BaseApiController
{
    public function analyze(SecurityAuditRequest $request): JsonResponse
    {
        $url = $request->input('url');

        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        $cacheKey = 'security_audit_' . md5($url);

        $cachedResult = Cache::get($cacheKey);
        if ($cachedResult) {
            if (isset($cachedResult['error'])) {
                return $this->errorResponse($cachedResult['error'], 500);
            }
            return $this->successResponse($cachedResult);
        }

        SecurityAuditJob::dispatch($url, $cacheKey);

        return $this->successResponse(
            [
                'status' => 'processing',
                'cache_key' => $cacheKey,
            ],
            'Аудит безопасности начался. Проверьте результаты через несколько секунд.',
            202
        );
    }
}