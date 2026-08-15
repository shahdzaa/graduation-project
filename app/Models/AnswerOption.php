<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnswerOption extends Model
{
    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
        'option_key',
        'option_text',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function aptitudeMappings(): HasMany
    {
        return $this->hasMany(AptitudeScoreMapping::class);
    }

    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'selected_option_id');
    }
}
