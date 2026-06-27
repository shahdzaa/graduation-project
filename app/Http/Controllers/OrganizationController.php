<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Organization::with('courses')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $organization = Organization::create($validated);
        return response()->json($organization, 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->json($organization->load('courses'));
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $organization->update($validated);
        return response()->json($organization);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();
        return response()->json(['message' => 'Organization deleted successfully']);
    }
}
