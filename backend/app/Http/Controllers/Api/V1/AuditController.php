<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\AuditRequest;
use App\Http\Resources\AuditResource;
use App\Jobs\AnalyzeWebsiteJob;
use App\Repositories\AuditRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuditController extends BaseApiController
{
    public function __construct(
        private AuditRepository $auditRepository
    ) {}

    public function analyze(AuditRequest $request): JsonResponse
    {
        try {
            $url = $request->input('url');
            $user = $this->requireAuthenticatedUser();

            $audit = $this->auditRepository->createPendingAudit($url, $user->id);

            AnalyzeWebsiteJob::dispatch($url, $user->id, $audit->id);

            return $this->successResponse(
                [
                    'id' => $audit->id,
                    'status' => $audit->status->value,
                    'url' => $url,
                ],
                'Анализ начался. Результаты будут доступны через 1-2 минуты.',
                202
            );

        } catch (\Exception $e) {
            Log::error('Lighthouse audit dispatch error', ['message' => $e->getMessage()]);
            return $this->errorResponse('Ошибка при запуске анализа. Попробуйте позже.', 500);
        }
    }

    public function history(): JsonResponse
    {
        try {
            $user = $this->requireAuthenticatedUser();

            $audits = $this->auditRepository->getUserAuditsPaginated($user, 20);

            return response()->json([
                'success' => true,
                'data' => AuditResource::collection($audits)->response()->getData(true)['data'],
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'total' => $audits->total(),
            ]);

        } catch (\Exception $e) {
            Log::error('Audit history error', ['message' => $e->getMessage()]);
            return $this->errorResponse('Ошибка при получении истории аудитов.', 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->requireAuthenticatedUser();

            $audit = $this->auditRepository->findByIdForUserOrFail($id, $user);

            return $this->successResponse(new AuditResource($audit));

        } catch (\Exception $e) {
            return $this->errorResponse('Аудит не найден.', 404);
        }
    }

    public function status(int $id): JsonResponse
    {
        return $this->show($id);
    }
}

