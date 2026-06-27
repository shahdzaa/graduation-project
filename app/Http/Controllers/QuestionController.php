<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $questions = Question::with(['assessment', 'answerOptions', 'userAnswers'])->get();
        return response()->json($questions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'text' => 'required|string',
            'question_type' => 'required|string|max:50',
        ]);

        $question = Question::create($validated);
        return response()->json($question->load('assessment'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question): JsonResponse
    {
        return response()->json($question->load(['assessment', 'answerOptions', 'userAnswers']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'text' => 'required|string',
            'question_type' => 'required|string|max:50',
        ]);

        $question->update($validated);
        return response()->json($question->load('assessment'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question): JsonResponse
    {
        $question->delete();
        return response()->json(['message' => 'Question deleted successfully']);
    }
}
