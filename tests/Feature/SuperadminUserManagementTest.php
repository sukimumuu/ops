<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Superadmin']);
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'PPAT']);
    Role::firstOrCreate(['name' => 'user']);
});

it('shows the add user form to a superadmin', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $this->actingAs($superadmin)
        ->get(route('superadmin.user-management.create'))
        ->assertOk()
        ->assertSeeText('Tambah Pengguna')
        ->assertSee('name="role"', false)
        ->assertSee('value="admin"', false)
        ->assertSee('value="ppat"', false)
        ->assertSee('value="user"', false);
});

it('creates a user and assigns each supported role', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    foreach ([
        ['admin', 'admin', '08123456789'],
        ['ppat', 'PPAT', '08123456790'],
        ['user', 'user', '08123456791'],
    ] as [$role, $assignedRole, $phone]) {
        $response = $this->post(route('superadmin.user-management.store'), [
            'name' => ucfirst($role).' Test',
            'email' => $role.'@example.test',
            'phone' => $phone,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $role,
        ]);

        $response->assertRedirect(route('superadmin.user-management'));
        $createdUser = User::where('email', $role.'@example.test')->firstOrFail();
        expect($createdUser->hasRole($assignedRole))->toBeTrue()
            ->and($createdUser->phone)->toBe('62'.substr($phone, 1))
            ->and(Hash::check('password123', $createdUser->password))->toBeTrue();
    }
});

it('rejects invalid role and duplicate contact details', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $existingUser = User::factory()->create([
        'email' => 'existing@example.test',
        'phone' => '628123456789',
    ]);

    $this->actingAs($superadmin)
        ->from(route('superadmin.user-management.create'))
        ->post(route('superadmin.user-management.store'), [
            'name' => 'New User',
            'email' => $existingUser->email,
            'phone' => '+62 812 3456 789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'superadmin',
        ])
        ->assertRedirect(route('superadmin.user-management.create'))
        ->assertSessionHasErrors(['email', 'phone', 'role']);
});

it('prevents non-superadmins from accessing user management', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user)
        ->get(route('superadmin.user-management.create'))
        ->assertForbidden();
});
