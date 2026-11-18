<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\AuditEvent;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Personnel;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\TransferEvent;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\AuditService;
use App\Services\PersonnelService;
use App\Services\ReportingService;
use Carbon\CarbonImmutable;
use Database\Seeders\B1DemoSeeder;
use Database\Seeders\CanonicalDemoSeeder;
use RuntimeException;
use Tests\B1DatabaseTestCase;

class CleanupSeedRecordsTest extends B1DatabaseTestCase
{
    private function administrator(): User
    {
        $admin = User::factory()->create(['status' => 'Active', 'name' => 'System Administrator']);
        $role = Role::where('slug', 'system_admin')->first() ?? Role::factory()->create(['slug' => 'system_admin', 'name' => 'System Administrator']);
        $scope = AccessScope::where('kind', 'National')->first() ?? AccessScope::factory()->create();
        UserRoleAssignment::factory()->create(['user_id' => $admin->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $admin;
    }

    public function test_preview_keeps_all_records_unchanged(): void
    {
        $person = Inmate::factory()->create();
        $before = $person->refresh()->getAttributes();
        $this->artisan('ups:demo-cleanup')->assertSuccessful();
        $this->assertSame($before, $person->fresh()->getAttributes());
        $this->assertSame(0, AuditEvent::where('action', 'seed_fixtures_archived')->count());
    }

    public function test_cleanup_archives_placeholder_graphs_and_keeps_robert_and_immutable_events(): void
    {
        $this->seed(B1DemoSeeder::class);
        $this->seed(CanonicalDemoSeeder::class);
        $robert = Inmate::where('first_name', 'Robert')->where('last_name', 'Ssemakula')->firstOrFail();
        $before = $robert->getAttributes();
        $events = TransferEvent::orderBy('id')->get()->map->getAttributes()->all();
        $audits = AuditEvent::orderBy('id')->get()->map->getAttributes()->all();
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $this->assertSame($before, $robert->fresh()->getAttributes());
        $this->assertSame(3, Inmate::onlyTrashed()->count());
        $this->assertSame(2, Personnel::onlyTrashed()->count());
        $this->assertSame(2, Visitor::onlyTrashed()->count());
        $this->assertSame(2, Facility::onlyTrashed()->count());
        $this->assertSame(0, AdmissionEpisode::whereHas('inmate', fn ($query) => $query->where('first_name', 'Fictional'))->count());
        $this->assertSame(0, Transfer::where('transfer_ref', 'DEMO-B1-TRF-001')->count());
        $this->assertSame(0, Visit::where('visit_ref', 'like', 'DEMO-B1-%')->count());
        $this->assertSame($events, TransferEvent::whereIn('id', array_column($events, 'id'))->orderBy('id')->get()->map->getAttributes()->all());
        $this->assertSame($audits, AuditEvent::whereIn('id', array_column($audits, 'id'))->orderBy('id')->get()->map->getAttributes()->all());
        $this->assertTrue(TransferEvent::where('event_ref', 'DEMO-B1-TE-001')->firstOrFail()->transfer->trashed());
        $this->assertDatabaseHas('audit_events', ['action' => 'seed_fixtures_archived']);
        $this->seed(CanonicalDemoSeeder::class);
        $this->assertSame(3, Inmate::onlyTrashed()->count());
        $this->assertNotNull(Inmate::find($robert->id));
    }

    public function test_realistic_demo_references_and_names_are_not_classified_as_placeholders(): void
    {
        $robert = Inmate::factory()->create(['first_name' => 'Robert', 'last_name' => 'Ssemakula', 'identity_ref' => 'DEMO-REALISTIC-ROBERT', 'notes' => 'Fictional demonstration provenance']);
        $other = Inmate::factory()->create(['first_name' => 'Testimony', 'last_name' => 'Ogwang']);
        $generated = Inmate::factory()->create(['first_name' => 'Kato_xA123', 'last_name' => 'Muwanga']);
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $this->assertNotNull(Inmate::find($robert->id));
        $this->assertNotNull(Inmate::find($other->id));
        $this->assertSoftDeleted($generated);
    }

    public function test_facility_cleanup_preserves_custody_assignments_of_retained_inmates(): void
    {
        $facility = Facility::factory()->create(['name' => 'Fictional North Development Facility']);
        $inmate = Inmate::factory()->create(['first_name' => 'Robert', 'last_name' => 'Mugabe']);
        $episode = AdmissionEpisode::factory()->create(['inmate_id' => $inmate->id, 'admitting_facility_id' => $facility->id]);
        $location = CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id]);
        $before = $location->refresh()->getAttributes();
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $this->assertNotNull(Facility::find($facility->id));
        $this->assertSame($before, $location->fresh()->getAttributes());
        $this->assertSame($facility->id, $inmate->fresh()->currentFacilityId());
    }

