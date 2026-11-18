{{-- Print partial: occupancy --}}
<h2 class="text-lg font-semibold mb-3">Occupancy</h2>
<table class="w-full text-sm border-collapse">
    <thead><tr class="border-b-2 border-[#102A43] text-left"><th class="py-2">Facility</th><th class="py-2 text-right">Population</th><th class="py-2 text-right">Capacity</th><th class="py-2 text-right">Occupancy</th></tr></thead>
    <tbody>
        @foreach($data['rows'] as $row)
        <tr class="border-b">
            <td class="py-2">{{ $row['facility'] }}</td>
            <td class="py-2 text-right tabular-nums">{{ $row['population'] }}</td>
            <td class="py-2 text-right tabular-nums">{{ $row['capacity'] ?? 'unknown' }}</td>
            <td class="py-2 text-right tabular-nums">{{ $row['occupancy_pct'] !== null ? $row['occupancy_pct'].'%' : '—' }}</td>
        </tr>
        @endforeach
        <tr class="border-t-2 border-[#102A43] font-semibold"><td class="py-2">Total</td><td class="py-2 text-right tabular-nums">{{ $data['totals']['population'] }}</td><td class="py-2 text-right tabular-nums">{{ $data['totals']['capacity'] }}</td><td class="py-2"></td></tr>
    </tbody>
</table>
