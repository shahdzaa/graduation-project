<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationLog extends Model
{
    protected $fillable = ['user_id', 'attempt_id', 'recommended_course_id', 'confidence_score', 'algorithm_version', 'recommendation_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(UserTestAttempt::class, 'attempt_id');
    }

    public function recommendedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'recommended_course_id');
    }
}
