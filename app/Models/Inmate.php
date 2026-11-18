<?php

namespace App\Models;

use Database\Factories\InmateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'identity_ref',
    'prisoner_number',
    'first_name',
    'middle_name',
    'last_name',
    'gender',
    'date_of_birth',
    'nationality',
    'district',
    'id_type',
    'id_number',
    'kin_name',
    'kin_relationship',
    'kin_contact',
    'notes',
])]
#[Hidden(['id_number', 'kin_contact', 'date_of_birth', 'notes'])]
class Inmate extends Model
{
    /** @use HasFactory<InmateFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(AdmissionEpisode::class);
    }

    public function photoLink(): HasOne
    {
        return $this->hasOne(DocumentLink::class)->ofMany(
            ['id' => 'max'],
            fn (Builder $query): Builder => $query->where('purpose', DocumentLink::INMATE_PHOTO)
        );
    }

    /**
     * Get the current facility ID from the latest open custody location,
     * or fallback to admitting facility, or fallback to the latest historical episode if discharged.
     */
    public function currentFacilityId(): ?int
    {
        $openEpisode = $this->admissions()->whereNull('closed_at')->latest('admitted_at')->first();
        if ($openEpisode) {
            $locationFacilityId = $openEpisode->locations()->whereNull('left_at')->latest('entered_at')->value('facility_id');
            if ($locationFacilityId) {
                return (int) $locationFacilityId;
            }

            return (int) $openEpisode->admitting_facility_id;
        }

        $latestEpisode = $this->admissions()->latest('admitted_at')->first();
        if ($latestEpisode) {
            $locId = $latestEpisode->locations()->latest('entered_at')->value('facility_id')
                ?? $latestEpisode->admitting_facility_id;

            return $locId ? (int) $locId : null;
        }

        return null;
    }

    public function visitorRelationships(): HasMany
    {
        return $this->hasMany(VisitorRelationship::class);
    }

    public function scopeVisibleTo(Builder $query, User $user, string $permission = 'inmates.read'): Builder
    {
        $ids = $user->facilityIdsForPermission($permission);
        if ($ids === null) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($ids): void {
            $query->whereHas('admissions', fn (Builder $episodes): Builder => $episodes->whereNull('closed_at')
                ->whereHas('locations', fn (Builder $locations): Builder => $locations->whereNull('left_at')->whereIn('facility_id', $ids)))
                ->orWhere(function (Builder $released) use ($ids): void {
                    $released->whereDoesntHave('admissions', fn (Builder $episodes): Builder => $episodes->whereNull('closed_at'))
                        ->whereHas('admissions.locations', fn (Builder $locations): Builder => $locations->whereIn('facility_id', $ids));
                });
        });
    }
}
