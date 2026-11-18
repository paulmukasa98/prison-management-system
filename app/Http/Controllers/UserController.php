<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AssignUserRoleRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserStatusRequest;
use App\Models\AccessScope;
use App\Models\Facility;
use App\Models\Permission;
use App\Models\Personnel;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Services\AuditService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManagement($request);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $users = User::with(['activeRoleAssignments.role', 'activeRoleAssignments.scope.facility', 'personnel'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search): Builder => $query->where(fn (Builder $query): Builder => $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->orderBy('name')->orderBy('id')->paginate(25)->withQueryString();

        return view('users.index', ['users' => $users]);
    }

    public function show(Request $request, int $id): View
    {
        $this->authorizeManagement($request);

        return view('users.show', ['user' => User::with(['personnel', 'activeRoleAssignments.role.permissions', 'activeRoleAssignments.scope.facility'])->findOrFail($id)]);
    }

    public function create(Request $request): View
    {
        $this->authorizeManagement($request, true);

        return view('users.create', [
            'roles' => Role::orderBy('name')->get(), 'facilities' => Facility::orderBy('name')->get(),
            'candidates' => Personnel::where('employment_status', 'Active')->whereDoesntHave('account')->orderBy('last_name')->limit(100)->get(),
        ]);
    }

    public function store(StoreUserRequest $request, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManagement($request, true);
        $data = $request->validated();
        $user = DB::transaction(function () use ($data, $request, $auditService): User {
            $user = User::create([
                'account_ref' => 'USR-'.Str::uuid(), 'name' => $data['name'], 'email' => $data['email'],
                'personnel_id' => $data['personnel_id'] ?? null, 'password' => $data['initial_password'], 'status' => 'Active',
            ]);
            $this->grantRole($user, $data['role_key'], $data['facility_scope'], $request->user());
            $auditService->log('account_created', 'Success', 'User', $user->email, ['user_id' => $user->id, 'role' => $data['role_key']], actorId: $request->user()->id);

            return $user;
        }, 3);

        return redirect()->route('users.show', $user)->with('status', 'Account provisioned. Communicate the initial password using an approved secure channel.');
    }

    public function status(Request $request, int $id): View
    {
        $this->authorizeManagement($request);

        return view('users.status', ['user' => User::findOrFail($id)]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, int $id, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManagement($request);
        abort_if($request->user()->id === $id, 422, 'Ask another administrator to change your account status.');
        DB::transaction(function () use ($request, $id, $auditService): void {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $before = $user->status;
            $user->status = $request->validated('status');
            $user->remember_token = null;
            $user->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $auditService->log('account_status_changed', 'Success', 'User', $user->email, ['old_status' => $before, 'new_status' => $user->status, 'reason' => $request->validated('reason')], actorId: $request->user()->id);
        }, 3);

        return redirect()->route('users.show', $id)->with('status', 'Account status changed and existing sessions revoked.');
    }

    public function assignRole(Request $request, int $id): View
    {
        $this->authorizeManagement($request, true);

        return view('users.assign-role', [
            'user' => User::with('activeRoleAssignments.role.permissions')->findOrFail($id),
            'roles' => Role::with('permissions')->orderBy('name')->get(), 'facilities' => Facility::orderBy('name')->get(),
        ]);
    }

    public function updateRole(AssignUserRoleRequest $request, int $id, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManagement($request, true);
        abort_if($request->user()->id === $id, 422, 'Ask another administrator to change your roles.');
        DB::transaction(function () use ($request, $id, $auditService): void {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            $user->activeRoleAssignments()->update(['ended_at' => now()]);
            $this->grantRole($user, $request->validated('role_key'), $request->validated('facility_scope'), $request->user());
            $user->remember_token = null;
            $user->save();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            $auditService->log('role_assigned', 'Success', 'User', $user->email, ['role' => $request->validated('role_key'), 'scope' => $request->validated('facility_scope')], actorId: $request->user()->id);
        }, 3);

        return redirect()->route('users.show', $id)->with('status', 'Role assignment saved. Previous assignments remain in history.');
    }

    public function roles(Request $request): View
    {
        $this->authorizeManagement($request);

        return view('users.roles', ['roles' => Role::with('permissions')->orderBy('name')->get(), 'permissions' => Permission::orderBy('module')->orderBy('name')->get()]);
    }

    private function authorizeManagement(Request $request, bool $assigningRoles = false): void
    {
        abort_unless($request->user()->facilityIdsForPermission('users.manage') === null, 403);
        if ($assigningRoles) {
            abort_unless($request->user()->facilityIdsForPermission('roles.assign') === null, 403);
        }
    }

    private function grantRole(User $user, string $roleSlug, string $scopeName, User $actor): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        if ($role->slug === 'system_admin' && $scopeName !== 'National') {
            throw ValidationException::withMessages(['facility_scope' => 'System administrators require an explicit national scope.']);
        }
        $facility = $scopeName === 'National' ? null : Facility::where('name', $scopeName)->firstOrFail();
        $scope = AccessScope::firstOrCreate(
            ['kind' => $facility ? 'Facility' : 'National', 'facility_id' => $facility?->id],
            ['scope_key' => $facility ? 'SCOPE-FAC-'.$facility->id : 'SCOPE-NATIONAL']
        );
        $assignment = new UserRoleAssignment([
            'assignment_ref' => 'URA-'.Str::uuid(), 'user_id' => $user->id,
            'role_id' => $role->id, 'access_scope_id' => $scope->id, 'granted_at' => now(),
        ]);
        $assignment->granted_by = $actor->id;
        $assignment->save();
    }
}
