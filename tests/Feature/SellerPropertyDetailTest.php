<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
});

function createSellerDetailProperty(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah Detail Penjual',
        'description' => 'Deskripsi lengkap properti untuk pengujian halaman detail penjual.',
        'price' => 1500000000,
        'negotiable' => true,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Merdeka No. 1',
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'rooms' => '3',
        'bathrooms' => '2',
        'floors' => '2',
        'certificate_type' => 'SHM',
        'url_maps' => 'https://www.openstreetmap.org/?mlat=-6.9175&mlon=107.6191#map=16/-6.9175/107.6191',
    ], $attributes));
}

function createSellerDetailAppointment(User $seller, User $buyer, Property $property, array $attributes = []): Appointment
{
    return Appointment::query()->create(array_merge([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->addDays(2),
        'status' => Appointment::STATUS_REQUESTED,
    ], $attributes));
}

it('redirects guests to the login page', function () {
    $property = createSellerDetailProperty(User::factory()->create());

    $this->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertRedirect(route('login'));
});

it('shows essential property information and the latest survey progress', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create(['name' => 'Calon Pembeli Survei']);
    $property = createSellerDetailProperty($seller);

    createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'scheduled_at' => now()->subDays(5),
    ]);
    $latest = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
        'scheduled_at' => now()->addDays(2),
    ]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Rumah Detail Penjual')
        ->assertSeeText('Rp 1.500.000.000')
        ->assertSeeText('Coblong, Bandung, Jawa Barat')
        ->assertSeeText('Deskripsi lengkap properti untuk pengujian halaman detail penjual.')
        ->assertSeeText('SHM')
        ->assertSeeText('Calon Pembeli Survei')
        ->assertSeeText('Progres properti')
        ->assertSeeText('Survei terjadwal')
        ->assertSeeText('Keputusan pembeli')
        ->assertSeeText('Buka lokasi di peta')
        ->assertViewHas('latestAppointment', fn (Appointment $appointment): bool => $appointment->is($latest))
        ->assertViewHas('selectedAppointment', fn (Appointment $appointment): bool => $appointment->is($latest))
        ->assertViewHas('progress', fn (array $progress): bool => $progress['label'] === 'Survei terjadwal'
            && $progress['percentage'] === 66);
});

it('shows an empty survey progress when the property has no appointments', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $property = createSellerDetailProperty($seller);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSeeText('Belum ada pengajuan survei')
        ->assertViewHas('progress', fn (array $progress): bool => $progress['percentage'] === 0
            && $progress['completed_steps'] === 0);
});

it('reserves the property when the buyer agrees to proceed after a survey', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($seller)
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
            'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
            'result_notes' => 'Calon pembeli tertarik dan akan melanjutkan diskusi harga.',
        ])
        ->assertRedirect(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertSessionHas('status', 'Hasil survei disimpan dan properti ditandai sebagai dipesan.');

    $appointment->refresh();

    expect($appointment->status)->toBe(Appointment::STATUS_COMPLETED)
        ->and($appointment->buyer_intent)->toBe(Appointment::BUYER_INTENT_DEAL)
        ->and($appointment->result_notes)->toBe('Calon pembeli tertarik dan akan melanjutkan diskusi harga.')
        ->and($appointment->completed_at)->not->toBeNull()
        ->and($property->fresh()->status)->toBe('reserved')
        ->and($buyer->notifications()->first()->data)->toBe([
            'title' => 'Hasil survei tersedia',
            'message' => 'Hasil survei Rumah Detail Penjual telah dicatat: Berminat membeli. Catatan: Calon pembeli tertarik dan akan melanjutkan diskusi harga.',
            'url' => route('buyer.survey-schedule'),
        ]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Survei selesai')
        ->assertSeeText('Berminat membeli')
        ->assertSeeText('Calon pembeli tertarik dan akan melanjutkan diskusi harga.');
});

