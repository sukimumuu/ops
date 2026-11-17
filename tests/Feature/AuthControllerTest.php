<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'User']);
});

it('assigns the User role to newly registered users', function () {
    $response = $this->post(route('register'), [
        'first_name' => 'New',
        'last_name' => 'Example',
        'phone' => '08123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('phone', '628123456789')->firstOrFail();

    expect($user->hasRole('User'))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

it('rejects login attempts from deactivated accounts', function () {
    User::factory()->create([
        'phone' => '628123456789',
        'is_active' => false,
    ]);

    $this->from(route('login'))
        ->post(route('login'), [
            'phone' => '08123456789',
            'password' => 'password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('phone');

    $this->assertGuest();
});

it('logs out an authenticated user after their account is deactivated', function () {
    $user = User::factory()->create(['is_active' => false]);
    $user->assignRole('User');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Akun Anda telah dinonaktifkan.');

    $this->assertGuest();
});

it('consolidates legacy Buyer and Seller assignments into the User role', function () {
    $buyerRole = Role::create(['name' => 'Buyer', 'guard_name' => 'web']);
    $sellerRole = Role::create(['name' => 'Seller', 'guard_name' => 'web']);
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $buyer->assignRole($buyerRole);
    $seller->assignRole($sellerRole);

    $this->seed(RoleAndPermissionSeeder::class);

    $buyer->refresh();
    $seller->refresh();

    expect($buyer->hasRole('User'))->toBeTrue()
        ->and($buyer->hasRole('Buyer'))->toBeFalse()
        ->and($buyer->hasRole('Seller'))->toBeFalse()
        ->and($buyer->getRoleNames()->all())->toBe(['User'])
        ->and($seller->hasRole('User'))->toBeTrue()
        ->and($seller->getRoleNames()->all())->toBe(['User'])
        ->and($buyer->can('properties.create'))->toBeTrue()
        ->and($buyer->can('appointments.request'))->toBeTrue()
        ->and($seller->can('properties.create'))->toBeTrue()
        ->and($seller->can('appointments.request'))->toBeTrue();

    expect(Role::whereIn('name', ['Buyer', 'Seller'])->exists())->toBeFalse();
});
