<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return view('dashboard.notifications', compact('notifications'));
    }

    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->unreadNotifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->data['title'],
                'message' => $notification->data['message'],
                'url' => $notification->data['url'],
                'created_at' => $notification->created_at->toIso8601String(),
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $user = $request->user();
        $user->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
