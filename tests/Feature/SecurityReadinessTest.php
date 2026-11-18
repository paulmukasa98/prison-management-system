<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Permission;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Models\Role;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\BackupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\B1DatabaseTestCase;

class SecurityReadinessTest extends B1DatabaseTestCase
{
    /** @param list<string> $permissions */
    private function officer(array $permissions, ?Facility $facility = null): User
    {
        $user = User::factory()->create(['status' => 'Active']);
        $role = Role::factory()->create();
        foreach ($permissions as $slug) {
            $role->permissions()->attach(Permission::firstOrCreate(['slug' => $slug], ['name' => $slug, 'module' => 'Security']));
        }
        $scope = $facility
            ? AccessScope::firstOrCreate(['kind' => 'Facility', 'facility_id' => $facility->id], ['scope_key' => 'SECURITY-FAC-'.$facility->id])
            : AccessScope::firstOrCreate(['kind' => 'National'], ['scope_key' => 'SECURITY-NATIONAL']);
        UserRoleAssignment::factory()->create(['user_id' => $user->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id]);

        return $user;
    }

    public function test_account_routes_require_national_management_and_cannot_be_used_for_privilege_escalation(): void
    {
        $facility = Facility::factory()->create();
        $officer = $this->officer(['inmates.read', 'users.manage', 'roles.assign'], $facility);
        $target = User::factory()->create(['status' => 'Active']);
        $this->actingAs($officer)->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();
        $this->get(route('users.show', $target))->assertForbidden();
        $this->get(route('settings.index'))->assertForbidden();
        $this->post(route('users.store'), [])->assertForbidden();
        $this->post(route('users.status.update', $target), ['status' => 'Active', 'reason' => 'Attempt'])->assertForbidden();
        $this->post(route('users.role.update', $target), [])->assertForbidden();
        $this->assertSame(0, $target->activeRoleAssignments()->count());
    }

    public function test_authorized_account_provisioning_creates_real_account_and_preserves_role_history(): void
    {
        $admin = $this->officer(['users.manage', 'roles.assign']);
        $role = Role::factory()->create(['name' => 'Fictional Records Role']);
        $targetRole = Role::factory()->create(['name' => 'Fictional Auditor Role']);
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Fictional New User', 'email' => 'new-user@example.invalid', 'role_key' => $role->slug,
            'facility_scope' => 'National', 'initial_password' => 'Fictional-Initial-123!',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $user = User::where('email', 'new-user@example.invalid')->firstOrFail();
        $this->assertTrue($user->hasRole($role->slug));
        $this->get(route('users.show', $user))->assertOk()->assertSee('Fictional New User')->assertDontSee('Fictional-Initial-123!');
        $this->post(route('users.role.update', $user), ['role_key' => $targetRole->slug, 'facility_scope' => 'National'])->assertSessionHasNoErrors();
        $this->assertSame(2, $user->roleAssignments()->count());
        $this->assertTrue($user->hasRole($targetRole->slug));
        $this->assertFalse($user->hasRole($role->slug));
        $this->assertDatabaseHas('audit_events', ['action' => 'role_assigned', 'resource_reference' => $user->email]);
    }

    public function test_account_suspension_revokes_sessions_and_cannot_suspend_the_acting_administrator(): void
    {
        $admin = $this->officer(['users.manage', 'roles.assign']);
        $target = User::factory()->create(['status' => 'Active', 'remember_token' => 'fictional-token']);
        DB::table('sessions')->insert(['id' => 'fictional-session', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);
        $this->actingAs($admin)->post(route('users.status.update', $target), ['status' => 'Suspended', 'reason' => 'Fictional security review'])->assertSessionHasNoErrors();
        $this->assertSame('Suspended', $target->fresh()->status);
        $this->assertNull($target->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'fictional-session']);
        $this->post(route('users.status.update', $admin), ['status' => 'Suspended', 'reason' => 'Self change'])->assertStatus(422);
        $this->assertSame('Active', $admin->fresh()->status);
    }

