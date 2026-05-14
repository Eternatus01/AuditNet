<?php

namespace App\Models;

use App\Enums\AuditStatus;
use App\Models\AuditComparisonSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditComparison extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'status',
        'error_message',
        'share_token',
        'audited_at',
    ];

    protected $casts = [
        'status' => AuditStatus::class,
        'audited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(AuditComparisonSite::class)->orderBy('sort_order');
    }
}
