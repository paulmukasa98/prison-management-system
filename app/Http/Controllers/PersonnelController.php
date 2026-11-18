<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personnel\StorePersonnelAssignmentRequest;
use App\Http\Requests\Personnel\StorePersonnelRequest;
use App\Http\Requests\Personnel\UpdatePersonnelRequest;
use App\Models\Facility;
use App\Models\Personnel;
use App\Models\PersonnelAssignment;
use App\Services\PersonnelService;
use App\Support\Demo\AssignmentDemoData;
use App\Support\Demo\PersonnelDemoData;
use App\Support\InterfacePermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function __construct(protected PersonnelService $service) {}

    public function index(Request $request, InterfacePermissions $permissions): View
    {
        Gate::authorize('viewAny', Personnel::class);
        $personnel = Personnel::visibleTo($request->user())->orderBy('last_name')->orderBy('id')->get()->map(function (Personnel $person) use ($permissions): array {
            $record = $this->service->toViewArray($person);

            return $record + ['can_manage' => $permissions->allows('personnel.manage', $record['facility_id'])];
        })->all();
        $assigned = count(array_filter($personnel, fn (array $person): bool => $person['facility_id'] !== null));

        return view('personnel.index', [
            'personnel' => $personnel, 'facilities' => Facility::visibleTo($request->user(), 'personnel.read')->pluck('name')->all(),
            'counts' => ['total' => count($personnel), 'active' => count(array_filter($personnel, fn (array $person): bool => $person['employment_status'] === 'Active')), 'assigned' => $assigned, 'unassigned' => count($personnel) - $assigned],
            'ranks' => PersonnelDemoData::RANKS, 'departments' => PersonnelDemoData::DEPARTMENTS, 'statuses' => PersonnelDemoData::EMPLOYMENT_STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('manage', Personnel::class);

        return view('personnel.create', ['facilities' => Facility::visibleTo($request->user(), 'personnel.manage')->pluck('name')->all(), 'ranks' => PersonnelDemoData::RANKS, 'departments' => PersonnelDemoData::DEPARTMENTS, 'statuses' => PersonnelDemoData::EMPLOYMENT_STATUSES]);
    }

    public function store(StorePersonnelRequest $request): RedirectResponse
    {
        $person = $this->service->create($request->validated(), $request->user());

        return redirect()->route('personnel.show', $person)->with('status', 'Personnel record registered.');
    }

    public function show(Request $request, int $id): View
    {
        $person = Personnel::visibleTo($request->user())->findOrFail($id);
        Gate::authorize('view', $person);
        $assignments = $this->assignmentQuery($request)->where('personnel_id', $id)->get()->map(fn (PersonnelAssignment $assignment): array => $this->service->toAssignmentViewArray($assignment))->all();

        return view('personnel.show', ['person' => $this->service->toViewArray($person), 'assignments' => $assignments, 'timeline' => []]);
    }

    public function edit(Request $request, int $id): View
    {
        $person = Personnel::visibleTo($request->user(), 'personnel.manage')->findOrFail($id);
        Gate::authorize('manage', $person);

        return view('personnel.edit', ['person' => $this->service->toViewArray($person), 'facilities' => Facility::visibleTo($request->user(), 'personnel.manage')->pluck('name')->all(), 'ranks' => PersonnelDemoData::RANKS, 'departments' => PersonnelDemoData::DEPARTMENTS]);
    }

    public function update(UpdatePersonnelRequest $request, int $id): RedirectResponse
    {
        $person = Personnel::visibleTo($request->user(), 'personnel.manage')->findOrFail($id);
        $this->service->update($person, $request->validated(), $request->user());

        return redirect()->route('personnel.show', $person)->with('status', 'Personnel details updated.');
    }

    public function assignments(Request $request): View
    {
        Gate::authorize('viewAny', Personnel::class);
        $assignments = $this->assignmentQuery($request)->get()->map(fn (PersonnelAssignment $assignment): array => $this->service->toAssignmentViewArray($assignment))->all();

        return view('personnel.assignments', [
            'assignments' => $assignments, 'facilities' => Facility::visibleTo($request->user(), 'personnel.read')->pluck('name')->all(), 'statuses' => AssignmentDemoData::STATUSES,
            'counts' => ['total' => count($assignments), 'active' => count(array_filter($assignments, fn (array $assignment): bool => $assignment['status'] === 'Active')), 'completed' => count(array_filter($assignments, fn (array $assignment): bool => $assignment['status'] === 'Completed')), 'suspended' => count(array_filter($assignments, fn (array $assignment): bool => $assignment['status'] === 'Suspended'))],
        ]);
    }

    public function assignmentCreate(Request $request): View
    {
        Gate::authorize('manage', Personnel::class);
        $personnel = Personnel::visibleTo($request->user(), 'personnel.manage')->orderBy('last_name')->get()->map(fn (Personnel $person): array => $this->service->toViewArray($person))->all();

        return view('personnel.assignment-create', ['personnel' => $personnel, 'facilities' => Facility::visibleTo($request->user(), 'personnel.manage')->pluck('name')->all(), 'roles' => AssignmentDemoData::ROLES, 'reasons' => AssignmentDemoData::REASONS]);
    }

    public function assignmentStore(StorePersonnelAssignmentRequest $request): RedirectResponse
    {
        $person = Personnel::visibleTo($request->user(), 'personnel.manage')->findOrFail($request->validated('personnel_id'));
        $this->service->assign($person, $request->validated(), $request->user());

        return redirect()->route('personnel.assignments')->with('status', 'Assignment recorded.');
    }

    public function assignmentEnd(Request $request, int $id): RedirectResponse
    {
        $assignment = $this->assignmentQuery($request, 'personnel.manage')->findOrFail($id);
        $data = $request->validate(['end_date' => ['nullable', 'date', 'after_or_equal:'.$assignment->start_date->toDateString()]]);
        $this->service->endAssignment($assignment, $data, $request->user());

        return redirect()->route('personnel.assignments')->with('status', 'Assignment concluded.');
    }

    public function assignmentHistory(Request $request, int $id): View
    {
        $person = Personnel::visibleTo($request->user())->findOrFail($id);
        $assignments = $this->assignmentQuery($request)->where('personnel_id', $id)->get()->map(fn (PersonnelAssignment $assignment): array => $this->service->toAssignmentViewArray($assignment))->all();

        return view('personnel.assignment-history', ['person' => $this->service->toViewArray($person), 'assignments' => $assignments]);
    }

    private function assignmentQuery(Request $request, string $permission = 'personnel.read'): Builder
    {
        $ids = $request->user()->facilityIdsForPermission($permission);

        return PersonnelAssignment::query()->when($ids !== null, fn (Builder $query): Builder => $query->whereIn('facility_id', $ids))->with(['personnel', 'facility'])->orderByDesc('start_date')->orderByDesc('id');
    }
}
