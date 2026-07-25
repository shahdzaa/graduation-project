<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTestAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_score' => $this->total_score,
            'user' => new UserResource($this->whenLoaded('user')),
            'assessment' => new AssessmentResource($this->whenLoaded('assessment')),
            'answers' => UserAnswerResource::collection($this->whenLoaded('userAnswers')),
        ];
    }
}
