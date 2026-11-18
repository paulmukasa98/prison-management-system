<?php

namespace Tests\Feature;

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
use Database\Seeders\B1DemoSeeder;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\B1DatabaseTestCase;

class B1DatabaseTest extends B1DatabaseTestCase
{
    public static function domainModels(): array
    {
        return array_combine(
            $models = [Facility::class, FacilityCapacity::class, Personnel::class, PersonnelAssignment::class, User::class, Role::class, Permission::class, AccessScope::class, UserRoleAssignment::class, Inmate::class, AdmissionEpisode::class, LegalReference::class, Discharge::class, Transfer::class, TransferEvent::class, CustodyLocation::class, Visitor::class, VisitorRelationship::class, Visit::class, VisitEvent::class, Document::class, DocumentLink::class, AuditEvent::class],
            array_map(fn (string $model): array => [$model], $models),
        );
    }

    #[DataProvider('domainModels')]
    public function test_factory_creates_a_valid_domain_record(string $model): void
    {
        $record = $model::factory()->create();

        $this->assertModelExists($record);
    }

    public static function uniqueReferences(): array
    {
        return [
            'facility code' => [Facility::class, 'code'],
            'staff number' => [Personnel::class, 'staff_number'],
            'posting reference' => [PersonnelAssignment::class, 'assignment_ref'],
            'account reference' => [User::class, 'account_ref'],
            'inmate identity' => [Inmate::class, 'identity_ref'],
            'prisoner number' => [Inmate::class, 'prisoner_number'],
            'admission reference' => [AdmissionEpisode::class, 'admission_ref'],
            'transfer reference' => [Transfer::class, 'transfer_ref'],
            'visitor reference' => [Visitor::class, 'visitor_ref'],
            'visit reference' => [Visit::class, 'visit_ref'],
            'document reference' => [Document::class, 'document_ref'],
            'audit reference' => [AuditEvent::class, 'audit_ref'],
        ];
    }

    #[DataProvider('uniqueReferences')]
    public function test_rejects_duplicate_business_reference(string $model, string $field): void
    {
        $record = $model::factory()->create($field === 'prisoner_number' ? ['prisoner_number' => 'DEMO-UNIQUE-PRISONER'] : []);
        $this->expectException(QueryException::class);

        $model::factory()->create([$field => $record->{$field}]);
    }

    public function test_repeated_admissions_preserve_internal_identity_and_closed_history(): void
    {
        $inmate = Inmate::factory()->create();
        $past = AdmissionEpisode::factory()->closed()->for($inmate)->create();
        $current = AdmissionEpisode::factory()->for($inmate)->create(['admitted_at' => '2026-03-01 08:00:00']);

        $this->assertCount(2, $inmate->admissions);
        $this->assertSame($inmate->id, $past->inmate->id);
        $this->assertSame($inmate->id, $current->inmate->id);
        $this->assertSame('2026-02-01', $past->closed_at->toDateString());
        $this->assertNull($current->closed_at);
        $this->assertNotSame($past->admission_ref, $current->admission_ref);
    }

    public function test_rejects_two_open_admissions_even_with_different_states(): void
    {
        $inmate = Inmate::factory()->create();
        AdmissionEpisode::factory()->for($inmate)->create(['state' => 'Open']);
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('open_inmate_id');

        AdmissionEpisode::factory()->for($inmate)->create(['state' => 'Draft']);
    }

    public function test_rejects_two_open_locations_for_the_same_admission(): void
    {
        $admission = AdmissionEpisode::factory()->create();
        CustodyLocation::factory()->for($admission, 'admission')->create();
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('open_admission_id');

        CustodyLocation::factory()->for($admission, 'admission')->create();
    }

    public function test_rejects_a_discharge_legal_reference_from_another_admission(): void
    {
        $legal = LegalReference::factory()->create();
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('b1_discharge_legal_episode');

        Discharge::factory()->create(['legal_reference_id' => $legal->id]);
    }

    public function test_rejects_a_location_transfer_from_another_admission(): void
    {
        $transfer = Transfer::factory()->create();
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('b1_location_enter_transfer');

        CustodyLocation::factory()->create(['entered_transfer_id' => $transfer->id]);
    }

    public function test_rejects_multiple_accounts_for_one_personnel_record(): void
    {
        $personnel = Personnel::factory()->create();
        User::factory()->for($personnel, 'personnel')->create();
        $this->expectException(QueryException::class);

        User::factory()->for($personnel, 'personnel')->create();
    }

