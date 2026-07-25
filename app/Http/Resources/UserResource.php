<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at,

            // بروفايلات مرتبطة (بتنعرض بس لو معمولها eager load)
            'student_profile' => new StudentProfileResource($this->whenLoaded('studentProfile')),
            'instructor_profile' => new InstructorProfileResource($this->whenLoaded('instructorProfile')),
        ];
    }
}
