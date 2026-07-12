<?php

namespace App\Http\Controllers;

use App\Models\AnswerOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AnswerOptionResource;

class AnswerOptionController extends Controller
{
    public function index(): JsonResponse
    {
        return AnswerOptionResource::collection(AnswerOption::with(['question', 'aptitudeMappings', 'userAnswers'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'text' => 'required|string|max:500',
            'is_correct' => 'boolean',
        ]);

        $option = AnswerOption::create($validated);
        return (new AnswerOptionResource(option->load('question')))->response()->setStatusCode(201);
    }

    public function show(AnswerOption $answerOption): JsonResponse
    {
        return (new AnswerOptionResource($answerOption->load(['question', 'aptitudeMappings', 'userAnswers'])))->response();
    }

    public function update(Request $request, AnswerOption $answerOption): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'text' => 'required|string|max:500',
            'is_correct' => 'boolean',
        ]);

        $answerOption->update($validated);
        return (new AnswerOptionResource($answerOption->load('question')))->response();
    }

    public function destroy(AnswerOption $answerOption): JsonResponse
    {
        $answerOption->delete();
        return response()->json(['message' => 'Answer option deleted successfully']);
    }
}
