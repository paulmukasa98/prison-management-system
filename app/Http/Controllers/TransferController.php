<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transfer\DispatchTransferRequest;
use App\Http\Requests\Transfer\ReceiveTransferRequest;
use App\Http\Requests\Transfer\ReviewTransferRequest;
use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Models\Facility;
use App\Models\Inmate;
use App\Models\Transfer;
use App\Services\InmateService;
use App\Services\TransferService;
use App\Support\Demo\TransferDemoData;
use App\Support\InterfacePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(Request $request, TransferService $service, InterfacePermissions $permissions): View
    {
        Gate::authorize('viewAny', Transfer::class);
        $transfers = Transfer::visibleTo($request->user())->with(['admission.inmate', 'origin', 'destination', 'requester', 'events.actor'])->latest('id')->get()->map(fn (Transfer $transfer): array => $service->toViewArray($transfer) + [
            'can_review' => $permissions->allows('transfers.review', $transfer->origin_facility_id) || $permissions->allows('transfers.authorize', $transfer->origin_facility_id),
            'can_dispatch' => $permissions->allows('transfers.dispatch', $transfer->origin_facility_id),
            'can_receive' => $permissions->allows('transfers.receive', $transfer->destination_facility_id),
        ])->all();
        $counts = [
            'total' => count($transfers),
            'pending' => count(array_filter($transfers, fn (array $transfer): bool => in_array($transfer['status'], ['Pending Approval', 'Awaiting Clarification', 'Draft'], true))),
            'in_transit' => count(array_filter($transfers, fn (array $transfer): bool => in_array($transfer['status'], ['In Transit', 'Arrival Confirmation Pending', 'Ready for Dispatch', 'Dispatched'], true))),
            'completed' => count(array_filter($transfers, fn (array $transfer): bool => $transfer['status'] === 'Completed')),
        ];

        return view('transfers.index', ['transfers' => $transfers, 'counts' => $counts, 'facilities' => Facility::visibleTo($request->user(), 'inmates.read')->pluck('name')->all(), 'statuses' => array_keys(TransferDemoData::STATUSES)]);
    }

    public function create(Request $request, InmateService $service): View
    {
        Gate::authorize('request', Transfer::class);
        $inmates = Inmate::visibleTo($request->user(), 'transfers.request')->whereHas('admissions', fn ($query) => $query->whereNull('closed_at'))->orderBy('id')->get()->map(fn (Inmate $inmate): array => $service->toViewArray($inmate))->all();

        return view('transfers.create', ['inmates' => $inmates, 'facilities' => Facility::where('status', 'Active')->pluck('name')->all(), 'reasons' => TransferDemoData::REASONS, 'priorities' => TransferDemoData::PRIORITIES]);
    }

    public function store(StoreTransferRequest $request, TransferService $service): RedirectResponse
    {
        $transfer = $service->createRequest($request->validated(), $request->user());

        return redirect()->route('transfers.show', $transfer)->with('status', 'Transfer request recorded.');
    }

    public function show(Request $request, int $id, TransferService $service): View
    {
        $transfer = $this->find($request, $id);
        Gate::authorize('view', $transfer);
        $timeline = $transfer->events->map(fn ($event): array => [
            'event' => $event->to_state, 'detail' => ($event->actor?->name ?? 'Historical actor').' · '.($event->notes ?? $event->event_type),
            'ts' => $event->occurred_at, 'type' => $event->event_type,
            'terminal' => in_array($event->to_state, ['Completed', 'Rejected', 'Cancelled'], true),
        ])->all();

        return view('transfers.show', [
            'transfer' => $service->toViewArray($transfer), 'transferModel' => $transfer, 'timeline' => $timeline,
            'canViewInmate' => $transfer->admission?->inmate && Gate::allows('view', $transfer->admission->inmate),
        ]);
    }

    public function review(Request $request, int $id, TransferService $service): View
    {
        return $this->form($request, $id, 'review', $service);
    }

    public function dispatch(Request $request, int $id, TransferService $service): View
    {
        return $this->form($request, $id, 'dispatch', $service);
    }

    public function receive(Request $request, int $id, TransferService $service): View
    {
        return $this->form($request, $id, 'receive', $service);
    }

    public function reviewStore(ReviewTransferRequest $request, int $id, TransferService $service): RedirectResponse
    {
        $transfer = $this->find($request, $id);
        Gate::authorize('review', $transfer);
        $service->review($transfer, $request->validated(), $request->user());

        return redirect()->route('transfers.show', $id)->with('status', 'Review recorded.');
    }

    public function dispatchStore(DispatchTransferRequest $request, int $id, TransferService $service): RedirectResponse
    {
        $transfer = $this->find($request, $id);
        Gate::authorize('dispatch', $transfer);
        $service->dispatch($transfer, $request->validated(), $request->user());

        return redirect()->route('transfers.show', $id)->with('status', 'Dispatch recorded.');
    }

    public function receiveStore(ReceiveTransferRequest $request, int $id, TransferService $service): RedirectResponse
    {
        $transfer = $this->find($request, $id);
        Gate::authorize('receive', $transfer);
        $service->receive($transfer, $request->validated(), $request->user());

        return redirect()->route('transfers.show', $id)->with('status', 'Receipt recorded.');
    }

    public function cancel(Request $request, int $id, TransferService $service): RedirectResponse
    {
        $transfer = $this->find($request, $id);
        Gate::authorize('cancel', $transfer);
        $data = $request->validate(['notes' => ['required', 'string', 'max:2000']]);
        $service->cancel($transfer, $data['notes'], $request->user());

        return redirect()->route('transfers.show', $id)->with('status', 'Transfer cancelled.');
    }

    public function history(Request $request, TransferService $service): View
    {
        Gate::authorize('viewAny', Transfer::class);
        $transfers = Transfer::visibleTo($request->user())->whereIn('state', ['Completed', 'Rejected', 'Cancelled'])
            ->with(['admission.inmate', 'origin', 'destination', 'requester', 'events.actor'])->latest('id')->get()->map(fn (Transfer $transfer): array => $service->toViewArray($transfer))->all();

        return view('transfers.history', ['transfers' => $transfers, 'facilities' => Facility::visibleTo($request->user(), 'inmates.read')->pluck('name')->all(), 'statuses' => ['Completed', 'Rejected', 'Cancelled']]);
    }

    private function find(Request $request, int $id): Transfer
    {
        return Transfer::visibleTo($request->user())->with(['admission.inmate', 'origin', 'destination', 'requester', 'events.actor'])->findOrFail($id);
    }

    private function form(Request $request, int $id, string $action, TransferService $service): View
    {
        $transfer = $this->find($request, $id);
        Gate::authorize($action, $transfer);

        return view('transfers.'.$action, ['transfer' => $service->toViewArray($transfer)]);
    }
}
