<?php

namespace App\Http\Controllers;

use App\Models\CourseOrganization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseOrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CourseOrganization::with(['course', 'organization'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);
        $courseOrganization = CourseOrganization::create($validated);
        return response()->json($courseOrganization->load(['course', 'organization']), 201);
    }

    public function show(CourseOrganization $courseOrganization): JsonResponse
    {
        return response()->json($courseOrganization->load(['course', 'organization']));
    }

    public function update(Request $request, CourseOrganization $courseOrganization): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);
        $courseOrganization->update($validated);
        return response()->json($courseOrganization->load(['course', 'organization']));
    }

    public function destroy(CourseOrganization $courseOrganization): JsonResponse
    {
        $courseOrganization->delete();
        return response()->json(['message' => 'Course organization deleted successfully']);
    }
}
