<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SkillController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Skill::with(['courses', 'aptitudeMappings', 'studentSkillMatrices'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $skill = Skill::create($validated);
        return response()->json($skill, 201);
    }

    public function show(Skill $skill): JsonResponse
    {
        return response()->json($skill->load(['courses', 'aptitudeMappings', 'studentSkillMatrices']));
    }

    public function update(Request $request, Skill $skill): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $skill->update($validated);
        return response()->json($skill);
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
