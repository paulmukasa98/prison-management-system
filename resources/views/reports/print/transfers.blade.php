{{-- Print partial: transfers --}}
<h2 class="text-lg font-semibold mb-3">Summary</h2>
<table class="w-full text-sm border-collapse mb-6">
    <tbody>
        <tr class="border-b"><td class="py-2 font-medium">Requests in period</td><td class="py-2 text-right tabular-nums">{{ count($data['requested']) }}</td></tr>
        <tr class="border-b"><td class="py-2 font-medium">Completed movements in period</td><td class="py-2 text-right tabular-nums">{{ count($data['completed']) }}</td></tr>
        <tr class="border-b"><td class="py-2 font-medium">Open pipeline (all time)</td><td class="py-2 text-right tabular-nums">{{ $data['pending'] }}</td></tr>
    </tbody>
</table>
<h2 class="text-lg font-semibold mb-3">Request register</h2>
<table class="w-full text-sm border-collapse">
    <thead><tr class="border-b-2 border-[#102A43] text-left"><th class="py-2">Ref</th><th class="py-2">Route</th><th class="py-2">Status</th><th class="py-2 text-right">Requested</th></tr></thead>
    <tbody>
        @foreach($data['requested'] as $t)
        <tr class="border-b"><td class="py-2 font-mono text-xs">{{ $t['transfer_ref'] }}</td><td class="py-2">{{ $t['origin_facility'] }} → {{ $t['destination_facility'] }}</td><td class="py-2">{{ $t['status'] }}</td><td class="py-2 text-right">{{ \Carbon\Carbon::parse($t['request_date'])->format('d M Y') }}</td></tr>
        @endforeach
    </tbody>
</table>
