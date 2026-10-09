<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $activeTransactionStates = ['held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'];

        $metrics = [
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
        ];

        $recentTransactions = Transaction::query()
            ->select('id', 'uuid', 'property_id', 'buyer_id', 'seller_id', 'total_property_amount', 'state', 'created_at')
            ->with(['property:id,title', 'buyer:id,name', 'seller:id,name'])
            ->latest()
            ->limit(6)
            ->get();

        $pendingProperties = Property::query()
            ->select('id', 'title', 'seller_id', 'city', 'status', 'created_at')
            ->with('seller:id,name')
            ->where('status', 'pending_verification')
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard.index', compact('metrics', 'recentTransactions', 'pendingProperties'));
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
