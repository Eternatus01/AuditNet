<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditComparisonSite extends Model
{
    protected $fillable = [
        'audit_comparison_id',
        'url',
        'sort_order',
        'performance',
        'accessibility',
        'best_practices',
        'seo',
        'lcp',
        'fid',
        'cls',
        'fcp',
        'tbt',
        'speed_index',
        'security_audit',
        'recommendations',
        'error_message',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'performance' => 'integer',
        'accessibility' => 'integer',
        'best_practices' => 'integer',
        'seo' => 'integer',
        'lcp' => 'float',
        'fid' => 'float',
        'cls' => 'float',
        'fcp' => 'float',
        'tbt' => 'float',
        'speed_index' => 'float',
        'security_audit' => 'array',
        'recommendations' => 'array',
    ];

    public function comparison(): BelongsTo
    {
        return $this->belongsTo(AuditComparison::class, 'audit_comparison_id');
    }
}
