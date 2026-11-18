<?php

namespace App\Providers;

use App\Models\AuditEvent;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Personnel;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Policies\AuditEventPolicy;
use App\Policies\FacilityPolicy;
use App\Policies\InmatePolicy;
use App\Policies\PersonnelPolicy;
use App\Policies\TransferPolicy;
use App\Policies\UserPolicy;
use App\Policies\VisitorPolicy;
use App\Policies\VisitPolicy;
use App\Support\InterfacePermissions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('permission', fn (string $permission, ?int $facilityId = null): bool => app(InterfacePermissions::class)->allows($permission, $facilityId));
        Blade::if('nationalPermission', fn (string $permission): bool => app(InterfacePermissions::class)->national($permission));
        Blade::if('permissionAny', fn (array $permissions): bool => app(InterfacePermissions::class)->any($permissions));

        // Super administrator bypass
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('system_admin')) {
                return true;
            }

            return null;
        });

        // Register Policies
        Gate::policy(Inmate::class, InmatePolicy::class);
        Gate::policy(Transfer::class, TransferPolicy::class);
        Gate::policy(Personnel::class, PersonnelPolicy::class);
        Gate::policy(Facility::class, FacilityPolicy::class);
        Gate::policy(Visitor::class, VisitorPolicy::class);
        Gate::policy(Visit::class, VisitPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditEvent::class, AuditEventPolicy::class);

        // Core ability gates
        Gate::define('export-reports', fn (User $user): bool => $user->hasPermission('reports.export'));
        Gate::define('view-audit', fn (User $user): bool => $user->hasPermission('audit.view'));
        Gate::define('manage-users', fn (User $user): bool => $user->hasPermission('users.manage'));
        Gate::define('assign-roles', fn (User $user): bool => $user->hasPermission('roles.assign'));

        View::composer(['layouts.app', 'inmates.create'], function (\Illuminate\View\View $view): void {
            $user = auth()->user();
            $context = $view->getData()['demoContext'] ?? [];
            $context['demo_user'] = [
                'name' => $user?->name ?? 'Authorized User',
                'initials' => $user ? mb_substr($user->name, 0, 1) : 'U',
                'role' => $user?->primaryRoleName() ?? '',
                'facility' => $user?->primaryFacilityName() ?? '',
                'email' => $user?->email ?? '',
            ];
            $context['notification_count'] = 0;
            $view->with('demoContext', $context);
        });
    }
}
