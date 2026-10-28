<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function review(User $user, Property $property): bool
    {
        return $user->can('properties.approve_listing')
            || $user->can('properties.reject_listing');
    }

    public function approve(User $user, Property $property): bool
    {
        return $user->can('properties.approve_listing');
    }

    public function reject(User $user, Property $property): bool
    {
        return $user->can('properties.reject_listing');
    }

    public function viewSensitiveDocument(User $user, Property $property): bool
    {
        return $user->can('documents.view_sensitive_pii');
    }
}
