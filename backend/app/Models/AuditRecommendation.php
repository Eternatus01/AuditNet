<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditRecommendation extends Model
{
    protected $fillable = [
        'audit_id',
        'category',
        'audit_id_key',
        'title',
        'description',
        'score',
        'score_display_mode',
        'display_value',
        'details',
        'numeric_value',
        'numeric_unit',
    ];

    protected $casts = [
        'score' => 'float',
        'details' => 'array',
        'numeric_value' => 'integer',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
