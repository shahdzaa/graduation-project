<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['assessment_id', 'syllabi_id', 'text', 'question_type'];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class, 'syllabi_id');
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }
}
