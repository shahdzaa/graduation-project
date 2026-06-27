<?php

namespace App\Http\Controllers;

use App\Models\SyllabusType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SyllabusTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SyllabusType::with('syllabi')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $type = SyllabusType::create($validated);
        return response()->json($type, 201);
    }

    public function show(SyllabusType $syllabusType): JsonResponse
    {
        return response()->json($syllabusType->load('syllabi'));
    }

    public function update(Request $request, SyllabusType $syllabusType): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $syllabusType->update($validated);
        return response()->json($syllabusType);
    }

    public function destroy(SyllabusType $syllabusType): JsonResponse
    {
        $syllabusType->delete();
        return response()->json(['message' => 'Syllabus type deleted successfully']);
    }
}
