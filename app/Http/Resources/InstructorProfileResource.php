<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorProfileResource extends JsonResource
{
// InstructorProfileResource.php
public function toArray(Request $request): array
{
    return [
        'id'               => $this->id,
        'bio'              => $this->bio,
        'specialization'   => $this->specialization,
        'linkedin_url'     => $this->linkedin_url,
        'website_url'      => $this->website_url,
        'years_experience' => $this->years_experience,
        'average_rating'   => $this->average_rating,
        'courses_count'    => $this->whenCounted('courses'),
        'user'             => new UserResource($this->whenLoaded('user')),
        'courses'          => CourseResource::collection($this->whenLoaded('courses')),
    ];
}
}
