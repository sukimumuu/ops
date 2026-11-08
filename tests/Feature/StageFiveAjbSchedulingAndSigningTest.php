<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'appointments.confirm_or_reject',
        'appointments.view_all',
        'appointments.request',
        'properties.view_assigned',
        'documents.verify_status',
        'documents.view_sensitive_pii',
        'transactions.view_all',
        'transactions.mark_bpn_cleared',
        'transactions.schedule_ajb',
        'transactions.view_own_tracker',
        'transactions.initiate_dp',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'PPAT', 'guard_name' => 'web'])
        ->syncPermissions([
            'appointments.view_all',
            'properties.view_assigned',
            'documents.verify_status',
            'documents.view_sensitive_pii',
            'transactions.view_all',
            'transactions.mark_bpn_cleared',
            'transactions.schedule_ajb',
        ]);

    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web'])
        ->syncPermissions([
            'appointments.confirm_or_reject',
            'appointments.request',
            'transactions.view_own_tracker',
            'transactions.initiate_dp',
        ]);
});

function createTestProperty(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah Tes Stage 5 AJB',
        'description' => 'Deskripsi rumah tes untuk penandatanganan AJB.',
        'price' => 1000000000,
        'negotiable' => true,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Dago No. 100',
        'land_area_sqm' => 150,
        'building_area_sqm' => 120,
        'rooms' => '4',
        'bathrooms' => '3',
        'floors' => '2',
        'certificate_type' => 'SHM',
    ], $attributes));
}

function createTestAppointment(User $seller, User $buyer, Property $property, array $attributes = []): Appointment
{
    return Appointment::query()->create(array_merge([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subDays(5),
        'completed_at' => now()->subDays(4),
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->subDays(3)->format('Y-m-d'),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_CONFIRMED,
    ], $attributes));
}

function createTestTransaction(User $buyer, User $seller, Property $property, string $state = 'held_in_escrow'): Transaction
{
    return Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 100000000,
        'total_property_amount' => $property->price,
        'state' => $state,
    ]);
}

it('locks Stage 5 until Stage 4 escrow payment is completed', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');

    $property = createTestProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createTestAppointment($seller, $buyer, $property);
    // Transaction in bpn_cleared (Stage 4 not yet paid)
    $transaction = createTestTransaction($buyer, $seller, $property, 'bpn_cleared');

    // On Seller Property Detail page:
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Tahap ini terkunci sampai pembayaran pelunasan properti (escrow) terkonfirmasi.')
        ->assertDontSeeText('Ajukan Jadwal Temu PPAT untuk AJB');

    // On PPAT Client Property Detail page:
    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Tahap ini terkunci sampai pelunasan properti (escrow) terkonfirmasi.');

    // Backend prevents submitting meeting request if escrow payment not complete
    $this->actingAs($seller)
        ->post(route('seller.my-properties.ajb-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ajb_meeting_date' => now()->addWeek()->format('Y-m-d'),
            'ajb_meeting_time' => '09:00',
        ])
        ->assertSessionHasErrors(['ajb_meeting_date']);

    expect($appointment->fresh()->ajb_meeting_date)->toBeNull();
});

