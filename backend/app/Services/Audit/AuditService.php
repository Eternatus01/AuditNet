<?php

namespace App\Services\Audit;

use App\DTOs\LighthouseResultDTO;

class AuditService
{
    public function __construct(
        private LighthouseApiClient $apiClient,
        private LighthouseResultParser $parser,
    ) {}

    public function performFullAudit(string $url): array
    {
        $rawData = $this->apiClient->analyze($url);
        $parsedResult = $this->parser->parse($rawData, $url);
        
        return [
            'result' => $parsedResult,
            'rawData' => $rawData,
        ];
    }
}

