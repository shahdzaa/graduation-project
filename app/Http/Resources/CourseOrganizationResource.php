<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseOrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'course_id' => $this->course_id,
            'organization_id' => $this->organization_id,
            'course' => new CourseResource($this->whenLoaded('course')),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
        ];
    }
}
