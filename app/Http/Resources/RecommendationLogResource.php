<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recommendation_date' => $this->recommendation_date,
            'user' => new UserResource($this->whenLoaded('user')),
            'recommended_course' => new CourseResource($this->whenLoaded('recommendedCourse')),
        ];
    }
}
