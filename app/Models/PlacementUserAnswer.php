<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlacementUserAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'placement_question_id',
        'selected_option_id',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(PlacementAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(PlacementAnswerOption::class, 'selected_option_id');
    }
}
