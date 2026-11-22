<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
});

function createSuperadminDetailProperty(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'reserved',
        'title' => 'Rumah Master Data',
        'description' => 'Properti untuk pengujian detail master data Superadmin.',
        'price' => 1500000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Dipati Ukur No. 10',
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'rooms' => '3',
        'bathrooms' => '2',
        'certificate_type' => 'SHM',
    ], $attributes));
}

it('links the property drawer to the Superadmin transaction detail page', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $seller = User::factory()->create();
    $property = createSuperadminDetailProperty($seller);

    $this->actingAs($superadmin)
        ->get(route('superadmin.master-data-property'))
        ->assertOk()
        ->assertSeeText('Rumah Master Data')
        ->assertSee(route('superadmin.master-data-property.show', ['property' => $property->uuid]), false);
});

it('shows transaction progress and status history to superadmins', function () {
    $superadmin = User::factory()->create(['name' => 'Super Admin']);
    $superadmin->assignRole('Superadmin');
    $seller = User::factory()->create(['name' => 'Pak Joko Penjual']);
    $buyer = User::factory()->create(['name' => 'Budi Pembeli']);
    $buyer->assignRole('User');

    $property = createSuperadminDetailProperty($seller);

    Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subWeek(),
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->subDays(3)->toDateString(),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_CONFIRMED,
        'completed_at' => now()->subDays(6),
    ]);

    $transaction = Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 150000000,
        'total_property_amount' => $property->price,
        'state' => 'bpn_cleared',
    ]);

    $transaction->logs()->create([
        'actor_id' => $superadmin->id,
        'from_state' => 'dp_paid',
        'to_state' => 'bpn_cleared',
        'notes' => 'Seluruh dokumen legal disetujui. Pengecekan BPN selesai.',
    ]);

    $this->actingAs($superadmin)
        ->get(route('superadmin.master-data-property.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Rumah Master Data')
        ->assertSeeText('Survei & Tahapan Transaksi')
        ->assertSeeText('Riwayat survei properti')
        ->assertSeeText('Riwayat status transaksi')
        ->assertSeeText('Pengecekan Legalitas BPN & Validasi Pajak')
        ->assertSeeText('Seluruh dokumen legal disetujui. Pengecekan BPN selesai.')
        ->assertSeeText('Budi Pembeli')
        ->assertSeeText('Pak Joko Penjual');
});

it('forbids non-superadmin users from opening the transaction detail page', function () {
    $administrator = User::factory()->create();
    $administrator->assignRole('Admin');
    $seller = User::factory()->create();
    $property = createSuperadminDetailProperty($seller);

    $this->actingAs($administrator)
        ->get(route('superadmin.master-data-property.show', ['property' => $property->uuid]))
        ->assertForbidden();
});

it('renders the detail page for properties without any transaction yet', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $seller = User::factory()->create();
    $property = createSuperadminDetailProperty($seller, [
        'title' => 'Rumah Belum Bertransaksi',
        'status' => 'draft',
    ]);

    $this->actingAs($superadmin)
        ->get(route('superadmin.master-data-property.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Rumah Belum Bertransaksi')
        ->assertSeeText('Belum ada pengajuan survei')
        ->assertSeeText('Belum ada riwayat survei.')
        ->assertDontSeeText('Riwayat status transaksi');
});
