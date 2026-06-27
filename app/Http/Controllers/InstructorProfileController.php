<?php

namespace App\Http\Controllers;

use App\Models\InstructorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InstructorProfileController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(InstructorProfile::with(['user', 'courses'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bio' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialization' => 'nullable|string|max:250',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);
        $instructor = InstructorProfile::create($validated);
        return response()->json($instructor->load(['user', 'courses']), 201);
    }

    public function show(InstructorProfile $instructorProfile): JsonResponse
    {
        return response()->json($instructorProfile->load(['user', 'courses']));
    }

    public function update(Request $request, InstructorProfile $instructorProfile): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bio' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialization' => 'nullable|string|max:250',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);
        $instructorProfile->update($validated);
        return response()->json($instructorProfile->load(['user', 'courses']));
    }

    public function destroy(InstructorProfile $instructorProfile): JsonResponse
    {
        $instructorProfile->delete();
        return response()->json(['message' => 'Instructor profile deleted successfully']);
    }
}
