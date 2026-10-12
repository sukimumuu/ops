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
    Role::firstOrCreate(['name' => 'Buyer']);
    Role::firstOrCreate(['name' => 'Seller']);
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
        ->assertSee('value="buyer"', false)
        ->assertSee('value="seller"', false)
        ->assertSee("x-show=\"role === 'ppat'\"", false)
        ->assertSee('name="province"', false)
        ->assertSee('name="city"', false)
        ->assertSee('name="district"', false)
        ->assertSee('listing\\/wilayah\\/provinces', false)
        ->assertSee('__CODE__', false);
});

it('creates a user and assigns each supported role', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $this->actingAs($superadmin);

    foreach ([
        ['admin', 'admin', '08123456789'],
        ['ppat', 'PPAT', '08123456790'],
        ['buyer', 'Buyer', '08123456791'],
        ['seller', 'Seller', '08123456795'],
    ] as [$role, $assignedRole, $phone]) {
        $userData = [
            'name' => ucfirst($role).' Test',
            'email' => $role.'@example.test',
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
        $createdUser = User::where('email', $role.'@example.test')->firstOrFail();
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
        'name' => 'Buyer Without Location',
        'email' => 'buyer-location@example.test',
        'phone' => '08123456793',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'buyer',
        'province' => 'Banten',
        'city' => 'Kota Tangerang Selatan',
        'district' => 'Pondok Aren',
    ])->assertRedirect(route('superadmin.user-management'));

    $buyer = User::where('email', 'buyer-location@example.test')->firstOrFail();
    expect($buyer->province)->toBeNull()
        ->and($buyer->city)->toBeNull()
        ->and($buyer->district)->toBeNull();
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
});

it('prevents non-superadmins from accessing user management', function () {
    $user = User::factory()->create();
    $user->assignRole('Buyer');

    $this->actingAs($user)
        ->get(route('superadmin.user-management.create'))
        ->assertForbidden();
});
