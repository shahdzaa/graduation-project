<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentCourseResource;
use App\Models\StudentCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|in:active,completed,dropped',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $enrollments = StudentCourse::query()
            ->where('user_id', $request->user()->id)
            ->select(['id', 'user_id', 'course_id', 'enrolled_at', 'status', 'progress_percent', 'created_at', 'updated_at'])
            ->with([
                'course:id,title,url,thumbnail,price,is_free,language,is_published,duration_minutes,level_id,type_id,domain_id,average_rating',
                'course.level:id,name',
                'course.type:id,name',
                'course.domain:id,name',
            ])
            ->when(isset($validated['status']), fn ($q) => $q->where('status', $validated['status']))
            ->latest('enrolled_at')
            ->paginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return StudentCourseResource::collection($enrollments)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => [
                'required',
                'exists:courses,id',
                Rule::unique('student_courses')->where(fn ($q) => $q->where('user_id', $request->user()->id)),
            ],
            'enrolled_at' => 'nullable|date',
        ]);

        $enrollment = StudentCourse::create([
            'user_id' => $request->user()->id,
            'course_id' => $validated['course_id'],
            'enrolled_at' => $validated['enrolled_at'] ?? now(),
            'status' => 'active',
            'progress_percent' => 0,
        ]);

        return (new StudentCourseResource($this->loadCourse($enrollment)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, StudentCourse $studentCourse): JsonResponse
    {
        $this->ensureOwner($request, $studentCourse);

        return (new StudentCourseResource($this->loadCourse($studentCourse)))->response();
    }

    public function update(Request $request, StudentCourse $studentCourse): JsonResponse
    {
        $this->ensureOwner($request, $studentCourse);
        $studentCourse->update($request->validate([
            'status' => 'sometimes|in:active,completed,dropped',
            'progress_percent' => 'sometimes|numeric|min:0|max:100',
        ]));

        return (new StudentCourseResource($this->loadCourse($studentCourse)))->response();
    }

    public function destroy(Request $request, StudentCourse $studentCourse): JsonResponse
    {
        $this->ensureOwner($request, $studentCourse);
        $studentCourse->delete();

        return response()->json(['message' => 'Student course deleted successfully']);
    }

    private function loadCourse(StudentCourse $enrollment): StudentCourse
    {
        return $enrollment->load([
            'course:id,title,url,thumbnail,price,is_free,language,is_published,duration_minutes,level_id,type_id,domain_id,average_rating',
            'course.level:id,name',
            'course.type:id,name',
            'course.domain:id,name',
        ]);
    }

    private function ensureOwner(Request $request, StudentCourse $enrollment): void
    {
        abort_unless(
            $request->user()->hasRole('admin') || $enrollment->user_id === $request->user()->id,
            403
        );
    }
}
