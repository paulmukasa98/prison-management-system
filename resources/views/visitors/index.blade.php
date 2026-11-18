@extends('layouts.app')
@section('title', 'Visitor Directory')
@section('content')
<x-page-heading title="Visitor Directory" description="Visitor identities visible within your authorized facilities." :breadcrumbs="[['label'=>'Visitors']]">
    @can('register', \App\Models\Visitor::class)<x-button.primary href="{{ route('visitors.create') }}">Register Visitor</x-button.primary>@endcan
    @permission('visitors.read')
<x-button.secondary href="{{ route('visitors.records') }}">Visit Records</x-button.secondary>
@endpermission
</x-page-heading>
@if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
<div class="grid grid-cols-2 gap-4 mb-5">
    <x-card><p class="text-sm text-[#64748B]">Registered Identities</p><p class="text-2xl font-bold">{{ $counts['total'] }}</p></x-card>
    <x-card><p class="text-sm text-[#64748B]">With Visible Visit History</p><p class="text-2xl font-bold">{{ $counts['with_visits'] }}</p></x-card>
</div>
<x-card class="mb-5"><form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
    <x-form.input name="search" label="Search Visitor" placeholder="Name or visitor reference" :value="request('search')" class="grow"/>
    <x-form.select name="status" label="Status"><option value="">All statuses</option>@foreach(['Registered','Under Review','Blocked'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</x-form.select>
    <x-button.primary type="submit">Search</x-button.primary>@permission('visitors.read')
<x-button.secondary href="{{ route('visitors.index') }}">Clear</x-button.secondary>
@endpermission
</form></x-card>
<x-card :padding="false">
    <div class="table-responsive"><table class="w-full text-sm text-left">
        <thead class="bg-[#F8FAFC]"><tr>@foreach(['Reference','Name','Identity Document','Phone (masked)','Status','Visits'] as $heading)<th class="px-4 py-3">{{ $heading }}</th>@endforeach</tr></thead>
        <tbody class="divide-y divide-[#E2E8F0]">@forelse($visitors as $visitor)
            <tr><td class="px-4 py-3 font-mono text-xs"><a class="text-[#2563EB] hover:underline" href="{{ route('visitors.show', $visitor['id']) }}">{{ $visitor['visitor_ref'] }}</a></td><td class="px-4 py-3">{{ $visitor['full_name'] }}</td><td class="px-4 py-3">{{ $visitor['id_number_masked'] }}</td><td class="px-4 py-3">{{ $visitor['phone'] }}</td><td class="px-4 py-3">{{ $visitor['status'] }}</td><td class="px-4 py-3">{{ $visitor['visit_count'] }}</td></tr>
        @empty<tr><td colspan="6" class="p-5"><x-empty-state title="No visitors found" description="Try another search or register a visitor in your facility."/></td></tr>@endforelse</tbody>
    </table></div>
    <div class="p-4">{{ $visitors->links() }}</div>
</x-card>
@endsection