    public static function invalidChronology(): array
    {
        return [
            'admission closes early' => [AdmissionEpisode::class, ['closed_at' => '2025-12-31 08:00:00'], 'b1_admission_dates'],
            'posting ends early' => [PersonnelAssignment::class, ['end_date' => '2025-12-31'], 'b1_posting_dates'],
            'location ends early' => [CustodyLocation::class, ['left_at' => '2025-12-31 08:00:00'], 'b1_location_dates'],
            'transfer received before dispatch' => [Transfer::class, ['dispatched_at' => '2026-01-03 10:00:00', 'received_at' => '2026-01-03 09:00:00'], 'b1_transfer_dates'],
            'transfer received without dispatch' => [Transfer::class, ['received_at' => '2026-01-03 09:00:00'], 'b1_transfer_dates'],
            'zero scheduled duration' => [Visit::class, ['scheduled_end_at' => '2026-01-03 09:00:00'], 'b1_visit_schedule'],
            'unpaired schedule' => [Visit::class, ['scheduled_end_at' => null], 'b1_visit_schedule'],
            'checkout without checkin' => [Visit::class, ['checked_out_at' => '2026-01-03 09:30:00'], 'b1_visit_checkout'],
            'checkout before checkin' => [Visit::class, ['checked_in_at' => '2026-01-03 09:30:00', 'checked_out_at' => '2026-01-03 09:00:00'], 'b1_visit_checkout'],
            'discharge execution without authorization' => [Discharge::class, ['executed_at' => '2026-01-03 09:00:00'], 'b1_discharge_dates'],
            'role assignment ends early' => [UserRoleAssignment::class, ['ended_at' => '2025-12-31 08:00:00'], 'b1_grant_dates'],
            'zero transfer sequence' => [TransferEvent::class, ['sequence' => 0], 'b1_transfer_sequence'],
            'zero visit sequence' => [VisitEvent::class, ['sequence' => 0], 'b1_visit_sequence'],
        ];
    }

    #[DataProvider('invalidChronology')]
    public function test_database_rejects_invalid_intervals(string $model, array $attributes, string $constraint): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage($constraint);

