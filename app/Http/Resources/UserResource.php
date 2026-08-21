<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'is_active' => $this->is_active,
            'role' => $this->when(
                $this->relationLoaded('roles'),
                fn () => $this->roles->first()?->name
            ),
            'student_profile' => new StudentProfileResource($this->whenLoaded('studentProfile')),
            'instructor_profile' => new InstructorProfileResource($this->whenLoaded('instructorProfile')),
            'student_courses_count' => $this->whenCounted('studentCourses'),
            'taught_courses_count' => $this->whenCounted('taughtCourses'),
        ];
    }
}
