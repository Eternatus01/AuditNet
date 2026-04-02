<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\AuditRequest;
use App\Http\Resources\AuditResource;
use App\Repositories\AuditRepository;
use App\Services\Audit\AuditService;
use App\Services\Audit\RecommendationParser;
use App\Services\Security\SecurityAuditService;
use App\Enums\AuditStatus;
use Illuminate\Http\JsonResponse;

class AuditController extends BaseApiController
{
    public function __construct(
        private AuditRepository $auditRepository,
        private AuditService $auditService,
        private SecurityAuditService $securityAuditService,
        private RecommendationParser $recommendationParser
    ) {}

    public function analyze(AuditRequest $request): JsonResponse
    {
        try {
            $url = $request->input('url');
            $user = $this->requireAuthenticatedUser();

            $audit = $this->auditRepository->createPendingAudit($url, $user->id);

            try {
                $this->auditRepository->updateAuditStatus($audit->id, AuditStatus::PROCESSING);

                $fullAudit = $this->auditService->performFullAudit($url);
                $result = $fullAudit['result'];
                $rawData = $fullAudit['rawData'];

                $this->auditRepository->updateAuditWithResults($audit->id, [
                    'performance' => $result->performance,
                    'accessibility' => $result->accessibility,
                    'bestPractices' => $result->bestPractices,
                    'seo' => $result->seo,
                    'lcp' => $result->lcp,
                    'fid' => $result->fid,
                    'cls' => $result->cls,
                    'fcp' => $result->fcp,
                    'tbt' => $result->tbt,
                    'speedIndex' => $result->speedIndex,
                ]);

                try {
                    $securityResult = $this->securityAuditService->auditWebsite($url);
                    
                    $audit->securityAudit()->create([
                        'headers' => $securityResult->headers,
                        'sensitive_files' => $securityResult->sensitiveFiles,
                        'directory_listing' => $securityResult->directoryListing,
                        'scripts_info' => $securityResult->scriptsInfo,
                        'robots_txt' => $securityResult->robotsTxt,
                        'sitemap_xml' => $securityResult->sitemapXml,
                    ]);
                } catch (\Exception $securityError) {
                    // не блокируем основной аудит при сбое security-проверки
                }

                try {
                    $recommendations = $this->recommendationParser->parse($rawData);
                    
                    foreach ($recommendations as $recommendation) {
                        $audit->recommendations()->create($recommendation);
                    }
                } catch (\Exception $recommendationError) {
                    // не блокируем основной аудит при сбое парсинга рекомендаций
                }

                $completedAudit = $this->auditRepository->findById($audit->id);

                return $this->successResponse(
                    new AuditResource($completedAudit),
                    'Анализ выполнен успешно.'
                );

            } catch (\Exception $e) {
                $this->auditRepository->markAuditAsFailed($audit->id, $e->getMessage());

                $errorMessage = 'Ошибка при анализе';
                if (str_contains($e->getMessage(), 'Lighthouse')) {
                    $errorMessage = 'Lighthouse сервис недоступен. Попробуйте через минуту (сервис "просыпается")';
                } elseif (str_contains($e->getMessage(), 'timeout')) {
                    $errorMessage = 'Превышено время ожидания. Попробуйте ещё раз';
                }

                return $this->errorResponse($errorMessage . ': ' . $e->getMessage(), 500);
            }

        } catch (\Exception $e) {
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

