<?php

namespace App\Http\Controllers;

use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SyllabusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Syllabus::with(['module', 'type'])->get());
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
        return response()->json($syllabus->load(['module', 'type']), 201);
    }

    public function show(Syllabus $syllabus): JsonResponse
    {
        return response()->json($syllabus->load(['module', 'type']));
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
        return response()->json($syllabus->load(['module', 'type']));
    }

    public function destroy(Syllabus $syllabus): JsonResponse
    {
        $syllabus->delete();
        return response()->json(['message' => 'Syllabus deleted successfully']);
    }
}
