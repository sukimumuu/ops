<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
{
    $user = $request->user();
    $viewData = [];

    if ($user !== null && $user->hasRole('Seller')) {
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
                ->where('seller_id', $user->id)
                ->with(['property:id,title', 'buyer:id,name'])
                ->latest()
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'property_id', 'buyer_id', 'total_property_amount', 'state', 'created_at']),
        ];
    } elseif ($user !== null && $user->hasRole('Buyer')) {
        $viewData = [
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
            'recentTransactions' => Transaction::query()
                ->where('buyer_id', $user->id)
                ->with(['property:id,title,city,province', 'seller:id,name'])
                ->latest()
                ->orderByDesc('id')
                ->limit(5)
                ->get(['id', 'property_id', 'seller_id', 'total_property_amount', 'booking_fee_amount', 'state', 'created_at']),
        ];
    } else {
        // Fallback untuk Admin / Superadmin / Role lainnya
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

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function detailNotif()
    {
        return view('dashboard.detail-notif');
    }
}
