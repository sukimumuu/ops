<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $ppatPermissions = [
        'appointments.view_all',
        'documents.verify_status',
        'documents.view_sensitive_pii',
        'transactions.view_all',
        'transactions.mark_bpn_cleared',
    ];

    foreach ($ppatPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'PPAT', 'guard_name' => 'web'])
        ->syncPermissions($ppatPermissions);
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
});

function createLandDeedProperty(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah Klien PPAT',
        'description' => 'Properti untuk pengujian halaman properti klien PPAT.',
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

function createLandDeedDocument(Property $property, string $type = PropertyPhoto::TYPE_CERTIFICATE, array $attributes = []): PropertyPhoto
{
    return PropertyPhoto::query()->create(array_merge([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/documents/'.$type.'.pdf',
        'type' => $type,
        'sort_order' => 0,
    ], $attributes));
}

function createLandDeedTransaction(User $buyer, User $seller, Property $property, string $state = 'dp_paid'): Transaction
{
    return Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 150000000,
        'total_property_amount' => $property->price,
        'state' => $state,
    ]);
}

it('shows only properties assigned to the signed-in PPAT', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $otherPpat = User::factory()->create();
    $otherPpat->assignRole('PPAT');
    $seller = User::factory()->create();

    createLandDeedProperty($seller, [
        'title' => 'Rumah Klien Sendiri',
        'assigned_ppat_id' => $ppat->id,
    ]);
    createLandDeedProperty($seller, [
        'title' => 'Rumah Klien PPAT Lain',
        'assigned_ppat_id' => $otherPpat->id,
    ]);
    createLandDeedProperty($seller, [
        'title' => 'Rumah Tanpa PPAT',
        'assigned_ppat_id' => null,
    ]);

    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties'))
        ->assertOk()
        ->assertSeeText('Rumah Klien Sendiri')
        ->assertDontSeeText('Rumah Klien PPAT Lain')
        ->assertDontSeeText('Rumah Tanpa PPAT');
});

it('searches and filters assigned client properties', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();

    createLandDeedProperty($seller, [
        'title' => 'Villa Dago Asri',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'status' => 'published',
        'category' => 'sale',
        'assigned_ppat_id' => $ppat->id,
    ]);
    createLandDeedProperty($seller, [
        'title' => 'Rumah Surabaya Barat',
        'city' => 'Surabaya',
        'district' => 'Wonokromo',
        'status' => 'reserved',
        'category' => 'rent',
        'property_type' => 'tanah',
        'assigned_ppat_id' => $ppat->id,
    ]);

    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties', ['search' => 'Dago']))
        ->assertSeeText('Villa Dago Asri')
        ->assertDontSeeText('Rumah Surabaya Barat');

    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties', ['status' => 'reserved']))
        ->assertSeeText('Rumah Surabaya Barat')
        ->assertDontSeeText('Villa Dago Asri');

    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties', ['property_type' => 'tanah']))
        ->assertSeeText('Rumah Surabaya Barat')
        ->assertDontSeeText('Villa Dago Asri');
});

it('denies client property access to non-PPAT users and unassigned PPATs', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $otherPpat = User::factory()->create();
    $otherPpat->assignRole('PPAT');
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();

    $property = createLandDeedProperty($seller, [
        'title' => 'Rumah Klien Terbatas',
        'assigned_ppat_id' => $ppat->id,
    ]);

    $this->actingAs($buyer)
        ->get(route('land-deed.client-properties'))
        ->assertForbidden();

    $this->actingAs($buyer)
        ->get(route('land-deed.client-properties.show', ['property' => $property->uuid]))
        ->assertForbidden();

    $this->actingAs($otherPpat)
        ->get(route('land-deed.client-properties.show', ['property' => $property->uuid]))
        ->assertForbidden();
});

