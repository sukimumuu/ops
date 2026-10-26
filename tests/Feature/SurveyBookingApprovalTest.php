<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['Admin', 'Superadmin', 'PPAT', 'User'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

function createSurveyBookingProperty(User $seller, ?User $ppat = null): Property
{
    return Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'assigned_ppat_id' => $ppat?->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah untuk survei',
        'description' => 'Properti untuk pengujian persetujuan survei.',
        'price' => 1250000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Merdeka No. 1',
        'url_maps' => 'https://www.openstreetmap.org/?mlat=-6.9175&mlon=107.6191#map=16/-6.9175/107.6191',
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'certificate_type' => 'SHM',
    ]);
}

function createSurveyBooking(User $seller, User $buyer, Property $property, string $status = 'requested'): Appointment
{
    return Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->addDays(2),
        'status' => $status,
    ]);
}

it('allows a seller to approve a survey booking request directly', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertRedirect(route('seller.request-survey'))
        ->assertSessionHas('status', 'Permintaan survei berhasil disetujui.');

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('allows a seller to reject a survey booking request with a suggested schedule', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $suggestedDate = now()->addDays(4);

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment), [
            'status' => 'rejected',
            'suggested_date' => $suggestedDate->format('Y-m-d'),
            'suggested_time' => '13:00',
        ])
        ->assertRedirect(route('seller.request-survey'))
        ->assertSessionHas('status', 'Permintaan survei ditolak. Jadwal pengganti telah dikirim ke pembeli untuk dikonfirmasi.');

    $appointment->refresh();

    expect($appointment->status)->toBe('rejected')
        ->and($appointment->reschedule_suggested_at->format('Y-m-d H:i'))->toBe($suggestedDate->format('Y-m-d').' 13:00');
});

it('requires a suggested schedule when a seller rejects a survey booking request', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment), ['status' => 'rejected'])
        ->assertRedirect(route('seller.request-survey'))
        ->assertSessionHasErrors(['suggested_date', 'suggested_time']);

    expect($appointment->fresh()->status)->toBe('requested')
        ->and($appointment->fresh()->reschedule_suggested_at)->toBeNull();
});

it('always associates a survey booking with the property owner', function () {
    $owner = User::factory()->create();
    $otherSeller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($owner);

    $appointment = createSurveyBooking($otherSeller, $buyer, $property);

    expect($appointment->seller_id)->toBe($owner->id)
        ->and($appointment->seller->is($owner))->toBeTrue()
        ->and($appointment->property->is($property))->toBeTrue()
        ->and($appointment->buyer->is($buyer))->toBeTrue();
});

it('prevents a PPAT from reviewing a survey booking request', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($ppat)
        ->from(route('land-deed.index'))
        ->post(route('appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe('requested');
});

it('prevents a seller from reviewing another seller booking', function () {
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($otherSeller)
        ->post(route('appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe('requested');
});

it('prevents buyers from reviewing a survey booking', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($buyer)
        ->post(route('appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe('requested');
});

it('does not allow a survey booking to be reviewed twice', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'confirmed');

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment), ['status' => 'rejected', 'suggested_date' => now()->addDay()->format('Y-m-d'), 'suggested_time' => '09:00'])
        ->assertRedirect(route('seller.request-survey'))
        ->assertSessionHasErrors('status');

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('rejects unsupported review statuses', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment), ['status' => 'cancelled'])
        ->assertSessionHasErrors('status');

    expect($appointment->fresh()->status)->toBe('requested');
});

