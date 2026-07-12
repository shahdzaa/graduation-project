<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ModuleResource;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        return ModuleResource::collection(Module::with(['courses', 'syllabi'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer',
        ]);
        $module = Module::create($validated);
        return (new ModuleResource($module))->response()->setStatusCode(201);
    }

    public function show(Module $module): JsonResponse
    {
        return (new ModuleResource($module->load(['courses', 'syllabi'])))->response();
    }

    public function update(Request $request, Module $module): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer',
        ]);
        $module->update($validated);
        return (new ModuleResource($module))->response();
    }

    public function destroy(Module $module): JsonResponse
    {
        $module->delete();
        return response()->json(['message' => 'Module deleted successfully']);
    }
}
