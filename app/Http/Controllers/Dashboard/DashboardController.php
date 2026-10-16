<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvatarRequest;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user !== null && $user->hasRole('PPAT')) {
            return redirect()->route('land-deed.index');
        }

        $viewData = [];

        if ($user !== null && $user->hasRole('User')) {
            $viewData = [
                'sellerName' => $user->name,
                'sellerStats' => [
                    'totalProperties' => Property::query()
                        ->where('seller_id', $user->id)
                        ->count(),
                    'publishedProperties' => Property::query()
                        ->where('seller_id', $user->id)
                        ->where('status', 'published')
                        ->count(),
                    'pendingSurveys' => Appointment::query()
                        ->where('seller_id', $user->id)
                        ->where('status', 'requested')
                        ->count(),
                    'activeTransactions' => Transaction::query()
                        ->where('seller_id', $user->id)
                        ->whereIn('state', ['held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'])
                        ->count(),
                ],
                'recentProperties' => Property::query()
                    ->where('seller_id', $user->id)
                    ->select(['id', 'title', 'city', 'province', 'price', 'status', 'created_at'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(),
                'recentSurveys' => Appointment::query()
                    ->where('seller_id', $user->id)
                    ->where('status', 'requested')
                    ->with(['property:id,title', 'buyer:id,name'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'buyer_id', 'scheduled_at', 'created_at']),
                'recentTransactions' => Transaction::query()
                    ->where(function (Builder $query) use ($user): void {
                        $query->where('seller_id', $user->id)
                            ->orWhere('buyer_id', $user->id);
                    })
                    ->with(['property:id,title,city,province', 'buyer:id,name', 'seller:id,name'])
                    ->latest()
                    ->orderByDesc('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'buyer_id', 'seller_id', 'total_property_amount', 'booking_fee_amount', 'state', 'created_at']),
            ];
        }

        if ($user !== null && $user->hasRole('User')) {
            $viewData = array_merge($viewData, [
                'buyerName' => $user->name,
                'buyerStats' => [
                    'totalSurveys' => Appointment::query()
                        ->where('buyer_id', $user->id)
                        ->count(),
                    'upcomingSurveys' => Appointment::query()
                        ->where('buyer_id', $user->id)
                        ->whereIn('status', ['requested', 'confirmed'])
                        ->where('scheduled_at', '>=', now())
                        ->count(),
                    'activeTransactions' => Transaction::query()
                        ->where('buyer_id', $user->id)
                        ->whereIn('state', ['draft', 'held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'])
                        ->count(),
                    'completedTransactions' => Transaction::query()
                        ->where('buyer_id', $user->id)
                        ->where('state', 'completed')
                        ->count(),
                ],
                'upcomingSurveys' => Appointment::query()
                    ->where('buyer_id', $user->id)
                    ->whereIn('status', ['requested', 'confirmed'])
                    ->where('scheduled_at', '>=', now())
                    ->with(['property:id,title,city,province', 'seller:id,name'])
                    ->orderBy('scheduled_at')
                    ->orderBy('id')
                    ->limit(5)
                    ->get(['id', 'property_id', 'seller_id', 'scheduled_at', 'status']),
            ]);
        } else {
            $activeTransactionStates = ['held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'];

            $viewData = [
                'metrics' => [
                    [
                        'label' => 'Pengguna terdaftar',
                        'value' => User::withoutRole('Superadmin')->count(),
                        'description' => 'Akun di luar superadmin',
                        'icon' => 'users',
                    ],
                    [
                        'label' => 'Properti tayang',
                        'value' => Property::where('status', 'published')->count(),
                        'description' => 'Listing yang terlihat publik',
                        'icon' => 'home',
                    ],
                    [
                        'label' => 'Menunggu verifikasi',
                        'value' => Property::where('status', 'pending_verification')->count(),
                        'description' => 'Listing perlu ditinjau',
                        'icon' => 'clipboard',
                    ],
                    [
                        'label' => 'Transaksi berjalan',
                        'value' => Transaction::whereIn('state', $activeTransactionStates)->count(),
                        'description' => 'Dalam proses hingga AJB',
                        'icon' => 'arrows',
                    ],
                ],
                'recentTransactions' => Transaction::query()
                    ->select('id', 'uuid', 'property_id', 'buyer_id', 'seller_id', 'total_property_amount', 'state', 'created_at')
                    ->with(['property:id,title', 'buyer:id,name', 'seller:id,name'])
                    ->latest()
                    ->limit(6)
                    ->get(),
                'pendingProperties' => Property::query()
                    ->select('id', 'title', 'seller_id', 'city', 'status', 'created_at')
                    ->with('seller:id,name')
                    ->where('status', 'pending_verification')
                    ->latest()
                    ->limit(6)
                    ->get(),
            ];
        }

        return view('dashboard.index', $viewData);
    }

    public function profile(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $formattedPhone = $this->formatPhoneNumber($user->phone);
        $maskedPhone = $this->maskPhoneNumber($user->phone);
        $roleDisplayName = $this->formatRoleName($user);
        $identifier = $this->formatUserIdentifier($user);
        $devices = $this->resolveConnectedDevices($request, $user);

        return view('dashboard.profile', [
            'user' => $user,
            'roleDisplayName' => $roleDisplayName,
            'idLabel' => $identifier['label'],
            'formattedId' => $identifier['code'],
            'formattedPhone' => $formattedPhone,
            'maskedPhone' => $maskedPhone,
            'devices' => $devices,
        ]);
    }

    private function formatRoleName(User $user): string
    {
        $role = $user->roles->first()?->name;

        if (! $role) {
            return 'Pengguna';
        }

        return match (strtolower($role)) {
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'ppat' => 'PPAT',
            'user' => 'Pengguna',
            default => ucfirst($role),
        };
    }

    private function formatUserIdentifier(User $user): array
    {
        $role = strtolower($user->roles->first()?->name ?? '');
        $paddedId = str_pad((string) $user->id, 3, '0', STR_PAD_LEFT);

        return match ($role) {
            'superadmin', 'admin' => ['label' => 'ID Admin', 'code' => 'ADM-'.$paddedId],
            'ppat' => ['label' => 'ID PPAT', 'code' => 'PPT-'.$paddedId],
            default => ['label' => 'ID Akun', 'code' => 'USR-'.$paddedId],
        };
    }

    private function formatPhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '-';
        }

        $clean = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($clean, '62')) {
            $rest = substr($clean, 2);
            if (strlen($rest) >= 7) {
                return '+62 '.substr($rest, 0, 3).'-'.substr($rest, 3, 4).'-'.substr($rest, 7);
            }

            return '+62 '.$rest;
        }

        if (str_starts_with($clean, '0')) {
            return $clean;
        }

        return '+'.$clean;
    }

    private function maskPhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '-';
        }

        $clean = preg_replace('/\D/', '', (string) $phone);
        if (strlen($clean) >= 8) {
            $last4 = substr($clean, -4);
            $prefix = str_starts_with($clean, '62') ? '+62 '.substr($clean, 2, 3) : substr($clean, 0, 4);

            return $prefix.'-••••-'.$last4;
        }

        return '+62 ••••-••••';
    }

    private function resolveConnectedDevices(Request $request, User $user): array
    {
        $devices = [];
        $currentSessionId = $request->hasSession() ? $request->session()->getId() : null;
        $currentIp = $request->ip() ?: '127.0.0.1';
        $currentUserAgent = (string) ($request->userAgent() ?: 'Unknown Device');

        $dbSessions = collect();
        if (config('session.driver') === 'database') {
            try {
                $dbSessions = DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->orderByDesc('last_activity')
                    ->get();
            } catch (\Throwable $e) {
                // Ignore fallback to current request device
            }
        }

        if ($dbSessions->isNotEmpty()) {
            $hasCurrent = false;
            foreach ($dbSessions as $session) {
                $isCurrent = $currentSessionId !== null && $session->id === $currentSessionId;
                if ($isCurrent) {
                    $hasCurrent = true;
                }
                $parsed = $this->parseUserAgent((string) $session->user_agent);
                $lastActive = Carbon::createFromTimestamp($session->last_activity)->locale('id')->diffForHumans();

                $devices[] = [
                    'name' => $parsed['browser'].' di '.$parsed['platform'],
                    'is_mobile' => $parsed['is_mobile'],
                    'is_current' => $isCurrent,
                    'ip_address' => $session->ip_address ?: '127.0.0.1',
                    'last_active' => $isCurrent ? 'Aktif sekarang' : $lastActive,
                ];
            }

            if (! $hasCurrent) {
                $currentParsed = $this->parseUserAgent($currentUserAgent);
                array_unshift($devices, [
                    'name' => $currentParsed['browser'].' di '.$currentParsed['platform'],
                    'is_mobile' => $currentParsed['is_mobile'],
                    'is_current' => true,
                    'ip_address' => $currentIp,
                    'last_active' => 'Aktif sekarang',
                ]);
            }
        } else {
            $currentParsed = $this->parseUserAgent($currentUserAgent);
            $devices[] = [
                'name' => $currentParsed['browser'].' di '.$currentParsed['platform'],
                'is_mobile' => $currentParsed['is_mobile'],
                'is_current' => true,
                'ip_address' => $currentIp,
                'last_active' => 'Aktif sekarang',
            ];
        }

        return $devices;
    }

    private function parseUserAgent(string $userAgent): array
    {
        $isMobile = (bool) preg_match('/(iPhone|Android|Mobile|iPad)/i', $userAgent);

        $platform = 'Perangkat Lain';
        if (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows PC';
        } elseif (stripos($userAgent, 'Macintosh') !== false || stripos($userAgent, 'Mac OS') !== false) {
            $platform = 'Mac';
        } elseif (stripos($userAgent, 'iPhone') !== false) {
            $platform = 'iPhone';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
        }

        $browser = 'Peramban Web';
        if (stripos($userAgent, 'Edge') !== false || stripos($userAgent, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'is_mobile' => $isMobile,
        ];
    }

    /**
     * Upload and replace the authenticated user's avatar.
     *
     * The old avatar file is deleted from the public disk before the new
     * one is stored, preventing orphaned files from accumulating.
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $user = $request->user();

        // Delete the previous avatar if one exists
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store the new avatar under avatars/{userId}/filename
        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');

        $user->avatar_path = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'avatar_url' => Storage::disk('public')->url($path),
            'message' => 'Foto profil berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the authenticated user's avatar, reverting to the generated fallback.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->avatar_path) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada foto profil yang perlu dihapus.',
            ], 422);
        }

        if (Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
        $user->save();

        $fallbackUrl = 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=ea580c&color=fff&size=256&bold=true';

        return response()->json([
            'success' => true,
            'avatar_url' => $fallbackUrl,
            'message' => 'Foto profil berhasil dihapus.',
        ]);
    }

    public function detailNotif()
    {
        return view('dashboard.detail-notif');
    }
}
