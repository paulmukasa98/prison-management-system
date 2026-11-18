@extends('layouts.app')
@section('title', 'Visit '.$visit['visit_ref'])
@section('content')
<x-page-heading :title="$visit['visit_ref']" description="Visit occurrence, participants, authorization and event history." :breadcrumbs="[['label'=>'Visitors','route'=>'visitors.index'],['label'=>'Visit Records','route'=>'visitors.records'],['label'=>'Visit Details']]"/>
@if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
@if($errors->any())<x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>@endif
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <x-card>
            <div class="mb-4"><x-visit-status-badge :status="$visit['status']"/></div>
            <dl>
                <x-detail-row label="Visitor"><a class="text-[#2563EB] hover:underline" href="{{ route('visitors.show', $visit['visitor_id']) }}">{{ $visit['visitor_name'] }}</a></x-detail-row>
                <x-detail-row label="Inmate">{{ $visit['inmate_name'] }} · {{ $visit['prisoner_number'] }}</x-detail-row>
                <x-detail-row label="Facility">{{ $visit['facility'] }}</x-detail-row>
                <x-detail-row label="Purpose">{{ $visit['purpose'] }}</x-detail-row>
                <x-detail-row label="Relationship">{{ $visit['relationship'] }}</x-detail-row>
                <x-detail-row label="Visit Window">{{ $visit['visit_date'] ?? 'Unscheduled' }} {{ $visit['start_time'] }} · {{ $visit['duration_mins'] ?? '—' }} min ({{ config('visitation.timezone') }})</x-detail-row>
                <x-detail-row label="Checked In">{{ $visit['check_in_time'] ?? 'Not checked in' }}</x-detail-row>
                <x-detail-row label="Checked Out">{{ $visit['check_out_time'] ?? 'Not checked out' }}</x-detail-row>
                <x-detail-row label="Notes">{{ $visit['notes'] ?? 'None' }}</x-detail-row>
            </dl>
        </x-card>
        <x-card><x-section-heading title="Visit Timeline" description="Recorded events are append-only."/><x-visit-timeline :events="$timeline"/></x-card>
    </div>
    <x-card>
        <x-section-heading title="Actions"/>
        <div class="flex flex-col gap-3">
            @if(in_array($visit['status'], ['Requested','Pending Review','Approved']))
                @can('review', $visitModel)<x-button.primary href="{{ route('visitors.visits.review', $visit['id']) }}">Review Visit</x-button.primary>@endcan
            @endif
            @if($visit['status'] === 'Approved')
                @can('schedule', $visitModel)
                <form method="POST" action="{{ route('visitors.visits.schedule.store', $visit['id']) }}">
                    @csrf<input type="hidden" name="reason" value="Approved visit window confirmed"><input type="hidden" name="confirmed" value="1">
                    <x-button.primary type="submit">Confirm Schedule</x-button.primary>
                </form>
                @endcan
            @endif
            @if($visit['status'] === 'Scheduled')
                @can('checkinout', $visitModel)<x-button.primary href="{{ route('visitors.visits.check-in', $visit['id']) }}">Check In</x-button.primary>@endcan
            @endif
            @if($visit['status'] === 'Checked In')
                @can('checkinout', $visitModel)<x-button.primary href="{{ route('visitors.visits.check-out', $visit['id']) }}">Check Out</x-button.primary>@endcan
            @endif
            @if(in_array($visit['status'], \App\Models\Visit::OPEN_STATES))
                @can('cancel', $visitModel)<x-button.secondary href="{{ route('visitors.visits.cancel', $visit['id']) }}">Cancel Visit</x-button.secondary>@endcan
            @endif
            @permission('visitors.read')
<x-button.secondary href="{{ route('visitors.records') }}">Back to Records</x-button.secondary>
@endpermission
        </div>
    </x-card>
</div>
@endsection