<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            // ترتيب الوحدة جوا كورس معيّن (متوفر بس لما تكوني جايي من $course->modules)
            'order_index' => $this->whenPivotLoaded('course_modules', fn () => $this->pivot->order_index),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'syllabus' => SyllabusResource::collection($this->whenLoaded('syllabus')),
        ];
    }
}
