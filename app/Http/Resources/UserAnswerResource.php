<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => new QuestionResource($this->whenLoaded('question')),
            'selected_option' => new AnswerOptionResource($this->whenLoaded('selectedOption')),
        ];
    }
}
