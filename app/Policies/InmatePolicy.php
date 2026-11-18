<?php

namespace App\Policies;

use App\Models\Inmate;
use App\Models\User;

class InmatePolicy
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
        return $user->hasPermission('inmates.read');
    }

    public function view(User $user, Inmate $inmate): bool
    {
        return $user->hasPermission('inmates.read', $inmate->currentFacilityId());
    }

    public function create(User $user, ?int $facilityId = null): bool
    {
        return $user->hasPermission('inmates.admit', $facilityId);
    }

    public function update(User $user, Inmate $inmate): bool
    {
        return $user->hasPermission('inmates.edit', $inmate->currentFacilityId());
    }

    public function discharge(User $user, Inmate $inmate): bool
    {
        return $user->hasPermission('inmates.discharge', $inmate->currentFacilityId());
    }
}