it('allows seller to submit AJB meeting request after escrow payment is confirmed', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');

    $property = createTestProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createTestAppointment($seller, $buyer, $property);
    $transaction = createTestTransaction($buyer, $seller, $property, 'held_in_escrow');

    $meetingDate = now()->addDays(7)->format('Y-m-d');

    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Ajukan Jadwal Temu PPAT untuk AJB');

    $this->actingAs($seller)
        ->post(route('seller.my-properties.ajb-meetings.store', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'ajb_meeting_date' => $meetingDate,
            'ajb_meeting_time' => '13:00',
            'ajb_notes' => 'Harap disiapkan berkas akta jual beli.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $refreshed = $appointment->fresh();
    expect($refreshed->ajb_meeting_date->format('Y-m-d'))->toBe($meetingDate)
        ->and($refreshed->ajb_meeting_time)->toBe('13:00')
        ->and($refreshed->ajb_meeting_status)->toBe(Appointment::AJB_MEETING_STATUS_REQUESTED)
        ->and($refreshed->ajb_notes)->toBe('Harap disiapkan berkas akta jual beli.');

    // Status is displayed on Seller page
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Menunggu persetujuan PPAT')
        ->assertSeeText('13:00 WIB');
});

it('displays AJB meeting on PPAT dashboard calendar and allows PPAT to approve it', function () {
    $seller = User::factory()->create(['name' => 'Budi Penjual']);
    $seller->assignRole('User');
    $buyer = User::factory()->create(['name' => 'Dewi Pembeli']);
    $buyer->assignRole('User');
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');

    $property = createTestProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createTestAppointment($seller, $buyer, $property, [
        'ajb_meeting_date' => now()->addDays(3)->format('Y-m-d'),
        'ajb_meeting_time' => '09:00',
        'ajb_meeting_status' => Appointment::AJB_MEETING_STATUS_REQUESTED,
        'ajb_notes' => 'Catatan penandatanganan AJB.',
    ]);
    $transaction = createTestTransaction($buyer, $seller, $property, 'held_in_escrow');

    // Calendar displays the booking
    $this->actingAs($ppat)
        ->get(route('land-deed.index', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment) {
            return $bookings->contains(fn ($b) => $b['id'] === 'ajb-meeting-'.$appointment->id
                && $b['status_key'] === 'ajb_meeting_requested'
                && $b['time'] === '09:00');
        });

    // PPAT approves the proposed schedule
    $this->actingAs($ppat)
        ->post(route('land-deed.ajb-meetings.update', $appointment), [
            'decision' => Appointment::AJB_MEETING_STATUS_CONFIRMED,
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Jadwal penandatanganan AJB berhasil disetujui.');

    expect($appointment->fresh()->ajb_meeting_status)->toBe(Appointment::AJB_MEETING_STATUS_CONFIRMED);

    // Seller now sees confirmed status
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Jadwal disetujui PPAT');
});

it('allows PPAT to reject the AJB meeting request', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');

    $property = createTestProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createTestAppointment($seller, $buyer, $property, [
        'ajb_meeting_date' => now()->addDays(3)->format('Y-m-d'),
        'ajb_meeting_time' => '13:00',
        'ajb_meeting_status' => Appointment::AJB_MEETING_STATUS_REQUESTED,
    ]);
    $transaction = createTestTransaction($buyer, $seller, $property, 'held_in_escrow');

    $this->actingAs($ppat)
        ->post(route('land-deed.ajb-meetings.update', $appointment), [
            'decision' => Appointment::AJB_MEETING_STATUS_REJECTED,
            'ajb_notes' => 'Hari tersebut kantor sedang ada renovasi.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Jadwal penandatanganan AJB telah ditolak.');

    $refreshed = $appointment->fresh();
    expect($refreshed->ajb_meeting_status)->toBe(Appointment::AJB_MEETING_STATUS_REJECTED)
        ->and($refreshed->ajb_notes)->toBe('Hari tersebut kantor sedang ada renovasi.');

    // Seller sees rejected status and can resubmit a new schedule
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Jadwal ditolak PPAT')
        ->assertSeeText('Perbarui Jadwal AJB');
});

it('allows PPAT to reschedule and seller can accept the reschedule proposal', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');

    $property = createTestProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createTestAppointment($seller, $buyer, $property, [
        'ajb_meeting_date' => now()->addDays(3)->format('Y-m-d'),
        'ajb_meeting_time' => '09:00',
        'ajb_meeting_status' => Appointment::AJB_MEETING_STATUS_REQUESTED,
    ]);
    $transaction = createTestTransaction($buyer, $seller, $property, 'held_in_escrow');

    $newDate = now()->addDays(6)->format('Y-m-d');

    // PPAT reschedules to new date and time
    $this->actingAs($ppat)
        ->post(route('land-deed.ajb-meetings.update', $appointment), [
            'decision' => Appointment::AJB_MEETING_STATUS_RESCHEDULED,
            'ajb_suggested_date' => $newDate,
            'ajb_suggested_time' => '15:30',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $refreshed = $appointment->fresh();
    expect($refreshed->ajb_meeting_status)->toBe(Appointment::AJB_MEETING_STATUS_RESCHEDULED)
        ->and($refreshed->ajb_suggested_date->format('Y-m-d'))->toBe($newDate)
        ->and($refreshed->ajb_suggested_time)->toBe('15:30');

    // Seller sees rescheduling proposal and can accept
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('PPAT menyarankan jadwal pengganti')
        ->assertSeeText('15:30 WIB')
        ->assertSeeText('Setujui Jadwal Pengganti');

    // Seller accepts the reschedule
    $this->actingAs($seller)
        ->post(route('seller.my-properties.ajb-meetings.respond', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'decision' => 'accepted',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Jadwal pengganti AJB disetujui. Jadwal penandatanganan telah dikonfirmasi.');

    $refreshedAfterAccept = $appointment->fresh();
    expect($refreshedAfterAccept->ajb_meeting_status)->toBe(Appointment::AJB_MEETING_STATUS_CONFIRMED)
        ->and($refreshedAfterAccept->ajb_meeting_date->format('Y-m-d'))->toBe($newDate)
        ->and($refreshedAfterAccept->ajb_meeting_time)->toBe('15:30')
        ->and($refreshedAfterAccept->ajb_suggested_date)->toBeNull();
});

it('allows PPAT to submit AJB signing evidence, transitions state to ajb_scheduled, and completes Stage 5', function () {
    Storage::fake('public');

    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');

    $property = createTestProperty($seller, ['assigned_ppat_id' => $ppat->id]);
    $appointment = createTestAppointment($seller, $buyer, $property, [
        'ajb_meeting_date' => now()->addDays(2)->format('Y-m-d'),
        'ajb_meeting_time' => '13:00',
        'ajb_meeting_status' => Appointment::AJB_MEETING_STATUS_CONFIRMED,
    ]);
    $transaction = createTestTransaction($buyer, $seller, $property, 'held_in_escrow');

    // PPAT client property detail page shows upload form
    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Unggah Bukti Penandatanganan AJB');

    // PPAT uploads the AJB signing evidence
    $file = UploadedFile::fake()->create('akta-jual-beli-no-123.pdf', 500, 'application/pdf');

    $this->actingAs($ppat)
        ->post(route('land-deed.ajb-evidence.store', [
            'property' => $property->uuid,
        ]), [
            'ajb_document' => $file,
            'ajb_notes' => 'Nomor Akta: AJB/2026/XI/001.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Bukti penandatanganan Akta Jual Beli (AJB) berhasil diunggah. Tahap 5 selesai.');

    $refreshedAppointment = $appointment->fresh();
    $refreshedTransaction = $transaction->fresh();

    expect($refreshedAppointment->ajb_document_path)->not->toBeNull()
        ->and($refreshedAppointment->ajb_signed_at)->not->toBeNull()
        ->and($refreshedTransaction->state)->toBe('ajb_scheduled')
        ->and($refreshedTransaction->ajb_document_path)->toBe($refreshedAppointment->ajb_document_path)
        ->and($refreshedTransaction->ajb_signed_at)->not->toBeNull();

    Storage::disk('public')->assertExists($refreshedAppointment->ajb_document_path);

    // Transaction log recorded
    expect($refreshedTransaction->logs()->where('to_state', 'ajb_scheduled')->exists())->toBeTrue();

    // Check Seller Property Detail page displays uploaded evidence & completion
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Akta Jual Beli Selesai Ditandatangani')
        ->assertSeeText('Lihat Dokumen Akta Jual Beli (AJB)');

    // Check PPAT Client Property Detail page displays uploaded evidence & completion
    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties.show', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]))
        ->assertOk()
        ->assertSeeText('Akta Jual Beli Selesai Ditandatangani')
        ->assertSeeText('Lihat Bukti Akta Jual Beli (AJB)');
});
