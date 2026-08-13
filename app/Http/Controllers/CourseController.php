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
    public function index(Request $request): JsonResponse
{
    $user = auth('sanctum')->user();
    $isStaff = $user && $user->hasAnyRole(['admin', 'instructor']);

    $query = Course::with(['level', 'type', 'domain', 'category']);

    if (!$isStaff) {
        $query->where('is_published', true);
    }

    if ($request->filled('domain_id')) {
        $query->where('domain_id', $request->domain_id);
    }

    $sort = $request->get('sort', 'trending');
    match ($sort) {
        'trending' => $query->orderByDesc('average_rating'),
        'newest'   => $query->orderByDesc('created_at'),
        default    => $query->orderByDesc('average_rating'),
    };

    $perPage = $request->get('per_page', 12);
    $courses = $query->paginate($perPage);

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
        return (new CourseResource($course->load(['level', 'type', 'domain', 'category', 'learningOutcomes'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): JsonResponse
    {
        $course->load([
            'level',
            'type',
            'domain',
            'modules',
            'skills',
            'organizations',
            'instructors',
            'prerequisites',
            'learningOutcomes',
        ]);

        $course->loadCount('studentCourses');  // ✅ لتفعيل students_count

        return (new CourseResource($course))->response();
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
    return (new CourseResource($course->load(['level', 'type', 'domain', 'category', 'learningOutcomes'])))->response();
}

public function destroy(Course $course): JsonResponse
{
    $this->authorize('delete', $course);

    $course->delete();
    return response()->json(['message' => 'Course deleted successfully']);
}
}
