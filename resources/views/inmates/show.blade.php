@extends('layouts.app')

@section('title', $inmate['full_name'] . ' — Inmate Profile')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Dashboard', 'route' => 'dashboard'],
    ['label' => 'Inmates',   'route' => 'inmates.index'],
    ['label' => $inmate['prisoner_number']],
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
     PROFILE HEADER CARD
     ===================================================================== --}}
<div class="bg-brand-dark rounded-xl p-5 sm:p-6 mb-5 text-white">
    <div class="flex flex-col sm:flex-row gap-5">
        {{-- Avatar --}}
        <div class="flex-shrink-0">
            <div class="w-20 h-24 sm:w-24 sm:h-28 rounded-xl bg-[#183B56] border-2 border-white/20 flex items-center justify-center overflow-hidden">
                @if($inmate['photo'])
                    <img src="{{ $inmate['photo'] }}" alt="Inmate photograph" class="w-full h-full object-cover">
                @else
                    <span class="text-3xl font-bold text-white/70">{{ $inmate['initials'] }}</span>
                @endif
            </div>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-blue-300 font-mono font-semibold mb-1">{{ $inmate['prisoner_number'] }}</p>
                    <h1 class="text-xl font-bold text-white">{{ $inmate['full_name'] }}</h1>
                    <p class="text-sm text-white/70 mt-0.5">{{ $inmate['gender'] }} &middot; Age {{ $inmate['age'] }} &middot; {{ $inmate['district'] }}, Uganda</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-inmate-status-badge :status="$inmate['status']"/>
                    <x-custody-badge :classification="$inmate['classification']"/>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-6 text-sm">
                <div>
                    <p class="text-xs text-white/50 mb-0.5">Facility</p>
                    <p class="text-white/90 font-medium truncate">{{ $inmate['facility'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/50 mb-0.5">Admitted</p>
                    <p class="text-white/90 font-medium">{{ \Carbon\Carbon::parse($inmate['admission_date'])->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/50 mb-0.5">Admission Ref</p>
                    <p class="text-white/90 font-mono text-xs">{{ $inmate['admission_ref'] }}</p>
                </div>
                @if($inmate['expected_release'])
                <div>
                    <p class="text-xs text-white/50 mb-0.5">Expected Release</p>
                    <p class="text-white/90 font-medium">{{ \Carbon\Carbon::parse($inmate['expected_release'])->format('d M Y') }}</p>
                </div>
                @endif
                @if($inmate['next_court_date'])
                <div>
                    <p class="text-xs text-white/50 mb-0.5">Next Court Date</p>
                    <p class="text-amber-300 font-medium">{{ \Carbon\Carbon::parse($inmate['next_court_date'])->format('d M Y') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Action row --}}
    <div class="mt-5 pt-4 border-t border-white/10 flex flex-wrap gap-2">
        @permission('inmates.edit', $inmate['facility_id'])
<a href="{{ route('inmates.edit', $inmate['id']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
            Edit Details
        </a>
@endpermission
        <a href="{{ route('inmates.admissions', $inmate['id']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Admission History
        </a>
        @permission('transfers.request', $inmate['facility_id'])
<a href="{{ route('transfers.create', ['inmate_id' => $inmate['id']]) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-300 bg-white/10 hover:bg-white/20 rounded-lg transition-colors"
           title="Transfer workflows now available">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Initiate Transfer
        </a>
@endpermission
        @if(in_array($inmate['status'], ['In Custody', 'Remanded']))
        @permission('inmates.discharge', $inmate['facility_id'])
<a href="{{ route('inmates.discharge', $inmate['id']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-red-300 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Initiate Discharge
        </a>
@endpermission
        @endif
    </div>
</div>

{{-- =====================================================================
     PROFILE TABS
     ===================================================================== --}}
<div x-data="{ tab: 'overview' }">

    {{-- Tab nav --}}
    <div class="flex overflow-x-auto gap-0 border-b border-[#E2E8F0] mb-5 -mx-0.5 no-print" role="tablist">
        @foreach([
            ['key' => 'overview',   'label' => 'Overview'],
            ['key' => 'personal',   'label' => 'Personal Details'],
            ['key' => 'legal',      'label' => 'Legal & Custody'],
            ['key' => 'admissions', 'label' => 'Admission History'],
            ['key' => 'transfers',  'label' => 'Transfers'],
            ['key' => 'visits',     'label' => 'Visits'],
            ['key' => 'activity',   'label' => 'Activity History'],
        ] as $t)
        @continue($t['key'] === 'transfers' && !app(\App\Support\InterfacePermissions::class)->any(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive']))
        @continue($t['key'] === 'visits' && !app(\App\Support\InterfacePermissions::class)->allows('visitors.read'))
        <button
            @click="tab = '{{ $t['key'] }}'"
            :aria-selected="tab === '{{ $t['key'] }}'"
            role="tab"
            :aria-controls="'tab-{{ $t['key'] }}'"
            class="flex-shrink-0 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand"
            :class="tab === '{{ $t['key'] }}' ? 'border-brand text-[#2563EB]' : 'border-transparent text-[#64748B] hover:text-[#0F172A] hover:border-[#E2E8F0]'"
        >{{ $t['label'] }}</button>
        @endforeach
    </div>

    {{-- Overview --}}
    <div x-show="tab === 'overview'" id="tab-overview" role="tabpanel">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-card>
                <x-section-heading title="Current Custody Status"/>
                <dl>
                    <x-detail-row label="Prisoner Number" mono>{{ $inmate['prisoner_number'] }}</x-detail-row>
                    <x-detail-row label="Custody Status"><x-inmate-status-badge :status="$inmate['status']"/></x-detail-row>
                    <x-detail-row label="Classification"><x-custody-badge :classification="$inmate['classification']"/></x-detail-row>
                    <x-detail-row label="Facility">{{ $inmate['facility'] }}</x-detail-row>
                    <x-detail-row label="Admission Date">{{ \Carbon\Carbon::parse($inmate['admission_date'])->format('d M Y') }}</x-detail-row>
                    <x-detail-row label="Admission Reference" mono>{{ $inmate['admission_ref'] }}</x-detail-row>
                    @if($inmate['next_court_date'])
                        <x-detail-row label="Next Court Date"><span class="text-amber-600 font-medium">{{ \Carbon\Carbon::parse($inmate['next_court_date'])->format('d M Y') }}</span></x-detail-row>
                    @endif
                    @if($inmate['expected_release'])
                        <x-detail-row label="Expected Release">{{ \Carbon\Carbon::parse($inmate['expected_release'])->format('d M Y') }}</x-detail-row>
                    @endif
                    @if($inmate['actual_discharge'])
                        <x-detail-row label="Discharge Date">{{ \Carbon\Carbon::parse($inmate['actual_discharge'])->format('d M Y') }}</x-detail-row>
                        <x-detail-row label="Discharge Reason">{{ $inmate['discharge_reason'] }}</x-detail-row>
                    @endif
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Quick Reference"/>
                <dl>
                    <x-detail-row label="Full Name">{{ $inmate['full_name'] }}</x-detail-row>
                    <x-detail-row label="Age">{{ $inmate['age'] }} years</x-detail-row>
                    <x-detail-row label="Gender">{{ $inmate['gender'] }}</x-detail-row>
                    <x-detail-row label="Nationality">{{ $inmate['nationality'] }}</x-detail-row>
                    <x-detail-row label="District">{{ $inmate['district'] }}</x-detail-row>
                    @if($inmate['kin_name'])
                        <x-detail-row label="Next of Kin">{{ $inmate['kin_name'] }} ({{ $inmate['kin_relationship'] ?? '—' }})</x-detail-row>
                    @endif
                </dl>
                @if($inmate['notes'])
                    <div class="mt-3 pt-3 border-t border-[#F1F5F9]">
                        <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-xs text-[#64748B] leading-relaxed">{{ $inmate['notes'] }}</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Personal Details --}}
    <div x-show="tab === 'personal'" id="tab-personal" role="tabpanel" style="display:none">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-card>
                <x-section-heading title="Personal Information"/>
                <dl>
                    <x-detail-row label="First Name">{{ $inmate['first_name'] }}</x-detail-row>
                    <x-detail-row label="Middle Name">{{ $inmate['middle_name'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Last Name">{{ $inmate['last_name'] }}</x-detail-row>
                    <x-detail-row label="Gender">{{ $inmate['gender'] }}</x-detail-row>
                    <x-detail-row label="Date of Birth">{{ \Carbon\Carbon::parse($inmate['date_of_birth'])->format('d M Y') }} (Age {{ $inmate['age'] }})</x-detail-row>
                    <x-detail-row label="Nationality">{{ $inmate['nationality'] }}</x-detail-row>
                    <x-detail-row label="District of Origin">{{ $inmate['district'] }}</x-detail-row>
                </dl>
            </x-card>
            <div class="space-y-5">
                <x-card>
                    <x-section-heading title="Identification"/>
                    <dl>
                        <x-detail-row label="Document Type">{{ $inmate['id_type'] }}</x-detail-row>
                        @if($inmate['id_number'] && $inmate['id_number'] !== '—')
                            <x-detail-row label="Document Number" mono sensitive>
                                <span class="select-none" title="Sensitive — masked for display">{{ mb_strlen($inmate['id_number']) > 4 ? mb_substr($inmate['id_number'], 0, 4) . str_repeat('•', mb_strlen($inmate['id_number']) - 4) : str_repeat('•', mb_strlen($inmate['id_number'])) }}</span>
                            </x-detail-row>
                        @else
                            <x-detail-row label="Document Number">Not recorded</x-detail-row>
                        @endif
                    </dl>
                </x-card>
                <x-card>
                    <x-section-heading title="Next of Kin / Emergency Contact"/>
                    @if($inmate['kin_name'])
                    <dl>
                        <x-detail-row label="Name">{{ $inmate['kin_name'] }}</x-detail-row>
                        <x-detail-row label="Relationship">{{ $inmate['kin_relationship'] ?? '—' }}</x-detail-row>
                        <x-detail-row label="Contact">{{ $inmate['kin_contact'] ?? '—' }}</x-detail-row>
                    </dl>
                    @else
                    <x-empty-state title="No next-of-kin recorded" description="Emergency contact details have not been recorded for this inmate."/>
                    @endif
                </x-card>
            </div>
        </div>
    </div>

    {{-- Legal & Custody --}}
    <div x-show="tab === 'legal'" id="tab-legal" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Legal &amp; Custody Details"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Custody Classification"><x-custody-badge :classification="$inmate['classification']"/></x-detail-row>
                    <x-detail-row label="Legal Basis">{{ $inmate['legal_basis'] }}</x-detail-row>
                    <x-detail-row label="Warrant / Court Order Ref" mono>{{ $inmate['warrant_ref'] }}</x-detail-row>
                    <x-detail-row label="Issuing Authority">{{ $inmate['issuing_authority'] }}</x-detail-row>
                    <x-detail-row label="Case Reference" mono>{{ $inmate['case_ref'] ?? '—' }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Admission Date">{{ \Carbon\Carbon::parse($inmate['admission_date'])->format('d M Y') }}</x-detail-row>
                    @if($inmate['sentence'])
                        <x-detail-row label="Sentence">{{ $inmate['sentence'] }}</x-detail-row>
                    @endif
                    @if($inmate['expected_release'])
                        <x-detail-row label="Expected Release">{{ \Carbon\Carbon::parse($inmate['expected_release'])->format('d M Y') }}</x-detail-row>
                    @endif
                    @if($inmate['next_court_date'])
                        <x-detail-row label="Next Court Date"><span class="text-amber-600">{{ \Carbon\Carbon::parse($inmate['next_court_date'])->format('d M Y') }}</span></x-detail-row>
                    @endif
                    @if($inmate['actual_discharge'])
                        <x-detail-row label="Discharge Date">{{ \Carbon\Carbon::parse($inmate['actual_discharge'])->format('d M Y') }}</x-detail-row>
                        <x-detail-row label="Discharge Reason">{{ $inmate['discharge_reason'] }}</x-detail-row>
                        <x-detail-row label="Discharge Auth Ref" mono>{{ $inmate['discharge_auth_ref'] }}</x-detail-row>
                    @endif
                </dl>
            </div>
        </x-card>
    </div>

    {{-- Admission History --}}
    <div x-show="tab === 'admissions'" id="tab-admissions" role="tabpanel" style="display:none">
        @if(count($admissions))
        <div class="space-y-4">
            @foreach($admissions as $i => $ep)
            <x-card>
                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                    <div>
                        <span class="font-mono text-xs text-[#2563EB] font-semibold">{{ $ep['admission_ref'] }}</span>
                        @if($ep['status'] === 'Current')
                            <x-badge variant="active" class="ml-2">Current</x-badge>
                        @else
                            <x-badge variant="released" class="ml-2">Completed</x-badge>
                        @endif
                    </div>
                    <span class="text-xs text-[#64748B]">Episode {{ count($admissions) - $i }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-6 text-sm">
                    <div><p class="text-xs text-[#64748B]">Admitted</p><p>{{ \Carbon\Carbon::parse($ep['admission_date'])->format('d M Y') }}</p></div>
                    <div><p class="text-xs text-[#64748B]">Facility</p><p>{{ $ep['facility'] }}</p></div>
                    <div><p class="text-xs text-[#64748B]">Classification</p><p><x-custody-badge :classification="$ep['classification']"/></p></div>
                    <div class="sm:col-span-2"><p class="text-xs text-[#64748B]">Legal Basis</p><p>{{ $ep['legal_basis'] }}</p></div>
                    <div><p class="text-xs text-[#64748B]">Warrant Ref</p><p class="font-mono text-xs">{{ $ep['warrant_ref'] }}</p></div>
                    @if($ep['discharge_date'])
                    <div><p class="text-xs text-[#64748B]">Discharged</p><p>{{ \Carbon\Carbon::parse($ep['discharge_date'])->format('d M Y') }}</p></div>
                    <div><p class="text-xs text-[#64748B]">Discharge Reason</p><p>{{ $ep['discharge_reason'] ?? '—' }}</p></div>
                    @endif
                </div>
            </x-card>
            @endforeach
        </div>
        @else
        <x-empty-state title="No admission history" description="No previous admission episodes found for this inmate."/>
        @endif
    </div>

    @permissionAny(['transfers.request', 'transfers.review', 'transfers.authorize', 'transfers.dispatch', 'transfers.receive'])
{{-- Transfers --}}
    <div x-show="tab === 'transfers'" id="tab-transfers" role="tabpanel" style="display:none">
        @if(count($transfers))
        <div class="space-y-4">
            @foreach($transfers as $t)
            <x-card>
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('transfers.show', $t['id']) }}"
                           class="font-mono text-xs text-[#2563EB] font-semibold hover:underline">{{ $t['transfer_ref'] }}</a>
                        <x-transfer-status-badge :status="$t['status']"/>
                        @if($t['priority'] !== 'Routine')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">{{ $t['priority'] }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-[#64748B]">{{ \Carbon\Carbon::parse($t['request_date'])->format('d M Y') }}</span>
                </div>
                <div class="mt-3 flex items-center gap-3 text-sm">
                    <span class="font-medium text-[#0F172A]">{{ $t['origin_facility'] }}</span>
                    <svg class="w-4 h-4 text-[#2563EB] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                    <span class="font-medium text-[#0F172A]">{{ $t['destination_facility'] }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-2">
                    <p class="text-xs text-[#64748B]">{{ $t['transfer_reason'] }}</p>
                    <a href="{{ route('transfers.show', $t['id']) }}"
                       class="text-xs text-[#2563EB] hover:underline flex-shrink-0">View details →</a>
                </div>
            </x-card>
            @endforeach
        </div>
        @else
        <x-empty-state title="No transfer records" description="No transfers have been recorded for this inmate.">
            @if(in_array($inmate['status'], ['In Custody', 'Remanded']))
                @permission('transfers.request', $inmate['facility_id'])
<x-button.primary href="{{ route('transfers.create', ['inmate_id' => $inmate['id']]) }}" size="sm">Initiate Transfer</x-button.primary>
@endpermission
            @endif
        </x-empty-state>
        @endif
    </div>

    @endpermissionAny
@permission('visitors.read')
{{-- Visits --}}
    <div x-show="tab === 'visits'" id="tab-visits" role="tabpanel" style="display:none">
        <x-card :padding="false">
            <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-sm font-semibold text-[#0F172A]">Visit Records</p>
                    <p class="text-xs text-[#64748B] mt-0.5">The latest 25 visit records within your authorized facility scope.</p>
                </div>
                @permission('visits.schedule', $inmate['facility_id'])
<a href="{{ route('visitors.schedule') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#2563EB] bg-[#EFF6FF] rounded-lg hover:bg-brand-soft transition-colors">
                    Schedule a Visit
                </a>
@endpermission
            </div>
            @if(empty($visits))
            <div class="p-5">
                <x-empty-state title="No visits recorded" description="No visit records are visible within your authorized facility scope."/>
            </div>
            @else
            <div class="table-responsive">
                <table class="w-full text-sm">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Ref</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Visitor</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left hidden sm:table-cell">Purpose</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Date</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left hidden md:table-cell">Duration</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach($visits as $v)
                        <tr class="hover:bg-[#F8FAFC]">
                            <td class="px-4 py-3 font-mono text-xs text-[#2563EB]">{{ $v['visit_ref'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#0F172A]">
                                <a href="{{ route('visitors.show', $v['visitor_id']) }}" class="hover:text-[#2563EB] hover:underline">{{ $v['visitor_name'] }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-[#64748B] hidden sm:table-cell">{{ $v['purpose'] }}</td>
                            <td class="px-4 py-3 text-sm text-[#64748B]">{{ \Carbon\Carbon::parse($v['visit_date'])->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-[#64748B] hidden md:table-cell">{{ $v['duration_mins'] }} min</td>
                            <td class="px-4 py-3"><x-visit-status-badge :status="$v['status']"/></td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('visitors.visits.show', $v['id']) }}" class="text-xs font-medium text-[#2563EB] hover:underline">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-card>
    </div>

    @endpermission
{{-- Activity History --}}
    <div x-show="tab === 'activity'" id="tab-activity" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Activity Timeline"/>
            <x-activity-timeline :events="$timeline" />
        </x-card>
    </div>

</div>{{-- end tabs --}}



@endsection
