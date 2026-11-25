<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Superadmin']);
    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'PPAT']);
    Role::firstOrCreate(['name' => 'User']);
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
        ->assertSee('value="user"', false)
        ->assertSee("x-show=\"role === 'ppat'\"", false)
        ->assertSee('@mihom.id', false)
        ->assertSee('inputmode="numeric"', false)
        ->assertSee('>+62</span>', false)
        ->assertSee('name="province"', false)
        ->assertSee('name="city"', false)
        ->assertSee('name="district"', false)
        ->assertSee('listing\\/wilayah\\/provinces', false)
        ->assertSee('__CODE__', false);
});

it('shows editable account details and confirmation actions to superadmins', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $user = User::factory()->create([
        'name' => 'Akun Target',
        'email' => 'target@example.test',
        'phone' => '628123456700',
    ]);
    $user->assignRole('User');
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($superadmin)
        ->get(route('superadmin.user-management'))
        ->assertSee('Aktif')
        ->assertSee('Edit')
        ->assertSee('Nonaktifkan')
        ->assertSee('Hapus')
        ->assertSee('aria-modal="true"', false)
        ->assertSee('data-confirm-title="Nonaktifkan akun?"', false);

    $this->get(route('superadmin.user-management.edit', $user))
        ->assertSeeText('Edit Pengguna')
        ->assertSee('value="Akun Target"', false)
        ->assertSee('value="target@example.test"', false)
        ->assertSee('value="628123456700"', false)
        ->assertSee('value="user" selected', false);

    $this->get(route('superadmin.user-management.edit', $admin))
        ->assertSee('value="admin" selected', false);
});

it('creates a user and assigns each supported role', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    foreach ([
        ['admin', 'Admin', '08123456789'],
        ['ppat', 'PPAT', '08123456790'],
        ['user', 'User', '08123456796'],
    ] as [$role, $assignedRole, $phone]) {
        $userData = [
            'name' => ucfirst($role).' Test',
            'email' => $role,
            'phone' => $phone,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $role,
        ];

        if ($role === 'ppat') {
            $userData += [
                'province' => 'Banten',
                'province_code' => '36',
                'city' => 'Kota Tangerang Selatan',
                'city_code' => '36.74',
                'district' => 'Pondok Aren',
                'district_code' => '36.74.03',
            ];
        }

        $response = $this->post(route('superadmin.user-management.store'), $userData);

        $response->assertRedirect(route('superadmin.user-management'));
        $createdUser = User::where('email', $role.'@mihom.id')->firstOrFail();
        expect($createdUser->hasRole($assignedRole))->toBeTrue()
            ->and($createdUser->phone)->toBe('62'.substr($phone, 1))
            ->and(Hash::check('password123', $createdUser->password))->toBeTrue();
    }
});

it('requires and stores the selected location for PPAT users only', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    $ppatData = [
        'name' => 'PPAT Location Test',
        'email' => 'ppat-location@example.test',
        'phone' => '08123456792',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'ppat',
    ];

    $this->from(route('superadmin.user-management.create'))
        ->post(route('superadmin.user-management.store'), $ppatData)
        ->assertRedirect(route('superadmin.user-management.create'))
        ->assertSessionHasErrors(['province', 'city', 'district', 'province_code', 'city_code', 'district_code']);

    $this->post(route('superadmin.user-management.store'), $ppatData + [
        'province' => 'Banten',
        'province_code' => '36',
        'city' => 'Kota Tangerang Selatan',
        'city_code' => '36.74',
        'district' => 'Pondok Aren',
        'district_code' => '36.74.03',
    ])->assertRedirect(route('superadmin.user-management'));

    $ppat = User::where('email', 'ppat-location@example.test')->firstOrFail();
    expect($ppat->province)->toBe('Banten')
        ->and($ppat->city)->toBe('Kota Tangerang Selatan')
        ->and($ppat->district)->toBe('Pondok Aren');

    $this->post(route('superadmin.user-management.store'), [
        'name' => 'User Without Location',
        'email' => 'user-location@example.test',
        'phone' => '08123456793',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
        'province' => 'Banten',
        'city' => 'Kota Tangerang Selatan',
        'district' => 'Pondok Aren',
    ])->assertRedirect(route('superadmin.user-management'));

    $user = User::where('email', 'user-location@example.test')->firstOrFail();
    expect($user->province)->toBeNull()
        ->and($user->city)->toBeNull()
        ->and($user->district)->toBeNull();
});

