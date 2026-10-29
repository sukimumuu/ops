<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
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

    public function storeSubscription(Request $request): JsonResponse
    {
        abort_unless(
            config('webpush.vapid.public_key')
                && config('webpush.vapid.private_key'),
            503,
            'Notifikasi browser belum dikonfigurasi.',
        );

        $validated = $request->validate([
            'endpoint' => [
                'required',
                'string',
                'max:4096',
                'url',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || ! $this->isAllowedPushEndpoint($value)) {
                        $fail('Endpoint push browser tidak valid.');
                    }
                },
            ],
            'keys' => ['required', 'array:p256dh,auth'],
            'keys.p256dh' => ['required', 'string', 'size:87', 'regex:/\A[A-Za-z0-9_-]+\z/'],
            'keys.auth' => ['required', 'string', 'size:22', 'regex:/\A[A-Za-z0-9_-]+\z/'],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $user->webPushSubscriptions()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $validated['endpoint'])],
            [
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
            ],
        );

        return response()->json(['subscribed' => true], 201);
    }

    public function destroySubscription(Request $request, string $endpointHash): JsonResponse
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $endpointHash) === 1, 404);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $user->webPushSubscriptions()
            ->where('endpoint_hash', $endpointHash)
            ->delete();

        return response()->json(['subscribed' => false]);
    }

    private function isAllowedPushEndpoint(string $endpoint): bool
    {
        $parts = parse_url($endpoint);

        if (
            $parts === false
            || ($parts['scheme'] ?? null) !== 'https'
            || ! isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)
        ) {
            return false;
        }

        $host = strtolower($parts['host']);
        $allowedDomains = [
            'fcm.googleapis.com',
            'android.googleapis.com',
            'push.services.mozilla.com',
            'updates.push.services.mozilla.com',
            'web.push.apple.com',
            'notify.windows.com',
            'push.services.opera.com',
        ];

        foreach ($allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }
}
