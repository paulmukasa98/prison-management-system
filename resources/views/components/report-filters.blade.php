@props(['from', 'to', 'facilities', 'facilityId', 'base'])
<form method="GET" action="{{ $base }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end gap-4 p-4 bg-white rounded-xl border border-[#E2E8F0]" aria-label="Report filters">
    <x-form.input name="from" label="From" type="date" required :value="$from"/>
    <x-form.input name="to" label="To" type="date" required :value="$to"/>
    <x-form.select name="facility_id" label="Facility Scope"><option value="">All authorized facilities</option>@foreach($facilities as $facility)<option value="{{ $facility->id }}" @selected($facilityId === $facility->id)>{{ $facility->name }}</option>@endforeach</x-form.select>
    <div class="flex gap-3"><x-button.primary type="submit">Apply</x-button.primary><x-button.secondary href="{{ $base }}">Reset</x-button.secondary></div>
</form>