<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseModuleResource;
use App\Models\CourseModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseModuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|integer|exists:courses,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $links = CourseModule::query()
            ->select(['id', 'course_id', 'module_id', 'order_index', 'created_at', 'updated_at'])
            ->with([
                'course:id,title',
                'module:id,name,description,duration_minutes',
            ])
            ->when(isset($validated['course_id']), fn ($q) => $q->where('course_id', $validated['course_id']))
            ->orderBy('course_id')
            ->orderBy('order_index')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return CourseModuleResource::collection($links)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $link = CourseModule::create($request->validate($this->rules()));

        return (new CourseModuleResource($this->loadRelations($link)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(CourseModule $courseModule): JsonResponse
    {
        return (new CourseModuleResource($this->loadRelations($courseModule)))->response();
    }

    public function update(Request $request, CourseModule $courseModule): JsonResponse
    {
        $courseModule->update($request->validate($this->rules($courseModule)));

        return (new CourseModuleResource($this->loadRelations($courseModule)))->response();
    }

    public function destroy(CourseModule $courseModule): JsonResponse
    {
        $courseModule->delete();

        return response()->json(['message' => 'Course module deleted successfully']);
    }

    private function loadRelations(CourseModule $link): CourseModule
    {
        return $link->load(['course:id,title', 'module:id,name,description,duration_minutes']);
    }

    private function rules(?CourseModule $link = null): array
    {
        $required = $link ? 'sometimes' : 'required';

        return [
            'course_id' => "{$required}|exists:courses,id",
            'module_id' => [
                $required,
                'exists:modules,id',
                Rule::unique('course_modules')->where(
                    fn ($query) => $query->where('course_id', request('course_id', $link?->course_id))
                )->ignore($link?->id),
            ],
            'order_index' => [
                $required,
                'integer',
                'min:0',
                Rule::unique('course_modules')->where(
                    fn ($query) => $query->where('course_id', request('course_id', $link?->course_id))
                )->ignore($link?->id),
            ],
        ];
    }
}
