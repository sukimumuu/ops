<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('redirects unauthenticated guests to login when opening profile page', function () {
    $this->get(route('profile'))
        ->assertRedirect(route('login'));
});

it('dynamically displays the profile data of the currently logged-in user', function () {
    $sellerRole = Role::create(['name' => 'Seller', 'guard_name' => 'web']);

    $user = User::factory()->create([
        'name' => 'Budi Pratama',
        'username' => 'budipratama',
        'email' => 'budi.pratama@example.com',
        'phone' => '081298765432',
        'kyc_status' => 'verified',
        'email_verified_at' => now(),
    ]);
    $user->assignRole($sellerRole);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertOk()
        ->assertSee('Budi Pratama')
        ->assertSee('@budipratama')
        ->assertSee('budi.pratama@example.com')
        ->assertSee('Seller')
        ->assertSee('ID Penjual')
        ->assertSee('SLR-'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT))
        ->assertSee('Terverifikasi')
        ->assertSee('Email telah terverifikasi')
        ->assertDontSee('Surya Adinata')
        ->assertDontSee('surya.adinata@laba.co.id');
});

it('ensures users can only access their own profile data', function () {
    $role = Role::create(['name' => 'Buyer', 'guard_name' => 'web']);

    $currentUser = User::factory()->create([
        'name' => 'Pengguna Aktif',
        'username' => 'penggunaaktif',
        'email' => 'aktif@example.com',
        'phone' => '081111111111',
    ]);
    $currentUser->assignRole($role);

    $otherUser = User::factory()->create([
        'name' => 'Pengguna Rahasia Lain',
        'username' => 'penggunalain',
        'email' => 'rahasia@example.com',
        'phone' => '082222222222',
    ]);
    $otherUser->assignRole($role);

    $response = $this->actingAs($currentUser)->get(route('profile'));

    $response->assertOk()
        ->assertSee('Pengguna Aktif')
        ->assertSee('aktif@example.com')
        ->assertDontSee('Pengguna Rahasia Lain')
        ->assertDontSee('rahasia@example.com');
});

it('formats account identifiers and roles accurately for superadmins', function () {
    $superadminRole = Role::create(['name' => 'Superadmin', 'guard_name' => 'web']);

    $admin = User::factory()->create([
        'name' => 'Admin Utama',
        'username' => 'adminutama',
        'email' => 'superadmin@mihom.id',
        'phone' => '085555555555',
        'kyc_status' => 'verified',
    ]);
    $admin->assignRole($superadminRole);

    $response = $this->actingAs($admin)->get(route('profile'));

    $response->assertOk()
        ->assertSee('Admin Utama')
        ->assertSee('Super Admin')
        ->assertSee('ID Admin')
        ->assertSee('ADM-'.str_pad((string) $admin->id, 3, '0', STR_PAD_LEFT));
});

it('handles users with missing or unverified email gracefully', function () {
    $role = Role::create(['name' => 'user', 'guard_name' => 'web']);

    $userWithoutEmail = User::factory()->create([
        'name' => 'User Tanpa Email',
        'username' => 'tanpaemail',
        'email' => null,
        'phone' => '083333333333',
        'email_verified_at' => null,
        'kyc_status' => 'unverified',
    ]);
    $userWithoutEmail->assignRole($role);

    $response = $this->actingAs($userWithoutEmail)->get(route('profile'));

    $response->assertOk()
        ->assertSee('User Tanpa Email')
        ->assertSee('Belum ada email')
        ->assertSee('Belum Verifikasi');
});
