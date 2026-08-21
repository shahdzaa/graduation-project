<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unread_only' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $notifications = Notification::query()
            ->when(! $request->user()->hasRole('admin'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($validated['unread_only'] ?? false, fn ($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate($validated['per_page'] ?? 20)
            ->withQueryString();

        return NotificationResource::collection($notifications)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $notification = Notification::create($request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:250',
            'body' => 'nullable|string',
            'data' => 'nullable|array',
            'read_at' => 'nullable|date',
        ]));

        return (new NotificationResource($notification))->response()->setStatusCode(201);
    }

    public function show(Request $request, Notification $notification): JsonResponse
    {
        $this->ensureCanAccess($request, $notification);

        return (new NotificationResource($notification))->response();
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        $this->ensureCanAccess($request, $notification);

        if ($request->user()->hasRole('admin')) {
            $notification->update($request->validate([
                'user_id' => 'sometimes|exists:users,id',
                'type' => 'sometimes|string|max:100',
                'title' => 'sometimes|string|max:250',
                'body' => 'nullable|string',
                'data' => 'nullable|array',
                'read_at' => 'nullable|date',
            ]));
        } else {
            $notification->update(['read_at' => now()]);
        }

        return (new NotificationResource($notification))->response();
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

    private function ensureCanAccess(Request $request, Notification $notification): void
    {
        abort_unless(
            $request->user()->hasRole('admin') || $notification->user_id === $request->user()->id,
            403
        );
    }
}
