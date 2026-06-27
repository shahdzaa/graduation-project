<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = ['name', 'description', 'duration_minutes'];

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_modules')->withPivot('order_index');
    }

    public function syllabi(): HasMany
    {
        return $this->hasMany(Syllabus::class);
    }
}
