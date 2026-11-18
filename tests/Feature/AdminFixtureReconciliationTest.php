<?php

namespace Tests\Feature;

use App\Support\Demo\AuditDemoData;
use App\Support\Demo\FacilityDemoData;
use App\Support\Demo\InmateDemoData;
use App\Support\Demo\PersonnelDemoData;
use App\Support\Demo\ReportDemoData;
use App\Support\Demo\RoleDemoData;
use App\Support\Demo\TransferDemoData;
use App\Support\Demo\UserDemoData;
use App\Support\Demo\VisitDemoData;
use App\Support\Demo\VisitorDemoData;
use Tests\TestCase;

class AdminFixtureReconciliationTest extends TestCase
{
    public function test_every_audit_actor_resolves_to_an_account_or_system(): void
    {
        foreach (AuditDemoData::all() as $event) {
            if ($event['actor_user_id'] === null) {
                continue;
            }
            $this->assertNotNull(
                UserDemoData::find($event['actor_user_id']),
                "Audit event {$event['audit_ref']} references unknown actor #{$event['actor_user_id']}"
            );
        }
    }

    public function test_every_audit_resource_reference_resolves_in_canonical_fixtures(): void
    {
        $sources = [
            'Inmate' => InmateDemoData::all(),
            'Transfer' => TransferDemoData::all(),
            'Visitor' => VisitorDemoData::all(),
            'Visit' => VisitDemoData::all(),
            'User' => UserDemoData::all(),
            'Facility' => FacilityDemoData::all(),
        ];

        foreach (AuditDemoData::all() as $event) {
            $type = $event['resource_type'];
            if (! isset($sources[$type])) {
                $this->assertTrue(in_array($type, ['Session', 'Report', 'AuditLog', 'System'], true), "Unexpected audit resource type {$type}");

                continue;
            }

            $match = collect($sources[$type])->first(fn ($r) => $r['id'] === $event['resource_id']);
            $this->assertNotNull($match, "{$event['audit_ref']} points at missing {$type} #{$event['resource_id']}");
            $expectedRef = match ($type) {
                'Inmate' => $match['prisoner_number'],
                'Transfer' => $match['transfer_ref'],
                'Visitor' => $match['visitor_ref'],
                'Visit' => $match['visit_ref'],
                'User' => $match['user_ref'],
                'Facility' => $match['code'],
            };
            $this->assertSame($expectedRef, $event['resource_ref'], "{$event['audit_ref']} ref mismatch for {$type}");
        }
    }

    public function test_role_grants_are_a_subset_of_the_permission_catalog(): void
    {
        $catalog = array_keys(RoleDemoData::all());

        foreach (RoleDemoData::roles() as $role) {
            foreach ($role['grants'] as $grant) {
                if ($grant === '*') {
                    continue;
                }
                $this->assertContains($grant, $catalog, "Role {$role['key']} grants unknown permission {$grant}");
            }
        }
    }

    public function test_each_personnel_record_has_at_most_one_account(): void
    {
        $linked = array_filter(array_column(UserDemoData::all(), 'personnel_id'));
        $this->assertCount(count($linked), array_unique($linked), 'Two accounts share one personnel record.');
    }

    public function test_report_landing_tiles_reconcile_with_canonical_fixtures(): void
    {
        $range = ReportDemoData::range('2024');
        $tiles = collect(ReportDemoData::landingTiles($range))->keyBy('key');

        $this->assertSame(count(InmateDemoData::active()), $tiles['population']['headline']);
        $this->assertSame(count(ReportDemoData::admissionsIn($range)), $tiles['movement']['headline']);
        $this->assertSame(count(ReportDemoData::visitsIn($range)), $tiles['visitation']['headline']);
        $this->assertSame(PersonnelDemoData::summaryCounts()['active'], $tiles['personnel']['headline']);
        $this->assertSame(
            count(array_filter(TransferDemoData::all(), fn ($t) => str_starts_with($t['request_date'], '2024'))),
            $tiles['transfers']['headline']
        );
        $this->assertSame(
            count(InmateDemoData::active()),
            $tiles['occupancy']['headline'],
            'Occupancy headline must equal the same active population, just grouped by facility.'
        );
    }

    public function test_audit_summary_counts_match_event_rows(): void
    {
        $counts = AuditDemoData::summaryCounts();

        $this->assertSame(count(AuditDemoData::all()), $counts['total']);
        $this->assertSame(
            $counts['success'] + $counts['failed'] + $counts['denied'],
            $counts['total']
        );
    }
}
