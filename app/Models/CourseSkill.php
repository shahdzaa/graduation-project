<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSkill extends Model
{
    protected $fillable = ['course_id', 'skill_id'];
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}
