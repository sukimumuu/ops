<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate([
        'name' => 'appointments.confirm_or_reject',
        'guard_name' => 'web',
    ]);
    $propertyUpdatePermission = Permission::firstOrCreate([
        'name' => 'properties.update_own',
        'guard_name' => 'web',
    ]);
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web'])
        ->syncPermissions([$permission, $propertyUpdatePermission]);
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
        ->assertDontSeeText('Pilih Jadwal Temu PPAT')
        ->assertViewHas('latestAppointment', fn (Appointment $appointment): bool => $appointment->is($latest))
        ->assertViewHas('selectedAppointment', fn (Appointment $appointment): bool => $appointment->is($latest))
        ->assertViewHas('progress', fn (array $progress): bool => $progress['label'] === 'Survei terjadwal'
            && $progress['percentage'] === 66);
});

it('shows the purchase timeline and PPAT date picker when the buyer is interested', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $ppat = User::factory()->create();
    $buyer = User::factory()->create(['name' => 'Pembeli Berminat']);
    $property = createSellerDetailProperty($seller, ['assigned_ppat_id' => $ppat->id]);

    createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'completed_at' => now(),
    ]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertSeeText('Pembeli Berminat')
        ->assertSeeText('Berminat membeli')
        ->assertSeeText('Pilih Jadwal Temu PPAT')
        ->assertSeeText('Pembayaran DP')
        ->assertSeeText('Pengecekan Legalitas BPN & Validasi Pajak')
        ->assertSeeText('Pelunasan Properti (Escrow)')
        ->assertSeeText('Penjadwalan & TTD Akta Jual Beli (Offline)')
        ->assertSeeText('Eksekusi Pencairan Escrow (Release Funds)')
        ->assertSeeText('Balik Nama Sertifikat (Tahap Akhir)')
        ->assertSeeText('Tanggal pertemuan yang diinginkan')
        ->assertSeeText('Tanggal akan dikirim kepada PPAT yang ditugaskan untuk disetujui.')
        ->assertSeeText('Ajukan jadwal temu PPAT')
        ->assertSee('type="date"', false)
        ->assertSee('action="'.route('seller.my-properties.ppat-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $property->appointments()->first()->id,
        ]).'"', false);
});

it('saves the requested PPAT meeting date without changing the survey schedule', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $ppat = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'scheduled_at' => '2026-10-20 10:00:00',
    ]);

    $this->travelTo(now()->setDate(2026, 10, 1)->startOfDay());

    $this->actingAs($seller)
        ->post(route('seller.my-properties.ppat-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ppat_meeting_date' => '2026-10-15',
        ])
        ->assertRedirect(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertSessionHas('status', 'Permintaan jadwal temu PPAT berhasil dikirim dan ditambahkan ke kalender PPAT.');

    expect($appointment->fresh()->ppat_meeting_date->format('Y-m-d'))->toBe('2026-10-15')
        ->and($appointment->fresh()->ppat_meeting_status)->toBe(Appointment::PPAT_MEETING_STATUS_REQUESTED)
        ->and($appointment->fresh()->scheduled_at->format('Y-m-d H:i:s'))->toBe('2026-10-20 10:00:00');

    expect($ppat->notifications()->first()->data)->toBe([
        'title' => 'Permintaan jadwal temu PPAT',
        'message' => $seller->name.' mengajukan pertemuan dengan '.$buyer->name.' pada Kamis, 15 Oktober 2026.',
        'url' => route('land-deed.index', ['month' => '2026-10']),
    ])->and($buyer->notifications()->first()->data)->toBe([
        'title' => 'Permintaan jadwal temu PPAT diajukan',
        'message' => $seller->name.' mengajukan jadwal temu PPAT pada Kamis, 15 Oktober 2026.',
        'url' => route('buyer.survey-schedule'),
    ]);
});

it('rejects a past PPAT meeting date', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $ppat = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
    ]);

    $this->travelTo(now()->setDate(2026, 10, 29)->startOfDay());

    $this->actingAs($seller)
        ->post(route('seller.my-properties.ppat-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ppat_meeting_date' => '2026-10-28',
        ])
        ->assertRedirectBackWithErrors([
            'ppat_meeting_date' => 'Tanggal temu PPAT tidak boleh di masa lalu.',
        ]);

    expect($appointment->fresh()->ppat_meeting_date)->toBeNull();
});

it('does not schedule a PPAT meeting when the buyer is not proceeding with the deal', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $ppat = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_NO_DEAL,
    ]);

    $this->actingAs($seller)
        ->from(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->post(route('seller.my-properties.ppat-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ppat_meeting_date' => '2026-11-15',
        ])
        ->assertRedirectBackWithErrors([
            'ppat_meeting_date' => 'Jadwal temu PPAT hanya dapat diajukan setelah pembeli berminat melanjutkan transaksi.',
        ]);

    expect($appointment->fresh()->ppat_meeting_date)->toBeNull();
});

