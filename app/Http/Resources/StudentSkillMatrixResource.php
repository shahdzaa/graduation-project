<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSkillMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'skill_id' => $this->skill_id,
            'current_score' => $this->current_score,
            'last_updated' => $this->last_updated,
            'user' => new UserResource($this->whenLoaded('user')),
            'skill' => new SkillResource($this->whenLoaded('skill')),
        ];
    }
}
