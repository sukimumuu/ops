<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\EscrowDisbursement;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ListingController extends Controller
{
    /**
     * Number of stages in the sale transaction pipeline.
     */
    private const TRANSACTION_STAGE_COUNT = 7;

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
                ->whereIn('state', ['dp_paid', 'held_in_escrow', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled', Transaction::STATE_CERTIFICATE_TRANSFER_PENDING])
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

        $property->load([
            'photos',
            'documents.validatedBy:id,name',
            'assignedPpat:id,name,email',
        ]);

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

        $transaction = $property->transactions()
            ->with('certificateTransferSubmittedBy:id,name')
            ->latest('id')
            ->first();
        $escrowDisbursement = $transaction?->latestEscrowDisbursement();
        $isStageSevenUnlocked = $escrowDisbursement?->status === EscrowDisbursement::STATUS_RELEASED;
        $escrowReleaseAmount = $transaction?->ledgerEntries()
            ->where('status', TransactionLedgerEntry::STATUS_RESERVED)
            ->sum('amount') ?? 0;
        $dpPaymentAttempt = $transaction?->latestDpPaymentAttempt();
        $escrowPaymentAttempt = $transaction?->latestEscrowPaymentAttempt();
        $remainingEscrowAmount = $transaction?->remainingEscrowAmount();
        $isStageFourUnlocked = $transaction?->isStageFourUnlocked() ?? false;
        $isEscrowPaid = $transaction?->hasCompletedEscrowPayment() ?? false;
        $bpnProgress = $property->bpnVerificationProgress();
        $isStageThreeUnlocked = $transaction?->hasCompletedDownPayment() ?? false;

        return view('dashboard.listing.property-detail', compact(
            'property',
            'appointments',
            'latestAppointment',
            'selectedAppointment',
            'progress',
            'mapUrl',
            'surveyStats',
            'transaction',
            'escrowDisbursement',
            'isStageSevenUnlocked',
            'escrowReleaseAmount',
            'dpPaymentAttempt',
            'escrowPaymentAttempt',
            'remainingEscrowAmount',
            'bpnProgress',
            'isStageThreeUnlocked',
            'isStageFourUnlocked',
            'isEscrowPaid',
        ));
    }

    public function editProperty(Property $property): View
    {
        Gate::authorize('update', $property);

        $property->load(['photos', 'certificate', 'supportingDocuments']);
        $oldInput = session()->get('_old_input', []);

        $initialForm = [
            'property_type' => old('property_type', $property->property_type),
            'building_type' => old('building_type', old('type', $property->property_type === 'bangunan' ? $property->type : '')),
            'land_type' => old('land_type', old('type', $property->property_type === 'tanah' ? $property->type : '')),
            'transaction_type' => old('transaction_type', old('category', $property->category) === 'sale' ? 'dijual' : 'disewakan'),
            'rent_period' => old('rent_period', $property->rent_period),
            'title' => old('title', $property->title),
            'description' => old('description', $property->description),
            'price' => is_array($oldInput) && array_key_exists('price', $oldInput)
                ? old('price')
                : number_format((float) $property->price, 0, ',', '.'),
            'negotiable' => old('negotiable', $property->negotiable),
            'land_area_sqm' => old('land_area_sqm', $property->land_area_sqm),
            'building_area_sqm' => old('building_area_sqm', $property->building_area_sqm),
            'bedrooms' => old('bedrooms', old('rooms', $property->rooms)),
            'bathrooms' => old('bathrooms', $property->bathrooms),
            'floors' => old('floors', $property->floors),
            'condition' => old('condition', $property->condition),
            'front_width' => old('front_width', $property->front_width),
            'land_contour' => old('land_contour', $property->land_contour),
            'road_access' => old('road_access', $property->road_access),
            'zone_type' => old('zone_type', $property->zone_type),
            'province' => old('province', $property->province),
            'province_code' => old('province_code', $property->province_code),
            'city' => old('city', $property->city),
            'city_code' => old('city_code', $property->city_code),
            'district' => old('district', $property->district),
            'district_code' => old('district_code', $property->district_code),
            'postal_code' => old('postal_code', $property->postal_code),
            'address' => old('address', $property->address),
            'latitude' => old('latitude', $property->latitude),
            'longitude' => old('longitude', $property->longitude),
            'url_maps' => old('url_maps', $property->url_maps),
        ];

        return view('user.property-edit', compact('property', 'initialForm'));
    }

    public function updateProperty(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $validated = $request->validated();

        $provinceChanged = $property->province !== $validated['province'];
        $cityChanged = $property->city !== $validated['city'];
        $districtChanged = $property->district !== $validated['district'];

        if ($validated['category'] === 'sale') {
            $validated['rent_period'] = null;
        }

        if ($validated['property_type'] === 'bangunan') {
            $validated['front_width'] = null;
            $validated['land_contour'] = null;
            $validated['road_access'] = null;
            $validated['zone_type'] = null;
        } else {
            $validated['building_area_sqm'] = null;
            $validated['rooms'] = null;
            $validated['bathrooms'] = null;
            $validated['floors'] = null;
            $validated['condition'] = null;
        }

        $property->fill($validated);
        $property->negotiable = $request->boolean('negotiable');

        if ($provinceChanged) {
            if (! filled($validated['province_code'] ?? null) || $validated['province_code'] === $property->getOriginal('province_code')) {
                $property->province_code = null;
            }
        }

        if ($provinceChanged || $cityChanged) {
            if (! filled($validated['city_code'] ?? null) || $validated['city_code'] === $property->getOriginal('city_code')) {
                $property->city_code = null;
            }
        }

        if ($provinceChanged || $cityChanged || $districtChanged) {
            if (! filled($validated['district_code'] ?? null) || $validated['district_code'] === $property->getOriginal('district_code')) {
                $property->district_code = null;
            }
        }

        $property->save();

        return redirect()
            ->route('seller.my-properties.show', ['property' => $property->uuid])
            ->with('status', 'Perubahan properti berhasil disimpan.');
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

    /**
     * Show the selling transaction tracker for the authenticated seller.
     */
    public function sellingTransaction(Request $request): View
    {
        $user = $request->user();

        $stateOptions = [
            'draft' => 'Menunggu pembayaran DP',
            'dp_paid' => 'DP dibayar, dana ditahan',
            'bpn_checking' => 'Verifikasi BPN',
            'bpn_cleared' => 'BPN selesai',
            'held_in_escrow' => 'Dalam escrow',
            'ajb_scheduled' => 'Jadwal AJB',
            Transaction::STATE_CERTIFICATE_TRANSFER_PENDING => 'Menunggu balik nama sertifikat',
            Transaction::STATE_COMPLETED => 'Selesai',
            'refunded' => 'Dikembalikan',
        ];
        $sortOptions = [
            'newest' => 'Terbaru',
            'oldest' => 'Terlama',
            'value_desc' => 'Nilai tertinggi',
            'value_asc' => 'Nilai terendah',
        ];

        $search = trim((string) $request->query('search', ''));
        $state = (string) $request->query('state', '');
        $sort = (string) $request->query('sort', 'newest');

        $state = array_key_exists($state, $stateOptions) ? $state : '';
        $sort = array_key_exists($sort, $sortOptions) ? $sort : 'newest';

        $transactions = Transaction::query()
            ->where('seller_id', $user->id)
            ->with([
                'property:id,uuid,title,city,province,address',
                'buyer:id,name,email',
                'dpPaymentAttempts:id,transaction_id,payment_type,amount,status',
                'ledgerEntries:id,transaction_id,dp_payment_attempt_id,amount,status',
                'ledgerEntries.dpPaymentAttempt:id,payment_type,status,amount',
                'escrowDisbursements:id,transaction_id,amount,status,recipient_name,confirmed_at',
            ])
            ->when($search !== '', fn (Builder $query): Builder => $this->searchSellerTransactions($query, $search))
            ->when($state !== '', fn (Builder $query): Builder => $query->where('state', $state));

        match ($sort) {
            'oldest' => $transactions->orderBy('created_at')->orderBy('id'),
            'value_desc' => $transactions->orderByDesc('total_property_amount')->orderByDesc('id'),
            'value_asc' => $transactions->orderBy('total_property_amount')->orderByDesc('id'),
            default => $transactions->orderByDesc('created_at')->orderByDesc('id'),
        };

        $transactions = $transactions->paginate(10)->withQueryString();

        $transactions->getCollection()->each(function (Transaction $transaction): void {
            $this->hydrateTransactionSummary($transaction);
        });

        $sellerTransactions = Transaction::query()->where('seller_id', $user->id);
        $closedStates = [Transaction::STATE_COMPLETED, 'refunded'];
        $stats = [
            'total' => (clone $sellerTransactions)->count(),
            'active' => (clone $sellerTransactions)->whereNotIn('state', $closedStates)->count(),
            'awaiting_payment' => (clone $sellerTransactions)->where('state', 'draft')->count(),
            'completed' => (clone $sellerTransactions)->where('state', Transaction::STATE_COMPLETED)->count(),
            'active_value' => (float) (clone $sellerTransactions)
                ->whereNotIn('state', $closedStates)
                ->sum('total_property_amount'),
        ];

        $hasFilters = $search !== '' || $state !== '';

        return view('dashboard.listing.selling-transaction', [
            'transactions' => $transactions,
            'stats' => $stats,
            'search' => $search,
            'state' => $state,
            'sort' => $sort,
            'hasFilters' => $hasFilters,
            'stateOptions' => $stateOptions,
            'sortOptions' => $sortOptions,
            'totalStageCount' => self::TRANSACTION_STAGE_COUNT,
        ]);
    }

    /**
     * Attach the seller-facing summary attributes used by the transaction tracker.
     */
    private function hydrateTransactionSummary(Transaction $transaction): void
    {
        $ledgerDpAmount = (float) $transaction->ledgerEntries
            ->filter(fn (TransactionLedgerEntry $entry): bool => $entry->dpPaymentAttempt?->isDpPayment() === true)
            ->sum(fn (TransactionLedgerEntry $entry): float => (float) $entry->amount);

        $confirmedDpAmount = $ledgerDpAmount > 0
            ? $ledgerDpAmount
            : (float) $transaction->dpPaymentAttempts
                ->filter(fn (DpPaymentAttempt $attempt): bool => $attempt->isDpPayment()
                    && $attempt->status === DpPaymentAttempt::STATUS_PAID)
                ->sum(fn (DpPaymentAttempt $attempt): float => (float) $attempt->amount);

        $latestDisbursement = $transaction->escrowDisbursements->sortByDesc('id')->first();
        $stage = $this->transactionStage($transaction, $latestDisbursement);

        $transaction->setAttribute('confirmed_dp_amount', $confirmedDpAmount);
        $transaction->setAttribute(
            'remaining_amount',
            max(0, (float) $transaction->total_property_amount - $confirmedDpAmount),
        );
        $transaction->setAttribute('escrow_release_status', $latestDisbursement?->status);
        $transaction->setAttribute('current_stage', $stage['step']);
        $transaction->setAttribute('current_stage_label', $stage['label']);
        $transaction->setAttribute('current_stage_progress', $stage['progress']);
        $transaction->setAttribute('next_action', $stage['next_action']);
    }

    /**
     * Map a transaction state to the stage shown in the seller tracker.
     *
     * @return array{step: int, label: string, progress: int, next_action: string}
     */
    private function transactionStage(Transaction $transaction, ?EscrowDisbursement $latestDisbursement): array
    {
        $state = (string) $transaction->state;

        if ($state === 'ajb_scheduled' && $latestDisbursement?->status === EscrowDisbursement::STATUS_PENDING) {
            return [
                'step' => 6,
                'label' => 'Eksekusi pencairan escrow',
                'progress' => 85,
                'next_action' => 'Menunggu admin mengonfirmasi pencairan dana escrow kepada Anda.',
            ];
        }

        return match ($state) {
            'draft' => [
                'step' => 2,
                'label' => 'Pembayaran DP',
                'progress' => 30,
                'next_action' => 'Menunggu pembeli menyelesaikan pembayaran DP.',
            ],
            'dp_paid' => [
                'step' => 3,
                'label' => 'Pengecekan legalitas BPN & validasi pajak',
                'progress' => 45,
                'next_action' => 'DP diterima dan dana ditahan. Menunggu verifikasi legalitas BPN oleh PPAT.',
            ],
            'bpn_checking' => [
                'step' => 3,
                'label' => 'Pengecekan legalitas BPN & validasi pajak',
                'progress' => 50,
                'next_action' => 'PPAT sedang melakukan pengecekan legalitas BPN dan validasi pajak.',
            ],
            'bpn_cleared' => [
                'step' => 4,
                'label' => 'Pelunasan properti (escrow)',
                'progress' => 60,
                'next_action' => 'Menunggu pembeli menyelesaikan pelunasan escrow.',
            ],
            'held_in_escrow' => [
                'step' => 5,
                'label' => 'Penjadwalan & tanda tangan AJB',
                'progress' => 75,
                'next_action' => 'Dana pelunasan aman di escrow. Menunggu jadwal penandatanganan AJB.',
            ],
            'ajb_scheduled' => [
                'step' => 5,
                'label' => 'Penjadwalan & tanda tangan AJB',
                'progress' => 80,
                'next_action' => 'Menunggu PPAT mengunggah bukti AJB yang telah ditandatangani.',
            ],
            Transaction::STATE_CERTIFICATE_TRANSFER_PENDING => [
                'step' => 7,
                'label' => 'Balik nama sertifikat',
                'progress' => 90,
                'next_action' => 'AJB ditandatangani. Menunggu proses balik nama sertifikat di BPN.',
            ],
            Transaction::STATE_COMPLETED => [
                'step' => 7,
                'label' => 'Balik nama sertifikat',
                'progress' => 100,
                'next_action' => 'Transaksi selesai dan sertifikat telah dialihkan ke pembeli.',
            ],
            'refunded' => [
                'step' => 0,
                'label' => 'Dana dikembalikan',
                'progress' => 0,
                'next_action' => 'Transaksi dibatalkan dan dana telah dikembalikan ke pembeli.',
            ],
            default => [
                'step' => 1,
                'label' => 'Pilih jadwal temu PPAT',
                'progress' => 15,
                'next_action' => 'Menunggu jadwal temu PPAT dikonfirmasi.',
            ],
        };
    }

    private function searchSellerTransactions(Builder $query, string $search): Builder
    {
        $term = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->whereHas('property', fn (Builder $property): Builder => $property
                ->where('title', 'like', $term)
                ->orWhere('address', 'like', $term)
                ->orWhere('city', 'like', $term))
                ->orWhereHas('buyer', fn (Builder $buyer): Builder => $buyer->where('name', 'like', $term));
        });
    }

    public function disbursementAccount()
    {
        return view('dashboard.listing.disbursement-account');
    }
}
