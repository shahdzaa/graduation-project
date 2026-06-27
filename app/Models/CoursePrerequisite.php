<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePrerequisite extends Model
{
    protected $fillable = ['course_id', 'prerequisite_course_id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function prerequisiteCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }
}
