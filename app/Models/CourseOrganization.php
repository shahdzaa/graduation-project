<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOrganization extends Model
{
    protected $fillable = ['course_id', 'organization_id'];
    public $timestamps = false;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