it('does not schedule a PPAT meeting before a PPAT is assigned to the property', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
    ]);

    $this->actingAs($seller)
        ->from(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->post(route('seller.my-properties.ppat-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ppat_meeting_date' => '2026-11-15',
        ])
        ->assertRedirectBackWithErrors([
            'ppat_meeting_date' => 'Properti ini belum memiliki PPAT yang ditugaskan.',
        ]);

    expect($appointment->fresh()->ppat_meeting_date)->toBeNull();
});

it('forbids another seller from scheduling a PPAT meeting', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('User');
    $ppat = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSellerDetailProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createSellerDetailAppointment($seller, $buyer, $property, [
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
    ]);

    $this->actingAs($otherSeller)
        ->post(route('seller.my-properties.ppat-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ppat_meeting_date' => '2026-11-15',
        ])
        ->assertForbidden();

    expect($appointment->fresh()->ppat_meeting_date)->toBeNull();
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

it('shows the edit link only while a property awaits verification', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $pendingProperty = createSellerDetailProperty($seller, ['status' => 'pending_verification']);
    $publishedProperty = createSellerDetailProperty($seller, ['status' => 'published']);
    $rejectedProperty = createSellerDetailProperty($seller, ['status' => 'rejected']);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', ['property' => $pendingProperty->uuid]))
        ->assertSeeText('Edit')
        ->assertSee('href="'.route('seller.my-properties.edit', ['property' => $pendingProperty->uuid]).'"', false);

    $this->get(route('seller.my-properties.show', ['property' => $publishedProperty->uuid]))
        ->assertDontSee('href="'.route('seller.my-properties.edit', ['property' => $publishedProperty->uuid]).'"', false);

    $this->get(route('seller.my-properties.show', ['property' => $rejectedProperty->uuid]))
        ->assertDontSee('href="'.route('seller.my-properties.edit', ['property' => $rejectedProperty->uuid]).'"', false);
});

it('lets the owner update a pending property without changing its approval status', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $property = createSellerDetailProperty($seller, [
        'status' => 'pending_verification',
        'category' => 'rent',
        'rent_period' => 'bulan',
        'province_code' => '32',
        'city_code' => '32.73',
        'district_code' => '32.73.01',
    ]);
    $editUrl = route('seller.my-properties.edit', ['property' => $property->uuid]);

    $response = $this->actingAs($seller)
        ->get($editUrl)
        ->assertOk()
        ->assertSeeText('Edit properti')
        ->assertSeeText('Informasi properti')
        ->assertSeeText('Foto & dokumen legal')
        ->assertSeeText('Lokasi properti')
        ->assertSee('name="building_type"', false)
        ->assertSee('name="transaction_type"', false)
        ->assertSee('name="province_code"', false)
        ->assertSee('name="url_maps"', false)
        ->assertSee('value="'.$property->title.'"', false)
        ->assertViewHas('initialForm', fn (array $initialForm): bool => $initialForm['property_type'] === 'bangunan'
            && $initialForm['building_type'] === 'rumah'
            && $initialForm['transaction_type'] === 'disewakan'
            && $initialForm['price'] === '1.500.000.000'
            && $initialForm['bedrooms'] === '3'
            && $initialForm['province_code'] === '32'
            && $initialForm['city_code'] === '32.73'
            && $initialForm['district_code'] === '32.73.01');

    expect(substr_count($response->getContent(), '<section class="wizard-card">'))->toBe(3)
        ->and(substr_count($response->getContent(), 'action="'.route('seller.my-properties.update', ['property' => $property->uuid]).'"'))
        ->toBe(1);

    $this->put(route('seller.my-properties.update', ['property' => $property->uuid]), [
        'property_type' => 'bangunan',
        'building_type' => 'rumah',
        'transaction_type' => 'dijual',
        'rent_period' => '',
        'title' => 'Rumah Detail Penjual Diperbarui',
        'description' => 'Deskripsi properti yang telah diperbarui.',
        'price' => '1.800.000.000',
        'negotiable' => '1',
        'land_area_sqm' => 140,
        'building_area_sqm' => 95,
        'bedrooms' => '4',
        'bathrooms' => '3',
        'floors' => '2',
        'condition' => 'siap_huni',
        'front_width' => 8,
        'land_contour' => '',
        'road_access' => '',
        'zone_type' => '',
        'certificate_type' => 'SHM',
        'province' => 'Jawa Barat',
        'province_code' => '32',
        'city' => 'Cimahi',
        'city_code' => '32.77',
        'district' => 'Cimahi Utara',
        'district_code' => '32.77.01',
        'postal_code' => '40512',
        'address' => 'Jl. Baru No. 2',
        'latitude' => '',
        'longitude' => '',
        'url_maps' => '',
    ])
        ->assertRedirect(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertSessionHas('status', 'Perubahan properti berhasil disimpan.');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'title' => 'Rumah Detail Penjual Diperbarui',
        'price' => 1800000000,
        'negotiable' => true,
        'city' => 'Cimahi',
        'status' => 'pending_verification',
        'rent_period' => null,
        'province_code' => '32',
        'city_code' => '32.77',
        'district_code' => '32.77.01',
    ]);
});

