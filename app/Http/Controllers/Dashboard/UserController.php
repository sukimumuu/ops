<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function savedProperties(Request $request): View
    {
        $sort = (string) $request->query('sort', 'newest');
        $sort = in_array($sort, ['newest', 'oldest'], true) ? $sort : 'newest';
        $direction = $sort === 'oldest' ? 'asc' : 'desc';

        $savedProperties = $request->user()
            ->savedProperties()
            ->with(['photos' => fn ($query) => $query->limit(1)])
            ->orderByPivot('created_at', $direction)
            ->orderByPivot('id', $direction)
            ->get();

        $savedProperties->each(function (Property $property): void {
            $property->setAttribute('image_url', $property->primaryPhotoUrl());
        });

        return view('dashboard.user.saved-properti', compact('savedProperties', 'sort'));
    }

    public function saveProperty(Request $request, string $uuid): RedirectResponse
    {
        $property = Property::query()
            ->where('uuid', $uuid)
            ->where('status', 'published')
            ->firstOrFail();

        $request->user()->savedProperties()->syncWithoutDetaching([$property->id]);

        return back()->with('success', 'Properti berhasil disimpan.');
    }

    public function unsaveProperty(Request $request, string $uuid): RedirectResponse
    {
        $property = Property::query()->where('uuid', $uuid)->firstOrFail();

        $request->user()->savedProperties()->detach($property->id);

        return back()->with('success', 'Properti dihapus dari daftar tersimpan.');
    }

    public function surveySchedule(Request $request): View
    {
        $buyer = $request->user();

        $validated = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->where('buyer_id', $buyer->id)
            ->whereBetween('scheduled_at', [$monthStart, $monthEnd])
            ->with([
                'seller:id,name,avatar_path',
                'property:id,title,address,city,district,certificate_type',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $calendarBookings = $appointments->map(fn (Appointment $appointment): array => [
            'id' => $appointment->id,
            'counterpart_name' => $appointment->seller->name,
            'avatar_url' => $appointment->seller->avatar_url,
            'seller_name' => $appointment->seller->name,
            'buyer_name' => $appointment->buyer?->name,
            'date' => $appointment->scheduled_at->locale('id')->translatedFormat('l, d F Y'),
            'time' => $appointment->scheduled_at->format('H:i'),
            'reschedule_date' => $appointment->reschedule_suggested_at?->locale('id')->translatedFormat('l, d F Y'),
            'reschedule_time' => $appointment->reschedule_suggested_at?->format('H:i'),
            'has_reschedule' => $appointment->hasPendingReschedule(),
            'reschedule_url' => route('buyer.survey-schedule.reschedule', $appointment),
            'status_key' => $appointment->status,
            'status' => $appointment->statusLabel(),
            'notes' => $appointment->notes,
            'property_title' => $appointment->property->title,
            'property_certificate' => $appointment->property->certificate_type,
            'property_address' => $appointment->property->address,
            'property_location' => $appointment->property->district.', '.$appointment->property->city,
        ])->values();

        $appointmentsByDate = $appointments->groupBy(fn (Appointment $appointment): string => $appointment->scheduled_at->toDateString());
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);
        $calendarDays = CarbonPeriod::create($calendarStart, $calendarEnd);

        $rescheduleRequests = Appointment::query()
            ->where('buyer_id', $buyer->id)
            ->where('status', Appointment::STATUS_REJECTED)
            ->whereNotNull('reschedule_suggested_at')
            ->with([
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type',
            ])
            ->orderBy('reschedule_suggested_at')
            ->get();

        $upcomingSurveys = Appointment::query()
            ->where('buyer_id', $buyer->id)
            ->whereIn('status', [Appointment::STATUS_REQUESTED, Appointment::STATUS_CONFIRMED])
            ->where('scheduled_at', '>=', now())
            ->with(['property:id,title,city,province', 'seller:id,name'])
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get(['id', 'property_id', 'seller_id', 'scheduled_at', 'status']);

        return view('dashboard.user.survey-schedule', compact(
            'appointments',
            'appointmentsByDate',
            'calendarBookings',
            'calendarDays',
            'month',
            'upcomingSurveys',
            'rescheduleRequests',
        ));
    }

    public function transactionEscrow()
    {
        return view('dashboard.user.transaction-and-escrow');
    }
}
