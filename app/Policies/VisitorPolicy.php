<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visitor;

class VisitorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('visitors.read');
    }

    public function view(User $user, Visitor $visitor): bool
    {
        return Visitor::visibleTo($user)->whereKey($visitor->id)->exists();
    }

    public function register(User $user): bool
    {
        return $user->hasPermission('visitors.register');
    }

    public function update(User $user, Visitor $visitor): bool
    {
        if ($visitor->registered_facility_id === null) {
            return $user->facilityIdsForPermission('visitors.register') === null;
        }

        return $user->hasPermission('visitors.register', $visitor->registered_facility_id);
    }

    public function schedule(User $user, ?int $facilityId = null): bool
    {
        return $user->hasPermission('visits.schedule', $facilityId);
    }
}
