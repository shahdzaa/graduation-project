<?php

namespace App\Http\Controllers;

use App\Models\CourseSkill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseSkillResource;

class CourseSkillController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseSkillResource::collection(CourseSkill::with(['course', 'skill'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'skill_id' => 'required|exists:skills,id',
        ]);
        $courseSkill = CourseSkill::create($validated);
        return (new CourseSkillResource(courseSkill->load(['course', 'skill'])))->response()->setStatusCode(201);
    }

    public function show(CourseSkill $courseSkill): JsonResponse
    {
        return (new CourseSkillResource($courseSkill->load(['course', 'skill'])))->response();
    }

    public function update(Request $request, CourseSkill $courseSkill): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'skill_id' => 'required|exists:skills,id',
        ]);
        $courseSkill->update($validated);
        return (new CourseSkillResource($courseSkill->load(['course', 'skill'])))->response();
    }

    public function destroy(CourseSkill $courseSkill): JsonResponse
    {
        $courseSkill->delete();
        return response()->json(['message' => 'Course skill deleted successfully']);
    }
}
