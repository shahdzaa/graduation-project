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
            'order_index' => $this->whenPivotLoaded('course_modules', fn () => $this->pivot->order_index),
            'syllabus' => SyllabusResource::collection($this->whenLoaded('syllabi')),
            'courses_count' => $this->whenCounted('courses'),
            'syllabus_count' => $this->whenCounted('syllabi'),
        ];
    }
}
