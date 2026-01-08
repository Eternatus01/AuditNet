<?php

namespace App\DTOs;

class LighthouseResultDTO
{
    public function __construct(
        public readonly string $url,
        public readonly ?int $performance,
        public readonly ?int $accessibility,
        public readonly ?int $bestPractices,
        public readonly ?int $seo,
        public readonly ?float $lcp,
        public readonly ?float $fid,
        public readonly ?float $cls,
        public readonly ?float $fcp,
        public readonly ?float $tbt,
        public readonly ?float $speedIndex,
        public readonly string $timestamp,
    ) {}

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'performance' => $this->performance,
            'accessibility' => $this->accessibility,
            'best_practices' => $this->bestPractices,
            'seo' => $this->seo,
            'lcp' => $this->lcp,
            'fid' => $this->fid,
            'cls' => $this->cls,
            'fcp' => $this->fcp,
            'tbt' => $this->tbt,
            'speed_index' => $this->speedIndex,
            'timestamp' => $this->timestamp,
        ];
    }
}

