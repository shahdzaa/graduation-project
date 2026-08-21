<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detailsLoaded = $this->relationLoaded('user')
            && $this->user
            && $this->user->relationLoaded('studentCourses');

        return [
            'id'         => $this->id,
            'phone'      => $this->phone,
            'github_url' => $this->github_url,
            'country'    => $this->country,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'user'       => new UserResource($this->whenLoaded('user')),
            'summary'    => $this->when($detailsLoaded, function () {
                $enrollments = $this->user->studentCourses;

                return [
                    'enrolled_courses' => $enrollments->count(),
                    'active_courses' => $enrollments->where('status', 'active')->count(),
                    'completed_courses' => $enrollments->where('status', 'completed')->count(),
                    'dropped_courses' => $enrollments->where('status', 'dropped')->count(),
                    'average_progress' => round((float) ($enrollments->avg('progress_percent') ?? 0), 1),
                    'skills' => $this->user->relationLoaded('skillMatrix')
                        ? $this->user->skillMatrix->count()
                        : 0,
                    'placement_attempts' => $this->user->relationLoaded('placementAttempts')
                        ? $this->user->placementAttempts->count()
                        : 0,
                ];
            }),
            'enrollments' => $this->when($detailsLoaded, function () {
                return $this->user->studentCourses->map(function ($enrollment) {
                    $course = $enrollment->course;

                    return [
                        'id' => $enrollment->id,
                        'enrolled_at' => $enrollment->enrolled_at?->toISOString(),
                        'status' => $enrollment->status,
                        'progress_percent' => (float) $enrollment->progress_percent,
                        'course' => [
                            'id' => $course?->id,
                            'title' => $course?->title,
                            'url' => $course?->url,
                            'thumbnail' => $course?->thumbnail,
                            'language' => $course?->language,
                            'duration_minutes' => $course?->duration_minutes,
                            'average_rating' => $course?->average_rating,
                            'level' => $course?->level?->name,
                            'domain' => $course?->domain?->name,
                        ],
                    ];
                })->values();
            }),
            'skills' => $this->when(
                $this->relationLoaded('user')
                    && $this->user
                    && $this->user->relationLoaded('skillMatrix'),
                fn () => $this->user->skillMatrix->map(fn ($matrix) => [
                    'id' => $matrix->skill_id,
                    'name' => $matrix->skill?->name,
                    'current_score' => (float) $matrix->current_score,
                    'last_updated' => $matrix->last_updated?->toISOString(),
                ])->values()
            ),
            'placement_attempts' => $this->when(
                $this->relationLoaded('user')
                    && $this->user
                    && $this->user->relationLoaded('placementAttempts'),
                fn () => $this->user->placementAttempts->map(fn ($attempt) => [
                    'id' => $attempt->id,
                    'category' => $attempt->category,
                    'start_time' => $attempt->start_time?->toISOString(),
                    'end_time' => $attempt->end_time?->toISOString(),
                    'total_score' => (int) $attempt->total_score,
                    'known_syllabi_count' => count($attempt->known_syllabi ?? []),
                    'status' => $attempt->status,
                ])->values()
            ),
        ];
    }
}
