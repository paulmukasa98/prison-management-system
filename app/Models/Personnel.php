<?php

namespace App\Models;

use Database\Factories\PersonnelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'staff_number',
    'first_name',
    'middle_name',
    'last_name',
    'gender',
    'date_of_birth',
    'nationality',
    'rank',
    'department',
    'employment_status',
    'appointed_on',
    'email',
    'phone',
    'kin_name',
    'kin_contact',
    'notes',
])]
#[Hidden(['phone', 'kin_contact', 'email', 'date_of_birth', 'notes'])]
class Personnel extends Model
{
    /** @use HasFactory<PersonnelFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'personnel';

    protected $attributes = [
        'employment_status' => 'Inactive',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'appointed_on' => 'date',
        ];
    }

    /** @return HasMany<PersonnelAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PersonnelAssignment::class);
    }

    public function currentAssignment(): ?PersonnelAssignment
    {
        return $this->assignments()
            ->where('status', 'Active')
            ->where('start_date', '<=', now()->toDateString())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()))
            ->latest('start_date')
            ->first();
    }

    public function currentFacilityId(): ?int
    {
        return $this->currentAssignment()?->facility_id;
    }

    /** @return HasOne<User, $this> */
    public function account(): HasOne
    {
        return $this->hasOne(User::class, 'personnel_id');
    }

    public function scopeVisibleTo(Builder $query, User $user, string $permission = 'personnel.read'): Builder
    {
        $ids = $user->facilityIdsForPermission($permission);

        return $ids === null ? $query : $query->whereHas('assignments', fn (Builder $assignments): Builder => $assignments
            ->whereIn('facility_id', $ids)->where('status', 'Active')->where('start_date', '<=', now()->toDateString())
            ->where(fn (Builder $assignments): Builder => $assignments->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString())));
    }
}
