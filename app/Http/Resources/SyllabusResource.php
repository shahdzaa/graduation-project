<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyllabusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration_minutes' => $this->duration_minutes,
            'type' => new SyllabusTypeResource($this->whenLoaded('type')),
        ];
    }
}
