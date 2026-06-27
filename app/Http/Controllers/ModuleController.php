<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Module::with(['courses', 'syllabi'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer',
        ]);
        $module = Module::create($validated);
        return response()->json($module, 201);
    }

    public function show(Module $module): JsonResponse
    {
        return response()->json($module->load(['courses', 'syllabi']));
    }

    public function update(Request $request, Module $module): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer',
        ]);
        $module->update($validated);
        return response()->json($module);
    }

    public function destroy(Module $module): JsonResponse
    {
        $module->delete();
        return response()->json(['message' => 'Module deleted successfully']);
    }
}
