<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Tests\B1DatabaseTestCase;

class InmateExportTest extends B1DatabaseTestCase
{
    /** @param list<string> $permissions */
    private function officer(array $permissions, ?Facility $facility = null, ?User $user = null): User
    {
        $user ??= User::factory()->create(['status' => 'Active']);
        $role = Role::factory()->create();
        foreach ($permissions as $permission) {
            $role->permissions()->attach(Permission::firstOrCreate(['slug' => $permission], ['name' => $permission, 'module' => 'Reports']));
        }
        $scope = AccessScope::firstOrCreate(['kind' => $facility ? 'Facility' : 'National', 'facility_id' => $facility?->id], ['scope_key' => $facility ? 'EXPORT-FAC-'.$facility->id : 'EXPORT-NATIONAL']);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $user;
    }

    private function inmate(Facility $facility, string $name, string $gender = 'Male', string $classification = 'Remand'): Inmate
    {
        $inmate = Inmate::factory()->create(['first_name' => $name, 'last_name' => 'Ssemakula', 'gender' => $gender, 'id_number' => 'PRIVATE-ID-12345', 'kin_contact' => 'PRIVATE-CONTACT-12345']);
        $episode = AdmissionEpisode::factory()->create(['inmate_id' => $inmate->id, 'admitting_facility_id' => $facility->id, 'classification' => $classification]);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);

        return $inmate;
    }

    public function test_export_requires_authentication_read_and_export_permissions_and_enabled_configuration(): void
    {
        $this->get(route('inmates.export'))->assertRedirect(route('login'));
        $readOnly = $this->officer(['inmates.read']);
        $this->actingAs($readOnly)->get(route('inmates.export'))->assertForbidden();
        $this->get(route('inmates.index'))->assertOk()->assertDontSee('exportRecords()"', false);
        $exportOnly = $this->officer(['reports.export']);
        $this->actingAs($exportOnly)->get(route('inmates.export'))->assertForbidden();
        $authorized = $this->officer(['inmates.read', 'reports.export']);
        config(['reporting.csv_exports_enabled' => false]);
        $this->actingAs($authorized)->get(route('inmates.export'))->assertStatus(409);
    }

    public function test_export_intersects_permission_scopes_and_excludes_archived_records_and_private_fields(): void
    {
        $own = Facility::factory()->create();
        $other = Facility::factory()->create();
        $this->inmate($own, 'Robert');
        $this->inmate($other, 'Geoffrey');
        $this->inmate($own, 'Archived')->delete();
        $user = $this->officer(['inmates.read']);
        $this->officer(['reports.export'], $own, $user);
        $response = $this->actingAs($user)->get(route('inmates.export'))->assertOk()->assertDownload()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Robert Ssemakula', $csv);
        foreach (['Geoffrey', 'Archived', 'PRIVATE-ID', 'PRIVATE-CONTACT', 'inmate-photos/'] as $private) {
            $this->assertStringNotContainsString($private, $csv);
        }
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertDatabaseHas('audit_events', ['action' => 'inmate_directory_exported', 'actor_id' => $user->id]);
        $disjoint = $this->officer(['inmates.read'], $other);
        $this->officer(['reports.export'], $own, $disjoint);
        $csv = $this->actingAs($disjoint)->get(route('inmates.export'))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Robert', $csv);
        $this->assertStringNotContainsString('Geoffrey', $csv);
    }

    public function test_export_uses_current_filters_and_sort_across_all_pages_and_quotes_formula_cells(): void
    {
        $facility = Facility::factory()->create(['name' => 'Luzira Upper Prison']);
        $other = Facility::factory()->create();
        $first = $this->inmate($facility, 'Robert');
        $first->update(['prisoner_number' => '=FORMULA-123']);
        $this->inmate($facility, 'Ronald');
        $this->inmate($facility, 'Sarah', 'Female');
        $this->inmate($facility, 'Roger', 'Male', 'Convicted');
        $this->inmate($other, 'Rodney');
        $user = $this->officer(['inmates.read', 'reports.export']);
        $response = $this->actingAs($user)->get(route('inmates.export', [
            'search' => 'ro', 'facility' => $facility->name, 'classification' => 'Remand',
            'status' => 'Remanded', 'gender' => 'Male', 'sort' => 'full_name', 'direction' => 'desc', 'page' => 2, 'perPage' => 1,
        ]))->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=FORMULA-123", $csv);
        $this->assertLessThan(strpos($csv, 'Robert'), strpos($csv, 'Ronald'));
        foreach (['Sarah', 'Roger', 'Rodney'] as $excluded) {
            $this->assertStringNotContainsString($excluded, $csv);
        }
        $this->get(route('inmates.export', ['sort' => 'password', 'direction' => 'SQL']))->assertSessionHasErrors(['sort', 'direction']);
    }

    public function test_all_six_report_pages_expose_working_downloads_with_the_selected_filters(): void
    {
        $facility = Facility::factory()->create();
        $user = $this->officer(['reports.view', 'reports.export'], $facility);
        $this->actingAs($user);
        foreach (['population', 'movement', 'transfers', 'visitation', 'personnel', 'occupancy'] as $report) {
            $filters = ['from' => '2026-11-01', 'to' => '2026-11-17', 'facility_id' => $facility->id];
            $this->get(route('reports.'.$report, $filters))->assertOk()->assertSee('Download CSV')->assertSee(route('reports.export', ['report' => $report] + $filters));
            $response = $this->get(route('reports.export', ['report' => $report] + $filters))->assertOk()->assertDownload();
            $csv = $response->streamedContent();
            $this->assertStringContainsString('2026-11-01', $csv);
            $this->assertStringContainsString('2026-11-17', $csv);
        }
    }
}
