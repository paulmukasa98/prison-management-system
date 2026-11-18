@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('content')
<x-page-heading title="Reports & Analytics" description="Facility-scoped population, movement, transfers, staffing and visits from recorded data." :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Reports']]"/>
<x-report-filters :from="$from" :to="$to" :facilities="$facilities" :facility-id="$facilityId" :base="route('reports.index')"/>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-5">@foreach($reportTitles as $key => $title)
    <a href="{{ route('reports.'.$key, ['from' => $from, 'to' => $to, 'facility_id' => $facilityId]) }}" class="bg-white rounded-xl border border-[#E2E8F0] p-5 hover:border-brand">
        <h2 class="text-lg font-semibold">{{ $title }}</h2><p class="mt-3 text-sm text-[#2563EB]">Open report →</p>
    </a>
@endforeach</div>
<p class="mt-5 text-sm text-[#64748B]">Reports exclude personal contact details and identity documents. Printing and CSV downloads require export authority.</p>
@endsection