<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AptitudeScoreMappingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'weight_score' => $this->weight_score,
            'domain' => new DomainResource($this->whenLoaded('domain')),
            'skill' => new SkillResource($this->whenLoaded('skill')),
        ];
    }
}
