<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'skill_id' => $this->skill_id,
            'course' => new CourseResource($this->whenLoaded('course')),
            'skill' => new SkillResource($this->whenLoaded('skill')),
        ];
    }
}
