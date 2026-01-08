<?php

namespace App\Services\Audit;

use App\Models\User;
use App\DTOs\LighthouseResultDTO;
use App\Repositories\AuditRepository;

class AuditService
{
    public function __construct(
        private LighthouseApiClient $apiClient,
        private LighthouseResultParser $parser,
        private AuditRepository $auditRepository,
    ) {}

    public function performAudit(string $url, User $user): LighthouseResultDTO
    {
        $rawData = $this->apiClient->analyze($url);
        $result = $this->parser->parse($rawData, $url);

        $this->auditRepository->createPendingAudit($url, $user->id);

        return $result;
    }

    public function performAuditForJob(string $url): LighthouseResultDTO
    {
        $rawData = $this->apiClient->analyze($url);
        return $this->parser->parse($rawData, $url);
    }
}

