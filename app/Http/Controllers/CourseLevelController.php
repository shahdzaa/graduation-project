<?php

namespace App\Http\Controllers;

use App\Models\CourseLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseLevelResource;

class CourseLevelController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseLevelResource::collection(CourseLevel::withCount('courses')->orderBy('id')->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:50']);
        $level = CourseLevel::create($validated);
        return (new CourseLevelResource($level))->response()->setStatusCode(201);
    }

    public function show(CourseLevel $courseLevel): JsonResponse
    {
        return (new CourseLevelResource($courseLevel->loadCount('courses')))->response();
    }

    public function update(Request $request, CourseLevel $courseLevel): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:50']);
        $courseLevel->update($validated);
        return (new CourseLevelResource($courseLevel))->response();
    }

    public function destroy(CourseLevel $courseLevel): JsonResponse
    {
        $courseLevel->delete();
        return response()->json(['message' => 'Course level deleted successfully']);
    }
}
