<?php

namespace App\Models;

use Database\Factories\CustodyLocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'location_ref',
    'admission_episode_id',
    'facility_id',
    'entered_at',
    'left_at',
    'entered_transfer_id',
    'left_transfer_id',
])]
#[Hidden(['open_admission_id'])]
class CustodyLocation extends Model
{
    /** @use HasFactory<CustodyLocationFactory> */
    use HasFactory, SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(AdmissionEpisode::class, 'admission_episode_id')->withTrashed();
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }

    public function enteringTransfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'entered_transfer_id')->withTrashed();
    }

    public function leavingTransfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'left_transfer_id')->withTrashed();
    }
}
