<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class SuperadminController extends Controller
{
    private const USER_ROLES = [
        'admin' => 'admin',
        'ppat' => 'PPAT',
        'buyer' => 'Buyer',
        'seller' => 'Seller',
    ];

    public function userManagement(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'exists:roles,name', 'not_in:Superadmin'],
        ]);
        $search = trim($filters['search'] ?? '');
        $role = $filters['role'] ?? null;
        $users = User::withoutRole('Superadmin')
            ->select('id', 'name', 'email', 'phone')
            ->with('roles:id,name')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role, fn (Builder $query): Builder => $query->whereHas('roles', fn (Builder $roles): Builder => $roles->where('name', $role)))
            ->paginate(10)
            ->withQueryString();
        $roles = Role::query()->where('name', '!=', 'Superadmin')->orderBy('name')->pluck('name');

        return view('dashboard.superadmin.user-management', compact('users', 'roles'));
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

        $provinceCode = preg_quote((string) $request->input('province_code'), '/');
        $cityCode = preg_quote((string) $request->input('city_code'), '/');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(array_keys(self::USER_ROLES))],
            'province' => ['required_if:role,ppat', 'nullable', 'string', 'max:100'],
            'city' => ['required_if:role,ppat', 'nullable', 'string', 'max:100'],
            'district' => ['required_if:role,ppat', 'nullable', 'string', 'max:100'],
            'province_code' => ['required_if:role,ppat', 'nullable', 'string', 'regex:/^\\d{2}$/'],
            'city_code' => ['required_if:role,ppat', 'nullable', 'string', 'regex:/^'.$provinceCode.'\\.\\d{2}$/'],
            'district_code' => ['required_if:role,ppat', 'nullable', 'string', 'regex:/^'.$cityCode.'\\.\\d{2}$/'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'province' => $validated['role'] === 'ppat' ? $validated['province'] : null,
            'city' => $validated['role'] === 'ppat' ? $validated['city'] : null,
            'district' => $validated['role'] === 'ppat' ? $validated['district'] : null,
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

    public function masterDataProperty(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'property_type' => ['nullable', 'in:bangunan,tanah'],
            'category' => ['nullable', 'in:sale,rent'],
        ]);
        $search = trim($filters['search'] ?? '');
        $pendingProperties = Property::query()
            ->with(['seller:id,name', 'photos', 'certificate'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhereHas('seller', fn (Builder $seller): Builder => $seller->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['property_type'] ?? null, fn (Builder $query, string $type): Builder => $query->where('property_type', $type))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category): Builder => $query->where('category', $category))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $ppatUsers = User::role('PPAT')
            ->select('id', 'name', 'phone', 'province', 'city', 'district')
            ->orderBy('name')
            ->get();

        return view('dashboard.superadmin.master-data-property', compact('pendingProperties', 'ppatUsers'));
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
