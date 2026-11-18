<?php

namespace App\Models;

use Database\Factories\VisitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'visitor_ref',
    'first_name',
    'middle_name',
    'last_name',
    'gender',
    'id_type',
    'id_number',
    'phone',
    'email',
    'residence',
    'status',
    'registered_at',
    'notes',
    'registered_facility_id',
])]
#[Hidden(['id_number', 'identity_fingerprint', 'phone', 'email', 'residence', 'notes'])]
class Visitor extends Model
{
    /** @use HasFactory<VisitorFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'id_number' => 'encrypted',
        ];
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(VisitorRelationship::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function scopeVisibleTo(Builder $query, User $user, string $permission = 'visitors.read'): Builder
    {
        $facilityIds = $user->facilityIdsForPermission($permission);

        return $facilityIds === null ? $query : $query->where(function (Builder $query) use ($facilityIds): void {
            $query->whereIn('registered_facility_id', $facilityIds)
                ->orWhereHas('visits', fn (Builder $visits): Builder => $visits->whereIn('facility_id', $facilityIds));
        });
    }

    public function maskedIdentityNumber(): string
    {
        if (! $this->id_number) {
            return 'Not presented';
        }

        return '••••'.mb_substr($this->id_number, -4);
    }
}
