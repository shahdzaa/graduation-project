<?php

namespace App\Http\Controllers;

use App\Models\StudentSkillMatrix;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentSkillMatrixController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(StudentSkillMatrix::with(['student', 'skill'])->get());
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
        return response()->json($skillMatrix->load(['student', 'skill']), 201);
    }

    public function show(StudentSkillMatrix $studentSkillMatrix): JsonResponse
    {
        return response()->json($studentSkillMatrix->load(['student', 'skill']));
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
        return response()->json($studentSkillMatrix->load(['student', 'skill']));
    }

    public function destroy(StudentSkillMatrix $studentSkillMatrix): JsonResponse
    {
        $studentSkillMatrix->delete();
        return response()->json(['message' => 'Student skill matrix deleted successfully']);
    }
}
