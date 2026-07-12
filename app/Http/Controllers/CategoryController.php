<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = Category::with(['parent', 'children', 'domain', 'courses'])->get();
        return CategoryResource::collection($categories)->response();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:250|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'domain_id' => 'nullable|exists:domains,id',
            'icon' => 'nullable|string',
            'order_index' => 'integer|default:0',
        ]);

        $category = Category::create($validated);
        return (new CategoryResource(category->load(['parent', 'domain'])))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return (new CategoryResource($category->load(['parent', 'children', 'domain', 'courses'])))->response();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:250|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'domain_id' => 'nullable|exists:domains,id',
            'icon' => 'nullable|string',
            'order_index' => 'integer',
        ]);

        $category->update($validated);
        return (new CategoryResource($category->load(['parent', 'domain'])))->response();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