    public function test_explicit_linked_facility_cleanup_removes_choices_without_fabricating_custody_movements(): void
    {
        $admin = $this->administrator();
        $facility = Facility::factory()->create(['name' => 'Fictional North Development Facility']);
        $inmate = Inmate::factory()->create(['first_name' => 'Robert', 'last_name' => 'Mugabe']);
        $episode = AdmissionEpisode::factory()->create(['inmate_id' => $inmate->id, 'admitting_facility_id' => $facility->id, 'admitted_at' => now()->subDay(), 'state' => 'Active']);
        $location = CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id, 'entered_at' => now()->subDay()]);
        $before = $location->refresh()->getAttributes();
        $this->artisan('ups:demo-cleanup', ['--include-linked-facilities' => true])->assertSuccessful();
        $this->assertNotNull(Facility::find($facility->id));
        $this->artisan('ups:demo-cleanup', ['--force' => true, '--include-linked-facilities' => true])->assertSuccessful();
        $this->assertSoftDeleted($facility);
        $this->assertSame($before, $location->fresh()->getAttributes());
        $this->assertSame($facility->id, $inmate->fresh()->currentFacilityId());
        $this->assertSame('Active', $episode->fresh()->state);
        $this->assertSame('Fictional North Development Facility', $episode->fresh()->admittingFacility->name);
        $this->actingAs($admin)->get(route('facilities.index'))->assertOk()->assertDontSee($facility->name);
        $this->get(route('inmates.create'))->assertOk()->assertDontSee($facility->name);
        $this->get(route('inmates.index'))->assertOk()->assertSee('Robert')->assertSee('Facility assignment requires correction')->assertDontSee($facility->name);
        $reporting = app(ReportingService::class);
        $report = $reporting->report('population', $admin, CarbonImmutable::now()->subMonth(), CarbonImmutable::now());
        $this->assertSame(1, $report['summary']['In custody at snapshot']);
        $this->assertSame('Unconfirmed facility', $report['rows'][0][0]);
        $occupancy = $reporting->report('occupancy', $admin, CarbonImmutable::now()->subMonth(), CarbonImmutable::now());
        $this->assertContains(['Unconfirmed facility', 1, 'Unknown', 'Undefined'], $occupancy['rows']);
    }

    public function test_reports_and_facility_counts_exclude_archived_fixtures(): void
    {
        $admin = $this->administrator();
        $facility = Facility::factory()->create(['name' => 'Luzira Upper Prison', 'status' => 'Active']);
        foreach (['Robert', 'Fictional'] as $firstName) {
            $inmate = Inmate::factory()->create(['first_name' => $firstName, 'last_name' => 'Ssemakula']);
            $episode = AdmissionEpisode::factory()->create(['inmate_id' => $inmate->id, 'admitting_facility_id' => $facility->id, 'admitted_at' => now()->subDay()]);
            CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $facility->id, 'entered_at' => now()->subDay()]);
        }
        $this->assertSame(2, $facility->activePopulation());
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $this->assertSame(1, $facility->fresh()->activePopulation());
        $report = app(ReportingService::class)->report('population', $admin, CarbonImmutable::now()->subMonth(), CarbonImmutable::now());
        $this->assertSame(1, $report['summary']['In custody at snapshot']);
        $this->actingAs($admin)->get(route('inmates.index'))->assertOk()->assertSee('Robert')
            ->assertViewHas('inmates', fn (array $records): bool => count($records) === 1 && $records[0]['first_name'] === 'Robert');
    }

    public function test_cleanup_is_idempotent_and_rolls_back_when_auditing_fails(): void
    {
        $inmate = Inmate::factory()->create();
        $this->mock(AuditService::class)->shouldReceive('log')->once()->andThrow(new RuntimeException('Fictional cleanup audit failure'));
        try {
            $this->artisan('ups:demo-cleanup', ['--force' => true])->run();
            $this->fail('Cleanup must not succeed without its audit event.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fictional cleanup audit failure', $exception->getMessage());
        }
        $this->assertNotNull(Inmate::find($inmate->id));
        $this->app->forgetInstance(AuditService::class);
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $this->assertSame(1, Inmate::onlyTrashed()->count());
        $this->assertSame(1, AuditEvent::where('action', 'seed_fixtures_archived')->count());
    }

    public function test_production_cleanup_and_main_database_test_seeding_are_refused(): void
    {
        $person = Inmate::factory()->create();
        $this->app->instance('env', 'production');
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertFailed();
        $this->assertNotNull(Inmate::find($person->id));
        $this->app->instance('env', 'local');
        config(['database.default' => 'mysql']);
        try {
            (new B1DemoSeeder)->run();
            $this->fail('Low-level fixtures must never seed the main database.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('explicitly isolated', $exception->getMessage());
        } finally {
            config(['database.default' => 'b1_test']);
        }
    }

    public function test_new_staff_numbers_do_not_reuse_archived_staff_sequences(): void
    {
        $admin = $this->administrator();
        $person = Personnel::factory()->create(['first_name' => 'Fictional', 'last_name' => 'Officer']);
        $person->update(['staff_number' => 'UPS-STF-'.date('Y').'-'.str_pad((string) $person->id, 5, '0', STR_PAD_LEFT)]);
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $created = app(PersonnelService::class)->create(['first_name' => 'Grace', 'last_name' => 'Nabirye', 'rank' => 'Officer', 'department' => 'Administration', 'gender' => 'Female'], $admin);
        $this->assertNotSame($person->staff_number, $created->staff_number);
        $this->assertNotNull(Personnel::find($created->id));
    }

    public function test_archived_facilities_cannot_be_reused_by_old_admission_forms(): void
    {
        $admin = $this->administrator();
        $facility = Facility::factory()->create(['name' => 'Fictional Removed Facility']);
        $this->artisan('ups:demo-cleanup', ['--force' => true])->assertSuccessful();
        $payload = ['first_name' => 'Robert', 'last_name' => 'Ssemakula', 'gender' => 'Male', 'date_of_birth' => '1990-01-01', 'classification' => 'Remand', 'legal_basis' => 'Fictional test order'];
        $this->actingAs($admin)->post(route('inmates.store'), $payload + ['facility_id' => $facility->id])->assertSessionHasErrors('facility_id');
        $this->post(route('inmates.store'), $payload + ['facility' => $facility->name])->assertSessionHasErrors('facility');
        $this->assertSame(0, Inmate::count());
    }
}
