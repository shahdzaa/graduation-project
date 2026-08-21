<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecommendationLogResource;
use App\Models\RecommendationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = RecommendationLog::query()
            ->select(['id', 'user_id', 'attempt_id', 'recommended_course_id', 'recommendation_date', 'created_at', 'updated_at'])
            ->with([
                'user:id,name,email,avatar,is_active',
                'user.roles:id,name',
                'attempt:id,user_id,category,total_score,status,start_time,end_time',
                'recommendedCourse:id,title,thumbnail,domain_id,level_id,type_id,average_rating',
                'recommendedCourse.domain:id,name',
                'recommendedCourse.level:id,name',
                'recommendedCourse.type:id,name',
            ])
            ->when(! $request->user()->hasRole('admin'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('recommendation_date')
            ->paginate(min(max((int) $request->input('per_page', 20), 1), 100));

        return RecommendationLogResource::collection($logs)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'attempt_id' => 'required|exists:placement_attempts,id',
            'recommended_course_id' => 'required|exists:courses,id',
            'recommendation_date' => 'nullable|date',
        ]);

        $userId = $request->user()->hasRole('admin')
            ? ($validated['user_id'] ?? $request->user()->id)
            : $request->user()->id;

        abort_unless(
            \App\Models\PlacementAttempt::where('id', $validated['attempt_id'])
                ->where('user_id', $userId)
                ->exists(),
            422,
            'محاولة الاختبار لا تخص هذا المستخدم.'
        );

        $log = RecommendationLog::create([
            'user_id' => $userId,
            'attempt_id' => $validated['attempt_id'],
            'recommended_course_id' => $validated['recommended_course_id'],
            'recommendation_date' => $validated['recommendation_date'] ?? now(),
        ]);

        return (new RecommendationLogResource($this->loadRelations($log)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, RecommendationLog $recommendationLog): JsonResponse
    {
        $this->ensureCanAccess($request, $recommendationLog);

        return (new RecommendationLogResource($this->loadRelations($recommendationLog)))->response();
    }

    public function update(Request $request, RecommendationLog $recommendationLog): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $recommendationLog->update($request->validate([
            'attempt_id' => 'sometimes|exists:placement_attempts,id',
            'recommended_course_id' => 'sometimes|exists:courses,id',
            'recommendation_date' => 'sometimes|date',
        ]));

        return (new RecommendationLogResource($this->loadRelations($recommendationLog)))->response();
    }

    public function destroy(RecommendationLog $recommendationLog): JsonResponse
    {
        $recommendationLog->delete();

        return response()->json(['message' => 'Recommendation log deleted successfully']);
    }

    private function loadRelations(RecommendationLog $log): RecommendationLog
    {
        return $log->load([
            'user:id,name,email,avatar,is_active',
            'user.roles:id,name',
            'attempt:id,user_id,category,total_score,status,start_time,end_time',
            'recommendedCourse:id,title,thumbnail,domain_id,level_id,type_id,average_rating',
            'recommendedCourse.domain:id,name',
            'recommendedCourse.level:id,name',
            'recommendedCourse.type:id,name',
        ]);
    }

    private function ensureCanAccess(Request $request, RecommendationLog $log): void
    {
        abort_unless(
            $request->user()->hasRole('admin') || $log->user_id === $request->user()->id,
            403
        );
    }
}
