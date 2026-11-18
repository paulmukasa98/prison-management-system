<?php

namespace App\Support\Demo;

/**
 * UserDemoData — Phase 7 fictional system accounts.
 *
 * IMPORTANT: A personnel record and a login account are deliberately distinct
 * concepts. Accounts here are linked to DEMO personnel records for display
 * only; no authentication or authorization is implemented (backend phase B2).
 * Passwords are never represented anywhere, even as placeholders.
 */
class UserDemoData
{
    public const ACCOUNT_STATUSES = ['Active', 'Invited', 'Suspended', 'Deactivated'];

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'user_ref' => 'DEMO-USR-0001',
                'personnel_id' => 7,
                'name' => 'Isaac Ssebatindira',
                'email' => 'i.ssebatindira@demo.ups.go.ug',
                'role_key' => 'commissioner',
                'facility_scope' => 'National',
                'status' => 'Active',
                'last_login' => '2026-11-11 08:42',
                'created_date' => '2025-02-03',
                'invited_by' => null,
            ],
            [
                'id' => 2,
                'user_ref' => 'DEMO-USR-0002',
                'personnel_id' => 1,
                'name' => 'Patrick Ochieng',
                'email' => 'p.ochieng@demo.ups.go.ug',
                'role_key' => 'facility_officer_in_charge',
                'facility_scope' => 'Luzira Upper Prison',
                'status' => 'Active',
                'last_login' => '2026-11-11 07:15',
                'created_date' => '2025-02-05',
                'invited_by' => null,
            ],
            [
                'id' => 3,
                'user_ref' => 'DEMO-USR-0003',
                'personnel_id' => 6,
                'name' => 'Lydia Nabwire',
                'email' => 'l.nabwire@demo.ups.go.ug',
                'role_key' => 'records_officer',
                'facility_scope' => 'Soroti Prison',
                'status' => 'Active',
                'last_login' => '2026-11-10 16:30',
                'created_date' => '2025-03-12',
                'invited_by' => 'DEMO-USR-0002',
            ],
            [
                'id' => 4,
                'user_ref' => 'DEMO-USR-0004',
                'personnel_id' => 3,
                'name' => 'Samuel Tumusiime',
                'email' => 's.tumusiime@demo.ups.go.ug',
                'role_key' => 'duty_warden',
                'facility_scope' => 'Kampala Prison',
                'status' => 'Active',
                'last_login' => '2026-11-11 06:58',
                'created_date' => '2025-04-01',
                'invited_by' => null,
            ],
            [
                'id' => 5,
                'user_ref' => 'DEMO-USR-0005',
                'personnel_id' => 2,
                'name' => 'Grace Akello',
                'email' => 'g.akello@demo.ups.go.ug',
                'role_key' => 'records_officer',
                'facility_scope' => 'Murchison Bay Prison',
                'status' => 'Active',
                'last_login' => '2026-11-09 14:22',
                'created_date' => '2025-05-20',
                'invited_by' => 'DEMO-USR-0002',
            ],
            [
                'id' => 6,
                'user_ref' => 'DEMO-USR-0006',
                'personnel_id' => 5,
                'name' => 'Moses Kyomuhendo',
                'email' => 'm.kyomuhendo@demo.ups.go.ug',
                'role_key' => 'facility_officer_in_charge',
                'facility_scope' => 'Fort Portal Prison',
                'status' => 'Active',
                'last_login' => '2026-11-08 11:05',
                'created_date' => '2025-06-11',
                'invited_by' => null,
            ],
            [
                'id' => 7,
                'user_ref' => 'DEMO-USR-0007',
                'personnel_id' => null,
                'name' => 'Auditor (external)',
                'email' => 'compliance.audit@demo.gov.ug',
                'role_key' => 'auditor',
                'facility_scope' => 'National (read-only)',
                'status' => 'Active',
                'last_login' => '2026-10-30 09:12',
                'created_date' => '2025-09-02',
                'invited_by' => null,
            ],
            [
                'id' => 8,
                'user_ref' => 'DEMO-USR-0008',
                'personnel_id' => 4,
                'name' => 'Harriet Atim',
                'email' => 'h.atim@demo.ups.go.ug',
                'role_key' => 'records_officer',
                'facility_scope' => 'Gulu Prison',
                'status' => 'Invited',
                'last_login' => null,
                'created_date' => '2026-11-05',
                'invited_by' => 'DEMO-USR-0002',
            ],
            [
                'id' => 9,
                'user_ref' => 'DEMO-USR-0009',
                'personnel_id' => 8,
                'name' => 'Faridah Nassolo',
                'email' => 'f.nassolo@demo.ups.go.ug',
                'role_key' => 'duty_warden',
                'facility_scope' => 'Murchison Bay Prison',
                'status' => 'Suspended',
                'last_login' => '2026-09-18 10:40',
                'created_date' => '2025-07-15',
                'invited_by' => 'DEMO-USR-0001',
            ],
            [
                'id' => 10,
                'user_ref' => 'DEMO-USR-0010',
                'personnel_id' => null,
                'name' => 'Former Officer (account closed)',
                'email' => 'retired.officer@demo.ups.go.ug',
                'role_key' => 'duty_warden',
                'facility_scope' => 'Jinja Prison',
                'status' => 'Deactivated',
                'last_login' => '2026-06-01 08:00',
                'created_date' => '2025-01-20',
                'invited_by' => null,
            ],
        ];
    }

    public static function find(int $id): ?array
    {
        foreach (static::all() as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }

        return null;
    }

    public static function findByRef(string $ref): ?array
    {
        foreach (static::all() as $user) {
            if ($user['user_ref'] === $ref) {
                return $user;
            }
        }

        return null;
    }

    /** Display name for an actor reference used by audit events. */
    public static function displayName(?int $userId): string
    {
        if ($userId === null) {
            return 'System';
        }

        $user = static::find($userId);

        return $user ? $user['name'].' ('.RoleDemoData::nameForKey($user['role_key']).')' : 'Unknown account #'.$userId;
    }

    /** @return array{total:int, active:int, invited:int, suspended:int, deactivated:int} */
    public static function summaryCounts(): array
    {
        $counts = ['total' => 0, 'active' => 0, 'invited' => 0, 'suspended' => 0, 'deactivated' => 0];
        foreach (static::all() as $user) {
            $counts['total']++;
            $counts[strtolower($user['status'])]++;
        }

        return $counts;
    }

    /** Enrich a user row with role/personnel context for views. */
    public static function enrich(array $user): array
    {
        $user['role_name'] = RoleDemoData::nameForKey($user['role_key']);
        $user['initials'] = strtoupper(substr($user['name'], 0, 1));
        $user['personnel'] = $user['personnel_id'] ? PersonnelDemoData::enrich(PersonnelDemoData::find($user['personnel_id'])) : null;

        return $user;
    }
}