it('shows the seller calendar with status indicators and review actions', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);
    $appointment->update(['scheduled_at' => '2026-10-20 10:00:00']);

    $this->actingAs($seller)
        ->get(route('seller.request-survey', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Kalender permintaan survei')
        ->assertSee('Menunggu persetujuan')
        ->assertSee('bg-amber-500', false)
        ->assertSee('ring-amber-300', false)
        ->assertSee('Setujui survei')
        ->assertSee('Tolak & atur jadwal')
        ->assertSee('name="suggested_date"', false)
        ->assertSee('Kirim jadwal pengganti')
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment, $buyer, $property): bool {
            $booking = $bookings->firstWhere('id', $appointment->id);

            return $booking !== null
                && $booking['status_key'] === 'requested'
                && $booking['status'] === 'Menunggu persetujuan'
                && $booking['can_review'] === true
                && $booking['counterpart_name'] === $appointment->buyer->name
                && $booking['counterpart_email'] === $buyer->email
                && $booking['counterpart_phone'] === $buyer->phone
                && $booking['property_maps_url'] === $property->url_maps;
        });
});

it('shows the survey calendar on the seller dashboard', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create(['name' => 'Pembeli Dashboard Penjual']);
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);
    $appointment->update(['scheduled_at' => '2026-10-20 10:00:00']);

    $this->actingAs($seller)
        ->get(route('seller.listing', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Kalender survei properti')
        ->assertSee('Pembeli Dashboard Penjual')
        ->assertSee('Setujui survei')
        ->assertViewHas('calendarBookings', fn ($bookings): bool => $bookings->firstWhere('id', $appointment->id) !== null);
});

it('hides the survey calendar from the PPAT dashboard', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($ppat)
        ->get(route('land-deed.index'))
        ->assertOk()
        ->assertSee('Kalender survei dinonaktifkan sementara')
        ->assertDontSee('Pilih avatar untuk membuka rincian survei.')
        ->assertDontSee('Setujui survei');
});

it('shows only survey requests belonging to the signed-in seller', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('User');
    $buyer = User::factory()->create(['name' => 'Calon Pembeli Sendiri']);
    $otherBuyer = User::factory()->create(['name' => 'Calon Pembeli Lain']);

    $property = createSurveyBookingProperty($seller);
    $otherProperty = createSurveyBookingProperty($otherSeller);
    createSurveyBooking($seller, $buyer, $property);
    createSurveyBooking($otherSeller, $otherBuyer, $otherProperty);

    $this->actingAs($seller)
        ->get(route('seller.request-survey'))
        ->assertOk()
        ->assertSee('Calon Pembeli Sendiri')
        ->assertDontSee('Calon Pembeli Lain')
        ->assertSee('Setujui')
        ->assertSee('Tolak')
        ->assertViewHas('pendingSurveyCount', 1);
});

it('shows buyers a calendar and upcoming list for their own survey bookings only', function () {
    $buyer = User::factory()->create(['name' => 'Pembeli Kalender']);
    $buyer->assignRole('User');
    $seller = User::factory()->create(['name' => 'Penjual Kalender']);
    $otherSeller = User::factory()->create(['name' => 'Penjual Lain']);
    $otherBuyer = User::factory()->create();

    $month = now()->startOfMonth();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);
    $appointment->update(['scheduled_at' => $month->copy()->addDays(2)->setTime(10, 0)]);

    $otherProperty = createSurveyBookingProperty($otherSeller);
    createSurveyBooking($otherSeller, $otherBuyer, $otherProperty)
        ->update(['scheduled_at' => $month->copy()->addDays(3)->setTime(11, 0)]);

    $this->actingAs($buyer)
        ->get(route('buyer.survey-schedule', ['month' => $month->format('Y-m')]))
        ->assertOk()
        ->assertSee('Jadwal survei mendatang')
        ->assertSee('Penjual Kalender')
        ->assertDontSee('Penjual Lain')
        ->assertSee('bg-amber-500', false)
        ->assertDontSee('Setujui survei')
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment, $seller, $property): bool {
            return $bookings->count() === 1
                && $bookings->first()['id'] === $appointment->id
                && $bookings->first()['status_key'] === 'requested'
                && $bookings->first()['seller_name'] === 'Penjual Kalender'
                && $bookings->first()['counterpart_email'] === $seller->email
                && $bookings->first()['counterpart_phone'] === $seller->phone
                && $bookings->first()['property_maps_url'] === $property->url_maps;
        });
});

