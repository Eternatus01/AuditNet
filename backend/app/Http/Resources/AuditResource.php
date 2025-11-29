<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'url' => $this->url,
            'status' => $this->status,
            'performance' => $this->performance,
            'accessibility' => $this->accessibility,
            'best_practices' => $this->best_practices,
            'seo' => $this->seo,
            'lcp' => $this->lcp,
            'fid' => $this->fid,
            'cls' => $this->cls,
            'fcp' => $this->fcp,
            'tbt' => $this->tbt,
            'speed_index' => $this->speed_index,
            'error_message' => $this->error_message,
            'audited_at' => $this->audited_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}