it('updates land details and clears fields that only apply to buildings', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $property = createSellerDetailProperty($seller, [
        'status' => 'pending_verification',
        'building_area_sqm' => 90,
        'rooms' => '3',
        'bathrooms' => '2',
        'floors' => '2',
        'condition' => 'siap_huni',
    ]);

    $this->actingAs($seller)
        ->put(route('seller.my-properties.update', ['property' => $property->uuid]), [
            'property_type' => 'tanah',
            'land_type' => 'kavling',
            'transaction_type' => 'dijual',
            'title' => 'Tanah kavling strategis',
            'description' => 'Tanah siap bangun dengan akses jalan utama.',
            'price' => '750.000.000',
            'negotiable' => '0',
            'land_area_sqm' => 500,
            'front_width' => 20,
            'land_contour' => 'datar',
            'road_access' => 'jalan_utama',
            'zone_type' => 'perumahan',
            'certificate_type' => 'SHM',
            'province' => 'Jawa Barat',
            'city' => 'Bandung',
            'district' => 'Coblong',
            'postal_code' => '40132',
            'address' => 'Jl. Utama No. 10',
        ])
        ->assertRedirect(route('seller.my-properties.show', ['property' => $property->uuid]));

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'property_type' => 'tanah',
        'type' => 'kavling',
        'category' => 'sale',
        'price' => 750000000,
        'building_area_sqm' => null,
        'rooms' => null,
        'bathrooms' => null,
        'floors' => null,
        'condition' => null,
        'status' => 'pending_verification',
    ]);
});

it('clears stale location codes when a city is changed without selecting a region', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $property = createSellerDetailProperty($seller, [
        'status' => 'pending_verification',
        'province_code' => '32',
        'city_code' => '32.73',
        'district_code' => '32.73.01',
    ]);

    $this->actingAs($seller)
        ->put(route('seller.my-properties.update', ['property' => $property->uuid]), [
            'property_type' => 'bangunan',
            'type' => 'rumah',
            'category' => 'sale',
            'title' => 'Rumah Detail Penjual',
            'description' => 'Deskripsi lengkap properti untuk pengujian halaman detail penjual.',
            'price' => 1500000000,
            'negotiable' => '1',
            'land_area_sqm' => 120,
            'certificate_type' => 'SHM',
            'province' => 'Jawa Barat',
            'city' => 'Cimahi',
            'district' => 'Cimahi Utara',
            'address' => 'Jl. Merdeka No. 1',
        ])
        ->assertRedirect(route('seller.my-properties.show', ['property' => $property->uuid]));

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'province_code' => '32',
        'city' => 'Cimahi',
        'city_code' => null,
        'district_code' => null,
    ]);
});

it('forbids editing an approved property or a property owned by another seller', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('User');
    $approvedProperty = createSellerDetailProperty($seller, ['status' => 'published']);
    $pendingProperty = createSellerDetailProperty($seller, ['status' => 'pending_verification']);

    $this->actingAs($seller)
        ->get(route('seller.my-properties.edit', ['property' => $approvedProperty->uuid]))
        ->assertForbidden();

    $this->put(route('seller.my-properties.update', ['property' => $approvedProperty->uuid]), [
        'title' => 'Judul tidak boleh diperbarui',
    ])->assertForbidden();

    $this->actingAs($otherSeller)
        ->get(route('seller.my-properties.edit', ['property' => $pendingProperty->uuid]))
        ->assertForbidden();

    $this->put(route('seller.my-properties.update', ['property' => $pendingProperty->uuid]), [
        'title' => 'Judul milik penjual lain',
    ])->assertForbidden();

    expect($approvedProperty->fresh()->title)->toBe('Rumah Detail Penjual')
        ->and($pendingProperty->fresh()->title)->toBe('Rumah Detail Penjual');
});

it('keeps a pending property unchanged when required edit data is invalid', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $property = createSellerDetailProperty($seller, ['status' => 'pending_verification']);

    $this->actingAs($seller)
        ->from(route('seller.my-properties.edit', ['property' => $property->uuid]))
        ->put(route('seller.my-properties.update', ['property' => $property->uuid]), [
            'title' => '',
            'price' => '-100',
        ])
        ->assertRedirect(route('seller.my-properties.edit', ['property' => $property->uuid]))
        ->assertSessionHasErrors(['title', 'price']);

    $this->get(route('seller.my-properties.edit', ['property' => $property->uuid]))
        ->assertViewHas('initialForm', fn (array $initialForm): bool => blank($initialForm['title'])
            && $initialForm['price'] === '-100');

    $this->from(route('seller.my-properties.edit', ['property' => $property->uuid]))
        ->put(route('seller.my-properties.update', ['property' => $property->uuid]), [
            'price' => '',
        ])
        ->assertSessionHasErrors(['price']);

    $this->get(route('seller.my-properties.edit', ['property' => $property->uuid]))
        ->assertViewHas('initialForm', fn (array $initialForm): bool => $initialForm['price'] === null);

    expect($property->fresh()->title)->toBe('Rumah Detail Penjual');
});
