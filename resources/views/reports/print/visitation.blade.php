{{-- Print partial: visitation --}}
<h2 class="text-lg font-semibold mb-3">Summary</h2>
<table class="w-full text-sm border-collapse mb-6">
    <tbody>
        <tr class="border-b"><td class="py-2 font-medium">Visits in period</td><td class="py-2 text-right tabular-nums">{{ $data['total'] }}</td></tr>
        @foreach($data['byStatus'] as $label => $count)
        <tr class="border-b"><td class="py-2">{{ $label }}</td><td class="py-2 text-right tabular-nums">{{ $count }}</td></tr>
        @endforeach
    </tbody>
</table>
<h2 class="text-lg font-semibold mb-3">Purpose mix</h2>
<table class="w-full text-sm border-collapse">
    <thead><tr class="border-b-2 border-[#102A43] text-left"><th class="py-2">Purpose</th><th class="py-2 text-right">Visits</th></tr></thead>
    <tbody>
        @foreach($data['byPurpose'] as $label => $count)
        <tr class="border-b"><td class="py-2">{{ $label }}</td><td class="py-2 text-right tabular-nums">{{ $count }}</td></tr>
        @endforeach
    </tbody>
</table>
