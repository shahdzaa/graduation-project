<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CertificateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Certificate::with(['user', 'course'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'certificate_number' => 'required|string|max:100|unique:certificates',
            'file_path' => 'nullable|string',
            'issued_at' => 'required|date',
        ]);

        $certificate = Certificate::create($validated);
        return response()->json($certificate->load(['user', 'course']), 201);
    }

    public function show(Certificate $certificate): JsonResponse
    {
        return response()->json($certificate->load(['user', 'course']));
    }

    public function update(Request $request, Certificate $certificate): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'certificate_number' => 'required|string|max:100|unique:certificates,certificate_number,' . $certificate->id,
            'file_path' => 'nullable|string',
            'issued_at' => 'required|date',
        ]);

        $certificate->update($validated);
        return response()->json($certificate->load(['user', 'course']));
    }

    public function destroy(Certificate $certificate): JsonResponse
    {
        $certificate->delete();
        return response()->json(['message' => 'Certificate deleted successfully']);
    }
}
