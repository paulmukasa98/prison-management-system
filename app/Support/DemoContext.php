<?php

namespace App\Support;

use App\Support\Demo\AuditDemoData;
use App\Support\Demo\FacilityDemoData;
use App\Support\Demo\InmateDemoData;
use App\Support\Demo\PersonnelDemoData;
use App\Support\Demo\ReportDemoData;
use App\Support\Demo\TransferDemoData;
use App\Support\Demo\UserDemoData;
use App\Support\Demo\VisitDemoData;

class DemoContext
{
    private static function facilityRows(): array
    {
        $derived = FacilityDemoData::derived();

        return array_values(array_map(fn (array $facility) => [
            'name' => $facility['name'],
            'count' => $derived[$facility['name']]['current_population'],
            'capacity' => $facility['capacity'],
        ], FacilityDemoData::all()));
    }

    public static function data(): array
    {
        $periods = static::livePeriods();
        $period = $periods['this_month'];
        $user = UserDemoData::enrich(UserDemoData::find(1));
        $initials = implode('', array_map(fn (string $name) => mb_substr($name, 0, 1), explode(' ', $user['name'])));

        return [
            'demo_user' => [
                'name' => $user['name'],
                'initials' => $initials,
                'role' => $user['role_name'],
                'role_slug' => $user['role_key'],
                'facility' => $user['facility_scope'],
                'facility_id' => null,
                'email' => $user['email'],
                'avatar' => null,
            ],
            'notification_count' => 0,
            'stats' => [
                'active_inmates' => $period['active_inmates'],
                'total_personnel' => $period['total_personnel'],
                'pending_transfers' => $period['pending_transfers'],
                'visits_this_month' => $period['visits'],
                'new_admissions' => $period['new_admissions'],
                'discharges' => $period['discharges'],
            ],
            'all_periods' => $periods,
            'current_period' => 'this_month',
            'recent_activities' => static::activities(),
            'facility_stats' => static::facilityRows(),
            'gender_stats' => $period['gender'],
            'custody_stats' => $period['custody'],
            'chart_admissions' => $period['admissions_chart'],
            'chart_discharges' => $period['discharges_chart'],
            'chart_months' => $period['months'],
            'period_options' => [
                'this_month' => 'This Month',
                'last_month' => 'Last Month',
                'last_3_months' => 'Last 3 Months',
                'this_year' => 'This Year',
            ],
            'phase' => 8,
            'phase_label' => 'Frontend QA & Handoff',
        ];
    }

    public static function periodsJson(): string
    {
        return json_encode(static::livePeriods(), JSON_THROW_ON_ERROR);
    }

    private static function livePeriods(): array
    {
        $today = now()->startOfDay();
        $ranges = [
            'this_month' => ['This Month', $today->copy()->startOfMonth(), $today],
            'last_month' => ['Last Month', $today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => ['Last 3 Months', $today->copy()->subMonthsNoOverflow(2)->startOfMonth(), $today],
            'this_year' => ['This Year', $today->copy()->startOfYear(), $today],
        ];
        $periods = [];
        foreach ($ranges as $key => [$label, $from, $to]) {
            $range = ['from' => $from->toDateString(), 'to' => $to->toDateString()];
            $series = ReportDemoData::monthlySeries($range);
            $periods[$key] = [
                'label' => $label,
                'from' => $range['from'],
                'to' => $range['to'],
                'active_inmates' => count(InmateDemoData::active()),
                'total_personnel' => PersonnelDemoData::summaryCounts()['active'],
                'pending_transfers' => TransferDemoData::pendingCount(),
                'visits' => count(VisitDemoData::completedBetween($range['from'], $range['to'])),
                'new_admissions' => count(ReportDemoData::admissionsIn($range)),
                'discharges' => count(ReportDemoData::dischargesIn($range)),
                'gender' => static::currentGender(),
                'custody' => static::currentCustody(),
                'admissions_chart' => $series['admissions'],
                'discharges_chart' => $series['discharges'],
                'months' => $series['labels'],
            ];
        }

        return $periods;
    }

    private static function currentGender(): array
    {
        $gender = ['male' => 0, 'female' => 0, 'unspecified' => 0];
        foreach (InmateDemoData::active() as $inmate) {
            $key = match ($inmate['gender']) {
                'Male' => 'male',
                'Female' => 'female',
                default => 'unspecified',
            };
            $gender[$key]++;
        }

        return $gender;
    }

    private static function currentCustody(): array
    {
        $counts = ReportDemoData::tally(InmateDemoData::active(), 'classification');

        return array_values(array_map(fn (string $classification) => [
            'label' => $classification,
            'count' => $counts[$classification] ?? 0,
        ], InmateDemoData::$classifications));
    }

    private static function activities(): array
    {
        $events = AuditDemoData::enriched();
        usort($events, fn (array $left, array $right) => strcmp($right['occurred_at'], $left['occurred_at']));

        return array_map(fn (array $event) => [
            'type' => match ($event['resource_type']) {
                'Inmate' => 'admission',
                'Transfer' => 'transfer',
                'Visit', 'Visitor' => 'visit',
                default => 'personnel',
            },
            'text' => AuditDemoData::actionLabel($event['action']),
            'detail' => $event['description'],
            'time' => $event['occurred_at'],
            'status' => match ($event['outcome']) {
                'Success' => 'success',
                'Denied' => 'warning',
                default => 'danger',
            },
            'outcome' => $event['outcome'],
            'color' => $event['outcome'] === 'Success' ? 'green' : 'yellow',
        ], array_slice($events, 0, 8));
    }

    public static function facilitiesJson(): string
    {
        return json_encode(static::facilityRows(), JSON_THROW_ON_ERROR);
    }
}
