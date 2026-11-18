<?php

namespace App\Http\Controllers;

use App\Http\Requests\Visitor\StoreVisitorRequest;
use App\Http\Requests\Visitor\StoreVisitRequest;
use App\Http\Requests\Visitor\TransitionVisitRequest;
use App\Http\Requests\Visitor\UpdateVisitorRequest;
use App\Models\AdmissionEpisode;
use App\Models\Facility;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Services\VisitorService;
use App\Services\VisitService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VisitorController extends Controller
{
    public function __construct(protected VisitorService $visitorService, protected VisitService $visitService) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Visitor::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:Registered,Under Review,Blocked'],
        ]);
        $query = Visitor::visibleTo($request->user());
        $total = (clone $query)->count();
        $withVisits = (clone $query)->whereHas('visits', fn (Builder $visits): Builder => $visits->visibleTo($request->user()))->count();
        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(fn (Builder $query): Builder => $query->where('visitor_ref', 'like', $search)->orWhere('first_name', 'like', $search)->orWhere('last_name', 'like', $search));
        }
        $query->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('status', $status));
        $visitors = $query->with(['visits' => fn (HasMany $visits): HasMany => $visits->visibleTo($request->user())->with('facility')])
            ->orderBy('last_name')->orderBy('id')->paginate(25)->withQueryString();
        $visitors->through(fn (Visitor $visitor): array => $this->visitorService->toViewArray($visitor, $request->user()));

        return view('visitors.index', [
            'visitors' => $visitors, 'counts' => ['total' => $total, 'with_visits' => $withVisits],
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('register', Visitor::class);

        return view('visitors.create', ['facilities' => $this->facilities($request->user(), 'visitors.register')->get()]);
    }

    public function store(StoreVisitorRequest $request): RedirectResponse
    {
        $visitor = $this->visitorService->register($request->validated(), $request->user());

        return redirect()->route('visitors.show', $visitor->id)->with('status', 'Visitor registered under review. Each visit requires a separate approval.');
    }

    public function show(Request $request, int $id): View
    {
        $visitor = Visitor::visibleTo($request->user())->findOrFail($id);
        Gate::authorize('view', $visitor);
        $visits = $visitor->visits()->visibleTo($request->user())->with(['admission.inmate', 'facility', 'visitor'])
            ->orderByDesc('requested_at')->orderByDesc('id')->paginate(25)->withQueryString();
        $visits->through(fn (Visit $visit): array => $this->visitService->toViewArray($visit));

        return view('visitors.show', [
            'visitor' => $this->visitorService->toViewArray($visitor, $request->user()),
            'visitorModel' => $visitor, 'visits' => $visits,
        ]);
    }

    public function edit(Request $request, int $id): View
    {
        $visitor = Visitor::visibleTo($request->user())->findOrFail($id);
        Gate::authorize('update', $visitor);

        return view('visitors.edit', [
            'visitor' => $this->visitorService->toViewArray($visitor, $request->user(), true),
            'visitorModel' => $visitor,
        ]);
    }

    public function update(UpdateVisitorRequest $request, int $id): RedirectResponse
    {
        $visitor = Visitor::visibleTo($request->user())->findOrFail($id);
        $this->visitorService->update($visitor, $request->validated(), $request->user());

        return redirect()->route('visitors.show', $id)->with('status', 'Visitor details corrected and audited.');
    }

    public function redact(Request $request, int $id): RedirectResponse
    {
        $visitor = Visitor::visibleTo($request->user())->findOrFail($id);
        Gate::authorize('update', $visitor);
        $request->validate(['confirmed' => ['accepted']]);
        $this->visitorService->redact($visitor, $request->user());

        return redirect()->route('visitors.show', $id)->with('status', 'Identity and contact identifiers redacted. Visit history retained.');
    }

    public function records(Request $request): View
    {
        Gate::authorize('viewAny', Visitor::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'], 'facility_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:'.implode(',', Visit::STATES)],
            'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $query = Visit::visibleTo($request->user());
        $counts = (clone $query)->selectRaw('state, COUNT(*) AS total')->groupBy('state')->pluck('total', 'state')->all();
        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(fn (Builder $query): Builder => $query->where('visit_ref', 'like', $search)
                ->orWhereHas('visitor', fn (Builder $visitor): Builder => $visitor->where('first_name', 'like', $search)->orWhere('last_name', 'like', $search)->orWhere('visitor_ref', 'like', $search))
                ->orWhereHas('admission.inmate', fn (Builder $inmate): Builder => $inmate->where('first_name', 'like', $search)->orWhere('last_name', 'like', $search)->orWhere('prisoner_number', 'like', $search)));
        }
        $query->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('state', $status));
        $query->when($filters['facility_id'] ?? null, fn (Builder $query, int $facilityId): Builder => $query->where('facility_id', $facilityId));
        if (! empty($filters['from'])) {
            $query->where('scheduled_start_at', '>=', CarbonImmutable::parse($filters['from'], config('visitation.timezone'))->startOfDay()->utc());
        }
        if (! empty($filters['to'])) {
            $query->where('scheduled_start_at', '<=', CarbonImmutable::parse($filters['to'], config('visitation.timezone'))->endOfDay()->utc());
        }
        $visits = $query->with(['visitor', 'admission.inmate', 'facility'])->orderByDesc('requested_at')->orderByDesc('id')->paginate(25)->withQueryString();
        $visits->through(fn (Visit $visit): array => $this->visitService->toViewArray($visit));

        return view('visitors.records', [
            'visits' => $visits, 'counts' => $counts, 'statuses' => Visit::STATES,
            'facilities' => $this->facilities($request->user(), 'visitors.read')->get(),
        ]);
    }

    public function schedule(Request $request): View
    {
        Gate::authorize('schedule', Visitor::class);
        $request->validate(['visitor_search' => ['nullable', 'string', 'max:100'], 'inmate_search' => ['nullable', 'string', 'max:100']]);
        $facilityIds = $request->user()->facilityIdsForPermission('visits.schedule');
        $episodes = AdmissionEpisode::whereNull('closed_at')
            ->whereHas('locations', fn (Builder $query): Builder => $query->whereNull('left_at')->when($facilityIds !== null, fn (Builder $query): Builder => $query->whereIn('facility_id', $facilityIds)))
            ->with(['inmate', 'locations.facility'])
            ->when($request->filled('inmate_search'), fn (Builder $query): Builder => $query->whereHas('inmate', fn (Builder $inmate): Builder => $inmate->where(fn (Builder $inmate): Builder => $inmate->where('prisoner_number', 'like', '%'.$request->input('inmate_search').'%')->orWhere('first_name', 'like', '%'.$request->input('inmate_search').'%')->orWhere('last_name', 'like', '%'.$request->input('inmate_search').'%'))))
            ->orderBy('id')->limit(100)->get();
        $visitors = Visitor::visibleTo($request->user())->where('status', '!=', 'Blocked')
            ->when($request->filled('visitor_search'), fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query->where('visitor_ref', 'like', '%'.$request->input('visitor_search').'%')->orWhere('first_name', 'like', '%'.$request->input('visitor_search').'%')->orWhere('last_name', 'like', '%'.$request->input('visitor_search').'%')))
            ->orderBy('last_name')->orderBy('id')->limit(100)->get(['id', 'visitor_ref', 'first_name', 'last_name']);

        return view('visitors.schedule', [
            'visitors' => $visitors, 'episodes' => $episodes,
            'facilities' => $this->facilities($request->user(), 'visits.schedule')->where('status', 'Active')->get(),
        ]);
    }

    public function visitStore(StoreVisitRequest $request): RedirectResponse
    {
        $visit = $this->visitService->request($request->validated(), $request->user());

        return redirect()->route('visitors.visits.show', $visit->id)->with('status', 'Visit requested. It must be approved and scheduled before check-in.');
    }

    public function visitShow(Request $request, int $id): View
    {
        $visit = Visit::visibleTo($request->user())->with(['visitor', 'admission.inmate', 'facility', 'events.actor'])->findOrFail($id);
        Gate::authorize('view', $visit);

        return view('visitors.visit-show', [
            'visit' => $this->visitService->toViewArray($visit), 'visitModel' => $visit,
            'timeline' => $visit->events->map(fn ($event): array => [
                'event' => $event->to_state, 'timestamp' => $event->occurred_at,
                'detail' => ($event->actor?->name ?? 'Historical actor').' · '.($event->actor_role ?? 'Role not recorded').($event->reason ? ' · '.$event->reason : ''),
            ])->all(),
        ]);
    }

    public function review(Request $request, int $id): View
    {
        return $this->transitionForm($request, $id, 'review');
    }

    public function checkIn(Request $request, int $id): View
    {
        return $this->transitionForm($request, $id, 'check-in');
    }

    public function checkOut(Request $request, int $id): View
    {
        return $this->transitionForm($request, $id, 'check-out');
    }

    public function cancel(Request $request, int $id): View
    {
        return $this->transitionForm($request, $id, 'cancel');
    }

    public function transitionStore(TransitionVisitRequest $request, int $id): RedirectResponse
    {
        $action = match ($request->route()->getName()) {
            'visitors.visits.review.store' => $request->validated('decision') === 'Approve' ? 'approve' : 'deny',
            'visitors.visits.schedule.store' => 'schedule',
            'visitors.visits.check-in.store' => 'check-in',
            'visitors.visits.check-out.store' => 'check-out',
            default => 'cancel',
        };
        $visit = Visit::visibleTo($request->user())->findOrFail($id);
        $visit = $this->visitService->transition($visit, $action, $request->validated(), $request->user());

        return redirect()->route('visitors.visits.show', $id)->with('status', 'Visit is now '.$visit->state.'.');
    }

    private function transitionForm(Request $request, int $id, string $action): View
    {
        $visit = Visit::visibleTo($request->user())->findOrFail($id);
        Gate::authorize(in_array($action, ['check-in', 'check-out']) ? 'checkinout' : $action, $visit);

        return view('visitors.'.($action === 'review' ? 'review' : $action), [
            'visit' => $this->visitService->toViewArray($visit), 'visitModel' => $visit, 'action' => $action,
        ]);
    }

    private function facilities(User $user, string $permission): Builder
    {
        $ids = $user->facilityIdsForPermission($permission);

        return Facility::query()->when($ids !== null, fn (Builder $query): Builder => $query->whereIn('id', $ids))->orderBy('name');
    }
}
