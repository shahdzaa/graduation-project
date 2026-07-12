<?php

namespace App\Http\Controllers;

use App\Models\CourseInstructor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseInstructorResource;

class CourseInstructorController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseInstructorResource::collection(CourseInstructor::with(['course', 'instructor'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructor_profiles,id',
        ]);
        $courseInstructor = CourseInstructor::create($validated);
        return (new CourseInstructorResource(courseInstructor->load(['course', 'instructor'])))->response()->setStatusCode(201);
    }

    public function show(CourseInstructor $courseInstructor): JsonResponse
    {
        return (new CourseInstructorResource($courseInstructor->load(['course', 'instructor'])))->response();
    }

    public function update(Request $request, CourseInstructor $courseInstructor): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:instructor_profiles,id',
        ]);
        $courseInstructor->update($validated);
        return (new CourseInstructorResource($courseInstructor->load(['course', 'instructor'])))->response();
    }

    public function destroy(CourseInstructor $courseInstructor): JsonResponse
    {
        $courseInstructor->delete();
        return response()->json(['message' => 'Course instructor deleted successfully']);
    }
}
