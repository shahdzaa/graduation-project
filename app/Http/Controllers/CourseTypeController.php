<?php

namespace App\Http\Controllers;

use App\Models\CourseType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseType::with('courses')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $type = CourseType::create($validated);
        return response()->json($type, 201);
    }

    public function show(CourseType $courseType): JsonResponse
    {
        return response()->json($courseType->load('courses'));
    }

    public function update(Request $request, CourseType $courseType): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $courseType->update($validated);
        return response()->json($courseType);
    }

    public function destroy(CourseType $courseType): JsonResponse
    {
        $courseType->delete();
        return response()->json(['message' => 'Course type deleted successfully']);
    }
}
