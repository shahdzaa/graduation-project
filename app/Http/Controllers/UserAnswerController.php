<?php

namespace App\Http\Controllers;

use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserAnswerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(UserAnswer::with(['attempt', 'question', 'selectedOption'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attempt_id' => 'required|exists:user_test_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'required|exists:answer_options,id',
        ]);

        $answer = UserAnswer::create($validated);
        return response()->json($answer->load(['attempt', 'question', 'selectedOption']), 201);
    }

    public function show(UserAnswer $userAnswer): JsonResponse
    {
        return response()->json($userAnswer->load(['attempt', 'question', 'selectedOption']));
    }

    public function update(Request $request, UserAnswer $userAnswer): JsonResponse
    {
        $validated = $request->validate([
            'attempt_id' => 'required|exists:user_test_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'required|exists:answer_options,id',
        ]);

        $userAnswer->update($validated);
        return response()->json($userAnswer->load(['attempt', 'question', 'selectedOption']));
    }

    public function destroy(UserAnswer $userAnswer): JsonResponse
    {
        $userAnswer->delete();
        return response()->json(['message' => 'User answer deleted successfully']);
    }
}
