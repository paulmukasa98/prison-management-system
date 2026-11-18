@extends('layouts.app')
@section('title', 'Schedule a Visit')
@section('content')
<x-page-heading title="Schedule a Visit" description="Request a time window, then obtain approval and confirm scheduling." :breadcrumbs="[['label'=>'Visitors','route'=>'visitors.index'],['label'=>'Schedule a Visit']]"/>
@if($errors->any())<x-alert type="error" class="mb-5"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert>@endif
<x-card class="mb-5">
    <x-section-heading title="Find Participants" description="Choices are limited to 100 matches within your authorized facilities. Narrow your search when needed."/>
    <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
        <x-form.input name="visitor_search" label="Visitor Name or Reference" :value="request('visitor_search')" class="grow"/>
        <x-form.input name="inmate_search" label="Inmate Name or Prisoner Number" :value="request('inmate_search')" class="grow"/>
        <x-button.secondary type="submit">Find</x-button.secondary>
    </form>
</x-card>
<form method="POST" action="{{ route('visitors.visits.store') }}" class="space-y-5">
    @csrf
    <x-card>
        <x-section-heading title="Visitor, Custody Episode and Facility"/>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <x-form.select name="visitor_id" label="Visitor" required><option value="">Select visitor</option>@foreach($visitors as $visitor)<option value="{{ $visitor->id }}" @selected((string) old('visitor_id') === (string) $visitor->id)>{{ $visitor->first_name }} {{ $visitor->last_name }} · {{ $visitor->visitor_ref }}</option>@endforeach</x-form.select>
            <x-form.select name="admission_episode_id" label="Inmate / Active Admission" required><option value="">Select custody episode</option>@foreach($episodes as $episode)<option value="{{ $episode->id }}" @selected((string) old('admission_episode_id') === (string) $episode->id)>{{ $episode->inmate->prisoner_number }} · {{ $episode->inmate->first_name }} {{ $episode->inmate->last_name }} · {{ $episode->locations->firstWhere('left_at', null)?->facility?->name }}</option>@endforeach</x-form.select>
            <x-form.select name="facility_id" label="Visit Facility" required><option value="">Select current custody facility</option>@foreach($facilities as $facility)<option value="{{ $facility->id }}" @selected((string) old('facility_id') === (string) $facility->id)>{{ $facility->name }}</option>@endforeach</x-form.select>
        </div>
    </x-card>
    <x-card>
        <x-section-heading title="Proposed Visit Window" :description="'All times use '.config('visitation.timezone').'. Availability is checked when saving.'"/>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-form.input name="scheduled_start_at" label="Start" type="datetime-local" required :value="old('scheduled_start_at')"/>
            <x-form.input name="scheduled_end_at" label="End" type="datetime-local" required :value="old('scheduled_end_at')"/>
            <x-form.select name="purpose" label="Purpose" required><option value="">Select purpose</option>@foreach(config('visitation.purposes') as $purpose)<option @selected(old('purpose') === $purpose)>{{ $purpose }}</option>@endforeach</x-form.select>
            <x-form.select name="relationship" label="Relationship to Inmate" required><option value="">Select relationship</option>@foreach(config('visitation.relationships') as $relationship)<option @selected(old('relationship') === $relationship)>{{ $relationship }}</option>@endforeach</x-form.select>
            <x-form.textarea name="notes" label="Notes">{{ old('notes') }}</x-form.textarea>
        </div>
        <label class="flex items-start gap-2 mt-5 text-sm"><input name="confirmed" type="checkbox" value="1" required class="mt-1"><span>I have reviewed the request. Approval and scheduling are required before check-in.</span></label>
        <div class="flex flex-wrap gap-3 mt-5"><x-button.primary type="submit">Submit Visit Request</x-button.primary>@permission('visitors.read')
<x-button.secondary href="{{ route('visitors.records') }}">Back to Records</x-button.secondary>
@endpermission</div>
    </x-card>
</form>
@endsection