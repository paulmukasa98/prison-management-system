<?php

namespace App\Models;

use Database\Factories\TransferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'transfer_ref',
    'admission_episode_id',
    'origin_facility_id',
    'destination_facility_id',
    'requesting_facility_id',
    'state',
    'priority',
    'reason',
    'requested_at',
    'proposed_on',
    'dispatched_at',
    'received_at',
    'requested_by',
    'notes',
])]
class Transfer extends Model
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $ids = [];
        foreach (['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'] as $permission) {
            $permitted = $user->facilityIdsForPermission($permission);
            if ($permitted === null) {
                return $query;
            }
            $ids = [...$ids, ...$permitted];
        }

        return $query->where(fn (Builder $query): Builder => $query->whereIn('origin_facility_id', $ids)->orWhereIn('destination_facility_id', $ids));
    }

    /** @use HasFactory<TransferFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'proposed_on' => 'date',
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionEpisode::class, 'admission_episode_id')->withTrashed();
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'origin_facility_id')->withTrashed();
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'destination_facility_id')->withTrashed();
    }

    public function requestingFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'requesting_facility_id')->withTrashed();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    public function events(): HasMany
    {
        return $this->hasMany(TransferEvent::class)->orderBy('sequence');
    }
}
