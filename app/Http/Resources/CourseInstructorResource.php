<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// بتغلّف مستخدم (مدرّس) جوا سياق كورس معيّن — $this->resource هون هو User
// جاي من علاقة $course->instructors()
class CourseInstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'instructor_profile' => new InstructorProfileResource($this->whenLoaded('instructorProfile')),
        ];
    }
}
