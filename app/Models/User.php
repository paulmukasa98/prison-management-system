<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'account_ref', 'personnel_id', 'status', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'personnel_id')->withTrashed();
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function activeRoleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class)->whereNull('ended_at');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class, 'actor_id');
    }

    /**
     * Get the primary role name safely.
     */
    public function primaryRoleName(): string
    {
        try {
            return $this->activeRoleAssignments()->with('role')->first()?->role?->name ?? 'Authorized User';
        } catch (\Throwable) {
            return 'Authorized User';
        }
    }

    /**
     * Get the primary facility or national scope name safely.
     */
    public function primaryFacilityName(): string
    {
        try {
            if ($this->isNational()) {
                return 'National HQ';
            }

            return $this->activeRoleAssignments()->with('scope.facility')->first()?->scope?->facility?->name ?? 'Facility Scope';
        } catch (\Throwable) {
            return 'Facility Scope';
        }
    }

    /**
     * Check if the account is in Active status.
     */
    public function isActive(): bool
    {
        return ! $this->trashed() && $this->status === 'Active';
    }

    /**
     * Check if the user has an active National scope assignment.
     */
    public function isNational(): bool
    {
        return $this->activeRoleAssignments()
            ->whereHas('scope', fn ($q) => $q->where('kind', 'National'))
            ->exists();
    }

    /**
     * Get list of facility IDs the user is authorized for through active assignments.
     *
     * @return array<int>
     */
    public function assignedFacilityIds(): array
    {
        return $this->activeRoleAssignments()
            ->with('scope')
            ->get()
            ->map(fn ($assignment) => $assignment->scope?->facility_id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if the user can access a specific facility context.
     */
    public function canAccessFacility(?int $facilityId): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->isNational()) {
            return true;
        }

        if ($facilityId === null) {
            return count($this->assignedFacilityIds()) > 0;
        }

        return in_array($facilityId, $this->assignedFacilityIds(), true);
    }

    /**
     * Check if the user has a specific role (by slug or array of slugs).
     *
     * @param  string|array<string>  $roleSlugs
     */
    public function hasRole(string|array $roleSlugs): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $slugs = (array) $roleSlugs;

        return $this->activeRoleAssignments()
            ->whereHas('role', fn ($q) => $q->whereIn('slug', $slugs))
            ->exists();
    }

    /**
     * Check if the user has a permission, optionally scoped to a facility.
     */
    public function hasPermission(string $permissionSlug, ?int $facilityId = null): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $assignments = $this->activeRoleAssignments()->with(['role.permissions', 'scope'])->get();

        foreach ($assignments as $assignment) {
            $role = $assignment->role;
            if (! $role) {
                continue;
            }

            $hasPerm = $role->slug === 'system_admin'
                || $role->permissions->contains('slug', $permissionSlug);

            if (! $hasPerm) {
                continue;
            }

            $scope = $assignment->scope;
            if (! $scope) {
                continue;
            }

            if ($scope->kind === 'National') {
                return true;
            }

            if ($scope->kind === 'Facility') {
                if ($facilityId === null || $scope->facility_id === $facilityId) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @return list<int>|null Null means national access for this permission. */
    public function facilityIdsForPermission(string $permission): ?array
    {
        if (! $this->isActive()) {
            return [];
        }

        $facilityIds = [];
        foreach ($this->activeRoleAssignments()->with(['role.permissions', 'scope'])->get() as $assignment) {
            if (! $assignment->role || ! $assignment->scope
                || ($assignment->role->slug !== 'system_admin' && ! $assignment->role->permissions->contains('slug', $permission))) {
                continue;
            }
            if ($assignment->scope->kind === 'National') {
                return null;
            }
            if ($assignment->scope->kind === 'Facility' && $assignment->scope->facility_id !== null) {
                $facilityIds[] = (int) $assignment->scope->facility_id;
            }
        }

        return array_values(array_unique($facilityIds));
    }
}
