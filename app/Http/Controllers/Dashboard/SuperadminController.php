<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuperadminController extends Controller
{
    private const USER_ROLES = [
        'admin' => 'admin',
        'ppat' => 'PPAT',
        'user' => 'user',
    ];

    public function index(): View
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

        return view('dashboard.superadmin.index', compact('metrics', 'recentTransactions', 'pendingProperties'));
    }

    public function userManagement(): View
    {
        $users = User::withoutRole('Superadmin')->select('id', 'name', 'email', 'phone')->paginate(10);

        return view('dashboard.superadmin.user-management', compact('users'));
    }

    public function createUser(): View
    {
        return view('dashboard.superadmin.create-user');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        if ($request->filled('phone')) {
            $normalizedPhone = new User;
            $normalizedPhone->phone = (string) $request->input('phone');
            $request->merge(['phone' => $normalizedPhone->phone]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys(self::USER_ROLES))],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);
        $user->assignRole(self::USER_ROLES[$validated['role']]);

        return redirect()
            ->route('superadmin.user-management')
            ->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function transactionAndEscrow()
    {
        return view('dashboard.superadmin.transaction-and-escrow');
    }

    public function masterDataProperty()
    {
        return view('dashboard.superadmin.master-data-property');
    }

    public function configurationSystem()
    {
        return view('dashboard.superadmin.configuration-system');
    }

    public function auditLog()
    {
        return view('dashboard.superadmin.audit-log');
    }
}
