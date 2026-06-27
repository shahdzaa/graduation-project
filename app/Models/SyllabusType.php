<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyllabusType extends Model
{
    protected $fillable = ['name'];

    public function syllabi(): HasMany
    {
        return $this->hasMany(Syllabus::class, 'type_id');
    }
}
