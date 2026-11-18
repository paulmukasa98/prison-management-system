<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Services\AuditService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', AuditEvent::class);
        $filters = $request->validate([
            'actor_id' => ['nullable', 'integer'], 'action' => ['nullable', 'string', 'max:128'],
            'resource_type' => ['nullable', 'string', 'max:64'], 'facility_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $query = AuditEvent::visibleTo($request->user());
        foreach (['actor_id', 'action', 'resource_type', 'facility_id'] as $filter) {
            $query->when($filters[$filter] ?? null, fn (Builder $query, mixed $value): Builder => $query->where($filter, $value));
        }
        if (! empty($filters['from'])) {
            $query->where('occurred_at', '>=', CarbonImmutable::parse($filters['from'], config('reporting.timezone'))->startOfDay()->utc());
        }
        if (! empty($filters['to'])) {
            $query->where('occurred_at', '<=', CarbonImmutable::parse($filters['to'], config('reporting.timezone'))->endOfDay()->utc());
        }

        return view('audit.index', ['events' => $query->with(['actor:id,name', 'facility:id,name'])->orderByDesc('occurred_at')->orderByDesc('id')->paginate(50)->withQueryString()]);
    }

    public function show(Request $request, int $id, AuditService $audit): View
    {
        $event = AuditEvent::visibleTo($request->user())->with(['actor:id,name', 'facility:id,name'])->findOrFail($id);
        Gate::authorize('view', $event);
        $audit->log(action: 'audit_event_viewed', outcome: 'Success', resourceType: 'AuditEvent', resourceReference: $event->audit_ref, facilityId: $event->facility_id);

        return view('audit.show', [
            'event' => $event, 'changes' => $audit->redact($event->changes ?? []),
            'signatureValid' => $event->integrity_hash === null ? null : hash_equals($event->integrity_hash, AuditService::signatureFor($event)),
        ]);
    }
}
