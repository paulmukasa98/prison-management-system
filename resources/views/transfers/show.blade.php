@extends('layouts.app')

@section('title', $transfer['transfer_ref'] . ' — Transfer Details')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'route' => 'dashboard'],
    ['label' => 'Transfers', 'route' => 'transfers.index'],
    ['label' => $transfer['transfer_ref']],
]" class="mb-4"/>

@if (session('status'))
    <x-alert type="success" class="mb-5">
        {{ session('status') }}
    </x-alert>
@endif

@if (session('error'))
    <x-alert type="danger" class="mb-5">
        {{ session('error') }}
    </x-alert>
@endif

{{-- =====================================================================
     HEADER CARD
     ===================================================================== --}}
<div class="bg-brand-dark rounded-xl p-5 sm:p-6 mb-5 text-white">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-mono text-blue-300 text-sm font-semibold">{{ $transfer['transfer_ref'] }}</span>
                <x-transfer-status-badge :status="$transfer['status']"/>
                @if($transfer['priority'] !== 'Routine')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $transfer['priority'] }}</span>
                @endif
            </div>
            <h1 class="text-xl font-bold text-white mt-1">{{ $transfer['inmate_name'] }}</h1>
            <p class="text-sm text-white/70 mt-0.5">
                <span class="font-mono">{{ $transfer['prisoner_number'] }}</span>
                &middot; {{ $transfer['classification'] }}
            </p>
        </div>
        <div class="flex-shrink-0 text-sm text-white/70">
            <p class="mb-1">Requested: <span class="text-white font-medium">{{ \Carbon\Carbon::parse($transfer['request_date'])->format('d M Y') }}</span></p>
            @if($transfer['proposed_transfer_date'])
                <p>Proposed: <span class="text-white font-medium">{{ \Carbon\Carbon::parse($transfer['proposed_transfer_date'])->format('d M Y') }}</span></p>
            @endif
        </div>
    </div>

    {{-- Facility movement --}}
    <div class="mt-4 flex items-center gap-2 text-sm">
        <span class="text-white/70">{{ $transfer['origin_facility'] }}</span>
        <svg class="w-5 h-5 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="to" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
        </svg>
        <span class="text-white font-semibold">{{ $transfer['destination_facility'] }}</span>
    </div>

    {{-- Action row --}}
    <div class="mt-4 pt-4 border-t border-white/10 flex flex-wrap gap-2">
        @if($canViewInmate)
<a href="{{ route('inmates.show', $transfer['inmate_id']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            View Inmate Profile
        </a>
@endif
        @if(in_array($transfer['status'], ['Pending Approval', 'Awaiting Clarification']))
            @can('review', $transferModel)
<a href="{{ route('transfers.review', $transfer['id']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-300 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                Review Request
            </a>
@endcan
        @endif
        @if($transfer['status'] === 'Ready for Dispatch')
            @can('dispatch', $transferModel)
<a href="{{ route('transfers.dispatch', $transfer['id']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-indigo-300 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                Record Dispatch
            </a>
@endcan
        @endif
        @if(in_array($transfer['status'], ['In Transit', 'Dispatched', 'Arrival Confirmation Pending']))
            @can('receive', $transferModel)
<a href="{{ route('transfers.receive', $transfer['id']) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-purple-300 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                Confirm Arrival
            </a>
@endcan
        @endif
    </div>
</div>

{{-- =====================================================================
     CONTENT TABS
     ===================================================================== --}}
