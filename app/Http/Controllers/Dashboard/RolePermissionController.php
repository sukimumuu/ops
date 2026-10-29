<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    private const PERMISSION_GROUPS = [
        'users' => 'Pengguna',
        'properties' => 'Properti',
        'documents' => 'Dokumen',
        'appointments' => 'Janji temu',
        'transactions' => 'Transaksi & escrow',
        'system' => 'Sistem',
    ];

    private const PERMISSION_LABELS = [
        'users.update_own_profile' => 'Ubah profil sendiri',
        'users.submit_kyc' => 'Ajukan verifikasi identitas',
        'users.verify_kyc' => 'Verifikasi KYC pengguna',
        'users.manage_roles_permissions' => 'Kelola role dan permission',
        'users.view_all_users' => 'Lihat semua pengguna',
        'properties.create' => 'Buat listing',
        'properties.update_own' => 'Ubah listing sendiri',
        'properties.delete_own' => 'Hapus listing sendiri',
        'properties.approve_listing' => 'Setujui listing',
        'properties.reject_listing' => 'Tolak listing',
        'documents.upload_basic' => 'Unggah dokumen dasar',
        'documents.upload_transactional' => 'Unggah dokumen transaksi',
        'documents.view_sensitive_pii' => 'Lihat data pribadi sensitif',
        'documents.verify_status' => 'Verifikasi dokumen',
        'documents.input_bpn_result' => 'Masukkan hasil pengecekan BPN',
        'appointments.request' => 'Ajukan janji temu',
        'appointments.confirm_or_reject' => 'Setujui atau tolak janji temu',
        'appointments.cancel_own' => 'Batalkan janji temu sendiri',
        'appointments.view_all' => 'Lihat semua janji temu',
        'transactions.initiate_dp' => 'Mulai pembayaran uang muka',
        'transactions.view_own_tracker' => 'Lihat transaksi sendiri',
        'transactions.view_all' => 'Lihat semua transaksi',
        'transactions.mark_bpn_cleared' => 'Tandai pengecekan BPN selesai',
        'transactions.schedule_ajb' => 'Atur jadwal AJB',
        'transactions.trigger_escrow_refund' => 'Proses pengembalian dana escrow',
        'transactions.trigger_escrow_release' => 'Proses pencairan dana escrow',
        'transactions.manual_override_state' => 'Ubah status transaksi secara manual',
        'system.view_audit_logs' => 'Lihat audit log',
        'system.view_webhook_logs' => 'Lihat webhook log',
        'system.configure_payment_gateway' => 'Atur payment gateway',
        'system.manage_settings' => 'Kelola pengaturan sistem',
    ];

    public function index(): View
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (Role $role): int => $role->name === 'Superadmin' ? 0 : 1)
            ->values();

        $permissionGroups = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'users.manage_roles_permissions')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => explode('.', $permission->name, 2)[0]);

        return view('dashboard.superadmin.configuration-system', [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
            'groupLabels' => self::PERMISSION_GROUPS,
            'permissionLabels' => self::PERMISSION_LABELS,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        abort_if($role->name === 'Superadmin', 403, 'Role Superadmin dilindungi dan tidak dapat diubah.');

        $editablePermissions = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'users.manage_roles_permissions')
            ->pluck('name')
            ->all();

        $validated = $request->validate([
            'permissions' => ['sometimes', 'array', 'max:100'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::in($editablePermissions)],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('superadmin.configuration-system')
            ->with('status', 'Permission role '.$role->name.' berhasil diperbarui.');
    }
}
