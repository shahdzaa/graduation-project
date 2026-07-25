<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bio' => $this->bio,
            'specialization' => $this->specialization,
            'linkedin_url' => $this->linkedin_url,
            'years_experience' => $this->years_experience,
            'website_url' => $this->website_url,
            'average_rating' => $this->average_rating,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
