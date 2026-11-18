<?php

namespace App\Models;

use Database\Factories\AccessScopeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['scope_key', 'kind', 'facility_id'])]
class AccessScope extends Model
{
    /** @use HasFactory<AccessScopeFactory> */
    use HasFactory;

    /** @return BelongsTo<Facility, $this> */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }

    /** @return HasMany<UserRoleAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }
}
