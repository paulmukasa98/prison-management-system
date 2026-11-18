<?php

namespace App\Support\Demo;

/**
 * ReportDemoData — Phase 7 report datasets.
 *
 * Every figure is derived from, or reconciles with, the canonical DEMO
 * fixtures (inmates, visits, transfers, personnel, facilities). The demo
 * population's custody history is fictional and concentrated in calendar
 * 2024, so reports default to that period; switching to another range shows
 * honest zero/empty states rather than invented numbers.
 */
class ReportDemoData
{
    public const RANGES = [
        '2026' => ['label' => 'Calendar 2026', 'from' => '2026-01-01', 'to' => '2026-12-31'],
        '2025' => ['label' => 'Calendar 2025', 'from' => '2025-01-01', 'to' => '2025-12-31'],
        '2024' => ['label' => 'Calendar 2024', 'from' => '2024-01-01', 'to' => '2024-12-31'],
        'all' => ['label' => 'All time', 'from' => '2000-01-01', 'to' => '2099-12-31'],
    ];

    /** @return array{from:string,to:string,label:string} */
    public static function range(?string $key): array
    {
        $key = isset(static::RANGES[$key]) ? $key : '2024';

        return ['label' => static::RANGES[$key]['label']] + array_slice(static::RANGES[$key], 1);
    }

    private static function inRange(string $date, array $range): bool
    {
        return $date >= $range['from'] && $date <= $range['to'];
    }

    /** Inmate records whose admission date falls in the range. */
    public static function admissionsIn(array $range): array
    {
        return array_values(array_filter(
            InmateDemoData::all(),
            fn ($i) => static::inRange($i['admission_date'], $range)
        ));
    }

    /** Released inmates whose actual discharge date falls in the range. */
    public static function dischargesIn(array $range): array
    {
        return array_values(array_filter(
            InmateDemoData::all(),
            fn ($i) => $i['status'] === 'Released'
                && ! empty($i['actual_discharge'])
                && static::inRange($i['actual_discharge'], $range)
        ));
    }

    /** Visits in range that reached a terminal outcome (completed family). */
    public static function visitsIn(array $range): array
    {
        return array_values(array_filter(
            VisitDemoData::all(),
            fn ($v) => static::inRange($v['visit_date'], $range)
        ));
    }

    /** Transfers requested in range. */
    public static function transfersRequestedIn(array $range): array
    {
        return array_values(array_filter(
            TransferDemoData::all(),
            fn ($t) => static::inRange($t['request_date'], $range)
        ));
    }

    /** Transfers physically completed (received) in range. */
    public static function transfersCompletedIn(array $range): array
    {
        return array_values(array_filter(
            TransferDemoData::all(),
            fn ($t) => $t['status'] === 'Completed'
                && ! empty($t['arrival_date'])
                && static::inRange(substr($t['arrival_date'], 0, 10), $range)
        ));
    }

    /** Population snapshot at range end: admitted on/before, not discharged before. */
    public static function populationAt(string $date): array
    {
        return array_values(array_filter(
            InmateDemoData::all(),
            fn ($i) => $i['admission_date'] <= $date
                && ($i['status'] !== 'Released' || ($i['actual_discharge'] ?? '9999') > $date)
        ));
    }

