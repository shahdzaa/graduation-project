<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\LearningOutcomeResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'thumbnail' => $this->thumbnail,
            'description' => $this->description,
            'price' => $this->price,
            'is_free' => $this->is_free,
            'language' => $this->language,
            'is_published' => $this->is_published,
            'duration_minutes' => $this->duration_minutes,
            'schedule' => $this->schedule,
            'average_rating' => $this->average_rating,
            'domain' => new DomainResource($this->whenLoaded('domain')),
            'level' => new CourseLevelResource($this->whenLoaded('level')),
            'type' => new CourseTypeResource($this->whenLoaded('type')),
            'instructors' => UserResource::collection($this->whenLoaded('instructors')),
            'organizations' => OrganizationResource::collection($this->whenLoaded('organizations')),
            'prerequisites' => CoursePrerequisiteResource::collection($this->whenLoaded('prerequisites')),
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'learning_outcomes' => LearningOutcomeResource::collection($this->whenLoaded('learningOutcomes')),
            'students_count' => $this->whenCounted('studentCourses'),
            'modules_count' => $this->whenCounted('modules'),
            'reviews_count' => $this->whenCounted('reviews'),
            'enrollment' => $this->whenPivotLoaded('student_courses', function () {
                return [
                    'enrolled_at' => $this->pivot->enrolled_at,
                    'status' => $this->pivot->status,
                    'progress_percent' => $this->pivot->progress_percent,
                ];
            }),

            'similarity_score' => $this->when(
                isset($this->similarity_score),
                fn () => $this->similarity_score
            ),
        ];
    }
}
