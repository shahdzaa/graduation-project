<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'role' => 'nullable|in:student,instructor,admin',
            'is_active' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $users = User::query()
            ->select(['id', 'name', 'email', 'avatar', 'is_active', 'created_at', 'updated_at'])
            ->with([
                'roles:id,name',
                'studentProfile:id,user_id,phone,github_url,birth_date,country',
                'instructorProfile:id,user_id,bio,specialization,linkedin_url,years_experience,website_url,average_rating',
            ])
            ->withCount(['studentCourses', 'taughtCourses'])
            ->when(isset($validated['search']), function ($query) use ($validated) {
                $search = $validated['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(isset($validated['role']), fn ($q) => $q->role($validated['role']))
            ->when(array_key_exists('is_active', $validated), fn ($q) => $q->where('is_active', $validated['is_active']))
            ->latest()
            ->paginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return UserResource::collection($users)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'email' => 'required|email|max:250|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,instructor,admin',
            'avatar' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $role = $validated['role'];
            unset($validated['role']);
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);
            $user->assignRole($role);

            if ($role === 'student') {
                $user->studentProfile()->create([]);
            } elseif ($role === 'instructor') {
                $user->instructorProfile()->create([]);
            }

            return $user;
        });

        return (new UserResource($this->loadSummary($user)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): JsonResponse
    {
        return (new UserResource($this->loadSummary($user)))->response();
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:250',
            'email' => 'sometimes|email|max:250|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|in:student,instructor,admin',
            'avatar' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $role = $validated['role'] ?? null;
            unset($validated['role']);

            if (! empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            if ($role) {
                $user->syncRoles([$role]);
                if ($role === 'student') {
                    $user->studentProfile()->firstOrCreate([]);
                } elseif ($role === 'instructor') {
                    $user->instructorProfile()->firstOrCreate([]);
                }
            }
        });

        return (new UserResource($this->loadSummary($user->refresh())))->response();
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    private function loadSummary(User $user): User
    {
        return $user
            ->load(['roles:id,name', 'studentProfile', 'instructorProfile'])
            ->loadCount(['studentCourses', 'taughtCourses']);
    }
}
