<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('shows role permissions inside the superadmin system configuration page', function () {
    $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'properties.approve_listing', 'guard_name' => 'web']);
    $superadminRole->givePermissionTo($permission);

    $superadmin = User::factory()->create();
    $superadmin->assignRole($superadminRole);

    $this->actingAs($superadmin)
        ->get(route('superadmin.configuration-system'))
        ->assertOk()
        ->assertSeeText('Sistem & konfigurasi')
        ->assertSeeText('Pengaturan role & permission')
        ->assertSeeText('Superadmin')
        ->assertSeeText('Admin')
        ->assertSeeText('User')
        ->assertSeeText('Akses penuh · dilindungi')
        ->assertSeeText('Setujui listing')
        ->assertSee(route('superadmin.configuration-system.permissions.update', $adminRole), false)
        ->assertViewHas('roles', fn ($roles): bool => $roles->contains('id', $userRole->id));
});

it('updates role permissions without changing the assigned users', function () {
    $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $oldPermission = Permission::firstOrCreate(['name' => 'users.view_all_users', 'guard_name' => 'web']);
    $newPermission = Permission::firstOrCreate(['name' => 'properties.approve_listing', 'guard_name' => 'web']);
    $adminRole->givePermissionTo($oldPermission);

    $adminUser = User::factory()->create();
    $adminUser->assignRole($adminRole);
    $superadmin = User::factory()->create();
    $superadmin->assignRole($superadminRole);

    $this->actingAs($superadmin)
        ->put(route('superadmin.configuration-system.permissions.update', $adminRole), [
            'permissions' => [$newPermission->name],
        ])
        ->assertRedirect(route('superadmin.configuration-system'))
        ->assertSessionHas('status', 'Permission role Admin berhasil diperbarui.');

    expect($adminRole->fresh()->hasPermissionTo($newPermission))->toBeTrue()
        ->and($adminRole->fresh()->hasPermissionTo($oldPermission))->toBeFalse()
        ->and($adminUser->fresh()->hasRole('Admin'))->toBeTrue();
});

it('applies saved role permissions to admin feature access', function () {
    $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $viewUsersPermission = Permission::firstOrCreate(['name' => 'users.view_all_users', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);
    $superadmin = User::factory()->create();
    $superadmin->assignRole($superadminRole);

    $this->actingAs($admin)
        ->get(route('admin.user-management'))
        ->assertForbidden();

    $this->actingAs($superadmin)
        ->put(route('superadmin.configuration-system.permissions.update', $adminRole), [
            'permissions' => [$viewUsersPermission->name],
        ])
        ->assertRedirect(route('superadmin.configuration-system'));

    $this->actingAs($admin)
        ->get(route('admin.user-management'))
        ->assertOk();
});

it('rejects unknown or superadmin-only permissions without changing role access', function () {
    $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $existingPermission = Permission::firstOrCreate(['name' => 'users.view_all_users', 'guard_name' => 'web']);
    $adminRole->givePermissionTo($existingPermission);
    Permission::firstOrCreate(['name' => 'users.manage_roles_permissions', 'guard_name' => 'web']);

    $superadmin = User::factory()->create();
    $superadmin->assignRole($superadminRole);

    $this->actingAs($superadmin)
        ->from(route('superadmin.configuration-system'))
        ->put(route('superadmin.configuration-system.permissions.update', $adminRole), [
            'permissions' => ['users.manage_roles_permissions'],
        ])
        ->assertRedirect(route('superadmin.configuration-system'))
        ->assertSessionHasErrors('permissions.0');

    expect($adminRole->fresh()->hasPermissionTo($existingPermission))->toBeTrue()
        ->and($adminRole->fresh()->hasPermissionTo('users.manage_roles_permissions'))->toBeFalse();
});

it('prevents administrators from viewing or changing role permissions', function () {
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'users.view_all_users', 'guard_name' => 'web']);
    $adminRole->givePermissionTo($permission);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $this->actingAs($admin)
        ->get(route('superadmin.configuration-system'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->put(route('superadmin.configuration-system.permissions.update', $adminRole), [
            'permissions' => [],
        ])
        ->assertForbidden();

    expect($adminRole->fresh()->hasPermissionTo($permission))->toBeTrue();
});

it('prevents changes to the superadmin role permissions', function () {
    $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'users.view_all_users', 'guard_name' => 'web']);
    $superadminRole->givePermissionTo($permission);
    $superadmin = User::factory()->create();
    $superadmin->assignRole($superadminRole);

    $this->actingAs($superadmin)
        ->put(route('superadmin.configuration-system.permissions.update', $superadminRole), [
            'permissions' => [],
        ])
        ->assertForbidden();

    expect($superadminRole->fresh()->hasPermissionTo($permission))->toBeTrue();
});
