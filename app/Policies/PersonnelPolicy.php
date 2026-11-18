<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\User;

class PersonnelPolicy
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
        return $user->hasPermission('personnel.read');
    }

    public function view(User $user, Personnel $personnel): bool
    {
        return $user->hasPermission('personnel.read', $personnel->currentFacilityId());
    }

    public function manage(User $user, ?Personnel $personnel = null): bool
    {
        return $user->hasPermission('personnel.manage', $personnel?->currentFacilityId());
    }
}
