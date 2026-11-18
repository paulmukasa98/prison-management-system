<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PersonnelService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Register a new staff member and optionally assign them to an initial posting.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): Personnel
    {
        if (! $actor->hasPermission('personnel.manage')) {
            abort(403, 'Unauthorized. You do not hold personnel management privileges.');
        }

        return DB::transaction(function () use ($data, $actor) {
            if (app()->isProduction() && empty($data['staff_number'])) {
                throw ValidationException::withMessages(['staff_number' => 'Supply a staff number issued under the approved organizational policy.']);
            }
            if (! empty($data['facility_id']) && ! $actor->hasPermission('personnel.manage', (int) $data['facility_id'])) {
                abort(403);
            }
            if (empty($data['facility_id']) && $actor->facilityIdsForPermission('personnel.manage') !== null) {
                throw ValidationException::withMessages(['facility_id' => 'Select an authorized initial posting for this personnel record.']);
            }
            $staffNumber = $data['staff_number']
                ?? ('UPS-STF-'.date('Y').'-'.str_pad((string) ((Personnel::withTrashed()->max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT));
            if (! empty($data['facility_id'])) {
                Facility::findOrFail($data['facility_id']);
            }

            $personnel = Personnel::create([
                'staff_number' => $staffNumber,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'nationality' => $data['nationality'] ?? 'Ugandan',
                'rank' => $data['rank'],
                'department' => $data['department'],
                'employment_status' => $data['employment_status'] ?? 'Active',
                'appointed_on' => $data['appointed_on'] ?? now()->toDateString(),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'kin_name' => $data['kin_name'] ?? null,
                'kin_contact' => $data['kin_contact'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Optional initial posting
            if (! empty($data['facility_id'])) {
                $assignmentRef = 'ASN-'.date('Y').'-'.strtoupper(Str::random(6));

                PersonnelAssignment::create([
                    'assignment_ref' => $assignmentRef,
                    'personnel_id' => $personnel->id,
                    'facility_id' => (int) $data['facility_id'],
                    'duty_title' => $data['duty_title'] ?? $personnel->rank,
                    'reason' => 'Initial Service Posting',
                    'start_date' => $personnel->appointed_on ?? now()->toDateString(),
                    'end_date' => null,
                    'status' => 'Active',
                    'authority_reference' => 'APP-ORD-'.date('Y'),
                    'recorded_by' => $actor->id,
                ]);
            }

            $this->auditService->log(
                action: 'personnel_created',
                outcome: 'Success',
                resourceType: 'Personnel',
                resourceReference: $personnel->staff_number,
                changes: [
                    'personnel_id' => $personnel->id,
                    'name' => "{$personnel->first_name} {$personnel->last_name}",
                    'rank' => $personnel->rank,
                    'department' => $personnel->department,
                    'employment_status' => $personnel->employment_status,
                ],
                description: "Registered personnel record {$personnel->staff_number} ({$personnel->first_name} {$personnel->last_name}).",
                facilityId: $personnel->currentFacilityId(),
                actorId: $actor->id
            );

            return $personnel;
        });
    }

    /**
     * Update personnel service details.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Personnel $personnel, array $data, User $actor): Personnel
    {
        $facilityId = $personnel->currentFacilityId();

        if (! $actor->hasPermission('personnel.manage', $facilityId)) {
            abort(403, 'Unauthorized. You do not hold management authority for this personnel record.');
        }

        $oldValues = $personnel->only([
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
            'nationality', 'rank', 'department', 'employment_status', 'appointed_on',
            'email', 'phone', 'kin_name', 'kin_contact', 'notes',
        ]);

        $personnel->update(array_filter([
            'first_name' => $data['first_name'] ?? $personnel->first_name,
            'middle_name' => $data['middle_name'] ?? $personnel->middle_name,
            'last_name' => $data['last_name'] ?? $personnel->last_name,
            'gender' => $data['gender'] ?? $personnel->gender,
            'date_of_birth' => $data['date_of_birth'] ?? $personnel->date_of_birth,
            'nationality' => $data['nationality'] ?? $personnel->nationality,
            'rank' => $data['rank'] ?? $personnel->rank,
            'department' => $data['department'] ?? $personnel->department,
            'employment_status' => $data['employment_status'] ?? $personnel->employment_status,
            'appointed_on' => $data['appointed_on'] ?? $personnel->appointed_on,
            'email' => $data['email'] ?? $personnel->email,
            'phone' => $data['phone'] ?? $personnel->phone,
            'kin_name' => $data['kin_name'] ?? $personnel->kin_name,
            'kin_contact' => $data['kin_contact'] ?? $personnel->kin_contact,
            'notes' => $data['notes'] ?? $personnel->notes,
        ], fn ($v) => $v !== null));

        $newValues = $personnel->only(array_keys($oldValues));

        $this->auditService->log(
            action: 'personnel_updated',
            outcome: 'Success',
            resourceType: 'Personnel',
            resourceReference: $personnel->staff_number,
            changes: [
                'before' => $oldValues,
                'after' => $newValues,
            ],
            description: "Updated personnel record {$personnel->staff_number}.",
            facilityId: $facilityId,
            actorId: $actor->id
        );

        return $personnel;
    }

    /**
     * Post a staff member to a facility assignment, automatically concluding any overlapping active posting.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function assign(Personnel $personnel, array $data, User $actor): PersonnelAssignment
    {
        $targetFacilityId = Facility::findOrFail($data['facility_id'])->id;

        if (! $actor->hasPermission('personnel.manage', $targetFacilityId)) {
            abort(403, 'Unauthorized. You do not hold assignment authority for the target facility.');
        }

        return DB::transaction(function () use ($personnel, $data, $targetFacilityId, $actor) {
            $personnel = Personnel::whereKey($personnel->id)->lockForUpdate()->firstOrFail();
            $startDate = Carbon::parse($data['start_date']);
            if ($personnel->assignments()->where('status', 'Active')->where('start_date', '>', now()->toDateString())
                ->where(fn ($query) => $query->whereNull('end_date')->orWhere('end_date', '>=', $startDate->toDateString()))
                ->when(! empty($data['end_date']), fn ($query) => $query->where('start_date', '<=', $data['end_date']))->exists()) {
                throw ValidationException::withMessages(['start_date' => 'The posting conflicts with an existing future assignment.']);
            }

            // Conclude any prior open active posting to prevent contradictory overlapping active assignments
            $priorActive = $personnel->assignments()
                ->where('status', 'Active')
                ->whereNull('end_date')
                ->get();

            foreach ($priorActive as $activeAssignment) {
                $endDate = $startDate->copy()->subDay();
                if ($endDate->lt($activeAssignment->start_date)) {
                    throw ValidationException::withMessages(['start_date' => 'The new posting must start after the existing posting began.']);
                }
                $activeAssignment->update([
                    'end_date' => $endDate,
                    'status' => $endDate->gte(today()) ? 'Active' : 'Completed',
                ]);
            }

            $assignmentRef = 'ASN-'.date('Y').'-'.strtoupper(Str::random(6));

            $assignment = PersonnelAssignment::create([
                'assignment_ref' => $assignmentRef,
                'personnel_id' => $personnel->id,
                'facility_id' => $targetFacilityId,
                'duty_title' => $data['duty_title'],
                'reason' => $data['reason'] ?? 'Official Service Reassignment',
                'start_date' => $startDate->toDateString(),
                'end_date' => ! empty($data['end_date']) ? $data['end_date'] : null,
                'status' => 'Active',
                'authority_reference' => $data['authority_reference'] ?? null,
                'recorded_by' => $actor->id,
            ]);

            $this->auditService->log(
                action: 'personnel_assigned',
                outcome: 'Success',
                resourceType: 'Personnel',
                resourceReference: $personnel->staff_number,
                changes: [
                    'assignment_ref' => $assignment->assignment_ref,
                    'facility_id' => $targetFacilityId,
                    'duty_title' => $assignment->duty_title,
                    'start_date' => $assignment->start_date->toDateString(),
                ],
                description: "Assigned {$personnel->staff_number} to facility as {$assignment->duty_title}.",
                facilityId: $targetFacilityId,
                actorId: $actor->id
            );

            return $assignment;
        });
    }

    /**
     * Conclude an active posting.
     *
     * @param  array<string, mixed>  $data
     */
    public function endAssignment(PersonnelAssignment $assignment, array $data, User $actor): PersonnelAssignment
    {
        if (! $actor->hasPermission('personnel.manage', $assignment->facility_id)) {
            abort(403, 'Unauthorized. You do not hold assignment authority for this facility.');
        }

        $endDate = ! empty($data['end_date']) ? $data['end_date'] : now()->toDateString();
        if ($assignment->start_date && Carbon::parse($endDate)->lt($assignment->start_date)) {
            $endDate = $assignment->start_date->toDateString();
        }

        $assignment->update([
            'end_date' => $endDate,
            'status' => 'Completed',
        ]);

        $this->auditService->log(
            action: 'personnel_assignment_ended',
            outcome: 'Success',
            resourceType: 'Personnel',
            resourceReference: $assignment->personnel->staff_number,
            changes: [
                'assignment_ref' => $assignment->assignment_ref,
                'end_date' => $endDate,
                'status' => 'Completed',
            ],
            description: "Concluded posting {$assignment->assignment_ref} for {$assignment->personnel->staff_number}.",
            facilityId: $assignment->facility_id,
            actorId: $actor->id
        );

        return $assignment;
    }

    /**
     * Map Personnel to array format for views.
     *
     * @return array<string, mixed>
     */
    public function toViewArray(Personnel $person): array
    {
        $currentPosting = $person->currentAssignment();
        $facility = $currentPosting?->facility;
        $dob = $person->date_of_birth;

        $fullName = trim($person->first_name.' '.($person->middle_name ? $person->middle_name.' ' : '').$person->last_name);
        $initials = strtoupper(substr($person->first_name, 0, 1).substr($person->last_name, 0, 1));

        return [
            'id' => $person->id,
            'staff_number' => $person->staff_number,
            'first_name' => $person->first_name,
            'middle_name' => $person->middle_name,
            'last_name' => $person->last_name,
            'full_name' => $fullName,
            'initials' => $initials,
            'gender' => $person->gender ?? 'Unspecified',
            'date_of_birth' => $dob?->format('Y-m-d'),
            'age' => $dob?->age ?? '—',
            'nationality' => $person->nationality ?? 'Ugandan',
            'rank' => $person->rank ?? 'Warder',
            'department' => $person->department ?? 'Operations',
            'email' => $person->email ?? '—',
            'phone' => $person->phone ?? '—',
            'facility' => $facility?->name,
            'facility_id' => $facility?->id,
            'duty_title' => $currentPosting?->duty_title ?? $person->rank,
            'employment_status' => $person->employment_status ?? 'Active',
            'appointment_date' => $person->appointed_on?->format('Y-m-d') ?? date('Y-m-d'),
            'kin_name' => $person->kin_name,
            'kin_contact' => $person->kin_contact,
            'notes' => $person->notes,
            'created_date' => $person->created_at?->format('Y-m-d') ?? date('Y-m-d'),
            'updated_date' => $person->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
        ];
    }

    /**
     * Map PersonnelAssignment to array format for views.
     *
     * @return array<string, mixed>
     */
    public function toAssignmentViewArray(PersonnelAssignment $a): array
    {
        $person = $a->personnel;
        $facility = $a->facility;

        return [
            'id' => $a->id,
            'assignment_ref' => $a->assignment_ref,
            'personnel_id' => $person?->id,
            'staff_number' => $person?->staff_number ?? '—',
            'name' => $person ? trim("{$person->first_name} {$person->last_name}") : 'Unknown',
            'personnel_name' => $person ? trim("{$person->first_name} {$person->last_name}") : 'Unknown',
            'rank' => $person?->rank ?? '—',
            'facility' => $facility?->name ?? 'Unassigned',
            'facility_id' => $facility?->id,
            'duty_title' => $a->duty_title ?? 'Staff Member',
            'role' => $a->duty_title ?? 'Staff Member',
            'start_date' => $a->start_date?->format('Y-m-d') ?? '—',
            'end_date' => $a->end_date?->format('Y-m-d'),
            'status' => $a->status,
            'reason' => $a->reason ?? 'Official Posting',
            'authority_reference' => $a->authority_reference ?? '—',
            'auth_ref' => $a->authority_reference ?? '—',
            'notes' => $a->reason ?? null,
            'created_date' => $a->created_at?->format('Y-m-d') ?? date('Y-m-d'),
        ];
    }
}
