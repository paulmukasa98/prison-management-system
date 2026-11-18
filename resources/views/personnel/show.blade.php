@extends('layouts.app')
@section('title', $person['full_name'] . ' — Personnel Profile')
@section('content')

<x-breadcrumb :items="[
    ['label'=>'Dashboard','route'=>'dashboard'],
    ['label'=>'Personnel','route'=>'personnel.index'],
    ['label'=>$person['staff_number']],
]" class="mb-4"/>

{{-- Header --}}
<div class="bg-brand-dark rounded-xl p-5 sm:p-6 mb-5 text-white">
    <div class="flex flex-col sm:flex-row gap-5">
        <div class="w-20 h-20 rounded-xl bg-[#183B56] border-2 border-white/20 flex items-center justify-center flex-shrink-0">
            <span class="text-2xl font-bold text-white/70">{{ $person['initials'] }}</span>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-blue-300 font-mono font-semibold mb-1">{{ $person['staff_number'] }}</p>
                    <h1 class="text-xl font-bold text-white">{{ $person['full_name'] }}</h1>
                    <p class="text-sm text-white/70 mt-0.5">{{ $person['rank'] }} &middot; {{ $person['department'] }}</p>
                </div>
                <x-personnel-status-badge :status="$person['employment_status']"/>
            </div>
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-6 text-sm">
                <div><p class="text-xs text-white/50 mb-0.5">Facility</p><p class="text-white/90 font-medium truncate">{{ $person['facility'] ?? 'HQ / Unassigned' }}</p></div>
                <div><p class="text-xs text-white/50 mb-0.5">Appointed</p><p class="text-white/90 font-medium">{{ \Carbon\Carbon::parse($person['appointment_date'])->format('d M Y') }}</p></div>
                <div><p class="text-xs text-white/50 mb-0.5">Gender</p><p class="text-white/90">{{ $person['gender'] }}</p></div>
            </div>
        </div>
    </div>
    <div class="mt-4 pt-4 border-t border-white/10 flex flex-wrap gap-2">
        @permission('personnel.manage', $person['facility_id'])
<a href="{{ route('personnel.edit', $person['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit Details
        </a>
@endpermission
        <a href="{{ route('personnel.assignment.history', $person['id']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Assignment History
        </a>
        @permission('personnel.manage', $person['facility_id'])
<a href="{{ route('personnel.assignment.create', ['personnel_id' => $person['id']]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-300 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
            New Assignment
        </a>
