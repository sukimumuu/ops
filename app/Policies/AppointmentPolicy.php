<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user may approve, reject, or reschedule the survey booking.
     */
    public function review(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['Admin', 'Superadmin'])) {
            return true;
        }

        if ($appointment->seller_id === $user->id) {
            return true;
        }

        return $appointment->property?->seller_id === $user->id;
    }

    /**
     * Determine whether the user may respond to a proposed reschedule.
     */
    public function respondToReschedule(User $user, Appointment $appointment): bool
    {
        return $appointment->buyer_id === $user->id;
    }
}
