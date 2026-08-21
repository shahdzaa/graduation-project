<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseInstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'user_id' => $this->user_id,
            'course' => new CourseResource($this->whenLoaded('course')),
            'instructor' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
