<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\AuditEvent;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\AuditService;
use App\Services\InmateService;
use App\Services\TransferService;
use App\Services\VisitService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\B1DatabaseTestCase;

class VisitorOperationsTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-17 08:00:00', 'UTC'));
    }

    /** @param list<string> $permissions */
    private function officer(array $permissions, ?Facility $facility = null, ?User $user = null): User
    {
        $user ??= User::factory()->create(['status' => 'Active']);
        $role = Role::factory()->create();
        foreach ($permissions as $slug) {
            $permission = Permission::firstOrCreate(['slug' => $slug], ['name' => $slug, 'module' => 'Visitors']);
            $role->permissions()->attach($permission);
        }
        $scope = $facility
            ? AccessScope::firstOrCreate(['kind' => 'Facility', 'facility_id' => $facility->id], ['scope_key' => 'TEST-FAC-'.$facility->id])
            : AccessScope::firstOrCreate(['kind' => 'National'], ['scope_key' => 'TEST-NATIONAL']);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $user;
    }

    /** @return array{Facility, AdmissionEpisode, Visitor, User} */
    private function context(): array
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $episode = AdmissionEpisode::factory()->create(['admitting_facility_id' => $facility->id, 'state' => 'Active']);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $visitor = Visitor::factory()->create(['registered_facility_id' => $facility->id]);
        $officer = $this->officer(['visitors.read', 'visitors.register', 'visits.schedule', 'visits.authorize', 'visits.checkinout'], $facility);

        return [$facility, $episode, $visitor, $officer];
    }

    /** @return array<string, mixed> */
    private function visitData(Facility $facility, AdmissionEpisode $episode, Visitor $visitor): array
    {
        return [
            'facility_id' => $facility->id, 'admission_episode_id' => $episode->id, 'visitor_id' => $visitor->id,
            'scheduled_start_at' => '2026-11-18T12:00', 'scheduled_end_at' => '2026-11-18T12:30',
            'purpose' => 'Family Visit', 'relationship' => 'Mother', 'confirmed' => '1',
        ];
    }

    /** @return array<string, mixed> */
    private function identityData(Facility $facility): array
    {
        return [
            'facility_id' => $facility->id, 'first_name' => 'Fictional', 'last_name' => 'Visitor',
            'gender' => 'Female', 'id_type' => 'Passport', 'id_number' => 'DEMO-ID-12345678',
            'phone' => '+256700000111', 'email' => 'visitor@example.invalid', 'residence' => 'Fictional District',
            'confirmed' => '1',
        ];
    }

    public function test_registration_encrypts_document_and_audits_without_contact_data(): void
    {
        [$facility, , , $officer] = $this->context();
        $data = $this->identityData($facility);
        $this->actingAs($officer)->post(route('visitors.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $visitor = Visitor::where('first_name', 'Fictional')->where('last_name', 'Visitor')->firstOrFail();
        $this->assertSame('Under Review', $visitor->status);
        $this->assertSame('DEMO-ID-12345678', $visitor->id_number);
        $this->assertNotSame('DEMO-ID-12345678', DB::table('visitors')->where('id', $visitor->id)->value('id_number'));
        $this->assertDatabaseHas('audit_events', ['action' => 'visitor_registered', 'actor_id' => $officer->id, 'facility_id' => $facility->id]);
        $audit = AuditEvent::where('resource_reference', $visitor->visitor_ref)->firstOrFail();
        $this->assertStringNotContainsString('DEMO-ID-12345678', json_encode($audit->changes));
        $this->actingAs($officer)->get(route('visitors.show', $visitor))->assertOk()->assertSee('5678')->assertDontSee($data['id_number'])->assertDontSee($data['phone'])->assertDontSee($data['email']);
        $this->get(route('visitors.index'))->assertOk()->assertDontSee($data['id_number'])->assertDontSee($data['phone']);
    }

    public function test_duplicate_document_is_rejected_without_disclosing_other_visitor(): void
    {
        [$facility, , , $officer] = $this->context();
        $data = $this->identityData($facility);
        $this->actingAs($officer)->post(route('visitors.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('visitors.store'), array_replace($data, ['first_name' => 'Another', 'id_number' => 'demo-id-12345678']))
            ->assertSessionHasErrors(['id_number' => 'A visitor with this identity document already exists. Contact an authorized records officer.']);
        $this->assertSame(1, Visitor::where('last_name', 'Visitor')->count());
    }

    public function test_invalid_registration_does_not_flash_document_number_or_grant_status(): void
    {
        [$facility, , , $officer] = $this->context();
        $data = array_replace($this->identityData($facility), ['first_name' => '', 'status' => 'Registered']);
        $this->actingAs($officer)->from(route('visitors.create'))->post(route('visitors.store'), $data)
            ->assertSessionHasErrors('first_name')->assertSessionMissing('_old_input.id_number');
        $this->assertSame(0, Visitor::where('last_name', 'Visitor')->count());
    }

    public function test_full_request_approval_schedule_checkin_checkout_lifecycle_has_one_event_per_action(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $this->actingAs($officer)->post(route('visitors.visits.store'), $this->visitData($facility, $episode, $visitor))->assertSessionHasNoErrors()->assertRedirect();
        $visit = Visit::where('visitor_id', $visitor->id)->firstOrFail();
        $this->assertSame('Requested', $visit->state);
        $this->assertSame('2026-11-18 09:00:00', $visit->scheduled_start_at->format('Y-m-d H:i:s'));
        $this->assertDatabaseHas('visitor_relationships', ['visitor_id' => $visitor->id, 'inmate_id' => $episode->inmate_id, 'relationship' => 'Mother']);
        $payload = ['reason' => 'Fictional authorized decision', 'confirmed' => '1'];
        $this->post(route('visitors.visits.review.store', $visit), $payload + ['decision' => 'Approve'])->assertSessionHasNoErrors();
        $this->assertSame('Approved', $visit->fresh()->state);
        $this->post(route('visitors.visits.schedule.store', $visit), $payload)->assertSessionHasNoErrors();
        $this->travelTo(CarbonImmutable::parse('2026-11-18 09:05:00', 'UTC'));
        $this->post(route('visitors.visits.check-in.store', $visit), $payload + ['identity_verified' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('Checked In', $visit->fresh()->state);
        $checkedInAt = $visit->fresh()->checked_in_at;
        $this->post(route('visitors.visits.check-in.store', $visit), $payload + ['identity_verified' => '1'])->assertSessionHasErrors('state');
        $this->assertTrue($checkedInAt->eq($visit->fresh()->checked_in_at));
        $this->travel(5)->minutes();
        $this->post(route('visitors.visits.check-out.store', $visit), $payload)->assertSessionHasNoErrors();
        $this->assertSame('Completed', $visit->fresh()->state);
        $this->post(route('visitors.visits.check-out.store', $visit), $payload)->assertSessionHasErrors('state');
        $this->assertSame(5, $visit->events()->count());
        $this->assertSame([1, 2, 3, 4, 5], $visit->events()->pluck('sequence')->all());
        $this->assertSame(5, AuditEvent::where('resource_reference', $visit->visit_ref)->count());
        $this->assertSame($officer->id, $visit->events()->latest('sequence')->first()->actor_id);
        $this->assertNotNull($visit->events()->latest('sequence')->first()->actor_role);
    }

    public function test_overlapping_visitor_or_inmate_requests_are_rejected_but_adjacent_slots_are_allowed(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $data = $this->visitData($facility, $episode, $visitor);
        $this->actingAs($officer)->post(route('visitors.visits.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('visitors.visits.store'), array_replace($data, ['scheduled_start_at' => '2026-11-18T12:10']))->assertSessionHasErrors('scheduled_start_at');
        $otherVisitor = Visitor::factory()->create(['registered_facility_id' => $facility->id]);
        $this->post(route('visitors.visits.store'), array_replace($data, ['visitor_id' => $otherVisitor->id]))->assertSessionHasErrors('scheduled_start_at');
        $this->post(route('visitors.visits.store'), array_replace($data, ['scheduled_start_at' => '2026-11-18T12:30', 'scheduled_end_at' => '2026-11-18T13:00']))->assertSessionHasNoErrors();
        $this->assertSame(2, Visit::where('visitor_id', $visitor->id)->count());
    }

    public function test_denial_and_cancellation_preserve_history_and_free_the_slot(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $data = $this->visitData($facility, $episode, $visitor);
        $this->actingAs($officer)->post(route('visitors.visits.store'), $data)->assertSessionHasNoErrors();
        $visit = Visit::where('visitor_id', $visitor->id)->firstOrFail();
        $this->post(route('visitors.visits.review.store', $visit), ['decision' => 'Deny', 'reason' => 'Eligibility not confirmed', 'confirmed' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('Denied', $visit->fresh()->state);
        $this->post(route('visitors.visits.store'), $data)->assertSessionHasNoErrors();
        $second = Visit::where('visitor_id', $visitor->id)->latest('id')->first();
        $this->post(route('visitors.visits.cancel.store', $second), ['reason' => 'Visitor withdrew', 'confirmed' => '1'])->assertSessionHasNoErrors();
        $this->post(route('visitors.visits.cancel.store', $second), ['reason' => 'Replay', 'confirmed' => '1'])->assertSessionHasErrors('state');
        $this->assertSame('Cancelled', $second->fresh()->state);
        $this->assertSame(2, $second->events()->count());
    }

    public function test_reception_permission_cannot_approve_and_checkout_before_checkin_is_refused(): void
    {
        [$facility, $episode, $visitor] = $this->context();
        $warden = $this->officer(['visitors.read', 'visits.checkinout'], $facility);
        $visit = Visit::factory()->create(['visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $payload = ['reason' => 'Attempt', 'confirmed' => '1'];
        $this->actingAs($warden)->post(route('visitors.visits.review.store', $visit), $payload + ['decision' => 'Approve'])->assertForbidden();
        $this->post(route('visitors.visits.check-out.store', $visit), $payload)->assertSessionHasErrors('state');
        $this->assertSame('Requested', $visit->fresh()->state);
        $this->assertSame(0, $visit->events()->count());
    }

    public function test_cross_facility_identity_and_visit_access_is_not_found_including_mixed_national_read_role(): void
    {
        [$facility, $episode, $visitor] = $this->context();
        $otherFacility = Facility::factory()->create(['status' => 'Active']);
        $actor = $this->officer(['visitors.read', 'visits.schedule', 'visits.checkinout', 'visits.authorize'], $otherFacility);
        $this->officer(['reports.view'], null, $actor);
        $visit = Visit::factory()->create(['visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $this->actingAs($actor)->get(route('visitors.show', $visitor))->assertNotFound();
        $this->get(route('visitors.visits.show', $visit))->assertNotFound();
        $this->post(route('visitors.visits.review.store', $visit), ['decision' => 'Approve', 'reason' => 'Attempt', 'confirmed' => '1'])->assertNotFound();
        $this->get(route('visitors.index'))->assertOk()->assertDontSee($visitor->visitor_ref);
        $this->get(route('visitors.records'))->assertOk()->assertDontSee($visit->visit_ref);
        $this->post(route('visitors.visits.store'), $this->visitData($facility, $episode, $visitor))->assertForbidden();
    }

    public function test_shared_visitor_profile_only_exposes_visits_in_the_readers_facility(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        Visit::factory()->create(['visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $elsewhere = Visit::factory()->create(['visitor_id' => $visitor->id]);
        $this->actingAs($officer)->get(route('visitors.show', $visitor))->assertOk()->assertDontSee($elsewhere->visit_ref);
    }

    public function test_guest_and_active_account_without_permissions_cannot_access_visitor_operations(): void
    {
        $this->get(route('visitors.index'))->assertRedirect(route('login'));
        $this->post(route('visitors.store'), [])->assertRedirect(route('login'));
        $user = User::factory()->create(['status' => 'Active']);
        $this->actingAs($user)->get(route('visitors.index'))->assertForbidden();
        $this->post(route('visitors.store'), [])->assertForbidden();
    }

    public function test_schedule_rejects_closed_or_transferred_custody_and_blocked_visitors(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $data = $this->visitData($facility, $episode, $visitor);
        $visitor->update(['status' => 'Blocked']);
        $this->actingAs($officer)->post(route('visitors.visits.store'), $data)->assertSessionHasErrors('visitor_id');
        $visitor->update(['status' => 'Under Review']);
        $episode->update(['closed_at' => now(), 'state' => 'Discharged']);
        $this->post(route('visitors.visits.store'), $data)->assertSessionHasErrors('admission_episode_id');
        $episode->update(['closed_at' => null, 'state' => 'Active']);
        $episode->locations()->update(['left_at' => now()]);
        $this->post(route('visitors.visits.store'), $data)->assertNotFound();
        $this->assertSame(0, Visit::where('visitor_id', $visitor->id)->count());
    }

    public function test_checkin_requires_the_window_and_manual_identity_verification(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $visit = Visit::factory()->create([
            'visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id, 'state' => 'Scheduled',
            'scheduled_start_at' => '2026-11-18 09:00:00', 'scheduled_end_at' => '2026-11-18 09:30:00',
        ]);
        $payload = ['reason' => 'Reception arrival', 'confirmed' => '1'];
        $this->actingAs($officer)->post(route('visitors.visits.check-in.store', $visit), $payload + ['identity_verified' => '1'])->assertSessionHasErrors('state');
        $this->travelTo(CarbonImmutable::parse('2026-11-18 09:05:00', 'UTC'));
        $this->post(route('visitors.visits.check-in.store', $visit), $payload)->assertSessionHasErrors('identity_verified');
        $this->post(route('visitors.visits.check-in.store', $visit), $payload + ['identity_verified' => '0'])->assertSessionHasErrors('identity_verified');
        $this->assertNull($visit->fresh()->checked_in_at);
    }

    public function test_dispatch_cancels_open_visits_with_actor_history(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $this->officer(['transfers.dispatch'], $facility, $officer);
        $visit = Visit::factory()->create(['visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id, 'state' => 'Scheduled']);
        $transfer = Transfer::factory()->create([
            'admission_episode_id' => $episode->id, 'origin_facility_id' => $facility->id, 'state' => 'Ready for Dispatch',
            'requested_at' => now()->subHour(),
        ]);
        app(TransferService::class)->dispatch($transfer, ['dispatch_date' => now()->toDateString(), 'dispatch_time' => '08:00:00', 'handover_ref' => 'DEMO-HANDOVER'], $officer);
        $this->assertSame('Cancelled', $visit->fresh()->state);
        $this->assertDatabaseHas('visit_events', ['visit_id' => $visit->id, 'event_type' => 'custody-change', 'actor_id' => $officer->id]);
    }

    public function test_discharge_cancels_visits_and_checked_in_visitors_prevent_custody_movement(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $this->officer(['inmates.discharge'], $facility, $officer);
        $visit = Visit::factory()->create([
            'visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id,
            'state' => 'Checked In', 'checked_in_at' => now(),
        ]);
        $data = ['discharge_reason' => 'Court order', 'auth_ref' => 'DEMO-AUTH'];
        try {
            app(InmateService::class)->discharge($episode->inmate, $data, $officer);
            $this->fail('Expected active visitor to prevent discharge.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('visits', $exception->errors());
        }
        $this->assertNull($episode->fresh()->closed_at);
        $visit->update(['state' => 'Requested', 'checked_in_at' => null]);
        app(InmateService::class)->discharge($episode->inmate, $data, $officer);
        $this->assertSame('Cancelled', $visit->fresh()->state);
        $this->assertNotNull($episode->fresh()->closed_at);
    }

    public function test_transaction_rolls_back_visit_and_event_when_audit_fails(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $this->mock(AuditService::class)->shouldReceive('log')->once()->andThrow(new \RuntimeException('Audit unavailable'));
        $this->actingAs($officer)->post(route('visitors.visits.store'), $this->visitData($facility, $episode, $visitor))->assertStatus(500);
        $this->assertSame(0, Visit::where('visitor_id', $visitor->id)->count());
        $this->assertSame(0, $visitor->relationships()->count());
    }

    public function test_identifier_redaction_preserves_visits_and_removes_personal_identifiers(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $visitor->update(['id_type' => 'Passport', 'id_number' => 'DEMO-REDACT-1234', 'phone' => '123456789', 'email' => 'private@example.invalid']);
        $visit = Visit::factory()->create(['visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $this->actingAs($officer)->post(route('visitors.redact', $visitor), ['confirmed' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($visitor->fresh()->id_number);
        $this->assertNull($visitor->fresh()->phone);
        $this->assertNull($visitor->fresh()->email);
        $this->assertDatabaseHas('visits', ['id' => $visit->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'visitor_identifiers_redacted', 'resource_reference' => $visitor->visitor_ref]);
    }

    public function test_production_operations_require_confirmed_rules(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['visitation.rules_confirmed' => false, 'operations.production_approved' => true]);
        $this->actingAs($officer)->post(route('visitors.visits.store'), $this->visitData($facility, $episode, $visitor))
            ->assertSessionHasErrors('visitation');
        $this->assertSame(0, Visit::where('visitor_id', $visitor->id)->count());
    }

    public function test_configured_hours_and_daily_limit_are_enforced(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $data = $this->visitData($facility, $episode, $visitor);
        config(['visitation.visiting_hours' => ['start' => '09:00', 'end' => '12:00']]);
        $this->actingAs($officer)->post(route('visitors.visits.store'), $data)->assertSessionHasErrors('scheduled_start_at');
        config(['visitation.visiting_hours' => null, 'visitation.max_visits_per_inmate_per_day' => 1]);
        $this->post(route('visitors.visits.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('visitors.visits.store'), array_replace($data, ['scheduled_start_at' => '2026-11-18T14:00', 'scheduled_end_at' => '2026-11-18T14:30']))->assertSessionHasErrors('scheduled_start_at');
    }

    public function test_visit_events_are_append_only(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $visit = app(VisitService::class)->request($this->visitData($facility, $episode, $visitor), $officer);
        $event = $visit->events()->firstOrFail();
        $this->expectException(DomainException::class);
        $event->update(['reason' => 'Rewrite']);
    }

    public function test_profile_and_forms_escape_user_supplied_html_and_render_without_demo_fallback(): void
    {
        [$facility, $episode, $visitor, $officer] = $this->context();
        $visitor->update(['first_name' => '<script>alert(1)</script>']);
        $this->actingAs($officer)->get(route('visitors.show', $visitor))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('visitors.create'))->assertOk()->assertSee('Register Visitor')->assertDontSee('nothing was saved');
        $this->get(route('visitors.edit', $visitor))->assertOk()->assertSee('Review Changes');
        $this->get(route('visitors.schedule'))->assertOk()->assertSee('Schedule a Visit');
        $visit = Visit::factory()->create(['visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        foreach (['show', 'review', 'check-in', 'check-out', 'cancel'] as $page) {
            $this->get(route('visitors.visits.'.$page, $visit))->assertOk();
        }
    }
}