it('shows assigned client property detail with the stage three legal check link', function () {
    $ppat = User::factory()->create(['name' => 'PPAT Andini']);
    $ppat->assignRole('PPAT');
    $buyer = User::factory()->create(['name' => 'Budi Pembeli', 'email' => 'budi@example.com']);
    $seller = User::factory()->create(['name' => 'Pak Joko Penjual']);
    $property = createLandDeedProperty($seller, [
        'title' => 'Rumah Mewah Antapani',
        'price' => 1750000000,
        'assigned_ppat_id' => $ppat->id,
    ]);

    Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subWeek(),
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->addWeek()->format('Y-m-d'),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_REQUESTED,
        'completed_at' => now()->subDay(),
    ]);

    createLandDeedTransaction($buyer, $seller, $property);

    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Rumah Mewah Antapani')
        ->assertSeeText('Budi Pembeli')
        ->assertSeeText('Pak Joko Penjual')
        ->assertSeeText('Pengecekan Legalitas BPN & Validasi Pajak')
        ->assertSee(route('land-deed.bpn-legal-check', ['property' => $property->uuid]), false);
});

it('shows every legal document on the BPN legal check page with its validation status', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $property = createLandDeedProperty($seller, ['assigned_ppat_id' => $ppat->id]);

    createLandDeedDocument($property, PropertyPhoto::TYPE_CERTIFICATE, [
        'validation_status' => PropertyPhoto::VALIDATION_APPROVED,
    ]);
    createLandDeedDocument($property, PropertyPhoto::TYPE_IMB);
    createLandDeedDocument($property, PropertyPhoto::TYPE_PBB, [
        'validation_status' => PropertyPhoto::VALIDATION_REJECTED,
        'validation_notes' => 'Hasil pindai kurang jelas.',
    ]);
    createLandDeedTransaction($buyer, $seller, $property, 'bpn_checking');

    $this->actingAs($ppat)
        ->get(route('land-deed.bpn-legal-check', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Sertifikat')
        ->assertSeeText('IMB / PBG')
        ->assertSeeText('SPPT / STTS PBB')
        ->assertSeeText('Disetujui')
        ->assertSeeText('Ditolak')
        ->assertSeeText('Menunggu validasi')
        ->assertSeeText('Hasil pindai kurang jelas.')
        ->assertSeeText('Pengecekan BPN');
});

it('makes approved documents read-only on the BPN legal check page', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $property = createLandDeedProperty($seller, ['assigned_ppat_id' => $ppat->id]);

    $approved = createLandDeedDocument($property, PropertyPhoto::TYPE_CERTIFICATE, [
        'validation_status' => PropertyPhoto::VALIDATION_APPROVED,
        'validated_by_id' => $ppat->id,
        'validated_at' => now(),
    ]);
    $pending = createLandDeedDocument($property, PropertyPhoto::TYPE_PBB);

    $approvedAction = route('land-deed.documents.review', [
        'property' => $property->uuid,
        'document' => $approved->id,
    ]);
    $pendingAction = route('land-deed.documents.review', [
        'property' => $property->uuid,
        'document' => $pending->id,
    ]);

    $this->actingAs($ppat)
        ->get(route('land-deed.bpn-legal-check', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Disetujui')
        ->assertDontSee('action="'.$approvedAction.'"', false)
        ->assertSee('action="'.$pendingAction.'"', false);

    $this->post($approvedAction, [
        'validation_status' => PropertyPhoto::VALIDATION_REJECTED,
        'validation_notes' => 'Mencoba mengubah dokumen yang sudah disetujui.',
    ])->assertSessionHasErrors('validation_status');

    $this->assertDatabaseHas('property_photos', [
        'id' => $approved->id,
        'validation_status' => PropertyPhoto::VALIDATION_APPROVED,
    ]);
});

it('moves a transaction through BPN checking and clears it when all documents are approved', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $property = createLandDeedProperty($seller, ['assigned_ppat_id' => $ppat->id]);

    $certificate = createLandDeedDocument($property, PropertyPhoto::TYPE_CERTIFICATE);
    $pbb = createLandDeedDocument($property, PropertyPhoto::TYPE_PBB);
    $transaction = createLandDeedTransaction($buyer, $seller, $property);

    $this->actingAs($ppat)
        ->post(route('land-deed.documents.review', [
            'property' => $property->uuid,
            'document' => $certificate->id,
        ]), ['validation_status' => PropertyPhoto::VALIDATION_APPROVED])
        ->assertRedirect()
        ->assertSessionHas('status', 'Dokumen berhasil disetujui.');

    $this->assertDatabaseHas('property_photos', [
        'id' => $certificate->id,
        'validation_status' => PropertyPhoto::VALIDATION_APPROVED,
        'validated_by_id' => $ppat->id,
    ]);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'state' => 'bpn_checking',
    ]);
    $this->assertDatabaseHas('transaction_logs', [
        'transaction_id' => $transaction->id,
        'actor_id' => $ppat->id,
        'from_state' => 'dp_paid',
        'to_state' => 'bpn_checking',
    ]);

    $this->post(route('land-deed.documents.review', [
        'property' => $property->uuid,
        'document' => $pbb->id,
    ]), ['validation_status' => PropertyPhoto::VALIDATION_APPROVED])
        ->assertRedirect();

    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'state' => 'bpn_cleared',
    ]);
    $this->assertDatabaseHas('transaction_logs', [
        'transaction_id' => $transaction->id,
        'actor_id' => $ppat->id,
        'from_state' => 'bpn_checking',
        'to_state' => 'bpn_cleared',
    ]);
    expect($transaction->logs()->count())->toBe(2);
});

