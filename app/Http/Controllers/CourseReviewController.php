<?php

namespace App\Http\Controllers;

use App\Models\CourseReview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseReviewResource;

class CourseReviewController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseReviewResource::collection(CourseReview::with(['course', 'student'])->get())->response();
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
        return (new CourseReviewResource($review->load(['course', 'student'])))->response()->setStatusCode(201);
    }

    public function show(CourseReview $courseReview): JsonResponse
    {
        return (new CourseReviewResource($courseReview->load(['course', 'student'])))->response();
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
        return (new CourseReviewResource($courseReview->load(['course', 'student'])))->response();
    }

    public function destroy(CourseReview $courseReview): JsonResponse
    {
        $courseReview->delete();
        return response()->json(['message' => 'Course review deleted successfully']);
    }
}
