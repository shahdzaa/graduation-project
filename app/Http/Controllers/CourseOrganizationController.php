<?php

namespace App\Http\Controllers;

use App\Models\CourseOrganization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseOrganizationResource;

class CourseOrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        return CourseOrganizationResource::collection(CourseOrganization::with(['course', 'organization'])->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);
        $courseOrganization = CourseOrganization::create($validated);
        return (new CourseOrganizationResource(courseOrganization->load(['course', 'organization'])))->response()->setStatusCode(201);
    }

    public function show(CourseOrganization $courseOrganization): JsonResponse
    {
        return (new CourseOrganizationResource($courseOrganization->load(['course', 'organization'])))->response();
    }

    public function update(Request $request, CourseOrganization $courseOrganization): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);
        $courseOrganization->update($validated);
        return (new CourseOrganizationResource($courseOrganization->load(['course', 'organization'])))->response();
    }

    public function destroy(CourseOrganization $courseOrganization): JsonResponse
    {
        $courseOrganization->delete();
        return response()->json(['message' => 'Course organization deleted successfully']);
    }
}
