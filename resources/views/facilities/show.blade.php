@extends('layouts.app')
@section('title', $facility['name'] . ' — Facility Profile')
@section('content')

<x-breadcrumb :items="[
    ['label'=>'Dashboard','route'=>'dashboard'],
    ['label'=>'Facilities','route'=>'facilities.index'],
    ['label'=>$facility['code']],
]" class="mb-4"/>

{{-- Header --}}
<div class="bg-brand-dark rounded-xl p-5 sm:p-6 mb-5 text-white">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="font-mono text-blue-300 text-sm font-semibold">{{ $facility['code'] }}</span>
                <x-facility-status-badge :status="$facility['status']"/>
            </div>
            <h1 class="text-xl font-bold text-white">{{ $facility['name'] }}</h1>
            <p class="text-sm text-white/70 mt-0.5">{{ $facility['type'] }} &middot; {{ $facility['region'] }}, {{ $facility['district'] }}</p>
        </div>
        <div class="text-sm text-white/70 text-right">
            <p>Population: <span class="text-white font-bold">{{ number_format($facility['current_population']) }}</span></p>
            <p>Capacity: <span class="text-white font-semibold">{{ $facility['capacity'] === null ? 'Unknown' : number_format($facility['capacity']) }}</span></p>
            <p>Occupancy: <span class="{{ $facility['occupancy_pct'] >= 90 ? 'text-red-300 font-bold' : 'text-white font-semibold' }}">{{ $facility['occupancy_pct'] }}%</span></p>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-white/10 flex flex-wrap gap-2">
        @permission('facilities.manage', $facility['id'])
<a href="{{ route('facilities.edit', $facility['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit Facility
        </a>
@endpermission
        @permission('personnel.read', $facility['id'])
<a href="{{ route('facilities.personnel', $facility['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            View Personnel
        </a>
@endpermission
        @permission('facilities.read')
<a href="{{ route('facilities.capacity') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            Capacity Overview
        </a>
