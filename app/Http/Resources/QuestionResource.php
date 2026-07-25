<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'syllabus' => new SyllabusResource($this->whenLoaded('syllabus')),
            // ملاحظة: لو الاختبار عم يتاخد حالياً، خفي is_correct بالـ Resource
            // (فلترة الحقول الحساسة بتصير بالكونترولر عبر Resource تاني أو بشرط هون)
            'answer_options' => AnswerOptionResource::collection($this->whenLoaded('answerOptions')),
        ];
    }
}
