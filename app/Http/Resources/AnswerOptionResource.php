<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            // ⚠️ خليها when() مو دايماً true — بيّنيها بس لصفحة مراجعة النتيجة
            // مش أثناء الطالب عم ياخد الاختبار (حتى ما يغش)
            'is_correct' => $this->when(
                $request->routeIs('*.review') || $request->boolean('show_answers'),
                $this->is_correct
            ),
        ];
    }
}
