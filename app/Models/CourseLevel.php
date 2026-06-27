<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLevel extends Model
{
    protected $fillable = ['name'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'level_id');
    }
}
