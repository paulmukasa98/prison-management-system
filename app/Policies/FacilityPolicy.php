<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

class FacilityPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('system_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('facilities.read');
    }

    public function view(User $user, Facility $facility): bool
    {
        return $user->hasPermission('facilities.read', $facility->id);
    }

    public function manage(User $user, ?Facility $facility = null): bool
    {
        return $user->hasPermission('facilities.manage', $facility?->id);
    }
}
