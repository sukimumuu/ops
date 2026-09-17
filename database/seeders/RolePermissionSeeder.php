<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // make sure to clear the cache before seeding
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'view public list',
            'ai semantic search',
            'create listing',
            'upload legal documents',
            'verify legal documents',
            'booking survey',
            'transaction/escrow initiation',
            'approve transaction/escrow',
            'manage user roles and permissions',
            'view audit transaction history',
        ];
        // store permissions in the database
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles
        $roles = [
            'superadmin',
            'verifier',
            'seller',
            'buyer',
        ];
        // store roles in the database
        foreach ($roles as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }

        // assign permissions to roles buyer
        $buyer = Role::where('name', 'buyer')->first();
        $buyer->givePermissionTo([
            'view public list',
            'ai semantic search',
            'booking survey',
            'transaction/escrow initiation',
        ]);
        // assign permissions to roles seller
        $seller = Role::where('name', 'seller')->first();
        $seller->givePermissionTo([
            'view public list',
            'ai semantic search',
            'create listing',
            'upload legal documents',
            'booking survey',
            'transaction/escrow initiation',
        ]);
        // assign permissions to roles verifier
        $verifier = Role::where('name', 'verifier')->first();
        $verifier->givePermissionTo([
            'view public list',
            'ai semantic search',
            'upload legal documents',
            'verify legal documents',
            'view audit transaction history',
        ]);
        // create superadmin user and assign all permissions
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@mihom.id',
            'password' => Hash::make('password'),
        ]);
        $superadmin->assignRole('superadmin');
        $superadmin->givePermissionTo(Permission::all());
    }
}
