<?php

namespace App\Http\Controllers;

use App\Models\CourseReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseReviewController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseReview::with(['course', 'student'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'student_id' => 'required|exists:student_profiles,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string',
        ]);
        $review = CourseReview::create($validated);
        return response()->json($review->load(['course', 'student']), 201);
    }

    public function show(CourseReview $courseReview): JsonResponse
    {
        return response()->json($courseReview->load(['course', 'student']));
    }

    public function update(Request $request, CourseReview $courseReview): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'student_id' => 'required|exists:student_profiles,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string',
        ]);
        $courseReview->update($validated);
        return response()->json($courseReview->load(['course', 'student']));
    }

    public function destroy(CourseReview $courseReview): JsonResponse
    {
        $courseReview->delete();
        return response()->json(['message' => 'Course review deleted successfully']);
    }
}
