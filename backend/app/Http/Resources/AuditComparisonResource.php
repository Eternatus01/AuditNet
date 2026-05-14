<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditComparisonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'comparison',
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'status' => $this->status,
            'error_message' => $this->error_message,
            'audited_at' => $this->audited_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'sites' => $this->whenLoaded('sites', function () {
                return $this->sites->map(fn ($site) => [
                    'id' => $site->id,
                    'url' => $site->url,
                    'sort_order' => $site->sort_order,
                    'performance' => $site->performance,
                    'accessibility' => $site->accessibility,
                    'best_practices' => $site->best_practices,
                    'seo' => $site->seo,
                    'lcp' => $site->lcp,
                    'fid' => $site->fid,
                    'cls' => $site->cls,
                    'fcp' => $site->fcp,
                    'tbt' => $site->tbt,
                    'speed_index' => $site->speed_index,
                    'security_audit' => $site->security_audit,
                    'recommendations' => $site->recommendations ?? [],
                    'error_message' => $site->error_message,
                    'created_at' => $site->created_at->toIso8601String(),
                    'updated_at' => $site->updated_at->toIso8601String(),
                ]);
            }),
        ];
    }
}
