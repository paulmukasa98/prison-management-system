<?php

namespace App\Support\Demo;

/**
 * FacilityDemoData — Phase 5 canonical fictional facility records.
 *
 * IMPORTANT: All records are completely invented for UI demonstration.
 * Facility codes use the DEMO-FAC- prefix. Capacities, regions, and
 * establishment dates are fictional and not verified UPS data.
 *
 * This class is the CANONICAL source for facility names and IDs.
 * InmateDemoData::$facilities and TransferDemoData use the same 8
 * facility names — they are the string keys that map to these IDs.
 *
 * Facility IDs 1–8 correspond to InmateDemoData::$facilities index 0–7.
 */
class FacilityDemoData
{
    // -------------------------------------------------------------------------
    // Configurable reference lists
    // -------------------------------------------------------------------------

    public const TYPES = [
        'Maximum Security',
        'Medium Security',
        'Minimum Security',
        'Remand Home',
        'Open Prison',
    ];

    public const REGIONS = [
        'Central',
        'Northern',
        'Eastern',
        'Western',
    ];

    public const STATUSES = [
        'Active',
        'Inactive',
        'Under Renovation',
        'Decommissioned',
    ];

    // -------------------------------------------------------------------------
    // Canonical facility dataset — 8 records matching InmateDemoData::$facilities
    // -------------------------------------------------------------------------

    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'code' => 'DEMO-FAC-001',
                'name' => 'Luzira Upper Prison',
                'type' => 'Maximum Security',
                'region' => 'Central',
                'district' => 'Kampala',
                'location_desc' => 'Luzira peninsula, east of Kampala city centre.',
                'capacity' => 1_500,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1927-01-01',
                'notes' => 'Fictional record. Male maximum-security facility.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 2,
                'code' => 'DEMO-FAC-002',
                'name' => 'Murchison Bay Prison',
                'type' => 'Medium Security',
                'region' => 'Central',
                'district' => 'Kampala',
                'location_desc' => 'Luzira area, adjacent to Luzira Upper Prison.',
                'capacity' => 1_200,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1950-06-01',
                'notes' => 'Fictional record. Houses both male and female inmates.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 3,
                'code' => 'DEMO-FAC-003',
                'name' => 'Kampala Prison',
                'type' => 'Medium Security',
                'region' => 'Central',
                'district' => 'Kampala',
                'location_desc' => 'Kampala city, upper prison road.',
                'capacity' => 900,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1960-03-15',
                'notes' => 'Fictional record.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 4,
                'code' => 'DEMO-FAC-004',
                'name' => 'Gulu Prison',
                'type' => 'Medium Security',
                'region' => 'Northern',
                'district' => 'Gulu',
                'location_desc' => 'Gulu city centre, northern Uganda.',
                'capacity' => 800,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1962-07-04',
                'notes' => 'Fictional record. Northern regional facility.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 5,
                'code' => 'DEMO-FAC-005',
                'name' => 'Fort Portal Prison',
                'type' => 'Medium Security',
                'region' => 'Western',
                'district' => 'Kabarole',
                'location_desc' => 'Fort Portal town, western Uganda.',
                'capacity' => 600,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1965-11-20',
                'notes' => 'Fictional record. Western regional facility.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 6,
                'code' => 'DEMO-FAC-006',
                'name' => 'Soroti Prison',
                'type' => 'Medium Security',
                'region' => 'Eastern',
                'district' => 'Soroti',
                'location_desc' => 'Soroti town, eastern Uganda.',
                'capacity' => 400,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1970-04-10',
                'notes' => 'Fictional record. Eastern regional facility.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 7,
                'code' => 'DEMO-FAC-007',
                'name' => 'Kigo Prison',
                'type' => 'Medium Security',
                'region' => 'Central',
                'district' => 'Wakiso',
                'location_desc' => 'Kigo, Wakiso district, greater Kampala area.',
                'capacity' => 500,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1985-09-01',
                'notes' => 'Fictional record. Central region.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
            [
                'id' => 8,
                'code' => 'DEMO-FAC-008',
                'name' => 'Jinja Prison',
                'type' => 'Medium Security',
                'region' => 'Eastern',
                'district' => 'Jinja',
                'location_desc' => 'Jinja city, eastern Uganda.',
                'capacity' => 450,
                'capacity_date' => '2015-01-01',
                'capacity_notes' => 'Fictional capacity — not verified UPS data.',
                'status' => 'Active',
                'responsible_officer' => 'Demo Officer in Charge (demo role)',
                'established' => '1958-02-14',
                'notes' => 'Fictional record. Eastern regional facility.',
                'created_date' => '2024-01-01',
                'updated_date' => '2025-01-01',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public static function find(int $id): ?array
    {
        foreach (static::all() as $f) {
            if ($f['id'] === $id) {
                return $f;
            }
        }

        return null;
    }

    public static function findByName(string $name): ?array
    {
        foreach (static::all() as $f) {
            if ($f['name'] === $name) {
                return $f;
            }
        }

        return null;
    }

    /** Canonical flat list of facility names — matches InmateDemoData::$facilities. */
    public static function names(): array
    {
        return array_column(static::all(), 'name');
    }

    /** Summary counts for the directory page. */
    public static function summaryCounts(): array
    {
        $derived = static::derived();

        return [
            'total' => count(static::all()),
            'active' => count(array_filter(static::all(), fn ($f) => $f['status'] === 'Active')),
            'capacity' => array_sum(array_column(static::all(), 'capacity')),
            'population' => array_sum(array_column($derived, 'current_population')),
        ];
    }

    /** Occupancy percentage. Guard against zero capacity. */
    public static function occupancy(array $facility): float
    {
        if (! $facility['capacity']) {
            return 0.0;
        }

        return round(($facility['current_population'] / $facility['capacity']) * 100, 1);
    }

    /** Enrich a facility record with computed fields. */
    public static function enrich(array $f): array
    {
        $f = array_merge($f, static::derived()[$f['name']] ?? []);
        $f['occupancy_pct'] = static::occupancy($f);

        return $f;
    }

    /**
     * Population and staffing figures derived live from the inmate/transfer/
     * assignment fixtures — the single source of truth so every module's
     * figures reconcile. Facility records only carry declared capacity.
     *
     * @return array<string, array{current_population:int, male_population:int, female_population:int, remand_count:int, convicted_count:int, civil_debtor_count:int, personnel_count:int}> keyed by facility name
     */
    public static function derived(): array
    {
        $stats = [];

        foreach (static::names() as $name) {
            $stats[$name] = [
                'current_population' => 0,
                'male_population' => 0,
                'female_population' => 0,
                'remand_count' => 0,
                'convicted_count' => 0,
                'civil_debtor_count' => 0,
                'personnel_count' => 0,
            ];
        }

        // An inmate whose latest completed transfer moved them in is counted at
        // the destination; otherwise at their recorded facility.
        $currentFacility = [];
        foreach (TransferDemoData::all() as $transfer) {
            if ($transfer['status'] !== 'Completed') {
                continue;
            }
            $inmate = InmateDemoData::find($transfer['inmate_id']);
            if ($inmate && ($inmate['admission_date'] ?? '') <= ($transfer['arrival_date'] ?? '')) {
                $currentFacility[$transfer['inmate_id']] = $transfer['destination_facility'];
            }
        }

        foreach (InmateDemoData::active() as $inmate) {
            $facility = $currentFacility[$inmate['id']] ?? $inmate['facility'];
            if (! isset($stats[$facility])) {
                continue;
            }
            $stats[$facility]['current_population']++;
            match ($inmate['gender']) {
                'Male' => $stats[$facility]['male_population']++,
                'Female' => $stats[$facility]['female_population']++,
                default => null,
            };
            match ($inmate['classification']) {
                'Convicted' => $stats[$facility]['convicted_count']++,
                'Remand' => $stats[$facility]['remand_count']++,
                'Civil Debtor' => $stats[$facility]['civil_debtor_count']++,
                default => null,
            };
        }

        foreach (AssignmentDemoData::active() as $assignment) {
            $facility = FacilityDemoData::find($assignment['facility_id'])['name'] ?? null;
            if ($facility) {
                $stats[$facility]['personnel_count']++;
            }
        }

        return $stats;
    }
}
