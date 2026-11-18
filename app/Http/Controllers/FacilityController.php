<?php

namespace App\Http\Controllers;

use App\Http\Requests\Facility\StoreFacilityCapacityRequest;
use App\Http\Requests\Facility\StoreFacilityRequest;
use App\Http\Requests\Facility\UpdateFacilityRequest;
use App\Models\AuditEvent;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\PersonnelAssignment;
use App\Services\FacilityService;
use App\Services\InmateService;
use App\Services\PersonnelService;
use App\Support\Demo\FacilityDemoData;
use App\Support\InterfacePermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function __construct(protected FacilityService $service, protected PersonnelService $personnelService, protected InmateService $inmateService) {}

    public function index(Request $request, InterfacePermissions $permissions): View
    {
        Gate::authorize('viewAny', Facility::class);
        $facilities = Facility::visibleTo($request->user())->orderBy('name')->get()->map(fn (Facility $facility): array => $this->service->toViewArray($facility) + [
            'can_manage' => $permissions->allows('facilities.manage', $facility->id),
        ])->all();

        return view('facilities.index', ['facilities' => $facilities, 'counts' => $this->totals($facilities), 'regions' => FacilityDemoData::REGIONS, 'types' => FacilityDemoData::TYPES, 'statuses' => FacilityDemoData::STATUSES]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('manage', Facility::class);
        abort_unless($request->user()->facilityIdsForPermission('facilities.manage') === null, 403);

        return view('facilities.create', ['regions' => FacilityDemoData::REGIONS, 'types' => FacilityDemoData::TYPES]);
    }

    public function store(StoreFacilityRequest $request): RedirectResponse
    {
        abort_unless($request->user()->facilityIdsForPermission('facilities.manage') === null, 403);
        $facility = $this->service->create($request->validated(), $request->user());

        return redirect()->route('facilities.show', $facility)->with('status', 'Facility registered.');
    }

    public function show(Request $request, int $id): View
    {
        $facility = Facility::visibleTo($request->user())->findOrFail($id);
        Gate::authorize('view', $facility);
        $inmates = Inmate::visibleTo($request->user())->whereHas('admissions.locations', fn (Builder $query): Builder => $query->where('facility_id', $id)->whereNull('left_at'))->orderBy('id')->get()->map(fn (Inmate $inmate): array => $this->inmateService->toViewArray($inmate))->all();
        $timeline = AuditEvent::visibleTo($request->user())->where('facility_id', $id)->latest('occurred_at')->limit(10)->get()->map(fn (AuditEvent $event): array => [
            'event' => $event->action, 'detail' => $event->resource_type.' · '.$event->resource_reference,
            'timestamp' => $event->occurred_at, 'type' => 'edit',
        ])->all();

        return view('facilities.show', ['facility' => $this->service->toViewArray($facility), 'inmates' => $inmates, 'personnel' => $this->personnel($request, $id), 'timeline' => $timeline]);
    }

    public function edit(Request $request, int $id): View
    {
        $facility = Facility::visibleTo($request->user(), 'facilities.manage')->findOrFail($id);
        Gate::authorize('manage', $facility);

        return view('facilities.edit', ['facility' => $this->service->toViewArray($facility), 'regions' => FacilityDemoData::REGIONS, 'types' => FacilityDemoData::TYPES]);
    }

    public function update(UpdateFacilityRequest $request, int $id): RedirectResponse
    {
        $facility = Facility::visibleTo($request->user(), 'facilities.manage')->findOrFail($id);
        DB::transaction(function () use ($request, $facility): void {
            $data = $request->validated();
            if (isset($data['capacity'])) {
                $this->service->updateCapacity($facility, [
                    'capacity' => $data['capacity'], 'effective_on' => $data['capacity_date'] ?? now()->toDateString(),
                    'source_reference' => $data['capacity_source'] ?? null, 'notes' => $data['capacity_reason'] ?? null,
                ], $request->user());
            }
            $this->service->update($facility, $data, $request->user());
        });

        return redirect()->route('facilities.show', $facility)->with('status', 'Facility updated.');
    }

    public function capacity(Request $request): View
    {
        Gate::authorize('viewAny', Facility::class);
        $facilities = Facility::visibleTo($request->user())->orderBy('name')->get()->map(fn (Facility $facility): array => $this->service->toViewArray($facility))->all();

        return view('facilities.capacity', [
            'facilities' => $facilities, 'totals' => $this->totals($facilities),
            'capacityJson' => json_encode(array_map(fn (array $facility): array => [
                'name' => $facility['name'], 'population' => $facility['current_population'],
                'capacity' => $facility['capacity'], 'occupancy' => $facility['occupancy_pct'],
            ], $facilities), JSON_THROW_ON_ERROR),
        ]);
    }

    public function capacityStore(StoreFacilityCapacityRequest $request, int $id): RedirectResponse
    {
        $facility = Facility::visibleTo($request->user(), 'facilities.manage')->findOrFail($id);
        $this->service->updateCapacity($facility, $request->validated(), $request->user());

        return redirect()->route('facilities.show', $facility)->with('status', 'Capacity revision recorded.');
    }

    public function personnelList(Request $request, int $id): View
    {
        $facility = Facility::visibleTo($request->user())->findOrFail($id);
        abort_unless($request->user()->hasPermission('personnel.read', $id), 403);

        return view('facilities.personnel', ['facility' => $this->service->toViewArray($facility), 'personnel' => $this->personnel($request, $id)]);
    }

    /** @return list<array<string, mixed>> */
    private function personnel(Request $request, int $facilityId): array
    {
        if (! $request->user()->hasPermission('personnel.read', $facilityId)) {
            return [];
        }

        return PersonnelAssignment::where('facility_id', $facilityId)->where('status', 'Active')
            ->where('start_date', '<=', now()->toDateString())
            ->where(fn (Builder $query): Builder => $query->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()))
            ->with(['personnel', 'facility'])->orderBy('id')->get()->map(fn (PersonnelAssignment $assignment): array => $this->personnelService->toAssignmentViewArray($assignment))->all();
    }

    /** @param list<array<string, mixed>> $facilities
     * @return array<string, int>
     */
    private function totals(array $facilities): array
    {
        return [
            'total' => count($facilities), 'active' => count(array_filter($facilities, fn (array $facility): bool => $facility['status'] === 'Active')),
            'population' => array_sum(array_column($facilities, 'current_population')),
            'capacity' => array_sum(array_column($facilities, 'capacity')),
        ];
    }
}
