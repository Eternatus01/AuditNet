<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\SecurityAuditRequest;
use App\Jobs\SecurityAuditJob;

class SecurityController extends Controller
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
                return response()->json($cachedResult, 500);
            }
            return response()->json($cachedResult, 200);
        }

        SecurityAuditJob::dispatch($url, $cacheKey);

        return response()->json([
            'message' => 'Аудит безопасности начался. Проверьте результаты через несколько секунд.',
            'status' => 'processing',
            'cache_key' => $cacheKey,
        ], 202);
    }
}