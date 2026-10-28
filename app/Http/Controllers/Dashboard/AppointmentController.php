<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Notifications\AppointmentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
