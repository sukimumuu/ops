<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user may approve or reject the survey booking.
     */
    public function review(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['Admin', 'Superadmin'])) {
            return true;
        }

        if ($appointment->seller_id === $user->id) {
            return true;
        }

        if (! $user->hasRole('PPAT')) {
            return false;
        }

        return $appointment->property?->assigned_ppat_id === $user->id;
    }
}
