<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'module_id' => $this->module_id,
            'order_index' => $this->order_index,
            'course' => new CourseResource($this->whenLoaded('course')),
            'module' => new ModuleResource($this->whenLoaded('module')),
        ];
    }
}
