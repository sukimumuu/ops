<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /**
     * Approve or reject a pending survey booking request.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        Gate::authorize('review', $appointment);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Appointment::REVIEW_STATUSES)],
        ]);

        $updated = Appointment::query()
            ->whereKey($appointment->getKey())
            ->where('status', Appointment::STATUS_REQUESTED)
            ->update(['status' => $validated['status']]);

        if ($updated === 0) {
            return back()->withErrors([
                'status' => 'Permintaan survei ini sudah ditinjau dan tidak dapat diubah kembali.',
            ]);
        }

        $message = $validated['status'] === Appointment::STATUS_CONFIRMED
            ? 'Permintaan survei berhasil disetujui.'
            : 'Permintaan survei berhasil ditolak.';

        return back()->with('status', $message);
    }
}
