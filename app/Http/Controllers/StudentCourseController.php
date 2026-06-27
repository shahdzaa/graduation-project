<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentCourseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StudentCourse::with(['student', 'course'])->get());
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
        return response()->json($studentCourse->load(['student', 'course']), 201);
    }

    public function show(StudentCourse $studentCourse): JsonResponse
    {
        return response()->json($studentCourse->load(['student', 'course']));
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
        return response()->json($studentCourse->load(['student', 'course']));
    }

    public function destroy(StudentCourse $studentCourse): JsonResponse
    {
        $studentCourse->delete();
        return response()->json(['message' => 'Student course deleted successfully']);
    }
}
