<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Domain::with(['assessments', 'aptitudeMappings', 'categories'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
        ]);

        $domain = Domain::create($validated);
        return response()->json($domain, 201);
    }

    public function show(Domain $domain): JsonResponse
    {
        return response()->json($domain->load(['assessments', 'aptitudeMappings', 'categories']));
    }

    public function update(Request $request, Domain $domain): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
        ]);

        $domain->update($validated);
        return response()->json($domain);
    }

    public function destroy(Domain $domain): JsonResponse
    {
        $domain->delete();
        return response()->json(['message' => 'Domain deleted successfully']);
    }
}
