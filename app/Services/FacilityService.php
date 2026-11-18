<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FacilityService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Register a new prison facility with optional initial capacity.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Facility
    {
        if (! $actor->hasPermission('facilities.manage')) {
            abort(403, 'Unauthorized. You do not hold facility management privileges.');
        }

        return DB::transaction(function () use ($data, $actor) {
            $code = $data['code'] ?? ('FAC-'.strtoupper(Str::random(6)));

            $facility = Facility::create([
                'code' => $code,
                'name' => $data['name'],
                'type' => $data['type'],
                'region' => $data['region'],
                'district' => $data['district'],
                'location_description' => $data['location_description'] ?? null,
                'status' => $data['status'] ?? 'Active',
                'established_on' => $data['established_on'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            if (isset($data['capacity']) && $data['capacity'] !== null) {
                FacilityCapacity::create([
                    'facility_id' => $facility->id,
                    'capacity' => (int) $data['capacity'],
                    'effective_on' => $facility->established_on ?? now()->toDateString(),
                    'source_reference' => 'Initial Commissioning Declaration',
                    'notes' => 'Recorded at initial facility registration.',
                    'recorded_by' => $actor->id,
                ]);
            }

            $this->auditService->log(
                action: 'facility_created',
                outcome: 'Success',
                resourceType: 'Facility',
                resourceReference: $facility->code,
                changes: [
                    'facility_id' => $facility->id,
                    'name' => $facility->name,
                    'region' => $facility->region,
                    'type' => $facility->type,
                    'status' => $facility->status,
                ],
                description: "Created prison facility record {$facility->name} ({$facility->code}).",
                facilityId: $facility->id,
                actorId: $actor->id
            );

            return $facility;
        });
    }

    /**
     * Update facility details.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Facility $facility, array $data, User $actor): Facility
    {
        if (! $actor->hasPermission('facilities.manage', $facility->id)) {
            abort(403, 'Unauthorized. You do not hold management authority for this facility.');
        }

        $oldValues = $facility->only([
            'name', 'type', 'region', 'district', 'location_description', 'status', 'notes',
        ]);

        $facility->update(array_filter([
            'name' => $data['name'] ?? $facility->name,
            'type' => $data['type'] ?? $facility->type,
            'region' => $data['region'] ?? $facility->region,
            'district' => $data['district'] ?? $facility->district,
            'location_description' => $data['location_description'] ?? $facility->location_description,
            'status' => $data['status'] ?? $facility->status,
            'notes' => $data['notes'] ?? $facility->notes,
        ], fn ($v) => $v !== null));

        $newValues = $facility->only(array_keys($oldValues));

        $this->auditService->log(
            action: 'facility_updated',
            outcome: 'Success',
            resourceType: 'Facility',
            resourceReference: $facility->code,
            changes: [
                'before' => $oldValues,
                'after' => $newValues,
            ],
            description: "Updated operational details for facility {$facility->name}.",
            facilityId: $facility->id,
            actorId: $actor->id
        );

        return $facility;
    }

    /**
     * Record a formal capacity revision for a facility.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCapacity(Facility $facility, array $data, User $actor): FacilityCapacity
    {
        if (! $actor->hasPermission('facilities.manage', $facility->id)) {
            abort(403, 'Unauthorized. You do not hold capacity management privileges for this facility.');
        }

        return DB::transaction(function () use ($facility, $data, $actor) {
            $effectiveOn = $data['effective_on'] ?? now()->toDateString();

            $capacity = FacilityCapacity::updateOrCreate(
                [
                    'facility_id' => $facility->id,
                    'effective_on' => $effectiveOn,
                ],
                [
                    'capacity' => (int) $data['capacity'],
                    'source_reference' => $data['source_reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => $actor->id,
                ]
            );

            $this->auditService->log(
                action: 'facility_capacity_updated',
                outcome: 'Success',
                resourceType: 'Facility',
                resourceReference: $facility->code,
                changes: [
                    'facility_id' => $facility->id,
                    'capacity' => $capacity->capacity,
                    'effective_on' => $effectiveOn,
                    'source_reference' => $capacity->source_reference,
                ],
                description: "Recorded capacity update ({$capacity->capacity} beds) for facility {$facility->name}.",
                facilityId: $facility->id,
                actorId: $actor->id
            );

            return $capacity;
        });
    }

    /**
     * Format a Facility model into array expected by Blade templates.
     *
     * @return array<string, mixed>
     */
    public function toViewArray(Facility $facility): array
    {
        $currentCap = $facility->currentCapacity();
        $latestCapRecord = $facility->capacities()->where('effective_on', '<=', now()->toDateString())->latest('effective_on')->first();
        $population = $facility->activePopulation();
        $occupancyPct = ($currentCap !== null && $currentCap > 0)
            ? round(($population / $currentCap) * 100, 1)
            : null;

        $inmates = $facility->locations()->whereNull('left_at')->with('admission.inmate')->get();

        $malePop = 0;
        $femalePop = 0;
        $remandCount = 0;
        $convictedCount = 0;
        $civilDebtorCount = 0;

        foreach ($inmates as $loc) {
            $inmate = $loc->admission?->inmate;
            $class = $loc->admission?->classification;

            if ($inmate?->gender === 'Male') {
                $malePop++;
            } elseif ($inmate?->gender === 'Female') {
                $femalePop++;
            }

            if ($class === 'Remand') {
                $remandCount++;
            } elseif ($class === 'Convicted') {
                $convictedCount++;
            } elseif ($class === 'Civil Debtor') {
                $civilDebtorCount++;
            }
        }

        $oicAssignment = $facility->assignments()
            ->where('status', 'Active')
            ->where('start_date', '<=', now()->toDateString())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()))
            ->where(fn ($q) => $q->where('duty_title', 'like', '%Officer in Charge%')->orWhere('duty_title', 'like', '%Warden%'))
            ->with('personnel')
            ->first();

        $responsibleOfficer = $oicAssignment
            ? "{$oicAssignment->personnel->first_name} {$oicAssignment->personnel->last_name} ({$oicAssignment->duty_title})"
            : 'Officer in Charge';

        return [
            'id' => $facility->id,
            'code' => $facility->code,
            'name' => $facility->name,
            'type' => $facility->type ?? 'Medium Security',
            'region' => $facility->region ?? 'Central',
            'district' => $facility->district ?? 'Kampala',
            'location_desc' => $facility->location_description ?? 'Standard Prison Facility',
            'capacity' => $currentCap,
            'capacity_date' => $latestCapRecord?->effective_on?->format('Y-m-d'),
            'capacity_notes' => $latestCapRecord?->notes,
            'current_population' => $population,
            'occupancy_pct' => $occupancyPct,
            'status' => $facility->status,
            'responsible_officer' => $responsibleOfficer,
            'established' => $facility->established_on?->format('Y-m-d') ?? '1950-01-01',
            'notes' => $facility->notes,
            'personnel_count' => $facility->assignedPersonnelCount(),
            'male_population' => $malePop,
            'female_population' => $femalePop,
            'remand_count' => $remandCount,
            'convicted_count' => $convictedCount,
            'civil_debtor_count' => $civilDebtorCount,
            'created_date' => $facility->created_at?->format('Y-m-d') ?? date('Y-m-d'),
            'updated_date' => $facility->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
        ];
    }
}
