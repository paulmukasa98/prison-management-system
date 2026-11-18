@extends('layouts.app')
@section('title', $facility['name'] . ' — Personnel')
@section('content')

<x-page-heading
    :title="$facility['name'] . ' — Assigned Personnel'"
    description="Active personnel assignment records for this facility."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Facilities','route'=>'facilities.index'],['label'=>$facility['code'],'url'=>route('facilities.show',$facility['id'])],['label'=>'Personnel']]"
>
    <x-button.secondary href="{{ route('facilities.show', $facility['id']) }}">← Back</x-button.secondary>
    @permission('personnel.manage', $facility['id'])
<x-button.primary href="{{ route('personnel.assignment.create') }}" size="sm">New Assignment</x-button.primary>
@endpermission
</x-page-heading>

@if(count($personnel))
<x-card :padding="false">
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Facility personnel">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Staff No.</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Name</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden sm:table-cell">Role</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide hidden md:table-cell">Start Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Profile</th>
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
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('personnel.show', $a['personnel_id']) }}" class="text-xs text-[#2563EB] hover:underline font-medium">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-card>
@else
<x-empty-state title="No personnel assigned" description="No active assignments recorded for this facility.">
    @permission('personnel.manage', $facility['id'])
<x-button.primary href="{{ route('personnel.assignment.create') }}" size="sm">New Assignment</x-button.primary>
@endpermission
</x-empty-state>
@endif


@endsection
