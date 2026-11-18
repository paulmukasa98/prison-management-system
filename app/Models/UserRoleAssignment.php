<?php

namespace App\Models;

use Database\Factories\UserRoleAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['assignment_ref', 'user_id', 'role_id', 'access_scope_id', 'granted_by', 'granted_at', 'ended_at'])]
class UserRoleAssignment extends Model
{
    /** @use HasFactory<UserRoleAssignmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include active role assignments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return BelongsTo<AccessScope, $this> */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(AccessScope::class, 'access_scope_id');
    }

    /** @return BelongsTo<User, $this> */
    public function grantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by')->withTrashed();
    }
}
