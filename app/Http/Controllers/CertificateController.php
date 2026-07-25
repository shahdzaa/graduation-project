<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CertificateResource;

class CertificateController extends Controller
{
    public function index(): JsonResponse
    {
        return CertificateResource::collection(Certificate::with(['user', 'course'])->get())->response();
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
        return (new CertificateResource($certificate->load(['user', 'course'])))->response()->setStatusCode(201);
    }

    public function show(Certificate $certificate): JsonResponse
    {
        return (new CertificateResource($certificate->load(['user', 'course'])))->response();
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
        return (new CertificateResource($certificate->load(['user', 'course'])))->response();
    }

    public function destroy(Certificate $certificate): JsonResponse
    {
        $certificate->delete();
        return response()->json(['message' => 'Certificate deleted successfully']);
    }
}