it('rejects a document with notes and keeps the transaction in BPN checking', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $buyer = User::factory()->create();
    $seller = User::factory()->create();
    $property = createLandDeedProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $document = createLandDeedDocument($property, PropertyPhoto::TYPE_CERTIFICATE);
    $transaction = createLandDeedTransaction($buyer, $seller, $property, 'bpn_checking');

    $this->actingAs($ppat)
        ->post(route('land-deed.documents.review', [
            'property' => $property->uuid,
            'document' => $document->id,
        ]), ['validation_status' => PropertyPhoto::VALIDATION_REJECTED])
        ->assertSessionHasErrors('validation_notes');

    $this->assertDatabaseHas('property_photos', [
        'id' => $document->id,
        'validation_status' => PropertyPhoto::VALIDATION_PENDING,
    ]);

    $this->post(route('land-deed.documents.review', [
        'property' => $property->uuid,
        'document' => $document->id,
    ]), [
        'validation_status' => PropertyPhoto::VALIDATION_REJECTED,
        'validation_notes' => 'Foto sertifikat tidak terbaca.',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Dokumen ditolak dan menunggu perbaikan.');

    $this->assertDatabaseHas('property_photos', [
        'id' => $document->id,
        'validation_status' => PropertyPhoto::VALIDATION_REJECTED,
        'validation_notes' => 'Foto sertifikat tidak terbaca.',
        'validated_by_id' => $ppat->id,
    ]);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'state' => 'bpn_checking',
    ]);
});

it('prevents another PPAT from reviewing documents of an assigned property', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $otherPpat = User::factory()->create();
    $otherPpat->assignRole('PPAT');
    $seller = User::factory()->create();
    $property = createLandDeedProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $document = createLandDeedDocument($property);

    $this->actingAs($otherPpat)
        ->post(route('land-deed.documents.review', [
            'property' => $property->uuid,
            'document' => $document->id,
        ]), ['validation_status' => PropertyPhoto::VALIDATION_APPROVED])
        ->assertForbidden();

    $this->assertDatabaseHas('property_photos', [
        'id' => $document->id,
        'validation_status' => PropertyPhoto::VALIDATION_PENDING,
    ]);
});
