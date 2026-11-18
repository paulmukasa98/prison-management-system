<?php

namespace Database\Seeders;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\AuditEvent;
use App\Models\CustodyLocation;
use App\Models\Discharge;
use App\Models\Document;
use App\Models\DocumentLink;
use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\Inmate;
use App\Models\LegalReference;
use App\Models\Permission;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\TransferEvent;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\VisitEvent;
use App\Models\Visitor;
use App\Models\VisitorRelationship;
use App\Support\B1DatabaseGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class B1DemoSeeder extends Seeder
{
    public function run(): void
    {
        app(B1DatabaseGuard::class)->assertAllowed(DB::getDefaultConnection());

        DB::transaction(function (): void {
            $north = $this->record(Facility::class, ['code' => 'DEMO-B1-FAC-001'], ['name' => 'Fictional North Development Facility', 'status' => 'Active']);
            $south = $this->record(Facility::class, ['code' => 'DEMO-B1-FAC-002'], ['name' => 'Fictional South Development Facility', 'status' => 'Active']);
            $this->record(FacilityCapacity::class, ['facility_id' => $north->id, 'effective_on' => '2026-01-01'], ['capacity' => 100, 'source_reference' => 'DEMO-B1-CAP-001']);
            $this->record(FacilityCapacity::class, ['facility_id' => $south->id, 'effective_on' => '2026-01-01'], ['capacity' => null, 'source_reference' => 'DEMO-B1-CAP-002']);

            $officer = $this->record(Personnel::class, ['staff_number' => 'DEMO-B1-STAFF-001'], ['first_name' => 'Fictional', 'last_name' => 'Posted Officer', 'employment_status' => 'Active', 'appointed_on' => '2025-01-01']);
            $this->record(Personnel::class, ['staff_number' => 'DEMO-B1-STAFF-002'], ['first_name' => 'Fictional', 'last_name' => 'Unposted Officer', 'employment_status' => 'Inactive']);
            $account = $this->record(User::class, ['account_ref' => 'DEMO-B1-USER-001'], ['name' => 'Fictional Disabled Officer', 'email' => 'officer-b1@example.invalid', 'personnel_id' => $officer->id, 'status' => 'Disabled', 'email_verified_at' => null]);
            $this->record(User::class, ['account_ref' => 'DEMO-B1-USER-002'], ['name' => 'Fictional Disabled External Account', 'email' => 'external-b1@example.invalid', 'personnel_id' => null, 'status' => 'Disabled', 'email_verified_at' => null]);
            $this->record(PersonnelAssignment::class, ['assignment_ref' => 'DEMO-B1-POST-001'], ['personnel_id' => $officer->id, 'facility_id' => $north->id, 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'status' => 'Completed', 'duty_title' => 'Fictional Records Duty', 'recorded_by' => $account->id]);
            $this->record(PersonnelAssignment::class, ['assignment_ref' => 'DEMO-B1-POST-002'], ['personnel_id' => $officer->id, 'facility_id' => $south->id, 'start_date' => '2026-01-01', 'end_date' => null, 'status' => 'Active', 'duty_title' => 'Fictional Records Duty', 'recorded_by' => $account->id]);

            $role = $this->record(Role::class, ['slug' => 'demo_b1_reader'], ['name' => 'Fictional Proposed Reader', 'description' => 'Development catalog only; no authority is granted.']);
            $permission = $this->record(Permission::class, ['slug' => 'demo_b1.records.read'], ['name' => 'Fictional record-read proposal', 'module' => 'demo']);
            DB::table('permission_role')->insertOrIgnore(['role_id' => $role->id, 'permission_id' => $permission->id]);
            $scope = $this->record(AccessScope::class, ['scope_key' => 'DEMO-B1-SCOPE-NORTH'], ['kind' => 'Facility', 'facility_id' => $north->id]);
            $this->record(AccessScope::class, ['scope_key' => 'DEMO-B1-SCOPE-NATIONAL'], ['kind' => 'National', 'facility_id' => null]);
            $this->record(UserRoleAssignment::class, ['assignment_ref' => 'DEMO-B1-GRANT-001'], ['user_id' => $account->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id, 'granted_at' => '2026-01-01 08:00:00', 'ended_at' => '2026-01-02 08:00:00']);

            $returning = $this->record(Inmate::class, ['identity_ref' => 'DEMO-B1-INM-001'], ['prisoner_number' => 'DEMO-B1-PN-001', 'first_name' => 'Fictional', 'last_name' => 'Returning Person']);
            $second = $this->record(Inmate::class, ['identity_ref' => 'DEMO-B1-INM-002'], ['prisoner_number' => 'DEMO-B1-PN-002', 'first_name' => 'Fictional', 'last_name' => 'Second Person']);
            $third = $this->record(Inmate::class, ['identity_ref' => 'DEMO-B1-INM-003'], ['prisoner_number' => null, 'first_name' => 'Fictional', 'last_name' => 'Unnumbered Person']);
            $past = $this->record(AdmissionEpisode::class, ['admission_ref' => 'DEMO-B1-ADM-001'], ['inmate_id' => $returning->id, 'admitting_facility_id' => $north->id, 'admitted_at' => '2025-01-01 08:00:00', 'closed_at' => '2025-06-01 10:00:00', 'state' => 'Closed', 'legal_basis' => 'Fictional development order, not legal authority']);
            $current = $this->record(AdmissionEpisode::class, ['admission_ref' => 'DEMO-B1-ADM-002'], ['inmate_id' => $returning->id, 'admitting_facility_id' => $north->id, 'admitted_at' => '2026-01-01 08:00:00', 'closed_at' => null, 'state' => 'Open', 'legal_basis' => 'Fictional development order, not legal authority']);
            $secondAdmission = $this->record(AdmissionEpisode::class, ['admission_ref' => 'DEMO-B1-ADM-003'], ['inmate_id' => $second->id, 'admitting_facility_id' => $north->id, 'admitted_at' => '2026-01-01 08:00:00', 'closed_at' => null, 'state' => 'Open']);
            $thirdAdmission = $this->record(AdmissionEpisode::class, ['admission_ref' => 'DEMO-B1-ADM-004'], ['inmate_id' => $third->id, 'admitting_facility_id' => $south->id, 'admitted_at' => '2026-01-01 08:00:00', 'closed_at' => null, 'state' => 'Open']);
            $legal = $this->record(LegalReference::class, ['reference' => 'DEMO-B1-LEGAL-001'], ['admission_episode_id' => $past->id, 'type' => 'Fictional Order', 'external_reference' => 'DEMO-B1-EXTERNAL-001', 'issuing_authority' => 'Fictional Development Authority', 'issued_on' => '2025-01-01']);
            $this->record(Discharge::class, ['discharge_ref' => 'DEMO-B1-DIS-001'], ['admission_episode_id' => $past->id, 'legal_reference_id' => $legal->id, 'state' => 'Completed', 'reason' => 'Fictional development closure', 'authority_reference' => 'DEMO-B1-AUTH-001', 'requested_at' => '2025-06-01 08:00:00', 'authorized_at' => '2025-06-01 09:00:00', 'executed_at' => '2025-06-01 10:00:00', 'executed_by' => $account->id]);

            $transfer = $this->record(Transfer::class, ['transfer_ref' => 'DEMO-B1-TRF-001'], ['admission_episode_id' => $current->id, 'origin_facility_id' => $north->id, 'destination_facility_id' => $south->id, 'requesting_facility_id' => $north->id, 'requested_at' => '2026-01-02 08:00:00', 'dispatched_at' => '2026-01-03 08:00:00', 'received_at' => '2026-01-03 10:00:00', 'state' => 'Completed', 'reason' => 'Fictional development movement', 'requested_by' => $account->id]);
            foreach (['Requested' => '2026-01-02 08:00:00', 'Dispatched' => '2026-01-03 08:00:00', 'Received' => '2026-01-03 10:00:00'] as $type => $at) {
                $sequence = match ($type) {
                    'Requested' => 1, 'Dispatched' => 2, 'Received' => 3
                };
                $this->record(TransferEvent::class, ['event_ref' => 'DEMO-B1-TE-00'.$sequence], ['transfer_id' => $transfer->id, 'sequence' => $sequence, 'event_type' => 'Fictional '.$type, 'occurred_at' => $at, 'recorded_at' => $at, 'actor_id' => $account->id]);
            }
            $this->record(CustodyLocation::class, ['location_ref' => 'DEMO-B1-LOC-001'], ['admission_episode_id' => $past->id, 'facility_id' => $north->id, 'entered_at' => '2025-01-01 08:00:00', 'left_at' => '2025-06-01 10:00:00']);
            $this->record(CustodyLocation::class, ['location_ref' => 'DEMO-B1-LOC-002'], ['admission_episode_id' => $current->id, 'facility_id' => $north->id, 'entered_at' => '2026-01-01 08:00:00', 'left_at' => '2026-01-03 08:00:00', 'left_transfer_id' => $transfer->id]);
            $this->record(CustodyLocation::class, ['location_ref' => 'DEMO-B1-LOC-003'], ['admission_episode_id' => $current->id, 'facility_id' => $south->id, 'entered_at' => '2026-01-03 10:00:00', 'left_at' => null, 'entered_transfer_id' => $transfer->id]);
            $this->record(CustodyLocation::class, ['location_ref' => 'DEMO-B1-LOC-004'], ['admission_episode_id' => $secondAdmission->id, 'facility_id' => $north->id, 'entered_at' => '2026-01-01 08:00:00', 'left_at' => null]);
            $this->record(CustodyLocation::class, ['location_ref' => 'DEMO-B1-LOC-005'], ['admission_episode_id' => $thirdAdmission->id, 'facility_id' => $south->id, 'entered_at' => '2026-01-01 08:00:00', 'left_at' => null]);

            $visitor = $this->record(Visitor::class, ['visitor_ref' => 'DEMO-B1-VST-001'], ['first_name' => 'Fictional', 'last_name' => 'First Visitor', 'status' => 'Under Review', 'registered_at' => '2025-12-01 08:00:00']);
            $otherVisitor = $this->record(Visitor::class, ['visitor_ref' => 'DEMO-B1-VST-002'], ['first_name' => 'Fictional', 'last_name' => 'Second Visitor', 'status' => 'Under Review', 'registered_at' => '2025-12-01 08:00:00']);
            $this->record(VisitorRelationship::class, ['visitor_id' => $visitor->id, 'inmate_id' => $returning->id], ['relationship' => 'Fictional family relationship']);
            $this->record(VisitorRelationship::class, ['visitor_id' => $otherVisitor->id, 'inmate_id' => $second->id], ['relationship' => 'Fictional legal representative']);
            foreach ([[$visitor, $current, $south, 'Completed'], [$otherVisitor, $secondAdmission, $north, 'Scheduled'], [$visitor, $current, $south, 'Cancelled']] as $index => [$person, $admission, $facility, $state]) {
                $number = $index + 1;
                $visit = $this->record(Visit::class, ['visit_ref' => 'DEMO-B1-VIS-00'.$number], ['visitor_id' => $person->id, 'admission_episode_id' => $admission->id, 'facility_id' => $facility->id, 'state' => $state, 'purpose' => 'Fictional development visit', 'requested_at' => '2026-01-04 08:00:00', 'scheduled_start_at' => '2026-01-05 09:00:00', 'scheduled_end_at' => '2026-01-05 09:30:00', 'checked_in_at' => $state === 'Completed' ? '2026-01-05 09:00:00' : null, 'checked_out_at' => $state === 'Completed' ? '2026-01-05 09:30:00' : null]);
                $this->record(VisitEvent::class, ['event_ref' => 'DEMO-B1-VE-00'.$number], ['visit_id' => $visit->id, 'sequence' => 1, 'event_type' => 'Fictional '.$state, 'to_state' => $state, 'occurred_at' => '2026-01-05 09:30:00', 'recorded_at' => '2026-01-05 09:30:00', 'actor_id' => $account->id]);
            }
            $document = $this->record(Document::class, ['document_ref' => 'DEMO-B1-DOC-001'], ['state' => 'Metadata Only', 'storage_key' => null, 'original_name' => null]);
            $this->record(DocumentLink::class, ['document_id' => $document->id, 'legal_reference_id' => $legal->id], ['purpose' => 'Fictional legal metadata, no document exists']);
            $this->record(AuditEvent::class, ['audit_ref' => 'DEMO-B1-AUD-001'], ['actor_id' => null, 'facility_id' => null, 'occurred_at' => '2026-01-05 10:00:00', 'recorded_at' => '2026-01-05 10:00:00', 'action' => 'fictional.seed', 'outcome' => 'Illustration', 'resource_type' => 'DevelopmentDataset', 'resource_reference' => 'DEMO-B1', 'correlation_id' => 'DEMO-B1-COR-001', 'description' => 'Fictional dataset illustration, not an operational audit.', 'changes' => ['dataset' => 'DEMO-B1', 'contains_real_data' => false]]);
        });
    }

    /** @param class-string<Model> $model @param array<string, mixed> $identity @param array<string, mixed> $attributes */
    private function record(string $model, array $identity, array $attributes): Model
    {
        $record = $model::query()->firstOrNew($identity);
        $expected = (new $model)->forceFill($identity + $attributes)->getAttributes();

        if ($record->exists) {
            foreach ($expected as $column => $value) {
                $actual = $record->getRawOriginal($column);
                if (($actual === null) !== ($value === null) || (string) $actual !== (string) $value) {
                    if (is_numeric($actual) && is_numeric($value) && (int) $actual === (int) $value) {
                        continue;
                    }
                    if (str_starts_with((string) $value, (string) $actual.' 00:00:00') || str_starts_with((string) $actual, (string) $value.' 00:00:00')) {
                        continue;
                    }
                    if (json_validate((string) $actual) && json_validate((string) $value)) {
                        if (json_decode((string) $actual, true) === json_decode((string) $value, true)) {
                            continue;
                        }
                    }
                    throw new RuntimeException("Conflicting B1 development record in {$record->getTable()} on column {$column} (actual: '{$actual}', expected: '{$value}'). No existing records were overwritten.");
                }
            }

            return $record;
        }

        $record->forceFill($identity + $attributes);
        if ($record instanceof User) {
            $record->password = Str::random(64);
        }
        $record->save();

        return $record;
    }
}
