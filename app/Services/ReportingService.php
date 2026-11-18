<?php

namespace App\Services;

use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Discharge;
use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\PersonnelAssignment;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportingService
{
    public const TITLES = [
        'population' => 'Inmate Population', 'movement' => 'Admissions and Discharges',
        'transfers' => 'Inmate Transfers', 'visitation' => 'Visitation',
        'personnel' => 'Personnel Staffing', 'occupancy' => 'Facility Occupancy',
    ];

    public function facilities(User $user, string $permission = 'reports.view'): Collection
    {
        return $this->scope(Facility::query(), $user, $permission, null, 'facilities.id')->orderBy('name')->get(['id', 'name']);
    }

    public function scope(Builder $query, User $user, string $permission, ?int $facilityId = null, string $column = 'facility_id'): Builder
    {
        $ids = $user->facilityIdsForPermission($permission);
        if ($permission === 'reports.export') {
            $readIds = $user->facilityIdsForPermission('reports.view');
            if ($readIds !== null) {
                $query->whereIn($column, $readIds);
            }
        }
        if ($ids !== null) {
            $query->whereIn($column, $ids);
        }
        if ($facilityId !== null) {
            $query->where($column, $facilityId);
        }

        return $query;
    }

    public function populationQuery(User $user, CarbonImmutable $at, ?int $facilityId = null, string $permission = 'reports.view'): Builder
    {
        return $this->scope(CustodyLocation::query(), $user, $permission, $facilityId, 'custody_locations.facility_id')
            ->join('admission_episodes', 'admission_episodes.id', '=', 'custody_locations.admission_episode_id')
            ->join('inmates', 'inmates.id', '=', 'admission_episodes.inmate_id')
            ->join('facilities', 'facilities.id', '=', 'custody_locations.facility_id')
            ->where('custody_locations.entered_at', '<=', $at)
            ->where(fn (Builder $query): Builder => $query->whereNull('custody_locations.left_at')->orWhere('custody_locations.left_at', '>', $at))
            ->where('admission_episodes.admitted_at', '<=', $at)
            ->where(fn (Builder $query): Builder => $query->whereNull('admission_episodes.closed_at')->orWhere('admission_episodes.closed_at', '>', $at));
    }

    /** @return array<string, mixed> */
    public function report(string $report, User $user, CarbonImmutable $from, CarbonImmutable $to, ?int $facilityId = null, string $permission = 'reports.view'): array
    {
        abort_unless(array_key_exists($report, self::TITLES), 404);
        $at = $to->min(CarbonImmutable::now());
        $populationRows = fn (): Collection => $this->populationQuery($user, $at, $facilityId, $permission)
            ->selectRaw("facilities.id AS facility_id, CASE WHEN facilities.deleted_at IS NULL THEN facilities.name ELSE 'Unconfirmed facility' END AS facility, COALESCE(inmates.gender, 'Unspecified') AS gender, COALESCE(admission_episodes.classification, 'Unspecified') AS classification, COUNT(DISTINCT admission_episodes.id) AS population")
            ->groupBy('facilities.id', 'facilities.name', 'facilities.deleted_at', 'inmates.gender', 'admission_episodes.classification')->orderBy('facilities.name')->get();

        if ($report === 'population') {
            $records = $populationRows();
            $rows = $records->map(fn ($row): array => [$row->facility, $row->gender, $row->classification, (int) $row->population])->all();
            $transit = $this->transitQuery($user, $at, $facilityId, $permission);
            $transitRows = (clone $transit)->selectRaw("COALESCE(inmates.gender, 'Unspecified') AS gender, COALESCE(admission_episodes.classification, 'Unspecified') AS classification, COUNT(DISTINCT admission_episodes.id) AS population")
                ->groupBy('inmates.gender', 'admission_episodes.classification')->get();
            foreach ($transitRows as $row) {
                $rows[] = ['In transit', $row->gender, $row->classification, (int) $row->population];
            }

            $ageBands = $this->populationQuery($user, $at, $facilityId, $permission)->selectRaw("CASE WHEN inmates.date_of_birth IS NULL THEN 'Unknown' WHEN TIMESTAMPDIFF(YEAR, inmates.date_of_birth, ?) < 25 THEN 'Under 25' WHEN TIMESTAMPDIFF(YEAR, inmates.date_of_birth, ?) < 35 THEN '25–34' WHEN TIMESTAMPDIFF(YEAR, inmates.date_of_birth, ?) < 50 THEN '35–49' ELSE '50+' END AS age_band, COUNT(DISTINCT admission_episodes.id) AS total", [$at->toDateString(), $at->toDateString(), $at->toDateString()])->groupBy('age_band')->orderBy('age_band')->pluck('total', 'age_band')->map(fn (mixed $count): int => (int) $count)->all();
            $transitAges = $transit->selectRaw("CASE WHEN inmates.date_of_birth IS NULL THEN 'Unknown' WHEN TIMESTAMPDIFF(YEAR, inmates.date_of_birth, ?) < 25 THEN 'Under 25' WHEN TIMESTAMPDIFF(YEAR, inmates.date_of_birth, ?) < 35 THEN '25–34' WHEN TIMESTAMPDIFF(YEAR, inmates.date_of_birth, ?) < 50 THEN '35–49' ELSE '50+' END AS age_band, COUNT(DISTINCT admission_episodes.id) AS total", [$at->toDateString(), $at->toDateString(), $at->toDateString()])->groupBy('age_band')->pluck('total', 'age_band');
            foreach ($transitAges as $band => $count) {
                $ageBands[$band] = ($ageBands[$band] ?? 0) + (int) $count;
            }

            return $this->result($report, ['Facility', 'Gender', 'Custody Classification', 'Population'], $rows, [
                'In custody at snapshot' => (int) $records->sum('population') + (int) $transitRows->sum('population'),
                'In transit' => (int) $transitRows->sum('population'),
            ], 'Snapshot: '.$at->setTimezone(config('reporting.timezone'))->format('Y-m-d H:i').'. Transit remains in the custody total, scoped to the origin facility, and is excluded from facility occupancy.') + ['ageBands' => $ageBands];
        }
        if ($report === 'movement') {
            $series = $this->series($user, $from, $to, $facilityId, $permission);
            $rows = [];
            foreach ($series['labels'] as $index => $label) {
                $rows[] = [$label, $series['admissions'][$index], $series['discharges'][$index]];
            }

            return $this->result($report, ['Month', 'Admissions', 'Executed Discharges'], $rows, [
                'Admissions' => array_sum($series['admissions']), 'Discharges' => array_sum($series['discharges']),
            ], 'Admissions use the admitting facility; discharges use the recorded custody location at execution.');
        }
        if ($report === 'transfers') {
            $query = $this->transferQuery($user, $permission, $facilityId)->whereBetween('requested_at', [$from, $to]);
            $records = (clone $query)->join('facilities AS origin', 'origin.id', '=', 'transfers.origin_facility_id')
                ->join('facilities AS destination', 'destination.id', '=', 'transfers.destination_facility_id')
                ->selectRaw('origin.name AS origin, destination.name AS destination, transfers.state, COUNT(*) AS total')
                ->groupBy('origin.name', 'destination.name', 'transfers.state')->orderBy('origin.name')->orderBy('destination.name')->orderBy('transfers.state')->get();

            return $this->result($report, ['Origin', 'Destination', 'Current State', 'Requests'], $records->map(fn ($row): array => [$row->origin, $row->destination, $row->state, (int) $row->total])->all(), [
                'Requested in period' => (clone $query)->count(),
                'Received in period' => $this->transferQuery($user, $permission, $facilityId)->whereBetween('received_at', [$from, $to])->count(),
            ], 'State counts describe requests made in the selected period. Receiving counts use the receiving timestamp.');
        }
        if ($report === 'visitation') {
            $query = $this->scope(Visit::query(), $user, $permission, $facilityId)->whereBetween('scheduled_start_at', [$from, $to]);
            $records = (clone $query)->join('facilities', 'facilities.id', '=', 'visits.facility_id')
                ->selectRaw('facilities.name AS facility, visits.state, visits.purpose, COUNT(*) AS total')
                ->groupBy('facilities.name', 'visits.state', 'visits.purpose')->orderBy('facilities.name')->orderBy('visits.state')->orderBy('visits.purpose')->get();

            return $this->result($report, ['Facility', 'Current State', 'Purpose', 'Visits'], $records->map(fn ($row): array => [$row->facility, $row->state, $row->purpose ?? 'Unspecified', (int) $row->total])->all(), [
                'Scheduled start in period' => (clone $query)->count(),
                'Completed in period' => $this->scope(Visit::query(), $user, $permission, $facilityId)->where('state', 'Completed')->whereBetween('checked_out_at', [$from, $to])->count(),
            ], 'Visit occurrences remain separate from visitor identities. Personal contact and identity document data are excluded.');
        }
        if ($report === 'personnel') {
            $records = $this->scope(PersonnelAssignment::query(), $user, $permission, $facilityId, 'personnel_assignments.facility_id')
                ->join('personnel', 'personnel.id', '=', 'personnel_assignments.personnel_id')
                ->join('facilities', 'facilities.id', '=', 'personnel_assignments.facility_id')
                ->where('personnel.employment_status', 'Active')->where('personnel_assignments.start_date', '<=', $at->toDateString())
                ->where(fn (Builder $query): Builder => $query->whereNull('personnel_assignments.end_date')->orWhere('personnel_assignments.end_date', '>=', $at->toDateString()))
                ->selectRaw("facilities.name AS facility, personnel.rank, personnel.department, COALESCE(personnel.gender, 'Unspecified') AS gender, COUNT(DISTINCT personnel.id) AS total")
                ->groupBy('facilities.name', 'personnel.rank', 'personnel.department', 'personnel.gender')->orderBy('facilities.name')->orderBy('personnel.rank')->get();

            $staffTotal = (int) $records->sum('total');

            return $this->result($report, ['Facility', 'Rank', 'Department', 'Gender', 'Posted Staff'], $records->map(fn ($row): array => [$row->facility, $row->rank, $row->department, $row->gender, (int) $row->total])->all(), [
                'Posted active staff' => $staffTotal,
                'Prisoners per posted officer' => $staffTotal > 0 ? round($populationRows()->sum('population') / $staffTotal, 1) : 0,
            ], 'Uses posting dates at the snapshot and current employment status. Historical employment status is not reconstructed.');
        }

        $populations = $populationRows()->groupBy('facility_id')->map(fn (Collection $rows): int => (int) $rows->sum('population'));
        $facilities = $this->scope(Facility::query(), $user, $permission, $facilityId, 'facilities.id')
            ->select(['facilities.id', 'facilities.name'])->addSelect([
                'recorded_capacity' => FacilityCapacity::select('capacity')->whereColumn('facility_id', 'facilities.id')
                    ->where('effective_on', '<=', $at->toDateString())->orderByDesc('effective_on')->orderByDesc('id')->limit(1),
            ])->orderBy('name')->get();
        $rows = $facilities->map(function (Facility $facility) use ($populations): array {
            $population = $populations[$facility->id] ?? 0;
            $capacity = $facility->recorded_capacity === null ? null : (int) $facility->recorded_capacity;

            return [$facility->name, $population, $capacity ?? 'Unknown', $capacity !== null && $capacity > 0 ? round($population / $capacity * 100, 1).'%' : 'Undefined'];
        })->all();

        $unconfirmedPopulation = $populations->except($facilities->modelKeys())->sum();
        if ($unconfirmedPopulation > 0) {
            $rows[] = ['Unconfirmed facility', $unconfirmedPopulation, 'Unknown', 'Undefined'];
        }

        return $this->result($report, ['Facility', 'Population', 'Recorded Capacity', 'Occupancy'], $rows, [
            'Population in facilities' => $populations->sum(), 'Capacity unknown' => $facilities->whereNull('recorded_capacity')->count() + ($unconfirmedPopulation > 0 ? 1 : 0),
        ], 'Capacity uses the latest effective record on or before the snapshot. Zero and unknown capacity remain distinct.');
    }

    /** @return array<string, mixed> */
    private function result(string $report, array $columns, array $rows, array $summary, string $note): array
    {
        return ['key' => $report, 'title' => self::TITLES[$report], 'columns' => $columns, 'rows' => $rows, 'summary' => $summary, 'note' => $note];
    }

    public function transferQuery(User $user, string $permission, ?int $facilityId = null): Builder
    {
        $ids = $user->facilityIdsForPermission($permission);
        $query = Transfer::query();
        if ($permission === 'reports.export') {
            $readIds = $user->facilityIdsForPermission('reports.view');
            if ($readIds !== null) {
                $query->where(fn (Builder $query): Builder => $query->whereIn('origin_facility_id', $readIds)->orWhereIn('destination_facility_id', $readIds));
            }
        }
        if ($ids !== null) {
            $query->where(fn (Builder $query): Builder => $query->whereIn('origin_facility_id', $ids)->orWhereIn('destination_facility_id', $ids));
        }
        if ($facilityId !== null) {
            $query->where(fn (Builder $query): Builder => $query->where('origin_facility_id', $facilityId)->orWhere('destination_facility_id', $facilityId));
        }

        return $query;
    }

    private function transitQuery(User $user, CarbonImmutable $at, ?int $facilityId, string $permission): Builder
    {
        return AdmissionEpisode::query()->join('inmates', 'inmates.id', '=', 'admission_episodes.inmate_id')
            ->where('admission_episodes.admitted_at', '<=', $at)
            ->where(fn (Builder $query): Builder => $query->whereNull('admission_episodes.closed_at')->orWhere('admission_episodes.closed_at', '>', $at))
            ->whereDoesntHave('locations', fn (Builder $query): Builder => $query->where('entered_at', '<=', $at)
                ->where(fn (Builder $query): Builder => $query->whereNull('left_at')->orWhere('left_at', '>', $at)))
            ->whereHas('transfers', function (Builder $query) use ($user, $at, $facilityId, $permission): void {
                $this->scope($query, $user, $permission, $facilityId, 'origin_facility_id')
                    ->where('dispatched_at', '<=', $at)
                    ->where(fn (Builder $query): Builder => $query->whereNull('received_at')->orWhere('received_at', '>', $at));
            });
    }

    private function dischargeQuery(User $user, string $permission, ?int $facilityId = null): Builder
    {
        return Discharge::where('state', 'Executed')->whereHas('admission.locations', function (Builder $query) use ($user, $permission, $facilityId): void {
            $this->scope($query, $user, $permission, $facilityId, 'custody_locations.facility_id')
                ->whereColumn('custody_locations.left_at', 'discharges.executed_at');
        });
    }

    /** @return array{labels:list<string>, admissions:list<int>, discharges:list<int>, visits:list<int>} */
    public function series(User $user, CarbonImmutable $from, CarbonImmutable $to, ?int $facilityId = null, string $permission = 'reports.view'): array
    {
        $series = ['labels' => [], 'admissions' => [], 'discharges' => [], 'visits' => []];
        $timezone = config('reporting.timezone');
        for ($month = $from->setTimezone($timezone)->startOfMonth(); $month->lte($to); $month = $month->addMonth()) {
            $start = $month->utc()->max($from);
            $end = $month->endOfMonth()->utc()->min($to);
            $series['labels'][] = $month->format('M Y');
            $series['admissions'][] = $this->scope(AdmissionEpisode::query(), $user, $permission, $facilityId, 'admitting_facility_id')->whereBetween('admitted_at', [$start, $end])->count();
            $series['discharges'][] = $this->dischargeQuery($user, $permission, $facilityId)->whereBetween('executed_at', [$start, $end])->count();
            $series['visits'][] = $this->scope(Visit::query(), $user, $permission, $facilityId)->where('state', 'Completed')->whereBetween('checked_out_at', [$start, $end])->count();
        }

        return $series;
    }

    /** @return array<string, mixed> */
    public function dashboard(User $user): array
    {
        $timezone = config('reporting.timezone');
        $today = CarbonImmutable::now($timezone);
        $periods = [];
        $ranges = [
            'this_month' => [$today->startOfMonth(), $today],
            'last_month' => [$today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()],
            'last_3_months' => [$today->subMonthsNoOverflow(2)->startOfMonth(), $today],
            'this_year' => [$today->startOfYear(), $today],
        ];
        $population = $this->report('population', $user, $today->startOfMonth()->utc(), $today->utc(), permission: 'inmates.read');
        $gender = ['male' => 0, 'female' => 0, 'unspecified' => 0];
        $custody = [];
        $facilities = [];
        foreach ($population['rows'] as [$facility, $sex, $classification, $count]) {
            $gender[match ($sex) {
                'Male' => 'male', 'Female' => 'female', default => 'unspecified'
            }] += $count;
            $custody[$classification] = ($custody[$classification] ?? 0) + $count;
            $facilities[$facility] = ($facilities[$facility] ?? 0) + $count;
        }
        $staff = $this->report('personnel', $user, $today->startOfMonth()->utc(), $today->utc(), permission: 'personnel.read');
        foreach ($ranges as $key => [$from, $to]) {
            $series = $this->series($user, $from->utc(), $to->utc(), permission: 'inmates.read');
            $visitCount = $this->scope(Visit::query(), $user, 'visitors.read')->where('state', 'Completed')->whereBetween('checked_out_at', [$from->utc(), $to->utc()])->count();
            $periods[$key] = [
                'label' => match ($key) {
                    'this_month' => 'This Month', 'last_month' => 'Last Month', 'last_3_months' => 'Last 3 Months', default => 'This Year'
                },
                'active_inmates' => array_sum($gender), 'total_personnel' => $staff['summary']['Posted active staff'],
                'pending_transfers' => $this->transferQuery($user, 'transfers.request')->whereIn('state', ['Pending Approval', 'Awaiting Clarification'])->count(),
                'visits' => $visitCount, 'new_admissions' => array_sum($series['admissions']), 'discharges' => array_sum($series['discharges']),
                'gender' => $gender, 'custody' => collect($custody)->map(fn (int $count, string $label): array => ['label' => $label, 'count' => $count, 'color' => '#2563EB'])->values()->all(),
                'months' => $series['labels'], 'admissions_chart' => $series['admissions'], 'discharges_chart' => $series['discharges'],
                'from' => $from->toDateString(), 'to' => $to->toDateString(),
            ];
        }

        $occupancy = $this->report('occupancy', $user, $today->startOfMonth()->utc(), $today->utc(), permission: 'inmates.read');

        return [
            'periods' => $periods,
            'facilities' => collect($occupancy['rows'])->map(fn (array $row): array => ['name' => $row[0], 'count' => $row[1], 'capacity' => is_int($row[2]) ? $row[2] : null])->all(),
        ];
    }
}
