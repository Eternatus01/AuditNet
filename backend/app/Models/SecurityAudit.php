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
        'robots_txt',
        'sitemap_xml',
        'error_message',
    ];

    protected $casts = [
        'headers' => 'array',
        'sensitive_files' => 'array',
        'directory_listing' => 'array',
        'scripts_info' => 'array',
        'robots_txt' => 'boolean',
        'sitemap_xml' => 'boolean',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
