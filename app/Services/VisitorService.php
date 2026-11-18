<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VisitorService
{
    public function __construct(protected AuditService $auditService) {}

    /** @param array<string, mixed> $data */
    public function register(array $data, User $actor): Visitor
    {
        $facilityId = (int) $data['facility_id'];
        abort_unless($actor->hasPermission('visitors.register', $facilityId), 403);
        $facility = Facility::findOrFail($facilityId);
        if ($facility->status !== 'Active') {
            throw ValidationException::withMessages(['facility_id' => 'Visitor registration requires an active facility.']);
        }

        try {
            return DB::transaction(function () use ($data, $actor, $facilityId): Visitor {
                $identity = $this->identityFingerprint($data['id_type'] ?? null, $data['id_number'] ?? null);
                $this->assertIdentityAvailable($identity);
                $visitor = new Visitor(Arr::only($data, [
                    'first_name', 'middle_name', 'last_name', 'gender', 'id_type', 'id_number',
                    'phone', 'email', 'residence', 'notes',
                ]));
                $visitor->visitor_ref = 'VST-'.Str::uuid();
                $visitor->status = 'Under Review';
                $visitor->registered_at = now();
                $visitor->registered_facility_id = $facilityId;
                $visitor->identity_fingerprint = $identity;
                $visitor->save();
                $this->auditService->log(
                    action: 'visitor_registered', outcome: 'Success', resourceType: 'Visitor',
                    resourceReference: $visitor->visitor_ref, changes: ['status' => 'Under Review'],
                    facilityId: $facilityId, actorId: $actor->id
                );

                return $visitor;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['id_number' => 'A visitor with this identity document already exists. Contact an authorized records officer.']);
        }
    }

    /** @param array<string, mixed> $data */
    public function update(Visitor $visitor, array $data, User $actor): Visitor
    {
        Gate::forUser($actor)->authorize('update', $visitor);
        try {
            return DB::transaction(function () use ($visitor, $data, $actor): Visitor {
                $visitor = Visitor::whereKey($visitor->id)->lockForUpdate()->firstOrFail();
                $fields = Arr::only($data, [
                    'first_name', 'middle_name', 'last_name', 'gender', 'id_type', 'id_number',
                    'phone', 'email', 'residence', 'notes', 'status',
                ]);
                if (empty($fields['id_number'])) {
                    unset($fields['id_number']);
                    if (($fields['id_type'] ?? null) !== $visitor->id_type) {
                        throw ValidationException::withMessages(['id_number' => 'Enter the replacement document number when changing its type.']);
                    }
                } else {
                    $identity = $this->identityFingerprint($fields['id_type'] ?? null, $fields['id_number']);
                    $this->assertIdentityAvailable($identity, $visitor->id);
                    $visitor->identity_fingerprint = $identity;
                }
                $visitor->fill($fields);
                $changedFields = array_keys($visitor->getDirty());
                $visitor->save();
                $this->auditService->log(
                    action: 'visitor_corrected', outcome: 'Success', resourceType: 'Visitor',
                    resourceReference: $visitor->visitor_ref,
                    changes: ['changed_fields' => $changedFields, 'reason_recorded' => filled($data['correction_reason'] ?? null)],
                    facilityId: $visitor->registered_facility_id, actorId: $actor->id
                );

                return $visitor;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['id_number' => 'A visitor with this identity document already exists. Contact an authorized records officer.']);
        }
    }

    public function redact(Visitor $visitor, User $actor): void
    {
        Gate::forUser($actor)->authorize('update', $visitor);
        DB::transaction(function () use ($visitor, $actor): void {
            $visitor = Visitor::whereKey($visitor->id)->lockForUpdate()->firstOrFail();
            if ($visitor->visits()->where('state', 'Checked In')->exists()) {
                throw ValidationException::withMessages(['visitor' => 'Check out the visitor before redacting identity information.']);
            }
            $visitor->forceFill([
                'id_number' => null, 'id_type' => null, 'identity_fingerprint' => null,
                'phone' => null, 'email' => null, 'residence' => null, 'notes' => null,
                'status' => 'Under Review',
            ])->save();
            $this->auditService->log(
                action: 'visitor_identifiers_redacted', outcome: 'Success', resourceType: 'Visitor',
                resourceReference: $visitor->visitor_ref, facilityId: $visitor->registered_facility_id, actorId: $actor->id
            );
        });
    }

    public function identityFingerprint(?string $type, ?string $number): ?string
    {
        if (! filled($number)) {
            return null;
        }
        if (! filled($type) || $type === 'None / Not Presented') {
            throw ValidationException::withMessages(['id_type' => 'Choose the identity document type.']);
        }

        return hash_hmac('sha256', Str::lower(trim($type)).'|'.Str::upper(preg_replace('/\s+/', '', $number)), config('app.key'));
    }

    private function assertIdentityAvailable(?string $fingerprint, ?int $exceptId = null): void
    {
        if ($fingerprint !== null && Visitor::where('identity_fingerprint', $fingerprint)->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))->exists()) {
            throw ValidationException::withMessages(['id_number' => 'A visitor with this identity document already exists. Contact an authorized records officer.']);
        }
    }

    /** @return array<string, mixed> */
    public function toViewArray(Visitor $visitor, User $actor, bool $includeContact = false): array
    {
        $visitor->loadMissing(['visits' => fn (HasMany $query): HasMany => $query->visibleTo($actor)->with('facility')]);
        $visits = $visitor->visits;
        $name = trim(implode(' ', array_filter([$visitor->first_name, $visitor->middle_name, $visitor->last_name])));

        return [
            'id' => $visitor->id, 'visitor_ref' => $visitor->visitor_ref,
            'first_name' => $visitor->first_name, 'middle_name' => $visitor->middle_name, 'last_name' => $visitor->last_name,
            'full_name' => $name, 'initials' => mb_substr($visitor->first_name, 0, 1).mb_substr($visitor->last_name, 0, 1),
            'gender' => $visitor->gender ?? 'Unspecified', 'status' => $visitor->status,
            'id_type' => $visitor->id_type ?? 'Not presented', 'id_number_masked' => $visitor->maskedIdentityNumber(),
            'phone' => $includeContact ? $visitor->phone : ($visitor->phone ? '••••'.mb_substr($visitor->phone, -3) : 'Not recorded'),
            'email' => $includeContact ? $visitor->email : 'Restricted',
            'residence' => $includeContact ? $visitor->residence : 'Restricted',
            'notes' => $includeContact ? $visitor->notes : null,
            'primary_relationship' => $visits->first()?->relationship ?? 'Not recorded',
            'registered_date' => $visitor->registered_at->toDateString(),
            'registered_facility_id' => $visitor->registered_facility_id,
            'visit_count' => $visits->count(), 'last_visit' => $visits->max('scheduled_start_at')?->toDateString(),
            'facilities_visited' => $visits->pluck('facility.name')->unique()->values()->all(),
        ];
    }
}
