<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Notification::with('user')->get());
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
        return response()->json($notification->load('user'), 201);
    }

    public function show(Notification $notification): JsonResponse
    {
        return response()->json($notification->load('user'));
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
        return response()->json($notification->load('user'));
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}
