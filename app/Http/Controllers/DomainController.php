<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Resources\DomainResource;
use App\Models\Domain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(): JsonResponse
    {
        $domains = Domain::query()
            ->select(['id', 'name', 'description'])
            ->withCount([
                'courses' => fn ($query) => $query->where('is_published', true),
                'categories',
            ])
            ->orderBy('name')
            ->get();

        return DomainResource::collection($domains)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $domain = Domain::create($request->validate($this->rules()));

        return (new DomainResource($domain))->response()->setStatusCode(201);
    }

    public function show(Request $request, Domain $domain): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $user = auth('sanctum')->user();
        $isStaff = $user && $user->hasAnyRole(['admin', 'instructor']);

        $courses = $domain->courses()
            ->select([
                'id', 'title', 'url', 'thumbnail', 'price', 'is_free', 'language',
                'is_published', 'duration_minutes', 'level_id', 'type_id', 'description',
                'schedule', 'domain_id', 'average_rating', 'created_at', 'updated_at',
            ])
            ->with(['level:id,name', 'type:id,name', 'domain:id,name'])
            ->withCount('reviews')
            ->when(! $isStaff, fn ($q) => $q->where('is_published', true))
            ->when(isset($validated['search']), fn ($q) => $q->where('title', 'like', "%{$validated['search']}%"))
            ->orderByDesc('average_rating')
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? 12)
            ->withQueryString();

        return response()->json([
            'domain' => (new DomainResource($domain->loadCount('categories')))->resolve($request),
            'courses' => CourseResource::collection($courses)->response()->getData(true),
        ]);
    }

    public function update(Request $request, Domain $domain): JsonResponse
    {
        $domain->update($request->validate($this->rules(true)));

        return (new DomainResource($domain))->response();
    }

    public function destroy(Domain $domain): JsonResponse
    {
        $domain->delete();

        return response()->json(['message' => 'Domain deleted successfully']);
    }

    private function rules(bool $updating = false): array
    {
        return [
            'name' => ($updating ? 'sometimes' : 'required') . '|string|max:250',
            'description' => 'nullable|string',
        ];
    }
}
