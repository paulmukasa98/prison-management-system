<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'code',
    'name',
    'type',
    'region',
    'district',
    'location_description',
    'status',
    'established_on',
    'notes',
])]
class Facility extends Model
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'status' => 'Inactive',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'established_on' => 'date',
        ];
    }

    /** @return HasMany<FacilityCapacity, $this> */
    public function capacities(): HasMany
    {
        return $this->hasMany(FacilityCapacity::class);
    }

    /** @return HasMany<PersonnelAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PersonnelAssignment::class);
    }

    /** @return HasMany<AdmissionEpisode, $this> */
    public function admissions(): HasMany
    {
        return $this->hasMany(AdmissionEpisode::class, 'admitting_facility_id');
    }

    /** @return HasMany<CustodyLocation, $this> */
    public function locations(): HasMany
    {
        return $this->hasMany(CustodyLocation::class);
    }

    /** @return HasMany<Visit, $this> */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function currentCapacity(): ?int
    {
        $cap = $this->capacities()
            ->where('effective_on', '<=', now()->toDateString())
            ->orderByDesc('effective_on')
            ->value('capacity');

        return $cap !== null ? (int) $cap : null;
    }

    public function scopeVisibleTo(Builder $query, User $user, string $permission = 'facilities.read'): Builder
    {
        $ids = $user->facilityIdsForPermission($permission);

        return $ids === null ? $query : $query->whereIn('id', $ids);
    }

    public function activePopulation(): int
    {
        return $this->locations()->whereNull('left_at')->count();
    }

    public function assignedPersonnelCount(): int
    {
        return $this->assignments()
            ->where('status', 'Active')
            ->where('start_date', '<=', now()->toDateString())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()))
            ->count();
    }
}
