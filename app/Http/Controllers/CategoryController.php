<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\SyllabusResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'domain_id', 'icon', 'order_index', 'created_at', 'updated_at'])
            ->with([
                'domain:id,name',
                'parent:id,name,slug,parent_id,domain_id,icon,order_index',
                'children:id,name,slug,parent_id,domain_id,icon,order_index',
            ])
            ->withCount('syllabi')
            ->orderBy('domain_id')
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::create($request->validate($this->rules()));

        return (new CategoryResource($this->loadSummary($category)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): JsonResponse
    {
        return (new CategoryResource($this->loadSummary($category)))->response();
    }

    public function syllabi(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'module_id' => 'nullable|integer|exists:modules,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $syllabi = $category->syllabi()
            ->select(['id', 'module_id', 'name', 'order_index', 'type_id', 'category_id', 'duration_minutes'])
            ->with(['module:id,name', 'type:id,name'])
            ->when(isset($validated['module_id']), fn ($q) => $q->where('module_id', $validated['module_id']))
            ->when(isset($validated['search']), fn ($q) => $q->where('name', 'like', "%{$validated['search']}%"))
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return SyllabusResource::collection($syllabi)->response();
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $category->update($request->validate($this->rules($category)));

        return (new CategoryResource($this->loadSummary($category)))->response();
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    private function loadSummary(Category $category): Category
    {
        return $category
            ->load(['domain:id,name', 'parent:id,name,slug,parent_id,domain_id,icon,order_index', 'children'])
            ->loadCount('syllabi');
    }

    private function rules(?Category $category = null): array
    {
        $required = $category ? 'sometimes' : 'required';
        return [
            'name' => "{$required}|string|max:250",
            'slug' => [
                $required,
                'string',
                'max:250',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                Rule::notIn(array_filter([$category?->id])),
            ],
            'domain_id' => 'nullable|exists:domains,id',
            'icon' => 'nullable|string|max:250',
            'order_index' => 'nullable|integer|min:0',
        ];
    }
}
