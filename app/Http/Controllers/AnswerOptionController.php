<?php

namespace App\Http\Controllers;

use App\Models\AnswerOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnswerOptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(AnswerOption::with(['question', 'aptitudeMappings', 'userAnswers'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'text' => 'required|string|max:500',
            'is_correct' => 'boolean',
        ]);

        $option = AnswerOption::create($validated);
        return response()->json($option->load('question'), 201);
    }

    public function show(AnswerOption $answerOption): JsonResponse
    {
        return response()->json($answerOption->load(['question', 'aptitudeMappings', 'userAnswers']));
    }

    public function update(Request $request, AnswerOption $answerOption): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'text' => 'required|string|max:500',
            'is_correct' => 'boolean',
        ]);

        $answerOption->update($validated);
        return response()->json($answerOption->load('question'));
    }

    public function destroy(AnswerOption $answerOption): JsonResponse
    {
        $answerOption->delete();
        return response()->json(['message' => 'Answer option deleted successfully']);
    }
}
