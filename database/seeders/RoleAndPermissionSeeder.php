<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            'users.update_own_profile', 'users.submit_kyc', 'users.verify_kyc', 'users.manage_roles_permissions', 'users.view_all_users',
            'properties.create', 'properties.update_own', 'properties.delete_own', 'properties.approve_listing', 'properties.reject_listing',
            'documents.upload_basic', 'documents.upload_transactional', 'documents.view_sensitive_pii', 'documents.verify_status', 'documents.input_bpn_result',
            'appointments.request', 'appointments.confirm_or_reject', 'appointments.cancel_own', 'appointments.view_all',
            'transactions.initiate_dp', 'transactions.view_own_tracker', 'transactions.view_all', 'transactions.mark_bpn_cleared', 
            'transactions.schedule_ajb', 'transactions.trigger_escrow_refund', 'transactions.trigger_escrow_release', 'transactions.manual_override_state',
            'system.view_audit_logs', 'system.view_webhook_logs', 'system.configure_payment_gateway', 'system.manage_settings'
        ];

        foreach ($permissions as$permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buyer Role
        $buyerRole = Role::firstOrCreate(['name' => 'Buyer']);$buyerRole->givePermissionTo([
            'users.update_own_profile',
            'users.submit_kyc',
            'appointments.request',
            'appointments.cancel_own',
            'transactions.initiate_dp',
            'transactions.view_own_tracker',
            'documents.upload_transactional'
        ]);

        // Seller Role
        $sellerRole = Role::firstOrCreate(['name' => 'Seller']);$sellerRole->givePermissionTo([
            'users.update_own_profile',
            'users.submit_kyc',
            'properties.create',
            'properties.update_own',
            'properties.delete_own',
            'documents.upload_basic',
            'appointments.confirm_or_reject',
            'appointments.cancel_own',
            'transactions.view_own_tracker'
        ]);

        // Admin Concierge Role
        $adminConciergeRole = Role::firstOrCreate(['name' => 'Admin Concierge']);$adminConciergeRole->givePermissionTo([
            'users.view_all_users',
            'users.verify_kyc',
            'properties.approve_listing',
            'properties.reject_listing',
            'documents.view_sensitive_pii',
            'documents.verify_status',
            'documents.input_bpn_result',
            'appointments.view_all',
            'transactions.view_all',
            'transactions.mark_bpn_cleared',
            'transactions.schedule_ajb',
            'transactions.trigger_escrow_refund',
            'transactions.trigger_escrow_release',
            'system.view_audit_logs'
        ]);

        // Super Admin Role (Mendapatkan semua permission)
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);$superAdminRole->givePermissionTo(Permission::all());
    }
}
