@extends('layouts.app')
@section('title', 'Audit Event')
@section('content')
<x-page-heading :title="$event->audit_ref" description="Immutable recorded event." :breadcrumbs="[['label'=>'Audit','route'=>'audit.index'],['label'=>'Event']]"/>
<x-card class="max-w-4xl"><dl>
    @if($event->resource_type === 'Report' && array_key_exists($event->resource_reference ?? '', \App\Services\ReportingService::TITLES))
        @permission('reports.view')
        <x-detail-row label="Report"><a class="text-[#2563EB] hover:underline" href="{{ route('reports.'.$event->resource_reference) }}">Open report</a></x-detail-row>
        @endpermission
    @endif
    <x-detail-row label="Timestamp">{{ $event->occurred_at->setTimezone(config('reporting.timezone'))->format('Y-m-d H:i:s') }}</x-detail-row>
    <x-detail-row label="Actor">{{ $event->actor?->name ?? 'System / historical actor' }}</x-detail-row>
    <x-detail-row label="Action">{{ $event->action }}</x-detail-row>
    <x-detail-row label="Outcome">{{ $event->outcome }}</x-detail-row>
    <x-detail-row label="Resource">{{ $event->resource_type }} · {{ $event->resource_reference }}</x-detail-row>
    <x-detail-row label="Facility">{{ $event->facility?->name ?? 'National / security' }}</x-detail-row>
    <x-detail-row label="Correlation ID">{{ $event->correlation_id }}</x-detail-row>
    <x-detail-row label="Signature">{{ $signatureValid === null ? 'Historical event — unsigned' : ($signatureValid ? 'Verified' : 'Integrity check failed') }}</x-detail-row>
</dl><h2 class="font-semibold mt-5 mb-3">Permitted Metadata</h2><pre class="text-xs whitespace-pre-wrap break-words bg-[#F8FAFC] p-4 rounded-lg">{{ json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></x-card>
@endsection
