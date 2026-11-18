<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\B1DatabaseTestCase;

class PasswordResetTest extends B1DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    public function test_forgot_password_screen_renders(): void
    {
        $response = $this->get('/auth/forgot-password');

        $response->assertOk()
            ->assertSee('Forgot your password?')
            ->assertSee('name="email"', false);
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        $email = 'reset.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'status' => 'Active',
        ]);

        $response = $this->from('/auth/forgot-password')->post('/auth/forgot-password', [
            'email' => $email,
        ]);

        $response->assertRedirect('/auth/forgot-password');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $email,
        ]);

        $this->assertDatabaseHas('audit_events', [
            'action' => 'password_reset_requested',
            'outcome' => 'Success',
            'resource_reference' => $email,
            'actor_id' => $user->id,
        ]);
    }

    public function test_reset_password_screen_renders_with_token(): void
    {
        $token = 'sample-test-token-12345';
        $email = 'test.'.uniqid().'@demo.ups.go.ug';

        $response = $this->get('/auth/reset-password/'.$token.'?email='.$email);

        $response->assertOk()
            ->assertSee('Reset your password')
            ->assertSee('value="'.$token.'"', false)
            ->assertSee('value="'.$email.'"', false);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $email = 'complete.'.uniqid().'@demo.ups.go.ug';

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('OldPassword123!'),
            'status' => 'Active',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/auth/reset-password', [
            'token' => $token,
            'email' => $email,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertRedirect('/auth/login');
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword123!', $user->password));

        $this->assertDatabaseHas('audit_events', [
            'action' => 'password_reset_completed',
            'outcome' => 'Success',
            'resource_reference' => $email,
            'actor_id' => $user->id,
        ]);

        // Verify login works with new password
        $loginResponse = $this->post('/auth/login', [
            'email' => $email,
            'password' => 'NewSecurePassword123!',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
