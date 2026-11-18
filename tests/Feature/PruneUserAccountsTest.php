<?php

namespace Tests\Feature;

use App\Models\AccessScope;
use App\Models\AuditEvent;
use App\Models\Personnel;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use Database\Seeders\CanonicalDemoSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\B1DatabaseTestCase;

class PruneUserAccountsTest extends B1DatabaseTestCase
{
    private function administrator(): User
    {
        $admin = User::factory()->create(['email' => 'keeper@example.invalid', 'status' => 'Active', 'remember_token' => 'keeper-remember']);
        $role = Role::where('slug', 'system_admin')->first() ?? Role::factory()->create(['slug' => 'system_admin']);
        $scope = AccessScope::where('kind', 'National')->first() ?? AccessScope::factory()->create();
        UserRoleAssignment::factory()->create([
            'user_id' => $admin->id, 'role_id' => $role->id, 'access_scope_id' => $scope->id,
        ]);

        return $admin;
    }

    public function test_preview_does_not_change_accounts_credentials_or_role_history(): void
    {
        $admin = $this->administrator();
        $other = User::factory()->create(['status' => 'Active']);
        $before = $other->getRawOriginal('password');
        $this->artisan('ups:users-prune', ['--keep' => $admin->email])->assertSuccessful();
        $this->assertSame(2, User::count());
        $this->assertSame($before, $other->fresh()->getRawOriginal('password'));
        $this->assertSame(0, AuditEvent::where('action', 'accounts_archived')->count());
    }

    public function test_cleanup_revokes_other_accounts_and_preserves_keeper_and_historical_actor_records(): void
    {
        $admin = $this->administrator();
        $keeperBefore = $admin->refresh()->getAttributes();
        $personnel = Personnel::factory()->create();
        $other = User::factory()->create([
            'email' => 'remove@example.invalid', 'status' => 'Active', 'password' => 'Fictional-Old-123!',
            'remember_token' => 'old-remember', 'personnel_id' => $personnel->id,
        ]);
        $assignment = UserRoleAssignment::factory()->create([
            'user_id' => $other->id, 'access_scope_id' => $admin->activeRoleAssignments()->firstOrFail()->access_scope_id,
        ]);
        $event = app(AuditService::class)->log('historical_action', 'Success', 'Test', actorId: $other->id);
        $signature = $event->integrity_hash;
        DB::table('sessions')->insert([
            ['id' => 'keeper-session', 'user_id' => $admin->id, 'payload' => 'fictional', 'last_activity' => time()],
            ['id' => 'removed-session', 'user_id' => $other->id, 'payload' => 'fictional', 'last_activity' => time()],
        ]);
        DB::table('password_reset_tokens')->insert([
            ['email' => $admin->email, 'token' => 'keeper-token'],
            ['email' => $other->email, 'token' => 'removed-token'],
        ]);
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertSuccessful();
        $this->assertSame([$admin->id], User::pluck('id')->all());
        $this->assertSame($keeperBefore, $admin->fresh()->getAttributes());
        $archived = User::withTrashed()->findOrFail($other->id);
        $this->assertTrue($archived->trashed());
        $this->assertFalse($archived->isActive());
        $this->assertSame('Disabled', $archived->status);
        $this->assertNull($archived->remember_token);
        $this->assertFalse(Hash::check('Fictional-Old-123!', $archived->password));
        $this->assertNotNull($assignment->fresh()->ended_at);
        $this->assertDatabaseMissing('sessions', ['id' => 'removed-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'keeper-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $other->email]);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $admin->email]);
        $this->assertDatabaseHas('personnel', ['id' => $personnel->id]);
        $this->assertSame($other->id, $event->fresh()->actor->id);
        $this->assertSame($signature, AuditService::signatureFor($event->fresh()));
        $this->assertDatabaseHas('audit_events', ['action' => 'accounts_archived']);
        $this->assertNull(Auth::getProvider()->retrieveByCredentials(['email' => $other->email, 'password' => 'Fictional-Old-123!']));
        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee($admin->email)->assertDontSee($other->email);
        $this->get(route('users.show', $other->id))->assertNotFound();
    }

    public function test_missing_or_nonprivileged_keeper_cannot_remove_accounts(): void
    {
        $other = User::factory()->create(['status' => 'Active']);
        $this->artisan('ups:users-prune', ['--keep' => 'missing@example.invalid', '--force' => true])->assertFailed();
        $this->artisan('ups:users-prune', ['--keep' => $other->email, '--force' => true])->assertFailed();
        $this->artisan('ups:users-prune', ['--keep' => '', '--force' => true])->assertFailed();
        $this->assertSame(1, User::count());
    }

    public function test_disabled_or_facility_scoped_administrator_cannot_be_the_keeper(): void
    {
        $admin = $this->administrator();
        User::factory()->create();
        $admin->update(['status' => 'Disabled']);
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertFailed();
        $admin->update(['status' => 'Active']);
        $admin->activeRoleAssignments()->firstOrFail()->update(['access_scope_id' => AccessScope::factory()->facility()->create()->id]);
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertFailed();
        $this->assertSame(2, User::count());
    }

    public function test_production_environment_rejects_cleanup(): void
    {
        $admin = $this->administrator();
        User::factory()->create();
        $this->app->instance('env', 'production');
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertFailed();
        $this->assertSame(2, User::count());
    }

    public function test_repeated_cleanup_is_idempotent(): void
    {
        $admin = $this->administrator();
        User::factory()->create();
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertSuccessful();
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertSuccessful();
        $this->assertSame(1, User::count());
        $this->assertSame(1, User::onlyTrashed()->count());
        $this->assertSame(1, AuditEvent::where('action', 'accounts_archived')->count());
    }

    public function test_demo_seeding_does_not_reactivate_archived_accounts(): void
    {
        $this->seed(CanonicalDemoSeeder::class);
        $admin = $this->administrator();
        $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->assertSuccessful();
        $archivedCount = User::onlyTrashed()->count();
        $this->seed(CanonicalDemoSeeder::class);
        $this->assertSame(1, User::count());
        $this->assertSame($archivedCount, User::onlyTrashed()->count());
    }

    public function test_audit_failure_rolls_back_account_and_access_changes(): void
    {
        $admin = $this->administrator();
        $other = User::factory()->create(['status' => 'Active', 'remember_token' => 'keep-on-rollback']);
        $assignment = UserRoleAssignment::factory()->create([
            'user_id' => $other->id, 'access_scope_id' => $admin->activeRoleAssignments()->firstOrFail()->access_scope_id,
        ]);
        $before = $other->refresh()->getAttributes();
        $this->mock(AuditService::class)->shouldReceive('log')->once()->andThrow(new RuntimeException('Fictional audit failure'));
        try {
            $this->artisan('ups:users-prune', ['--keep' => $admin->email, '--force' => true])->run();
            $this->fail('The cleanup should fail when its audit event cannot be recorded.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fictional audit failure', $exception->getMessage());
        }
        $this->assertSame(2, User::count());
        $this->assertSame($before, $other->fresh()->getAttributes());
        $this->assertNull($assignment->fresh()->ended_at);
    }
}
