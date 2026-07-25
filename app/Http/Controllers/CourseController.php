<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseResource;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with(['level', 'type', 'domain', 'category', 'modules', 'reviews', 'skills', 'organizations', 'instructors', 'certificates'])->get();
        return CourseResource::collection($courses)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:250',
            'url' => 'required|string|max:1000',
            'duration_minutes' => 'required|integer',
            'level_id' => 'required|exists:course_levels,id',
            'type_id' => 'required|exists:course_types,id',
            'domain_id' => 'required|exists:domains,id',
            'category_id' => 'nullable|exists:categories,id',
            'thumbnail' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'language' => 'nullable|string|max:10',
            'is_published' => 'boolean',
            'description' => 'nullable|string',
            'schedule' => 'nullable|string',
            'average_rating' => 'numeric|min:0|max:5',
        ]);

        $course = Course::create($validated);
        return (new CourseResource($course->load(['level', 'type', 'domain', 'category'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): JsonResponse
    {
        return (new CourseResource($course->load(['level', 'type', 'domain', 'category', 'modules', 'reviews', 'skills', 'organizations', 'instructors', 'certificates'])))->response();
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, Course $course): JsonResponse
{
    $this->authorize('update', $course);

    $validated = $request->validate([
         'title' => 'required|string|max:250',
            'url' => 'required|string|max:1000',
            'duration_minutes' => 'required|integer',
            'level_id' => 'required|exists:course_levels,id',
            'type_id' => 'required|exists:course_types,id',
            'domain_id' => 'required|exists:domains,id',
            'category_id' => 'nullable|exists:categories,id',
            'thumbnail' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'language' => 'nullable|string|max:10',
            'is_published' => 'boolean',
            'description' => 'nullable|string',
            'schedule' => 'nullable|string',
            'average_rating' => 'numeric|min:0|max:5',
    ]);

    $course->update($validated);
    return (new CourseResource($course->load(['level', 'type', 'domain', 'category'])))->response();
}

public function destroy(Course $course): JsonResponse
{
    $this->authorize('delete', $course);

    $course->delete();
    return response()->json(['message' => 'Course deleted successfully']);
}
}
