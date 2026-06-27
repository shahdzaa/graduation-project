<?php

namespace App\Http\Controllers;

use App\Models\CourseLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseLevelController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseLevel::with('courses')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:50']);
        $level = CourseLevel::create($validated);
        return response()->json($level, 201);
    }

    public function show(CourseLevel $courseLevel): JsonResponse
    {
        return response()->json($courseLevel->load('courses'));
    }

    public function update(Request $request, CourseLevel $courseLevel): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:50']);
        $courseLevel->update($validated);
        return response()->json($courseLevel);
    }

    public function destroy(CourseLevel $courseLevel): JsonResponse
    {
        $courseLevel->delete();
        return response()->json(['message' => 'Course level deleted successfully']);
    }
}
