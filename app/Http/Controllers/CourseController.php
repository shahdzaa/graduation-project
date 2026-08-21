<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'domain_id' => 'nullable|integer|exists:domains,id',
            'level_id' => 'nullable|integer|exists:course_levels,id',
            'type_id' => 'nullable|integer|exists:course_types,id',
            'language' => 'nullable|string|max:10',
            'is_free' => 'nullable|boolean',
            'search' => 'nullable|string|max:200',
            'sort' => 'nullable|in:trending,newest,title,duration',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $user = auth('sanctum')->user();
        $isStaff = $user && $user->hasAnyRole(['admin', 'instructor']);

        $query = Course::query()
            ->select([
                'id', 'title', 'url', 'thumbnail', 'price', 'is_free',
                'language', 'is_published', 'duration_minutes', 'level_id',
                'type_id', 'description', 'schedule', 'domain_id',
                'average_rating', 'created_at', 'updated_at',
            ])
            ->with([
                'level:id,name',
                'type:id,name',
                'domain:id,name',
            ])
            ->withCount(['reviews', 'studentCourses', 'modules']);

        if (! $isStaff) {
            $query->where('is_published', true);
        }

        $query
            ->when(isset($filters['domain_id']), fn ($q) => $q->where('domain_id', $filters['domain_id']))
            ->when(isset($filters['level_id']), fn ($q) => $q->where('level_id', $filters['level_id']))
            ->when(isset($filters['type_id']), fn ($q) => $q->where('type_id', $filters['type_id']))
            ->when(isset($filters['language']), fn ($q) => $q->where('language', $filters['language']))
            ->when(array_key_exists('is_free', $filters), fn ($q) => $q->where('is_free', $filters['is_free']))
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            });

        match ($filters['sort'] ?? 'trending') {
            'newest' => $query->latest('created_at'),
            'title' => $query->orderBy('title'),
            'duration' => $query->orderBy('duration_minutes'),
            default => $query->orderByDesc('average_rating'),
        };

        $courses = $query
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 12)
            ->withQueryString();

        return CourseResource::collection($courses)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $course = Course::create($validated);

        return (new CourseResource($this->loadDetails($course)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Course $course): JsonResponse
    {
        return (new CourseResource($this->loadDetails($course)))->response();
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);
        $course->update($request->validate($this->rules($course)));

        return (new CourseResource($this->loadDetails($course)))->response();
    }

    public function destroy(Course $course): JsonResponse
    {
        $this->authorize('delete', $course);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    private function loadDetails(Course $course): Course
    {
        $course->load([
            'level:id,name',
            'type:id,name',
            'domain:id,name,description',
            'modules' => fn ($query) => $query
                ->select('modules.id', 'name', 'description', 'duration_minutes')
                ->with([
                    'syllabi:id,module_id,name,order_index,type_id,category_id,duration_minutes',
                    'syllabi.type:id,name',
                    'syllabi.category:id,name,slug,domain_id',
                ]),
            'skills:id,name',
            'organizations:id,name',
            'instructors:id,name,email,avatar,is_active',
            'instructors.roles:id,name',
            'instructors.instructorProfile:id,user_id,bio,specialization,linkedin_url,years_experience,website_url,average_rating',
            'prerequisites:id,title,level_id',
            'prerequisites.level:id,name',
            'learningOutcomes:id,course_id,content,sort_order',
        ])->loadCount(['studentCourses', 'reviews']);

        return $course;
    }

    private function rules(?Course $course = null): array
    {
        $required = $course ? 'sometimes' : 'required';

        return [
            'title' => "{$required}|string|max:250",
            'url' => "{$required}|string|max:1000",
            'duration_minutes' => 'nullable|integer|min:0',
            'level_id' => 'nullable|exists:course_levels,id',
            'type_id' => 'nullable|exists:course_types,id',
            'domain_id' => "{$required}|exists:domains,id",
            'thumbnail' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_free' => 'sometimes|boolean',
            'language' => 'nullable|string|max:10',
            'is_published' => 'sometimes|boolean',
            'description' => 'nullable|string',
            'schedule' => 'nullable|string|max:255',
            'average_rating' => 'nullable|numeric|min:0|max:5',
        ];
    }
}
