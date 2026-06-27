<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnswer extends Model
{
    protected $fillable = ['attempt_id', 'question_id', 'selected_option_id'];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(UserTestAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class, 'selected_option_id');
    }
}
