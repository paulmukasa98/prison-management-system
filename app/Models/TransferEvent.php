<?php

namespace App\Models;

use Database\Factories\TransferEventFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_ref', 'transfer_id', 'sequence', 'event_type', 'from_state', 'to_state', 'actor_id', 'facility_id', 'occurred_at', 'recorded_at', 'authority_reference', 'notes'])]
class TransferEvent extends Model
{
    /** @use HasFactory<TransferEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new DomainException('Transfer events are append-only.');
        });
        static::deleting(static function (): never {
            throw new DomainException('Transfer events are append-only.');
        });
    }

    protected function casts(): array
    {
        return ['sequence' => 'integer', 'occurred_at' => 'immutable_datetime', 'recorded_at' => 'immutable_datetime'];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class)->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }
}
