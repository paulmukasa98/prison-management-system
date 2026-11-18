<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('profile.show', ['user' => $request->user()->load(['personnel', 'activeRoleAssignments.role', 'activeRoleAssignments.scope.facility'])]);
    }

    public function update(UpdateProfileRequest $request, AuditService $audit): RedirectResponse
    {
        DB::transaction(function () use ($request, $audit): void {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $user->fill($request->safe()->only(['name', 'email']));
            if ($user->isDirty('email')) {
                if (! Hash::check($request->validated('current_password') ?? '', $user->password)) {
                    throw ValidationException::withMessages(['current_password' => 'The password is incorrect.']);
                }
                $user->email_verified_at = null;
                $user->remember_token = null;
                $this->revokeOtherSessions($request, $user);
                DB::table('password_reset_tokens')->whereIn('email', [$user->getOriginal('email'), $user->email])->delete();
            }
            $changedFields = array_keys($user->getDirty());
            $user->save();
            $audit->log('profile_updated', 'Success', 'User', $user->account_ref, ['changed_fields' => $changedFields], actorId: $user->id);
        }, 3);

        return redirect()->route('profile.show')->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request, AuditService $audit): RedirectResponse
    {
        DB::transaction(function () use ($request, $audit): void {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if (! Hash::check($request->validated('current_password'), $user->password)) {
                throw ValidationException::withMessages(['current_password' => 'The password is incorrect.'])->errorBag('passwordChange');
            }
            $user->password = $request->validated('password');
            $user->remember_token = null;
            $user->save();
            $this->revokeOtherSessions($request, $user);
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $audit->log('profile_password_changed', 'Success', 'User', $user->account_ref, actorId: $user->id);
        }, 3);
        $request->session()->regenerate();

        return redirect()->route('profile.show')->with('status', 'Password changed. Other sessions have been signed out.');
    }

    private function revokeOtherSessions(Request $request, User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();
    }
}
