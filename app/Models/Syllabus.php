<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Syllabus extends Model
{
    protected $table = 'syllabus';

    protected $fillable = [
        'module_id',
        'name',
        'order_index',
        'type_id',
        'category_id',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'order_index' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SyllabusType::class, 'type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
