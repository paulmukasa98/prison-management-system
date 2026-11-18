{{-- Print partial: admissions & discharges --}}
<h2 class="text-lg font-semibold mb-3">Summary</h2>
<table class="w-full text-sm border-collapse mb-6">
    <tbody>
        <tr class="border-b"><td class="py-2 font-medium">Admissions in period</td><td class="py-2 text-right tabular-nums">{{ count($data['admissions']) }}</td></tr>
        <tr class="border-b"><td class="py-2 font-medium">Discharges in period</td><td class="py-2 text-right tabular-nums">{{ count($data['discharges']) }}</td></tr>
    </tbody>
</table>
<h2 class="text-lg font-semibold mb-3">Episodes</h2>
<table class="w-full text-sm border-collapse">
    <thead><tr class="border-b-2 border-[#102A43] text-left"><th class="py-2">Type</th><th class="py-2">Prisoner</th><th class="py-2">Facility</th><th class="py-2 text-right">Date</th></tr></thead>
    <tbody>
        @foreach($data['admissions'] as $a)
        <tr class="border-b"><td class="py-2">Admission</td><td class="py-2">{{ $a['first_name'].' '.$a['last_name'] }} ({{ $a['prisoner_number'] }})</td><td class="py-2">{{ $a['facility'] }}</td><td class="py-2 text-right">{{ \Carbon\Carbon::parse($a['admission_date'])->format('d M Y') }}</td></tr>
        @endforeach
        @foreach($data['discharges'] as $d)
        <tr class="border-b"><td class="py-2">Discharge</td><td class="py-2">{{ $d['first_name'].' '.$d['last_name'] }} ({{ $d['prisoner_number'] }})</td><td class="py-2">{{ $d['facility'] }}</td><td class="py-2 text-right">{{ \Carbon\Carbon::parse($d['actual_discharge'])->format('d M Y') }}</td></tr>
        @endforeach
    </tbody>
</table>
