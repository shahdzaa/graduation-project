<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StudentProfileResource;

class StudentProfileController extends Controller
{
    public function index(): JsonResponse
    {
        return StudentProfileResource::collection(StudentProfile::with(['user', 'courses', 'reviews', 'skillMatrices'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_of_birth' => 'nullable|date',
            'education_level' => 'nullable|string|max:250',
            'current_job' => 'nullable|string|max:250',
            'learning_goals' => 'nullable|string',
        ]);
        $student = StudentProfile::create($validated);
        return (new StudentProfileResource(student->load(['user', 'courses', 'reviews', 'skillMatrices'])))->response()->setStatusCode(201);
    }

    public function show(StudentProfile $studentProfile): JsonResponse
    {
        return (new StudentProfileResource($studentProfile->load(['user', 'courses', 'reviews', 'skillMatrices'])))->response();
    }

    public function update(Request $request, StudentProfile $studentProfile): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_of_birth' => 'nullable|date',
            'education_level' => 'nullable|string|max:250',
            'current_job' => 'nullable|string|max:250',
            'learning_goals' => 'nullable|string',
        ]);
        $studentProfile->update($validated);
        return (new StudentProfileResource($studentProfile->load(['user', 'courses', 'reviews', 'skillMatrices'])))->response();
    }

    public function destroy(StudentProfile $studentProfile): JsonResponse
    {
        $studentProfile->delete();
        return response()->json(['message' => 'Student profile deleted successfully']);
    }
}
