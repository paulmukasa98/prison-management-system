{{-- Print partial: personnel --}}
<h2 class="text-lg font-semibold mb-3">Summary</h2>
<table class="w-full text-sm border-collapse mb-6">
    <tbody>
        <tr class="border-b"><td class="py-2 font-medium">Active personnel in scope</td><td class="py-2 text-right tabular-nums">{{ $data['total'] }}</td></tr>
        @if($data['staffPerPrisoner'])
        <tr class="border-b"><td class="py-2 font-medium">Prisoners per active officer (demo)</td><td class="py-2 text-right tabular-nums">{{ $data['staffPerPrisoner'] }}</td></tr>
        @endif
    </tbody>
</table>
<h2 class="text-lg font-semibold mb-3">By facility</h2>
<table class="w-full text-sm border-collapse">
    <thead><tr class="border-b-2 border-[#102A43] text-left"><th class="py-2">Facility</th><th class="py-2 text-right">Staff</th></tr></thead>
    <tbody>
        @foreach($data['byFacility'] as $name => $count)
        <tr class="border-b"><td class="py-2">{{ $name }}</td><td class="py-2 text-right tabular-nums">{{ $count }}</td></tr>
        @endforeach
    </tbody>
</table>
