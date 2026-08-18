<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'phone'      => $this->phone,
            'github_url' => $this->github_url,
            'country'    => $this->country,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'user'       => new UserResource($this->whenLoaded('user')),
        ];
    }
}
