<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTestAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'assessment_id',
        'start_time',
        'end_time',
        'total_score',
        'category',
        'generation_batch_id',
        'status',
        'known_syllabi',
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

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'attempt_id');
    }

    public function recommendationLogs(): HasMany
    {
        return $this->hasMany(RecommendationLog::class, 'attempt_id');
    }
}
