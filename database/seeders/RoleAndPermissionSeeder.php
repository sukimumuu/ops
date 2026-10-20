<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.update_own_profile', 'users.submit_kyc', 'users.verify_kyc', 'users.manage_roles_permissions', 'users.view_all_users',
            'properties.create', 'properties.update_own', 'properties.delete_own', 'properties.approve_listing', 'properties.reject_listing',
            'documents.upload_basic', 'documents.upload_transactional', 'documents.view_sensitive_pii', 'documents.verify_status', 'documents.input_bpn_result',
            'appointments.request', 'appointments.confirm_or_reject', 'appointments.cancel_own', 'appointments.view_all',
            'transactions.initiate_dp', 'transactions.view_own_tracker', 'transactions.view_all', 'transactions.mark_bpn_cleared',
            'transactions.schedule_ajb', 'transactions.trigger_escrow_refund', 'transactions.trigger_escrow_release', 'transactions.manual_override_state',
            'system.view_audit_logs', 'system.view_webhook_logs', 'system.configure_payment_gateway', 'system.manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'users.update_own_profile',
            'users.submit_kyc',
            'properties.create',
            'properties.update_own',
            'properties.delete_own',
            'documents.upload_basic',
            'documents.upload_transactional',
            'appointments.request',
            'appointments.confirm_or_reject',
            'appointments.cancel_own',
            'transactions.initiate_dp',
            'transactions.view_own_tracker',
        ]);

        foreach (['Buyer', 'Seller'] as $legacyRoleName) {
            $legacyRole = Role::query()
                ->where('name', $legacyRoleName)
                ->where('guard_name', 'web')
                ->first();

            if ($legacyRole === null) {
                continue;
            }

            User::role($legacyRoleName)->get()->each(function (User $user) use ($legacyRole, $userRole): void {
                $user->assignRole($userRole);
                $user->removeRole($legacyRole);
            });

            $legacyRole->delete();
        }

        $adminConciergeRole = Role::firstOrCreate(['name' => 'PPAT', 'guard_name' => 'web']);
        $adminConciergeRole->syncPermissions([
            'properties.approve_listing',
            'properties.reject_listing',
            'documents.view_sensitive_pii',
            'documents.verify_status',
            'documents.input_bpn_result',
            'appointments.view_all',
            'appointments.confirm_or_reject',
            'transactions.view_all',
            'transactions.mark_bpn_cleared',
            'transactions.schedule_ajb',
            'transactions.trigger_escrow_refund',
            'transactions.trigger_escrow_release',
            'system.view_audit_logs',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'users.view_all_users',
            'users.verify_kyc',
            'properties.approve_listing',
            'properties.reject_listing',
            'documents.view_sensitive_pii',
            'documents.verify_status',
            'documents.input_bpn_result',
            'appointments.view_all',
            'appointments.confirm_or_reject',
            'transactions.view_all',
            'transactions.mark_bpn_cleared',
            'transactions.schedule_ajb',
            'transactions.trigger_escrow_refund',
            'transactions.trigger_escrow_release',
            'system.view_audit_logs',
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());
    }
}
