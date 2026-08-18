<?php

namespace App\Http\Controllers;

use App\Models\InstructorProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\InstructorProfileResource;

class InstructorProfileController extends Controller
{
    /**
     * GET /instructor-profiles
     * Admin only — قائمة كل المدرسين
     */
    public function index(): JsonResponse
{
    $instructors = InstructorProfile::with(['user', 'courses'])
        ->whereHas('user', function ($q) {
            $q->whereHas('roles', function ($r) {
                $r->where('name', 'instructor');
            });
        })
        ->latest()
        ->paginate(15);

    return InstructorProfileResource::collection($instructors)->response();
}

    /**
     * POST /instructor-profiles
     * إنشاء profile لمدرس موجود
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'          => 'required|exists:users,id|unique:instructor_profiles,user_id',
            'bio'              => 'nullable|string',
            'specialization'   => 'nullable|string|max:255',
            'linkedin_url'     => 'nullable|url|max:255',
            'website_url'      => 'nullable|url|max:255',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'average_rating'   => 'nullable|numeric|min:0|max:5',
        ]);

        $instructor = InstructorProfile::create($validated);

        return (new InstructorProfileResource($instructor->load(['user', 'courses'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /instructor-profiles/{instructorProfile}
     * صفحة المدرس الكاملة
     */
    public function show(InstructorProfile $instructorProfile): JsonResponse
    {
        return (new InstructorProfileResource(
            $instructorProfile->load(['user', 'courses'])
        ))->response();
    }

    /**
     * PUT /instructor-profiles/{instructorProfile}
     * تعديل بيانات المدرس
     */
    public function update(Request $request, InstructorProfile $instructorProfile): JsonResponse
    {
        $validated = $request->validate([
            'bio'              => 'nullable|string',
            'specialization'   => 'nullable|string|max:255',
            'linkedin_url'     => 'nullable|url|max:255',
            'website_url'      => 'nullable|url|max:255',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'average_rating'   => 'nullable|numeric|min:0|max:5',
        ]);

        $instructorProfile->update($validated);

        return (new InstructorProfileResource(
            $instructorProfile->load(['user', 'courses'])
        ))->response();
    }

    /**
     * DELETE /instructor-profiles/{instructorProfile}
     * حذف profile المدرس (وليس الـ user)
     */
    public function destroy(InstructorProfile $instructorProfile): JsonResponse
    {
        $instructorProfile->delete();

        return response()->json(['message' => 'Instructor profile deleted successfully']);
    }
    // InstructorProfileController.php
    // InstructorProfileController.php
public function me(Request $request): JsonResponse
{
    $user = $request->user()->load([
        'instructorProfile',
        'taughtCourses' => function ($q) {
            $q->with(['level', 'domain']);
        },
    ]);

    $profile = $user->instructorProfile;

    if (!$profile) {
        return response()->json(['message' => 'لا يوجد بروفايل مدرس لهذا المستخدم'], 404);
    }

    return response()->json([
        'id'               => $profile->id,
        'bio'              => $profile->bio,
        'specialization'   => $profile->specialization,
        'linkedin_url'     => $profile->linkedin_url,
        'website_url'      => $profile->website_url,
        'years_experience' => $profile->years_experience,
        'average_rating'   => $profile->average_rating,
        'user' => [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->getRoleNames()->first(),
        ],
        'courses' => \App\Http\Resources\CourseResource::collection($user->taughtCourses),
    ]);
}
}
