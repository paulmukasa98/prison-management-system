{{-- Print partial: population --}}
<h2 class="text-lg font-semibold mb-3">Headline figures</h2>
<table class="w-full text-sm border-collapse mb-6">
    <tbody>
        <tr class="border-b"><td class="py-2 font-medium">Total in custody at {{ $data['snapshotDate'] }}</td><td class="py-2 text-right tabular-nums">{{ $data['total'] }}</td></tr>
        @foreach($data['gender'] as $label => $count)
        <tr class="border-b"><td class="py-2">{{ $label }}</td><td class="py-2 text-right tabular-nums">{{ $count }}</td></tr>
        @endforeach
        @foreach($data['classification'] as $label => $count)
        <tr class="border-b"><td class="py-2">{{ $label }}</td><td class="py-2 text-right tabular-nums">{{ $count }}</td></tr>
        @endforeach
    </tbody>
</table>
<h2 class="text-lg font-semibold mb-3">By facility</h2>
<table class="w-full text-sm border-collapse">
    <thead><tr class="border-b-2 border-[#102A43] text-left"><th class="py-2">Facility</th><th class="py-2 text-right">Population</th></tr></thead>
    <tbody>
        @foreach($data['byFacility'] as $name => $count)
        <tr class="border-b"><td class="py-2">{{ $name }}</td><td class="py-2 text-right tabular-nums">{{ $count }}</td></tr>
        @endforeach
    </tbody>
</table>
