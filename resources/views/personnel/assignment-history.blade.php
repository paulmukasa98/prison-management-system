@extends('layouts.app')
@section('title', 'Assignment History — ' . $person['full_name'])
@section('content')

<x-page-heading title="Assignment History"
    :description="'All facility postings for ' . $person['full_name'] . ' (' . $person['staff_number'] . ')'"
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Personnel','route'=>'personnel.index'],['label'=>$person['staff_number'],'url'=>route('personnel.show',$person['id'])],['label'=>'Assignment History']]"
>
    <x-button.secondary href="{{ route('personnel.show', $person['id']) }}">← Back to Profile</x-button.secondary>
</x-page-heading>

{{-- Person banner --}}
<div class="flex flex-wrap items-center gap-4 bg-white border border-[#E2E8F0] rounded-xl px-5 py-4 mb-5 shadow-sm">
    <div class="w-10 h-10 rounded-full bg-[#DBEAFE] flex items-center justify-center flex-shrink-0">
        <span class="text-sm font-bold text-[#2563EB]">{{ $person['initials'] }}</span>
    </div>
    <div>
        <p class="text-sm font-semibold text-[#0F172A]">{{ $person['full_name'] }}</p>
        <p class="text-xs text-[#64748B]"><span class="font-mono">{{ $person['staff_number'] }}</span> &middot; {{ $person['rank'] }}</p>
    </div>
    <div class="sm:ml-auto"><x-personnel-status-badge :status="$person['employment_status']"/></div>
</div>

<p class="text-sm text-[#64748B] mb-4">
    <span class="font-semibold text-[#0F172A]">{{ count($assignments) }}</span> {{ Str::plural('assignment record', count($assignments)) }}.
    
</p>

@if(count($assignments))
<div class="space-y-4">
    @foreach($assignments as $i => $a)
    <div class="bg-white rounded-xl border {{ $a['status']==='Active' ? 'border-brand' : 'border-[#E2E8F0]' }} shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 {{ $a['status']==='Active' ? 'bg-[#DBEAFE]' : 'bg-[#F8FAFC]' }} border-b border-[#E2E8F0]">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full {{ $a['status']==='Active' ? 'bg-brand text-white' : 'bg-[#E2E8F0] text-[#64748B]' }} flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {{ count($assignments) - $i }}
                </div>
                <span class="font-mono text-sm {{ $a['status']==='Active' ? 'text-[#2563EB]' : 'text-[#0F172A]' }} font-semibold">{{ $a['assignment_ref'] }}</span>
                <x-assignment-status-badge :status="$a['status']"/>
            </div>
            <span class="text-xs text-[#64748B]">
                {{ \Carbon\Carbon::parse($a['start_date'])->format('d M Y') }}
                {{ $a['end_date'] ? ' — '.\Carbon\Carbon::parse($a['end_date'])->format('d M Y') : ' — Present' }}
            </span>
        </div>
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-3 gap-x-6">
            <div><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Facility</p><p class="text-sm text-[#0F172A]">{{ $a['facility'] }}</p></div>
            <div><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Role</p><p class="text-sm text-[#0F172A]">{{ $a['role'] }}</p></div>
            <div><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Reason</p><p class="text-sm text-[#0F172A]">{{ $a['reason'] }}</p></div>
            <div><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Auth Ref</p><p class="text-sm font-mono text-[#64748B]">{{ $a['auth_ref'] ?? '—' }}</p></div>
            @if(!empty($a['notes']))
            <div class="sm:col-span-2"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">Notes</p><p class="text-sm text-[#64748B] italic">{{ $a['notes'] }}</p></div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<x-empty-state title="No assignments recorded" description="No facility assignments found.">
    @permission('personnel.manage', $person['facility_id'])
<x-button.primary href="{{ route('personnel.assignment.create', ['personnel_id' => $person['id']]) }}" size="sm">New Assignment</x-button.primary>
@endpermission
</x-empty-state>
@endif
@endsection
