<?php

namespace App\Support;

class InterfacePermissions
{
    /** @return list<int>|null Null means national access. */
    public function scope(string $permission): ?array
    {
        $user = request()->user();
        if (! $user) {
            return [];
        }
        $key = 'interface_permission.'.$user->id.'.'.$permission;
        if (! request()->attributes->has($key)) {
            request()->attributes->set($key, $user->facilityIdsForPermission($permission));
        }

        return request()->attributes->get($key);
    }

    public function allows(string $permission, ?int $facilityId = null): bool
    {
        $scope = $this->scope($permission);

        return $scope === null || ($facilityId === null ? $scope !== [] : in_array($facilityId, $scope, true));
    }

    public function national(string $permission): bool
    {
        return $this->scope($permission) === null;
    }

    public function destination(string $route): bool
    {
        return match ($route) {
            'inmates.index', 'inmates.discharges' => $this->allows('inmates.read'),
            'personnel.index', 'personnel.assignments' => $this->allows('personnel.read'),
            'facilities.index', 'facilities.capacity' => $this->allows('facilities.read'),
            'visitors.index', 'visitors.records' => $this->allows('visitors.read'),
            'reports.index' => $this->allows('reports.view'),
            'audit.index' => $this->allows('audit.view'),
            'users.index', 'users.roles.index', 'settings.index' => $this->national('users.manage'),
            'transfers.index', 'transfers.history' => $this->any(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive']),
            default => true,
        };
    }

    /** @param list<string> $permissions */
    public function any(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->allows($permission)) {
                return true;
            }
        }

        return false;
    }
}
