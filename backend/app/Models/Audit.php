<?php

namespace App\Models;

use App\Enums\AuditStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Audit extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'status',
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
        'audited_at',
        'error_message',
    ];

    protected $casts = [
        'status' => AuditStatus::class,
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
        'audited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function securityAudit(): HasOne
    {
        return $this->hasOne(SecurityAudit::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(AuditRecommendation::class);
    }
}
