<?php

namespace App\Http\Controllers;

use App\Models\CourseType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseTypeResource;

class CourseTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseTypeResource::collection(CourseType::with('courses')->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $type = CourseType::create($validated);
        return (new CourseTypeResource($type))->response()->setStatusCode(201);
    }

    public function show(CourseType $courseType): JsonResponse
    {
        return (new CourseTypeResource($courseType->load('courses')))->response();
    }

    public function update(Request $request, CourseType $courseType): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $courseType->update($validated);
        return (new CourseTypeResource($courseType))->response();
    }

    public function destroy(CourseType $courseType): JsonResponse
    {
        $courseType->delete();
        return response()->json(['message' => 'Course type deleted successfully']);
    }
}
