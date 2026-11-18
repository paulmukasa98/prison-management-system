<?php

namespace App\Services;

use App\Models\AdmissionEpisode;
use App\Models\CustodyLocation;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Transfer;
use App\Models\TransferEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(
        protected AuditService $auditService,
        protected VisitService $visitService
    ) {}

    /**
     * Create and record a new inmate transfer request.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function createRequest(array $data, User $actor): Transfer
    {
        $inmate = Inmate::with('admissions.locations')->findOrFail($data['inmate_id']);

        $episode = $inmate->admissions()->whereNull('closed_at')->first();
        if (! $episode) {
            throw ValidationException::withMessages([
                'inmate_id' => 'The selected inmate does not have an active custody episode.',
            ]);
        }

        $originFacilityId = ! empty($data['origin_facility_id'])
            ? (int) $data['origin_facility_id']
            : (int) $inmate->currentFacilityId();

        $destinationFacilityId = Facility::findOrFail($data['destination_facility_id'])->id;

        if ($originFacilityId === $destinationFacilityId) {
            throw ValidationException::withMessages([
                'destination_facility_id' => 'Destination facility must be different from the origin facility.',
            ]);
        }

        if (! $actor->hasPermission('transfers.request', $originFacilityId)) {
            abort(403, 'Unauthorized. You do not hold transfer request authority for this origin facility.');
        }

        // Concurrency check: prevent multiple active transfers for the same episode
        $activeTransfer = Transfer::where('admission_episode_id', $episode->id)
            ->whereNotIn('state', ['Completed', 'Received', 'Rejected', 'Cancelled'])
            ->first();

        if ($activeTransfer) {
            throw ValidationException::withMessages([
                'transfer' => "An active transfer request ({$activeTransfer->transfer_ref}) is already in progress for this inmate.",
            ]);
        }

        return DB::transaction(function () use ($data, $episode, $originFacilityId, $destinationFacilityId, $actor, $inmate) {
            $episode = AdmissionEpisode::whereKey($episode->id)->lockForUpdate()->firstOrFail();
            $currentFacilityId = $episode->locations()->whereNull('left_at')->value('facility_id');
            if ($episode->closed_at !== null || (int) $currentFacilityId !== $originFacilityId) {
                throw ValidationException::withMessages(['origin_facility_id' => 'The origin must match the current custody location.']);
            }
            if ($episode->transfers()->whereNotIn('state', ['Completed', 'Received', 'Rejected', 'Cancelled'])->exists()) {
                throw ValidationException::withMessages(['transfer' => 'An active transfer already exists for this custody episode.']);
            }
            $transferRef = 'TRF-'.date('Y').'-'.strtoupper(Str::random(6));

            $transfer = Transfer::create([
                'transfer_ref' => $transferRef,
                'admission_episode_id' => $episode->id,
                'origin_facility_id' => $originFacilityId,
                'destination_facility_id' => $destinationFacilityId,
                'requesting_facility_id' => $originFacilityId,
                'state' => 'Pending Approval',
                'priority' => $data['priority'] ?? 'Routine',
                'reason' => $data['transfer_reason'],
                'requested_at' => now(),
                'proposed_on' => ! empty($data['proposed_transfer_date']) ? $data['proposed_transfer_date'] : null,
                'requested_by' => $actor->id,
                'notes' => $data['notes'] ?? null,
            ]);

            TransferEvent::create([
                'event_ref' => 'EVT-TRF-'.strtoupper(Str::random(8)),
                'transfer_id' => $transfer->id,
                'sequence' => 1,
                'event_type' => 'request',
                'from_state' => 'Draft',
                'to_state' => 'Pending Approval',
                'actor_id' => $actor->id,
                'facility_id' => $originFacilityId,
                'occurred_at' => now(),
                'recorded_at' => now(),
                'authority_reference' => $data['supporting_doc_ref'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditService->log(
                action: 'transfer_requested',
                outcome: 'Success',
                resourceType: 'Transfer',
                resourceReference: $transfer->transfer_ref,
                changes: [
                    'inmate_id' => $inmate->id,
                    'prisoner_number' => $inmate->prisoner_number,
                    'origin_facility_id' => $originFacilityId,
                    'destination_facility_id' => $destinationFacilityId,
                    'priority' => $transfer->priority,
                    'reason' => $transfer->reason,
                ],
                description: "Requested transfer for inmate {$inmate->prisoner_number} to destination facility.",
                facilityId: $originFacilityId,
                actorId: $actor->id
            );

            return $transfer;
        });
    }

    /**
     * Submit an authorization review decision for a transfer request.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function review(Transfer $transfer, array $data, User $actor): Transfer
    {
        if (! $actor->hasPermission('transfers.review', $transfer->origin_facility_id) && ! $actor->hasPermission('transfers.authorize', $transfer->origin_facility_id)) {
            abort(403, 'Unauthorized. You do not hold transfer review or authorization privileges.');
        }

        return DB::transaction(function () use ($transfer, $data, $actor) {
            /** @var Transfer $lockedTransfer */
            $lockedTransfer = Transfer::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedTransfer->state, ['Pending Approval', 'Awaiting Clarification', 'Draft'])) {
                throw ValidationException::withMessages([
                    'state' => "Transfer cannot be reviewed in its current state ({$lockedTransfer->state}).",
                ]);
            }

            $fromState = $lockedTransfer->state;
            $decision = $data['decision'];
            if ($decision === 'Approve' && ! $actor->hasPermission('transfers.authorize', $lockedTransfer->origin_facility_id)) {
                abort(403, 'Transfer approval requires authorization permission at the origin facility.');
            }
            $seq = ($lockedTransfer->events()->max('sequence') ?? 0) + 1;

            if ($decision === 'Approve') {
                $toState = 'Ready for Dispatch';
                $eventType = 'approve';
                $authRef = ! empty($data['auth_ref']) ? $data['auth_ref'] : 'AUTH-'.date('Y').'-'.strtoupper(Str::random(6));
            } elseif ($decision === 'Reject') {
                $toState = 'Rejected';
                $eventType = 'reject';
                $authRef = null;
            } else {
                $toState = 'Awaiting Clarification';
                $eventType = 'clarify';
                $authRef = null;
            }

            $lockedTransfer->update(['state' => $toState]);

            TransferEvent::create([
                'event_ref' => 'EVT-TRF-'.strtoupper(Str::random(8)),
                'transfer_id' => $lockedTransfer->id,
                'sequence' => $seq,
                'event_type' => $eventType,
                'from_state' => $fromState,
                'to_state' => $toState,
                'actor_id' => $actor->id,
                'facility_id' => $lockedTransfer->origin_facility_id,
                'occurred_at' => now(),
                'recorded_at' => now(),
                'authority_reference' => $authRef,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditService->log(
                action: 'transfer_'.strtolower($decision),
                outcome: 'Success',
                resourceType: 'Transfer',
                resourceReference: $lockedTransfer->transfer_ref,
                changes: [
                    'decision' => $decision,
                    'from_state' => $fromState,
                    'to_state' => $toState,
                    'auth_ref' => $authRef,
                    'notes' => $data['notes'] ?? null,
                ],
                description: "Transfer review decision '{$decision}' recorded for {$lockedTransfer->transfer_ref}.",
                facilityId: $lockedTransfer->origin_facility_id,
                actorId: $actor->id
            );

            return $lockedTransfer;
        });
    }

    /**
     * Record dispatch and handover of inmate for physical transit.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function dispatch(Transfer $transfer, array $data, User $actor): Transfer
    {
        if (! $actor->hasPermission('transfers.dispatch', $transfer->origin_facility_id)) {
            abort(403, 'Unauthorized. You do not hold dispatch authority for the origin facility.');
        }

        return DB::transaction(function () use ($transfer, $data, $actor) {
            $episode = AdmissionEpisode::whereKey($transfer->admission_episode_id)->lockForUpdate()->firstOrFail();
            /** @var Transfer $lockedTransfer */
            $lockedTransfer = Transfer::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedTransfer->state, ['Ready for Dispatch', 'Approved'])) {
                throw ValidationException::withMessages([
                    'state' => "Transfer cannot be dispatched in its current state ({$lockedTransfer->state}). Transfer must be approved first.",
                ]);
            }

            $dispatchDateStr = $data['dispatch_date'];
            $dispatchTimeStr = ! empty($data['dispatch_time']) ? $data['dispatch_time'] : now()->format('H:i:s');
            $dispatchDateTime = Carbon::parse("{$dispatchDateStr} {$dispatchTimeStr}");

            if ($lockedTransfer->requested_at && $dispatchDateTime->lt($lockedTransfer->requested_at)) {
                $dispatchDateTime = $lockedTransfer->requested_at->copy()->addSeconds(10);
            }

            // Close origin custody location
            $this->visitService->invalidateForEpisode($episode, $actor, 'Inmate transferred from the visit facility. Submit a new request at the receiving facility.');
            $episode->locations()->whereNull('left_at')->update([
                'left_at' => $dispatchDateTime,
                'left_transfer_id' => $lockedTransfer->id,
            ]);

            $fromState = $lockedTransfer->state;
            $toState = 'In Transit';
            $seq = ($lockedTransfer->events()->max('sequence') ?? 0) + 1;

            $lockedTransfer->update([
                'state' => $toState,
                'dispatched_at' => $dispatchDateTime,
            ]);

            TransferEvent::create([
                'event_ref' => 'EVT-TRF-'.strtoupper(Str::random(8)),
                'transfer_id' => $lockedTransfer->id,
                'sequence' => $seq,
                'event_type' => 'dispatch',
                'from_state' => $fromState,
                'to_state' => $toState,
                'actor_id' => $actor->id,
                'facility_id' => $lockedTransfer->origin_facility_id,
                'occurred_at' => $dispatchDateTime,
                'recorded_at' => now(),
                'authority_reference' => $data['handover_ref'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditService->log(
                action: 'transfer_dispatched',
                outcome: 'Success',
                resourceType: 'Transfer',
                resourceReference: $lockedTransfer->transfer_ref,
                changes: [
                    'dispatched_at' => $dispatchDateTime->toDateTimeString(),
                    'handover_ref' => $data['handover_ref'],
                    'escort_ref' => $data['escort_ref'] ?? null,
                ],
                description: "Escort dispatch recorded for transfer {$lockedTransfer->transfer_ref}.",
                facilityId: $lockedTransfer->origin_facility_id,
                actorId: $actor->id
            );

            return $lockedTransfer;
        });
    }

    /**
     * Confirm arrival and receipt of transferred inmate at destination facility.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function receive(Transfer $transfer, array $data, User $actor): Transfer
    {
        if (! $actor->hasPermission('transfers.receive', $transfer->destination_facility_id)) {
            abort(403, 'Unauthorized. You do not hold receiving authority for the destination facility.');
        }

        return DB::transaction(function () use ($transfer, $data, $actor) {
            $episode = AdmissionEpisode::whereKey($transfer->admission_episode_id)->lockForUpdate()->firstOrFail();
            if ($episode->closed_at !== null) {
                throw ValidationException::withMessages(['state' => 'A closed custody episode cannot be received.']);
            }
            /** @var Transfer $lockedTransfer */
            $lockedTransfer = Transfer::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedTransfer->state, ['In Transit', 'Arrival Confirmation Pending', 'Dispatched'])) {
                throw ValidationException::withMessages([
                    'state' => "Transfer cannot be received in its current state ({$lockedTransfer->state}). Transfer must be dispatched first.",
                ]);
            }

            $arrivalDateStr = $data['arrival_date'];
            $arrivalTimeStr = ! empty($data['arrival_time']) ? $data['arrival_time'] : now()->format('H:i:s');
            $arrivalDateTime = Carbon::parse("{$arrivalDateStr} {$arrivalTimeStr}");

            $minArrival = $lockedTransfer->dispatched_at ?? $lockedTransfer->requested_at;
            if ($minArrival && $arrivalDateTime->lt($minArrival)) {
                $arrivalDateTime = $minArrival->copy()->addSeconds(10);
            }

            $episode = $lockedTransfer->admission;

            // Close any lingering prior open locations
            $episode->locations()->whereNull('left_at')->update([
                'left_at' => $arrivalDateTime,
                'left_transfer_id' => $lockedTransfer->id,
            ]);

            // Open new custody location at destination facility
            CustodyLocation::create([
                'location_ref' => 'LOC-'.strtoupper(Str::random(8)),
                'admission_episode_id' => $episode->id,
                'facility_id' => $lockedTransfer->destination_facility_id,
                'entered_at' => $arrivalDateTime,
                'entered_transfer_id' => $lockedTransfer->id,
                'left_at' => null,
            ]);

            $fromState = $lockedTransfer->state;
            $toState = 'Completed';
            $seq = ($lockedTransfer->events()->max('sequence') ?? 0) + 1;

            $lockedTransfer->update([
                'state' => $toState,
                'received_at' => $arrivalDateTime,
            ]);

            TransferEvent::create([
                'event_ref' => 'EVT-TRF-'.strtoupper(Str::random(8)),
                'transfer_id' => $lockedTransfer->id,
                'sequence' => $seq,
                'event_type' => 'receive',
                'from_state' => $fromState,
                'to_state' => $toState,
                'actor_id' => $actor->id,
                'facility_id' => $lockedTransfer->destination_facility_id,
                'occurred_at' => $arrivalDateTime,
                'recorded_at' => now(),
                'authority_reference' => $data['arrival_ref'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->auditService->log(
                action: 'transfer_received',
                outcome: 'Success',
                resourceType: 'Transfer',
                resourceReference: $lockedTransfer->transfer_ref,
                changes: [
                    'received_at' => $arrivalDateTime->toDateTimeString(),
                    'destination_facility_id' => $lockedTransfer->destination_facility_id,
                    'arrival_ref' => $data['arrival_ref'],
                ],
                description: "Inmate arrival confirmed and custody established at destination facility for transfer {$lockedTransfer->transfer_ref}.",
                facilityId: $lockedTransfer->destination_facility_id,
                actorId: $actor->id
            );

            return $lockedTransfer;
        });
    }

    /**
     * Cancel a pending transfer.
     *
     * @throws ValidationException
     */
    public function cancel(Transfer $transfer, ?string $reason, User $actor): Transfer
    {
        $originId = $transfer->origin_facility_id;

        if (! $actor->hasPermission('transfers.request', $originId) && ! $actor->hasPermission('transfers.authorize', $originId)) {
            abort(403, 'Unauthorized. You do not hold cancellation privileges for this transfer.');
        }

        return DB::transaction(function () use ($transfer, $reason, $actor) {
            /** @var Transfer $lockedTransfer */
            $lockedTransfer = Transfer::where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedTransfer->state, ['Draft', 'Pending Approval', 'Awaiting Clarification', 'Approved', 'Ready for Dispatch'], true)) {
                throw ValidationException::withMessages([
                    'state' => 'Cannot cancel a transfer that is already dispatched or completed.',
                ]);
            }

            $fromState = $lockedTransfer->state;
            $toState = 'Cancelled';
            $seq = ($lockedTransfer->events()->max('sequence') ?? 0) + 1;

            $lockedTransfer->update(['state' => $toState]);

            TransferEvent::create([
                'event_ref' => 'EVT-TRF-'.strtoupper(Str::random(8)),
                'transfer_id' => $lockedTransfer->id,
                'sequence' => $seq,
                'event_type' => 'cancel',
                'from_state' => $fromState,
                'to_state' => $toState,
                'actor_id' => $actor->id,
                'facility_id' => $lockedTransfer->origin_facility_id,
                'occurred_at' => now(),
                'recorded_at' => now(),
                'notes' => $reason,
            ]);

            $this->auditService->log(
                action: 'transfer_cancelled',
                outcome: 'Success',
                resourceType: 'Transfer',
                resourceReference: $lockedTransfer->transfer_ref,
                changes: [
                    'from_state' => $fromState,
                    'to_state' => $toState,
                    'reason' => $reason,
                ],
                description: "Transfer {$lockedTransfer->transfer_ref} was cancelled.",
                facilityId: $lockedTransfer->origin_facility_id,
                actorId: $actor->id
            );

            return $lockedTransfer;
        });
    }

    /**
     * Format a Transfer model into array expected by Blade templates.
     *
     * @return array<string, mixed>
     */
    public function toViewArray(Transfer $transfer): array
    {
        $inmate = $transfer->admission?->inmate;
        $origin = $transfer->origin;
        $dest = $transfer->destination;

        $reviewEvent = $transfer->events->whereIn('event_type', ['approve', 'reject', 'clarify'])->sortByDesc('sequence')->first();
        $dispatchEvent = $transfer->events->firstWhere('event_type', 'dispatch');
        $receiveEvent = $transfer->events->firstWhere('event_type', 'receive');

        $status = $transfer->state;

        return [
            'id' => $transfer->id,
            'transfer_ref' => $transfer->transfer_ref,
            'inmate_id' => $inmate?->id ?? 1,
            'prisoner_number' => $inmate?->prisoner_number ?? 'PN-UNKNOWN',
            'inmate_name' => $inmate ? "{$inmate->first_name} {$inmate->last_name}" : 'Unknown Inmate',
            'classification' => $transfer->admission?->classification ?? 'Remand',
            'origin_facility' => $origin?->name ?? 'Origin Facility',
            'origin_facility_id' => $transfer->origin_facility_id,
            'destination_facility_id' => $transfer->destination_facility_id,
            'destination_facility' => $dest?->name ?? 'Destination Facility',
            'requesting_facility' => $transfer->requestingFacility?->name ?? ($origin?->name ?? 'Origin Facility'),
            'status' => $status,
            'priority' => $transfer->priority ?? 'Routine',
            'transfer_reason' => $transfer->reason ?? 'Administrative Reallocation',
            'notes' => $transfer->notes,
            'requesting_officer' => $transfer->requester?->name ?? 'Authorized Officer',
            'request_date' => $transfer->requested_at?->format('Y-m-d') ?? date('Y-m-d'),
            'proposed_transfer_date' => $transfer->proposed_on?->format('Y-m-d'),
            'supporting_doc_ref' => $transfer->events->first()?->authority_reference ?? '—',
            'review_status' => $reviewEvent ? ucfirst($reviewEvent->event_type).'d' : 'Pending',
            'reviewing_role' => $reviewEvent?->actor?->name ?? 'Reviewing Authority',
            'review_date' => $reviewEvent?->occurred_at?->format('Y-m-d'),
            'auth_ref' => $reviewEvent?->authority_reference,
            'review_notes' => $reviewEvent?->notes,
            'rejection_reason' => $reviewEvent?->event_type === 'reject' ? $reviewEvent->notes : null,
            'clarification_request' => $reviewEvent?->event_type === 'clarify' ? $reviewEvent->notes : null,
            'dispatch_date' => $transfer->dispatched_at?->format('Y-m-d H:i') ?? $dispatchEvent?->occurred_at?->format('Y-m-d H:i'),
            'dispatching_facility' => $origin?->name ?? 'Origin Facility',
            'dispatching_officer' => $dispatchEvent?->actor?->name ?? 'Escort Officer',
            'handover_ref' => $dispatchEvent?->authority_reference ?? '—',
            'escort_ref' => $dispatchEvent?->authority_reference,
            'departure_notes' => $dispatchEvent?->notes,
            'arrival_date' => $transfer->received_at?->format('Y-m-d H:i') ?? $receiveEvent?->occurred_at?->format('Y-m-d H:i'),
            'receiving_facility' => $dest?->name ?? 'Destination Facility',
            'receiving_officer' => $receiveEvent?->actor?->name ?? 'Receiving Officer',
            'arrival_ref' => $receiveEvent?->authority_reference ?? '—',
            'receipt_notes' => $receiveEvent?->notes,
            'created_date' => $transfer->created_at?->format('Y-m-d') ?? date('Y-m-d'),
            'updated_date' => $transfer->updated_at?->format('Y-m-d') ?? date('Y-m-d'),
            'closed_date' => in_array($status, ['Completed', 'Rejected', 'Cancelled']) ? $transfer->updated_at?->format('Y-m-d') : null,
        ];
    }
}
