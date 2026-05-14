<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AuditStatus;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\ComparisonRequest;
use App\Http\Resources\AuditComparisonResource;
use App\Repositories\AuditComparisonRepository;
use App\Services\Audit\AuditService;
use App\Services\Audit\RecommendationParser;
use App\Services\Security\SecurityAuditService;
use Illuminate\Http\JsonResponse;

class ComparisonController extends BaseApiController
{
    public function __construct(
        private AuditComparisonRepository $comparisonRepository,
        private AuditService $auditService,
        private SecurityAuditService $securityAuditService,
        private RecommendationParser $recommendationParser
    ) {}

    public function analyze(ComparisonRequest $request): JsonResponse
    {
        $user = $this->requireAuthenticatedUser();
        $urls = array_values($request->input('urls'));
        $comparison = $this->comparisonRepository->createPending($urls, $user);

        $comparison->update(['status' => AuditStatus::PROCESSING]);

        $successfulCount = 0;
        foreach ($urls as $index => $url) {
            $siteData = [
                'url' => $url,
                'sort_order' => $index,
            ];

            try {
                $fullAudit = $this->auditService->performFullAudit($url);
                $result = $fullAudit['result'];
                $rawData = $fullAudit['rawData'];

                $siteData = array_merge($siteData, [
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
                ]);

                try {
                    $siteData['security_audit'] = $this->securityAuditService->auditWebsite($url)->toArray();
                } catch (\Exception) {
                    $siteData['security_audit'] = null;
                }

                try {
                    $siteData['recommendations'] = $this->recommendationParser->parse($rawData);
                } catch (\Exception) {
                    $siteData['recommendations'] = [];
                }

                $successfulCount++;
            } catch (\Exception $e) {
                $siteData['error_message'] = $this->makeAnalysisErrorMessage($e);
            }

            $comparison->sites()->create($siteData);
        }

        $comparison->update([
            'status' => $successfulCount > 0 ? AuditStatus::COMPLETED : AuditStatus::FAILED,
            'error_message' => $successfulCount > 0 ? null : 'Не удалось проанализировать сайты',
            'audited_at' => now(),
        ]);

        return $this->successResponse(
            new AuditComparisonResource($comparison->fresh('sites')),
            'Сравнение выполнено успешно.'
        );
    }

    public function show(int $id): JsonResponse
    {
        try {
            $comparison = $this->comparisonRepository->findByIdForUserOrFail($id, $this->requireAuthenticatedUser());

            return $this->successResponse(new AuditComparisonResource($comparison));
        } catch (\Exception) {
            return $this->errorResponse('Сравнение не найдено.', 404);
        }
    }

    public function share(int $id): JsonResponse
    {
        try {
            $comparison = $this->comparisonRepository->findByIdForUserOrFail($id, $this->requireAuthenticatedUser());
            $token = $this->comparisonRepository->ensureShareToken($comparison);

            return $this->successResponse([
                'token' => $token,
            ], 'Публичная ссылка создана.');
        } catch (\Exception) {
            return $this->errorResponse('Сравнение не найдено.', 404);
        }
    }

    public function publicShow(string $token): JsonResponse
    {
        try {
            $comparison = $this->comparisonRepository->findByShareTokenOrFail($token);

            return $this->successResponse(new AuditComparisonResource($comparison));
        } catch (\Exception) {
            return $this->errorResponse('Публичное сравнение не найдено.', 404);
        }
    }

    private function makeAnalysisErrorMessage(\Exception $e): string
    {
        if (str_contains($e->getMessage(), 'Lighthouse')) {
            return 'Lighthouse сервис недоступен. Попробуйте позже.';
        }

        if (str_contains($e->getMessage(), 'timeout')) {
            return 'Превышено время ожидания.';
        }

        return $e->getMessage() ?: 'Ошибка анализа сайта';
    }
}
