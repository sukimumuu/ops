<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('shows only the signed-in seller properties and activity', function () {
    $sellerRole = Role::create(['name' => 'Seller', 'guard_name' => 'web']);

    $seller = User::factory()->create([
        'name' => 'Seller Dashboard',
        'phone' => '081234567801',
    ]);
    $seller->assignRole($sellerRole);

    $otherSeller = User::factory()->create([
        'name' => 'Another Seller',
        'phone' => '081234567802',
    ]);
    $otherSeller->assignRole($sellerRole);

    $buyer = User::factory()->create([
        'name' => 'Calon Pembeli',
        'phone' => '081234567803',
    ]);
    $otherBuyer = User::factory()->create([
        'name' => 'Pembeli Lain',
        'phone' => '081234567804',
    ]);

    $createProperty = function (User $owner, string $title): Property {
        return Property::query()->create([
            'uuid' => (string) Str::uuid(),
            'seller_id' => $owner->id,
            'property_type' => 'bangunan',
            'type' => 'rumah',
            'category' => 'sale',
            'status' => 'published',
            'title' => $title,
            'description' => 'Rumah nyaman di lingkungan strategis.',
            'price' => 1250000000,
            'negotiable' => false,
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'address' => 'Jl. Merdeka No. 1',
            'land_area_sqm' => 120,
            'building_area_sqm' => 90,
            'rooms' => '3',
            'bathrooms' => '2',
            'floors' => '1',
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
        ]);
    };

    $property = $createProperty($seller, 'Rumah Senja');
    $otherProperty = $createProperty($otherSeller, 'Properti Tetangga');

    Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => '2026-10-10 10:00:00',
        'status' => 'requested',
    ]);
    Appointment::query()->create([
        'property_id' => $otherProperty->id,
        'buyer_id' => $otherBuyer->id,
        'seller_id' => $otherSeller->id,
        'scheduled_at' => '2026-10-11 10:00:00',
        'status' => 'requested',
    ]);

    Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 5000000,
        'total_property_amount' => 1250000000,
        'state' => 'held_in_escrow',
    ]);
    Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $otherProperty->id,
        'buyer_id' => $otherBuyer->id,
        'seller_id' => $otherSeller->id,
        'booking_fee_amount' => 5000000,
        'total_property_amount' => 1250000000,
        'state' => 'held_in_escrow',
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard'));

    $response
        ->assertSee('Selamat datang, Seller Dashboard')
        ->assertSee('Rumah Senja')
        ->assertSee('Calon Pembeli')
        ->assertDontSee('Properti Tetangga')
        ->assertDontSee('Pembeli Lain')
        ->assertViewHas('sellerStats', fn (array $stats): bool => $stats === [
            'totalProperties' => 1,
            'publishedProperties' => 1,
            'pendingSurveys' => 1,
            'activeTransactions' => 1,
        ]);
});

it('shows only the signed-in buyer surveys and transactions', function () {
    $this->travelTo('2026-10-09 09:00:00');

    $buyerRole = Role::create(['name' => 'Buyer', 'guard_name' => 'web']);

    $buyer = User::factory()->create([
        'name' => 'Buyer Dashboard',
        'phone' => '081234567805',
    ]);
    $buyer->assignRole($buyerRole);

    $otherBuyer = User::factory()->create([
        'name' => 'Another Buyer',
        'phone' => '081234567806',
    ]);
    $otherBuyer->assignRole($buyerRole);

    $seller = User::factory()->create([
        'name' => 'Penjual Properti',
        'phone' => '081234567807',
    ]);

    $createProperty = function (User $owner, string $title): Property {
        return Property::query()->create([
            'uuid' => (string) Str::uuid(),
            'seller_id' => $owner->id,
            'property_type' => 'bangunan',
            'type' => 'rumah',
            'category' => 'sale',
            'status' => 'published',
            'title' => $title,
            'description' => 'Rumah nyaman di lingkungan strategis.',
            'price' => 1250000000,
            'negotiable' => false,
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'address' => 'Jl. Merdeka No. 1',
            'land_area_sqm' => 120,
            'building_area_sqm' => 90,
            'rooms' => '3',
            'bathrooms' => '2',
            'floors' => '1',
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
        ]);
    };

    $property = $createProperty($seller, 'Rumah Pilihan Buyer');
    $otherProperty = $createProperty($seller, 'Properti Privat Pembeli Lain');

    Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => '2026-10-10 10:00:00',
        'status' => 'requested',
    ]);
    Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => '2026-10-11 10:00:00',
        'status' => 'confirmed',
    ]);
    Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => '2026-10-08 10:00:00',
        'status' => 'completed',
    ]);
    Appointment::query()->create([
        'property_id' => $otherProperty->id,
        'buyer_id' => $otherBuyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => '2026-10-12 10:00:00',
        'status' => 'requested',
    ]);

    foreach ([
        ['buyer_id' => $buyer->id, 'property_id' => $property->id, 'state' => 'held_in_escrow'],
        ['buyer_id' => $buyer->id, 'property_id' => $property->id, 'state' => 'completed'],
        ['buyer_id' => $otherBuyer->id, 'property_id' => $otherProperty->id, 'state' => 'held_in_escrow'],
    ] as $transaction) {
        Transaction::query()->create([
            'uuid' => (string) Str::uuid(),
            'property_id' => $transaction['property_id'],
            'buyer_id' => $transaction['buyer_id'],
            'seller_id' => $seller->id,
            'booking_fee_amount' => 5000000,
            'total_property_amount' => 1250000000,
            'state' => $transaction['state'],
        ]);
    }

    $response = $this->actingAs($buyer)->get(route('dashboard'));

    $response
        ->assertSee('Selamat datang, Buyer Dashboard')
        ->assertSee('Rumah Pilihan Buyer')
        ->assertSee('Penjual Properti')
        ->assertDontSee('Properti Privat Pembeli Lain')
        ->assertViewHas('buyerStats', fn (array $stats): bool => $stats === [
            'totalSurveys' => 3,
            'upcomingSurveys' => 2,
            'activeTransactions' => 1,
            'completedTransactions' => 1,
        ]);
});

it('redirects guests to login when opening the dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
