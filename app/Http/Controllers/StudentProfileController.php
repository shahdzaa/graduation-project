<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentProfileResource;
use App\Models\StudentProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $students = StudentProfile::query()
            ->with(['user:id,name,email,avatar,is_active', 'user.roles:id,name'])
            ->whereHas('user.roles', fn ($q) => $q->where('name', 'student'))
            ->when(isset($validated['search']), function ($query) use ($validated) {
                $query->whereHas('user', function ($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['search']}%")
                        ->orWhere('email', 'like', "%{$validated['search']}%");
                });
            })
            ->latest()
            ->paginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return StudentProfileResource::collection($students)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $student = StudentProfile::create($request->validate([
            'user_id' => 'required|exists:users,id|unique:student_profiles,user_id',
            'phone' => 'nullable|string|max:20',
            'github_url' => 'nullable|url|max:255',
            'birth_date' => 'nullable|date',
            'country' => 'nullable|string|max:100',
        ]));

        return (new StudentProfileResource($student->load(['user', 'user.roles'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, StudentProfile $studentProfile): JsonResponse
    {
        $this->ensureCanAccess($request, $studentProfile);

        $studentProfile->load([
            'user:id,name,email,avatar,is_active',
            'user.roles:id,name',
            'user.studentCourses' => fn ($query) => $query->latest('enrolled_at'),
            'user.studentCourses.course:id,title,url,thumbnail,language,duration_minutes,average_rating,level_id,domain_id',
            'user.studentCourses.course.level:id,name',
            'user.studentCourses.course.domain:id,name',
            'user.skillMatrix' => fn ($query) => $query->orderByDesc('current_score'),
            'user.skillMatrix.skill:id,name',
            'user.placementAttempts' => fn ($query) => $query->latest('start_time'),
        ]);

        return (new StudentProfileResource($studentProfile))->response();
    }

    public function update(Request $request, StudentProfile $studentProfile): JsonResponse
    {
        $this->ensureCanAccess($request, $studentProfile);

        $studentProfile->update($request->validate([
            'phone' => 'nullable|string|max:20',
            'github_url' => 'nullable|url|max:255',
            'birth_date' => 'nullable|date',
            'country' => 'nullable|string|max:100',
        ]));

        return (new StudentProfileResource($studentProfile->load(['user', 'user.roles'])))->response();
    }

    public function destroy(StudentProfile $studentProfile): JsonResponse
    {
        $studentProfile->delete();

        return response()->json(['message' => 'Student profile deleted successfully']);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'roles:id,name',
            'studentProfile',
            'studentCourses.course:id,title,language,duration_minutes,level_id,domain_id',
            'studentCourses.course.level:id,name',
            'studentCourses.course.domain:id,name',
            'placementAttempts' => fn ($q) => $q->latest('start_time'),
            'skillMatrix.skill:id,name',
        ]);

        $profile = $user->studentProfile;
        $courses = $user->studentCourses->map(fn ($enrollment) => [
            'id' => $enrollment->course->id,
            'title' => $enrollment->course->title,
            'progress' => $enrollment->progress_percent,
            'status' => $enrollment->status,
            'language' => $enrollment->course->language,
            'level' => $enrollment->course->level?->name,
            'domain' => $enrollment->course->domain?->name,
        ]);

        $completedCount = $courses->where('status', 'completed')->count();
        $attempts = $user->placementAttempts
            ->where('status', 'completed')
            ->map(fn ($attempt) => [
                'id' => $attempt->id,
                'title' => $attempt->category,
                'date' => $attempt->end_time?->format('Y-m-d'),
                'score' => (int) $attempt->total_score,
                'color' => 'purple',
            ])
            ->values();

        return response()->json([
            'student' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->roles->first()?->name,
                'track' => '',
                'enrolledCourses' => $courses->count(),
                'completedCourses' => $completedCount,
                'completionRate' => $courses->isNotEmpty()
                    ? round($completedCount / $courses->count() * 100)
                    : 0,
                'studyHours' => 0,
            ],
            'about' => [
                'age' => $profile?->birth_date ? Carbon::parse($profile->birth_date)->age : null,
                'educationLevel' => null,
                'studyHours' => 0,
                'location' => $profile?->country,
                'github' => $profile?->github_url,
                'interests' => [],
            ],
            'courses' => $courses,
            'skills' => $user->skillMatrix->map(fn ($matrix) => [
                'name' => $matrix->skill->name,
                'percentage' => $matrix->current_score,
                'color' => 'purple',
            ])->values(),
            'assessments' => $attempts,
        ]);
    }

    private function ensureCanAccess(Request $request, StudentProfile $profile): void
    {
        abort_unless(
            $request->user()->hasRole('admin') || $profile->user_id === $request->user()->id,
            403
        );
    }
}
