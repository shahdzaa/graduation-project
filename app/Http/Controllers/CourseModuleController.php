<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseModuleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseModule::with(['course', 'module'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'order' => 'required|integer',
        ]);
        $courseModule = CourseModule::create($validated);
        return response()->json($courseModule->load(['course', 'module']), 201);
    }

    public function show(CourseModule $courseModule): JsonResponse
    {
        return response()->json($courseModule->load(['course', 'module']));
    }

    public function update(Request $request, CourseModule $courseModule): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'order' => 'required|integer',
        ]);
        $courseModule->update($validated);
        return response()->json($courseModule->load(['course', 'module']));
    }

    public function destroy(CourseModule $courseModule): JsonResponse
    {
        $courseModule->delete();
        return response()->json(['message' => 'Course module deleted successfully']);
    }
}
