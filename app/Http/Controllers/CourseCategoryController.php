<?php

namespace App\Http\Controllers;

use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $courseCategories = CourseCategory::with(['course', 'category'])->get();
        return response()->json($courseCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $courseCategory = CourseCategory::create($validated);
        return response()->json($courseCategory->load(['course', 'category']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CourseCategory $courseCategory): JsonResponse
    {
        return response()->json($courseCategory->load(['course', 'category']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseCategory $courseCategory): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $courseCategory->update($validated);
        return response()->json($courseCategory->load(['course', 'category']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseCategory $courseCategory): JsonResponse
    {
        $courseCategory->delete();
        return response()->json(['message' => 'Course category relationship deleted successfully']);
    }
}
