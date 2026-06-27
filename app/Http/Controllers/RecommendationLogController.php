<?php

namespace App\Http\Controllers;

use App\Models\RecommendationLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecommendationLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $logs = RecommendationLog::with(['user', 'attempt', 'recommendedCourse'])->get();
        return response()->json($logs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'attempt_id' => 'required|exists:user_test_attempts,id',
            'recommended_course_id' => 'required|exists:courses,id',
            'confidence_score' => 'required|numeric',
            'algorithm_version' => 'required|string|max:50',
            'recommendation_date' => 'required|date',
        ]);

        $log = RecommendationLog::create($validated);
        return response()->json($log->load(['user', 'attempt', 'recommendedCourse']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RecommendationLog $recommendationLog): JsonResponse
    {
        return response()->json($recommendationLog->load(['user', 'attempt', 'recommendedCourse']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecommendationLog $recommendationLog): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'attempt_id' => 'required|exists:user_test_attempts,id',
            'recommended_course_id' => 'required|exists:courses,id',
            'confidence_score' => 'required|numeric',
            'algorithm_version' => 'required|string|max:50',
            'recommendation_date' => 'required|date',
        ]);

        $recommendationLog->update($validated);
        return response()->json($recommendationLog->load(['user', 'attempt', 'recommendedCourse']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecommendationLog $recommendationLog): JsonResponse
    {
        $recommendationLog->delete();
        return response()->json(['message' => 'Recommendation log deleted successfully']);
    }
}
