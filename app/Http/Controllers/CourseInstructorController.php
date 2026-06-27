<?php

namespace App\Http\Controllers;

use App\Models\CourseInstructor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseInstructorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseInstructor::with(['course', 'instructor'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructor_profiles,id',
        ]);
        $courseInstructor = CourseInstructor::create($validated);
        return response()->json($courseInstructor->load(['course', 'instructor']), 201);
    }

    public function show(CourseInstructor $courseInstructor): JsonResponse
    {
        return response()->json($courseInstructor->load(['course', 'instructor']));
    }

    public function update(Request $request, CourseInstructor $courseInstructor): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructor_profiles,id',
        ]);
        $courseInstructor->update($validated);
        return response()->json($courseInstructor->load(['course', 'instructor']));
    }

    public function destroy(CourseInstructor $courseInstructor): JsonResponse
    {
        $courseInstructor->delete();
        return response()->json(['message' => 'Course instructor deleted successfully']);
    }
}
