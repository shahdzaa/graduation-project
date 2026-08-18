<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StudentProfileResource;

class StudentProfileController extends Controller
{
    public function index(): JsonResponse
    {
        $students = StudentProfile::with(['user'])
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', fn($r) => $r->where('name', 'student'));
            })
            ->latest()
            ->paginate(15);

        return StudentProfileResource::collection($students)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id|unique:student_profiles,user_id',
            'phone'      => 'nullable|string|max:20',
            'github_url' => 'nullable|url|max:255',
            'birth_date' => 'nullable|date',
            'country'    => 'nullable|string|max:100',
        ]);

        $student = StudentProfile::create($validated);

        return (new StudentProfileResource($student->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(StudentProfile $studentProfile): JsonResponse
    {
        return (new StudentProfileResource(
            $studentProfile->load('user')
        ))->response();
    }

    public function update(Request $request, StudentProfile $studentProfile): JsonResponse
    {
        $validated = $request->validate([
            'phone'      => 'nullable|string|max:20',
            'github_url' => 'nullable|url|max:255',
            'birth_date' => 'nullable|date',
            'country'    => 'nullable|string|max:100',
        ]);

        $studentProfile->update($validated);

        return (new StudentProfileResource(
            $studentProfile->load('user')
        ))->response();
    }

    public function destroy(StudentProfile $studentProfile): JsonResponse
    {
        $studentProfile->delete();

        return response()->json(['message' => 'Student profile deleted successfully']);
    }
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'studentProfile',
            'studentCourses' => function ($q) {
                $q->with(['level', 'domain']);
            },
            'testAttempts' => function ($q) {
                $q->with('assessment')->latest('start_time');
            },
        ]);

        $profile = $user->studentProfile;

        $age = $profile?->birth_date
            ? \Carbon\Carbon::parse($profile->birth_date)->age
            : null;

        // الكورسات
        $courses = $user->studentCourses->map(function ($sc) {
            return [
                'id'       => $sc->course->id,
                'title'    => $sc->course->title,
                'progress' => $sc->progress_percent ?? 0,   // عمود مباشر بـ StudentCourse نفسه
                'status'   => $sc->status ?? 'enrolled',
                'language' => $sc->course->language ?? null,
                'level'    => $sc->course->level->name ?? null,
            ];
        });

        $enrolledCount  = $courses->count();
        $completedCount = $courses->where('status', 'completed')->count();

        // محاولات الاختبار → بطاقات Assessment
        $assessments = $user->testAttempts
            ->filter(fn($attempt) => $attempt->end_time !== null) // بس المحاولات المخلّصة
            ->map(function ($attempt) {
                return [
                    'id'    => $attempt->id,
                    'title' => $attempt->assessment->name ?? 'Assessment',
                    'date'  => \Carbon\Carbon::parse($attempt->end_time)->format('Y-m-d'),
                    'score' => (int) round($attempt->total_score),
                    'color' => 'purple',
                ];
            })
            ->values();

        return response()->json([
            'student' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->avatar,
                'role'   => $user->getRoleNames()->first(),
                'track'  => '',
                'enrolledCourses'  => $enrolledCount,
                'completedCourses' => $completedCount,
                'completionRate'   => $enrolledCount
                    ? round($completedCount / $enrolledCount * 100)
                    : 0,
                'studyHours' => 0,
            ],
            'about' => [
                'age'            => $age,
                'educationLevel' => null,
                'studyHours'     => 0,
                'location'       => $profile?->country,
                'github'         => $profile?->github_url,
                'interests'      => [],
            ],
            'courses' => $courses,
            'skills' => [
                ['name' => 'Problem Solving', 'percentage' => 70, 'color' => 'purple'],
                ['name' => 'Communication',   'percentage' => 60, 'color' => 'cyan'],
            ],
            'assessments' => $assessments,
        ]);
    }
}