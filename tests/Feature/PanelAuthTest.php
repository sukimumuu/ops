<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['Superadmin', 'Admin', 'PPAT', 'User'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

it('renders the administrator panel login page', function () {
    $this->get('/panel')
        ->assertOk()
        ->assertSeeText('Panel Administrator')
        ->assertSeeText('Masuk ke Panel')
        ->assertSeeText('Email')
        ->assertSeeText('Kata Sandi');
});

it('authenticates a superadmin using email and password', function () {
    $user = User::factory()->create(['email' => 'superadmin@mihom.id']);
    $user->assignRole('Superadmin');

    $this->post('/panel', [
        'email' => 'superadmin@mihom.id',
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('authenticates an admin using email and password', function () {
    $user = User::factory()->create(['email' => 'admin@mihom.id']);
    $user->assignRole('Admin');

    $this->post('/panel', [
        'email' => 'admin@mihom.id',
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('redirects a ppat to the land deed dashboard after login', function () {
    $user = User::factory()->create(['email' => 'ppat@mihom.id']);
    $user->assignRole('PPAT');

    $this->post('/panel', [
        'email' => 'ppat@mihom.id',
        'password' => 'password',
    ])->assertRedirect(route('land-deed.index'));

    $this->assertAuthenticatedAs($user);
});

it('rejects credentials from users without administrator roles', function () {
    $user = User::factory()->create(['email' => 'user@mihom.id']);
    $user->assignRole('User');

    $this->from('/panel')
        ->post('/panel', [
            'email' => 'user@mihom.id',
            'password' => 'password',
        ])
        ->assertRedirect('/panel')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects invalid credentials', function () {
    User::factory()->create(['email' => 'admin@mihom.id']);

    $this->from('/panel')
        ->post('/panel', [
            'email' => 'admin@mihom.id',
            'password' => 'wrong-password',
        ])
        ->assertRedirect('/panel')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('redirects an authenticated administrator away from the login page', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $this->actingAs($user)
        ->get('/panel')
        ->assertRedirect(route('dashboard'));
});

it('logs an administrator out and returns to the panel login page', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');

    $this->actingAs($user)
        ->post('/panel/keluar')
        ->assertRedirect(route('panel.login'));

    $this->assertGuest();
});
