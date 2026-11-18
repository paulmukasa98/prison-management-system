<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AuditEvent;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Personnel;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Database\Seeders\CanonicalDemoSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\B1DatabaseTestCase;

class RbacAndAuthorizationTest extends B1DatabaseTestCase
{
    private function createAdministrator(): User
    {
        $admin = User::factory()->create(['status' => 'Active']);
        UserRoleAssignment::factory()->create([
            'user_id' => $admin->id,
            'role_id' => Role::where('slug', 'system_admin')->firstOrFail()->id,
            'access_scope_id' => AccessScope::where('kind', 'National')->firstOrFail()->id,
        ]);

        return $admin;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalDemoSeeder::class);
    }

    public function test_default_deny_user_without_permission_is_denied(): void
    {
        $user = User::factory()->create([
            'email' => 'noperms.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        $this->assertFalse($user->hasPermission('inmates.read'));
        $this->assertFalse($user->hasPermission('users.manage'));
        $this->assertFalse($user->hasPermission('transfers.authorize'));
        $this->assertFalse($user->canAccessFacility(1));
    }

    public function test_system_administrator_has_full_permissions_across_all_facilities(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        $adminRole = Role::where('slug', 'system_admin')->firstOrFail();
        $nationalScope = AccessScope::where('kind', 'National')->firstOrFail();

        UserRoleAssignment::create([
            'assignment_ref' => 'URA-TEST-'.strtoupper(Str::random(8)),
            'user_id' => $admin->id,
            'role_id' => $adminRole->id,
            'access_scope_id' => $nationalScope->id,
            'granted_at' => now(),
        ]);

        $this->assertTrue($admin->hasRole('system_admin'));
        $this->assertTrue($admin->isNational());
        $this->assertTrue($admin->hasPermission('inmates.read', 1));
        $this->assertTrue($admin->hasPermission('inmates.admit', 2));
        $this->assertTrue($admin->hasPermission('users.manage'));
        $this->assertTrue($admin->canAccessFacility(1));
        $this->assertTrue($admin->canAccessFacility(99));

        // Gate checks
        $this->assertTrue(Gate::forUser($admin)->allows('manage-users'));
        $this->assertTrue(Gate::forUser($admin)->allows('view-audit'));
    }

    public function test_facility_scoped_user_cannot_access_other_facilities_preventing_idor(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $recordsRole = Role::where('slug', 'records_officer')->firstOrFail();

        $scopeFac1 = AccessScope::firstOrCreate(
            ['kind' => 'Facility', 'facility_id' => $fac1->id],
            ['scope_key' => 'SCOPE-FAC-'.$fac1->id]
        );

        $officer = User::factory()->create([
            'email' => 'records.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        UserRoleAssignment::create([
            'assignment_ref' => 'URA-TEST-'.strtoupper(Str::random(8)),
            'user_id' => $officer->id,
            'role_id' => $recordsRole->id,
            'access_scope_id' => $scopeFac1->id,
            'granted_at' => now(),
        ]);

        $this->assertFalse($officer->isNational());
        $this->assertTrue($officer->canAccessFacility($fac1->id));
        $this->assertFalse($officer->canAccessFacility($fac2->id));

        // Permission scoped to facility
        $this->assertTrue($officer->hasPermission('inmates.read', $fac1->id));
        $this->assertFalse($officer->hasPermission('inmates.read', $fac2->id));
        $this->assertFalse($officer->hasPermission('inmates.admit', $fac2->id));
    }

    public function test_inmate_policy_enforces_facility_scope(): void
    {
        $fac1 = Facility::firstOrFail();
        $fac2 = Facility::where('id', '!=', $fac1->id)->firstOrFail();

        $inmateAtFac1 = Inmate::factory()->create();
        $inmateAtFac1->admissions()->create([
            'admission_ref' => 'ADM-'.uniqid(),
            'admitting_facility_id' => $fac1->id,
            'admitted_at' => now(),
            'state' => 'Active',
        ]);

        $inmateAtFac2 = Inmate::factory()->create();
        $inmateAtFac2->admissions()->create([
            'admission_ref' => 'ADM-'.uniqid(),
            'admitting_facility_id' => $fac2->id,
            'admitted_at' => now(),
            'state' => 'Active',
        ]);

        $recordsRole = Role::where('slug', 'records_officer')->firstOrFail();
        $scopeFac1 = AccessScope::where('kind', 'Facility')->where('facility_id', $fac1->id)->firstOrFail();

        $officer = User::factory()->create([
            'email' => 'policy.officer.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        UserRoleAssignment::create([
            'assignment_ref' => 'URA-TEST-'.strtoupper(Str::random(8)),
            'user_id' => $officer->id,
            'role_id' => $recordsRole->id,
            'access_scope_id' => $scopeFac1->id,
            'granted_at' => now(),
        ]);

        // Policy view and update
        $this->assertTrue(Gate::forUser($officer)->allows('view', $inmateAtFac1));
        $this->assertFalse(Gate::forUser($officer)->allows('view', $inmateAtFac2));
        $this->assertTrue(Gate::forUser($officer)->allows('update', $inmateAtFac1));
        $this->assertFalse(Gate::forUser($officer)->allows('update', $inmateAtFac2));

        // Discharge permission (records officer does NOT have inmates.discharge)
        $this->assertFalse(Gate::forUser($officer)->allows('discharge', $inmateAtFac1));
    }

    public function test_personnel_creation_does_not_provision_login_account(): void
    {
        $initialUserCount = User::count();

        $personnel = Personnel::create([
            'staff_number' => 'UPS-TEST-'.strtoupper(Str::random(5)),
            'first_name' => 'Kato',
            'last_name' => 'Mukasa',
            'employment_status' => 'Active',
            'email' => 'kato.'.uniqid().'@demo.ups.go.ug',
        ]);

        $this->assertNotNull($personnel->id);
        $this->assertNull(User::where('personnel_id', $personnel->id)->first());
        $this->assertEquals($initialUserCount, User::count());
    }

    public function test_account_provisioning_via_admin_controller(): void
    {
        $admin = $this->createAdministrator();

        $role = Role::firstOrFail();
        $newEmail = 'new.provisioned.'.uniqid().'@demo.ups.go.ug';

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Josephine Akello',
            'email' => $newEmail,
            'role_key' => $role->slug,
            'facility_scope' => 'National',
            'initial_password' => 'InitialPassword123!',
        ]);

        $user = User::where('email', $newEmail)->first();
        $this->assertNotNull($user);

        $response->assertRedirect('/users/'.$user->id);
        $this->assertEquals('Active', $user->status);
        $this->assertTrue(Hash::check('InitialPassword123!', $user->password));

        // Role assignment exists
        $this->assertTrue($user->hasRole($role->slug));

        // Audit log exists and password is NOT stored
        $audit = AuditEvent::where('action', 'account_created')->where('resource_reference', $user->email)->first();
        $this->assertNotNull($audit);
        $this->assertArrayNotHasKey('password', $audit->changes ?? []);
    }

    public function test_account_status_modification_is_audited(): void
    {
        $admin = $this->createAdministrator();

        $targetUser = User::factory()->create([
            'email' => 'target.status.'.uniqid().'@demo.ups.go.ug',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($admin)->post('/users/'.$targetUser->id.'/status', [
            'status' => 'Suspended',
            'reason' => 'Security review pending',
        ]);

        $response->assertRedirect('/users/'.$targetUser->id);

        $targetUser->refresh();
        $this->assertEquals('Suspended', $targetUser->status);

        $this->assertDatabaseHas('audit_events', [
            'action' => 'account_status_changed',
            'outcome' => 'Success',
            'resource_reference' => $targetUser->email,
        ]);
    }

    public function test_admin_bootstrap_cli_command_creates_active_administrator(): void
    {
        $adminEmail = 'cli.bootstrap.'.uniqid().'@ups.go.ug';

        $exitCode = Artisan::call('ups:admin-create', [
            '--name' => 'CLI Bootstrap Admin',
            '--email' => $adminEmail,
            '--password' => 'SecurePass1234!',
        ]);

        $this->assertEquals(0, $exitCode);

        $user = User::where('email', $adminEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('Active', $user->status);
        $this->assertTrue($user->hasRole('system_admin'));
        $this->assertTrue($user->isNational());
        $this->assertTrue(Hash::check('SecurePass1234!', $user->password));

        $this->assertDatabaseHas('audit_events', [
            'action' => 'bootstrap_admin_created',
            'outcome' => 'Success',
            'resource_reference' => $adminEmail,
        ]);
    }
}