it('updates user details and synchronizes the selected role', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $user = User::factory()->create([
        'email' => 'before@example.test',
        'phone' => '628123456701',
    ]);
    $user->assignRole('User');

    $this->actingAs($superadmin)
        ->put(route('superadmin.user-management.update', $user), [
            'name' => 'Akun Diperbarui',
            'email' => 'after@example.test',
            'phone' => '08123456702',
            'role' => 'admin',
        ])
        ->assertRedirect(route('superadmin.user-management'));

    $user->refresh();
    expect($user->name)->toBe('Akun Diperbarui')
        ->and($user->email)->toBe('after@example.test')
        ->and($user->phone)->toBe('628123456702')
        ->and($user->hasRole('Admin'))->toBeTrue()
        ->and($user->hasRole('User'))->toBeFalse();
});

it('rejects duplicate contact details when updating a user', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $existingUser = User::factory()->create();
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($superadmin)
        ->from(route('superadmin.user-management.edit', $user))
        ->put(route('superadmin.user-management.update', $user), [
            'name' => 'Akun Baru',
            'email' => $existingUser->email,
            'phone' => $existingUser->phone,
            'role' => 'user',
        ])
        ->assertRedirect(route('superadmin.user-management.edit', $user))
        ->assertSessionHasErrors(['email', 'phone']);
});

it('activates and deactivates managed user accounts', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($superadmin)
        ->patch(route('superadmin.user-management.status.update', $user), ['is_active' => '0'])
        ->assertRedirect(route('superadmin.user-management'));

    expect($user->fresh()->is_active)->toBeFalse();

    $this->patch(route('superadmin.user-management.status.update', $user), ['is_active' => '1'])
        ->assertRedirect(route('superadmin.user-management'));

    expect($user->fresh()->is_active)->toBeTrue();
});

it('soft deletes accounts while preserving their user records', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($superadmin)
        ->delete(route('superadmin.user-management.destroy', $user))
        ->assertRedirect(route('superadmin.user-management'));

    $this->assertSoftDeleted($user);
    expect(User::withTrashed()->findOrFail($user->id)->is_active)->toBeFalse();
});

it('rejects PPAT location codes outside the selected hierarchy', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $this->actingAs($superadmin)
        ->from(route('superadmin.user-management.create'))
        ->post(route('superadmin.user-management.store'), [
            'name' => 'Invalid PPAT Location',
            'email' => 'ppat-invalid-location@example.test',
            'phone' => '08123456794',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'ppat',
            'province' => 'Banten',
            'province_code' => '36',
            'city' => 'Kota Administrasi Jakarta Selatan',
            'city_code' => '31.74',
            'district' => 'Cilandak',
            'district_code' => '31.74.06',
        ])
        ->assertRedirect(route('superadmin.user-management.create'))
        ->assertSessionHasErrors(['city_code']);
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

    foreach (['buyer', 'seller'] as $removedRole) {
        $this->from(route('superadmin.user-management.create'))
            ->post(route('superadmin.user-management.store'), [
                'name' => 'Removed Role Test',
                'email' => $removedRole.'-role@example.test',
                'phone' => $removedRole === 'buyer' ? '08123456797' : '08123456798',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => $removedRole,
            ])
            ->assertRedirect(route('superadmin.user-management.create'))
            ->assertSessionHasErrors(['role']);
    }
});

it('prevents non-superadmins from accessing user management', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($user)
        ->get(route('superadmin.user-management.create'))
        ->assertForbidden();
});

it('does not allow superadmins to edit another superadmin account', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $protectedUser = User::factory()->create();
    $protectedUser->assignRole('Superadmin');

    $this->actingAs($superadmin)
        ->get(route('superadmin.user-management.edit', $protectedUser))
        ->assertNotFound();
});
