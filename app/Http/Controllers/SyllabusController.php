<?php

namespace App\Http\Controllers;

use App\Http\Resources\SyllabusResource;
use App\Models\Syllabus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SyllabusController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module_id' => 'nullable|integer|exists:modules,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $syllabi = Syllabus::query()
            ->select(['id', 'module_id', 'name', 'order_index', 'type_id', 'category_id', 'duration_minutes'])
            ->with(['module:id,name', 'type:id,name', 'category:id,name,slug,domain_id'])
            ->when(isset($validated['module_id']), fn ($q) => $q->where('module_id', $validated['module_id']))
            ->when(isset($validated['category_id']), fn ($q) => $q->where('category_id', $validated['category_id']))
            ->when(isset($validated['search']), fn ($q) => $q->where('name', 'like', "%{$validated['search']}%"))
            ->orderBy('module_id')
            ->orderBy('order_index')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return SyllabusResource::collection($syllabi)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $syllabus = Syllabus::create($request->validate($this->rules()));

        return (new SyllabusResource($this->loadRelations($syllabus)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Syllabus $syllabus): JsonResponse
    {
        return (new SyllabusResource($this->loadRelations($syllabus)))->response();
    }

    public function update(Request $request, Syllabus $syllabus): JsonResponse
    {
        $syllabus->update($request->validate($this->rules($syllabus)));

        return (new SyllabusResource($this->loadRelations($syllabus)))->response();
    }

    public function destroy(Syllabus $syllabus): JsonResponse
    {
        $syllabus->delete();

        return response()->json(['message' => 'Syllabus deleted successfully']);
    }

    private function loadRelations(Syllabus $syllabus): Syllabus
    {
        return $syllabus->load(['module:id,name', 'type:id,name', 'category:id,name,slug,domain_id']);
    }

    private function rules(?Syllabus $syllabus = null): array
    {
        $required = $syllabus ? 'sometimes' : 'required';

        return [
            'module_id' => "{$required}|exists:modules,id",
            'name' => "{$required}|string|max:5000",
            'order_index' => [
                $required,
                'integer',
                'min:0',
                Rule::unique('syllabus')->where(
                    fn ($query) => $query->where('module_id', request('module_id', $syllabus?->module_id))
                )->ignore($syllabus?->id),
            ],
            'type_id' => "{$required}|exists:syllabus_types,id",
            'category_id' => 'nullable|exists:categories,id',
            'duration_minutes' => "{$required}|integer|min:0",
        ];
    }
}
