<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'audit',
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
            'security_audit' => $this->whenLoaded('securityAudit', function () {
                return $this->securityAudit ? [
                    'headers' => $this->securityAudit->headers,
                    'sensitive_files' => $this->securityAudit->sensitive_files,
                    'directory_listing' => $this->securityAudit->directory_listing,
                    'scripts_info' => $this->securityAudit->scripts_info,
                    'robots_txt' => $this->securityAudit->robots_txt,
                    'sitemap_xml' => $this->securityAudit->sitemap_xml,
                    'https' => $this->securityAudit->https ?? [],
                    'header_analysis' => $this->securityAudit->header_analysis ?? [],
                    'cookie_flags' => $this->securityAudit->cookie_flags ?? [],
                    'mixed_content' => $this->securityAudit->mixed_content ?? [],
                    'script_integrity' => $this->securityAudit->script_integrity ?? [],
                    'server_exposure' => $this->securityAudit->server_exposure ?? [],
                    'security_txt' => $this->securityAudit->security_txt ?? false,
                    'security_recommendations' => $this->securityAudit->recommendations ?? [],
                ] : null;
            }),
            'recommendations' => $this->recommendations->map(function ($rec) {
                return [
                    'id' => $rec->id,
                    'category' => $rec->category,
                    'audit_id_key' => $rec->audit_id_key,
                    'title' => $rec->title,
                    'description' => $rec->description,
                    'score' => $rec->score,
                    'score_display_mode' => $rec->score_display_mode,
                    'display_value' => $rec->display_value,
                    'details' => $rec->details,
                    'numeric_value' => $rec->numeric_value,
                    'numeric_unit' => $rec->numeric_unit,
                ];
            }),
        ];
    }
}

