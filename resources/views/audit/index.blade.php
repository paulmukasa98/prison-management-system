@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
<x-page-heading title="Audit Log" description="Read-only event history." :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Audit']]"/>
<x-card class="mb-5">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <x-form.input name="actor_id" label="Actor ID" type="number" :value="request('actor_id')"/>
        <x-form.input name="action" label="Action" :value="request('action')"/>
        <x-form.input name="resource_type" label="Resource Type" :value="request('resource_type')"/>
        <x-form.input name="facility_id" label="Facility ID" type="number" :value="request('facility_id')"/>
        <x-form.input name="from" label="From" type="date" :value="request('from')"/>
        <x-form.input name="to" label="To" type="date" :value="request('to')"/>
        <div class="flex gap-3"><x-button.primary type="submit">Filter</x-button.primary>@permission('audit.view')
<x-button.secondary href="{{ route('audit.index') }}">Clear</x-button.secondary>
@endpermission</div>
    </form>
</x-card>
<x-card :padding="false"><div class="table-responsive"><table class="w-full text-sm text-left">
    <thead class="bg-[#F8FAFC]"><tr>@foreach(['Reference','Timestamp','Actor','Action','Resource','Facility','Outcome'] as $heading)<th class="px-4 py-3">{{ $heading }}</th>@endforeach</tr></thead>
    <tbody class="divide-y divide-[#E2E8F0]">@forelse($events as $event)<tr>
        <td class="px-4 py-3 text-xs font-mono"><a class="text-[#2563EB] hover:underline" href="{{ route('audit.show', $event) }}">{{ $event->audit_ref }}</a></td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $event->occurred_at->setTimezone(config('reporting.timezone'))->format('Y-m-d H:i') }}</td>
        <td class="px-4 py-3">{{ $event->actor?->name ?? 'System / historical actor' }}</td>
        <td class="px-4 py-3">{{ $event->action }}</td><td class="px-4 py-3">{{ $event->resource_type }}</td><td class="px-4 py-3">{{ $event->facility?->name ?? 'National / security' }}</td><td class="px-4 py-3">{{ $event->outcome }}</td>
    </tr>@empty<tr><td colspan="7" class="p-5"><x-empty-state title="No audit events found" description="Adjust your filters."/></td></tr>@endforelse</tbody>
</table></div><div class="p-4">{{ $events->links() }}</div></x-card>
@endsection