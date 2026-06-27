<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $courses = Course::with(['level', 'type', 'modules', 'reviews', 'skills', 'organizations', 'instructors', 'categories', 'certificates'])->get();
        return response()->json($courses);
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
            'description' => 'nullable|string',
        ]);

        $course = Course::create($validated);
        return response()->json($course->load(['level', 'type']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): JsonResponse
    {
        return response()->json($course->load(['level', 'type', 'modules', 'reviews', 'skills', 'organizations', 'instructors', 'categories', 'certificates']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:250',
            'url' => 'required|string|max:1000',
            'duration_minutes' => 'required|integer',
            'level_id' => 'required|exists:course_levels,id',
            'type_id' => 'required|exists:course_types,id',
            'description' => 'nullable|string',
            'average_rating' => 'numeric|min:0|max:5',
        ]);

        $course->update($validated);
        return response()->json($course->load(['level', 'type']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): JsonResponse
    {
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }
}
