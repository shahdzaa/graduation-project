<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DomainResource;

class DomainController extends Controller
{
    public function index(): JsonResponse
    {
        return DomainResource::collection(Domain::with(['assessments', 'aptitudeMappings', 'categories.courses', 'courses'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
        ]);

        $domain = Domain::create($validated);
        return (new DomainResource($domain))->response()->setStatusCode(201);
    }

    public function show(Domain $domain): JsonResponse
    {
        return (new DomainResource($domain->load(['assessments', 'aptitudeMappings', 'categories.courses', 'courses'])))->response();
    }

    public function update(Request $request, Domain $domain): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
        ]);

        $domain->update($validated);
        return (new DomainResource($domain))->response();
    }

    public function destroy(Domain $domain): JsonResponse
    {
        $domain->delete();
        return response()->json(['message' => 'Domain deleted successfully']);
    }
}
