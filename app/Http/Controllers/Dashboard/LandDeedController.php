<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\EscrowDisbursement;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\User;
use App\Notifications\AppointmentNotification;
use App\Services\Payments\DpAmountCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LandDeedController extends Controller
{
    /**
     * Page 1: Dashboard Antrian
     */
    public function index(Request $request): View
    {
        $ppat = $request->user();
        abort_unless($ppat instanceof User && $ppat->can('appointments.view_all'), 403);

        $validated = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
        ]);
        $month = Carbon::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $ppatMeetings = Appointment::query()
            ->whereHas('property', fn (Builder $query): Builder => $query->where('assigned_ppat_id', $ppat->id))
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('buyer_intent', Appointment::BUYER_INTENT_DEAL)
            ->whereNotNull('ppat_meeting_status')
            ->whereNotNull('ppat_meeting_date')
            ->whereBetween('ppat_meeting_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with([
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type,url_maps,assigned_ppat_id',
            ])
            ->get()
            ->map(function (Appointment $appointment): Appointment {
                $calendarAppointment = clone $appointment;
                $calendarAppointment->setAttribute(
                    'scheduled_at',
                    $appointment->ppat_meeting_date->copy()->startOfDay(),
                );
                $calendarAppointment->setAttribute('calendar_event_id', 'ppat-meeting-'.$appointment->id);
                $calendarAppointment->setAttribute('calendar_event_type', 'ppat_meeting');

                return $calendarAppointment;
            });

        $ajbMeetings = Appointment::query()
            ->whereHas('property', fn (Builder $query): Builder => $query->where('assigned_ppat_id', $ppat->id))
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('buyer_intent', Appointment::BUYER_INTENT_DEAL)
            ->whereNotNull('ajb_meeting_status')
            ->whereNotNull('ajb_meeting_date')
            ->whereBetween('ajb_meeting_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with([
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name',
                'property:id,title,address,city,district,certificate_type,url_maps,assigned_ppat_id',
            ])
            ->get()
            ->map(function (Appointment $appointment): Appointment {
                $calendarAppointment = clone $appointment;
                $calendarAppointment->setAttribute(
                    'scheduled_at',
                    $appointment->ajb_meeting_date->copy()->startOfDay(),
                );
                $calendarAppointment->setAttribute('calendar_event_id', 'ajb-meeting-'.$appointment->id);
                $calendarAppointment->setAttribute('calendar_event_type', 'ajb_meeting');

                return $calendarAppointment;
            });

        $calendarAppointments = $ppatMeetings->concat($ajbMeetings)->sortBy('scheduled_at')->values();

        $calendarBookings = $calendarAppointments->map(function (Appointment $appointment) use ($ppat): array {
            $isPpatMeeting = $appointment->calendar_event_type === 'ppat_meeting';
            $isAjbMeeting = $appointment->calendar_event_type === 'ajb_meeting';

            if ($isAjbMeeting) {
                return [
                    'id' => $appointment->calendar_event_id,
                    'event_type' => 'ajb_meeting',
                    'counterpart_name' => $appointment->buyer->name,
                    'counterpart_email' => $appointment->buyer->email,
                    'counterpart_phone' => $appointment->buyer->phone,
                    'avatar_url' => $appointment->buyer->avatar_url,
                    'seller_name' => $appointment->seller->name,
                    'date' => $appointment->ajb_meeting_date->locale('id')->translatedFormat('l, d F Y'),
                    'time' => $appointment->ajb_meeting_time,
                    'reschedule_date' => $appointment->ajb_suggested_date?->locale('id')->translatedFormat('l, d F Y'),
                    'reschedule_time' => $appointment->ajb_suggested_time,
                    'status_key' => 'ajb_meeting_'.$appointment->ajb_meeting_status,
                    'status' => $appointment->ajbMeetingStatusLabel(),
                    'buyer_intent_key' => $appointment->buyer_intent,
                    'buyer_intent_label' => $appointment->buyerIntentLabel(),
                    'result_notes' => $appointment->ajb_notes,
                    'result_recorded_at' => $appointment->ajb_signed_at
                        ? $appointment->ajb_signed_at->locale('id')->translatedFormat('l, d F Y, H:i').' WIB'
                        : null,
                    'can_review' => false,
                    'can_manage_meeting' => $appointment->ajb_meeting_status === Appointment::AJB_MEETING_STATUS_REQUESTED
                        && $ppat->can('manageAjbMeeting', $appointment),
                    'status_url' => null,
                    'ppat_meeting_url' => route('land-deed.ajb-meetings.update', $appointment),
                    'ppat_meeting_status' => $appointment->ajb_meeting_status,
                    'notes' => $appointment->ajb_notes,
                    'property_title' => $appointment->property->title,
                    'property_certificate' => $appointment->property->certificate_type,
                    'property_address' => $appointment->property->address,
                    'property_location' => $appointment->property->district.', '.$appointment->property->city,
                    'property_maps_url' => $appointment->property->url_maps,
                ];
            }

            return [
                'id' => $appointment->calendar_event_id,
                'event_type' => $appointment->calendar_event_type,
                'counterpart_name' => $appointment->buyer->name,
                'counterpart_email' => $appointment->buyer->email,
                'counterpart_phone' => $appointment->buyer->phone,
                'avatar_url' => $appointment->buyer->avatar_url,
                'seller_name' => $appointment->seller->name,
                'date' => $appointment->scheduled_at->locale('id')->translatedFormat('l, d F Y'),
                'time' => $isPpatMeeting ? null : $appointment->scheduled_at->format('H:i'),
                'reschedule_date' => $appointment->reschedule_suggested_at?->locale('id')->translatedFormat('l, d F Y'),
                'reschedule_time' => $appointment->reschedule_suggested_at?->format('H:i'),
                'status_key' => $isPpatMeeting
                    ? 'ppat_meeting_'.$appointment->ppat_meeting_status
                    : $appointment->status,
                'status' => $isPpatMeeting
                    ? $appointment->ppatMeetingStatusLabel()
                    : $appointment->statusLabel(),
                'buyer_intent_key' => $appointment->buyer_intent,
                'buyer_intent_label' => $appointment->buyerIntentLabel(),
                'result_notes' => $appointment->result_notes,
                'result_recorded_at' => $appointment->completed_at
                    ? $appointment->completed_at->locale('id')->translatedFormat('l, d F Y, H:i').' WIB'
                    : null,
                'can_review' => ! $isPpatMeeting && $ppat->can('review', $appointment),
                'can_manage_meeting' => $isPpatMeeting
                    && $appointment->ppat_meeting_status === Appointment::PPAT_MEETING_STATUS_REQUESTED
                    && $ppat->can('managePpatMeeting', $appointment),
                'status_url' => route('land-deed.appointments.status', $appointment),
                'ppat_meeting_url' => $isPpatMeeting
                    ? route('land-deed.ppat-meetings.update', $appointment)
                    : null,
                'ppat_meeting_status' => $isPpatMeeting ? $appointment->ppat_meeting_status : null,
                'notes' => $appointment->notes,
                'property_title' => $appointment->property->title,
                'property_certificate' => $appointment->property->certificate_type,
                'property_address' => $appointment->property->address,
                'property_location' => $appointment->property->district.', '.$appointment->property->city,
                'property_maps_url' => $appointment->property->url_maps,
            ];
        })->values();

        $appointmentsByDate = $calendarAppointments->groupBy(
            fn (Appointment $appointment): string => $appointment->scheduled_at->toDateString(),
        );
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);
        $calendarDays = CarbonPeriod::create($calendarStart, $calendarEnd);

        return view('dashboard.land-deed.index', compact(
            'ppatMeetings',
            'appointmentsByDate',
            'calendarBookings',
            'calendarDays',
            'month',
        ));
    }

    /**
     * Page: Daftar properti yang ditugaskan ke PPAT.
     */
    public function clientProperties(Request $request): View
    {
        $ppat = $request->user();
        abort_unless($ppat instanceof User && $ppat->can('appointments.view_all'), 403);

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
            ->where('assigned_ppat_id', $ppat->id)
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
                (bool) $property->has_requested_survey => Appointment::STATUS_REQUESTED,
                (bool) $property->has_confirmed_survey => Appointment::STATUS_CONFIRMED,
                default => null,
            });

            return $property;
        });

        $hasFilters = $search !== '' || $status !== '' || $category !== '' || $propertyType !== '';

        return view('dashboard.land-deed.client-properties', compact(
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

    /**
     * Page: Detail properti klien untuk PPAT.
     */
    public function clientDetailProperty(
        Request $request,
        Property $property,
        DpAmountCalculator $amountCalculator,
    ): View {
        $ppat = $request->user();

        abort_unless(
            $ppat instanceof User && $property->assigned_ppat_id === $ppat->id,
            403,
            'Anda tidak memiliki akses ke properti klien ini.',
        );

        $property->load([
            'photos',
            'documents.validatedBy:id,name',
            'assignedPpat:id,name,email,phone,avatar_path',
            'seller:id,name,email,phone,avatar_path',
        ]);

        $appointments = $property->appointments()
            ->with([
                'buyer:id,name,email,phone,avatar_path',
                'seller:id,name,email,phone,avatar_path',
            ])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        $transaction = $property->transactions()
            ->with(['buyer:id,name,email,phone,avatar_path'])
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
        $requiredDpAmount = $transaction?->state === 'draft'
            ? $amountCalculator->calculate($transaction->total_property_amount)
            : null;

        $latestAppointment = $appointments->first();
        $pendingPpatMeetingRequests = $appointments
            ->filter(fn (Appointment $appointment): bool => $appointment->status === Appointment::STATUS_COMPLETED
                && $appointment->buyer_intent === Appointment::BUYER_INTENT_DEAL
                && $appointment->ppat_meeting_status === Appointment::PPAT_MEETING_STATUS_REQUESTED
                && $appointment->ppat_meeting_date !== null)
            ->values();
        $selectedAppointment = $appointments->firstWhere('id', $request->integer('appointment'))
            ?? $pendingPpatMeetingRequests->first()
            ?? $latestAppointment;
        $canManagePpatMeeting = $pendingPpatMeetingRequests->isNotEmpty()
            && $ppat->can('managePpatMeeting', $pendingPpatMeetingRequests->first());
        $canManageAjbMeeting = $selectedAppointment !== null
            && $ppat->can('manageAjbMeeting', $selectedAppointment);

        $progress = $latestAppointment?->progressState() ?? [
            'label' => 'Belum ada pengajuan survei',
            'description' => 'Progres survei akan muncul setelah calon pembeli mengajukan jadwal kunjungan.',
            'percentage' => 0,
            'tone' => 'slate',
            'completed_steps' => 0,
            'stopped' => true,
        ];

        if ($latestAppointment) {
            if ($latestAppointment->hasPendingReschedule()) {
                $progress['description'] = 'Penjual menyarankan jadwal pengganti. Menunggu tanggapan calon pembeli.';
            } elseif ($latestAppointment->status === Appointment::STATUS_REQUESTED) {
                $progress['description'] = 'Permintaan jadwal survei klien menunggu persetujuan penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_CONFIRMED) {
                $progress['description'] = 'Jadwal survei klien telah disetujui penjual.';
            } elseif ($latestAppointment->status === Appointment::STATUS_COMPLETED) {
                $progress['description'] = 'Survei klien telah selesai dilaksanakan dan hasil telah dicatat.';
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

        $clientBuyer = $transaction?->buyer ?? $latestAppointment?->buyer;
        $bpnProgress = $property->bpnVerificationProgress();
        $isStageThreeUnlocked = $transaction?->hasCompletedDownPayment() ?? false;

        return view('dashboard.land-deed.client-detail-properties', compact(
            'property',
            'appointments',
            'latestAppointment',
            'selectedAppointment',
            'pendingPpatMeetingRequests',
            'canManagePpatMeeting',
            'canManageAjbMeeting',
            'transaction',
            'escrowDisbursement',
            'isStageSevenUnlocked',
            'escrowReleaseAmount',
            'dpPaymentAttempt',
            'escrowPaymentAttempt',
            'requiredDpAmount',
            'remainingEscrowAmount',
            'progress',
            'mapUrl',
            'surveyStats',
            'clientBuyer',
            'bpnProgress',
            'isStageThreeUnlocked',
            'isStageFourUnlocked',
            'isEscrowPaid',
        ));
    }

    /**
     * Page: Pengecekan Legalitas BPN & Validasi Pajak.
     */
    public function bpnLegalCheck(Request $request, Property $property): View
    {
        $ppat = $request->user();

        abort_unless(
            $ppat instanceof User && $property->assigned_ppat_id === $ppat->id,
            403,
            'Anda tidak memiliki akses ke properti klien ini.',
        );

        $property->load([
            'assignedPpat:id,name,email,phone,avatar_path',
            'seller:id,name,email,phone,avatar_path',
        ]);

        $documents = $property->documents()
            ->with('validatedBy:id,name')
            ->get();

        $transaction = $property->transactions()
            ->with(['buyer:id,name,email'])
            ->latest('id')
            ->first();

        abort_unless(
            $transaction?->hasCompletedDownPayment(),
            403,
            'Pengecekan BPN belum tersedia sebelum pembayaran DP berhasil dikonfirmasi.',
        );

        $metrics = [
            'total' => [
                'title' => 'Total Dokumen',
                'count' => $documents->count(),
                'unit' => 'dokumen',
                'description' => 'Dokumen legal yang diunggah penjual',
                'descriptionClass' => 'text-slate-500',
            ],
            'pending' => [
                'title' => 'Menunggu Validasi',
                'count' => $documents->where('validation_status', PropertyPhoto::VALIDATION_PENDING)->count(),
                'unit' => 'dokumen',
                'description' => 'Belum ditinjau PPAT',
                'descriptionClass' => 'text-slate-500',
            ],
            'approved' => [
                'title' => 'Disetujui',
                'count' => $documents->where('validation_status', PropertyPhoto::VALIDATION_APPROVED)->count(),
                'unit' => 'dokumen',
                'description' => 'Lolos pengecekan legalitas',
                'descriptionClass' => 'text-emerald-600 font-medium',
            ],
            'rejected' => [
                'title' => 'Ditolak',
                'count' => $documents->where('validation_status', PropertyPhoto::VALIDATION_REJECTED)->count(),
                'unit' => 'dokumen',
                'description' => 'Perlu perbaikan dari penjual',
                'descriptionClass' => 'text-rose-600 font-medium',
            ],
        ];

        $canReview = $ppat->can('documents.verify_status');

        return view('dashboard.land-deed.bpn-legal-check', compact(
            'property',
            'documents',
            'transaction',
            'metrics',
            'canReview',
        ));
    }

    /**
     * Review a legal document and advance the transaction's BPN state.
     */
    public function reviewDocument(Request $request, Property $property, PropertyPhoto $document): RedirectResponse
    {
        abort_unless($document->property_id === $property->id, 404);

        $ppat = $request->user();

        abort_unless(
            $ppat instanceof User && $property->assigned_ppat_id === $ppat->id,
            403,
            'Anda tidak memiliki akses ke dokumen properti ini.',
        );

        abort_unless(
            $property->transactions()->latest('id')->first()?->hasCompletedDownPayment(),
            403,
            'Validasi dokumen belum tersedia sebelum pembayaran DP berhasil dikonfirmasi.',
        );

        if ($document->isApproved()) {
            return back()->withErrors([
                'validation_status' => 'Dokumen yang telah disetujui bersifat final dan tidak dapat diubah.',
            ]);
        }

        $validated = $request->validate([
            'validation_status' => [
                'required',
                Rule::in([PropertyPhoto::VALIDATION_APPROVED, PropertyPhoto::VALIDATION_REJECTED]),
            ],
            'validation_notes' => ['nullable', 'string', 'max:2000', 'required_if:validation_status,rejected'],
        ]);

        DB::transaction(function () use ($property, $document, $ppat, $validated): void {
            $document->forceFill([
                'validation_status' => $validated['validation_status'],
                'validation_notes' => $validated['validation_notes'] ?? null,
                'validated_by_id' => $ppat->id,
                'validated_at' => now(),
            ])->save();

            $transaction = $property->transactions()->latest('id')->first();

            if (! $transaction instanceof Transaction) {
                return;
            }

            $currentState = (string) $transaction->state;

            if (in_array($currentState, ['dp_paid', 'held_in_escrow'], true)) {
                $this->moveTransactionTo(
                    $transaction,
                    'bpn_checking',
                    $ppat,
                    'Pengecekan legalitas BPN dimulai oleh PPAT.',
                );
                $currentState = 'bpn_checking';
            }

            $allDocumentsApproved = $property->documents()
                ->get()
                ->every(fn (PropertyPhoto $document): bool => $document->isApproved());

            if ($allDocumentsApproved && in_array($currentState, ['dp_paid', 'held_in_escrow', 'bpn_checking'], true)) {
                $this->moveTransactionTo(
                    $transaction,
                    'bpn_cleared',
                    $ppat,
                    'Seluruh dokumen legal disetujui. Pengecekan BPN selesai.',
                );
            } elseif (! $allDocumentsApproved && $currentState === 'bpn_cleared') {
                $this->moveTransactionTo(
                    $transaction,
                    'bpn_checking',
                    $ppat,
                    'Terdapat dokumen yang ditolak setelah BPN selesai. Pengecekan dibuka kembali.',
                );
            }
        });

        $message = $validated['validation_status'] === PropertyPhoto::VALIDATION_APPROVED
            ? 'Dokumen berhasil disetujui.'
            : 'Dokumen ditolak dan menunggu perbaikan.';

        return back()->with('status', $message);
    }

    /**
     * Upload AJB signing evidence by the PPAT.
     */
    public function uploadAjbEvidence(Request $request, Property $property): RedirectResponse
    {
        $ppat = $request->user();
        abort_unless($ppat instanceof User && ($ppat->can('appointments.view_all') || $property->assigned_ppat_id === $ppat->id), 403);

        $transaction = $property->transactions()->latest('id')->first();
        if (! $transaction || ! $transaction->hasCompletedEscrowPayment()) {
            return back()->withErrors([
                'ajb_document' => 'Pelunasan escrow belum selesai diverifikasi.',
            ]);
        }

        $appointment = $property->appointments()
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('buyer_intent', Appointment::BUYER_INTENT_DEAL)
            ->latest('id')
            ->first();

        if (! $appointment || $appointment->ajb_meeting_status !== Appointment::AJB_MEETING_STATUS_CONFIRMED) {
            return back()->withErrors([
                'ajb_document' => 'Jadwal penandatanganan AJB harus disetujui terlebih dahulu sebelum mengunggah bukti.',
            ]);
        }

        $validated = $request->validate([
            'ajb_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'ajb_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'ajb_document.required' => 'Pilih berkas bukti penandatanganan AJB.',
            'ajb_document.file' => 'Berkas yang diunggah tidak valid.',
            'ajb_document.mimes' => 'Format berkas bukti harus berupa PDF, JPG, JPEG, atau PNG.',
            'ajb_document.max' => 'Ukuran berkas bukti maksimal 10MB.',
        ]);

        $filePath = $request->file('ajb_document')->store('ajb-documents', 'public');

        $appointment->update([
            'ajb_document_path' => $filePath,
            'ajb_signed_at' => now(),
            'ajb_notes' => $validated['ajb_notes'] ?? $appointment->ajb_notes,
        ]);

        $transaction->update([
            'ajb_document_path' => $filePath,
            'ajb_signed_at' => now(),
        ]);

        $this->moveTransactionTo(
            $transaction,
            'ajb_scheduled',
            $ppat,
            'PPAT mengunggah bukti penandatanganan Akta Jual Beli (AJB).',
        );

        $appointment->load(['seller', 'buyer']);
        if ($appointment->seller) {
            $appointment->seller->notify(new AppointmentNotification(
                'Bukti AJB telah diunggah',
                'PPAT telah mengunggah bukti penandatanganan Akta Jual Beli (AJB) untuk '.$property->title.'. Tahap 5 selesai.',
                route('seller.my-properties.show', ['property' => $property->uuid, 'appointment' => $appointment->id]),
            ));
        }
        if ($appointment->buyer) {
            $appointment->buyer->notify(new AppointmentNotification(
                'Bukti AJB telah diunggah',
                'PPAT telah mengunggah bukti penandatanganan Akta Jual Beli (AJB) untuk '.$property->title.'. Tahap 5 selesai.',
                route('buyer.bought-properties.show', ['property' => $property->uuid]),
            ));
        }

        return back()->with('status', 'Bukti penandatanganan Akta Jual Beli (AJB) berhasil diunggah. Tahap 5 selesai.');
    }

    private function moveTransactionTo(
        Transaction $transaction,
        string $toState,
        User $actor,
        string $notes,
    ): void {
        $fromState = (string) $transaction->state;

        if ($fromState === $toState) {
            return;
        }

        $transaction->forceFill(['state' => $toState])->save();

        $transaction->logs()->create([
            'actor_id' => $actor->id,
            'from_state' => $fromState,
            'to_state' => $toState,
            'notes' => $notes,
        ]);
    }
}
