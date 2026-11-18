@extends('layouts.app')
@section('title', 'Visitor — '.$visitor['full_name'])
@section('content')
<x-page-heading :title="$visitor['full_name']" :description="$visitor['visitor_ref']" :breadcrumbs="[['label'=>'Visitors','route'=>'visitors.index'],['label'=>'Visitor Profile']]">
    @can('update', $visitorModel)<x-button.secondary href="{{ route('visitors.edit', $visitor['id']) }}">Edit Visitor</x-button.secondary>@endcan
    @can('schedule', \App\Models\Visitor::class)@permission('visits.schedule')
<x-button.primary href="{{ route('visitors.schedule', ['visitor_search' => $visitor['visitor_ref']]) }}">Request Visit</x-button.primary>
@endpermission
@endcan
</x-page-heading>
@if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <x-card><x-section-heading title="Identity Summary"/>
        <dl>
            <x-detail-row label="Status">{{ $visitor['status'] }}</x-detail-row>
            <x-detail-row label="Gender">{{ $visitor['gender'] }}</x-detail-row>
            <x-detail-row label="Document Type">{{ $visitor['id_type'] }}</x-detail-row>
            <x-detail-row label="Document Number (masked)">{{ $visitor['id_number_masked'] }}</x-detail-row>
            <x-detail-row label="Phone (masked)">{{ $visitor['phone'] }}</x-detail-row>
            <x-detail-row label="Registered">{{ $visitor['registered_date'] }}</x-detail-row>
            <x-detail-row label="Visible Visits">{{ $visitor['visit_count'] }}</x-detail-row>
        </dl>
        <p class="mt-4 text-xs text-[#64748B]">Contact corrections require registration authority. Registration does not approve a visit.</p>
    </x-card>
    <x-card class="lg:col-span-2" :padding="false"><div class="p-4"><x-section-heading title="Visit History"/></div>@include('visitors._visit-table', ['visits' => $visits])</x-card>
</div>
@endsection