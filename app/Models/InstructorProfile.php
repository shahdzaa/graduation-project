<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorProfile extends Model
{
    protected $fillable = ['user_id', 'bio', 'specialization', 'linkedin_url', 'average_rating'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
