<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            $this->auditService->log(
                action: 'session_terminated_inactive',
                outcome: 'Denied',
                resourceType: 'User',
                resourceReference: $user->email,
                description: 'User attempted access with inactive status: '.$user->status,
                actorId: $user->id
            );

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not active. Please contact the system administrator.',
            ]);
        }

        return $next($request);
    }
}
