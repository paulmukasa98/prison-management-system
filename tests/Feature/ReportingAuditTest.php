<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\FacilityCapacity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Services\AuditService;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\B1DatabaseTestCase;

class ReportingAuditTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-20 09:00:00', 'UTC'));
    }

    /** @param list<string> $permissions */
    private function officer(array $permissions, ?Facility $facility = null, ?User $user = null): User
    {
        $user ??= User::factory()->create(['status' => 'Active']);
        $role = Role::factory()->create();
        foreach ($permissions as $slug) {
            $role->permissions()->attach(Permission::firstOrCreate(['slug' => $slug], ['name' => $slug, 'module' => 'Reporting']));
        }
        $scope = $facility
            ? AccessScope::firstOrCreate(['kind' => 'Facility', 'facility_id' => $facility->id], ['scope_key' => 'REPORT-FAC-'.$facility->id])
            : AccessScope::firstOrCreate(['kind' => 'National'], ['scope_key' => 'REPORT-NATIONAL']);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $user;
    }

    private function episode(Facility $facility): AdmissionEpisode
    {
        $episode = AdmissionEpisode::factory()->create(['admitting_facility_id' => $facility->id, 'admitted_at' => '2026-11-01 08:00:00', 'state' => 'Active', 'classification' => 'Remand']);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id, 'entered_at' => '2026-11-01 08:00:00']);

        return $episode;
    }

    public function test_population_uses_historical_custody_location_and_scope_at_snapshot(): void
    {
        $origin = Facility::factory()->create(['name' => 'Fictional Origin']);
        $destination = Facility::factory()->create(['name' => 'Fictional Destination']);
        $episode = $this->episode($origin);
        $episode->locations()->update(['left_at' => '2026-11-10 08:00:00']);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $destination->id, 'entered_at' => '2026-11-10 08:00:00']);
        $user = $this->officer(['reports.view'], $origin);
        $this->actingAs($user)->get(route('reports.population', ['from' => '2026-11-01', 'to' => '2026-11-05']))
            ->assertOk()->assertViewHas('reportData', fn (array $data): bool => $data['summary']['In custody at snapshot'] === 1 && $data['rows'][0][0] === 'Fictional Origin')
            ->assertDontSee('Fictional Destination');
        $this->get(route('reports.population', ['from' => '2026-11-01', 'to' => '2026-11-12']))
            ->assertOk()->assertViewHas('reportData', fn (array $data): bool => $data['summary']['In custody at snapshot'] === 0);
    }

    public function test_transit_remains_in_custody_totals_without_inflating_facility_occupancy(): void
    {
        $origin = Facility::factory()->create();
        $destination = Facility::factory()->create();
        $episode = $this->episode($origin);
        $episode->locations()->update(['left_at' => '2026-11-10 08:00:00']);
        Transfer::factory()->create([
            'admission_episode_id' => $episode->id, 'origin_facility_id' => $origin->id,
            'destination_facility_id' => $destination->id, 'state' => 'In Transit',
            'dispatched_at' => '2026-11-10 08:00:00', 'received_at' => null,
        ]);
        $user = $this->officer(['reports.view', 'inmates.read'], $origin);
        $this->actingAs($user)->get(route('reports.population'))->assertOk()
            ->assertViewHas('reportData', fn (array $data): bool => $data['summary']['In custody at snapshot'] === 1 && $data['summary']['In transit'] === 1);
        $this->get(route('reports.occupancy'))->assertOk()
            ->assertViewHas('reportData', fn (array $data): bool => $data['summary']['Population in facilities'] === 0);
        $this->get(route('dashboard'))->assertOk()
            ->assertViewHas('periodsJson', fn (string $json): bool => json_decode($json, true)['this_month']['active_inmates'] === 1);
        $other = $this->officer(['reports.view'], $destination);
        $this->actingAs($other)->get(route('reports.population'))->assertOk()
            ->assertViewHas('reportData', fn (array $data): bool => $data['summary']['In custody at snapshot'] === 0);
    }

    public function test_report_authorization_and_invalid_facility_never_broaden_scope(): void
    {
        $own = Facility::factory()->create();
        $other = Facility::factory()->create();
        $this->episode($other);
        $this->get(route('reports.index'))->assertRedirect(route('login'));
        $user = $this->officer(['reports.view'], $own);
        $this->actingAs($user)->get(route('reports.population', ['facility_id' => $other->id]))->assertNotFound();
        $this->get(route('reports.population', ['facility_id' => 999999]))->assertNotFound();
        $this->get(route('reports.population', ['from' => '2026-11-20', 'to' => '2026-11-01']))->assertSessionHasErrors('to');
        $this->get(route('reports.population', ['from' => '2020-01-01', 'to' => '2026-11-01']))->assertSessionHasErrors('to');
        $this->actingAs(User::factory()->create(['status' => 'Active']))->get(route('reports.index'))->assertForbidden();
    }

    public function test_occupancy_preserves_unknown_and_zero_and_ignores_future_capacity(): void
    {
        $known = Facility::factory()->create(['name' => 'Capacity Known']);
        $zero = Facility::factory()->create(['name' => 'Capacity Zero']);
        $unknown = Facility::factory()->create(['name' => 'Capacity Unknown']);
        $this->episode($known);
        FacilityCapacity::factory()->create(['facility_id' => $known->id, 'capacity' => 100, 'effective_on' => '2026-11-01']);
        FacilityCapacity::factory()->create(['facility_id' => $known->id, 'capacity' => 500, 'effective_on' => '2026-12-01']);
        FacilityCapacity::factory()->create(['facility_id' => $zero->id, 'capacity' => 0, 'effective_on' => '2026-11-01']);
        $user = $this->officer(['reports.view']);
        $this->actingAs($user)->get(route('reports.occupancy'))->assertOk()
            ->assertViewHas('reportData', fn (array $data): bool => in_array(['Capacity Known', 1, 100, '1%'], $data['rows'], true)
                && in_array(['Capacity Zero', 0, 0, 'Undefined'], $data['rows'], true)
                && in_array(['Capacity Unknown', 0, 'Unknown', 'Undefined'], $data['rows'], true));
    }

    public function test_visit_completion_counts_use_reporting_timezone_and_exclude_contact_data(): void
    {
        $facility = Facility::factory()->create();
        $episode = $this->episode($facility);
        $visit = Visit::factory()->completed()->create([
            'admission_episode_id' => $episode->id, 'facility_id' => $facility->id,
            'scheduled_start_at' => '2026-11-17 20:50:00', 'scheduled_end_at' => '2026-11-17 21:20:00',
            'checked_in_at' => '2026-11-17 20:50:00', 'checked_out_at' => '2026-11-17 21:10:00',
        ]);
        $visit->visitor->update(['phone' => 'PRIVATE-CONTACT-4567', 'id_number' => 'PRIVATE-DOC-4567']);
        $user = $this->officer(['reports.view'], $facility);
        $this->actingAs($user)->get(route('reports.visitation', ['from' => '2026-11-18', 'to' => '2026-11-18']))
            ->assertOk()->assertViewHas('reportData', fn (array $data): bool => $data['summary']['Completed in period'] === 1 && $data['summary']['Scheduled start in period'] === 0)
            ->assertDontSee('PRIVATE-CONTACT-4567')->assertDontSee('PRIVATE-DOC-4567');
    }

    public function test_csv_and_print_require_export_permission_and_export_only_permission_intersection(): void
    {
        config(['reporting.csv_exports_enabled' => true]);
        $own = Facility::factory()->create(['name' => 'Export Own Facility']);
        $other = Facility::factory()->create(['name' => 'Export Other Facility']);
        $this->episode($own);
        $this->episode($other);
        $user = $this->officer(['reports.view']);
        $this->actingAs($user)->get(route('reports.export', ['report' => 'population']))->assertForbidden();
        $this->get(route('reports.print', ['report' => 'population']))->assertForbidden();
        $this->officer(['reports.export'], $own, $user);
        $response = $this->get(route('reports.export', ['report' => 'population']))->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Export Own Facility', $csv);
        $this->assertStringNotContainsString('Export Other Facility', $csv);
        $this->assertDatabaseHas('audit_events', ['action' => 'report_exported', 'actor_id' => $user->id]);
        $this->get(route('reports.print', ['report' => 'population']))->assertOk()->assertSee('Export Own Facility')->assertDontSee('Export Other Facility');
    }

    public function test_csv_exports_can_be_disabled_and_neutralize_formula_cells_when_enabled(): void
    {
        config(['reporting.csv_exports_enabled' => false]);
        $facility = Facility::factory()->create(['name' => '=HYPERLINK("example.invalid")']);
        $this->episode($facility);
        $user = $this->officer(['reports.view', 'reports.export'], $facility);
        $this->actingAs($user)->get(route('reports.export', ['report' => 'population']))->assertStatus(409);
        config(['reporting.csv_exports_enabled' => true]);
        $csv = $this->get(route('reports.export', ['report' => 'population']))->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_audit_metadata_is_redacted_signed_and_facility_scoped(): void
    {
        $own = Facility::factory()->create();
        $other = Facility::factory()->create();
        $user = $this->officer(['audit.view'], $own);
        $audit = app(AuditService::class);
        $event = $audit->log('test_sensitive_event', 'Success', 'Visitor', 'DEMO-RESOURCE', ['id_number' => 'DO-NOT-EXPOSE', 'nested' => ['password' => 'SECRET-PASS'], 'state' => 'Requested'], facilityId: $own->id, actorId: $user->id);
        $outside = $audit->log('outside_event', 'Success', 'Visit', facilityId: $other->id);
        $this->assertSame('[REDACTED]', $event->changes['id_number']);
        $this->assertSame('[REDACTED]', $event->changes['nested']['password']);
        $this->assertTrue(hash_equals($event->integrity_hash, AuditService::signatureFor($event->fresh())));
        $this->actingAs($user)->get(route('audit.show', $event))->assertOk()->assertSee('Verified')->assertDontSee('DO-NOT-EXPOSE')->assertDontSee('SECRET-PASS');
        $this->get(route('audit.show', $outside))->assertNotFound();
        $this->get(route('audit.index'))->assertOk()->assertSee($event->audit_ref)->assertDontSee($outside->audit_ref);
        $this->assertDatabaseHas('audit_events', ['action' => 'audit_event_viewed', 'resource_reference' => $event->audit_ref]);
        $this->artisan('audit:verify')->assertSuccessful();
    }

    public function test_database_rejects_raw_audit_mutation(): void
    {
        $event = app(AuditService::class)->log('raw_mutation_test', 'Success', 'Test');
        $this->expectException(QueryException::class);
        DB::table('audit_events')->where('id', $event->id)->update(['description' => 'Tamper']);
    }

    public function test_all_report_pages_render_database_aggregates_and_dashboard_has_no_fixture_activity(): void
    {
        $facility = Facility::factory()->create();
        $this->episode($facility);
        $user = $this->officer(['reports.view', 'inmates.read', 'visitors.read', 'personnel.read'], $facility);
        $this->actingAs($user);
        foreach (['population', 'movement', 'transfers', 'visitation', 'personnel', 'occupancy'] as $report) {
            $this->get(route('reports.'.$report))->assertOk()->assertViewHas('reportData');
        }
        $this->get(route('dashboard'))->assertOk()->assertDontSee('DEMO-PN-0001')
            ->assertViewHas('periodsJson', fn (string $json): bool => json_decode($json, true)['this_month']['active_inmates'] === 1);
    }
}
