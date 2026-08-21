<?php

namespace App\Http\Controllers;

use App\Http\Resources\ModuleResource;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'nullable|integer|exists:courses,id',
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $modules = Module::query()
            ->select(['id', 'name', 'description', 'duration_minutes', 'created_at', 'updated_at'])
            ->withCount(['courses', 'syllabi'])
            ->when(isset($validated['course_id']), function ($query) use ($validated) {
                $query->whereHas('courses', fn ($q) => $q->where('courses.id', $validated['course_id']));
            })
            ->when(isset($validated['search']), fn ($q) => $q->where('name', 'like', "%{$validated['search']}%"))
            ->orderBy('name')
            ->paginate($validated['per_page'] ?? 30)
            ->withQueryString();

        return ModuleResource::collection($modules)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $module = Module::create($request->validate($this->rules()));

        return (new ModuleResource($module))->response()->setStatusCode(201);
    }

    public function show(Module $module): JsonResponse
    {
        $module->load([
            'syllabi:id,module_id,name,order_index,type_id,category_id,duration_minutes',
            'syllabi.type:id,name',
            'syllabi.category:id,name,slug,domain_id',
        ])->loadCount(['courses', 'syllabi']);

        return (new ModuleResource($module))->response();
    }

    public function update(Request $request, Module $module): JsonResponse
    {
        $module->update($request->validate($this->rules(true)));

        return (new ModuleResource($module->loadCount(['courses', 'syllabi'])))->response();
    }

    public function destroy(Module $module): JsonResponse
    {
        $module->delete();

        return response()->json(['message' => 'Module deleted successfully']);
    }

    private function rules(bool $updating = false): array
    {
        return [
            'name' => ($updating ? 'sometimes' : 'required') . '|string|max:250',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
        ];
    }
}
