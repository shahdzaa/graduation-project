<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'courses_count' => $this->whenCounted('courses'),
            'categories_count' => $this->whenCounted('categories'),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
