<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoredSiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
            'schedule_day' => $this->schedule_day,
            'is_active' => $this->is_active,
            'last_run_at' => $this->last_run_at?->toIso8601String(),
            'last_audit_id' => $this->last_audit_id,
            'created_at' => $this->created_at->toIso8601String(),
            'last_audit' => $this->whenLoaded('lastAudit', function () {
                return $this->lastAudit ? [
                    'id' => $this->lastAudit->id,
                    'status' => $this->lastAudit->status,
                    'performance' => $this->lastAudit->performance,
                    'accessibility' => $this->lastAudit->accessibility,
                    'best_practices' => $this->lastAudit->best_practices,
                    'seo' => $this->lastAudit->seo,
                    'audited_at' => $this->lastAudit->audited_at?->toIso8601String(),
                ] : null;
            }),
        ];
    }
}
