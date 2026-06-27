<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = ['name', 'description', 'associated_domain_id'];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'associated_domain_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(UserTestAttempt::class);
    }
}
