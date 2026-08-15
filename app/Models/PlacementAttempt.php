<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'generation_batch_id',
        'start_time',
        'end_time',
        'total_score',
        'known_syllabi',
        'status',
    ];

    protected $casts = [
        'known_syllabi' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(PlacementUserAnswer::class, 'attempt_id');
    }
}
