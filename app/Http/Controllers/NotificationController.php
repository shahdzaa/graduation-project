<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return NotificationResource::collection(Notification::with('user')->get())->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:250',
            'body' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $notification = Notification::create($validated);
        return (new NotificationResource($notification->load('user')))->response()->setStatusCode(201);
    }

    public function show(Notification $notification): JsonResponse
    {
        return (new NotificationResource($notification->load('user')))->response();
    }

    public function update(Request $request, Notification $notification): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:250',
            'body' => 'nullable|string',
            'data' => 'nullable|array',
            'read_at' => 'nullable|date',
        ]);

        $notification->update($validated);
        return (new NotificationResource($notification->load('user')))->response();
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