    public function test_prior_modules_reject_cross_facility_ids_and_do_not_fall_back_to_fixtures(): void
    {
        $own = Facility::factory()->create(['name' => 'Own Fictional Facility']);
        $other = Facility::factory()->create(['name' => 'Other Fictional Facility']);
        $officer = $this->officer(['inmates.read', 'inmates.edit', 'inmates.discharge', 'personnel.read', 'personnel.manage', 'facilities.read', 'facilities.manage', 'transfers.request'], $own);
        $episode = AdmissionEpisode::factory()->create(['admitting_facility_id' => $other->id]);
        CustodyLocation::factory()->create(['admission_episode_id' => $episode->id, 'facility_id' => $other->id]);
        $person = Personnel::factory()->create();
        PersonnelAssignment::factory()->create(['personnel_id' => $person->id, 'facility_id' => $other->id]);
        $transfer = Transfer::factory()->create(['admission_episode_id' => $episode->id, 'origin_facility_id' => $other->id]);
        $this->actingAs($officer);
        foreach (['inmates.show', 'inmates.edit', 'inmates.admissions', 'inmates.discharge'] as $route) {
            $this->get(route($route, $episode->inmate_id))->assertNotFound();
        }
        foreach (['personnel.show', 'personnel.edit', 'personnel.assignment.history'] as $route) {
            $this->get(route($route, $person))->assertNotFound();
        }
        foreach (['facilities.show', 'facilities.edit', 'facilities.personnel'] as $route) {
            $this->get(route($route, $other))->assertNotFound();
        }
        $this->get(route('transfers.show', $transfer))->assertNotFound();
        $this->get(route('inmates.index'))->assertOk()->assertDontSee($episode->inmate->prisoner_number)->assertDontSee('DEMO-PN-0001');
        $this->get(route('personnel.index'))->assertOk()->assertDontSee($person->staff_number);
        $this->get(route('facilities.index'))->assertOk()->assertDontSee($other->name);
    }

    public function test_security_headers_prevent_caching_and_reject_caller_correlation_ids(): void
    {
        $response = $this->withHeader('X-Request-ID', 'caller-controlled')->get(route('login'))->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertTrue(Str::isUuid($response->headers->get('X-Request-ID')));
        $this->assertNotSame('caller-controlled', $response->headers->get('X-Request-ID'));
    }

    public function test_production_serving_is_blocked_without_approval(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        config(['operations.production_approved' => false]);
        $this->get(route('login'))->assertStatus(503);
    }

    public function test_readiness_fails_for_incomplete_settings_without_revealing_keys(): void
    {
        config(['operations.production_approved' => false, 'operations.backup_key' => 'do-not-print-this-key']);
        $this->artisan('pms:readiness')->expectsOutputToContain('FAIL Production approval recorded')->doesntExpectOutputToContain('do-not-print-this-key')->assertFailed();
    }

    public function test_backup_refuses_unconfigured_key_and_path_traversal_without_creating_plaintext(): void
    {
        config(['operations.backup_key' => null]);
        $this->artisan('pms:backup', ['--database' => 'b1_test'])->expectsOutputToContain('PMS_BACKUP_KEY')->assertFailed();
        $this->expectException(\RuntimeException::class);
        app(BackupService::class)->verify('../outside.pmsbackup');
    }

    public function test_approved_settings_pass_configuration_checks(): void
    {
        config([
            'operations.production_approved' => true, 'operations.security_review_reference' => 'DEMO-REVIEW',
            'operations.uat_reference' => 'DEMO-UAT', 'operations.restore_drill_reference' => 'DEMO-DRILL',
            'app.debug' => false, 'app.url' => 'https://example.invalid',
            'session.secure' => true, 'session.http_only' => true, 'session.encrypt' => true,
            'database.default' => 'mysql', 'queue.default' => 'database', 'mail.default' => 'smtp',
            'visitation.rules_confirmed' => true, 'operations.backup_key' => base64_encode(str_repeat('k', 32)),
        ]);
        $this->artisan('pms:readiness')->assertSuccessful();
        config(['database.default' => 'b1_test']);
    }
}
