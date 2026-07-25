<?php

namespace App\Http\Controllers;

use App\Models\CoursePrerequisite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CoursePrerequisiteResource;

class CoursePrerequisiteController extends Controller
{
    public function index(): JsonResponse
    {
        return CoursePrerequisiteResource::collection(CoursePrerequisite::with(['course', 'prerequisite'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'prerequisite_id' => 'required|exists:courses,id',
        ]);
        $coursePrerequisite = CoursePrerequisite::create($validated);
        return (new CoursePrerequisiteResource($coursePrerequisite->load(['course', 'prerequisite'])))->response()->setStatusCode(201);
    }

    public function show(CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        return (new CoursePrerequisiteResource($coursePrerequisite->load(['course', 'prerequisite'])))->response();
    }

    public function update(Request $request, CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'prerequisite_id' => 'required|exists:courses,id',
        ]);
        $coursePrerequisite->update($validated);
        return (new CoursePrerequisiteResource($coursePrerequisite->load(['course', 'prerequisite'])))->response();
    }

    public function destroy(CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        $coursePrerequisite->delete();
        return response()->json(['message' => 'Course prerequisite deleted successfully']);
    }
}
