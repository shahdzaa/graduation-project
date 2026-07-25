<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StudentCourseResource;

class StudentCourseController extends Controller
{
    public function index(): JsonResponse
    {
        return StudentCourseResource::collection(StudentCourse::with(['student', 'course'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
            'completion_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'certificate_issued' => 'boolean',
        ]);
        $studentCourse = StudentCourse::create($validated);
        return (new StudentCourseResource($studentCourse->load(['student', 'course'])))->response()->setStatusCode(201);
    }

    public function show(StudentCourse $studentCourse): JsonResponse
    {
        return (new StudentCourseResource($studentCourse->load(['student', 'course'])))->response();
    }

    public function update(Request $request, StudentCourse $studentCourse): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'required|date',
            'completion_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'certificate_issued' => 'boolean',
        ]);
        $studentCourse->update($validated);
        return (new StudentCourseResource($studentCourse->load(['student', 'course'])))->response();
    }

    public function destroy(StudentCourse $studentCourse): JsonResponse
    {
        $studentCourse->delete();
        return response()->json(['message' => 'Student course deleted successfully']);
    }
}
