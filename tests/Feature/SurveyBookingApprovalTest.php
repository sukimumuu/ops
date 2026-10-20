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

it('allows the assigned PPAT to approve a survey booking request', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($ppat)
        ->from(route('land-deed.index', ['month' => now()->format('Y-m')]))
        ->post(route('land-deed.appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertRedirect(route('land-deed.index', ['month' => now()->format('Y-m')]))
        ->assertSessionHas('status', 'Permintaan survei berhasil disetujui.');

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('allows the assigned PPAT to reject a survey booking request', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($ppat)
        ->from(route('land-deed.index'))
        ->post(route('appointments.status', $appointment), ['status' => 'rejected'])
        ->assertRedirect(route('land-deed.index'))
        ->assertSessionHas('status', 'Permintaan survei berhasil ditolak.');

    expect($appointment->fresh()->status)->toBe('rejected');
});

it('allows a seller to review a survey booking for their own property', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($seller)
        ->from(route('seller.request-survey'))
        ->post(route('appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertRedirect(route('seller.request-survey'));

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('prevents a PPAT from reviewing a booking assigned to another PPAT', function () {
    $assignedPpat = User::factory()->create();
    $assignedPpat->assignRole('PPAT');
    $otherPpat = User::factory()->create();
    $otherPpat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $assignedPpat);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($otherPpat)
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
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    $appointment = createSurveyBooking($seller, $buyer, $property, 'confirmed');

    $this->actingAs($ppat)
        ->from(route('land-deed.index'))
        ->post(route('land-deed.appointments.status', $appointment), ['status' => 'rejected'])
        ->assertRedirect(route('land-deed.index'))
        ->assertSessionHasErrors('status');

    expect($appointment->fresh()->status)->toBe('confirmed');
});

it('rejects unsupported review statuses', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    $appointment = createSurveyBooking($seller, $buyer, $property);

    $this->actingAs($ppat)
        ->from(route('land-deed.index'))
        ->post(route('land-deed.appointments.status', $appointment), ['status' => 'cancelled'])
        ->assertSessionHasErrors('status');

    expect($appointment->fresh()->status)->toBe('requested');
});

it('shows status-based indicators and review actions on the calendar', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    $property = createSurveyBookingProperty($seller, $ppat);
    $appointment = createSurveyBooking($seller, $buyer, $property);
    $appointment->update(['scheduled_at' => '2026-10-20 10:00:00']);

    $this->actingAs($ppat)
        ->get(route('land-deed.index', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Menunggu persetujuan')
        ->assertSee('bg-amber-500', false)
        ->assertSee('ring-amber-300', false)
        ->assertSee('Setujui survei')
        ->assertViewHas('calendarBookings', function ($bookings) use ($appointment): bool {
            $booking = $bookings->firstWhere('id', $appointment->id);

            return $booking !== null
                && $booking['status_key'] === 'requested'
                && $booking['status'] === 'Menunggu persetujuan'
                && $booking['can_review'] === true;
        });
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
