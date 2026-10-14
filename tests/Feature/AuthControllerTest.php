<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Buyer']);
});

it('assigns the Buyer role to newly registered users', function () {
    $response = $this->post(route('register'), [
        'first_name' => 'New',
        'last_name' => 'Buyer',
        'phone' => '08123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('phone', '628123456789')->firstOrFail();

    expect($user->hasRole('Buyer'))->toBeTrue();
});
