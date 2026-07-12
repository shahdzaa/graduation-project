<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'specialization',
        'linkedin_url',
        'years_experience',
        'website_url',
        'average_rating',
    ];

    protected $casts = [
        'average_rating' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
