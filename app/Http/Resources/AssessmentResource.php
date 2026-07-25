<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'domain' => new DomainResource($this->whenLoaded('domain')),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
