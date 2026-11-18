<?php

namespace App\Support\Demo;

/**
 * RoleDemoData — Phase 7 proposed permission catalog and role matrix.
 *
 * IMPORTANT: This is a design proposal only. Nothing here enforces access
 * control; real RBAC arrives with the backend phases (B2). Approval authority
 * and scope rules require UPS validation.
 */
class RoleDemoData
{
    /** Permission groups: group => [slug => label] */
    public const PERMISSION_GROUPS = [
        'Inmates' => [
            'inmates.read' => 'View inmate records',
            'inmates.admit' => 'Admit inmates',
            'inmates.edit' => 'Edit / correct inmate records',
            'inmates.discharge' => 'Authorise discharge',
        ],
        'Transfers' => [
            'transfers.request' => 'Request transfers',
            'transfers.review' => 'Review transfer requests',
            'transfers.authorize' => 'Authorize transfers',
            'transfers.dispatch' => 'Dispatch transfers',
            'transfers.receive' => 'Receive transfers',
        ],
        'Personnel & Facilities' => [
            'personnel.read' => 'View personnel records',
            'personnel.manage' => 'Manage personnel records',
            'facilities.read' => 'View facilities',
            'facilities.manage' => 'Manage facilities & capacity',
        ],
        'Visitors' => [
            'visitors.read' => 'View visitor records',
            'visitors.register' => 'Register visitors',
            'visits.schedule' => 'Schedule visits',
            'visits.authorize' => 'Approve or deny visit requests',
            'visits.checkinout' => 'Check visitors in / out',
        ],
        'Reporting' => [
            'reports.view' => 'View reports & analytics',
            'reports.export' => 'Export report data',
        ],
        'Administration' => [
            'users.manage' => 'Provision & manage system accounts',
            'roles.assign' => 'Assign roles to accounts',
            'audit.view' => 'View audit logs',
        ],
    ];

    /** @return array<string, string> flat slug => label */
    public static function all(): array
    {
        $flat = [];
        foreach (static::PERMISSION_GROUPS as $permissions) {
            foreach ($permissions as $slug => $label) {
                $flat[$slug] = $label;
            }
        }

        return $flat;
    }

    /**
     * Proposed roles. `grants` lists permission slugs; '*' means all.
     * Facility scope is a concept for review — not enforced anywhere yet.
     *
     * @return list<array{key:string,name:string,description:string,scope:string,grants:list<string>}>
     */
    public static function roles(): array
    {
        return [
            [
                'key' => 'system_admin',
                'name' => 'System Administrator',
                'description' => 'Full configuration, user provisioning and audit access.',
                'scope' => 'National',
                'grants' => ['*'],
            ],
            [
                'key' => 'commissioner',
                'name' => 'Commissioner / Deputy Commissioner',
                'description' => 'National oversight, transfer authorization, reporting.',
                'scope' => 'National',
                'grants' => [
                    'inmates.read', 'transfers.request', 'transfers.review', 'transfers.authorize',
                    'personnel.read', 'facilities.read', 'visitors.read', 'visits.schedule', 'visits.authorize',
                    'reports.view', 'reports.export', 'audit.view',
                ],
            ],
            [
                'key' => 'facility_officer_in_charge',
                'name' => 'Facility Officer in Charge',
                'description' => 'Runs a single facility; approves local movements.',
                'scope' => 'Assigned facility',
                'grants' => [
                    'inmates.read', 'inmates.admit', 'inmates.edit', 'inmates.discharge',
                    'transfers.request', 'transfers.review', 'transfers.dispatch', 'transfers.receive',
                    'personnel.read', 'facilities.read', 'visitors.read', 'visitors.register',
                    'visits.schedule', 'visits.authorize', 'visits.checkinout', 'reports.view', 'audit.view',
                ],
            ],
            [
                'key' => 'records_officer',
                'name' => 'Records Officer',
                'description' => 'Custody record entry and corrections at assigned facility.',
                'scope' => 'Assigned facility',
                'grants' => [
                    'inmates.read', 'inmates.admit', 'inmates.edit',
                    'transfers.request', 'visitors.read', 'visitors.register', 'reports.view',
                ],
            ],
            [
                'key' => 'duty_warden',
                'name' => 'Duty Warden',
                'description' => 'Reception duties: visitor check-in/out, read-only custody view.',
                'scope' => 'Assigned facility',
                'grants' => ['inmates.read', 'visitors.read', 'visits.checkinout'],
            ],
            [
                'key' => 'auditor',
                'name' => 'Compliance Auditor',
                'description' => 'Read-only visibility across records, reports and audit trail.',
                'scope' => 'National (read-only)',
                'grants' => [
                    'inmates.read', 'transfers.review', 'personnel.read', 'facilities.read',
                    'visitors.read', 'reports.view', 'reports.export', 'audit.view',
                ],
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        foreach (static::roles() as $role) {
            if ($role['key'] === $key) {
                return $role;
            }
        }

        return null;
    }

    public static function nameForKey(string $key): string
    {
        return static::find($key)['name'] ?? $key;
    }

    /** Does a role grant a permission? */
    public static function grants(string $roleKey, string $permission): bool
    {
        $role = static::find($roleKey);
        if (! $role) {
            return false;
        }

        return in_array('*', $role['grants'], true) || in_array($permission, $role['grants'], true);
    }

    public static function permissionCount(): int
    {
        return count(static::all());
    }
}
