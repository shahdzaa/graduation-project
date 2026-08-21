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
            'module_id' => $this->module_id,
            'type_id' => $this->type_id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'order_index' => $this->order_index,
            'duration_minutes' => $this->duration_minutes,
            'type' => new SyllabusTypeResource($this->whenLoaded('type')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'module' => new ModuleResource($this->whenLoaded('module')),
        ];
    }
}
