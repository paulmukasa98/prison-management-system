<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\Inmate;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\FacilityService;
use Database\Seeders\CanonicalDemoSeeder;
use Illuminate\Support\Str;
use Tests\B1DatabaseTestCase;

class PersonnelFacilityTest extends B1DatabaseTestCase
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

    protected function createTestFacility(string $name, int $capacity = 100): Facility
    {
        $code = 'FAC-TEST-'.strtoupper(Str::random(6));

        $facility = Facility::create([
            'code' => $code,
            'name' => $name,
            'type' => 'Medium Security',
            'region' => 'Central',
            'district' => 'Kampala',
            'location_description' => 'Test Location Coordinates',
            'status' => 'Active',
            'established_on' => '1980-01-01',
            'notes' => 'Test facility',
        ]);

        FacilityCapacity::create([
            'facility_id' => $facility->id,
            'capacity' => $capacity,
            'effective_on' => '2020-01-01',
            'source_reference' => 'Initial test establishment baseline',
        ]);

        return $facility;
    }

    protected function createActiveInmate(Facility $facility, string $gender = 'Male', string $classification = 'Remand'): Inmate
    {
        $ref = 'INM-TEST-'.strtoupper(Str::random(8));
        $inmate = Inmate::create([
            'identity_ref' => $ref,
            'prisoner_number' => 'PN-'.strtoupper(Str::random(6)),
            'first_name' => 'Test',
            'last_name' => 'Subject '.Str::random(4),
            'gender' => $gender,
            'date_of_birth' => '1990-05-15',
            'nationality' => 'Ugandan',
        ]);

        $episode = AdmissionEpisode::create([
            'admission_ref' => 'ADM-TEST-'.strtoupper(Str::random(8)),
            'inmate_id' => $inmate->id,
            'admitting_facility_id' => $facility->id,
            'admitted_at' => now()->subMonths(2),
            'state' => 'Open',
            'legal_basis' => 'Remand warrant',
            'classification' => $classification,
        ]);

        CustodyLocation::create([
            'location_ref' => 'LOC-TEST-'.strtoupper(Str::random(8)),
            'admission_episode_id' => $episode->id,
            'facility_id' => $facility->id,
            'entered_at' => now()->subMonths(2),
            'left_at' => null,
        ]);

        return $inmate;
    }

    public function test_system_admin_can_register_new_facility_with_initial_capacity(): void
    {
        $admin = $this->createOfficer('system_admin');
        $uniqueName = 'Soroti Medium Security Prison '.Str::random(5);

        $response = $this->actingAs($admin)->post(route('facilities.store'), [
            'name' => $uniqueName,
            'type' => 'Medium Security',
            'region' => 'Eastern',
            'district' => 'Soroti',
            'location_description' => 'Soroti Municipal Road',
            'status' => 'Active',
            'established_on' => '1975-04-12',
            'capacity' => 450,
            'notes' => 'Eastern region custody station',
        ]);

        $facility = Facility::where('name', $uniqueName)->first();
        $this->assertNotNull($facility);
        $response->assertRedirect(route('facilities.show', $facility->id));

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => $uniqueName,
            'region' => 'Eastern',
            'district' => 'Soroti',
            'status' => 'Active',
        ]);

        $this->assertDatabaseHas('facility_capacities', [
            'facility_id' => $facility->id,
            'capacity' => 450,
        ]);

        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'Facility',
            'resource_reference' => $facility->code,
            'action' => 'facility_created',
            'outcome' => 'Success',
        ]);
    }

    public function test_authorized_user_can_update_facility_details_and_capacity_via_edit(): void
    {
        $admin = $this->createOfficer('system_admin');
        $facility = $this->createTestFacility('Facility Edit Base '.Str::random(4), 300);

        $response = $this->actingAs($admin)->post(route('facilities.update', $facility->id), [
            'name' => $facility->name.' Updated',
            'type' => 'Maximum Security',
            'region' => 'Western',
            'district' => 'Mbarara',
            'location_description' => 'Updated location',
            'status' => 'Active',
            'capacity' => 380,
            'capacity_date' => now()->toDateString(),
            'capacity_reason' => 'Expansion wing added',
            'notes' => 'Updated operational notes',
        ]);

        $response->assertRedirect(route('facilities.show', $facility->id));

        $facility->refresh();
        $this->assertEquals('Maximum Security', $facility->type);
        $this->assertEquals('Western', $facility->region);
        $this->assertEquals('Mbarara', $facility->district);
        $this->assertEquals(380, $facility->currentCapacity());

        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'Facility',
            'resource_reference' => $facility->code,
            'action' => 'facility_updated',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'Facility',
            'resource_reference' => $facility->code,
            'action' => 'facility_capacity_updated',
        ]);
    }

    public function test_authorized_user_can_record_explicit_capacity_revision(): void
    {
        $admin = $this->createOfficer('system_admin');
        $facility = $this->createTestFacility('Facility Capacity Revision '.Str::random(4), 200);

        $effectiveDate = now()->addDay()->toDateString();

        $response = $this->actingAs($admin)->post(route('facilities.capacity.store', $facility->id), [
            'capacity' => 275,
            'effective_on' => $effectiveDate,
            'source_reference' => 'Ministry of Internal Affairs Gazetted Expansion Order',
            'notes' => 'Renovation completed for Block C',
        ]);

        $response->assertRedirect(route('facilities.show', $facility->id));

        $this->assertDatabaseHas('facility_capacities', [
            'facility_id' => $facility->id,
            'capacity' => 275,
            'effective_on' => $effectiveDate,
            'source_reference' => 'Ministry of Internal Affairs Gazetted Expansion Order',
        ]);
    }

    public function test_dynamic_occupancy_reflects_active_custody_locations(): void
    {
        $facility = $this->createTestFacility('Dynamic Occupancy Facility '.Str::random(4), 100);

        // 3 active inmates
        $this->createActiveInmate($facility, 'Male', 'Remand');
        $this->createActiveInmate($facility, 'Female', 'Convicted');
        $this->createActiveInmate($facility, 'Male', 'Convicted');

        // 1 inmate who left (should not be counted in active population)
        $inmateLeft = $this->createActiveInmate($facility, 'Male', 'Remand');
        $location = CustodyLocation::whereHas('admission', fn ($q) => $q->where('inmate_id', $inmateLeft->id))->first();
        $location->update(['left_at' => now()->subDay()]);

        $facilityService = app(FacilityService::class);
        $viewData = $facilityService->toViewArray($facility);

        $this->assertEquals(3, $facility->activePopulation());
        $this->assertEquals(3, $viewData['current_population']);
        $this->assertEquals(100, $viewData['capacity']);
        $this->assertEquals(3.0, $viewData['occupancy_pct']);
        $this->assertEquals(2, $viewData['male_population']);
        $this->assertEquals(1, $viewData['female_population']);
        $this->assertEquals(1, $viewData['remand_count']);
        $this->assertEquals(2, $viewData['convicted_count']);
    }

    public function test_system_admin_can_register_personnel_with_auto_generated_staff_number(): void
    {
        $admin = $this->createOfficer('system_admin');
        $uniq = Str::random(6);

        $response = $this->actingAs($admin)->post(route('personnel.store'), [
            'first_name' => 'Grace',
            'middle_name' => 'Akello',
            'last_name' => 'Okot-'.$uniq,
            'gender' => 'Female',
            'date_of_birth' => '1988-07-20',
            'nationality' => 'Ugandan',
            'rank' => 'Principal Officer I',
            'department' => 'Custodial Operations',
            'employment_status' => 'Active',
            'appointed_on' => '2012-03-01',
            'email' => 'grace.okot.'.uniqid().'@demo.ups.go.ug',
            'phone' => '+256 772 112233',
        ]);

        $personnel = Personnel::where('last_name', 'Okot-'.$uniq)->first();
        $this->assertNotNull($personnel);
        $response->assertRedirect(route('personnel.show', $personnel->id));

        $this->assertStringStartsWith('UPS-STF-', $personnel->staff_number);
        $this->assertEquals('Principal Officer I', $personnel->rank);
        $this->assertEquals('Custodial Operations', $personnel->department);
        $this->assertEquals('Active', $personnel->employment_status);

        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'Personnel',
            'resource_reference' => $personnel->staff_number,
            'action' => 'personnel_created',
        ]);
    }

    public function test_personnel_posting_lifecycle_auto_concludes_overlapping_active_assignments(): void
    {
        $admin = $this->createOfficer('system_admin');
        $facility1 = $this->createTestFacility('Posting Station A '.Str::random(4), 200);
        $facility2 = $this->createTestFacility('Posting Station B '.Str::random(4), 200);

        $personnel = Personnel::create([
            'staff_number' => 'UPS-STF-TEST-'.strtoupper(Str::random(6)),
            'first_name' => 'Patrick',
            'last_name' => 'Mukasa',
            'gender' => 'Male',
            'rank' => 'Senior Superintendent of Prisons',
            'department' => 'Security & Operations',
            'employment_status' => 'Active',
        ]);

        // First assignment starting 2026-01-01
        $assignment1 = PersonnelAssignment::create([
            'assignment_ref' => 'ASN-TEST-'.strtoupper(Str::random(8)),
            'personnel_id' => $personnel->id,
            'facility_id' => $facility1->id,
            'duty_title' => 'Officer in Charge',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'status' => 'Active',
            'recorded_by' => $admin->id,
        ]);

        $this->assertEquals($facility1->id, $personnel->currentFacilityId());

        // Second assignment starting 2026-06-01 via POST
        $response = $this->actingAs($admin)->post(route('personnel.assignment.store'), [
            'personnel_id' => $personnel->id,
            'facility_id' => $facility2->id,
            'duty_title' => 'Regional Commander',
            'start_date' => '2026-06-01',
            'reason' => 'Service Reassignment',
        ]);

        $response->assertRedirect(route('personnel.assignments'));

        // Verify assignment 1 was auto-concluded
        $assignment1->refresh();
        $this->assertEquals('Completed', $assignment1->status);
        $this->assertEquals('2026-05-31', $assignment1->end_date->format('Y-m-d'));

        // Verify assignment 2 is active at facility 2
        $assignment2 = PersonnelAssignment::where('personnel_id', $personnel->id)
            ->where('facility_id', $facility2->id)
            ->first();

        $this->assertNotNull($assignment2);
        $this->assertEquals('Active', $assignment2->status);
        $this->assertEquals('Regional Commander', $assignment2->duty_title);
        $this->assertNull($assignment2->end_date);

        // Verify personnel model reflects facility 2
        $this->assertEquals($facility2->id, $personnel->currentFacilityId());

        // Verify audit event
        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'Personnel',
            'action' => 'personnel_assigned',
        ]);
    }

    public function test_authorized_user_can_conclude_active_personnel_assignment(): void
    {
        $admin = $this->createOfficer('system_admin');
        $facility = $this->createTestFacility('Assignment End Facility '.Str::random(4), 150);

        $personnel = Personnel::create([
            'staff_number' => 'UPS-STF-TEST-'.strtoupper(Str::random(6)),
            'first_name' => 'Denis',
            'last_name' => 'Wandera',
            'rank' => 'Corporal',
            'department' => 'Administration',
            'employment_status' => 'Active',
        ]);

        $assignment = PersonnelAssignment::create([
            'assignment_ref' => 'ASN-TEST-'.strtoupper(Str::random(8)),
            'personnel_id' => $personnel->id,
            'facility_id' => $facility->id,
            'duty_title' => 'Gate Duty Officer',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'status' => 'Active',
            'recorded_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('personnel.assignment.end', $assignment->id), [
            'end_date' => '2026-07-15',
        ]);

        $response->assertRedirect(route('personnel.assignments'));

        $assignment->refresh();
        $this->assertEquals('Completed', $assignment->status);
        $this->assertEquals('2026-07-15', $assignment->end_date->format('Y-m-d'));

        $this->assertDatabaseHas('audit_events', [
            'resource_type' => 'Personnel',
            'action' => 'personnel_assignment_ended',
        ]);
    }

    public function test_facility_scoping_prevents_unauthorized_personnel_and_facility_modification(): void
    {
        $facility1 = $this->createTestFacility('Scoping Station 1 '.Str::random(4), 100);
        $facility2 = $this->createTestFacility('Scoping Station 2 '.Str::random(4), 100);

        // Officer scoped strictly to facility 1
        $officer = $this->createOfficer('facility_officer_in_charge', $facility1->id);

        // Attempt to update Facility 2
        $response = $this->actingAs($officer)->post(route('facilities.update', $facility2->id), [
            'name' => 'Unauthorized Rename Attempt',
            'type' => 'Maximum Security',
            'region' => 'Central',
            'district' => 'Kampala',
            'status' => 'Active',
        ]);

        $response->assertForbidden();

        // Attempt to record capacity for Facility 2
        $capacityResponse = $this->actingAs($officer)->post(route('facilities.capacity.store', $facility2->id), [
            'capacity' => 999,
            'effective_on' => now()->toDateString(),
        ]);

        $capacityResponse->assertForbidden();

        // Personnel at Facility 2
        $personnel = Personnel::create([
            'staff_number' => 'UPS-STF-TEST-'.strtoupper(Str::random(6)),
            'first_name' => 'Cross',
            'last_name' => 'Officer',
            'rank' => 'Wardress',
            'department' => 'Rehabilitation',
            'employment_status' => 'Active',
        ]);

        // Attempt to assign staff member into Facility 2
        $assignmentResponse = $this->actingAs($officer)->post(route('personnel.assignment.store'), [
            'personnel_id' => $personnel->id,
            'facility_id' => $facility2->id,
            'duty_title' => 'Shift Supervisor',
            'start_date' => now()->toDateString(),
        ]);

        $assignmentResponse->assertForbidden();
    }

    public function test_all_personnel_and_facility_views_render_successfully_with_database_records(): void
    {
        $admin = $this->createOfficer('system_admin');
        $facility = $this->createTestFacility('View Render Station '.Str::random(4), 150);

        $personnel = Personnel::create([
            'staff_number' => 'UPS-STF-TEST-'.strtoupper(Str::random(6)),
            'first_name' => 'Moses',
            'last_name' => 'Kasule',
            'rank' => 'Chief Warder III',
            'department' => 'Security',
            'employment_status' => 'Active',
        ]);

        PersonnelAssignment::create([
            'assignment_ref' => 'ASN-TEST-'.strtoupper(Str::random(8)),
            'personnel_id' => $personnel->id,
            'facility_id' => $facility->id,
            'duty_title' => 'Main Gate Security',
            'start_date' => '2026-02-01',
            'status' => 'Active',
            'recorded_by' => $admin->id,
        ]);

        // Facilities routes
        $this->actingAs($admin)->get(route('facilities.index'))->assertOk();
        $this->actingAs($admin)->get(route('facilities.create'))->assertOk();
        $this->actingAs($admin)->get(route('facilities.show', $facility->id))->assertOk();
        $this->actingAs($admin)->get(route('facilities.edit', $facility->id))->assertOk();
        $this->actingAs($admin)->get(route('facilities.capacity'))->assertOk();
        $this->actingAs($admin)->get(route('facilities.personnel', $facility->id))->assertOk();

        // Personnel routes
        $this->actingAs($admin)->get(route('personnel.index'))->assertOk();
        $this->actingAs($admin)->get(route('personnel.create'))->assertOk();
        $this->actingAs($admin)->get(route('personnel.show', $personnel->id))->assertOk();
        $this->actingAs($admin)->get(route('personnel.edit', $personnel->id))->assertOk();
        $this->actingAs($admin)->get(route('personnel.assignments'))->assertOk();
        $this->actingAs($admin)->get(route('personnel.assignment.create'))->assertOk();
        $this->actingAs($admin)->get(route('personnel.assignment.history', $personnel->id))->assertOk();
    }
}
