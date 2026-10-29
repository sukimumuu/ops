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
        if (! $user->can('appointments.confirm_or_reject')) {
            return false;
        }

        return $user->can('appointments.view_all')
            || $appointment->seller_id === $user->id
            || $appointment->property?->seller_id === $user->id;
    }

<<<<<<< Updated upstream
=======
    public function recordResult(User $user, Appointment $appointment): bool
    {
        return $user->can('appointments.confirm_or_reject')
            && ($appointment->seller_id === $user->id
                || $appointment->property?->seller_id === $user->id);
    }

>>>>>>> Stashed changes
    /**
     * Determine whether the seller may record the result of a confirmed survey.
     */
    public function recordResult(User $user, Appointment $appointment): bool
    {
        return $appointment->seller_id === $user->id
            || $appointment->property?->seller_id === $user->id;
    }

    /**
     * Determine whether the user may respond to a proposed reschedule.
     */
    public function respondToReschedule(User $user, Appointment $appointment): bool
    {
        return $user->can('appointments.request')
            && $appointment->buyer_id === $user->id;
    }
}
