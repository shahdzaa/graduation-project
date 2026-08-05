<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DomainResource;
use App\Http\Resources\CourseResource;

class DomainController extends Controller
{
    public function index(): JsonResponse
{
    $domains = Domain::withCount(['courses' => function ($query) {
        $query->where('is_published', true);
    }])
    ->having('courses_count', '>=', 0)
    ->get(['id', 'name']);

    return response()->json(['data' => $domains]);
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

    public function show(Request $request, Domain $domain): JsonResponse
    {
        $user = auth('sanctum')->user();
        $isStaff = $user && $user->hasAnyRole(['admin', 'instructor']);

        $coursesQuery = $domain->courses()->with(['level', 'type', 'category']);

        if (!$isStaff) {
            $coursesQuery->where('is_published', true);
        }

        $perPage = $request->get('per_page', 12);
        $courses = $coursesQuery->paginate($perPage);

        return response()->json([
            'domain' => [
                'id' => $domain->id,
                'name' => $domain->name,
            ],
            'courses' => CourseResource::collection($courses)->response()->getData(true),
        ]);
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
