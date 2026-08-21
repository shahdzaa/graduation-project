<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseReviewResource;
use App\Models\CourseReview;
use App\Models\StudentCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|integer|exists:courses,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $user = $request->user();
        $reviews = CourseReview::query()
            ->select(['id', 'course_id', 'user_id', 'rating', 'comment', 'created_at', 'updated_at'])
            ->with(['course:id,title,average_rating', 'user:id,name,email,avatar,is_active', 'user.roles:id,name'])
            ->when($user->hasRole('instructor'), function ($query) use ($user) {
                $query->whereHas('course.instructors', fn ($q) => $q->where('users.id', $user->id));
            })
            ->when(isset($validated['course_id']), fn ($q) => $q->where('course_id', $validated['course_id']))
            ->latest()
            ->paginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return CourseReviewResource::collection($reviews)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => [
                'required',
                'exists:courses,id',
                Rule::unique('course_reviews')->where(fn ($q) => $q->where('user_id', $request->user()->id)),
            ],
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:5000',
        ]);

        abort_unless(
            StudentCourse::where('user_id', $request->user()->id)
                ->where('course_id', $validated['course_id'])
                ->exists(),
            403,
            'يجب التسجيل في الكورس قبل تقييمه.'
        );

        $review = CourseReview::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return (new CourseReviewResource($this->loadRelations($review)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, CourseReview $courseReview): JsonResponse
    {
        $this->ensureCanAccess($request, $courseReview);

        return (new CourseReviewResource($this->loadRelations($courseReview)))->response();
    }

    public function update(Request $request, CourseReview $courseReview): JsonResponse
    {
        abort_unless($courseReview->user_id === $request->user()->id || $request->user()->hasRole('admin'), 403);
        $courseReview->update($request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:5000',
        ]));

        return (new CourseReviewResource($this->loadRelations($courseReview)))->response();
    }

    public function destroy(Request $request, CourseReview $courseReview): JsonResponse
    {
        abort_unless($courseReview->user_id === $request->user()->id || $request->user()->hasRole('admin'), 403);
        $courseReview->delete();

        return response()->json(['message' => 'Course review deleted successfully']);
    }

    private function loadRelations(CourseReview $review): CourseReview
    {
        return $review->load(['course:id,title,average_rating', 'user:id,name,email,avatar,is_active', 'user.roles:id,name']);
    }

    private function ensureCanAccess(Request $request, CourseReview $review): void
    {
        if ($request->user()->hasRole('admin') || $review->user_id === $request->user()->id) {
            return;
        }

        abort_unless(
            $request->user()->hasRole('instructor')
            && $review->course()->whereHas('instructors', fn ($q) => $q->where('users.id', $request->user()->id))->exists(),
            403
        );
    }
}
