<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determine whether the user may view the seller property detail page.
     */
    public function view(User $user, Property $property): bool
    {
        return $property->seller_id === $user->id;
    }

    public function review(User $user, Property $property): bool
    {
        return $user->hasAnyRole(['Admin', 'Superadmin']);
    }
}
