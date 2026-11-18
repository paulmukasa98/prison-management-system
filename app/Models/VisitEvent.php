<?php

namespace App\Models;

use Database\Factories\VisitEventFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_ref', 'visit_id', 'sequence', 'event_type', 'from_state', 'to_state', 'occurred_at', 'recorded_at', 'reason', 'actor_role'])]
class VisitEvent extends Model
{
    /** @use HasFactory<VisitEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new DomainException('Visit events are append-only.');
        });
        static::deleting(static function (): never {
            throw new DomainException('Visit events are append-only.');
        });
    }

    protected function casts(): array
    {
        return ['sequence' => 'integer', 'occurred_at' => 'immutable_datetime', 'recorded_at' => 'immutable_datetime'];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class)->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }
}
