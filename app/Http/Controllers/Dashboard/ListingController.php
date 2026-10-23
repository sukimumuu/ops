<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sellerName = $user->name;

        $sellerStats = [
            'totalProperties' => Property::query()
                ->where('seller_id', $user->id)
                ->count(),
            'publishedProperties' => Property::query()
                ->where('seller_id', $user->id)
                ->where('status', 'published')
                ->count(),
            'pendingSurveys' => Appointment::query()
                ->where('seller_id', $user->id)
                ->where('status', 'requested')
                ->count(),
            'activeTransactions' => Transaction::query()
                ->where('seller_id', $user->id)
                ->whereIn('state', ['held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled'])
                ->count(),
        ];

        $recentProperties = Property::query()
            ->where('seller_id', $user->id)
            ->select(['id', 'title', 'city', 'province', 'price', 'status', 'created_at'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentSurveys = Appointment::query()
            ->where('seller_id', $user->id)
            ->where('status', 'requested')
            ->with(['property:id,title', 'buyer:id,name'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'property_id', 'buyer_id', 'scheduled_at', 'created_at']);

        $recentTransactions = Transaction::query()
            ->where('seller_id', $user->id)
            ->with(['property:id,title,city,province', 'buyer:id,name', 'seller:id,name'])
            ->latest()
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'property_id', 'buyer_id', 'seller_id', 'total_property_amount', 'booking_fee_amount', 'state', 'created_at']);

        $properties = Property::query()
            ->where('seller_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('dashboard.listing.index', array_merge([
            'sellerName' => $sellerName,
            'sellerStats' => $sellerStats,
            'recentProperties' => $recentProperties,
            'recentSurveys' => $recentSurveys,
            'recentTransactions' => $recentTransactions,
            'properties' => $properties,
        ], $this->surveyCalendarData($request)));
    }

    public function myProperties()
    {
        $properties = Property::where('seller_id', Auth::user()->id)->paginate(10);

        return view('dashboard.listing.my-properties', compact('properties'));
    }

    public function requestSurvey(Request $request): View
    {
        $user = $request->user();

        $appointments = Appointment::query()
            ->where('seller_id', $user->id)
            ->with([
                'property:id,title,city,province,address',
                'buyer:id,name,email',
            ])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->paginate(10);

        $pendingSurveyCount = Appointment::query()
            ->where('seller_id', $user->id)
            ->where('status', Appointment::STATUS_REQUESTED)
            ->count();

        $surveyCalendar = $this->surveyCalendarData($request);

        return view('dashboard.listing.request-survey', [
            'appointments' => $appointments,
            'pendingSurveyCount' => $pendingSurveyCount,
            'calendarAppointments' => $surveyCalendar['appointments'],
            'appointmentsByDate' => $surveyCalendar['appointmentsByDate'],
            'calendarBookings' => $surveyCalendar['calendarBookings'],
            'calendarDays' => $surveyCalendar['calendarDays'],
            'month' => $surveyCalendar['month'],
        ]);
    }

    /**
     * Build the seller survey calendar for the selected month.
     *
     * @return array{appointments: \Illuminate\Database\Eloquent\Collection<int, Appointment>, appointmentsByDate: Collection<string, \Illuminate\Database\Eloquent\Collection<int, Appointment>>, calendarBookings: Collection<int, array<string, mixed>>, calendarDays: CarbonPeriod, month: Carbon}
     */
    private function surveyCalendarData(Request $request): array
    {
        $seller = $request->user();

        $validated = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $appointments = Appointment::query()
            ->where('seller_id', $seller->id)
            ->whereBetween('scheduled_at', [$monthStart, $monthEnd])
            ->with([
                'buyer:id,name,avatar_path',
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $calendarBookings = $appointments->map(fn (Appointment $appointment): array => [
            'id' => $appointment->id,
            'counterpart_name' => $appointment->buyer->name,
            'avatar_url' => $appointment->buyer->avatar_url,
            'seller_name' => $appointment->seller->name,
            'buyer_name' => $appointment->buyer->name,
            'date' => $appointment->scheduled_at->locale('id')->translatedFormat('l, d F Y'),
            'time' => $appointment->scheduled_at->format('H:i'),
            'reschedule_date' => $appointment->reschedule_suggested_at?->locale('id')->translatedFormat('l, d F Y'),
            'reschedule_time' => $appointment->reschedule_suggested_at?->format('H:i'),
            'status_key' => $appointment->status,
            'status' => $appointment->statusLabel(),
            'can_review' => $seller->can('review', $appointment),
            'status_url' => route('appointments.status', $appointment),
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

        return compact('appointments', 'appointmentsByDate', 'calendarBookings', 'calendarDays', 'month');
    }

    public function sellingTransaction()
    {
        return view('dashboard.listing.selling-transaction');
    }

    public function disbursementAccount()
    {
        return view('dashboard.listing.disbursement-account');
    }
}
