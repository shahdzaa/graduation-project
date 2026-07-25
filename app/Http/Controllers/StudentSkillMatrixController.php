<?php

namespace App\Http\Controllers;

use App\Models\StudentSkillMatrix;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StudentSkillMatrixResource;

class StudentSkillMatrixController extends Controller
{
    public function index(): JsonResponse
    {
        return StudentSkillMatrixResource::collection(StudentSkillMatrix::with(['student', 'skill'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'skill_id' => 'required|exists:skills,id',
            'proficiency_level' => 'required|integer|min:1|max:5',
            'years_of_experience' => 'nullable|integer',
        ]);
        $skillMatrix = StudentSkillMatrix::create($validated);
        return (new StudentSkillMatrixResource($skillMatrix->load(['student', 'skill'])))->response()->setStatusCode(201);
    }

    public function show(StudentSkillMatrix $studentSkillMatrix): JsonResponse
    {
        return (new StudentSkillMatrixResource($studentSkillMatrix->load(['student', 'skill'])))->response();
    }

    public function update(Request $request, StudentSkillMatrix $studentSkillMatrix): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'skill_id' => 'required|exists:skills,id',
            'proficiency_level' => 'required|integer|min:1|max:5',
            'years_of_experience' => 'nullable|integer',
        ]);
        $studentSkillMatrix->update($validated);
        return (new StudentSkillMatrixResource($studentSkillMatrix->load(['student', 'skill'])))->response();
    }

    public function destroy(StudentSkillMatrix $studentSkillMatrix): JsonResponse
    {
        $studentSkillMatrix->delete();
        return response()->json(['message' => 'Student skill matrix deleted successfully']);
    }
}
