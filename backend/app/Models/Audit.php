<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

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
}
