<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Syllabus extends Model
{
    protected $fillable = ['module_id', 'name', 'type_id', 'duration_minutes'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SyllabusType::class, 'type_id');
    }
}
