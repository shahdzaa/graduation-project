<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AssessmentResource;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $assessments = Assessment::with(['domain', 'questions', 'testAttempts'])->get();
        return AssessmentResource::collection($assessments)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'domain_id' => 'required|exists:domains,id',
        ]);

        $assessment = Assessment::create($validated);
        return (new AssessmentResource(assessment->load('domain')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Assessment $assessment): JsonResponse
    {
        return (new AssessmentResource($assessment->load(['domain', 'questions', 'testAttempts'])))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Assessment $assessment): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'domain_id' => 'required|exists:domains,id',
        ]);

        $assessment->update($validated);
        return (new AssessmentResource($assessment->load('domain')))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Assessment $assessment): JsonResponse
    {
        $assessment->delete();
        return response()->json(['message' => 'Assessment deleted successfully']);
    }
}
