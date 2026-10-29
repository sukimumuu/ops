<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Notifications\AppointmentNotification;
use App\Models\Property;
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
