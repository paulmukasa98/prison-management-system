<?php

namespace App\Models;

use Database\Factories\AdmissionEpisodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'admission_ref',
    'inmate_id',
    'admitting_facility_id',
    'admitted_at',
    'closed_at',
    'state',
    'classification',
    'legal_basis',
    'notes',
])]
#[Hidden(['open_inmate_id'])]
class AdmissionEpisode extends Model
{
    /** @use HasFactory<AdmissionEpisodeFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'admitted_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function inmate(): BelongsTo
    {
        return $this->belongsTo(Inmate::class)->withTrashed();
    }

    public function admittingFacility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'admitting_facility_id')->withTrashed();
    }

    public function legalReferences(): HasMany
    {
        return $this->hasMany(LegalReference::class);
    }

    public function discharges(): HasMany
    {
        return $this->hasMany(Discharge::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(CustodyLocation::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
