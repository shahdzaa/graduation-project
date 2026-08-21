<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseSkillResource;
use App\Models\CourseSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseSkillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'skill_id' => 'nullable|exists:skills,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $links = CourseSkill::query()
            ->with(['course:id,title', 'skill:id,name'])
            ->when(isset($validated['course_id']), fn ($q) => $q->where('course_id', $validated['course_id']))
            ->when(isset($validated['skill_id']), fn ($q) => $q->where('skill_id', $validated['skill_id']))
            ->orderBy('course_id')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return CourseSkillResource::collection($links)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $link = CourseSkill::firstOrCreate($request->validate([
            'course_id' => 'required|exists:courses,id',
            'skill_id' => 'required|exists:skills,id',
        ]));

        return (new CourseSkillResource($link->load(['course:id,title', 'skill:id,name'])))
            ->response()
            ->setStatusCode($link->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(int $course, int $skill): JsonResponse
    {
        $deleted = CourseSkill::where('course_id', $course)->where('skill_id', $skill)->delete();
        abort_if($deleted === 0, 404);

        return response()->json(['message' => 'Course skill deleted successfully']);
    }
}
