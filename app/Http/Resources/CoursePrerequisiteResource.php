<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// $this->resource هون هو Course (الكورس المتطلَّب) جاي من علاقة $course->prerequisites()
// نسخة مختصرة قصداً لتفادي التكرار اللانهائي (كورس بيرجع متطلباته يلي بترجع متطلباتها...)
class CoursePrerequisiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'level' => new CourseLevelResource($this->whenLoaded('level')),
        ];
    }
}
