<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

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

    public function myProperties(Request $request): View
    {
        $statusOptions = [
            'draft' => 'Draft',
            'pending_verification' => 'Menunggu Verifikasi',
            'published' => 'Terbit',
            'reserved' => 'Dipesan',
            'sold' => 'Terjual',
            'archived' => 'Diarsipkan',
            'rejected' => 'Ditolak',
        ];
        $categoryOptions = [
            'sale' => 'Dijual',
            'rent' => 'Disewakan',
        ];
        $propertyTypeOptions = [
            'bangunan' => 'Bangunan',
            'tanah' => 'Tanah',
        ];
        $sortOptions = [
            'newest' => 'Terbaru',
            'oldest' => 'Terlama',
            'price_asc' => 'Harga Terendah',
            'price_desc' => 'Harga Tertinggi',
        ];

        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $category = (string) $request->query('category', '');
        $propertyType = (string) $request->query('property_type', '');
        $sort = (string) $request->query('sort', 'newest');

        $status = array_key_exists($status, $statusOptions) ? $status : '';
        $category = array_key_exists($category, $categoryOptions) ? $category : '';
        $propertyType = array_key_exists($propertyType, $propertyTypeOptions) ? $propertyType : '';
        $sort = array_key_exists($sort, $sortOptions) ? $sort : 'newest';

        $properties = Property::query()
            ->where('seller_id', $request->user()->id)
            ->with(['photos' => fn ($query) => $query->limit(1)])
            ->withExists([
                'appointments as has_requested_survey' => fn (Builder $query): Builder => $query->where('status', Appointment::STATUS_REQUESTED),
                'appointments as has_confirmed_survey' => fn (Builder $query): Builder => $query->where('status', Appointment::STATUS_CONFIRMED),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('district', 'like', $term)
                        ->orWhere('province', 'like', $term);
                });
            })
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($category !== '', fn (Builder $query): Builder => $query->where('category', $category))
            ->when($propertyType !== '', fn (Builder $query): Builder => $query->where('property_type', $propertyType));

        match ($sort) {
            'oldest' => $properties->orderBy('created_at')->orderBy('id'),
            'price_asc' => $properties->orderBy('price')->orderByDesc('id'),
            'price_desc' => $properties->orderByDesc('price')->orderByDesc('id'),
            default => $properties->orderByDesc('created_at')->orderByDesc('id'),
        };

        $properties = $properties->paginate(8)->withQueryString();

        $properties->getCollection()->each(function (Property $property): void {
            $property->setAttribute('image_url', $property->primaryPhotoUrl());
            $property->setAttribute('survey_status', match (true) {
                (bool) $property->has_requested_survey => Appointment::STATUS_REQUESTED,
                (bool) $property->has_confirmed_survey => Appointment::STATUS_CONFIRMED,
                default => 'none',
            });
        });

        $totalProperties = Property::query()
            ->where('seller_id', $request->user()->id)
            ->count();

        $hasFilters = $search !== ''
            || $status !== ''
            || $category !== ''
            || $propertyType !== '';

        return view('dashboard.listing.my-properties', compact(
            'properties',
            'totalProperties',
            'search',
            'status',
            'category',
            'propertyType',
            'sort',
            'hasFilters',
            'statusOptions',
            'categoryOptions',
            'propertyTypeOptions',
            'sortOptions',
        ));
    }

    public function showProperty(Request $request, Property $property): View
    {
        Gate::authorize('view', $property);

        $property->load(['photos', 'assignedPpat:id,name,email']);

        $appointments = $property->appointments()
            ->with(['buyer:id,name,email,phone,avatar_path'])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        $latestAppointment = $appointments->first();
        $selectedAppointment = $appointments->firstWhere('id', $request->integer('appointment'))
            ?? $latestAppointment;

        $progress = $latestAppointment?->progressState() ?? [
            'label' => 'Belum ada pengajuan survei',
            'description' => 'Progres survei akan muncul setelah calon pembeli mengajukan jadwal kunjungan.',
            'percentage' => 0,
            'tone' => 'slate',
            'completed_steps' => 0,
            'stopped' => true,
        ];

        $property->setAttribute('image_url', $property->primaryPhotoUrl());

        $mapLocation = filled($property->latitude) && filled($property->longitude)
            ? "{$property->latitude},{$property->longitude}"
            : trim("{$property->address}, {$property->district}, {$property->city}, {$property->province}");
        $mapUrl = filled($property->url_maps)
            ? $property->url_maps
            : 'https://www.google.com/maps/search/?api=1&query='.urlencode($mapLocation);

        $surveyStats = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', Appointment::STATUS_COMPLETED)->count(),
            'confirmed' => $appointments->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'pending' => $appointments->where('status', Appointment::STATUS_REQUESTED)->count(),
        ];

        return view('dashboard.listing.property-detail', compact(
            'property',
            'appointments',
            'latestAppointment',
            'selectedAppointment',
            'progress',
            'mapUrl',
            'surveyStats',
        ));
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
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type,url_maps',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $calendarBookings = $appointments->map(fn (Appointment $appointment): array => [
            'id' => $appointment->id,
            'counterpart_name' => $appointment->buyer->name,
            'counterpart_email' => $appointment->buyer->email,
            'counterpart_phone' => $appointment->buyer->phone,
            'avatar_url' => $appointment->buyer->avatar_url,
            'seller_name' => $appointment->seller->name,
            'buyer_name' => $appointment->buyer->name,
            'date' => $appointment->scheduled_at->locale('id')->translatedFormat('l, d F Y'),
            'time' => $appointment->scheduled_at->format('H:i'),
            'reschedule_date' => $appointment->reschedule_suggested_at?->locale('id')->translatedFormat('l, d F Y'),
            'reschedule_time' => $appointment->reschedule_suggested_at?->format('H:i'),
            'status_key' => $appointment->status,
            'status' => $appointment->statusLabel(),
            'buyer_intent_key' => $appointment->buyer_intent,
            'buyer_intent_label' => $appointment->buyerIntentLabel(),
            'result_notes' => $appointment->result_notes,
            'result_recorded_at' => $appointment->completed_at
                ? $appointment->completed_at->locale('id')->translatedFormat('l, d F Y, H:i').' WIB'
                : null,
            'can_review' => $seller->can('review', $appointment),
            'status_url' => route('appointments.status', $appointment),
            'notes' => $appointment->notes,
            'property_title' => $appointment->property->title,
            'property_certificate' => $appointment->property->certificate_type,
            'property_address' => $appointment->property->address,
            'property_location' => $appointment->property->district.', '.$appointment->property->city,
            'property_maps_url' => $appointment->property->url_maps,
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