it('shows the survey result and reason note on the seller calendar', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'completed');
    $appointment->update([
        'scheduled_at' => '2026-10-20 10:00:00',
        'buyer_intent' => Appointment::BUYER_INTENT_NO_DEAL,
        'result_notes' => 'Calon pembeli memilih tidak melanjutkan pembelian.',
        'completed_at' => now(),
    ]);

    $this->actingAs($seller)
        ->get(route('seller.listing', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Hasil survei')
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment): bool {
            $booking = $bookings->firstWhere('id', $appointment->id);

            return $booking !== null
                && $booking['buyer_intent_key'] === Appointment::BUYER_INTENT_NO_DEAL
                && $booking['buyer_intent_label'] === 'Tidak melanjutkan pembelian'
                && $booking['result_notes'] === 'Calon pembeli memilih tidak melanjutkan pembelian.'
                && $booking['result_recorded_at'] !== null;
        });
});

it('shows the survey result and reason note on the buyer calendar', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'completed');
    $appointment->update([
        'scheduled_at' => '2026-10-20 10:00:00',
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'result_notes' => 'Calon pembeli tertarik dan akan melanjutkan diskusi harga.',
        'completed_at' => now(),
    ]);

    $this->actingAs($buyer)
        ->get(route('buyer.survey-schedule', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Hasil survei')
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment): bool {
            $booking = $bookings->firstWhere('id', $appointment->id);

            return $booking !== null
                && $booking['buyer_intent_key'] === Appointment::BUYER_INTENT_DEAL
                && $booking['buyer_intent_label'] === 'Berminat membeli'
                && $booking['result_notes'] === 'Calon pembeli tertarik dan akan melanjutkan diskusi harga.'
                && $booking['result_recorded_at'] !== null;
        });
});

it('scopes the buyer survey calendar to the selected month', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $property = createSurveyBookingProperty($seller);

    $nextMonth = now()->addMonthNoOverflow()->startOfMonth();
    $inside = createSurveyBooking($seller, $buyer, $property);
    $inside->update(['scheduled_at' => $nextMonth->copy()->addDays(2)->setTime(9, 0)]);

    $outside = createSurveyBooking($seller, $buyer, $property);
    $outside->update(['scheduled_at' => $nextMonth->copy()->addMonthNoOverflow()->addDays(2)->setTime(9, 0)]);

    $this->actingAs($buyer)
        ->get(route('buyer.survey-schedule', ['month' => $nextMonth->format('Y-m')]))
        ->assertOk()
        ->assertViewHas('calendarBookings', function ($bookings) use ($inside, $outside): bool {
            $ids = $bookings->pluck('id')->all();

            return $ids === [$inside->id] && ! in_array($outside->id, $ids, true);
        });
});

it('lists only upcoming requested and confirmed surveys in the buyer schedule list', function () {
    $buyer = User::factory()->create();
    $buyer->assignRole('User');
    $seller = User::factory()->create();
    $property = createSurveyBookingProperty($seller);

    $upcomingRequested = createSurveyBooking($seller, $buyer, $property, 'requested');
    $upcomingRequested->update(['scheduled_at' => now()->addDays(3)]);

    $upcomingConfirmed = createSurveyBooking($seller, $buyer, $property, 'confirmed');
    $upcomingConfirmed->update(['scheduled_at' => now()->addDays(5)]);

    $rejected = createSurveyBooking($seller, $buyer, $property, 'rejected');
    $rejected->update(['scheduled_at' => now()->addDays(4)]);

    $past = createSurveyBooking($seller, $buyer, $property, 'completed');
    $past->update(['scheduled_at' => now()->subDays(3)]);

    $this->actingAs($buyer)
        ->get(route('buyer.survey-schedule'))
        ->assertOk()
        ->assertSee('Disetujui')
        ->assertViewHas('upcomingSurveys', function ($surveys) use ($upcomingRequested, $upcomingConfirmed, $rejected, $past): bool {
            $ids = $surveys->pluck('id')->all();

            return count($ids) === 2
                && in_array($upcomingRequested->id, $ids, true)
                && in_array($upcomingConfirmed->id, $ids, true)
                && ! in_array($rejected->id, $ids, true)
                && ! in_array($past->id, $ids, true);
        });
});

