<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredSite extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'schedule_day',
        'is_active',
        'last_run_at',
        'last_audit_id',
    ];

    protected $casts = [
        'schedule_day' => 'integer',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class)->orderByDesc('created_at');
    }

    public function lastAudit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'last_audit_id');
    }
}
