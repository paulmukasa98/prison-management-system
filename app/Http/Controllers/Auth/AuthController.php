<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function loginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request, AuditService $auditService): RedirectResponse
    {
        $request->authenticate($auditService);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request, AuditService $auditService): RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $auditService->log(
                action: 'logout',
                outcome: 'Success',
                resourceType: 'User',
                resourceReference: $user->email,
                description: 'User initiated sign out.',
                actorId: $user->id
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Display the forgot password view.
     */
    public function forgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending a password reset link.
     */
    public function sendResetLink(ForgotPasswordRequest $request, AuditService $auditService): RedirectResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        $user = User::where('email', $request->input('email'))->first();

        $auditService->log(
            action: 'password_reset_requested',
            outcome: $status === Password::RESET_LINK_SENT ? 'Success' : 'Failure',
            resourceType: 'User',
            resourceReference: $request->input('email'),
            description: 'Password reset link requested. Status: '.trans($status),
            actorId: $user?->id
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', trans($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => trans($status)]);
    }

    /**
     * Display the password reset view.
     */
    public function resetPasswordForm(Request $request, ?string $token = null): View
    {
        return view('auth.reset-password', [
            'token' => $token ?? $request->route('token') ?? (string) $request->query('token'),
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function resetPassword(ResetPasswordRequest $request, AuditService $auditService): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $user = User::where('email', $request->input('email'))->first();

        $auditService->log(
            action: 'password_reset_completed',
            outcome: $status === Password::PASSWORD_RESET ? 'Success' : 'Failure',
            resourceType: 'User',
            resourceReference: $request->input('email'),
            description: 'Password reset submitted. Status: '.trans($status),
            actorId: $user?->id
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', trans($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => trans($status)]);
    }
}
