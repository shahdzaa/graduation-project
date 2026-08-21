<?php

namespace App\Http\Controllers;

use App\Http\Resources\CertificateResource;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $certificates = Certificate::query()
            ->select(['id', 'user_id', 'course_id', 'certificate_number', 'file_path', 'issued_at', 'created_at', 'updated_at'])
            ->with([
                'course:id,title,thumbnail,domain_id,level_id,type_id,average_rating',
                'course.domain:id,name',
                'course.level:id,name',
                'course.type:id,name',
            ])
            ->when(! $request->user()->hasRole('admin'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('issued_at')
            ->paginate(min(max((int) $request->input('per_page', 20), 1), 100));

        return CertificateResource::collection($certificates)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $certificate = Certificate::create($request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'certificate_number' => 'required|string|max:100|unique:certificates,certificate_number',
            'file_path' => 'nullable|string|max:1000',
            'issued_at' => 'required|date',
        ]));

        return (new CertificateResource($this->loadCourse($certificate)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Certificate $certificate): JsonResponse
    {
        $this->ensureCanAccess($request, $certificate);

        return (new CertificateResource($this->loadCourse($certificate)))->response();
    }

    public function update(Request $request, Certificate $certificate): JsonResponse
    {
        $certificate->update($request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'course_id' => 'sometimes|exists:courses,id',
            'certificate_number' => 'sometimes|string|max:100|unique:certificates,certificate_number,' . $certificate->id,
            'file_path' => 'nullable|string|max:1000',
            'issued_at' => 'sometimes|date',
        ]));

        return (new CertificateResource($this->loadCourse($certificate)))->response();
    }

    public function destroy(Certificate $certificate): JsonResponse
    {
        $certificate->delete();

        return response()->json(['message' => 'Certificate deleted successfully']);
    }

    private function loadCourse(Certificate $certificate): Certificate
    {
        return $certificate->load([
            'user:id,name,email,avatar,is_active',
            'user.roles:id,name',
            'course:id,title,thumbnail,domain_id,level_id,type_id,average_rating',
            'course.domain:id,name',
            'course.level:id,name',
            'course.type:id,name',
        ]);
    }

    private function ensureCanAccess(Request $request, Certificate $certificate): void
    {
        abort_unless(
            $request->user()->hasRole('admin') || $certificate->user_id === $request->user()->id,
            403
        );
    }
}