        $model::factory()->create($attributes);
    }

    public function test_rejects_transfer_to_its_origin_facility(): void
    {
        $facility = Facility::factory()->create();
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('b1_transfer_facilities');

        Transfer::factory()->create(['origin_facility_id' => $facility->id, 'destination_facility_id' => $facility->id]);
    }

    public function test_rejects_facility_scope_without_facility(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('b1_scope_kind');

        AccessScope::factory()->create(['kind' => 'Facility']);
    }

    public static function invalidDocumentTargets(): array
    {
        return ['no target' => [false], 'two targets' => [true]];
    }

    #[DataProvider('invalidDocumentTargets')]
    public function test_rejects_document_without_exactly_one_typed_target(bool $twoTargets): void
    {
        $document = Document::factory()->create();
        $inmate = Inmate::factory()->create();
        $visitor = Visitor::factory()->create();
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('b1_document_target');

        DocumentLink::factory()->create(['document_id' => $document->id, 'inmate_id' => $twoTargets ? $inmate->id : null, 'visitor_id' => $twoTargets ? $visitor->id : null]);
    }

    public function test_retains_unknown_capacity_as_null_and_zero_as_zero(): void
    {
        $facility = Facility::factory()->create();
        FacilityCapacity::factory()->for($facility)->create(['capacity' => null, 'effective_on' => '2026-01-01']);
        FacilityCapacity::factory()->for($facility)->create(['capacity' => 0, 'effective_on' => '2026-02-01']);

        $history = $facility->capacities()->orderBy('effective_on')->get();
        $this->assertNull($history[0]->capacity);
        $this->assertSame(0, $history[1]->capacity);
        $this->assertSame('2026-02-01', $history[1]->effective_on->toDateString());
    }

    public static function retainedParents(): array
    {
        return [
            'identity with admissions' => [AdmissionEpisode::class, 'inmate'],
            'facility with capacity history' => [FacilityCapacity::class, 'facility'],
            'personnel with postings' => [PersonnelAssignment::class, 'personnel'],
            'admission with transfer' => [Transfer::class, 'admission'],
            'visitor with visits' => [Visit::class, 'visitor'],
            'document with attachment' => [DocumentLink::class, 'document'],
        ];
    }

    #[DataProvider('retainedParents')]
    public function test_restricts_deletion_of_historical_parent(string $childClass, string $relation): void
    {
        $child = $childClass::factory()->create();
        $this->expectException(QueryException::class);

        $parent = $child->{$relation};
        DB::table($parent->getTable())->where('id', $parent->getKey())->delete();
    }

    public static function appendOnlyEvents(): array
    {
        return [
            'transfer update' => [TransferEvent::class, false],
            'transfer delete' => [TransferEvent::class, true],
            'visit update' => [VisitEvent::class, false],
            'visit delete' => [VisitEvent::class, true],
            'audit update' => [AuditEvent::class, false],
            'audit delete' => [AuditEvent::class, true],
        ];
    }

    #[DataProvider('appendOnlyEvents')]
    public function test_model_rejects_mutation_of_recorded_events(string $model, bool $delete): void
    {
        $event = $model::factory()->create();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('append-only');

        if ($delete) {
            $event->delete();
        } else {
            $event->update([$model === AuditEvent::class ? 'description' : 'event_type' => 'Altered']);
        }
    }

    public function test_seed_rerun_preserves_ids_passwords_and_history(): void
    {
        $this->seed(B1DemoSeeder::class);
        $passwords = User::orderBy('id')->pluck('password', 'account_ref')->all();
        $ids = AdmissionEpisode::orderBy('id')->pluck('id', 'admission_ref')->all();

        $this->seed(B1DemoSeeder::class);

        $this->assertSame($passwords, User::orderBy('id')->pluck('password', 'account_ref')->all());
        $this->assertSame($ids, AdmissionEpisode::orderBy('id')->pluck('id', 'admission_ref')->all());
        $this->assertDatabaseCount('facilities', 2);
        $this->assertDatabaseCount('personnel', 2);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('inmates', 3);
        $this->assertDatabaseCount('admission_episodes', 4);
        $this->assertDatabaseCount('custody_locations', 5);
        $this->assertDatabaseCount('transfer_events', 3);
        $this->assertDatabaseCount('visits', 3);
        $this->assertDatabaseCount('audit_events', 1);
        foreach (User::all() as $user) {
            $this->assertSame('Disabled', $user->status);
            $this->assertNull($user->email_verified_at);
            $this->assertFalse(Hash::check('password', $user->password));
            $this->assertStringEndsWith('@example.invalid', $user->email);
        }
    }

    public function test_seed_conflict_rolls_back_new_rows_without_overwriting(): void
    {
        Facility::factory()->create(['code' => 'DEMO-B1-FAC-002', 'name' => 'Existing Nonseed Facility']);
        try {
            $this->seed(B1DemoSeeder::class);
            $this->fail('A conflicting seed reference was accepted.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Conflicting B1', $error->getMessage());
        }

        $this->assertDatabaseCount('facilities', 1);
        $this->assertDatabaseHas('facilities', ['code' => 'DEMO-B1-FAC-002', 'name' => 'Existing Nonseed Facility']);
        $this->assertDatabaseMissing('facilities', ['code' => 'DEMO-B1-FAC-001']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_seed_linkage_reconstructs_prior_admission_and_transfer_locations(): void
    {
        $this->seed(B1DemoSeeder::class);

        $inmate = Inmate::where('identity_ref', 'DEMO-B1-INM-001')->firstOrFail();
        $admission = $inmate->admissions()->whereNull('closed_at')->firstOrFail();
        $locations = $admission->locations()->orderBy('entered_at')->get();
        $transfer = $admission->transfers()->firstOrFail();
        $this->assertCount(2, $inmate->admissions);
        $this->assertSame('DEMO-B1-FAC-001', $locations[0]->facility->code);
        $this->assertSame('DEMO-B1-FAC-002', $locations[1]->facility->code);
        $this->assertSame($transfer->id, $locations[0]->leavingTransfer->id);
        $this->assertSame($transfer->id, $locations[1]->enteringTransfer->id);
        $this->assertSame([1, 2, 3], $transfer->events->pluck('sequence')->all());
        $this->assertSame('DEMO-B1-LEGAL-001', Discharge::firstOrFail()->legalReference->reference);
        $this->assertSame('Fictional Development Authority', DocumentLink::firstOrFail()->legalReference->issuing_authority);
        $this->assertSame('Fictional Proposed Reader', User::where('account_ref', 'DEMO-B1-USER-001')->firstOrFail()->roleAssignments->first()->role->name);
        $this->assertSame('demo_b1.records.read', Role::firstOrFail()->permissions->first()->slug);
        $this->assertSame(['dataset' => 'DEMO-B1', 'contains_real_data' => false], AuditEvent::firstOrFail()->changes);
        $this->assertNull(Personnel::where('staff_number', 'DEMO-B1-STAFF-002')->firstOrFail()->account);
        $this->assertSame(0, Personnel::where('staff_number', 'DEMO-B1-STAFF-002')->firstOrFail()->assignments()->count());
    }

    public function test_private_and_generated_attributes_are_not_mass_assignable_or_serialized(): void
    {
        $document = new Document;
        $document->fill(['document_ref' => 'DEMO-B1-DOC-PRIVATE', 'storage_key' => 'private/legal.pdf', 'uploaded_by' => 999]);
        $this->assertNull($document->storage_key);
        $this->assertNull($document->uploaded_by);
        $document->forceFill(['storage_key' => 'private/legal.pdf']);
        $this->assertArrayNotHasKey('storage_key', $document->toArray());

        $admission = new AdmissionEpisode;
        $admission->fill(['open_inmate_id' => 999]);
        $this->assertNull($admission->open_inmate_id);
        $this->assertArrayNotHasKey('open_inmate_id', $admission->toArray());
        $user = User::factory()->create();
        $this->assertArrayNotHasKey('password', $user->toArray());
        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }
}
