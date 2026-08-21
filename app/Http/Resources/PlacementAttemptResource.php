<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlacementAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_score' => $this->total_score,
            'known_syllabi' => $this->known_syllabi,
            'status' => $this->status,
        ];
    }
}
