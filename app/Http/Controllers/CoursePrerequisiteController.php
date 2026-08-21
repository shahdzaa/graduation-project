<?php

namespace App\Http\Controllers;

use App\Http\Resources\CoursePrerequisiteLinkResource;
use App\Models\CoursePrerequisite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoursePrerequisiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $links = CoursePrerequisite::query()
            ->with(['course:id,title', 'prerequisiteCourse:id,title,level_id', 'prerequisiteCourse.level:id,name'])
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', $request->integer('course_id')))
            ->orderBy('course_id')
            ->paginate(min(max($request->integer('per_page', 50), 1), 100));

        return CoursePrerequisiteLinkResource::collection($links)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $link = CoursePrerequisite::create($request->validate($this->rules()));

        return (new CoursePrerequisiteLinkResource($this->loadRelations($link)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        return (new CoursePrerequisiteLinkResource($this->loadRelations($coursePrerequisite)))->response();
    }

    public function update(Request $request, CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        $coursePrerequisite->update($request->validate($this->rules($coursePrerequisite)));

        return (new CoursePrerequisiteLinkResource($this->loadRelations($coursePrerequisite)))->response();
    }

    public function destroy(CoursePrerequisite $coursePrerequisite): JsonResponse
    {
        $coursePrerequisite->delete();

        return response()->json(['message' => 'Course prerequisite deleted successfully']);
    }

    private function loadRelations(CoursePrerequisite $link): CoursePrerequisite
    {
        return $link->load(['course:id,title', 'prerequisiteCourse:id,title,level_id', 'prerequisiteCourse.level:id,name']);
    }

    private function rules(?CoursePrerequisite $link = null): array
    {
        return [
            'course_id' => ($link ? 'sometimes' : 'required') . '|exists:courses,id',
            'prerequisite_course_id' => [
                $link ? 'sometimes' : 'required',
                'different:course_id',
                'exists:courses,id',
                Rule::unique('course_prerequisites')->where(
                    fn ($q) => $q->where('course_id', request('course_id', $link?->course_id))
                )->ignore($link?->id),
            ],
        ];
    }
}
