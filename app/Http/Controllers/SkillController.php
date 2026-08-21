<?php

namespace App\Http\Controllers;

use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'course_id' => 'nullable|integer|exists:courses,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $skills = Skill::query()
            ->select(['id', 'name'])
            ->withCount(['courses', 'studentSkillMatrices'])
            ->when(isset($validated['search']), fn ($q) => $q->where('name', 'like', "%{$validated['search']}%"))
            ->when(isset($validated['course_id']), function ($query) use ($validated) {
                $query->whereHas('courses', fn ($q) => $q->where('courses.id', $validated['course_id']));
            })
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return SkillResource::collection($skills)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $skill = Skill::create($request->validate(['name' => 'required|string|max:250|unique:skills,name']));

        return (new SkillResource($skill))->response()->setStatusCode(201);
    }

    public function show(Skill $skill): JsonResponse
    {
        return (new SkillResource($skill->loadCount(['courses', 'studentSkillMatrices'])))->response();
    }

    public function update(Request $request, Skill $skill): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250|unique:skills,name,' . $skill->id,
        ]);
        $skill->update($validated);

        return (new SkillResource($skill->loadCount(['courses', 'studentSkillMatrices'])))->response();
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
