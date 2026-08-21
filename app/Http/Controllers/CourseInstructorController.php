<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseInstructorResource;
use App\Models\CourseInstructor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseInstructorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'user_id' => 'nullable|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $requestedUserId = $request->user()->hasRole('admin')
            ? ($validated['user_id'] ?? null)
            : $request->user()->id;

        $links = CourseInstructor::query()
            ->with([
                'course:id,title',
                'user:id,name,email,avatar,is_active',
                'user.roles:id,name',
                'user.instructorProfile:id,user_id,bio,specialization,linkedin_url,years_experience,website_url,average_rating',
            ])
            ->when(isset($validated['course_id']), fn ($q) => $q->where('course_id', $validated['course_id']))
            ->when($requestedUserId, fn ($q) => $q->where('user_id', $requestedUserId))
            ->orderBy('course_id')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return CourseInstructorResource::collection($links)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'user_id' => 'required|exists:users,id',
        ]);

        abort_unless(
            $request->user()->hasRole('admin') || $request->user()->id === (int) $validated['user_id'],
            403
        );

        abort_unless(
            User::role('instructor', 'web')->whereKey($validated['user_id'])->exists(),
            422,
            'المستخدم المحدد ليس مدرّساً.'
        );

        $link = CourseInstructor::firstOrCreate($validated);

        return (new CourseInstructorResource($this->loadRelations($link)))
            ->response()
            ->setStatusCode($link->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, int $course, int $user): JsonResponse
    {
        abort_unless(
            $request->user()->hasRole('admin') || $request->user()->id === $user,
            403
        );

        $deleted = CourseInstructor::where('course_id', $course)->where('user_id', $user)->delete();
        abort_if($deleted === 0, 404);

        return response()->json(['message' => 'Course instructor deleted successfully']);
    }

    private function loadRelations(CourseInstructor $link): CourseInstructor
    {
        return $link->load([
            'course:id,title',
            'user:id,name,email,avatar,is_active',
            'user.roles:id,name',
            'user.instructorProfile:id,user_id,bio,specialization,linkedin_url,years_experience,website_url,average_rating',
        ]);
    }
}
