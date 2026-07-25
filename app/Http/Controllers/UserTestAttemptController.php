<?php

namespace App\Http\Controllers;

use App\Models\UserTestAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserTestAttemptResource;

class UserTestAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $attempts = UserTestAttempt::with(['user', 'assessment', 'userAnswers', 'recommendationLogs'])->get();
        return UserTestAttemptResource::collection($attempts)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assessment_id' => 'required|exists:assessments,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'total_score' => 'numeric|default:0',
        ]);

        $attempt = UserTestAttempt::create($validated);
        return (new UserTestAttemptResource($attempt->load(['user', 'assessment'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UserTestAttempt $userTestAttempt): JsonResponse
    {
        return (new UserTestAttemptResource($userTestAttempt->load(['user', 'assessment', 'userAnswers', 'recommendationLogs'])))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserTestAttempt $userTestAttempt): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'assessment_id' => 'required|exists:assessments,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'total_score' => 'numeric',
        ]);

        $userTestAttempt->update($validated);
        return (new UserTestAttemptResource($userTestAttempt->load(['user', 'assessment'])))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserTestAttempt $userTestAttempt): JsonResponse
    {
        $userTestAttempt->delete();
        return response()->json(['message' => 'Test attempt deleted successfully']);
    }
}
