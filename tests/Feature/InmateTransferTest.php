<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\AuditEvent;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\TransferEvent;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\CanonicalDemoSeeder;
use Illuminate\Support\Str;
use Tests\B1DatabaseTestCase;

class InmateTransferTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalDemoSeeder::class);
    }

    protected function createOfficer(string $roleSlug, ?int $facilityId = null): User
    {
        $user = User::factory()->create([
            'email' => 'officer.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        $role = Role::where('slug', $roleSlug)->firstOrFail();

        if ($facilityId !== null) {
            $scope = AccessScope::firstOrCreate(
                ['kind' => 'Facility', 'facility_id' => $facilityId],
                ['scope_key' => 'SCOPE-FAC-'.$facilityId]
            );
        } else {
            $scope = AccessScope::where('kind', 'National')->firstOrFail();
        }

        UserRoleAssignment::create([
            'assignment_ref' => 'URA-TEST-'.strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'role_id' => $role->id,
            'access_scope_id' => $scope->id,
            'granted_at' => now(),
        ]);

        return $user;
    }

    protected function createActiveInmate(Facility $facility): Inmate
    {
        $inmate = Inmate::create([
            'identity_ref' => 'INM-'.date('Y').'-'.strtoupper(Str::random(6)),
            'prisoner_number' => 'PN-'.rand(10000, 99999),
            'first_name' => 'Transfer_'.Str::random(5),
            'last_name' => 'Subject_'.Str::random(5),
            'gender' => 'Male',
            'date_of_birth' => '1992-05-15',
            'nationality' => 'Ugandan',
            'district' => 'Kampala',
            'id_type' => 'National ID',
            'id_number' => 'CM'.rand(10000000, 99999999).'UG',
        ]);

        $episode = AdmissionEpisode::create([
            'admission_ref' => 'ADM-'.date('Y').'-'.strtoupper(Str::random(6)),
            'inmate_id' => $inmate->id,
            'admitting_facility_id' => $facility->id,
            'admitted_at' => now()->subMonths(2),
            'state' => 'Active',
            'classification' => 'Convicted',
            'legal_basis' => 'High Court Conviction Warrant',
        ]);

        CustodyLocation::create([
            'location_ref' => 'LOC-'.strtoupper(Str::random(8)),
            'admission_episode_id' => $episode->id,
            'facility_id' => $facility->id,
            'entered_at' => now()->subMonths(2),
            'left_at' => null,
        ]);

        return $inmate;
    }

    public function test_authorized_officer_can_request_transfer_without_prematurely_moving_custody(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('records_officer', $fac1->id);
        $inmate = $this->createActiveInmate($fac1);

        $payload = [
            'inmate_id' => $inmate->id,
            'origin_facility_id' => $fac1->id,
            'destination_facility_id' => $fac2->id,
            'priority' => 'Routine',
            'transfer_reason' => 'Overcrowding Relief',
            'proposed_transfer_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Routine relocation to alleviate facility density.',
            'supporting_doc_ref' => 'DOC-REL-'.rand(1000, 9999),
        ];

        $response = $this->actingAs($originOfficer)->post(route('transfers.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $transfer = Transfer::where('origin_facility_id', $fac1->id)
            ->where('destination_facility_id', $fac2->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('Pending Approval', $transfer->state);
        $this->assertSame('Overcrowding Relief', $transfer->reason);
        $this->assertSame($originOfficer->id, $transfer->requested_by);

        // Verify initial TransferEvent
        $event = TransferEvent::where('transfer_id', $transfer->id)->where('sequence', 1)->first();
        $this->assertNotNull($event);
        $this->assertSame('request', $event->event_type);
        $this->assertSame('Draft', $event->from_state);
        $this->assertSame('Pending Approval', $event->to_state);

        // Verify AuditEvent
        $audit = AuditEvent::where('action', 'transfer_requested')
            ->where('resource_reference', $transfer->transfer_ref)
            ->first();
        $this->assertNotNull($audit);
        $this->assertSame($originOfficer->id, $audit->actor_id);

        // Ensure inmate's current custody facility remains at origin
        $inmate->refresh();
        $this->assertSame($fac1->id, $inmate->currentFacilityId());
    }

    public function test_transfer_request_rejects_same_origin_and_destination(): void
    {
        $fac1 = Facility::firstOrFail();
        $originOfficer = $this->createOfficer('records_officer', $fac1->id);
        $inmate = $this->createActiveInmate($fac1);

        $response = $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'origin_facility_id' => $fac1->id,
            'destination_facility_id' => $fac1->id,
            'transfer_reason' => 'Relocation within same facility',
        ]);

        $response->assertSessionHasErrors(['destination_facility_id']);
    }

    public function test_concurrent_active_transfer_for_same_inmate_is_rejected(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();
        $fac3 = Facility::whereNotIn('id', [$fac1->id, $fac2->id])->firstOrFail();

        $originOfficer = $this->createOfficer('records_officer', $fac1->id);
        $inmate = $this->createActiveInmate($fac1);

        // First transfer request
        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'First Request',
        ])->assertRedirect();

        // Second concurrent transfer request must be rejected
        $secondResponse = $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac3->id,
            'transfer_reason' => 'Conflicting Second Request',
        ]);

        $secondResponse->assertSessionHasErrors(['transfer']);
    }

    public function test_authorized_reviewer_can_approve_transfer(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('records_officer', $fac1->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Security Reclassification',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        // Approve transfer
        $authRef = 'AUTH-ORD-'.rand(1000, 9999);
        $reviewResponse = $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Approve',
            'auth_ref' => $authRef,
            'notes' => 'Approved following commissioner security review.',
        ]);

        $reviewResponse->assertRedirect(route('transfers.show', $transfer->id));
        $reviewResponse->assertSessionHas('status');

        $transfer->refresh();
        $this->assertSame('Ready for Dispatch', $transfer->state);

        // Verify TransferEvent
        $event = TransferEvent::where('transfer_id', $transfer->id)->where('event_type', 'approve')->first();
        $this->assertNotNull($event);
        $this->assertSame($authRef, $event->authority_reference);

        // Inmate still resides at origin facility
        $inmate->refresh();
        $this->assertSame($fac1->id, $inmate->currentFacilityId());
    }

    public function test_authorized_reviewer_can_reject_or_request_clarification(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('records_officer', $fac1->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Medical Grounds',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        // 1. Clarification request
        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Clarify',
            'notes' => 'Please attach specialist medical report.',
        ])->assertRedirect();

        $transfer->refresh();
        $this->assertSame('Awaiting Clarification', $transfer->state);

        // 2. Reject transfer
        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Reject',
            'notes' => 'Medical facilities adequate at current facility.',
        ])->assertRedirect();

        $transfer->refresh();
        $this->assertSame('Rejected', $transfer->state);
    }

    public function test_origin_officer_can_dispatch_transfer_closing_origin_custody_location(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('facility_officer_in_charge', $fac1->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Specialized Care',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        // Approve first
        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Approve',
            'auth_ref' => 'AUTH-'.rand(1000, 9999),
        ])->assertRedirect();

        // Dispatch
        $handoverRef = 'HO-DISP-'.rand(1000, 9999);
        $dispatchResponse = $this->actingAs($originOfficer)->post(route('transfers.dispatch.store', $transfer->id), [
            'dispatch_date' => now()->toDateString(),
            'dispatch_time' => now()->addMinutes(10)->format('H:i'),
            'handover_ref' => $handoverRef,
            'escort_ref' => 'ESC-'.rand(1000, 9999),
            'notes' => 'Escorted under armed guard.',
        ]);

        $dispatchResponse->assertRedirect(route('transfers.show', $transfer->id));
        $dispatchResponse->assertSessionHas('status');

        $transfer->refresh();
        $this->assertSame('In Transit', $transfer->state);
        $this->assertNotNull($transfer->dispatched_at);

        // Verify origin custody location closed
        $originLocation = CustodyLocation::where('admission_episode_id', $transfer->admission_episode_id)
            ->where('facility_id', $fac1->id)
            ->firstOrFail();

        $this->assertNotNull($originLocation->left_at);
        $this->assertSame($transfer->id, (int) $originLocation->left_transfer_id);

        // Verify TransferEvent
        $event = TransferEvent::where('transfer_id', $transfer->id)->where('event_type', 'dispatch')->first();
        $this->assertNotNull($event);
        $this->assertSame($handoverRef, $event->authority_reference);
    }

    public function test_cannot_dispatch_unapproved_transfer(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('facility_officer_in_charge', $fac1->id);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Premature Dispatch Test',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        // Still 'Pending Approval' - attempting to dispatch must fail
        $response = $this->actingAs($originOfficer)->post(route('transfers.dispatch.store', $transfer->id), [
            'dispatch_date' => now()->toDateString(),
            'handover_ref' => 'HO-FAIL-1',
        ]);

        $response->assertSessionHasErrors(['state']);
    }

    public function test_destination_officer_can_confirm_arrival_updating_inmate_facility_to_destination(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('facility_officer_in_charge', $fac1->id);
        $destOfficer = $this->createOfficer('facility_officer_in_charge', $fac2->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        // 1. Request
        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Full Lifecycle Test',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        // 2. Approve
        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Approve',
            'auth_ref' => 'AUTH-LIFECYCLE-1',
        ])->assertRedirect();

        // 3. Dispatch
        $this->actingAs($originOfficer)->post(route('transfers.dispatch.store', $transfer->id), [
            'dispatch_date' => now()->toDateString(),
            'dispatch_time' => now()->addMinutes(10)->format('H:i'),
            'handover_ref' => 'HO-LIFECYCLE-1',
        ])->assertRedirect();

        // 4. Confirm Arrival at destination
        $arrivalRef = 'ARR-CONF-'.rand(1000, 9999);
        $receiveResponse = $this->actingAs($destOfficer)->post(route('transfers.receive.store', $transfer->id), [
            'arrival_date' => now()->toDateString(),
            'arrival_time' => now()->addMinutes(30)->format('H:i'),
            'arrival_ref' => $arrivalRef,
            'cell_block' => 'Ward C',
            'notes' => 'Inmate received in good health and admitted to destination ward.',
        ]);

        $receiveResponse->assertRedirect(route('transfers.show', $transfer->id));
        $receiveResponse->assertSessionHas('status');

        $transfer->refresh();
        $this->assertSame('Completed', $transfer->state);
        $this->assertNotNull($transfer->received_at);

        // Inmate current custody facility is now officially Facility 2!
        $inmate->refresh();
        $this->assertSame($fac2->id, $inmate->currentFacilityId());

        // Verify new open CustodyLocation at Facility 2
        $newLocation = CustodyLocation::where('admission_episode_id', $transfer->admission_episode_id)
            ->where('facility_id', $fac2->id)
            ->whereNull('left_at')
            ->first();

        $this->assertNotNull($newLocation);
        $this->assertSame($transfer->id, (int) $newLocation->entered_transfer_id);

        // Verify TransferEvent
        $event = TransferEvent::where('transfer_id', $transfer->id)->where('event_type', 'receive')->first();
        $this->assertNotNull($event);
        $this->assertSame($arrivalRef, $event->authority_reference);

        // Verify AuditEvent
        $audit = AuditEvent::where('action', 'transfer_received')
            ->where('resource_reference', $transfer->transfer_ref)
            ->first();
        $this->assertNotNull($audit);
    }

    public function test_cannot_receive_transfer_before_dispatch(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('facility_officer_in_charge', $fac1->id);
        $destOfficer = $this->createOfficer('facility_officer_in_charge', $fac2->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Premature Arrival Test',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Approve',
            'auth_ref' => 'AUTH-PREMATURE-1',
        ])->assertRedirect();

        // Approved but not dispatched - receive must fail
        $response = $this->actingAs($destOfficer)->post(route('transfers.receive.store', $transfer->id), [
            'arrival_date' => now()->toDateString(),
            'arrival_ref' => 'ARR-FAIL-1',
        ]);

        $response->assertSessionHasErrors(['state']);
    }

    public function test_receiving_officer_cannot_confirm_arrival_if_not_authorized_for_destination_facility(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('facility_officer_in_charge', $fac1->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Cross Facility Receiving Auth Test',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Approve',
            'auth_ref' => 'AUTH-1',
        ])->assertRedirect();

        $this->actingAs($originOfficer)->post(route('transfers.dispatch.store', $transfer->id), [
            'dispatch_date' => now()->toDateString(),
            'handover_ref' => 'HO-1',
        ])->assertRedirect();

        // Origin officer (strictly scoped to Fac 1) cannot receive at Fac 2
        $response = $this->actingAs($originOfficer)->post(route('transfers.receive.store', $transfer->id), [
            'arrival_date' => now()->toDateString(),
            'arrival_ref' => 'ARR-UNAUTH-1',
        ]);

        $response->assertForbidden();
    }

    public function test_dispatching_officer_cannot_dispatch_if_not_authorized_for_origin_facility(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $originOfficer = $this->createOfficer('facility_officer_in_charge', $fac1->id);
        $destOfficer = $this->createOfficer('facility_officer_in_charge', $fac2->id);
        $reviewer = $this->createOfficer('commissioner', null);
        $inmate = $this->createActiveInmate($fac1);

        $this->actingAs($originOfficer)->post(route('transfers.store'), [
            'inmate_id' => $inmate->id,
            'destination_facility_id' => $fac2->id,
            'transfer_reason' => 'Cross Facility Dispatch Auth Test',
        ])->assertRedirect();

        $transfer = Transfer::where('destination_facility_id', $fac2->id)->latest('id')->firstOrFail();

        $this->actingAs($reviewer)->post(route('transfers.review.store', $transfer->id), [
            'decision' => 'Approve',
            'auth_ref' => 'AUTH-1',
        ])->assertRedirect();

        // Destination officer (strictly scoped to Fac 2) cannot dispatch from Fac 1
        $response = $this->actingAs($destOfficer)->post(route('transfers.dispatch.store', $transfer->id), [
            'dispatch_date' => now()->toDateString(),
            'handover_ref' => 'HO-UNAUTH-1',
        ]);

        $response->assertForbidden();
    }

    public function test_transfers_directory_and_history_render_cleanly_with_database_records(): void
    {
        $fac = Facility::firstOrFail();
        $officer = $this->createOfficer('facility_officer_in_charge', $fac->id);

        $responseIndex = $this->actingAs($officer)->get(route('transfers.index'));
        $responseIndex->assertOk();
        $responseIndex->assertViewHas('transfers');
        $responseIndex->assertViewHas('counts');

        $responseHistory = $this->actingAs($officer)->get(route('transfers.history'));
        $responseHistory->assertOk();
        $responseHistory->assertViewHas('transfers');

        $responseCreate = $this->actingAs($officer)->get(route('transfers.create'));
        $responseCreate->assertOk();
    }
}
