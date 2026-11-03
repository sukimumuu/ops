<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Services\Payments\DpAmountCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
                'seller:id,name,email,phone,avatar_path',
                'property:id,title,address,city,district,certificate_type,url_maps',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $calendarBookings = $appointments->map(fn (Appointment $appointment): array => [
            'id' => $appointment->id,
            'counterpart_name' => $appointment->seller->name,
            'counterpart_email' => $appointment->seller->email,
            'counterpart_phone' => $appointment->seller->phone,
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
            'buyer_intent_key' => $appointment->buyer_intent,
            'buyer_intent_label' => $appointment->buyerIntentLabel(),
            'result_notes' => $appointment->result_notes,
            'result_recorded_at' => $appointment->completed_at
                ? $appointment->completed_at->locale('id')->translatedFormat('l, d F Y, H:i').' WIB'
                : null,
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

    public function boughtProperties(Request $request): View
    {
        $user = $request->user();

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
            ->where(function (Builder $query) use ($user): void {
                $query->whereHas('transactions', fn (Builder $q): Builder => $q->where('buyer_id', $user->id))
                    ->orWhereHas('appointments', fn (Builder $q): Builder => $q->where('buyer_id', $user->id));
            })
            ->with(['photos' => fn ($query) => $query->limit(1)])
            ->withExists([
                'appointments as has_requested_survey' => fn (Builder $query): Builder => $query->where('buyer_id', $user->id)->where('status', Appointment::STATUS_REQUESTED),
                'appointments as has_confirmed_survey' => fn (Builder $query): Builder => $query->where('buyer_id', $user->id)->where('status', Appointment::STATUS_CONFIRMED),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = '%'.$search.'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('city', 'like', $term)
                        ->orWhere('district', 'like', $term);
                });
            })
            ->when($status !== '', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($category !== '', fn (Builder $query): Builder => $query->where('category', $category))
            ->when($propertyType !== '', fn (Builder $query): Builder => $query->where('property_type', $propertyType));

        $totalProperties = (clone $properties)->count();

        match ($sort) {
            'oldest' => $properties->orderBy('created_at')->orderBy('id'),
            'price_asc' => $properties->orderBy('price')->orderByDesc('id'),
            'price_desc' => $properties->orderByDesc('price')->orderByDesc('id'),
            default => $properties->orderByDesc('created_at')->orderByDesc('id'),
        };

        $properties = $properties->paginate(12)->withQueryString();

        $properties->getCollection()->transform(function (Property $property): Property {
            $property->setAttribute('image_url', $property->primaryPhotoUrl());
            $property->setAttribute('survey_status', match (true) {
                $property->has_requested_survey => Appointment::STATUS_REQUESTED,
                $property->has_confirmed_survey => Appointment::STATUS_CONFIRMED,
                default => null,
            });

            return $property;
        });

        $hasFilters = $search !== '' || $status !== '' || $category !== '' || $propertyType !== '';

        return view('dashboard.user.bought-properties', compact(
            'properties',
            'totalProperties',
            'hasFilters',
            'search',
            'status',
            'category',
            'propertyType',
            'sort',
            'statusOptions',
            'categoryOptions',
            'propertyTypeOptions',
            'sortOptions',
        ));
    }

    public function showBoughtProperty(
        Request $request,
        Property $property,
        DpAmountCalculator $amountCalculator,
    ): View {
        $user = $request->user();

        $hasAccess = $property->appointments()->where('buyer_id', $user->id)->exists()
            || $property->transactions()->where('buyer_id', $user->id)->exists();

        abort_unless($hasAccess, 403, 'Anda tidak memiliki akses ke properti ini.');

        $property->load([
            'photos',
            'assignedPpat:id,name,email,phone,avatar_path',
            'seller:id,name,email,phone,avatar_path',
        ]);

        $appointments = $property->appointments()
            ->where('buyer_id', $user->id)
            ->with(['seller:id,name,email,phone,avatar_path'])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        $transaction = $property->transactions()
            ->where('buyer_id', $user->id)
            ->latest('id')
            ->first();
        $dpPaymentAttempt = $transaction?->dpPaymentAttempts()->latest('id')->first();
        $requiredDpAmount = $transaction?->state === 'draft'
            ? $amountCalculator->calculate($transaction->total_property_amount)
            : null;

        $latestAppointment = $appointments->first();
        $selectedAppointment = $appointments->firstWhere('id', $request->integer('appointment'))
            ?? $latestAppointment;

        $progress = $latestAppointment?->progressState() ?? [
            'label' => 'Belum ada pengajuan survei',
            'description' => 'Progres survei akan muncul setelah Anda mengajukan jadwal kunjungan.',
            'percentage' => 0,
            'tone' => 'slate',
            'completed_steps' => 0,
            'stopped' => true,
        ];

        if ($latestAppointment) {
            if ($latestAppointment->hasPendingReschedule()) {
                $progress['description'] = 'Penjual menyarankan jadwal pengganti. Silakan tinjau jadwal tersebut.';
            } elseif ($latestAppointment->status === Appointment::STATUS_REQUESTED) {
                $progress['description'] = 'Permintaan jadwal survei Anda telah dikirim dan menunggu persetujuan penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_CONFIRMED) {
                $progress['description'] = 'Jadwal survei Anda telah disetujui penjual. Silakan datang sesuai jadwal yang telah ditentukan.';
            } elseif ($latestAppointment->status === Appointment::STATUS_COMPLETED) {
                $progress['description'] = 'Survei telah selesai dilaksanakan dan hasil telah dicatat.';
            }
        }

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

        return view('dashboard.user.property-detail', compact(
            'property',
            'appointments',
            'latestAppointment',
            'selectedAppointment',
            'transaction',
            'dpPaymentAttempt',
            'requiredDpAmount',
            'progress',
            'mapUrl',
            'surveyStats',
        ));
    }
}
