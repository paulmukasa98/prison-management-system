<?php

namespace Database\Seeders;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\AuditEvent;
use App\Models\CustodyLocation;
use App\Models\Discharge;
use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\Inmate;
use App\Models\LegalReference;
use App\Models\Permission;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\TransferEvent;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\VisitEvent;
use App\Models\Visitor;
use App\Models\VisitorRelationship;
use App\Support\Demo\AssignmentDemoData;
use App\Support\Demo\AuditDemoData;
use App\Support\Demo\FacilityDemoData;
use App\Support\Demo\InmateDemoData;
use App\Support\Demo\PersonnelDemoData;
use App\Support\Demo\RoleDemoData;
use App\Support\Demo\TransferDemoData;
use App\Support\Demo\UserDemoData;
use App\Support\Demo\VisitDemoData;
use App\Support\Demo\VisitorDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CanonicalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing') && ! env('PMS_ALLOW_DEMO_SEEDING', true)) {
            throw new \RuntimeException('Canonical demo records may only be seeded in local or testing environments.');
        }
        DB::transaction(function (): void {
            $this->seedFacilities();
            $this->seedRolesAndPermissions();
            $this->seedPersonnel();
            $this->seedAssignments();
            $this->seedUsers();
            $this->seedInmatesAndCustody();
            $this->seedTransfers();
            $this->seedVisitorsAndVisits();
            $this->seedAuditEvents();
        });
    }

    private function seedFacilities(): void
    {
        foreach (FacilityDemoData::all() as $f) {
            $facility = Facility::firstOrCreate(
                ['code' => $f['code']],
                [
                    'name' => $f['name'],
                    'type' => $f['type'] ?? null,
                    'region' => $f['region'] ?? null,
                    'district' => $f['district'] ?? null,
                    'location_description' => $f['location_desc'] ?? null,
                    'status' => $f['status'] ?? 'Active',
                    'established_on' => $f['established'] ?? null,
                    'notes' => $f['notes'] ?? null,
                ]
            );

            if (isset($f['capacity']) && $f['capacity'] !== null) {
                FacilityCapacity::firstOrCreate(
                    [
                        'facility_id' => $facility->id,
                        'effective_on' => $f['capacity_date'] ?? '2015-01-01',
                    ],
                    [
                        'capacity' => (int) $f['capacity'],
                        'source_reference' => $f['capacity_notes'] ?? null,
                    ]
                );
            }
        }
    }

    private function seedRolesAndPermissions(): void
    {
        foreach (RoleDemoData::PERMISSION_GROUPS as $module => $perms) {
            foreach ($perms as $slug => $label) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $label, 'module' => $module]
                );
            }
        }

        foreach (RoleDemoData::roles() as $r) {
            $role = Role::firstOrCreate(
                ['slug' => $r['key']],
                ['name' => $r['name'], 'description' => $r['description']]
            );
            if (($r['grants'] ?? []) === ['*']) {
                $permIds = Permission::pluck('id');
            } else {
                $permIds = Permission::whereIn('slug', $r['grants'] ?? [])->pluck('id');
            }
            $role->permissions()->syncWithoutDetaching($permIds);
        }

        AccessScope::firstOrCreate(
            ['kind' => 'National', 'facility_id' => null],
            ['scope_key' => 'DEMO-SCOPE-NATIONAL']
        );

        foreach (Facility::all() as $fac) {
            AccessScope::firstOrCreate(
                ['kind' => 'Facility', 'facility_id' => $fac->id],
                ['scope_key' => 'DEMO-SCOPE-FAC-'.$fac->id]
            );
        }
    }

    private function seedPersonnel(): void
    {
        foreach (PersonnelDemoData::all() as $p) {
            Personnel::firstOrCreate(
                ['staff_number' => $p['staff_number']],
                [
                    'first_name' => $p['first_name'],
                    'middle_name' => $p['middle_name'] ?? null,
                    'last_name' => $p['last_name'],
                    'gender' => $p['gender'] ?? null,
                    'date_of_birth' => $p['date_of_birth'] ?? null,
                    'nationality' => $p['nationality'] ?? 'Ugandan',
                    'rank' => $p['rank'] ?? null,
                    'department' => $p['department'] ?? null,
                    'employment_status' => $p['employment_status'] ?? 'Active',
                    'appointed_on' => $p['appointment_date'] ?? null,
                    'email' => $p['email'] ?? null,
                    'phone' => $p['phone'] ?? null,
                    'kin_name' => $p['kin_name'] ?? null,
                    'kin_contact' => $p['kin_contact'] ?? null,
                    'notes' => $p['notes'] ?? null,
                ]
            );
        }
    }

    private function seedAssignments(): void
    {
        foreach (AssignmentDemoData::all() as $a) {
            $staff = Personnel::where('staff_number', $a['staff_number'])->first();
            $fac = Facility::where('name', $a['facility'])->first();
            if ($staff && $fac) {
                PersonnelAssignment::firstOrCreate(
                    ['assignment_ref' => $a['assignment_ref']],
                    [
                        'personnel_id' => $staff->id,
                        'facility_id' => $fac->id,
                        'duty_title' => $a['duty_title'] ?? null,
                        'reason' => $a['reason'] ?? null,
                        'start_date' => $a['start_date'],
                        'end_date' => $a['end_date'] ?? null,
                        'status' => $a['status'] ?? 'Active',
                        'authority_reference' => $a['authority_ref'] ?? null,
                    ]
                );
            }
        }
    }

    private function seedUsers(): void
    {
        $admin = User::withTrashed()->firstOrCreate(
            ['email' => 'admin@ups.go.ug'],
            [
                'account_ref' => 'SYS-ADMIN-0001',
                'name' => 'System Administrator',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin123!')),
                'status' => 'Active',
                'last_login_at' => now(),
            ]
        );
        $adminRole = Role::where('slug', 'system_admin')->first();
        $natScope = AccessScope::where('kind', 'National')->first();
        if ($admin && $adminRole && $natScope) {
            UserRoleAssignment::firstOrCreate(
                ['assignment_ref' => 'SYS-URA-ADMIN'],
                [
                    'user_id' => $admin->id,
                    'role_id' => $adminRole->id,
                    'access_scope_id' => $natScope->id,
                    'granted_at' => now()->subYear(),
                ]
            );
        }

        foreach (UserDemoData::all() as $u) {
            $personnel = null;
            if (! empty($u['personnel_id'])) {
                $staff = PersonnelDemoData::find((int) $u['personnel_id']);
                if ($staff) {
                    $personnel = Personnel::where('staff_number', $staff['staff_number'])->first();
                }
            }

            $user = User::withTrashed()->firstOrCreate(
                ['email' => $u['email']],
                [
                    'account_ref' => $u['user_ref'],
                    'name' => $u['name'],
                    'password' => Hash::make(env('DEMO_USER_PASSWORD', 'Password123!')),
                    'personnel_id' => $personnel?->id,
                    'status' => $u['status'] ?? 'Active',
                    'last_login_at' => ! empty($u['last_login']) ? $u['last_login'].':00' : null,
                ]
            );

            if ($user->trashed()) {
                continue;
            }

            if (! empty($u['role_key'])) {
                $role = Role::where('slug', $u['role_key'])->first();
                $scope = null;
                if (($u['facility_scope'] ?? 'National') === 'National') {
                    $scope = AccessScope::where('kind', 'National')->first();
                } else {
                    $fac = Facility::where('name', $u['facility_scope'])->first();
                    $scope = $fac ? AccessScope::where('facility_id', $fac->id)->first() : null;
                }

                if ($role && $scope) {
                    UserRoleAssignment::firstOrCreate(
                        ['assignment_ref' => 'DEMO-URA-'.$user->id.'-'.$role->id],
                        [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                            'access_scope_id' => $scope->id,
                            'granted_at' => now()->subMonths(6),
                        ]
                    );
                }
            }
        }
    }

    private function seedInmatesAndCustody(): void
    {
        foreach (InmateDemoData::all() as $inm) {
            $inmate = Inmate::firstOrCreate(
                ['prisoner_number' => $inm['prisoner_number']],
                [
                    'identity_ref' => 'DEMO-INM-'.str_pad((string) $inm['id'], 4, '0', STR_PAD_LEFT),
                    'first_name' => $inm['first_name'],
                    'middle_name' => $inm['middle_name'] ?? null,
                    'last_name' => $inm['last_name'],
                    'gender' => $inm['gender'] ?? null,
                    'date_of_birth' => $inm['date_of_birth'] ?? null,
                    'nationality' => $inm['nationality'] ?? 'Ugandan',
                    'district' => $inm['district'] ?? null,
                    'id_type' => $inm['id_type'] ?? null,
                    'id_number' => $inm['id_number'] ?? null,
                    'kin_name' => $inm['kin_name'] ?? null,
                    'kin_relationship' => $inm['kin_relationship'] ?? null,
                    'kin_contact' => $inm['kin_contact'] ?? null,
                    'notes' => $inm['notes'] ?? null,
                ]
            );

            $fac = Facility::where('name', $inm['facility'])->first() ?? Facility::first();
            $admittedAt = ($inm['admission_date'] ?? '2023-01-01').' 08:00:00';
            $closedAt = ! empty($inm['actual_discharge']) ? $inm['actual_discharge'].' 10:00:00' : null;
            $isClosed = ($inm['status'] ?? '') === 'Released';

            $admission = AdmissionEpisode::firstOrCreate(
                ['admission_ref' => $inm['admission_ref'] ?? ('DEMO-ADM-'.str_pad((string) $inm['id'], 4, '0', STR_PAD_LEFT))],
                [
                    'inmate_id' => $inmate->id,
                    'admitting_facility_id' => $fac->id,
                    'admitted_at' => $admittedAt,
                    'closed_at' => $isClosed ? $closedAt : null,
                    'state' => $isClosed ? 'Closed' : 'Open',
                    'classification' => $inm['classification'] ?? 'Convicted',
                    'legal_basis' => $inm['legal_basis'] ?? null,
                    'notes' => $inm['notes'] ?? null,
                ]
            );

            $legalRef = LegalReference::firstOrCreate(
                ['reference' => 'DEMO-LEG-'.str_pad((string) $inm['id'], 4, '0', STR_PAD_LEFT)],
                [
                    'admission_episode_id' => $admission->id,
                    'type' => 'Committal Warrant',
                    'external_reference' => $inm['warrant_ref'] ?? null,
                    'issuing_authority' => $inm['issuing_authority'] ?? null,
                    'case_reference' => $inm['case_ref'] ?? null,
                    'next_court_on' => $inm['next_court_date'] ?? null,
                    'sentence_text' => $inm['sentence'] ?? null,
                    'supplied_release_on' => $inm['expected_release'] ?? null,
                    'issued_on' => $inm['admission_date'] ?? null,
                ]
            );

            if ($isClosed) {
                Discharge::firstOrCreate(
                    ['discharge_ref' => 'DEMO-DIS-'.str_pad((string) $inm['id'], 4, '0', STR_PAD_LEFT)],
                    [
                        'admission_episode_id' => $admission->id,
                        'legal_reference_id' => $legalRef->id,
                        'reason' => $inm['discharge_reason'] ?? 'Sentence Served',
                        'state' => 'Completed',
                        'authority_reference' => $inm['discharge_auth_ref'] ?? null,
                        'requested_at' => $closedAt,
                        'authorized_at' => $closedAt,
                        'executed_at' => $closedAt,
                    ]
                );
            }

            CustodyLocation::firstOrCreate(
                ['location_ref' => 'DEMO-LOC-'.str_pad((string) $inm['id'], 4, '0', STR_PAD_LEFT)],
                [
                    'admission_episode_id' => $admission->id,
                    'facility_id' => $fac->id,
                    'entered_at' => $admittedAt,
                    'left_at' => $isClosed ? $closedAt : null,
                ]
            );
        }
    }

    private function seedTransfers(): void
    {
        foreach (TransferDemoData::all() as $t) {
            $inmate = Inmate::where('prisoner_number', $t['prisoner_number'] ?? '')->first();
            $admission = $inmate ? AdmissionEpisode::where('inmate_id', $inmate->id)->first() : null;
            $orig = Facility::where('name', $t['origin_facility'] ?? '')->first();
            $dest = Facility::where('name', $t['destination_facility'] ?? '')->first();
            $reqFac = Facility::where('name', $t['requesting_facility'] ?? '')->first() ?? $orig;

            if ($admission && $orig && $dest && $orig->id !== $dest->id) {
                $reqAt = ($t['request_date'] ?? '2026-01-01').' 08:00:00';
                $dispAt = ! empty($t['dispatched_at']) ? $t['dispatched_at'] : null;
                $recAt = ! empty($t['received_at']) ? $t['received_at'] : null;

                $transfer = Transfer::firstOrCreate(
                    ['transfer_ref' => $t['transfer_ref']],
                    [
                        'admission_episode_id' => $admission->id,
                        'origin_facility_id' => $orig->id,
                        'destination_facility_id' => $dest->id,
                        'requesting_facility_id' => $reqFac->id,
                        'state' => $t['status'] ?? 'Draft',
                        'priority' => $t['priority'] ?? 'Routine',
                        'reason' => $t['transfer_reason'] ?? null,
                        'requested_at' => $reqAt,
                        'proposed_on' => $t['proposed_transfer_date'] ?? null,
                        'dispatched_at' => $dispAt,
                        'received_at' => $recAt,
                        'notes' => $t['notes'] ?? null,
                    ]
                );

                TransferEvent::firstOrCreate(
                    ['transfer_id' => $transfer->id, 'sequence' => 1],
                    [
                        'event_ref' => 'DEMO-TE-'.$transfer->id.'-1',
                        'event_type' => 'Request Initiated',
                        'from_state' => null,
                        'to_state' => $transfer->state,
                        'facility_id' => $orig->id,
                        'occurred_at' => $reqAt,
                        'recorded_at' => $reqAt,
                    ]
                );
            }
        }
    }

    private function seedVisitorsAndVisits(): void
    {
        foreach (VisitorDemoData::all() as $v) {
            Visitor::firstOrCreate(
                ['visitor_ref' => $v['visitor_ref']],
                [
                    'first_name' => $v['first_name'],
                    'middle_name' => $v['middle_name'] ?? null,
                    'last_name' => $v['last_name'],
                    'gender' => $v['gender'] ?? null,
                    'id_type' => $v['id_type'] ?? null,
                    'id_number' => $v['id_number_masked'] ?? null,
                    'phone' => $v['phone'] ?? null,
                    'email' => $v['email'] ?? null,
                    'residence' => $v['residence'] ?? null,
                    'status' => $v['status'] ?? 'Under Review',
                    'registered_at' => ($v['registered_date'] ?? '2026-01-01').' 08:00:00',
                    'notes' => $v['notes'] ?? null,
                ]
            );
        }

        foreach (VisitDemoData::all() as $vi) {
            $visitor = Visitor::where('visitor_ref', 'DEMO-VST-'.str_pad((string) $vi['visitor_id'], 4, '0', STR_PAD_LEFT))->first();
            $inmate = Inmate::where('prisoner_number', 'DEMO-PN-'.str_pad((string) $vi['inmate_id'], 4, '0', STR_PAD_LEFT))->first();
            $admission = $inmate ? AdmissionEpisode::where('inmate_id', $inmate->id)->first() : null;
            $fac = Facility::where('name', $vi['facility'])->first();

            if ($visitor && $inmate && $admission && $fac) {
                VisitorRelationship::firstOrCreate(
                    ['visitor_id' => $visitor->id, 'inmate_id' => $inmate->id],
                    ['relationship' => $vi['purpose'] ?? 'Family Visit']
                );

                $visitDate = $vi['visit_date'] ?? '2026-09-01';
                $startTime = $vi['start_time'] ?? '10:00';
                $duration = $vi['duration_mins'] ?? 30;
                $startAt = $visitDate.' '.$startTime.':00';
                $endAt = date('Y-m-d H:i:s', strtotime($startAt." +{$duration} minutes"));
                $reqAt = ($vi['request_date'] ?? $visitDate).' 08:00:00';

                $checkInAt = ! empty($vi['check_in_time']) ? $visitDate.' '.$vi['check_in_time'].':00' : null;
                $checkOutAt = ! empty($vi['check_out_time']) ? $visitDate.' '.$vi['check_out_time'].':00' : null;

                $visit = Visit::firstOrCreate(
                    ['visit_ref' => $vi['visit_ref']],
                    [
                        'visitor_id' => $visitor->id,
                        'admission_episode_id' => $admission->id,
                        'facility_id' => $fac->id,
                        'purpose' => $vi['purpose'] ?? null,
                        'relationship' => $visitor->primary_relationship ?? null,
                        'state' => $vi['status'] ?? 'Requested',
                        'requested_at' => $reqAt,
                        'scheduled_start_at' => $startAt,
                        'scheduled_end_at' => $endAt,
                        'checked_in_at' => $checkInAt,
                        'checked_out_at' => $checkOutAt,
                        'notes' => $vi['notes'] ?? null,
                    ]
                );

                VisitEvent::firstOrCreate(
                    ['visit_id' => $visit->id, 'sequence' => 1],
                    [
                        'event_ref' => 'DEMO-VE-'.$visit->id.'-1',
                        'event_type' => $visit->state,
                        'to_state' => $visit->state,
                        'occurred_at' => $startAt,
                        'recorded_at' => $startAt,
                    ]
                );
            }
        }
    }

    private function seedAuditEvents(): void
    {
        foreach (AuditDemoData::all() as $a) {
            $user = ! empty($a['actor_user_id']) ? User::where('account_ref', 'DEMO-USR-'.str_pad((string) $a['actor_user_id'], 4, '0', STR_PAD_LEFT))->first() : null;
            $fac = ! empty($a['facility']) && $a['facility'] !== 'National' ? Facility::where('name', $a['facility'])->first() : null;

            AuditEvent::firstOrCreate(
                ['audit_ref' => $a['audit_ref']],
                [
                    'actor_id' => $user?->id,
                    'facility_id' => $fac?->id,
                    'occurred_at' => $a['occurred_at'] ?? now(),
                    'recorded_at' => $a['occurred_at'] ?? now(),
                    'action' => $a['action'] ?? 'system.event',
                    'outcome' => $a['outcome'] ?? 'Success',
                    'resource_type' => $a['resource_type'] ?? 'System',
                    'resource_reference' => $a['resource_ref'] ?? null,
                    'correlation_id' => $a['correlation'] ?? Str::uuid()->toString(),
                    'description' => $a['description'] ?? null,
                    'changes' => [
                        'before' => $a['before'] ?? null,
                        'after' => $a['after'] ?? null,
                    ],
                ]
            );
        }
    }
}
