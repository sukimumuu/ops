<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('filters users by search and role for admins and superadmins', function () {
    Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'PPAT', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Seller', 'guard_name' => 'web']);

    $administrator = User::factory()->create();
    $administrator->assignRole('Superadmin');
    $matchingUser = User::factory()->create([
        'name' => 'Bandung Property Officer',
        'phone' => '6281234567001',
    ]);
    $matchingUser->assignRole('PPAT');
    $otherUser = User::factory()->create([
        'name' => 'Bandung Property Seller',
        'phone' => '6281234567002',
    ]);
    $otherUser->assignRole('Seller');

    $this->actingAs($administrator);

    foreach ([route('admin.user-management'), route('superadmin.user-management')] as $url) {
        $this->get($url.'?search=Bandung&role=PPAT')
            ->assertSeeText('Bandung Property Officer')
            ->assertDontSeeText('Bandung Property Seller');
    }
});

it('filters pending properties by search, type, and category for admins and superadmins', function () {
    Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);

    $administrator = User::factory()->create();
    $administrator->assignRole('Superadmin');
    $seller = User::factory()->create(['name' => 'Property Seller']);

    $createProperty = function (string $title, string $type, string $category) use ($seller): Property {
        return Property::query()->create([
            'uuid' => (string) Str::uuid(),
            'seller_id' => $seller->id,
            'property_type' => $type,
            'type' => $type === 'bangunan' ? 'rumah' : 'kavling',
            'category' => $category,
            'status' => 'pending_verification',
            'title' => $title,
            'description' => 'Properti untuk pengujian filter.',
            'price' => 1250000000,
            'negotiable' => false,
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'address' => 'Jl. Merdeka No. 1',
            'land_area_sqm' => 120,
            'building_area_sqm' => $type === 'bangunan' ? 90 : null,
            'certificate_type' => 'SHM',
        ]);
    };

    $createProperty('Bandung Rumah Dijual', 'bangunan', 'sale');
    $createProperty('Bandung Tanah Disewakan', 'tanah', 'rent');

    $this->actingAs($administrator);

    foreach ([route('admin.master-data-property'), route('superadmin.master-data-property')] as $url) {
        $this->get($url.'?search=Bandung&property_type=bangunan&category=sale')
            ->assertSeeText('Bandung Rumah Dijual')
            ->assertDontSeeText('Bandung Tanah Disewakan');
    }
});
