<?php

// =====================================================
// app/Models/PlacementQuestion.php
// =====================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementQuestion extends Model
{
    protected $fillable = [
        'category', 'question_number', 'question_text',
        'difficulty_level', 'syllabus_topic', 'explanation', 'generation_batch_id',
    ];

    public function options()
    {
        return $this->hasMany(PlacementAnswerOption::class, 'placement_question_id');
    }
}


// =====================================================
// app/Models/PlacementAnswerOption.php
// =====================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAnswerOption extends Model
{
    protected $fillable = [
        'placement_question_id', 'option_key', 'option_text', 'is_correct',
    ];

    protected $casts = ['is_correct' => 'boolean'];

    public function question()
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }
}


// =====================================================
// app/Models/PlacementAttempt.php
// =====================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAttempt extends Model
{
    protected $fillable = [
        'user_id', 'category', 'generation_batch_id',
        'start_time', 'end_time', 'total_score', 'known_syllabi', 'status',
    ];

    protected $casts = [
        'known_syllabi' => 'array',
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(PlacementUserAnswer::class, 'attempt_id');
    }
}


// =====================================================
// app/Models/PlacementUserAnswer.php
// =====================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementUserAnswer extends Model
{
    protected $fillable = [
        'attempt_id', 'placement_question_id', 'selected_option_id',
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
