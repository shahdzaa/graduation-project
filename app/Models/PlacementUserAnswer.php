<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementUserAnswer extends Model
{
    protected $fillable = [
        'attempt_id',
        'placement_question_id',
        'selected_option_id',
    ];

    public function question()
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }

    public function selectedOption()
    {
        return $this->belongsTo(PlacementAnswerOption::class, 'selected_option_id');
    }
}
