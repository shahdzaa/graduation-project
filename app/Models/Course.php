<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    protected $fillable = [
        'title',
        'url',
        'duration_minutes',
        'level_id',
        'type_id',
        'domain_id',
        'thumbnail',
        'price',
        'is_free',
        'language',
        'is_published',
        'description',
        'schedule',
        'average_rating',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'is_published' => 'boolean',
            'duration_minutes' => 'integer',
            'average_rating' => 'float',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CourseType::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_prerequisites', 'course_id', 'prerequisite_course_id');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'course_modules')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderByPivot('order_index');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_courses')
            ->withPivot('enrolled_at', 'status', 'progress_percent')
            ->withTimestamps();
    }

    public function studentCourses(): HasMany
    {
        return $this->hasMany(\App\Models\StudentCourse::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_skills');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'course_organizations');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructors', 'course_id', 'user_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function learningOutcomes(): HasMany
    {
        return $this->hasMany(LearningOutcome::class)->orderBy('sort_order');
    }
}
