<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
});

function createBuyerPropertyForTest(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah Impian Pembeli',
        'description' => 'Properti untuk pengujian halaman properti dibeli.',
        'price' => 1200000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Dipati Ukur No. 10',
        'land_area_sqm' => 100,
        'building_area_sqm' => 80,
        'rooms' => '3',
        'bathrooms' => '2',
        'certificate_type' => 'SHM',
    ], $attributes));
}

function createBuyerAppointmentForTest(User $buyer, User $seller, Property $property, array $attributes = []): Appointment
{
    return Appointment::query()->create(array_merge([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->addDay(),
        'status' => Appointment::STATUS_REQUESTED,
    ], $attributes));
}

it('redirects guests to the login page for bought properties', function () {
    $this->get(route('buyer.bought-properties'))
        ->assertRedirect(route('login'));
});

it('shows only properties the buyer is buying via appointment or transaction', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');

    $seller = User::factory()->create();
    $seller->assignRole('User');

    $otherBuyer = User::factory()->create();
    $otherBuyer->assignRole('User');

    $myAppointmentProperty = createBuyerPropertyForTest($seller, ['title' => 'Rumah Lewat Survei']);
    createBuyerAppointmentForTest($buyer, $seller, $myAppointmentProperty);

    $myTransactionProperty = createBuyerPropertyForTest($seller, ['title' => 'Rumah Lewat Transaksi']);
    Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $myTransactionProperty->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 5000000,
        'total_property_amount' => 1200000000,
        'state' => 'held_in_escrow',
    ]);

    $otherProperty = createBuyerPropertyForTest($seller, ['title' => 'Rumah Orang Lain']);
    createBuyerAppointmentForTest($otherBuyer, $seller, $otherProperty);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties'))
        ->assertOk()
        ->assertSeeText('Rumah Lewat Survei')
        ->assertSeeText('Rumah Lewat Transaksi')
        ->assertDontSeeText('Rumah Orang Lain');
});

it('searches bought properties by title and location', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $dagoProperty = createBuyerPropertyForTest($seller, [
        'title' => 'Villa Dago Asri',
        'city' => 'Bandung',
        'district' => 'Coblong',
    ]);
    createBuyerAppointmentForTest($buyer, $seller, $dagoProperty);

    $surabayaProperty = createBuyerPropertyForTest($seller, [
        'title' => 'Rumah Surabaya Barat',
        'city' => 'Surabaya',
        'district' => 'Wonokromo',
    ]);
    createBuyerAppointmentForTest($buyer, $seller, $surabayaProperty);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties', ['search' => 'Dago']))
        ->assertSeeText('Villa Dago Asri')
        ->assertDontSeeText('Rumah Surabaya Barat');

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties', ['search' => 'Surabaya']))
        ->assertSeeText('Rumah Surabaya Barat')
        ->assertDontSeeText('Villa Dago Asri');
});

it('filters bought properties by status, category, and property type', function (array $query, string $visibleTitle, string $hiddenTitle) {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $publishedProperty = createBuyerPropertyForTest($seller, [
        'title' => 'Properti Terbit',
        'status' => 'published',
        'category' => 'sale',
        'property_type' => 'bangunan',
    ]);
    createBuyerAppointmentForTest($buyer, $seller, $publishedProperty);

    $reservedProperty = createBuyerPropertyForTest($seller, [
        'title' => 'Properti Dipesan',
        'status' => 'reserved',
        'category' => 'rent',
        'property_type' => 'tanah',
    ]);
    createBuyerAppointmentForTest($buyer, $seller, $reservedProperty);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties', $query))
        ->assertSeeText($visibleTitle)
        ->assertDontSeeText($hiddenTitle);
})->with([
    'status' => [['status' => 'reserved'], 'Properti Dipesan', 'Properti Terbit'],
    'category' => [['category' => 'sale'], 'Properti Terbit', 'Properti Dipesan'],
    'property type' => [['property_type' => 'tanah'], 'Properti Dipesan', 'Properti Terbit'],
]);

it('sorts bought properties by price and creation date', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $cheap = createBuyerPropertyForTest($seller, ['title' => 'Properti Murah', 'price' => 500000000]);
    createBuyerAppointmentForTest($buyer, $seller, $cheap);

    $expensive = createBuyerPropertyForTest($seller, ['title' => 'Properti Mahal', 'price' => 2500000000]);
    createBuyerAppointmentForTest($buyer, $seller, $expensive);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties', ['sort' => 'price_asc']))
        ->assertSeeInOrder(['Properti Murah', 'Properti Mahal']);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties', ['sort' => 'price_desc']))
        ->assertSeeInOrder(['Properti Mahal', 'Properti Murah']);
});

it('displays empty state when user has no bought properties', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties'))
        ->assertOk()
        ->assertSeeText('Belum ada properti')
        ->assertSeeText('Anda belum memiliki properti yang sedang dibeli atau disurvei.');
});

it('shows buyer property detail page for authorized buyer', function () {
    $buyer = User::factory()->create(['name' => 'Budi Pembeli']);
    $buyer->assignRole('User');
    $seller = User::factory()->create(['name' => 'Pak Joko Penjual', 'email' => 'joko@example.com']);
    $seller->assignRole('User');

    $property = createBuyerPropertyForTest($seller, [
        'title' => 'Rumah Mewah Antapani',
        'price' => 1750000000,
    ]);

    createBuyerAppointmentForTest($buyer, $seller, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
        'notes' => 'Tolong sediakan kunci saat survei.',
    ]);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Rumah Mewah Antapani')
        ->assertSeeText('Rp 1.750.000.000')
        ->assertSeeText('Pak Joko Penjual')
        ->assertSeeText('joko@example.com')
        ->assertSeeText('Survei terjadwal')
        ->assertSeeText('Tolong sediakan kunci saat survei.');
});

it('denies access to property detail page for unauthorized users', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $otherUser = User::factory()->create();
    $otherUser->assignRole('User');
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $property = createBuyerPropertyForTest($seller);
    createBuyerAppointmentForTest($buyer, $seller, $property);

    $this->actingAs($otherUser)
        ->get(route('buyer.bought-properties.show', ['property' => $property->uuid]))
        ->assertForbidden();
});

it('shows deed timeline steps when survey buyer intent is deal', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $property = createBuyerPropertyForTest($seller);

    createBuyerAppointmentForTest($buyer, $seller, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->addWeek()->format('Y-m-d'),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_REQUESTED,
        'completed_at' => now()->subDay(),
    ]);
    $buyer->givePermissionTo(Permission::firstOrCreate([
        'name' => 'transactions.initiate_dp',
        'guard_name' => 'web',
    ]));
    Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 120000000,
        'total_property_amount' => $property->price,
        'state' => 'draft',
    ]);

    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Proses Pembelian Sedang Berjalan')
        ->assertSeeText('Jadwal Temu PPAT')
        ->assertSeeText('Pembayaran DP (Escrow)')
        ->assertSeeText('Pengecekan Legalitas BPN')
        ->assertSeeText('Validasi Pajak (PPh & BPHTB)')
        ->assertSeeText('Penjadwalan & TTD Akta Jual Beli (Offline)')
        ->assertSeeText('Eksekusi Pencairan Escrow (Release Funds)')
        ->assertSeeText('Balik Nama Sertifikat (Tahap Akhir)')
        ->assertSeeText('Menunggu persetujuan PPAT')
        ->assertSeeText('Pembayaran DP terkunci sampai jadwal temu PPAT disetujui.')
        ->assertDontSeeText('Bayar DP');
});
