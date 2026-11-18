<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\Visitor;
use Tests\B1DatabaseTestCase;

class VisitorPreviewTest extends B1DatabaseTestCase
{
    /** @return array{Visitor, Visit} */
    private function visitorWithVisit(string $state = 'Requested'): array
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $episode = AdmissionEpisode::factory()->create(['admitting_facility_id' => $facility->id, 'state' => 'Active']);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $visitor = Visitor::factory()->create(['registered_facility_id' => $facility->id, 'id_type' => 'Passport', 'id_number' => 'DEMO-SECRET-1234']);
        $visit = Visit::factory()->create([
            'visitor_id' => $visitor->id, 'admission_episode_id' => $episode->id,
            'facility_id' => $facility->id, 'state' => $state,
        ]);
        $user = User::factory()->create(['status' => 'Active']);
        $role = Role::firstOrCreate(['slug' => 'system_admin'], ['name' => 'Test Administrator']);
        $scope = AccessScope::firstOrCreate(['kind' => 'National'], ['scope_key' => 'TEST-NATIONAL']);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);
        $this->actingAs($user);

        return [$visitor, $visit];
    }

    public function test_visitor_directory_lists_registered_database_visitors(): void
    {
        [$visitor] = $this->visitorWithVisit();
        $this->get(route('visitors.index'))->assertOk()->assertSee($visitor->visitor_ref);
    }

    public function test_visitor_profile_shows_masked_document_and_database_visit_history(): void
    {
        [$visitor, $visit] = $this->visitorWithVisit();
        $this->get(route('visitors.show', $visitor))->assertOk()->assertSee($visit->visit_ref)->assertSee('1234')->assertDontSee('DEMO-SECRET-1234');
    }

    public function test_missing_visitor_is_not_found(): void
    {
        $this->visitorWithVisit();
        $this->get(route('visitors.show', 999999))->assertNotFound();
    }

    public function test_registration_form_submits_to_the_authorized_persistence_endpoint(): void
    {
        $this->visitorWithVisit();
        $this->get(route('visitors.create'))->assertOk()->assertSee('method="POST"', false)->assertSee(route('visitors.store'))->assertDontSee('nothing was saved');
        $this->post(route('visitors.store'), [])->assertSessionHasErrors(['first_name', 'last_name', 'facility_id']);
    }

    public function test_visit_records_directory_lists_database_visits(): void
    {
        [, $visit] = $this->visitorWithVisit();
        $this->get(route('visitors.records'))->assertOk()->assertSee($visit->visit_ref);
    }

    public function test_schedule_form_lists_active_custody_episodes_only(): void
    {
        [, $visit] = $this->visitorWithVisit();
        $closed = AdmissionEpisode::factory()->closed()->create();
        $this->get(route('visitors.schedule'))->assertOk()->assertSee('Schedule a Visit')
            ->assertViewHas('episodes', fn ($episodes): bool => $episodes->contains('id', $visit->admission_episode_id) && ! $episodes->contains('id', $closed->id));
    }

    public function test_visit_details_show_timeline_and_participants(): void
    {
        [$visitor, $visit] = $this->visitorWithVisit();
        $this->get(route('visitors.visits.show', $visit))->assertOk()->assertSee($visit->visit_ref)->assertSee($visitor->first_name)->assertSee('Visit Timeline');
    }

    public function test_check_out_form_uses_a_server_validated_post_action(): void
    {
        [, $visit] = $this->visitorWithVisit('Checked In');
        $this->get(route('visitors.visits.check-out', $visit))->assertOk()->assertSee(route('visitors.visits.check-out.store', $visit))->assertSee('Confirm Check Out');
    }

    public function test_cancel_form_requires_confirmation_and_reason(): void
    {
        [, $visit] = $this->visitorWithVisit();
        $this->get(route('visitors.visits.cancel', $visit))->assertOk()->assertSee('name="reason"', false)->assertSee('name="confirmed"', false);
    }

    public function test_edit_form_supports_audited_corrections_without_revealing_document_number(): void
    {
        [$visitor] = $this->visitorWithVisit();
        $this->get(route('visitors.edit', $visitor))->assertOk()->assertSee('Review Changes')->assertDontSee('DEMO-SECRET-1234');
    }
}
