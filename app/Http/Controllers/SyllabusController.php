<?php

namespace App\Http\Controllers;

use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SyllabusResource;

class SyllabusController extends Controller
{
    public function index(): JsonResponse
    {
        return SyllabusResource::collection(Syllabus::with(['module', 'type'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'name' => 'required|string|max:5000',
            'type_id' => 'required|exists:syllabus_types,id',
            'duration_minutes' => 'required|integer',
        ]);
        $syllabus = Syllabus::create($validated);
        return (new SyllabusResource(syllabus->load(['module', 'type'])))->response()->setStatusCode(201);
    }

    public function show(Syllabus $syllabus): JsonResponse
    {
        return (new SyllabusResource($syllabus->load(['module', 'type'])))->response();
    }

    public function update(Request $request, Syllabus $syllabus): JsonResponse
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'name' => 'required|string|max:5000',
            'type_id' => 'required|exists:syllabus_types,id',
            'duration_minutes' => 'required|integer',
        ]);
        $syllabus->update($validated);
        return (new SyllabusResource($syllabus->load(['module', 'type'])))->response();
    }

    public function destroy(Syllabus $syllabus): JsonResponse
    {
        $syllabus->delete();
        return response()->json(['message' => 'Syllabus deleted successfully']);
    }
}
