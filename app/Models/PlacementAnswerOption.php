<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAnswerOption extends Model
{
    protected $fillable = [
        'placement_question_id',
        'option_key',
        'option_text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }
}
