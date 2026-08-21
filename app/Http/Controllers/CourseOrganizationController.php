<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseOrganizationResource;
use App\Models\CourseOrganization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseOrganizationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $links = CourseOrganization::query()
            ->with(['course:id,title', 'organization:id,name'])
            ->when(isset($validated['course_id']), fn ($q) => $q->where('course_id', $validated['course_id']))
            ->when(isset($validated['organization_id']), fn ($q) => $q->where('organization_id', $validated['organization_id']))
            ->orderBy('course_id')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return CourseOrganizationResource::collection($links)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $link = CourseOrganization::firstOrCreate($request->validate([
            'course_id' => 'required|exists:courses,id',
            'organization_id' => 'required|exists:organizations,id',
        ]));

        return (new CourseOrganizationResource($link->load(['course:id,title', 'organization:id,name'])))
            ->response()
            ->setStatusCode($link->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(int $course, int $organization): JsonResponse
    {
        $deleted = CourseOrganization::where('course_id', $course)
            ->where('organization_id', $organization)
            ->delete();
        abort_if($deleted === 0, 404);

        return response()->json(['message' => 'Course organization deleted successfully']);
    }
}
