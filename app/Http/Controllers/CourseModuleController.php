<?php

namespace App\Http\Controllers;

use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseModuleResource;

class CourseModuleController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseModuleResource::collection(CourseModule::with(['course', 'module'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'order' => 'required|integer',
        ]);
        $courseModule = CourseModule::create($validated);
        return (new CourseModuleResource($courseModule->load(['course', 'module'])))->response()->setStatusCode(201);
    }

    public function show(CourseModule $courseModule): JsonResponse
    {
        return (new CourseModuleResource($courseModule->load(['course', 'module'])))->response();
    }

    public function update(Request $request, CourseModule $courseModule): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'order' => 'required|integer',
        ]);
        $courseModule->update($validated);
        return (new CourseModuleResource($courseModule->load(['course', 'module'])))->response();
    }

    public function destroy(CourseModule $courseModule): JsonResponse
    {
        $courseModule->delete();
        return response()->json(['message' => 'Course module deleted successfully']);
    }
}
