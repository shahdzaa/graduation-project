<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentSkillMatrixResource;
use App\Models\StudentSkillMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentSkillMatrixController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $userId = $request->user()->hasRole('admin')
            ? ($validated['user_id'] ?? null)
            : $request->user()->id;

        $matrices = StudentSkillMatrix::query()
            ->select(['user_id', 'skill_id', 'current_score', 'last_updated'])
            ->with(['user:id,name,email,avatar,is_active', 'user.roles:id,name', 'skill:id,name'])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('current_score')
            ->paginate($validated['per_page'] ?? 50)
            ->withQueryString();

        return StudentSkillMatrixResource::collection($matrices)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'skill_id' => 'required|exists:skills,id',
            'current_score' => 'required|numeric|min:0|max:100',
        ]);

        $userId = $request->user()->hasRole('admin')
            ? ($validated['user_id'] ?? $request->user()->id)
            : $request->user()->id;

        DB::table('student_skill_matrices')->updateOrInsert(
            ['user_id' => $userId, 'skill_id' => $validated['skill_id']],
            ['current_score' => $validated['current_score'], 'last_updated' => now()]
        );

        return (new StudentSkillMatrixResource($this->findMatrix($userId, $validated['skill_id'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $skill): JsonResponse
    {
        return (new StudentSkillMatrixResource($this->findMatrix($request->user()->id, $skill)))->response();
    }

    public function update(Request $request, int $user, int $skill): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin') || $request->user()->id === $user, 403);

        $validated = $request->validate([
            'current_score' => 'required|numeric|min:0|max:100',
        ]);

        $updated = DB::table('student_skill_matrices')
            ->where('user_id', $user)
            ->where('skill_id', $skill)
            ->update(['current_score' => $validated['current_score'], 'last_updated' => now()]);

        abort_if($updated === 0 && ! DB::table('student_skill_matrices')->where(['user_id' => $user, 'skill_id' => $skill])->exists(), 404);

        return (new StudentSkillMatrixResource($this->findMatrix($user, $skill)))->response();
    }

    public function destroy(Request $request, int $user, int $skill): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin') || $request->user()->id === $user, 403);

        $deleted = DB::table('student_skill_matrices')
            ->where('user_id', $user)
            ->where('skill_id', $skill)
            ->delete();

        abort_if($deleted === 0, 404);

        return response()->json(['message' => 'Student skill matrix deleted successfully']);
    }

    private function findMatrix(int $userId, int $skillId): StudentSkillMatrix
    {
        return StudentSkillMatrix::query()
            ->with(['user:id,name,email,avatar,is_active', 'user.roles:id,name', 'skill:id,name'])
            ->where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->firstOrFail();
    }
}
