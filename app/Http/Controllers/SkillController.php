<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SkillResource;

class SkillController extends Controller
{
    public function index(): JsonResponse
    {
        return SkillResource::collection(Skill::with(['courses', 'aptitudeMappings', 'studentSkillMatrices'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $skill = Skill::create($validated);
        return (new SkillResource($skill))->response()->setStatusCode(201);
    }

    public function show(Skill $skill): JsonResponse
    {
        return (new SkillResource($skill->load(['courses', 'aptitudeMappings', 'studentSkillMatrices'])))->response();
    }

    public function update(Request $request, Skill $skill): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $skill->update($validated);
        return (new SkillResource($skill))->response();
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
