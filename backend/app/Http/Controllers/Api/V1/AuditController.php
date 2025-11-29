<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditRequest;
use App\Http\Resources\AuditResource;
use App\Http\Resources\AuditCollection;
use App\Jobs\AnalyzeWebsiteJob;
use App\Models\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;

class AuditController extends Controller
{
    public function analyze(AuditRequest $request): JsonResponse
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

            $audit = Audit::create([
                'user_id' => $user->id,
                'url' => $url,
                'status' => 'pending',
            ]);

            AnalyzeWebsiteJob::dispatch($url, $user->id, $audit->id);

            return response()->json([
                'success' => true,
                'message' => 'Анализ начался. Результаты будут доступны через 1-2 минуты.',
                'data' => [
                    'audit_id' => $audit->id,
                    'status' => 'pending',
                    'url' => $url,
                ],
            ], 202);

        } catch (\Exception $e) {
            Log::error('Lighthouse audit dispatch error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при запуске анализа. Попробуйте позже.',
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
                'data' => AuditResource::collection($audits)->response()->getData(true)['data'],
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'total' => $audits->total(),
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
                'data' => new AuditResource($audit),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Аудит не найден.',
            ], 404);
        }
    }

    public function status(int $id): JsonResponse
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
                'data' => new AuditResource($audit),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Аудит не найден.',
            ], 404);
        }
    }
}

