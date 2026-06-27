<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $fillable = ['name', 'description'];

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function aptitudeMappings(): HasMany
    {
        return $this->hasMany(AptitudeScoreMapping::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
