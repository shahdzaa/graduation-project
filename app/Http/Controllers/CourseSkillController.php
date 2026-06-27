<?php

namespace App\Http\Controllers;

use App\Models\CourseSkill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseSkillController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseSkill::with(['course', 'skill'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'skill_id' => 'required|exists:skills,id',
        ]);
        $courseSkill = CourseSkill::create($validated);
        return response()->json($courseSkill->load(['course', 'skill']), 201);
    }

    public function show(CourseSkill $courseSkill): JsonResponse
    {
        return response()->json($courseSkill->load(['course', 'skill']));
    }

    public function update(Request $request, CourseSkill $courseSkill): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'skill_id' => 'required|exists:skills,id',
        ]);
        $courseSkill->update($validated);
        return response()->json($courseSkill->load(['course', 'skill']));
    }

    public function destroy(CourseSkill $courseSkill): JsonResponse
    {
        $courseSkill->delete();
        return response()->json(['message' => 'Course skill deleted successfully']);
    }
}
