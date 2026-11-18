<?php

namespace Tests\Feature;

use App\Support\Demo\AssignmentDemoData;
use App\Support\Demo\FacilityDemoData;
use App\Support\Demo\InmateDemoData;
use App\Support\Demo\PersonnelDemoData;
use App\Support\Demo\ReportDemoData;
use App\Support\Demo\TransferDemoData;
use App\Support\Demo\VisitDemoData;
use App\Support\Demo\VisitorDemoData;
use App\Support\DemoContext;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Guards the cross-cutting rule that demo figures reconcile across modules.
 */
class DemoDataReconciliationTest extends TestCase
{
    public function test_facility_populations_are_derived_from_active_inmate_records(): void
    {
        $derived = FacilityDemoData::derived();

        $this->assertSame(
            count(InmateDemoData::active()),
            array_sum(array_column($derived, 'current_population'))
        );
    }

    public function test_each_facility_custody_and_gender_splits_sum_to_its_population(): void
    {
        foreach (FacilityDemoData::derived() as $name => $stats) {
            $custodySum = $stats['convicted_count'] + $stats['remand_count'] + $stats['civil_debtor_count'];
            $genderSum = $stats['male_population'] + $stats['female_population'];

            $this->assertSame($stats['current_population'], $custodySum, "custody split mismatch at {$name}");
            $this->assertSame($stats['current_population'], $genderSum, "gender split mismatch at {$name}");
        }
    }

    public function test_dashboard_headcounts_match_fixture_records(): void
    {
        $stats = DemoContext::data()['stats'];

        $this->assertSame(count(InmateDemoData::active()), $stats['active_inmates']);
        $this->assertSame(PersonnelDemoData::summaryCounts()['active'], $stats['total_personnel']);
        $this->assertSame(TransferDemoData::pendingCount(), $stats['pending_transfers']);
    }

    public function test_dashboard_custody_breakdown_sums_to_active_inmates(): void
    {
        $stats = DemoContext::data()['stats'];

        $this->assertSame(
            $stats['active_inmates'],
            array_sum(array_column(DemoContext::data()['custody_stats'], 'count'))
        );
    }

    public function test_completed_transfers_agree_with_inmate_current_facility(): void
    {
        foreach (TransferDemoData::all() as $transfer) {
            if ($transfer['status'] !== 'Completed') {
                continue;
            }

            $inmate = InmateDemoData::find($transfer['inmate_id']);
            $this->assertNotNull($inmate, "transfer {$transfer['id']} references missing inmate");

            if ($inmate['status'] === 'Released') {
                continue;
            }

            // Fixture invariant: a completed transfer's destination is the inmate's recorded facility.
            $this->assertSame(
                $transfer['destination_facility'],
                $inmate['facility'],
                "inmate {$inmate['id']} is not at the destination of completed transfer {$transfer['id']}"
            );
        }
    }

    public function test_pending_count_covers_approval_and_clarification_stages(): void
    {
        $statuses = array_column(TransferDemoData::all(), 'status');

        $expected = count(array_filter(
            $statuses,
            fn ($status) => in_array($status, ['Pending Approval', 'Awaiting Clarification'])
        ));

        $this->assertSame($expected, TransferDemoData::pendingCount());
    }

    public function test_active_assignments_sum_to_derived_personnel_counts(): void
    {
        $derived = FacilityDemoData::derived();

        $this->assertSame(
            count(AssignmentDemoData::active()),
            array_sum(array_column($derived, 'personnel_count'))
        );
    }

    public function test_every_visit_references_an_existing_visitor_and_inmate(): void
    {
        foreach (VisitDemoData::all() as $visit) {
            $this->assertNotNull(VisitorDemoData::find($visit['visitor_id']), "visit {$visit['id']} references missing visitor");
            $this->assertNotNull(InmateDemoData::find($visit['inmate_id']), "visit {$visit['id']} references missing inmate");
        }
    }

    public function test_each_visit_facility_matches_the_inmate_current_location(): void
    {
        foreach (VisitDemoData::all() as $visit) {
            $inmate = InmateDemoData::find($visit['inmate_id']);

            $this->assertSame(
                $visit['facility'],
                $inmate['facility'],
                "visit {$visit['visit_ref']} is booked at {$visit['facility']} but inmate {$inmate['id']} is held at {$inmate['facility']}"
            );
        }
    }

    public function test_dashboard_visits_metric_matches_completed_fixture_visits(): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 12:00:00'));
        $stats = DemoContext::data()['stats'];

        $this->assertSame(
            count(VisitDemoData::completedBetween('2026-10-01', '2026-10-31')),
            $stats['visits_this_month']
        );
    }

    public function test_dashboard_periods_use_calendar_boundaries_and_no_synthetic_admissions(): void
    {
        $this->travelTo(Carbon::parse('2026-11-12 12:00:00'));

        $periods = DemoContext::data()['all_periods'];

        $this->assertSame('2026-11-01', $periods['this_month']['from']);
        $this->assertSame('2026-11-12', $periods['this_month']['to']);
        $this->assertSame('2026-10-01', $periods['last_month']['from']);
        $this->assertSame('2026-10-31', $periods['last_month']['to']);
        foreach ($periods as $period) {
            $this->assertSame(25, $period['active_inmates']);
            $this->assertSame(0, $period['new_admissions']);
            $this->assertSame(0, array_sum($period['admissions_chart']));
        }
    }

    public function test_all_time_monthly_report_keeps_events_instead_of_truncating_them_to_future_years(): void
    {
        $series = ReportDemoData::monthlySeries(ReportDemoData::range('all'));

        $this->assertSame(30, array_sum($series['admissions']));
        $this->assertSame(5, array_sum($series['discharges']));
        $this->assertSame(45, array_sum($series['visits']));
    }

    public function test_visitor_summary_counts_reconcile_with_records(): void
    {
        $counts = VisitorDemoData::summaryCounts();

        $this->assertSame(count(VisitorDemoData::all()), $counts['total']);
        $this->assertSame(
            count(array_filter(VisitorDemoData::all(), fn ($v) => $v['status'] === 'Registered')),
            $counts['registered']
        );
    }
}
