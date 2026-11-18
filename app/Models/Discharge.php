<?php

namespace App\Models;

use Database\Factories\DischargeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'discharge_ref',
    'admission_episode_id',
    'legal_reference_id',
    'reason',
    'state',
    'authority_reference',
    'requested_at',
    'authorized_at',
    'executed_at',
    'requested_by',
    'authorized_by',
    'executed_by',
    'notes',
])]
class Discharge extends Model
{
    /** @use HasFactory<DischargeFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'authorized_at' => 'datetime',
            'executed_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionEpisode::class, 'admission_episode_id')->withTrashed();
    }

    public function legalReference(): BelongsTo
    {
        return $this->belongsTo(LegalReference::class)->withTrashed();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by')->withTrashed();
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by')->withTrashed();
    }
}
