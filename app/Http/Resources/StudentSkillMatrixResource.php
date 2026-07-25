<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSkillMatrixResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'current_score' => $this->current_score,
            'last_updated' => $this->last_updated,
            'skill' => new SkillResource($this->whenLoaded('skill')),
        ];
    }
}
