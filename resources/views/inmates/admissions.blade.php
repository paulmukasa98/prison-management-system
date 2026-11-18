@extends('layouts.app')

@section('title', 'Admission History — ' . $inmate['full_name'])

@section('content')

<x-page-heading
    title="Admission History"
    :description="'All custody episodes for ' . $inmate['full_name'] . ' (' . $inmate['prisoner_number'] . ')'"
    :breadcrumbs="[
        ['label' => 'Dashboard',                'route' => 'dashboard'],
        ['label' => 'Inmates',                  'route' => 'inmates.index'],
        ['label' => $inmate['prisoner_number'], 'url'   => route('inmates.show', $inmate['id'])],
        ['label' => 'Admission History'],
    ]"
>
    <x-button.secondary href="{{ route('inmates.show', $inmate['id']) }}">← Back to Profile</x-button.secondary>
</x-page-heading>

{{-- Inmate summary banner --}}
<div class="flex flex-wrap items-center gap-4 bg-white border border-[#E2E8F0] rounded-xl px-5 py-4 mb-5 shadow-sm">
    <div class="w-10 h-10 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0">
        <span class="text-sm font-bold text-[#2563EB]">{{ $inmate['initials'] }}</span>
    </div>
    <div>
        <p class="text-sm font-semibold text-[#0F172A]">{{ $inmate['full_name'] }}</p>
        <p class="text-xs text-[#64748B]">
            <span class="font-mono">{{ $inmate['prisoner_number'] }}</span>
            &middot; {{ $inmate['gender'] }} &middot; Age {{ $inmate['age'] }}
        </p>
    </div>
    <div class="sm:ml-auto flex flex-wrap items-center gap-2">
        <x-inmate-status-badge :status="$inmate['status']"/>
        <x-custody-badge :classification="$inmate['classification']"/>
    </div>
</div>

{{-- Episode count --}}
<p class="text-sm text-[#64748B] mb-4">
    <span class="font-semibold text-[#0F172A]">{{ count($episodes) }}</span>
    {{ Str::plural('custody episode', count($episodes)) }} recorded.
    
</p>

@if(count($episodes))
    <div class="space-y-4 mb-6">
        @foreach($episodes as $i => $ep)
        @php $isCurrent = $ep['status'] === 'Current'; @endphp
        <div class="bg-white rounded-xl border {{ $isCurrent ? 'border-brand' : 'border-[#E2E8F0]' }} shadow-sm overflow-hidden">
            {{-- Episode header --}}
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 {{ $isCurrent ? 'bg-[#DBEAFE]' : 'bg-[#F8FAFC]' }} border-b border-[#E2E8F0]">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full {{ $isCurrent ? 'bg-brand text-white' : 'bg-[#E2E8F0] text-[#64748B]' }} flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ count($episodes) - $i }}
                    </div>
                    <span class="font-mono text-sm font-semibold {{ $isCurrent ? 'text-[#2563EB]' : 'text-[#0F172A]' }}">{{ $ep['admission_ref'] }}</span>
                    @if($isCurrent)
                        <x-badge variant="active">Current Admission</x-badge>
                    @else
                        <x-badge variant="released">Completed</x-badge>
                    @endif
                </div>
                <span class="text-xs text-[#64748B]">Admitted {{ \Carbon\Carbon::parse($ep['admission_date'])->format('d M Y') }}</span>
            </div>
            {{-- Episode body --}}
            <div class="p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-6">
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Facility</p>
                    <p class="text-sm text-[#0F172A]">{{ $ep['facility'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Classification</p>
                    <x-custody-badge :classification="$ep['classification']"/>
                </div>
                <div class="sm:col-span-2 lg:col-span-2">
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Legal Basis</p>
                    <p class="text-sm text-[#0F172A]">{{ $ep['legal_basis'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Warrant / Order Ref</p>
                    <p class="text-sm font-mono text-[#64748B]">{{ $ep['warrant_ref'] }}</p>
                </div>
                @if($ep['discharge_date'])
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Discharged</p>
                    <p class="text-sm text-[#0F172A]">{{ \Carbon\Carbon::parse($ep['discharge_date'])->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Discharge Reason</p>
                    <p class="text-sm text-[#0F172A]">{{ $ep['discharge_reason'] ?? '—' }}</p>
                </div>
                @elseif(!$isCurrent)
                <div>
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Status</p>
                    <p class="text-sm text-[#64748B] italic">{{ $ep['status'] }}</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
@else
    <x-empty-state title="No admission history" description="No custody episodes found."/>
@endif

{{-- Timeline --}}
<x-card>
    <x-section-heading title="Record Activity Timeline"/>
    <x-activity-timeline :events="$timeline"/>
</x-card>

@endsection