it('keeps the property status when the buyer does not proceed after a survey', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($seller)
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
            'buyer_intent' => Appointment::BUYER_INTENT_NO_DEAL,
            'result_notes' => 'Calon pembeli memilih tidak melanjutkan pembelian.',
        ])
        ->assertRedirect(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertSessionHas('status', 'Hasil survei berhasil disimpan.');

    $appointment->refresh();

    expect($appointment->status)->toBe(Appointment::STATUS_COMPLETED)
        ->and($appointment->buyer_intent)->toBe(Appointment::BUYER_INTENT_NO_DEAL)
        ->and($property->fresh()->status)->toBe('published');
});

it('requires a buyer decision when completing a survey', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($seller)
        ->from(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
            'result_notes' => 'Calon pembeli tertarik.',
        ])
        ->assertSessionHasErrors('buyer_intent');

    $appointment->refresh();

    expect($appointment->status)->toBe(Appointment::STATUS_CONFIRMED)
        ->and($appointment->buyer_intent)->toBeNull()
        ->and($property->fresh()->status)->toBe('published');
});

it('rejects an unsupported buyer decision', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($seller)
        ->from(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
            'buyer_intent' => 'maybe',
            'result_notes' => 'Calon pembeli masih ragu.',
        ])
        ->assertSessionHasErrors('buyer_intent');

    $appointment->refresh();

    expect($appointment->status)->toBe(Appointment::STATUS_CONFIRMED)
        ->and($appointment->buyer_intent)->toBeNull()
        ->and($property->fresh()->status)->toBe('published');
});

it('lets a seller cancel a confirmed survey and clears its completion time', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
        'completed_at' => now()->subHour(),
    ]);

    $this->actingAs($seller)
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_CANCELLED,
            'result_notes' => 'Calon pembeli membatalkan kunjungan.',
        ])
        ->assertRedirect(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertSessionHas('status', 'Survei berhasil dibatalkan.');

    $appointment->refresh();

    expect($appointment->status)->toBe(Appointment::STATUS_CANCELLED)
        ->and($appointment->buyer_intent)->toBeNull()
        ->and($appointment->result_notes)->toBe('Calon pembeli membatalkan kunjungan.')
        ->and($appointment->completed_at)->toBeNull()
        ->and($buyer->notifications()->first()->data)->toBe([
            'title' => 'Survei dibatalkan',
            'message' => 'Survei Rumah Detail Penjual dibatalkan oleh penjual. Catatan: Calon pembeli membatalkan kunjungan.',
            'url' => route('buyer.survey-schedule'),
        ]);
});

it('only records results for confirmed surveys', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property);

    $this->actingAs($seller)
        ->from(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
            'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
            'result_notes' => 'Hasil tidak boleh tersimpan.',
        ])
        ->assertSessionHasErrors('status');

    $appointment->refresh();

    expect($appointment->status)->toBe(Appointment::STATUS_REQUESTED)
        ->and($appointment->buyer_intent)->toBeNull()
        ->and($appointment->result_notes)->toBeNull()
        ->and($appointment->completed_at)->toBeNull()
        ->and($property->fresh()->status)->toBe('published')
        ->and($buyer->notifications()->exists())->toBeFalse();
});

it('selects an older confirmed survey for result entry', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);

    $olderConfirmed = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
        'scheduled_at' => now()->subDays(2),
    ]);
    createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'scheduled_at' => now()->subDay(),
    ]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $olderConfirmed->id,
        ]))
        ->assertOk()
        ->assertViewHas('selectedAppointment', fn (Appointment $appointment): bool => $appointment->is($olderConfirmed))
        ->assertSeeText('Survei sebelumnya')
        ->assertSeeText('Catat hasil survei');
});

it('forbids other sellers from viewing or updating the survey', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($otherSeller)
        ->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertForbidden();

    $this->actingAs($otherSeller)
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
        ])
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe(Appointment::STATUS_CONFIRMED);
});

it('rejects an appointment that belongs to another property', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $otherProperty = createSellerDetailProperty($seller, ['title' => 'Properti Lain']);
    $appointment = createSellerDetailAppointment($seller, $buyer, $otherProperty, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($seller)
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
        ])
        ->assertNotFound();

    expect($appointment->fresh()->status)->toBe(Appointment::STATUS_CONFIRMED);
});
