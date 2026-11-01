<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Property;
use App\Notifications\AppointmentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /**
     * Approve or reject a pending survey booking request from a buyer.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('review', $appointment);

        $requiresSuggestion = $appointment->status === Appointment::STATUS_REQUESTED;

        $rules = [
            'status' => ['required', Rule::in(Appointment::REVIEW_STATUSES)],
        ];

        if ($requiresSuggestion) {
            $rules['suggested_date'] = ['nullable', 'required_if:status,'.Appointment::STATUS_REJECTED, 'date', 'after_or_equal:today'];
            $rules['suggested_time'] = ['nullable', 'required_if:status,'.Appointment::STATUS_REJECTED, 'string', Rule::in(Appointment::TIME_SLOTS)];
        }

        $validated = $request->validate($rules, [
            'suggested_date.required_if' => 'Pilih tanggal jadwal pengganti.',
            'suggested_date.after_or_equal' => 'Tanggal jadwal pengganti tidak boleh di masa lalu.',
            'suggested_time.required_if' => 'Pilih waktu jadwal pengganti.',
            'suggested_time.in' => 'Waktu jadwal pengganti yang dipilih tidak valid.',
        ]);

        $updates = ['status' => $validated['status']];

        if ($requiresSuggestion && $validated['status'] === Appointment::STATUS_REJECTED) {
            $updates['reschedule_suggested_at'] = Carbon::parse($validated['suggested_date'])
                ->setTimeFromTimeString($validated['suggested_time']);
        }

        $updated = Appointment::query()
            ->whereKey($appointment->getKey())
            ->where('status', Appointment::STATUS_REQUESTED)
            ->update($updates);

        if ($updated === 0) {
            return back()->withErrors([
                'status' => 'Permintaan survei ini sudah ditinjau dan tidak dapat diubah kembali.',
            ]);
        }

        $appointment->refresh()->load(['buyer', 'property']);
        $isSuggested = $requiresSuggestion && $validated['status'] === Appointment::STATUS_REJECTED;
        $scheduledAt = $isSuggested
            ? $appointment->reschedule_suggested_at
            : $appointment->scheduled_at;
        $title = $isSuggested ? 'Penjual mengajukan jadwal lain' : 'Jadwal survei disetujui';
        $message = $isSuggested
            ? 'Penjual mengusulkan jadwal '.$scheduledAt->locale('id')->translatedFormat('d M Y, H:i').' WIB untuk '.$appointment->property->title.'.'
            : 'Penjual menyetujui jadwal survei '.$appointment->property->title.' pada '.$scheduledAt->locale('id')->translatedFormat('d M Y, H:i').' WIB.';

        $appointment->buyer->notify(new AppointmentNotification(
            $title,
            $message,
            route('buyer.survey-schedule'),
        ));

        $message = match ($validated['status']) {
            Appointment::STATUS_CONFIRMED => 'Permintaan survei berhasil disetujui.',
            default => $requiresSuggestion
                ? 'Permintaan survei ditolak. Jadwal pengganti telah dikirim ke pembeli untuk dikonfirmasi.'
                : 'Permintaan survei berhasil ditolak.',
        };

        return back()->with('status', $message);
    }

    /**
     * Record the result of a confirmed survey from the seller property page.
     */
    public function recordSurveyResult(Request $request, Property $property, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('view', $property);

        abort_unless($appointment->property_id === $property->id, 404);

        Gate::authorize('recordResult', $appointment);

        $validated = $request->validate([
            'status' => ['required', Rule::in([Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED])],
            'buyer_intent' => [
                'nullable',
                'required_if:status,'.Appointment::STATUS_COMPLETED,
                'string',
                Rule::in(Appointment::BUYER_INTENTS),
            ],
            'result_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Pilih hasil survei.',
            'status.in' => 'Hasil survei yang dipilih tidak valid.',
            'buyer_intent.required_if' => 'Pilih keputusan pembeli setelah survei.',
            'buyer_intent.in' => 'Keputusan pembeli yang dipilih tidak valid.',
            'result_notes.max' => 'Catatan hasil survei tidak boleh lebih dari 1000 karakter.',
        ]);

        $buyerIntent = $validated['status'] === Appointment::STATUS_COMPLETED
            ? $validated['buyer_intent']
            : null;

        $updated = DB::transaction(function () use ($appointment, $validated, $buyerIntent, $property): int {
            $updated = Appointment::query()
                ->whereKey($appointment->getKey())
                ->where('status', Appointment::STATUS_CONFIRMED)
                ->update([
                    'status' => $validated['status'],
                    'buyer_intent' => $buyerIntent,
                    'result_notes' => $validated['result_notes'] ?? null,
                    'completed_at' => $validated['status'] === Appointment::STATUS_COMPLETED ? now() : null,
                ]);

            if ($updated === 0) {
                return 0;
            }

            if ($buyerIntent === Appointment::BUYER_INTENT_DEAL) {
                Property::query()
                    ->whereKey($property->getKey())
                    ->update(['status' => 'reserved']);
            }

            return $updated;
        });

        if ($updated === 0) {
            return back()->withErrors([
                'status' => 'Hasil survei hanya dapat dicatat untuk survei yang sudah dikonfirmasi.',
            ]);
        }

        $appointment->refresh()->load('buyer');
        $isCancelled = $appointment->status === Appointment::STATUS_CANCELLED;
        $resultMessage = $isCancelled
            ? 'Survei '.$property->title.' dibatalkan oleh penjual.'
            : 'Hasil survei '.$property->title.' telah dicatat: '.$appointment->buyerIntentLabel().'.';

        if (filled($appointment->result_notes)) {
            $resultMessage .= ' Catatan: '.$appointment->result_notes;
        }

        $appointment->buyer->notify(new AppointmentNotification(
            $isCancelled ? 'Survei dibatalkan' : 'Hasil survei tersedia',
            $resultMessage,
            route('buyer.survey-schedule'),
        ));

        $message = match (true) {
            $validated['status'] === Appointment::STATUS_CANCELLED => 'Survei berhasil dibatalkan.',
            $buyerIntent === Appointment::BUYER_INTENT_DEAL => 'Hasil survei disimpan dan properti ditandai sebagai dipesan.',
            default => 'Hasil survei berhasil disimpan.',
        };

        return redirect()
            ->route('seller.my-properties.show', [
                'property' => $property->uuid,
                'appointment' => $appointment->id,
            ])
            ->with('status', $message);
    }

    /**
     * Save the seller's requested date for a PPAT meeting after a successful survey.
     */
    public function schedulePpatMeeting(Request $request, Property $property, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('view', $property);

        abort_unless($appointment->property_id === $property->id, 404);

        Gate::authorize('recordResult', $appointment);

        if (
            $appointment->status !== Appointment::STATUS_COMPLETED
            || $appointment->buyer_intent !== Appointment::BUYER_INTENT_DEAL
        ) {
            return back()->withErrors([
                'ppat_meeting_date' => 'Jadwal temu PPAT hanya dapat diajukan setelah pembeli berminat melanjutkan transaksi.',
            ]);
        }

        if ($property->assigned_ppat_id === null) {
            return back()->withErrors([
                'ppat_meeting_date' => 'Properti ini belum memiliki PPAT yang ditugaskan.',
            ]);
        }

        $validated = $request->validate([
            'ppat_meeting_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ], [
            'ppat_meeting_date.required' => 'Pilih tanggal temu PPAT.',
            'ppat_meeting_date.date_format' => 'Format tanggal temu PPAT tidak valid.',
            'ppat_meeting_date.after_or_equal' => 'Tanggal temu PPAT tidak boleh di masa lalu.',
        ]);

        if (in_array($appointment->ppat_meeting_status, [
            Appointment::PPAT_MEETING_STATUS_CONFIRMED,
            Appointment::PPAT_MEETING_STATUS_RESCHEDULED,
        ], true)) {
            return back()->withErrors([
                'ppat_meeting_date' => 'Jadwal temu PPAT sudah dikonfirmasi dan tidak dapat diajukan ulang.',
            ]);
        }

        $updated = Appointment::query()
            ->whereKey($appointment->getKey())
            ->where(function ($query): void {
                $query->whereNull('ppat_meeting_status')
                    ->orWhere('ppat_meeting_status', Appointment::PPAT_MEETING_STATUS_REQUESTED);
            })
            ->update([
                'ppat_meeting_date' => $validated['ppat_meeting_date'],
                'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_REQUESTED,
            ]);

        if ($updated === 0) {
            return back()->withErrors([
                'ppat_meeting_date' => 'Status jadwal temu PPAT sudah berubah. Muat ulang halaman dan coba lagi.',
            ]);
        }

        $appointment->refresh()->load(['buyer', 'seller', 'property.assignedPpat']);
        if ($appointment->property->assignedPpat === null) {
            return back()->withErrors([
                'ppat_meeting_date' => 'PPAT properti ini tidak lagi tersedia. Silakan hubungi administrator.',
            ]);
        }

        $formattedDate = $appointment->ppat_meeting_date->locale('id')->translatedFormat('l, d F Y');
        $appointment->property->assignedPpat->notify(new AppointmentNotification(
            'Permintaan jadwal temu PPAT',
            $appointment->seller->name.' mengajukan pertemuan dengan '.$appointment->buyer->name.' pada '.$formattedDate.'.',
            route('land-deed.index', ['month' => $appointment->ppat_meeting_date->format('Y-m')]),
        ));
        $appointment->buyer->notify(new AppointmentNotification(
            'Permintaan jadwal temu PPAT diajukan',
            $appointment->seller->name.' mengajukan jadwal temu PPAT pada '.$formattedDate.'.',
            route('buyer.survey-schedule'),
        ));

        return redirect()
            ->route('seller.my-properties.show', [
                'property' => $property->uuid,
                'appointment' => $appointment->id,
            ])
            ->with('status', 'Permintaan jadwal temu PPAT berhasil dikirim dan ditambahkan ke kalender PPAT.');
    }

    /**
     * Approve a PPAT meeting request or reschedule it to another date.
     */
    public function respondToPpatMeeting(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('managePpatMeeting', $appointment);

        if (
            $appointment->status !== Appointment::STATUS_COMPLETED
            || $appointment->buyer_intent !== Appointment::BUYER_INTENT_DEAL
            || $appointment->ppat_meeting_status !== Appointment::PPAT_MEETING_STATUS_REQUESTED
            || $appointment->ppat_meeting_date === null
        ) {
            return back()->withErrors([
                'ppat_meeting' => 'Permintaan jadwal temu ini sudah ditangani atau tidak lagi tersedia.',
            ]);
        }

        $validated = $request->validate([
            'decision' => ['required', Rule::in(Appointment::PPAT_MEETING_DECISIONS)],
            'ppat_suggested_date' => [
                'nullable',
                'required_if:decision,'.Appointment::PPAT_MEETING_STATUS_RESCHEDULED,
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
        ], [
            'decision.required' => 'Pilih tindakan untuk permintaan jadwal temu.',
            'decision.in' => 'Tindakan jadwal temu PPAT tidak valid.',
            'ppat_suggested_date.required_if' => 'Pilih tanggal pengganti.',
            'ppat_suggested_date.date_format' => 'Format tanggal pengganti tidak valid.',
            'ppat_suggested_date.after_or_equal' => 'Tanggal pengganti tidak boleh di masa lalu.',
        ]);

        if (
            $validated['decision'] === Appointment::PPAT_MEETING_STATUS_RESCHEDULED
            && $validated['ppat_suggested_date'] === $appointment->ppat_meeting_date->format('Y-m-d')
        ) {
            return back()->withErrors([
                'ppat_suggested_date' => 'Pilih tanggal yang berbeda dari jadwal yang diajukan.',
            ]);
        }

        $updates = [
            'ppat_meeting_status' => $validated['decision'],
        ];

        if ($validated['decision'] === Appointment::PPAT_MEETING_STATUS_RESCHEDULED) {
            $updates['ppat_meeting_date'] = $validated['ppat_suggested_date'];
        }

        $updated = Appointment::query()
            ->whereKey($appointment->getKey())
            ->where('ppat_meeting_status', Appointment::PPAT_MEETING_STATUS_REQUESTED)
            ->update($updates);

        if ($updated === 0) {
            return back()->withErrors([
                'ppat_meeting' => 'Permintaan jadwal temu ini sudah ditangani oleh PPAT lain.',
            ]);
        }

        $appointment->refresh()->load(['buyer', 'seller', 'property']);
        $meetingDate = $appointment->ppat_meeting_date->locale('id')->translatedFormat('l, d F Y');
        $isRescheduled = $validated['decision'] === Appointment::PPAT_MEETING_STATUS_RESCHEDULED;
        $message = $isRescheduled
            ? 'PPAT mengubah jadwal temu '.$appointment->property->title.' menjadi '.$meetingDate.'.'
            : 'PPAT menyetujui jadwal temu '.$appointment->property->title.' pada '.$meetingDate.'.';

        $appointment->buyer->notify(new AppointmentNotification(
            $isRescheduled ? 'Jadwal temu PPAT diubah' : 'Jadwal temu PPAT disetujui',
            $message,
            route('buyer.survey-schedule'),
        ));
        $appointment->seller->notify(new AppointmentNotification(
            $isRescheduled ? 'Jadwal temu PPAT diubah' : 'Jadwal temu PPAT disetujui',
            $message,
            route('seller.my-properties.show', [
                'property' => $appointment->property->uuid,
                'appointment' => $appointment->id,
            ]),
        ));

        return back()->with(
            'status',
            $isRescheduled
                ? 'Jadwal temu PPAT berhasil diubah dan pembeli serta penjual telah diberi tahu.'
                : 'Jadwal temu PPAT berhasil disetujui dan pembeli serta penjual telah diberi tahu.',
        );
    }

    /**
     * Accept or decline a reschedule suggestion proposed by the property seller.
     */
    public function respondToReschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('respondToReschedule', $appointment);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'declined'])],
        ]);

        if ($appointment->status !== Appointment::STATUS_REJECTED || $appointment->reschedule_suggested_at === null) {
            return back()->withErrors([
                'reschedule' => 'Tidak ada jadwal pengganti yang menunggu konfirmasi.',
            ]);
        }

        if ($validated['decision'] === 'accepted') {
            $appointment->update([
                'scheduled_at' => $appointment->reschedule_suggested_at,
                'reschedule_suggested_at' => null,
                'status' => Appointment::STATUS_CONFIRMED,
            ]);

            $appointment->load(['buyer', 'property']);
            $appointment->seller->notify(new AppointmentNotification(
                'Jadwal pengganti disetujui',
                $appointment->buyer->name.' menyetujui jadwal survei '.$appointment->property->title.'.',
                route('seller.request-survey'),
            ));

            return back()->with('status', 'Jadwal pengganti disetujui. Jadwal survei telah dikonfirmasi.');
        }

        $appointment->update([
            'reschedule_suggested_at' => null,
        ]);

        $appointment->load(['buyer', 'property']);
        $appointment->seller->notify(new AppointmentNotification(
            'Jadwal pengganti ditolak',
            $appointment->buyer->name.' menolak jadwal pengganti untuk '.$appointment->property->title.'.',
            route('seller.request-survey'),
        ));

        return back()->with('status', 'Jadwal pengganti ditolak. Silakan ajukan jadwal survei baru.');
    }
}
