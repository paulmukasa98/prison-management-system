<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\B1DatabaseTestCase;

class AuthenticationTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    public function test_guests_are_redirected_to_login_from_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/auth/login');
        $this->get('/users')->assertRedirect('/auth/login');
        $this->get('/inmates')->assertRedirect('/auth/login');
        $this->get('/reports')->assertRedirect('/auth/login');
    }

    public function test_login_screen_renders_with_csrf_and_form_controls(): void
    {
        $response = $this->get('/auth/login');

        $response->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_user_can_authenticate_with_valid_credentials(): void
    {
        $email = 'active.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('ValidPass123!'),
            'status' => 'Active',
        ]);

        $response = $this->post('/auth/login', [
            'email' => $email,
            'password' => 'ValidPass123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Verify audit event was logged
        $this->assertDatabaseHas('audit_events', [
            'action' => 'login_success',
            'outcome' => 'Success',
            'resource_reference' => $email,
            'actor_id' => $user->id,
        ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $email = 'wrong.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('CorrectPass123!'),
            'status' => 'Active',
        ]);

        $response = $this->from('/auth/login')->post('/auth/login', [
            'email' => $email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertRedirect('/auth/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'login_failed',
            'outcome' => 'Failure',
            'resource_reference' => $email,
        ]);
    }

    public function test_inactive_or_suspended_user_is_denied_login(): void
    {
        $email = 'suspended.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('ValidPass123!'),
            'status' => 'Suspended',
        ]);

        $response = $this->from('/auth/login')->post('/auth/login', [
            'email' => $email,
            'password' => 'ValidPass123!',
        ]);

        $response->assertRedirect('/auth/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'login_denied_inactive',
            'outcome' => 'Denied',
            'resource_reference' => $email,
        ]);
    }

    public function test_login_rate_limiting_locks_out_after_too_many_attempts(): void
    {
        $email = 'throttle.'.uniqid().'@demo.ups.go.ug';
        $throttleKey = strtolower($email).'|127.0.0.1';
        RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        $response = $this->from('/auth/login')->post('/auth/login', [
            'email' => $email,
            'password' => 'AnyPassword123!',
        ]);

        $response->assertRedirect('/auth/login');
        $response->assertSessionHasErrors('email');

        RateLimiter::clear($throttleKey);
    }

    public function test_authenticated_user_can_log_out_securely(): void
    {
        $email = 'logout.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)->post('/auth/logout');

        $response->assertRedirect('/auth/login');
        $this->assertGuest();

        $this->assertDatabaseHas('audit_events', [
            'action' => 'logout',
            'outcome' => 'Success',
            'resource_reference' => $email,
            'actor_id' => $user->id,
        ]);
    }

    public function test_session_is_terminated_if_active_user_becomes_suspended(): void
    {
        $email = 'dynamic.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'status' => 'Active',
        ]);

        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();

        // Admin suspends user
        $user->update(['status' => 'Suspended']);

        // Next request triggers EnsureUserIsActive middleware
        $response = $this->get('/dashboard');

        $response->assertRedirect('/auth/login');
        $this->assertGuest();
    }
}
