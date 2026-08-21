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
            'user_id' => $this->user_id,
            'attempt_id' => $this->attempt_id,
            'recommended_course_id' => $this->recommended_course_id,
            'recommendation_date' => $this->recommendation_date,
            'user' => new UserResource($this->whenLoaded('user')),
            'attempt' => new PlacementAttemptResource($this->whenLoaded('attempt')),
            'recommended_course' => new CourseResource($this->whenLoaded('recommendedCourse')),
        ];
    }
}
