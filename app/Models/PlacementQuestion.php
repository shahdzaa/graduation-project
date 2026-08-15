<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementQuestion extends Model
{
    protected $fillable = [
        'category',
        'question_number',
        'question_text',
        'difficulty_level',
        'syllabus_topic',
        'explanation',
        'generation_batch_id',
    ];

    public function options()
    {
        return $this->hasMany(PlacementAnswerOption::class, 'placement_question_id');
    }
}
