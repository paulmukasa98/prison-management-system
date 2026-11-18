<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AuditEvent;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use Carbon\CarbonImmutable;
use Database\Seeders\CanonicalDemoSeeder;
use Tests\B1DatabaseTestCase;

class AdminPreviewTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalDemoSeeder::class);
        $admin = User::factory()->create(['status' => 'Active']);
        UserRoleAssignment::factory()->create([
            'user_id' => $admin->id, 'role_id' => Role::where('slug', 'system_admin')->firstOrFail()->id,
            'access_scope_id' => AccessScope::where('kind', 'National')->firstOrFail()->id,
        ]);
        $this->actingAs($admin);
    }

    public function test_user_directory_lists_database_accounts(): void
    {
        $user = User::firstOrFail();
        $this->get(route('users.index'))->assertOk()->assertSee($user->email)->assertDontSee('Phase 7');
    }

    public function test_account_detail_shows_linked_personnel_and_never_credentials(): void
    {
        $user = User::whereNotNull('personnel_id')->firstOrFail();
        $this->get(route('users.show', $user))->assertOk()->assertSee('Linked Personnel Record')->assertSee($user->email)->assertDontSee($user->getRawOriginal('password'));
    }

    public function test_missing_account_is_not_found(): void
    {
        $this->get(route('users.show', 999999))->assertNotFound();
    }

    public function test_provisioning_form_excludes_personnel_who_already_have_accounts(): void
    {
        $this->get(route('users.create'))->assertOk()->assertSee('Provision Account')
            ->assertViewHas('candidates', fn ($candidates): bool => $candidates->every(fn ($person): bool => ! $person->account()->exists()));
    }

    public function test_status_and_role_forms_submit_to_authorized_endpoints(): void
    {
        $user = User::where('id', '!=', auth()->id())->firstOrFail();
        $this->get(route('users.status', $user))->assertOk()->assertSee(route('users.status.update', $user));
        $this->get(route('users.assign-role', $user))->assertOk()->assertSee(route('users.role.update', $user));
    }

    public function test_unknown_accounts_on_status_and_role_forms_are_not_found(): void
    {
        $this->get(route('users.status', 999999))->assertNotFound();
        $this->get(route('users.assign-role', 999999))->assertNotFound();
    }

    public function test_roles_matrix_renders_the_database_catalog(): void
    {
        $this->get(route('users.roles.index'))->assertOk()->assertSee('visits.authorize')->assertSee('System Administrator');
    }

    public function test_audit_directory_and_detail_render_recorded_events(): void
    {
        $event = AuditEvent::firstOrFail();
        $this->get(route('audit.index'))->assertOk()->assertSee($event->audit_ref);
        $this->get(route('audit.show', $event))->assertOk()->assertSee($event->correlation_id);
    }

    public function test_missing_audit_event_is_not_found(): void
    {
        $this->get(route('audit.show', 999999))->assertNotFound();
    }

    public function test_settings_page_shows_enforced_permission_catalog(): void
    {
        $this->get(route('settings.index'))->assertOk()->assertSee('Permission Catalog')->assertSee('transfers.authorize');
    }

    public function test_every_report_screen_returns_database_results(): void
    {
        foreach (['index', 'population', 'movement', 'transfers', 'visitation', 'personnel', 'occupancy'] as $report) {
            $this->get(route('reports.'.$report))->assertOk();
        }
    }

    public function test_print_reports_are_classified_and_have_export_audit_references(): void
    {
        foreach (['population', 'movement', 'transfers', 'visitation', 'personnel', 'occupancy'] as $report) {
            $this->get(route('reports.print', ['report' => $report]))->assertOk()->assertSee('Restricted')->assertSee('AUD-');
        }
    }

    public function test_invalid_filters_are_rejected_without_falling_back_to_national_scope(): void
    {
        $this->get(route('reports.population', ['facility_id' => 999999]))->assertNotFound();
        $this->get(route('reports.population', ['from' => 'bogus']))->assertSessionHasErrors('from');
        $this->get(route('reports.population', ['to' => ['2026']]))->assertSessionHasErrors('to');
    }

    public function test_population_age_bands_use_the_snapshot_date(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-20 09:00:00', 'UTC'));
        $response = $this->get(route('reports.population', ['from' => '2024-01-01', 'to' => '2024-12-31']))->assertOk()->assertSee('Age band counts');
        $data = $response->viewData('reportData');
        $this->assertSame($data['summary']['In custody at snapshot'], array_sum($data['ageBands']));
        $this->travelTo(CarbonImmutable::parse('2036-11-20 09:00:00', 'UTC'));
        $this->get(route('reports.population', ['from' => '2024-01-01', 'to' => '2024-12-31']))->assertOk()
            ->assertViewHas('reportData', fn (array $next): bool => $next['ageBands'] === $data['ageBands']);
    }

    public function test_staffing_ratio_is_scoped_to_the_selected_facility(): void
    {
        $facility = Facility::where('name', 'Kigo Prison')->firstOrFail();
        $response = $this->get(route('reports.personnel', ['facility_id' => $facility->id]))->assertOk();
        $data = $response->viewData('reportData');
        $this->assertArrayHasKey('Prisoners per posted officer', $data['summary']);
        foreach ($data['rows'] as $row) {
            $this->assertSame($facility->name, $row[0]);
        }
    }

    public function test_movement_series_reconciles_with_its_scoped_summary(): void
    {
        $facility = Facility::where('name', 'Kigo Prison')->firstOrFail();
        $data = $this->get(route('reports.movement', ['from' => '2024-01-01', 'to' => '2024-12-31', 'facility_id' => $facility->id]))->assertOk()->viewData('reportData');
        $this->assertSame($data['summary']['Admissions'], array_sum(array_column($data['rows'], 1)));
        $this->assertSame($data['summary']['Discharges'], array_sum(array_column($data['rows'], 2)));
    }

    public function test_role_form_shows_current_database_permissions(): void
    {
        $user = User::whereHas('activeRoleAssignments.role', fn ($query) => $query->where('slug', 'facility_officer_in_charge'))->firstOrFail();
        $this->get(route('users.assign-role', $user))->assertOk()->assertSee('Current Permissions')->assertSee('Authorise discharge');
    }

    public function test_status_choices_preserve_values_and_exclude_current_state(): void
    {
        $user = User::where('status', 'Active')->where('id', '!=', auth()->id())->firstOrFail();
        $this->get(route('users.status', $user))->assertOk()->assertSee('value="Suspended"', false)->assertSee('value="Deactivated"', false)->assertDontSee('value="Active"', false);
    }

    public function test_report_audit_event_links_to_the_named_report(): void
    {
        $event = app(AuditService::class)->log('report_exported', 'Success', 'Report', 'population');
        $this->get(route('audit.show', $event))->assertOk()->assertSee(route('reports.population'))->assertDontSee('Internal id #');
    }
}
