<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// $this->resource هون هو Skill جاي من علاقة $course->skills()
// (pivot بسيط course_id + skill_id بدون أعمدة إضافية وبدون timestamps)
class CourseSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'module' => new ModuleResource($this->whenLoaded('module')),
        ];
    }
}
