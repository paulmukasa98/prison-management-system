<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Permission;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use App\Support\Demo\RoleDemoData;
use Tests\B1DatabaseTestCase;

class InterfacePermissionsTest extends B1DatabaseTestCase
{
    /** @param list<string> $permissions */
    private function grant(array $permissions, ?Facility $facility = null, ?User $user = null, ?string $roleSlug = null): User
    {
        $user ??= User::factory()->create(['status' => 'Active']);
        $role = Role::factory()->create($roleSlug ? ['slug' => $roleSlug] : []);
        foreach ($permissions as $permission) {
            $role->permissions()->attach(Permission::firstOrCreate(['slug' => $permission], ['name' => $permission, 'module' => 'Interface Tests']));
        }
        $scope = AccessScope::firstOrCreate(['kind' => $facility ? 'Facility' : 'National', 'facility_id' => $facility?->id], ['scope_key' => $facility ? 'UI-FAC-'.$facility->id : 'UI-NATIONAL']);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $user;
    }

    private function inmate(Facility $facility): Inmate
    {
        $inmate = Inmate::factory()->create();
        $episode = AdmissionEpisode::factory()->create(['inmate_id' => $inmate->id, 'admitting_facility_id' => $facility->id, 'state' => 'Active']);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);

        return $inmate;
    }

    private function person(Facility $facility): Personnel
    {
        $person = Personnel::factory()->create(['employment_status' => 'Active']);
        PersonnelAssignment::factory()->create(['personnel_id' => $person->id, 'facility_id' => $facility->id, 'start_date' => now()->subDay(), 'end_date' => null, 'status' => 'Active']);

        return $person;
    }

    public function test_duty_warden_sees_only_permitted_navigation_and_cannot_admit_through_a_direct_request(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $inmate = $this->inmate($facility);
        $warden = $this->grant(RoleDemoData::find('duty_warden')['grants'], $facility, roleSlug: 'duty_warden');
        $response = $this->actingAs($warden)->get(route('dashboard'))->assertOk()
            ->assertSee('Active Inmates')->assertSee('Visits This Period')->assertDontSee('Total Personnel')->assertDontSee('Pending Transfers')->assertDontSee('Quick Actions');
        foreach (['inmates.create', 'transfers.index', 'personnel.index', 'facilities.index', 'reports.index', 'audit.index', 'users.index', 'settings.index', 'visitors.create', 'visitors.schedule'] as $route) {
            $response->assertDontSee('href="'.route($route).'"', false);
        }
        foreach (['inmates.index', 'inmates.discharges', 'visitors.index', 'visitors.records', 'profile.show'] as $route) {
            $response->assertSee('href="'.route($route).'"', false);
        }
        $this->get(route('inmates.index'))->assertOk()->assertDontSee('New Admission')->assertDontSee('/${inmate.id}/edit', false)
            ->assertViewHas('inmates', fn (array $records): bool => $records[0]['can_edit'] === false);
        $profile = $this->get(route('inmates.show', $inmate))->assertOk();
        foreach (['inmates.edit', 'inmates.discharge'] as $route) {
            $profile->assertDontSee('href="'.route($route, $inmate).'"', false);
        }
        $profile->assertDontSee('Initiate Transfer')->assertDontSee('Schedule a Visit');
        $this->get(route('inmates.create'))->assertForbidden();
        $this->post(route('inmates.store'), [])->assertForbidden();
        $this->assertDatabaseCount('inmates', 1);
    }

    public function test_directory_and_profile_edit_actions_follow_permission_specific_facility_scope(): void
    {
        $own = Facility::factory()->create();
        $other = Facility::factory()->create();
        $local = $this->inmate($own);
        $remote = $this->inmate($other);
        $user = $this->grant(['inmates.read']);
        $this->grant(['inmates.edit', 'inmates.discharge', 'transfers.request', 'visits.schedule'], $own, $user);
        $this->actingAs($user)->get(route('inmates.index'))->assertOk()->assertSee('x-if="inmate.can_edit"', false)
            ->assertViewHas('inmates', function (array $records) use ($local, $remote): bool {
                $byId = collect($records)->keyBy('id');

                return $byId[$local->id]['can_edit'] === true && $byId[$remote->id]['can_edit'] === false;
            });
        $this->get(route('inmates.show', $local))->assertOk()->assertSee('href="'.route('inmates.edit', $local).'"', false)->assertSee('Initiate Discharge')->assertSee('Initiate Transfer');
        $this->get(route('inmates.show', $remote))->assertOk()->assertDontSee('href="'.route('inmates.edit', $remote).'"', false)->assertDontSee('Initiate Discharge')->assertDontSee('Initiate Transfer');
        $this->get(route('inmates.edit', $remote))->assertNotFound();
    }

    public function test_read_only_personnel_and_facility_users_have_no_mutation_controls(): void
    {
        $facility = Facility::factory()->create();
        $person = $this->person($facility);
        $reader = $this->grant(['personnel.read', 'facilities.read'], $facility);
        $this->actingAs($reader)->get(route('personnel.index'))->assertOk()->assertDontSee('href="'.route('personnel.create').'"', false)
            ->assertDontSee('/${p.id}/edit', false)->assertViewHas('personnel', fn (array $records): bool => $records[0]['can_manage'] === false);
        $this->get(route('personnel.show', $person))->assertOk()->assertDontSee('Edit Details')->assertDontSee('New Assignment');
        $this->get(route('personnel.assignments'))->assertOk()->assertDontSee('New Assignment');
        $this->get(route('facilities.index'))->assertOk()->assertDontSee('href="'.route('facilities.create').'"', false)
            ->assertDontSee('/${f.id}/edit', false)->assertViewHas('facilities', fn (array $records): bool => $records[0]['can_manage'] === false);
        $this->get(route('facilities.show', $facility))->assertOk()->assertDontSee('href="'.route('facilities.edit', $facility).'"', false);
        $this->get(route('facilities.personnel', $facility))->assertOk()->assertDontSee('New Assignment');
    }

    public function test_scoped_managers_can_edit_only_their_records_and_cannot_create_national_facilities(): void
    {
        $own = Facility::factory()->create();
        $other = Facility::factory()->create();
        $local = $this->person($own);
        $remote = $this->person($other);
        $user = $this->grant(['personnel.read', 'facilities.read']);
        $this->grant(['personnel.manage', 'facilities.manage'], $own, $user);
        $this->actingAs($user)->get(route('personnel.index'))->assertOk()->assertViewHas('personnel', function (array $records) use ($local, $remote): bool {
            $byId = collect($records)->keyBy('id');

            return $byId[$local->id]['can_manage'] === true && $byId[$remote->id]['can_manage'] === false;
        });
        $this->get(route('facilities.index'))->assertOk()->assertDontSee('href="'.route('facilities.create').'"', false)
            ->assertViewHas('facilities', function (array $records) use ($own, $other): bool {
                $byId = collect($records)->keyBy('id');

                return $byId[$own->id]['can_manage'] === true && $byId[$other->id]['can_manage'] === false;
            });
        $this->get(route('personnel.show', $remote))->assertOk()->assertDontSee('Edit Details')->assertDontSee('New Assignment');
        $this->get(route('facilities.create'))->assertForbidden();
    }

    public function test_transfer_actions_use_origin_and_destination_permissions_and_readers_cannot_approve(): void
    {
        $origin = Facility::factory()->create();
        $destination = Facility::factory()->create();
        $inmate = $this->inmate($origin);
        $transfer = Transfer::factory()->create(['admission_episode_id' => $inmate->admissions()->firstOrFail()->id, 'origin_facility_id' => $origin->id, 'destination_facility_id' => $destination->id, 'state' => 'Pending Approval']);
        $reviewer = $this->grant(['transfers.review', 'transfers.dispatch', 'transfers.receive'], $origin);
        $this->actingAs($reviewer)->get(route('transfers.index'))->assertOk()->assertDontSee('href="'.route('transfers.create').'"', false)
            ->assertViewHas('transfers', fn (array $records): bool => $records[0]['can_review'] === true && $records[0]['can_dispatch'] === true && $records[0]['can_receive'] === false);
        $this->get(route('transfers.show', $transfer))->assertOk()->assertSee('Review Request')->assertDontSee('View Inmate Profile');
        $this->get(route('transfers.review', $transfer))->assertOk()->assertDontSee('value="Approve"', false)->assertSee('value="Reject"', false)->assertSee('value="Clarify"', false);
        $this->post(route('transfers.review.store', $transfer), ['decision' => 'Approve', 'auth_ref' => 'AUTH-1', 'confirmed' => '1'])->assertForbidden();
        $this->assertSame('Pending Approval', $transfer->fresh()->state);
        $this->grant(['transfers.authorize'], $origin, $reviewer);
        $this->get(route('transfers.review', $transfer))->assertOk()->assertSee('value="Approve"', false);
        $transfer->update(['state' => 'Arrival Confirmation Pending']);
        $this->get(route('transfers.show', $transfer))->assertOk()->assertDontSee('Confirm Arrival');
        $this->grant(['transfers.receive'], $destination, $reviewer);
        $this->get(route('transfers.show', $transfer))->assertOk()->assertSee('Confirm Arrival');
    }

    public function test_global_account_controls_require_national_permissions_and_role_assignment_authority(): void
    {
        $facility = Facility::factory()->create();
        $scoped = $this->grant(['users.manage', 'roles.assign'], $facility);
        $this->actingAs($scoped)->get(route('dashboard'))->assertOk()->assertDontSee('User Management')->assertDontSee('href="'.route('settings.index').'"', false);
        $this->get(route('users.index'))->assertForbidden();
        $manager = $this->grant(['users.manage']);
        $this->actingAs($manager)->get(route('users.index'))->assertOk()->assertDontSee('Provision Account')->assertSee('Roles and Permissions');
        $this->get(route('users.show', $scoped))->assertOk()->assertSee('Change Status')->assertDontSee('Assign Role');
        $this->grant(['roles.assign'], user: $manager);
        $this->get(route('users.index'))->assertOk()->assertSee('Provision Account');
        $this->get(route('users.show', $scoped))->assertOk()->assertSee('Assign Role');
    }

    public function test_admission_only_user_keeps_the_permitted_entry_point_without_an_inmate_directory_breadcrumb_link(): void
    {
        $facility = Facility::factory()->create(['status' => 'Active']);
        $officer = $this->grant(['inmates.admit'], $facility);
        $this->actingAs($officer)->get(route('dashboard'))->assertOk()->assertSee('New Admission')->assertDontSee('Active Inmates');
        $this->get(route('inmates.create'))->assertOk()->assertDontSee('href="'.route('inmates.index').'"', false);
    }

    public function test_administrators_keep_all_authorized_entry_points(): void
    {
        $admin = $this->grant([], roleSlug: 'system_admin');
        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        foreach (['inmates.create', 'transfers.index', 'personnel.index', 'facilities.index', 'reports.index', 'audit.index', 'users.index', 'settings.index'] as $route) {
            $response->assertSee('href="'.route($route).'"', false);
        }
    }

    public function test_audit_only_users_are_not_offered_report_links_without_report_read_permission(): void
    {
        $facility = Facility::factory()->create();
        $auditor = $this->grant(['audit.view'], $facility);
        $event = app(AuditService::class)->log('report_viewed', 'Success', 'Report', 'population', facilityId: $facility->id, actorId: $auditor->id);
        $this->actingAs($auditor)->get(route('audit.show', $event))->assertOk()->assertDontSee('Open report');
        $this->grant(['reports.view'], $facility, $auditor);
        $this->get(route('audit.show', $event))->assertOk()->assertSee('Open report');
    }

    public function test_released_inmate_actions_use_the_last_recorded_facility_after_a_transfer(): void
    {
        $origin = Facility::factory()->create();
        $destination = Facility::factory()->create();
        $inmate = $this->inmate($origin);
        $episode = $inmate->admissions()->firstOrFail();
        $episode->locations()->update(['entered_at' => now()->subDays(2), 'left_at' => now()->subDay()]);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $destination->id, 'entered_at' => now()->subDay(), 'left_at' => now()]);
        $episode->update(['state' => 'Discharged', 'closed_at' => now()]);
        $user = $this->grant(['inmates.read']);
        $this->grant(['inmates.edit'], $origin, $user);
        $this->actingAs($user)->get(route('inmates.index'))->assertOk()
            ->assertViewHas('inmates', fn (array $records): bool => $records[0]['facility_id'] === $destination->id && $records[0]['can_edit'] === false);
        $this->get(route('inmates.show', $inmate))->assertOk()->assertDontSee('Edit Details');
        $this->grant(['inmates.edit'], $destination, $user);
        $this->get(route('inmates.show', $inmate))->assertOk()->assertSee('Edit Details');
        $this->get(route('inmates.edit', $inmate))->assertOk();
    }
}
