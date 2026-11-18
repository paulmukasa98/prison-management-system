<?php

namespace App\Services;

use App\Models\AdmissionEpisode;
use App\Models\Discharge;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InmateService
{
    public function __construct(
        protected AuditService $auditService,
        protected VisitService $visitService
    ) {}

    /**
     * Admit a new inmate or readmit a previously discharged inmate into custody.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function admit(array $data, User $actor): Inmate
    {
        $facilityId = $this->resolveFacilityId($data, $actor);

        // Check if actor has permission to admit at this facility
        if (! $actor->hasPermission('inmates.admit', $facilityId)) {
            abort(403, 'Unauthorized. You do not hold admission authority for this facility.');
        }

        // Deduplication: look for existing inmate by national/official ID or (name + DOB)
        $inmate = null;
        if (! empty($data['id_number']) && ! empty($data['id_type'])) {
            $inmate = Inmate::where('id_type', $data['id_type'])
                ->where('id_number', $data['id_number'])
                ->first();
        }

        if (! $inmate && ! empty($data['first_name']) && ! empty($data['last_name']) && ! empty($data['date_of_birth'])) {
            $inmate = Inmate::where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->whereDate('date_of_birth', $data['date_of_birth'])
                ->first();
        }

        // Overlapping custody episode prevention
        if ($inmate) {
            $activeEpisode = $inmate->admissions()->whereNull('closed_at')->first();
            if ($activeEpisode) {
                throw ValidationException::withMessages([
                    'inmate' => "An active custody episode already exists for {$inmate->first_name} {$inmate->last_name} (Admission Ref: {$activeEpisode->admission_ref}). Overlapping active admissions are prohibited.",
                ]);
            }
        }

        return DB::transaction(function () use ($data, $inmate, $facilityId, $actor) {
            $isNewInmate = ! $inmate;

            if ($isNewInmate) {
                if (app()->isProduction() && empty($data['prisoner_number'])) {
                    throw ValidationException::withMessages(['prisoner_number' => 'Supply the prisoner number issued under the approved institutional policy.']);
                }
                $identityRef = 'INM-'.date('Y').'-'.strtoupper(Str::random(6));
                $prisonerNumber = $data['prisoner_number']
                    ?? ('DEV-PN-'.Str::uuid());

                $inmate = Inmate::create([
                    'identity_ref' => $identityRef,
                    'prisoner_number' => $prisonerNumber,
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['date_of_birth'],
                    'nationality' => $data['nationality'] ?? 'Ugandan',
                    'district' => $data['district'] ?? null,
                    'id_type' => $data['id_type'] ?? null,
                    'id_number' => $data['id_number'] ?? null,
                    'kin_name' => $data['kin_name'] ?? null,
                    'kin_relationship' => $data['kin_relationship'] ?? null,
                    'kin_contact' => $data['kin_contact'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            // Create admission episode
            $admissionRef = $data['admission_ref'] ?? ('ADM-'.Str::uuid());
            $admittedAt = ! empty($data['admission_date'])
                ? Carbon::parse($data['admission_date'])->setTimeFromTimeString($data['admission_time'] ?? now()->toTimeString())
                : now();

            /** @var AdmissionEpisode $episode */
            $episode = $inmate->admissions()->create([
                'admission_ref' => $admissionRef,
                'admitting_facility_id' => $facilityId,
                'admitted_at' => $admittedAt,
                'state' => 'Active',
                'classification' => $data['classification'],
                'legal_basis' => $data['legal_basis'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create initial custody location
            $episode->locations()->create([
                'location_ref' => 'LOC-'.date('Y').'-'.strtoupper(Str::random(6)),
                'facility_id' => $facilityId,
                'entered_at' => $admittedAt,
                'left_at' => null,
            ]);

            // Create primary legal reference
            $legalRef = 'LGL-'.date('Y').'-'.strtoupper(Str::random(6));
            $episode->legalReferences()->create([
                'reference' => $legalRef,
                'type' => $data['classification'] === 'Convicted' ? 'Committal Warrant' : 'Remand Warrant',
                'external_reference' => $data['warrant_ref'] ?? null,
                'issuing_authority' => $data['issuing_authority'] ?? 'Judiciary of Uganda',
                'issued_on' => $admittedAt->toDateString(),
                'case_reference' => $data['case_ref'] ?? null,
                'next_court_on' => ! empty($data['next_court_date']) ? $data['next_court_date'] : null,
                'sentence_text' => $data['sentence'] ?? null,
                'supplied_release_on' => ! empty($data['expected_release']) ? $data['expected_release'] : null,
            ]);

            // Audit event
            $this->auditService->log(
                action: 'inmate_admitted',
                outcome: 'Success',
                resourceType: 'Inmate',
                resourceReference: $inmate->prisoner_number,
                changes: [
                    'inmate_id' => $inmate->id,
                    'admission_ref' => $episode->admission_ref,
                    'facility_id' => $facilityId,
                    'classification' => $episode->classification,
                    'is_readmission' => ! $isNewInmate,
                ],
                description: "Inmate {$inmate->first_name} {$inmate->last_name} admitted to custody under {$episode->classification}.",
                facilityId: $facilityId,
                actorId: $actor->id
            );

            return $inmate;
        });
    }

    /**
     * Update an inmate's personal and administrative details.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Inmate $inmate, array $data, User $actor): Inmate
    {
        $facilityId = $inmate->currentFacilityId();

        if (! $actor->hasPermission('inmates.edit', $facilityId)) {
            abort(403, 'Unauthorized. You do not hold editing privileges for this inmate record.');
        }

        $oldValues = $inmate->only([
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
            'nationality', 'district', 'id_type', 'id_number', 'kin_name',
            'kin_relationship', 'kin_contact', 'notes',
        ]);

        $inmate->update(array_filter([
            'first_name' => $data['first_name'] ?? $inmate->first_name,
            'middle_name' => $data['middle_name'] ?? $inmate->middle_name,
            'last_name' => $data['last_name'] ?? $inmate->last_name,
            'gender' => $data['gender'] ?? $inmate->gender,
            'date_of_birth' => $data['date_of_birth'] ?? $inmate->date_of_birth,
            'nationality' => $data['nationality'] ?? $inmate->nationality,
            'district' => $data['district'] ?? $inmate->district,
            'id_type' => $data['id_type'] ?? $inmate->id_type,
            'id_number' => $data['id_number'] ?? $inmate->id_number,
            'kin_name' => $data['kin_name'] ?? $inmate->kin_name,
            'kin_relationship' => $data['kin_relationship'] ?? $inmate->kin_relationship,
            'kin_contact' => $data['kin_contact'] ?? $inmate->kin_contact,
            'notes' => $data['notes'] ?? $inmate->notes,
        ], fn ($val) => $val !== null));

        $newValues = $inmate->only(array_keys($oldValues));

        $this->auditService->log(
            action: 'inmate_updated',
            outcome: 'Success',
            resourceType: 'Inmate',
            resourceReference: $inmate->prisoner_number,
            changes: [
                'before' => $oldValues,
                'after' => $newValues,
            ],
            description: "Corrected administrative record for inmate {$inmate->prisoner_number}.",
            facilityId: $facilityId,
            actorId: $actor->id
        );

        return $inmate;
    }

    /**
     * Authorize and record a formal custody discharge.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function discharge(Inmate $inmate, array $data, User $actor): Discharge
    {
        $facilityId = $inmate->currentFacilityId();

        if (! $actor->hasPermission('inmates.discharge', $facilityId)) {
            abort(403, 'Unauthorized. You do not hold discharge authorization authority for this facility.');
        }

        /** @var AdmissionEpisode|null $episode */
        $episode = $inmate->admissions()->whereNull('closed_at')->latest('admitted_at')->first();

        if (! $episode) {
            throw ValidationException::withMessages([
                'inmate' => 'This inmate does not have an active custody episode to discharge.',
            ]);
        }

        return DB::transaction(function () use ($inmate, $episode, $data, $facilityId, $actor) {
            $episode = AdmissionEpisode::whereKey($episode->id)->lockForUpdate()->firstOrFail();
            if ($episode->closed_at !== null) {
                throw ValidationException::withMessages(['inmate' => 'This custody episode is already closed.']);
            }
            $this->visitService->invalidateForEpisode($episode, $actor, 'Custody episode discharged. A new visit request is required for any later admission.');
            $now = now();
            $dischargeDate = ! empty($data['discharge_date'])
                ? Carbon::parse($data['discharge_date'])->setTimeFromTimeString($now->toTimeString())
                : $now;

            // Close active custody location
            $episode->locations()->whereNull('left_at')->update(['left_at' => $dischargeDate]);

            // Close active custody episode
            $episode->update([
                'closed_at' => $dischargeDate,
                'state' => 'Discharged',
            ]);

            $latestLegalRef = $episode->legalReferences()->latest('issued_on')->first();

            $requestedAt = $dischargeDate->copy()->subMinutes(5);
            $authorizedAt = $dischargeDate->copy()->subMinutes(2);
            $executedAt = $dischargeDate;

            $discharge = Discharge::create([
                'discharge_ref' => 'DCH-'.date('Y').'-'.strtoupper(Str::random(6)),
                'admission_episode_id' => $episode->id,
                'legal_reference_id' => $latestLegalRef?->id,
                'reason' => $data['discharge_reason'],
                'state' => 'Executed',
                'authority_reference' => $data['auth_ref'],
                'requested_at' => $requestedAt,
                'authorized_at' => $authorizedAt,
                'executed_at' => $executedAt,
                'requested_by' => $actor->id,
                'authorized_by' => $actor->id,
                'executed_by' => $actor->id,
                'notes' => $data['discharge_notes'] ?? null,
            ]);

            $this->auditService->log(
                action: 'inmate_discharged',
                outcome: 'Success',
                resourceType: 'Inmate',
                resourceReference: $inmate->prisoner_number,
                changes: [
                    'inmate_id' => $inmate->id,
                    'admission_ref' => $episode->admission_ref,
                    'discharge_ref' => $discharge->discharge_ref,
                    'reason' => $discharge->reason,
                    'authority_reference' => $discharge->authority_reference,
                ],
                description: "Formally discharged inmate {$inmate->prisoner_number} from custody ({$discharge->reason}).",
                facilityId: $facilityId,
                actorId: $actor->id
            );

            return $discharge;
        });
    }

    /**
     * Convert an Eloquent Inmate model into the view payload expected by Blade components.
     *
     * @return array<string, mixed>
     */
    public function toViewArray(Inmate $inmate): array
    {
        $inmate->loadMissing([
            'photoLink.document',
            'admissions.admittingFacility',
            'admissions.legalReferences',
            'admissions.discharges',
            'admissions.locations.facility',
        ]);

        $activeEpisode = $inmate->admissions->firstWhere('closed_at', null)
            ?? $inmate->admissions->sortByDesc('admitted_at')->first();

        $latestLegal = $activeEpisode?->legalReferences->sortByDesc('issued_on')->first();
        $latestDischarge = $activeEpisode?->discharges->sortByDesc('executed_at')->first();

        $currentLocation = $activeEpisode?->locations->firstWhere('left_at', null)
            ?? ($activeEpisode?->closed_at !== null ? $activeEpisode?->locations->sortByDesc('entered_at')->first() : null);
        $facilityName = $currentLocation?->facility?->name
            ?? $activeEpisode?->admittingFacility?->name
            ?? 'Unassigned Facility';
        $facility = $currentLocation?->facility ?? $activeEpisode?->admittingFacility;
        if ($facility?->trashed()) {
            $facilityName = 'Facility assignment requires correction';
        }

        $dob = $inmate->date_of_birth ? Carbon::parse($inmate->date_of_birth) : null;
        $fullName = trim($inmate->first_name.' '.($inmate->middle_name ? $inmate->middle_name.' ' : '').$inmate->last_name);
        $initials = strtoupper(substr($inmate->first_name, 0, 1).substr($inmate->last_name, 0, 1));

        $status = 'In Custody';
        if (! $activeEpisode || $activeEpisode->closed_at !== null) {
            $status = 'Released';
        } elseif ($activeEpisode->classification === 'Remand') {
            $status = 'Remanded';
        }

        return [
            'id' => $inmate->id,
            'prisoner_number' => $inmate->prisoner_number ?? ('PN-'.$inmate->id),
            'identity_ref' => $inmate->identity_ref,
            'first_name' => $inmate->first_name,
            'middle_name' => $inmate->middle_name,
            'last_name' => $inmate->last_name,
            'full_name' => $fullName,
            'initials' => $initials,
            'gender' => $inmate->gender ?? 'Unspecified',
            'date_of_birth' => $inmate->date_of_birth?->format('Y-m-d'),
            'age' => $dob?->age ?? '—',
            'nationality' => $inmate->nationality ?? 'Ugandan',
            'district' => $inmate->district ?? '—',
            'id_type' => $inmate->id_type ?? 'None',
            'id_number' => $inmate->id_number ?? '—',
            'facility' => $facilityName,
            'facility_id' => $currentLocation?->facility_id ?? $activeEpisode?->admitting_facility_id,
            'classification' => $activeEpisode?->classification ?? 'Remand',
            'status' => $status,
            'admission_date' => $activeEpisode?->admitted_at?->format('Y-m-d') ?? date('Y-m-d'),
            'admission_ref' => $activeEpisode?->admission_ref ?? 'ADM-PENDING',
            'legal_basis' => $activeEpisode?->legal_basis ?? 'Court Remand Order',
            'warrant_ref' => $latestLegal?->external_reference ?? '—',
            'issuing_authority' => $latestLegal?->issuing_authority ?? 'Judiciary of Uganda',
            'case_ref' => $latestLegal?->case_reference ?? '—',
            'next_court_date' => $latestLegal?->next_court_on?->format('Y-m-d'),
            'sentence' => $latestLegal?->sentence_text,
            'expected_release' => $latestLegal?->supplied_release_on?->format('Y-m-d'),
            'actual_discharge' => $latestDischarge?->executed_at?->format('Y-m-d'),
            'discharge_reason' => $latestDischarge?->reason,
            'discharge_auth_ref' => $latestDischarge?->authority_reference,
            'kin_name' => $inmate->kin_name,
            'kin_relationship' => $inmate->kin_relationship,
            'kin_contact' => $inmate->kin_contact,
            'notes' => $inmate->notes,
            'photo' => $inmate->photoLink?->document?->state === 'Available' ? route('inmates.photo', $inmate->id) : null,
        ];
    }

    /**
     * Resolve facility ID from input or actor's authorized scope.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveFacilityId(array $data, User $actor): int
    {
        if (! empty($data['facility_id'])) {
            return Facility::findOrFail($data['facility_id'])->id;
        }

        if (! empty($data['facility'])) {
            return Facility::where('name', $data['facility'])->firstOrFail()->id;
        }

        $assignedIds = $actor->assignedFacilityIds();
        if (count($assignedIds) > 0) {
            return $assignedIds[0];
        }

        return Facility::firstOrFail()->id;
    }
}
