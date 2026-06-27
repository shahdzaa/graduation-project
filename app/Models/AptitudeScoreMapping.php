<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AptitudeScoreMapping extends Model
{
    protected $fillable = ['answer_option_id', 'domain_id', 'skill_id', 'weight_score'];

    public function answerOption(): BelongsTo
    {
        return $this->belongsTo(AnswerOption::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
