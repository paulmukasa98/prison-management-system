<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\B1DatabaseTestCase;

class ProfileTest extends B1DatabaseTestCase
{
    public function test_only_authenticated_active_users_can_manage_their_own_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->post(route('profile.update'), [])->assertRedirect(route('login'));
        $this->post(route('profile.password'), [])->assertRedirect(route('login'));
        $user = User::factory()->create(['name' => '<script>private-name</script>', 'status' => 'Active']);
        $this->actingAs($user)->get(route('profile.show'))->assertOk()
            ->assertSee('&lt;script&gt;private-name&lt;/script&gt;', false)
            ->assertDontSee('<script>private-name</script>', false)
            ->assertSee(route('profile.update'), false)->assertSee(route('profile.password'), false);
        $user->update(['status' => 'Suspended']);
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->post(route('profile.update'), [])->assertRedirect(route('login'));
    }

    public function test_profile_changes_cannot_change_privileges_or_another_account(): void
    {
        $user = User::factory()->create(['status' => 'Active']);
        $other = User::factory()->create(['name' => 'Another Officer']);
        $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'Updated Officer', 'email' => $user->email,
            'id' => $other->id, 'status' => 'Suspended', 'role_key' => 'system_admin', 'personnel_id' => 1234,
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.show'));
        $this->assertSame('Updated Officer', $user->fresh()->name);
        $this->assertSame('Active', $user->fresh()->status);
        $this->assertNull($user->fresh()->personnel_id);
        $this->assertSame(0, $user->roleAssignments()->count());
        $this->assertSame('Another Officer', $other->fresh()->name);
        $this->assertDatabaseHas('audit_events', ['action' => 'profile_updated', 'actor_id' => $user->id]);
    }

    public function test_email_change_requires_current_password_and_unique_valid_email_and_revokes_other_sessions(): void
    {
        $user = User::factory()->create(['status' => 'Active', 'password' => 'Existing-password-123', 'email_verified_at' => now(), 'remember_token' => 'old-remember']);
        $other = User::factory()->create();
        $this->actingAs($user)->post(route('profile.update'), ['name' => 'Officer', 'email' => 'changed@example.test'])->assertSessionHasErrors('current_password');
        $this->post(route('profile.update'), ['name' => '', 'email' => 'invalid', 'current_password' => 'wrong'])->assertSessionHasErrors(['name', 'email', 'current_password']);
        $this->post(route('profile.update'), ['name' => 'Officer', 'email' => $other->email, 'current_password' => 'Existing-password-123'])->assertSessionHasErrors('email');
        DB::table('sessions')->insert(['id' => 'other-profile-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('sessions')->insert(['id' => 'unrelated-profile-session', 'user_id' => $other->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'old-reset-token', 'created_at' => now()]);
        $this->post(route('profile.update'), ['name' => 'Officer', 'email' => 'changed@example.test', 'current_password' => 'Existing-password-123'])->assertSessionHasNoErrors()->assertRedirect(route('profile.show'));
        $this->assertSame('changed@example.test', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'other-profile-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'unrelated-profile-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['token' => 'old-reset-token']);
    }

    public function test_password_change_validates_credentials_confirmation_length_and_difference(): void
    {
        $user = User::factory()->create(['status' => 'Active', 'password' => 'Existing-password-123']);
        $this->actingAs($user);
        foreach ([
            ['current_password' => 'wrong', 'password' => 'New-password-456', 'password_confirmation' => 'New-password-456'],
            ['current_password' => 'Existing-password-123', 'password' => 'short', 'password_confirmation' => 'short'],
            ['current_password' => 'Existing-password-123', 'password' => 'New-password-456', 'password_confirmation' => 'different-password'],
            ['current_password' => 'Existing-password-123', 'password' => 'Existing-password-123', 'password_confirmation' => 'Existing-password-123'],
        ] as $payload) {
            $this->post(route('profile.password'), $payload)->assertSessionHasErrors(null, null, 'passwordChange');
        }
        $this->assertTrue(Hash::check('Existing-password-123', $user->fresh()->password));
        $this->assertSame(0, AuditEvent::where('action', 'profile_password_changed')->count());
    }

    public function test_password_change_hashes_password_revokes_other_sessions_and_does_not_audit_secrets(): void
    {
        $user = User::factory()->create(['status' => 'Active', 'password' => 'Existing-password-123', 'remember_token' => 'old-remember']);
        DB::table('sessions')->insert(['id' => 'other-password-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'old-reset-token', 'created_at' => now()]);
        $this->actingAs($user)->post(route('profile.password'), [
            'current_password' => 'Existing-password-123', 'password' => 'New-password-456', 'password_confirmation' => 'New-password-456',
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.show'));
        $this->assertTrue(Hash::check('New-password-456', $user->fresh()->password));
        $this->assertNull($user->fresh()->remember_token);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('sessions', ['id' => 'other-password-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['token' => 'old-reset-token']);
        $event = AuditEvent::where('action', 'profile_password_changed')->firstOrFail();
        $this->assertStringNotContainsString('password-123', json_encode($event));
        $this->assertStringNotContainsString('password-456', json_encode($event));
        $this->assertSame($user->id, $event->actor_id);
    }

    public function test_profile_update_is_rolled_back_if_audit_cannot_be_recorded(): void
    {
        $user = User::factory()->create(['name' => 'Original Officer', 'status' => 'Active']);
        $this->mock(AuditService::class)->shouldReceive('log')->once()->andThrow(new RuntimeException('Audit unavailable'));
        $this->actingAs($user)->post(route('profile.update'), ['name' => 'Updated Officer', 'email' => $user->email])->assertStatus(500);
        $this->assertSame('Original Officer', $user->fresh()->name);
    }
}
