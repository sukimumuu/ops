<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function review(User $user, Property $property): bool
    {
        return $user->hasAnyRole(['Admin', 'Superadmin']);
    }
}
