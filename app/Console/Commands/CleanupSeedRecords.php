<?php

namespace App\Console\Commands;

use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Discharge;
use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\Inmate;
use App\Models\LegalReference;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CleanupSeedRecords extends Command
{
    protected $signature = 'ups:demo-cleanup {--force : Archive the selected fixtures; without this option only show a preview}
        {--include-linked-facilities : Archive placeholder facility entries while retaining linked inmates and custody history}';

    protected $description = 'Archive obvious local test fixtures while preserving realistic names, custody links and immutable history';

    public function handle(AuditService $audit): int
    {
        if (! app()->environment('local', 'testing')) {
            $this->error('Seed cleanup is limited to local and testing environments.');

            return self::FAILURE;
        }

        return DB::transaction(function () use ($audit): int {
            $inmates = Inmate::orderBy('id')->lockForUpdate()->get();
            $inmateIds = $inmates->filter(fn (Inmate $inmate): bool => $this->placeholderName([$inmate->first_name, $inmate->middle_name, $inmate->last_name]))->modelKeys();
            $keptInmates = $inmates->whereNotIn('id', $inmateIds)->modelKeys();
            $staff = Personnel::orderBy('id')->lockForUpdate()->get();
            $protectedStaff = User::where('status', 'Active')->whereNotNull('personnel_id')->pluck('personnel_id')->all();
            $staffIds = $staff->filter(fn (Personnel $person): bool => $this->placeholderName([$person->first_name, $person->middle_name, $person->last_name])
                || ($person->first_name === 'Grace' && $person->middle_name === 'Akello' && preg_match('/\AOkot-[A-Za-z0-9]{6}\z/', $person->last_name) && preg_match('/[0-9]|[a-z][A-Z]|[A-Z]{2}/', substr($person->last_name, 5))))
                ->whereNotIn('id', $protectedStaff)->modelKeys();
            $visitorIds = Visitor::orderBy('id')->lockForUpdate()->get()->filter(fn (Visitor $visitor): bool => $this->placeholderName([$visitor->first_name, $visitor->middle_name, $visitor->last_name]))->modelKeys();
            $facilities = Facility::orderBy('id')->lockForUpdate()->get();
            $candidateFacilities = $facilities->filter(fn (Facility $facility): bool => $this->placeholderName([$facility->name])
                || str_starts_with($facility->code, 'FAC-TEST-')
                || preg_match('/\ASoroti Medium Security Prison [A-Za-z0-9]{5}\z/', $facility->name))->modelKeys();

            $protectedFacilities = array_unique([
                ...AdmissionEpisode::whereIn('inmate_id', $keptInmates)->pluck('admitting_facility_id')->all(),
                ...CustodyLocation::whereHas('admission', fn (Builder $query): Builder => $query->whereIn('inmate_id', $keptInmates))->pluck('facility_id')->all(),
                ...Transfer::whereHas('admission', fn (Builder $query): Builder => $query->whereIn('inmate_id', $keptInmates))->pluck('origin_facility_id')->all(),
                ...Transfer::whereHas('admission', fn (Builder $query): Builder => $query->whereIn('inmate_id', $keptInmates))->pluck('destination_facility_id')->all(),
                ...Visit::whereHas('admission', fn (Builder $query): Builder => $query->whereIn('inmate_id', $keptInmates))->pluck('facility_id')->all(),
            ]);
            $facilityIds = array_values(array_diff($candidateFacilities, $protectedFacilities));
            $heldFacilities = $facilities->whereIn('id', array_intersect($candidateFacilities, $protectedFacilities));
            $archivedFacilityIds = $this->option('include-linked-facilities') ? $candidateFacilities : $facilityIds;
            $episodeIds = AdmissionEpisode::whereIn('inmate_id', $inmateIds)->pluck('id')->all();
            $usedRoles = UserRoleAssignment::whereNull('ended_at')->whereHas('user', fn (Builder $query): Builder => $query->whereNull('users.deleted_at')->where('status', 'Active'))->pluck('role_id')->all();
            $roleIds = Role::whereNotIn('id', $usedRoles)->get()->filter(fn (Role $role): bool => $this->placeholderName([$role->name]))->modelKeys();
            $targets = [
                'inmates' => [Inmate::class, $inmateIds],
                'personnel' => [Personnel::class, $staffIds],
                'visitors' => [Visitor::class, $visitorIds],
                'facilities' => [Facility::class, $archivedFacilityIds],
                'admission_episodes' => [AdmissionEpisode::class, $episodeIds],
                'custody_locations' => [CustodyLocation::class, CustodyLocation::whereIn('admission_episode_id', $episodeIds)->orWhereIn('facility_id', $facilityIds)->pluck('id')->all()],
                'legal_references' => [LegalReference::class, LegalReference::whereIn('admission_episode_id', $episodeIds)->pluck('id')->all()],
                'discharges' => [Discharge::class, Discharge::whereIn('admission_episode_id', $episodeIds)->pluck('id')->all()],
                'transfers' => [Transfer::class, Transfer::whereIn('admission_episode_id', $episodeIds)->orWhereIn('origin_facility_id', $facilityIds)->orWhereIn('destination_facility_id', $facilityIds)->pluck('id')->all()],
                'visits' => [Visit::class, Visit::whereIn('admission_episode_id', $episodeIds)->orWhereIn('visitor_id', $visitorIds)->orWhereIn('facility_id', $facilityIds)->pluck('id')->all()],
                'personnel_assignments' => [PersonnelAssignment::class, PersonnelAssignment::whereIn('personnel_id', $staffIds)->orWhereIn('facility_id', $facilityIds)->pluck('id')->all()],
                'facility_capacities' => [FacilityCapacity::class, FacilityCapacity::whereIn('facility_id', $facilityIds)->pluck('id')->all()],
                'roles' => [Role::class, $roleIds],
            ];
            $counts = [];
            foreach ($targets as $table => [$model, $ids]) {
                $counts[$table] = count($ids);
            }
            $this->table(['Module', 'Records to archive'], collect($counts)->map(fn (int $count, string $table): array => [$table, $count])->all());
            foreach ($heldFacilities as $facility) {
                $this->warn(($this->option('include-linked-facilities') ? 'Archiving facility entry, retaining custody links: ' : 'Preserved linked facility: ').$facility->name);
            }
            if (! $this->option('force')) {
                $this->info('Preview only. Realistic names and their custody records are retained.');

                return self::SUCCESS;
            }
            if (array_sum($counts) === 0) {
                $this->info('No matching fixtures remain to archive.');

                return self::SUCCESS;
            }
            foreach ($targets as [$model, $ids]) {
                if ($ids !== []) {
                    $model::whereKey($ids)->delete();
                }
            }
            $audit->log(
                action: 'seed_fixtures_archived', outcome: 'Success', resourceType: 'DataMaintenance',
                changes: ['archived_counts' => $counts, 'retained_inmates' => count($keptInmates), 'preserved_custody_facility_ids' => $heldFacilities->modelKeys(), 'linked_facilities_archived' => (bool) $this->option('include-linked-facilities')],
                description: 'Local cleanup of placeholder identities and generated test labels. Realistic records and immutable events retained.'
            );
            $this->info('Selected fixtures archived. Operational listings and reports exclude them; recorded events remain intact.');

            return self::SUCCESS;
        }, 3);
    }

    /** @param list<string|null> $parts */
    private function placeholderName(array $parts): bool
    {
        $name = trim(implode(' ', array_filter($parts)));
        if (preg_match('/\b(?:fictional|dummy|sample|test|demo|demonstration|rollback|concurrency)\b/iu', $name)) {
            return true;
        }
        if (in_array(strtolower($name), ['other prisoner', 'national inmate', 'cross officer'], true)) {
            return true;
        }
        foreach ($parts as $part) {
            if (preg_match('/_[A-Za-z0-9]{4,6}\z/', $part ?? '')) {
                return true;
            }
        }

        return false;
    }
}
