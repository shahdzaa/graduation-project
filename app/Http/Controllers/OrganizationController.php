<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrganizationResource;

class OrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        return OrganizationResource::collection(Organization::withCount('courses')->orderBy('name')->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $organization = Organization::create($validated);
        return (new OrganizationResource($organization))->response()->setStatusCode(201);
    }

    public function show(Organization $organization): JsonResponse
    {
        return (new OrganizationResource($organization->loadCount('courses')))->response();
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:250']);
        $organization->update($validated);
        return (new OrganizationResource($organization))->response();
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();
        return response()->json(['message' => 'Organization deleted successfully']);
    }
}