<div x-data="{ tab: 'overview' }">

    <div class="flex overflow-x-auto gap-0 border-b border-[#E2E8F0] mb-5" role="tablist">
        @foreach([
            ['key' => 'overview',  'label' => 'Overview'],
            ['key' => 'request',   'label' => 'Request Details'],
            ['key' => 'auth',      'label' => 'Authorization'],
            ['key' => 'dispatch',  'label' => 'Dispatch'],
            ['key' => 'receiving', 'label' => 'Receiving'],
            ['key' => 'timeline',  'label' => 'Timeline'],
        ] as $t)
        <button @click="tab = '{{ $t['key'] }}'" :aria-selected="tab === '{{ $t['key'] }}'" role="tab"
            class="flex-shrink-0 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
            :class="tab === '{{ $t['key'] }}' ? 'border-brand text-[#2563EB]' : 'border-transparent text-[#64748B] hover:text-[#0F172A]'">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Overview --}}
    <div x-show="tab === 'overview'" role="tabpanel">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-card>
                <x-section-heading title="Transfer Summary"/>
                <dl>
                    <x-detail-row label="Transfer Reference" mono>{{ $transfer['transfer_ref'] }}</x-detail-row>
                    <x-detail-row label="Status"><x-transfer-status-badge :status="$transfer['status']"/></x-detail-row>
                    <x-detail-row label="Reason">{{ $transfer['transfer_reason'] }}</x-detail-row>
                    <x-detail-row label="Priority">{{ $transfer['priority'] }}</x-detail-row>
                    <x-detail-row label="Request Date">{{ \Carbon\Carbon::parse($transfer['request_date'])->format('d M Y') }}</x-detail-row>
                    @if($transfer['proposed_transfer_date'])
                        <x-detail-row label="Proposed Transfer Date">{{ \Carbon\Carbon::parse($transfer['proposed_transfer_date'])->format('d M Y') }}</x-detail-row>
                    @endif
                    @if($transfer['closed_date'])
                        <x-detail-row label="Closed Date">{{ \Carbon\Carbon::parse($transfer['closed_date'])->format('d M Y') }}</x-detail-row>
                    @endif
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Facility Movement"/>
                <div class="py-4">
                    <x-facility-movement :from="$transfer['origin_facility']" :to="$transfer['destination_facility']" size="lg"/>
                </div>
                <dl class="mt-2">
                    <x-detail-row label="Requesting Facility">{{ $transfer['requesting_facility'] }}</x-detail-row>
                    <x-detail-row label="Requesting Officer">{{ $transfer['requesting_officer'] }}</x-detail-row>
                    @if($transfer['supporting_doc_ref'])
                        <x-detail-row label="Supporting Doc Ref" mono>{{ $transfer['supporting_doc_ref'] }}</x-detail-row>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>

    {{-- Request Details --}}
    <div x-show="tab === 'request'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Request Details"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Prisoner Number" mono>{{ $transfer['prisoner_number'] }}</x-detail-row>
                    <x-detail-row label="Inmate Name">{{ $transfer['inmate_name'] }}</x-detail-row>
                    <x-detail-row label="Classification"><x-custody-badge :classification="$transfer['classification']"/></x-detail-row>
                    <x-detail-row label="Origin Facility">{{ $transfer['origin_facility'] }}</x-detail-row>
                    <x-detail-row label="Destination Facility">{{ $transfer['destination_facility'] }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Transfer Reason">{{ $transfer['transfer_reason'] }}</x-detail-row>
                    <x-detail-row label="Priority">{{ $transfer['priority'] }}</x-detail-row>
                    <x-detail-row label="Proposed Transfer Date">
                        {{ $transfer['proposed_transfer_date'] ? \Carbon\Carbon::parse($transfer['proposed_transfer_date'])->format('d M Y') : '—' }}
                    </x-detail-row>
                    <x-detail-row label="Notes">{{ $transfer['notes'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Supporting Doc Ref" mono>{{ $transfer['supporting_doc_ref'] ?? '—' }}</x-detail-row>
                </dl>
            </div>
        </x-card>
    </div>

    {{-- Authorization --}}
    <div x-show="tab === 'auth'" role="tabpanel" style="display:none">
        @if($transfer['review_date'])
        <x-card>
            <x-section-heading title="Authorization Details"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Review Status">
                        <x-transfer-status-badge :status="$transfer['review_status'] ?? $transfer['status']"/>
                    </x-detail-row>
                    <x-detail-row label="Reviewing Role">{{ $transfer['reviewing_role'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Review Date">{{ \Carbon\Carbon::parse($transfer['review_date'])->format('d M Y') }}</x-detail-row>
                </dl>
                <dl>
                    @if($transfer['auth_ref'])
                        <x-detail-row label="Authorization Reference" mono>{{ $transfer['auth_ref'] }}</x-detail-row>
                    @endif
                    <x-detail-row label="Review Notes">{{ $transfer['review_notes'] ?? '—' }}</x-detail-row>
                    @if($transfer['rejection_reason'])
                        <x-detail-row label="Rejection Reason"><span class="text-[#DC2626]">{{ $transfer['rejection_reason'] }}</span></x-detail-row>
                    @endif
                    @if($transfer['clarification_request'])
                        <x-detail-row label="Clarification Request"><span class="text-amber-700">{{ $transfer['clarification_request'] }}</span></x-detail-row>
                    @endif
                </dl>
            </div>
        </x-card>
        @else
        <x-empty-state title="No authorization decision yet" description="This transfer has not yet been reviewed. Use the Review Request action to process it.">
            @if(in_array($transfer['status'], ['Pending Approval', 'Awaiting Clarification']))
                @can('review', $transferModel)
<x-button.primary href="{{ route('transfers.review', $transfer['id']) }}" size="sm">Review Request</x-button.primary>
@endcan
            @endif
        </x-empty-state>
        @endif
    </div>

    {{-- Dispatch --}}
    <div x-show="tab === 'dispatch'" role="tabpanel" style="display:none">
        @if($transfer['dispatch_date'])
        <x-card>
            <x-section-heading title="Dispatch Information"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Dispatch Date &amp; Time">{{ \Carbon\Carbon::parse($transfer['dispatch_date'])->format('d M Y, H:i') }}</x-detail-row>
                    <x-detail-row label="Dispatching Facility">{{ $transfer['dispatching_facility'] }}</x-detail-row>
                    <x-detail-row label="Dispatching Officer">{{ $transfer['dispatching_officer'] }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Handover Reference" mono>{{ $transfer['handover_ref'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Escort Reference" mono>{{ $transfer['escort_ref'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Departure Notes">{{ $transfer['departure_notes'] ?? '—' }}</x-detail-row>
                </dl>
            </div>
        </x-card>
        @else
        <x-empty-state title="Dispatch not yet recorded" description="Dispatch details will appear here after the inmate has been dispatched from the originating facility.">
            @if($transfer['status'] === 'Ready for Dispatch')
                @can('dispatch', $transferModel)
<x-button.primary href="{{ route('transfers.dispatch', $transfer['id']) }}" size="sm">Record Dispatch</x-button.primary>
@endcan
            @endif
        </x-empty-state>
        @endif
    </div>

    {{-- Receiving --}}
    <div x-show="tab === 'receiving'" role="tabpanel" style="display:none">
        @if($transfer['arrival_date'])
        <x-card>
            <x-section-heading title="Receiving Confirmation"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Arrival Date &amp; Time">{{ \Carbon\Carbon::parse($transfer['arrival_date'])->format('d M Y, H:i') }}</x-detail-row>
                    <x-detail-row label="Receiving Facility">{{ $transfer['receiving_facility'] }}</x-detail-row>
                    <x-detail-row label="Receiving Officer">{{ $transfer['receiving_officer'] ?? '—' }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Arrival Reference" mono>{{ $transfer['arrival_ref'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Receipt Notes">{{ $transfer['receipt_notes'] ?? '—' }}</x-detail-row>
                </dl>
            </div>
        </x-card>
        @else
        <x-empty-state title="Arrival not yet confirmed" description="Receiving confirmation will appear here once the destination facility confirms the inmate's arrival.">
            @if(in_array($transfer['status'], ['In Transit', 'Dispatched', 'Arrival Confirmation Pending']))
                @can('receive', $transferModel)
<x-button.primary href="{{ route('transfers.receive', $transfer['id']) }}" size="sm">Confirm Arrival</x-button.primary>
@endcan
            @endif
        </x-empty-state>
        @endif
    </div>

    {{-- Timeline --}}
    <div x-show="tab === 'timeline'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Transfer Timeline"/>
            <x-transfer-timeline :events="$timeline"/>
        </x-card>
    </div>

</div>



@if(in_array($transfer['status'], ['Draft','Pending Approval','Awaiting Clarification','Approved','Ready for Dispatch']))
@can('cancel', $transferModel)
<x-card class="mt-5"><x-section-heading title="Cancel Transfer"/>
    <form method="POST" action="{{ route('transfers.cancel', $transfer['id']) }}" class="space-y-4">@csrf
        <x-form.textarea name="notes" label="Reason for Cancellation" required/>
        <x-button.danger type="submit">Cancel Transfer</x-button.danger>
    </form>
</x-card>
@endcan
@endif
@endsection
