<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// $this->resource هون هو Module جاي من علاقة $course->modules()
// بيتضمن order_index من جدول الـ pivot (course_modules)
class CourseModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'order_index' => $this->whenPivotLoaded('course_modules', fn () => $this->pivot->order_index),
        ];
    }
}
