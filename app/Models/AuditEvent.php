<?php

namespace App\Models;

use App\Services\AuditService;
use Database\Factories\AuditEventFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['audit_ref', 'actor_id', 'facility_id', 'occurred_at', 'recorded_at', 'action', 'outcome', 'resource_type', 'resource_reference', 'correlation_id', 'description', 'changes'])]
#[Hidden(['changes', 'integrity_hash'])]
class AuditEvent extends Model
{
    /** @use HasFactory<AuditEventFactory> */
    use HasFactory;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(static function (AuditEvent $event): void {
            $event->integrity_hash = AuditService::signatureFor($event);
        });
        static::updating(static function (): never {
            throw new DomainException('Audit events are append-only.');
        });
        static::deleting(static function (): never {
            throw new DomainException('Audit events are append-only.');
        });
    }

    protected function casts(): array
    {
        return ['occurred_at' => 'immutable_datetime', 'recorded_at' => 'immutable_datetime', 'changes' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class)->withTrashed();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $ids = $user->facilityIdsForPermission('audit.view');

        return $ids === null ? $query : $query->whereIn('facility_id', $ids);
    }
}
