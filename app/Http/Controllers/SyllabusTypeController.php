<?php

namespace App\Http\Controllers;

use App\Models\SyllabusType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SyllabusTypeResource;

class SyllabusTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return SyllabusTypeResource::collection(SyllabusType::withCount('syllabi')->orderBy('name')->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $type = SyllabusType::create($validated);
        return (new SyllabusTypeResource($type))->response()->setStatusCode(201);
    }

    public function show(SyllabusType $syllabusType): JsonResponse
    {
        return (new SyllabusTypeResource($syllabusType->loadCount('syllabi')))->response();
    }

    public function update(Request $request, SyllabusType $syllabusType): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $syllabusType->update($validated);
        return (new SyllabusTypeResource($syllabusType))->response();
    }

    public function destroy(SyllabusType $syllabusType): JsonResponse
    {
        $syllabusType->delete();
        return response()->json(['message' => 'Syllabus type deleted successfully']);
    }
}
