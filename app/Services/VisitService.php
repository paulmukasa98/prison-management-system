<?php

namespace App\Services;

use App\Models\AdmissionEpisode;
use App\Models\Facility;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitEvent;
use App\Models\Visitor;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VisitService
{
    public function __construct(protected AuditService $auditService) {}

    /** @param array<string, mixed> $data */
    public function request(array $data, User $actor): Visit
    {
        $this->assertOperationalRules();
        $facilityId = (int) $data['facility_id'];
        abort_unless($actor->hasPermission('visits.schedule', $facilityId), 403);

        return DB::transaction(function () use ($data, $actor, $facilityId): Visit {
            $episode = AdmissionEpisode::whereKey($data['admission_episode_id'])
                ->whereHas('locations', fn (Builder $query): Builder => $query->where('facility_id', $facilityId)->whereNull('left_at'))
                ->lockForUpdate()->firstOrFail();
            $visitor = Visitor::visibleTo($actor)->whereKey($data['visitor_id'])->lockForUpdate()->firstOrFail();
            $this->assertEligible($episode, $visitor, $facilityId);
            $start = CarbonImmutable::parse($data['scheduled_start_at'], config('visitation.timezone'))->utc();
            $end = CarbonImmutable::parse($data['scheduled_end_at'], config('visitation.timezone'))->utc();
            $this->assertSlot($visitor->id, $episode->id, $start, $end);
            $visit = Visit::create([
                'visit_ref' => 'VIS-'.Str::uuid(), 'visitor_id' => $visitor->id,
                'admission_episode_id' => $episode->id, 'facility_id' => $facilityId,
                'purpose' => $data['purpose'], 'relationship' => $data['relationship'],
                'state' => 'Requested', 'requested_at' => now(),
                'scheduled_start_at' => $start, 'scheduled_end_at' => $end, 'notes' => $data['notes'] ?? null,
            ]);
            $visitor->relationships()->firstOrCreate(
                ['inmate_id' => $episode->inmate_id], ['relationship' => $data['relationship']]
            );
            $this->recordEvent($visit, null, 'Requested', 'request', $actor, null);

            return $visit;
        }, 3);
    }

    /** @param array<string, mixed> $data */
    public function transition(Visit $visit, string $action, array $data, User $actor): Visit
    {
        $ability = match ($action) {
            'approve', 'deny' => 'review', 'schedule' => 'schedule', 'cancel' => 'cancel',
            'check-in', 'check-out' => 'checkinout',
            default => abort(422, 'Unknown visit action.'),
        };
        Gate::forUser($actor)->authorize($ability, $visit);
        if (in_array($action, ['approve', 'schedule', 'check-in'], true)) {
            $this->assertOperationalRules();
        }

        return DB::transaction(function () use ($visit, $action, $data, $actor): Visit {
            $episode = AdmissionEpisode::whereKey($visit->admission_episode_id)->lockForUpdate()->firstOrFail();
            $visitor = Visitor::whereKey($visit->visitor_id)->lockForUpdate()->firstOrFail();
            $visit = Visit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
            [$allowedStates, $toState] = match ($action) {
                'approve' => [['Requested', 'Pending Review'], 'Approved'],
                'deny' => [['Requested', 'Pending Review', 'Approved'], 'Denied'],
                'schedule' => [['Approved'], 'Scheduled'],
                'cancel' => [Visit::OPEN_STATES, 'Cancelled'],
                'check-in' => [['Scheduled'], 'Checked In'],
                'check-out' => [['Checked In'], 'Completed'],
            };
            if (! in_array($visit->state, $allowedStates, true)) {
                throw ValidationException::withMessages(['state' => "Cannot {$action} a visit in {$visit->state} status."]);
            }
            $fromState = $visit->state;
            if (in_array($action, ['approve', 'schedule', 'check-in'], true)) {
                $this->assertEligible($episode, $visitor, $visit->facility_id);
                $this->assertSlot($visitor->id, $episode->id, CarbonImmutable::instance($visit->scheduled_start_at), CarbonImmutable::instance($visit->scheduled_end_at), $visit->id, $action === 'check-in');
            }
            if ($action === 'check-in') {
                if (config('visitation.require_identity_verification') && ! ($data['identity_verified'] ?? false)) {
                    throw ValidationException::withMessages(['identity_verified' => 'Confirm that the identity document was verified at reception.']);
                }
                if (now()->lt($visit->scheduled_start_at->copy()->subMinutes((int) config('visitation.check_in_early_minutes', 0))) || now()->gte($visit->scheduled_end_at)) {
                    throw ValidationException::withMessages(['state' => 'Check-in is only available during the configured visit window.']);
                }
                if (Visit::where('visitor_id', $visitor->id)->where('state', 'Checked In')->whereKeyNot($visit->id)->exists()) {
                    throw ValidationException::withMessages(['state' => 'This visitor is already checked in.']);
                }
                $visit->checked_in_at = now();
            }
            if ($action === 'check-out') {
                if (! $visit->checked_in_at || now()->lt($visit->checked_in_at)) {
                    throw ValidationException::withMessages(['state' => 'A valid earlier check-in is required.']);
                }
                $visit->checked_out_at = now();
            }
            $visit->state = $toState;
            $visit->save();
            $this->recordEvent($visit, $fromState, $toState, $action, $actor, $data['reason']);

            return $visit;
        }, 3);
    }

    public function invalidateForEpisode(AdmissionEpisode $episode, User $actor, string $reason): void
    {
        if ($episode->visits()->where('state', 'Checked In')->exists()) {
            throw ValidationException::withMessages(['visits' => 'Check out all visitors before moving or discharging this inmate.']);
        }
        foreach ($episode->visits()->whereIn('state', Visit::OPEN_STATES)->orderBy('id')->lockForUpdate()->get() as $visit) {
            $fromState = $visit->state;
            $visit->update(['state' => 'Cancelled']);
            $this->recordEvent($visit, $fromState, 'Cancelled', 'custody-change', $actor, $reason);
        }
    }

    public function assertOperationalRules(): void
    {
        if (app()->isProduction() && ! config('visitation.rules_confirmed')) {
            throw ValidationException::withMessages(['visitation' => 'Visitation operations require confirmed institutional rules in production.']);
        }
    }

    private function assertEligible(AdmissionEpisode $episode, Visitor $visitor, int $facilityId): void
    {
        $location = $episode->locations()->whereNull('left_at')->first();
        if ($episode->closed_at !== null || ! $location || (int) $location->facility_id !== $facilityId) {
            throw ValidationException::withMessages(['admission_episode_id' => 'The inmate is no longer in custody at this facility. Submit a new request for their current custody location.']);
        }
        if ($visitor->status === 'Blocked') {
            throw ValidationException::withMessages(['visitor_id' => 'This visitor record is blocked.']);
        }
        if (Facility::findOrFail($facilityId)->status !== 'Active') {
            throw ValidationException::withMessages(['facility_id' => 'Visits require an active facility.']);
        }
    }

    private function assertSlot(int $visitorId, int $episodeId, CarbonImmutable $start, CarbonImmutable $end, ?int $exceptId = null, bool $checkingIn = false): void
    {
        if ($end->lte($start) || (! $checkingIn && $start->lt(now()))) {
            throw ValidationException::withMessages(['scheduled_start_at' => 'Choose a future visit window with an end after the start.']);
        }
        $hours = config('visitation.visiting_hours');
        $localStart = $start->setTimezone(config('visitation.timezone'));
        $localEnd = $end->setTimezone(config('visitation.timezone'));
        if (is_array($hours) && ($localStart->format('Y-m-d') !== $localEnd->format('Y-m-d') || $localStart->format('H:i') < $hours['start'] || $localEnd->format('H:i') > $hours['end'])) {
            throw ValidationException::withMessages(['scheduled_start_at' => 'The visit falls outside configured visiting hours.']);
        }
        $conflicts = Visit::whereIn('state', [...Visit::OPEN_STATES, 'Checked In'])
            ->when($exceptId, fn (Builder $query): Builder => $query->whereKeyNot($exceptId))
            ->where('scheduled_start_at', '<', $end)->where('scheduled_end_at', '>', $start)
            ->where(function (Builder $query) use ($visitorId, $episodeId): void {
                $query->whereRaw('1 = 0');
                if (config('visitation.prevent_visitor_overlap')) {
                    $query->orWhere('visitor_id', $visitorId);
                }
                if (config('visitation.prevent_inmate_overlap')) {
                    $query->orWhere('admission_episode_id', $episodeId);
                }
            })->exists();
        if ($conflicts) {
            throw ValidationException::withMessages(['scheduled_start_at' => 'This visitor or inmate already has an overlapping visit.']);
        }
        $limit = config('visitation.max_visits_per_inmate_per_day');
        if ($limit !== null && Visit::where('admission_episode_id', $episodeId)->whereNotIn('state', ['Cancelled', 'Denied', 'No-Show'])
            ->whereBetween('scheduled_start_at', [$localStart->startOfDay()->utc(), $localStart->endOfDay()->utc()])
            ->when($exceptId, fn (Builder $query): Builder => $query->whereKeyNot($exceptId))->count() >= $limit) {
            throw ValidationException::withMessages(['scheduled_start_at' => 'The configured daily visit limit has been reached.']);
        }
    }

    private function recordEvent(Visit $visit, ?string $fromState, string $toState, string $action, User $actor, ?string $reason): void
    {
        $event = new VisitEvent([
            'event_ref' => 'VE-'.Str::uuid(), 'visit_id' => $visit->id,
            'sequence' => (int) $visit->events()->max('sequence') + 1,
            'event_type' => $action, 'from_state' => $fromState, 'to_state' => $toState,
            'occurred_at' => now(), 'recorded_at' => now(), 'reason' => $reason,
            'actor_role' => $actor->primaryRoleName(),
        ]);
        $event->actor_id = $actor->id;
        $event->save();
        $this->auditService->log(
            action: 'visit_'.str_replace('-', '_', $action), outcome: 'Success', resourceType: 'Visit',
            resourceReference: $visit->visit_ref,
            changes: ['from_state' => $fromState, 'to_state' => $toState, 'event_ref' => $event->event_ref, 'actor_role' => $event->actor_role],
            facilityId: $visit->facility_id, actorId: $actor->id
        );
    }

    /** @return array<string, mixed> */
    public function toViewArray(Visit $visit): array
    {
        $visit->loadMissing(['visitor', 'admission.inmate', 'facility']);
        $visitor = $visit->visitor;
        $inmate = $visit->admission->inmate;
        $start = $visit->scheduled_start_at?->copy()->setTimezone(config('visitation.timezone'));
        $end = $visit->scheduled_end_at?->copy()->setTimezone(config('visitation.timezone'));

        return [
            'id' => $visit->id, 'visit_ref' => $visit->visit_ref,
            'visitor_id' => $visitor->id, 'visitor_ref' => $visitor->visitor_ref,
            'visitor_name' => trim($visitor->first_name.' '.$visitor->last_name),
            'inmate_id' => $inmate->id, 'inmate_name' => trim($inmate->first_name.' '.$inmate->last_name),
            'prisoner_number' => $inmate->prisoner_number, 'facility' => $visit->facility->name,
            'facility_id' => $visit->facility_id, 'status' => $visit->state,
            'purpose' => $visit->purpose, 'relationship' => $visit->relationship,
            'visit_date' => $start?->toDateString(), 'start_time' => $start?->format('H:i') ?? 'Unscheduled',
            'duration_mins' => $start && $end ? (int) $start->diffInMinutes($end) : null,
            'request_date' => $visit->requested_at?->toDateString(),
            'scheduled_date' => null,
            'check_in_time' => $visit->checked_in_at?->copy()->setTimezone(config('visitation.timezone'))->format('Y-m-d H:i'),
            'check_out_time' => $visit->checked_out_at?->copy()->setTimezone(config('visitation.timezone'))->format('Y-m-d H:i'),
            'notes' => $visit->notes, 'conflicts' => [],
        ];
    }
}
