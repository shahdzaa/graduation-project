<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacementAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'generation_batch_id',
        'start_time',
        'end_time',
        'total_score',
        'known_syllabi',
        'status',
    ];

    protected $casts = [
        'known_syllabi' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PlacementUserAnswer::class, 'attempt_id');
    }

    public function recommendationLogs(): HasMany
    {
        return $this->hasMany(RecommendationLog::class, 'attempt_id');
    }
}