    /** Monthly series for admissions/discharges/visits across a range. */
    public static function monthlySeries(array $range, ?string $facility = null): array
    {
        $admissions = static::byFacility(static::admissionsIn($range), $facility);
        $discharges = static::byFacility(static::dischargesIn($range), $facility);
        $visits = static::byFacility(static::visitsIn($range), $facility);
        $months = [];
        $cursor = (new \DateTimeImmutable($range['from']))->modify('first day of this month');
        $end = new \DateTimeImmutable($range['to']);
        if ($cursor->diff($end)->y >= 2) {
            $dates = array_merge(array_column($admissions, 'admission_date'), array_column($discharges, 'actual_discharge'), array_column($visits, 'visit_date'));
            if ($dates === []) {
                return ['labels' => [], 'keys' => [], 'admissions' => [], 'discharges' => [], 'visits' => []];
            }
            $cursor = (new \DateTimeImmutable(min($dates)))->modify('first day of this month');
            $end = new \DateTimeImmutable(max($dates));
        }
        while ($cursor <= $end) {
            $months[$cursor->format('Y-m')] = $cursor->format('M y');
            $cursor = $cursor->modify('+1 month');
        }

        $adm = array_fill_keys(array_keys($months), 0);
        $dis = array_fill_keys(array_keys($months), 0);
        $vis = array_fill_keys(array_keys($months), 0);

        foreach ($admissions as $i) {
            $k = substr($i['admission_date'], 0, 7);
            if (isset($adm[$k])) {
                $adm[$k]++;
            }
        }
        foreach ($discharges as $i) {
            $k = substr($i['actual_discharge'], 0, 7);
            if (isset($dis[$k])) {
                $dis[$k]++;
            }
        }
        foreach ($visits as $v) {
            $k = substr($v['visit_date'], 0, 7);
            if (isset($vis[$k])) {
                $vis[$k]++;
            }
        }

        return [
            'labels' => array_values($months),
            'keys' => array_keys($months),
            'admissions' => array_values($adm),
            'discharges' => array_values($dis),
            'visits' => array_values($vis),
        ];
    }

    /** Facility-filtered subset helper. */
    public static function byFacility(array $records, ?string $facility): array
    {
        if (! $facility) {
            return $records;
        }

        return array_values(array_filter($records, fn ($r) => ($r['facility'] ?? null) === $facility));
    }

    /** @return array<string,int> counts keyed by a record field */
    public static function tally(array $records, string $field): array
    {
        $counts = [];
        foreach ($records as $record) {
            $value = $record[$field] ?? 'Unspecified';
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        ksort($counts);

        return $counts;
    }

    /** Occupancy rows reconciled with FacilityDemoData::derived(). */
    public static function occupancyRows(): array
    {
        $derived = FacilityDemoData::derived();
        $rows = [];
        foreach (FacilityDemoData::all() as $f) {
            $d = $derived[$f['name']];
            $rows[] = [
                'facility' => $f['name'],
                'population' => $d['current_population'],
                'capacity' => $f['capacity'],
                'occupancy_pct' => $f['capacity'] > 0
                    ? round(($d['current_population'] / $f['capacity']) * 100, 1)
                    : null,
            ];
        }

        return $rows;
    }

    /** Landing-page tiles: one per report category with headline figures. */
    public static function landingTiles(array $range): array
    {
        $active = InmateDemoData::active();

        return [
            [
                'key' => 'population',
                'title' => 'Inmate Population',
                'description' => 'Custody totals by facility, gender and classification.',
                'headline' => count($active),
                'unit' => 'in custody now',
            ],
            [
                'key' => 'movement',
                'title' => 'Admissions & Discharges',
                'description' => 'Custody episodes opened and closed within the period.',
                'headline' => count(static::admissionsIn($range)),
                'unit' => 'admitted · '.count(static::dischargesIn($range)).' discharged',
            ],
            [
                'key' => 'transfers',
                'title' => 'Transfers',
                'description' => 'Requests raised vs movements completed in the period.',
                'headline' => count(static::transfersRequestedIn($range)),
                'unit' => 'requested · '.count(static::transfersCompletedIn($range)).' completed',
            ],
            [
                'key' => 'visitation',
                'title' => 'Visitation',
                'description' => 'Visit occurrences and outcomes in the period.',
                'headline' => count(static::visitsIn($range)),
                'unit' => 'visits recorded',
            ],
            [
                'key' => 'personnel',
                'title' => 'Personnel Staffing',
                'description' => 'Active staff and their current postings.',
                'headline' => PersonnelDemoData::summaryCounts()['active'],
                'unit' => 'active personnel',
            ],
            [
                'key' => 'occupancy',
                'title' => 'Facility Occupancy',
                'description' => 'Population against recorded capacity (unknown where unrecorded).',
                'headline' => array_sum(array_column(static::occupancyRows(), 'population')),
                'unit' => 'across '.count(FacilityDemoData::all()).' facilities',
            ],
        ];
    }
}