@endpermission
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'overview' }">
    <div class="flex overflow-x-auto gap-0 border-b border-[#E2E8F0] mb-5" role="tablist">
        @foreach([['key'=>'overview','label'=>'Overview'],['key'=>'personal','label'=>'Personal'],['key'=>'employment','label'=>'Employment'],['key'=>'assignments','label'=>'Assignments'],['key'=>'contact','label'=>'Contact'],['key'=>'activity','label'=>'Activity']] as $t)
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
                <x-section-heading title="Current Posting"/>
                <dl>
                    <x-detail-row label="Staff Number" mono>{{ $person['staff_number'] }}</x-detail-row>
                    <x-detail-row label="Status"><x-personnel-status-badge :status="$person['employment_status']"/></x-detail-row>
                    <x-detail-row label="Rank">{{ $person['rank'] }}</x-detail-row>
                    <x-detail-row label="Department">{{ $person['department'] }}</x-detail-row>
                    <x-detail-row label="Facility">{{ $person['facility'] ?? 'HQ / Unassigned' }}</x-detail-row>
                    <x-detail-row label="Appointed">{{ \Carbon\Carbon::parse($person['appointment_date'])->format('d M Y') }}</x-detail-row>
                </dl>
            </x-card>
            <x-card>
                <x-section-heading title="Quick Reference"/>
                <dl>
                    <x-detail-row label="Full Name">{{ $person['full_name'] }}</x-detail-row>
                    <x-detail-row label="Age">{{ $person['age'] }} years</x-detail-row>
                    <x-detail-row label="Gender">{{ $person['gender'] }}</x-detail-row>
                    <x-detail-row label="Nationality">{{ $person['nationality'] }}</x-detail-row>
                </dl>
                @if($person['notes'])
                <div class="mt-3 pt-3 border-t border-[#F1F5F9]">
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1">Notes</p>
                    <p class="text-xs text-[#64748B] leading-relaxed">{{ $person['notes'] }}</p>
                </div>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Personal --}}
    <div x-show="tab==='personal'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Personal Information"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="First Name">{{ $person['first_name'] }}</x-detail-row>
                    <x-detail-row label="Middle Name">{{ $person['middle_name'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Last Name">{{ $person['last_name'] }}</x-detail-row>
                    <x-detail-row label="Gender">{{ $person['gender'] }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Date of Birth">{{ \Carbon\Carbon::parse($person['date_of_birth'])->format('d M Y') }} (Age {{ $person['age'] }})</x-detail-row>
                    <x-detail-row label="Nationality">{{ $person['nationality'] }}</x-detail-row>
                </dl>
            </div>
        </x-card>
    </div>

    {{-- Employment --}}
    <div x-show="tab==='employment'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Employment Details"/>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10">
                <dl>
                    <x-detail-row label="Staff Number" mono>{{ $person['staff_number'] }}</x-detail-row>
                    <x-detail-row label="Rank / Position">{{ $person['rank'] }}</x-detail-row>
                    <x-detail-row label="Department">{{ $person['department'] }}</x-detail-row>
                </dl>
                <dl>
                    <x-detail-row label="Employment Status"><x-personnel-status-badge :status="$person['employment_status']"/></x-detail-row>
                    <x-detail-row label="Appointment Date">{{ \Carbon\Carbon::parse($person['appointment_date'])->format('d M Y') }}</x-detail-row>
                    <x-detail-row label="Current Facility">{{ $person['facility'] ?? 'HQ / Unassigned' }}</x-detail-row>
                </dl>
            </div>
        </x-card>
    </div>

    {{-- Assignments --}}
    <div x-show="tab==='assignments'" role="tabpanel" style="display:none">
        @if(count($assignments))
        <div class="space-y-4">
            @foreach($assignments as $a)
            <x-card>
                <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs text-[#2563EB] font-semibold">{{ $a['assignment_ref'] }}</span>
                        <x-assignment-status-badge :status="$a['status']"/>
                    </div>
                    <span class="text-xs text-[#64748B]">{{ \Carbon\Carbon::parse($a['start_date'])->format('d M Y') }}{{ $a['end_date'] ? ' — '.\Carbon\Carbon::parse($a['end_date'])->format('d M Y') : ' — Present' }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-y-2 gap-x-6 text-sm">
                    <div><p class="text-xs text-[#64748B]">Facility</p><p>{{ $a['facility'] }}</p></div>
                    <div><p class="text-xs text-[#64748B]">Role</p><p>{{ $a['role'] }}</p></div>
                    <div><p class="text-xs text-[#64748B]">Reason</p><p>{{ $a['reason'] }}</p></div>
                </div>
            </x-card>
            @endforeach
        </div>
        @else
        <x-empty-state title="No assignments recorded" description="No facility assignments found for this personnel record.">
            @permission('personnel.manage', $person['facility_id'])
<x-button.primary href="{{ route('personnel.assignment.create') }}" size="sm">New Assignment</x-button.primary>
@endpermission
        </x-empty-state>
        @endif
    </div>

    {{-- Contact --}}
    <div x-show="tab==='contact'" role="tabpanel" style="display:none">
        <x-card>
            <x-section-heading title="Contact Information" description="Sensitive details are masked."/>
            <dl>
                <x-detail-row label="Official Email">
                    @if($person['email'])
                        <span class="text-[#2563EB]">{{ substr($person['email'], 0, 3) }}***@demo.ups.go.ug</span>
                    @else —
                    @endif
                </x-detail-row>
                <x-detail-row label="Official Phone">
                    @if($person['phone'])
                        <span>{{ substr($person['phone'], 0, 8) }}****</span>
                    @else —
                    @endif
                </x-detail-row>
            </dl>
            <div class="mt-4 pt-4 border-t border-[#F1F5F9]">
                <x-section-heading title="Emergency Contact" description="Restricted — displayed in masked form."/>
                <dl>
                    <x-detail-row label="Name">{{ $person['kin_name'] ?? '—' }}</x-detail-row>
                    <x-detail-row label="Contact">
                        @if($person['kin_contact']) {{ substr($person['kin_contact'], 0, 8) }}**** @else — @endif
                    </x-detail-row>
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