it('lets a buyer accept a suggested reschedule and confirms the appointment', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'rejected');

    $suggestedAt = now()->addDays(4)->setTime(15, 30);
    $appointment->update(['reschedule_suggested_at' => $suggestedAt]);

    $this->actingAs($buyer)
        ->from(route('buyer.survey-schedule'))
        ->post(route('buyer.survey-schedule.reschedule', $appointment), ['decision' => 'accepted'])
        ->assertRedirect(route('buyer.survey-schedule'))
        ->assertSessionHas('status', 'Jadwal pengganti disetujui. Jadwal survei telah dikonfirmasi.');

    $appointment->refresh();

    expect($appointment->status)->toBe('confirmed')
        ->and($appointment->scheduled_at->format('Y-m-d H:i'))->toBe($suggestedAt->format('Y-m-d H:i'))
        ->and($appointment->reschedule_suggested_at)->toBeNull();
});

it('does not send an accepted reschedule back to the seller review queue', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'rejected');
    $appointment->update(['reschedule_suggested_at' => now()->addDays(4)->setTime(15, 30)]);

    $this->actingAs($buyer)
        ->post(route('buyer.survey-schedule.reschedule', $appointment), ['decision' => 'accepted']);

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment->fresh()), ['status' => 'confirmed'])
        ->assertSessionHasErrors('status');

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('lets a buyer decline a suggested reschedule', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'rejected');
    $appointment->update(['reschedule_suggested_at' => now()->addDays(4)->setTime(9, 0)]);

    $this->actingAs($buyer)
        ->from(route('buyer.survey-schedule'))
        ->post(route('buyer.survey-schedule.reschedule', $appointment), ['decision' => 'declined'])
        ->assertRedirect(route('buyer.survey-schedule'))
        ->assertSessionHas('status', 'Jadwal pengganti ditolak. Silakan ajukan jadwal survei baru.');

    $appointment->refresh();

    expect($appointment->status)->toBe('rejected')
        ->and($appointment->reschedule_suggested_at)->toBeNull();
});

it('prevents other buyers from responding to a reschedule suggestion', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $otherBuyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'rejected');
    $appointment->update(['reschedule_suggested_at' => now()->addDays(4)->setTime(9, 0)]);

    $this->actingAs($otherBuyer)
        ->post(route('buyer.survey-schedule.reschedule', $appointment), ['decision' => 'accepted'])
        ->assertForbidden();

    expect($appointment->fresh()->status)->toBe('rejected')
        ->and($appointment->fresh()->reschedule_suggested_at)->not->toBeNull();
});

it('shows the suggested reschedule to the buyer', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'rejected');

    $suggestedAt = now()->addDays(5)->setTime(15, 30);
    $appointment->update(['reschedule_suggested_at' => $suggestedAt]);

    $this->actingAs($buyer)
        ->get(route('buyer.survey-schedule', ['month' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee('Persetujuan jadwal pengganti')
        ->assertSee('Saran jadwal baru')
        ->assertSee('Setujui jadwal')
        ->assertSee('Penjual mengusulkan')
        ->assertSee('selectedAppointment.has_reschedule', false)
        ->assertSee('selectedAppointment.reschedule_url', false)
        ->assertSee($suggestedAt->locale('id')->translatedFormat('d M Y'))
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment): bool {
            $booking = $bookings->firstWhere('id', $appointment->id);

            return $booking !== null
                && $booking['has_reschedule'] === true
                && $booking['reschedule_url'] === route('buyer.survey-schedule.reschedule', $appointment);
        })
        ->assertViewHas('rescheduleRequests', function ($requests) use ($appointment): bool {
            return $requests->count() === 1 && $requests->first()->id === $appointment->id;
        });
});