@endpermission
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'overview' }">
    <div class="flex overflow-x-auto gap-0 border-b border-[#E2E8F0] mb-5" role="tablist">
        @foreach([['key'=>'overview','label'=>'Overview'],['key'=>'population','label'=>'Population & Capacity'],['key'=>'personnel','label'=>'Assigned Personnel'],['key'=>'admin','label'=>'Administrative Details'],['key'=>'activity','label'=>'Activity']] as $t)
        <button @click="tab='{{ $t['key'] }}'" :aria-selected="tab==='{{ $t['key'] }}'" role="tab"
            class="flex-shrink-0 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
            :class="tab==='{{ $t['key'] }}'?'border-brand text-[#2563EB]':'border-transparent text-[#64748B] hover:text-[#0F172A]'">
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Overview --}}
    <div x-show="tab==='overview'" role="tabpanel">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-card>
                <x-section-heading title="Facility Summary"/>
                <dl>
                    <x-detail-row label="Facility Code" mono>{{ $facility['code'] }}</x-detail-row>
                    <x-detail-row label="Status"><x-facility-status-badge :status="$facility['status']"/></x-detail-row>
                    <x-detail-row label="Type">{{ $facility['type'] }}</x-detail-row>
                    <x-detail-row label="Region">{{ $facility['region'] }}</x-detail-row>
                    <x-detail-row label="District">{{ $facility['district'] }}</x-detail-row>
                    <x-detail-row label="Location">{{ $facility['location_desc'] ?? '—' }}</x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Population &amp; Staffing"/>
                <dl>
                    <x-detail-row label="Current Population">{{ number_format($facility['current_population']) }}</x-detail-row>
                    <x-detail-row label="Recorded Capacity">{{ $facility['capacity'] === null ? 'Unknown' : number_format($facility['capacity']) }}</x-detail-row>
                    <x-detail-row label="Occupancy Rate">
                        <span class="{{ $facility['occupancy_pct'] >= 90 ? 'text-red-600 font-bold' : 'font-semibold' }}">{{ $facility['occupancy_pct'] }}%</span>
                        @if($facility['occupancy_pct'] >= 100) <span class="ml-1 text-red-600 text-xs font-bold" aria-label="Over capacity">⚠ Over capacity</span> @endif
                    </x-detail-row>
                    <x-detail-row label="Assigned Personnel">{{ $facility['personnel_count'] }}</x-detail-row>
                    <x-detail-row label="Established">{{ $facility['established'] ? \Carbon\Carbon::parse($facility['established'])->format('d M Y') : '—' }}</x-detail-row>
                </dl>
            </x-card>
        </div>
    </div>

    {{-- Population & Capacity --}}
    <div x-show="tab==='population'" role="tabpanel" style="display:none">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-card>
                <x-section-heading title="Population Breakdown"/>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs text-[#64748B] mb-1"><span>Male</span><span class="font-semibold text-[#0F172A]">{{ number_format($facility['male_population']) }}</span></div>
                        <div class="w-full bg-[#F1F5F9] rounded-full h-2"><div class="bg-[#2563EB] h-2 rounded-full" style="width:{{ $facility['current_population']>0?round(($facility['male_population']/$facility['current_population'])*100):0 }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-[#64748B] mb-1"><span>Female</span><span class="font-semibold text-[#0F172A]">{{ number_format($facility['female_population']) }}</span></div>
                        <div class="w-full bg-[#F1F5F9] rounded-full h-2"><div class="bg-purple-500 h-2 rounded-full" style="width:{{ $facility['current_population']>0?round(($facility['female_population']/$facility['current_population'])*100):0 }}%"></div></div>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-[#F1F5F9] space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-[#64748B]">Convicted</span><span class="font-semibold">{{ number_format($facility['convicted_count']) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#64748B]">Remand</span><span class="font-semibold">{{ number_format($facility['remand_count']) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#64748B]">Civil Debtor</span><span class="font-semibold">{{ number_format($facility['civil_debtor_count']) }}</span></div>
                </div>
            </x-card>
            <x-card>
                <x-section-heading title="Capacity Utilisation"/>
                <x-occupancy-bar :population="$facility['current_population']" :capacity="$facility['capacity']" :pct="$facility['occupancy_pct']" class="mb-4"/>
                <dl>
                    <x-detail-row label="Recorded Capacity">{{ $facility['capacity'] === null ? 'Unknown' : number_format($facility['capacity']) }}</x-detail-row>
                    <x-detail-row label="Current Population">{{ number_format($facility['current_population']) }}</x-detail-row>
                    <x-detail-row label="Available Space">{{ $facility['capacity'] === null ? 'Unknown' : max(0, $facility['capacity'] - $facility['current_population']) }}</x-detail-row>
                    <x-detail-row label="Capacity Notes">{{ $facility['capacity_notes'] ?? '—' }}</x-detail-row>
                </dl>
                
            </x-card>
        </div>
    </div>

    {{-- Personnel --}}
    <div x-show="tab==='personnel'" role="tabpanel" style="display:none">
        @if(count($personnel))
        <x-card :padding="false">
            <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-[#0F172A]">Assigned Personnel</p>
                    <p class="text-xs text-[#64748B]">{{ count($personnel) }} active assignment(s).</p>
                </div>
                @permission('personnel.read', $facility['id'])
<a href="{{ route('facilities.personnel', $facility['id']) }}" class="text-xs text-[#2563EB] hover:underline">View full list</a>
@endpermission
            </div>
            <div class="table-responsive">
                <table class="w-full text-sm">
                    <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Staff No.</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Name</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left hidden sm:table-cell">Role</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left hidden md:table-cell">Start Date</th>
                            <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach($personnel as $a)
                        <tr class="hover:bg-[#F8FAFC]">
                            <td class="px-4 py-3 font-mono text-xs text-[#2563EB]">{{ $a['staff_number'] }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#0F172A]">{{ $a['personnel_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-[#64748B] hidden sm:table-cell">{{ $a['role'] }}</td>
                            <td class="px-4 py-3 text-sm text-[#64748B] hidden md:table-cell">{{ $a['start_date'] }}</td>
                            <td class="px-4 py-3"><x-assignment-status-badge :status="$a['status']"/></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
        @else
        <x-empty-state title="No personnel assigned" description="No active assignments for this facility."/>
        @endif
    </div>

    {{-- Admin --}}
    <div x-show="tab==='admin'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Administrative Details"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Facility Code" mono>{{ $facility['code'] }}</x-detail-row>
                    <x-detail-row label="Facility Type">{{ $facility['type'] }}</x-detail-row>
                    <x-detail-row label="Administrative Status"><x-facility-status-badge :status="$facility['status']"/></x-detail-row>
                    <x-detail-row label="Responsible Officer">{{ $facility['responsible_officer'] ?? '—' }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Established">{{ $facility['established'] ? \Carbon\Carbon::parse($facility['established'])->format('d M Y') : '—' }}</x-detail-row>
                    <x-detail-row label="Capacity Effective">{{ $facility['capacity_date'] ? \Carbon\Carbon::parse($facility['capacity_date'])->format('d M Y') : '—' }}</x-detail-row>
                    <x-detail-row label="Notes">{{ $facility['notes'] ?? '—' }}</x-detail-row>
                </dl>
            </div>
        </x-card>
    </div>

    {{-- Activity --}}
    <div x-show="tab==='activity'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Activity Timeline"/>
            <x-activity-timeline :events="$timeline"/>
        </x-card>
    </div>
</div>


@endsection
