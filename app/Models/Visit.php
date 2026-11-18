<?php

namespace App\Models;

use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'visit_ref',
    'visitor_id',
    'admission_episode_id',
    'facility_id',
    'purpose',
    'relationship',
    'state',
    'requested_at',
    'scheduled_start_at',
    'scheduled_end_at',
    'checked_in_at',
    'checked_out_at',
    'notes',
])]
class Visit extends Model
{
    public const OPEN_STATES = ['Requested', 'Pending Review', 'Approved', 'Scheduled'];

    public const STATES = ['Requested', 'Pending Review', 'Approved', 'Scheduled', 'Checked In', 'Completed', 'Cancelled', 'Denied', 'No-Show'];

    /** @use HasFactory<VisitFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class)->withTrashed();
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionEpisode::class, 'admission_episode_id')->withTrashed();
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }

    public function events(): HasMany
    {
        return $this->hasMany(VisitEvent::class)->orderBy('sequence');
    }

    public function scopeVisibleTo(Builder $query, User $user, string $permission = 'visitors.read'): Builder
    {
        $facilityIds = $user->facilityIdsForPermission($permission);

        return $facilityIds === null ? $query : $query->whereIn('facility_id', $facilityIds);
    }
}
