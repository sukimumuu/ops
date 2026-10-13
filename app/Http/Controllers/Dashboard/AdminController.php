<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
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

        return view('dashboard.admin.user-management', compact('users', 'roles'));
    }

    public function transactionAndEscrow()
    {
        return view('dashboard.admin.transaction-and-escrow');
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
            ->with(['seller:id,name', 'assignedPpat:id,name,email,phone,province,city,district', 'photos', 'certificate'])
            ->where('status', 'pending_verification')
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

        return view('dashboard.admin.master-data-property', compact('pendingProperties'));
    }

    public function auditLog()
    {
        return view('dashboard.admin.audit-log');
    }
}
