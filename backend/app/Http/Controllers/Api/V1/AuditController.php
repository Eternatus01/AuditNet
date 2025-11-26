<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class AuditController extends Controller
{
    public function analyze(AuditRequest $request, AuditService $auditService): JsonResponse
    {
        try {
            $url = $request->input('url');
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован!',
                ], 401);
            }

            $results = $auditService->analyzeAndStore($url, $user);

            return response()->json([
                'success' => true,
                'data' => $results,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Lighthouse audit error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при анализе сайта. Попробуйте позже.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function history(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован!',
                ], 401);
            }

            $audits = $user->audits()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $audits,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Audit history error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении истории аудитов.',
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не авторизован!',
                ], 401);
            }

            $audit = $user->audits()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $audit,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Аудит не найден.',
            ], 404);
        }
    }
}

