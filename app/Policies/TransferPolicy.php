<?php

namespace App\Policies;

use App\Models\Transfer;
use App\Models\User;

class TransferPolicy
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
        return $user->hasPermission('transfers.review') || $user->hasPermission('transfers.request')
            || $user->hasPermission('transfers.dispatch') || $user->hasPermission('transfers.receive') || $user->hasPermission('transfers.authorize');
    }

    public function view(User $user, Transfer $transfer): bool
    {
        return Transfer::visibleTo($user)->whereKey($transfer->id)->exists();
    }

    public function request(User $user, ?int $sourceFacilityId = null): bool
    {
        return $user->hasPermission('transfers.request', $sourceFacilityId);
    }

    public function review(User $user, ?Transfer $transfer = null): bool
    {
        return $user->hasPermission('transfers.review', $transfer?->origin_facility_id) || $user->hasPermission('transfers.authorize', $transfer?->origin_facility_id);
    }

    public function authorize(User $user): bool
    {
        return $user->hasPermission('transfers.authorize');
    }

    public function dispatch(User $user, Transfer $transfer): bool
    {
        $originId = $transfer->origin_facility_id ?? $transfer->source_facility_id;

        return $user->hasPermission('transfers.dispatch', $originId);
    }

    public function receive(User $user, Transfer $transfer): bool
    {
        return $user->hasPermission('transfers.receive', $transfer->destination_facility_id);
    }

    public function cancel(User $user, Transfer $transfer): bool
    {
        $originId = $transfer->origin_facility_id ?? $transfer->source_facility_id;

        return $user->hasPermission('transfers.request', $originId)
            || $user->hasPermission('transfers.authorize', $originId);
    }
}
