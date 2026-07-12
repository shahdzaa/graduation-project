<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\QuestionResource;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $questions = Question::with(['assessment', 'syllabus', 'answerOptions', 'userAnswers'])->get();
        return QuestionResource::collection($questions)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'syllabi_id' => 'required|exists:syllabus,id',
            'text' => 'required|string',
            'question_type' => 'required|string|max:50',
        ]);

        $question = Question::create($validated);
        return (new QuestionResource(question->load(['assessment', 'syllabus'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question): JsonResponse
    {
        return (new QuestionResource($question->load(['assessment', 'syllabus', 'answerOptions', 'userAnswers'])))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'assessment_id' => 'required|exists:assessments,id',
            'syllabi_id' => 'required|exists:syllabus,id',
            'text' => 'required|string',
            'question_type' => 'required|string|max:50',
        ]);

        $question->update($validated);
        return (new QuestionResource($question->load(['assessment', 'syllabus'])))->response();
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
