<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentProfileController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StudentProfile::with(['user', 'courses', 'reviews', 'skillMatrices'])->get());
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
        return response()->json($student->load(['user', 'courses', 'reviews', 'skillMatrices']), 201);
    }

    public function show(StudentProfile $studentProfile): JsonResponse
    {
        return response()->json($studentProfile->load(['user', 'courses', 'reviews', 'skillMatrices']));
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
        return response()->json($studentProfile->load(['user', 'courses', 'reviews', 'skillMatrices']));
    }

    public function destroy(StudentProfile $studentProfile): JsonResponse
    {
        $studentProfile->delete();
        return response()->json(['message' => 'Student profile deleted successfully']);
    }
}
