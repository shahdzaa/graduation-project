<?php

namespace App\Http\Controllers;

use App\Models\InstructorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\InstructorProfileResource;

class InstructorProfileController extends Controller
{
    public function index(): JsonResponse
    {
        return InstructorProfileResource::collection(InstructorProfile::with(['user', 'courses'])->get())->response();
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
        return (new InstructorProfileResource($instructor->load(['user', 'courses'])))->response()->setStatusCode(201);
    }

    public function show(InstructorProfile $instructorProfile): JsonResponse
    {
        return (new InstructorProfileResource($instructorProfile->load(['user', 'courses'])))->response();
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
        return (new InstructorProfileResource($instructorProfile->load(['user', 'courses'])))->response();
    }

    public function destroy(InstructorProfile $instructorProfile): JsonResponse
    {
        $instructorProfile->delete();
        return response()->json(['message' => 'Instructor profile deleted successfully']);
    }
}
