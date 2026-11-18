<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inmate\DischargeInmateRequest;
use App\Http\Requests\Inmate\ExportInmatesRequest;
use App\Http\Requests\Inmate\StoreInmateAdmissionRequest;
use App\Http\Requests\Inmate\UpdateInmateRequest;
use App\Models\AdmissionEpisode;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Transfer;
use App\Models\Visit;
use App\Services\AuditService;
use App\Services\CsvExportService;
use App\Services\InmatePhotoService;
use App\Services\InmateService;
use App\Services\TransferService;
use App\Services\VisitService;
use App\Support\InterfacePermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InmateController extends Controller
{
    public function export(ExportInmatesRequest $request, InmateService $service, AuditService $audit, CsvExportService $csv): StreamedResponse
    {
        Gate::authorize('viewAny', Inmate::class);
        abort_unless($request->user()->hasPermission('reports.export'), 403);
        abort_unless(config('reporting.csv_exports_enabled'), 409, 'CSV exports are disabled.');
        $filters = $request->validated();
        $readIds = $request->user()->facilityIdsForPermission('inmates.read');
        $exportIds = $request->user()->facilityIdsForPermission('reports.export');
        $facilityIds = $readIds === null ? $exportIds : ($exportIds === null ? $readIds : array_intersect($readIds, $exportIds));
        $inmates = Inmate::visibleTo($request->user())->visibleTo($request->user(), 'reports.export')
            ->with(['photoLink.document', 'admissions.admittingFacility', 'admissions.legalReferences', 'admissions.discharges', 'admissions.locations.facility'])
            ->orderBy('id')->get()->map(fn (Inmate $inmate): array => $service->toViewArray($inmate))
            ->filter(function (array $inmate) use ($filters, $facilityIds): bool {
                if ($facilityIds !== null && ! in_array($inmate['facility_id'], $facilityIds, true)) {
                    return false;
                }
                $search = mb_strtolower(trim($filters['search'] ?? ''));
                if ($search !== '' && ! str_contains(mb_strtolower($inmate['prisoner_number'].' '.$inmate['full_name'].' '.$inmate['first_name'].' '.$inmate['last_name']), $search)) {
                    return false;
                }
                foreach (['facility', 'classification', 'status', 'gender'] as $field) {
                    if (! empty($filters[$field]) && $inmate[$field] !== $filters[$field]) {
                        return false;
                    }
                }

                return true;
            })->sortBy(fn (array $inmate): string => mb_strtolower($inmate[$filters['sort'] ?? 'prisoner_number']), SORT_REGULAR, ($filters['direction'] ?? 'asc') === 'desc');
        $audit->log('inmate_directory_exported', 'Success', 'InmateDirectory', changes: ['record_count' => $inmates->count(), 'filter_fields' => array_keys($filters)], actorId: $request->user()->id);
        $rows = (function () use ($inmates): \Generator {
            yield ['Prisoner Number', 'Name', 'Gender', 'Facility', 'Classification', 'Status', 'Admission Date'];
            foreach ($inmates as $inmate) {
                yield array_map(fn (string $field): mixed => $inmate[$field], ['prisoner_number', 'full_name', 'gender', 'facility', 'classification', 'status', 'admission_date']);
            }
        })();

        return $csv->download('inmates-'.now()->format('Ymd-His').'.csv', $rows);
    }

    public function index(Request $request, InmateService $service, InterfacePermissions $permissions): View
    {
        Gate::authorize('viewAny', Inmate::class);
        $inmates = Inmate::visibleTo($request->user())->with('photoLink.document')->orderBy('last_name')->orderBy('id')->get()->map(function (Inmate $inmate) use ($service, $permissions): array {
            $record = $service->toViewArray($inmate);

            return $record + ['can_edit' => $permissions->allows('inmates.edit', $record['facility_id'])];
        })->all();
        $active = array_filter($inmates, fn (array $inmate): bool => in_array($inmate['status'], ['In Custody', 'Remanded'], true));
        $counts = [
            'total' => count($inmates), 'active' => count($active),
            'in_custody' => count(array_filter($inmates, fn (array $inmate): bool => $inmate['status'] === 'In Custody')),
            'remand' => count(array_filter($active, fn (array $inmate): bool => $inmate['classification'] === 'Remand')),
            'remanded' => count(array_filter($active, fn (array $inmate): bool => $inmate['status'] === 'Remanded')),
            'convicted' => count(array_filter($active, fn (array $inmate): bool => $inmate['classification'] === 'Convicted')),
            'released' => count($inmates) - count($active), 'discharged' => count($inmates) - count($active),
        ];

        return view('inmates.index', ['inmates' => $inmates, 'counts' => $counts, 'facilities' => Facility::visibleTo($request->user(), 'inmates.read')->pluck('name')->all()]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Inmate::class);

        return view('inmates.create', ['facilities' => Facility::visibleTo($request->user(), 'inmates.admit')->where('status', 'Active')->pluck('name')->all()]);
    }

    public function store(StoreInmateAdmissionRequest $request, InmateService $service, InmatePhotoService $photos): RedirectResponse
    {
        $inmate = $photos->save(
            fn (): Inmate => $service->admit($request->safe()->except('photo'), $request->user()),
            $request->file('photo'), $request->user()
        );

        return redirect()->route('inmates.show', $inmate)->with('status', 'Admission recorded.');
    }

    public function show(Request $request, int $id, InmateService $service, VisitService $visits, TransferService $transfers): View
    {
        $inmate = Inmate::visibleTo($request->user())->with('admissions.legalReferences')->findOrFail($id);
        Gate::authorize('view', $inmate);

        return view('inmates.show', [
            'inmate' => $service->toViewArray($inmate), 'admissions' => $this->episodes($inmate),
            'transfers' => Transfer::visibleTo($request->user())->whereHas('admission', fn (Builder $query): Builder => $query->where('inmate_id', $id))
                ->with(['admission.inmate', 'origin', 'destination', 'requester', 'events.actor'])->latest('id')->limit(25)->get()->map(fn (Transfer $transfer): array => $transfers->toViewArray($transfer))->all(),
            'visits' => Visit::visibleTo($request->user())->whereHas('admission', fn (Builder $query): Builder => $query->where('inmate_id', $id))
                ->with(['visitor', 'admission.inmate', 'facility'])->orderByDesc('requested_at')->limit(25)->get()->map(fn (Visit $visit): array => $visits->toViewArray($visit))->all(),
            'timeline' => [],
        ]);
    }

    public function edit(Request $request, int $id, InmateService $service): View
    {
        $inmate = Inmate::visibleTo($request->user(), 'inmates.edit')->findOrFail($id);
        Gate::authorize('update', $inmate);

        return view('inmates.edit', ['inmate' => $service->toViewArray($inmate)]);
    }

    public function update(UpdateInmateRequest $request, int $id, InmateService $service, InmatePhotoService $photos): RedirectResponse
    {
        $inmate = Inmate::visibleTo($request->user(), 'inmates.edit')->findOrFail($id);
        $photos->save(
            fn (): Inmate => $service->update($inmate, $request->safe()->except('photo'), $request->user()),
            $request->file('photo'), $request->user()
        );

        return redirect()->route('inmates.show', $id)->with('status', 'Inmate details updated.');
    }

    public function photo(Request $request, int $id, AuditService $audit): StreamedResponse
    {
        $inmate = Inmate::visibleTo($request->user())->with('photoLink.document')->findOrFail($id);
        Gate::authorize('view', $inmate);
        $document = $inmate->photoLink?->document;
        abort_unless($document && $document->state === 'Available' && $document->disk === 'local'
            && $document->mime_type === 'image/jpeg'
            && preg_match('/\Ainmate-photos\/[a-f0-9-]{36}\.jpg\z/', $document->storage_key ?? ''), 404);
        abort_unless(Storage::disk('local')->exists($document->storage_key), 404);
        $audit->log(
            action: 'inmate_photo_viewed', outcome: 'Success', resourceType: 'Inmate',
            resourceReference: $inmate->prisoner_number, changes: ['document_ref' => $document->document_ref],
            facilityId: $inmate->currentFacilityId(), actorId: $request->user()->id
        );

        return Storage::disk('local')->response($document->storage_key, 'inmate-photo.jpg', [
            'Content-Type' => 'image/jpeg', 'Cache-Control' => 'private, no-store',
        ]);
    }

    public function admissions(Request $request, int $id, InmateService $service): View
    {
        $inmate = Inmate::visibleTo($request->user())->with('admissions.legalReferences')->findOrFail($id);

        return view('inmates.admissions', ['inmate' => $service->toViewArray($inmate), 'episodes' => $this->episodes($inmate), 'timeline' => []]);
    }

    public function discharges(Request $request, InmateService $service): View
    {
        Gate::authorize('viewAny', Inmate::class);
        $inmates = Inmate::visibleTo($request->user())->whereHas('admissions', fn (Builder $query): Builder => $query->where('state', 'Discharged'))
            ->orderBy('id')->get()->map(fn (Inmate $inmate): array => $service->toViewArray($inmate))->all();

        return view('inmates.discharges', ['inmates' => $inmates, 'facilities' => Facility::visibleTo($request->user(), 'inmates.read')->pluck('name')->all()]);
    }

    public function discharge(Request $request, int $id, InmateService $service): View
    {
        $inmate = Inmate::visibleTo($request->user(), 'inmates.discharge')->findOrFail($id);
        Gate::authorize('discharge', $inmate);

        return view('inmates.discharge-initiate', ['inmate' => $service->toViewArray($inmate)]);
    }

    public function dischargeStore(DischargeInmateRequest $request, int $id, InmateService $service): RedirectResponse
    {
        $inmate = Inmate::visibleTo($request->user(), 'inmates.discharge')->findOrFail($id);
        $service->discharge($inmate, $request->validated(), $request->user());

        return redirect()->route('inmates.show', $id)->with('status', 'Discharge recorded.');
    }

    /** @return list<array<string, mixed>> */
    private function episodes(Inmate $inmate): array
    {
        return $inmate->admissions()->with(['admittingFacility', 'legalReferences'])->orderByDesc('admitted_at')->get()->map(fn (AdmissionEpisode $episode): array => [
            'admission_ref' => $episode->admission_ref, 'facility' => $episode->admittingFacility->name,
            'admitted_at' => $episode->admitted_at->format('Y-m-d H:i'), 'closed_at' => $episode->closed_at?->format('Y-m-d H:i'),
            'state' => $episode->state, 'classification' => $episode->classification,
            'status' => $episode->closed_at === null ? 'Current' : 'Completed',
            'admission_date' => $episode->admitted_at->toDateString(), 'legal_basis' => $episode->legal_basis,
            'discharge_date' => $episode->closed_at?->toDateString(), 'discharge_reason' => $episode->discharges()->latest('executed_at')->value('reason'),
            'warrant_ref' => $episode->legalReferences->first()?->external_reference ?? 'Not recorded',
        ])->all();
    }
}
