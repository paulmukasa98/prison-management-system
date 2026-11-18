<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Visit;

class VisitPolicy
{
    public function view(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visitors.read', $visit->facility_id);
    }

    public function schedule(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.schedule', $visit->facility_id);
    }

    public function review(User $user, Visit $visit): bool
    {
        return $user->hasPermission(config('visitation.approval_permission'), $visit->facility_id);
    }

    public function cancel(User $user, Visit $visit): bool
    {
        return $this->schedule($user, $visit) || $this->review($user, $visit);
    }

    public function checkinout(User $user, Visit $visit): bool
    {
        return $user->hasPermission('visits.checkinout', $visit->facility_id);
    }
}
