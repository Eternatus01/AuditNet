<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAudit extends Model
{
    protected $fillable = [
        'audit_id',
        'headers',
        'sensitive_files',
        'directory_listing',
        'scripts_info',
        'https',
        'header_analysis',
        'cookie_flags',
        'mixed_content',
        'script_integrity',
        'server_exposure',
        'recommendations',
        'robots_txt',
        'sitemap_xml',
        'security_txt',
        'error_message',
    ];

    protected $casts = [
        'headers' => 'array',
        'sensitive_files' => 'array',
        'directory_listing' => 'array',
        'scripts_info' => 'array',
        'https' => 'array',
        'header_analysis' => 'array',
        'cookie_flags' => 'array',
        'mixed_content' => 'array',
        'script_integrity' => 'array',
        'server_exposure' => 'array',
        'recommendations' => 'array',
        'robots_txt' => 'boolean',
        'sitemap_xml' => 'boolean',
        'security_txt' => 'boolean',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
