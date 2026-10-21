<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['Superadmin', 'Admin', 'PPAT', 'User'] as $roleName) {
        Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    }
});

it('shows the administrator panel login page to guests', function () {
    $this->get(route('panel.login'))
        ->assertOk()
        ->assertSee('Panel Administrator')
        ->assertSee('Email')
        ->assertSee('Kata Sandi');
});

it('logs in administrators using email and password', function (string $role) {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);
    $user->assignRole($role);

    $expectedRoute = $role === 'PPAT' ? route('land-deed.index') : route('dashboard');

    $this->post(route('panel.login.store'), [
        'email' => 'admin@example.com',
        'password' => 'password',
    ])->assertRedirect($expectedRoute);

    $this->assertAuthenticatedAs($user);
})->with(['Superadmin', 'Admin', 'PPAT']);

it('rejects invalid administrator credentials', function () {
    User::factory()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ])->assignRole('Admin');

    $this->from(route('panel.login'))
        ->post(route('panel.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('panel.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects regular users from the administrator panel', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'password',
    ])->assignRole('User');

    $this->from(route('panel.login'))
        ->post(route('panel.login.store'), [
            'email' => 'user@example.com',
            'password' => 'password',
        ])
        ->assertRedirect(route('panel.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('redirects authenticated administrators away from the panel login page', function () {
    $user = User::factory()->create()->assignRole('Admin');

    $this->actingAs($user)
        ->get(route('panel.login'))
        ->assertRedirect(route('dashboard'));
});

it('logs out administrators back to the panel login page', function () {
    $user = User::factory()->create()->assignRole('Admin');

    $this->actingAs($user)
        ->post(route('panel.logout'))
        ->assertRedirect(route('panel.login'));

    $this->assertGuest();
});
