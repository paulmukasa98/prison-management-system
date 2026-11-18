@extends('layouts.app')
@section('title', 'Visit Records')
@section('content')
<x-page-heading title="Visit Records" description="Authorized visit occurrences and lifecycle history." :breadcrumbs="[['label'=>'Visitors','route'=>'visitors.index'],['label'=>'Visit Records']]">
    @can('schedule', \App\Models\Visitor::class)@permission('visits.schedule')
<x-button.primary href="{{ route('visitors.schedule') }}">Request a Visit</x-button.primary>
@endpermission
@endcan
</x-page-heading>
<div class="flex flex-wrap gap-2 mb-5">@foreach($counts as $status => $count)<span class="px-3 py-2 bg-white rounded-lg border border-[#E2E8F0] text-sm">{{ $status }}: <strong>{{ $count }}</strong></span>@endforeach</div>
@if($errors->any())<x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>@endif
<x-card class="mb-5"><form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <x-form.input name="search" label="Inmate, Visitor or Reference" :value="request('search')"/>
    <x-form.select name="facility_id" label="Facility"><option value="">All authorized facilities</option>@foreach($facilities as $facility)<option value="{{ $facility->id }}" @selected((string) request('facility_id') === (string) $facility->id)>{{ $facility->name }}</option>@endforeach</x-form.select>
    <x-form.select name="status" label="Status"><option value="">All statuses</option>@foreach($statuses as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</x-form.select>
    <x-form.input name="from" label="From" type="date" :value="request('from')"/>
    <x-form.input name="to" label="To" type="date" :value="request('to')"/>
    <div class="flex gap-3 items-end"><x-button.primary type="submit">Filter Visits</x-button.primary>@permission('visitors.read')
<x-button.secondary href="{{ route('visitors.records') }}">Clear</x-button.secondary>
@endpermission</div>
</form></x-card>
<x-card :padding="false">@include('visitors._visit-table', ['visits' => $visits])</x-card>
@endsection