<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoursePrerequisiteLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'prerequisite_course_id' => $this->prerequisite_course_id,
            'course' => new CourseResource($this->whenLoaded('course')),
            'prerequisite' => new CoursePrerequisiteResource($this->whenLoaded('prerequisiteCourse')),
        ];
    }
}
