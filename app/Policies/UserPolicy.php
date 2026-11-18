<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
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
        return $user->hasPermission('users.manage');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function updateStatus(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function assignRole(User $user): bool
    {
        return $user->hasPermission('roles.assign');
    }
}
