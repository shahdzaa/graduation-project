<?php

namespace App\Http\Controllers;

use App\Models\CoursePrerequisite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CoursePrerequisiteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CoursePrerequisite::with(['course', 'prerequisite'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'prerequisite_id' => 'required|exists:courses,id',
        ]);
        $coursePrerequisite = CoursePrerequisite::create($validated);
        return response()->json($coursePrerequisite->load(['course', 'prerequisite']), 201);
    }

    public function show(CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        return response()->json($coursePrerequisite->load(['course', 'prerequisite']));
    }

    public function update(Request $request, CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'prerequisite_id' => 'required|exists:courses,id',
        ]);
        $coursePrerequisite->update($validated);
        return response()->json($coursePrerequisite->load(['course', 'prerequisite']));
    }

    public function destroy(CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        $coursePrerequisite->delete();
        return response()->json(['message' => 'Course prerequisite deleted successfully']);
    }
}
