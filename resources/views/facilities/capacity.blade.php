@extends('layouts.app')
@section('title', 'Facility Capacity Overview')
@section('content')

<x-page-heading title="Facility Capacity Overview"
    description="Occupancy monitoring across all prison facilities — authorized database records."
    :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Facilities','route'=>'facilities.index'],['label'=>'Capacity Overview']]"
/>



{{-- Summary --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Facilities</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ $totals['total'] }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Capacity</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ number_format($totals['capacity']) }}</p><p class="text-xs text-[#94A3B8] mt-0.5">Recorded capacity figures</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Total Population</p><p class="text-2xl font-bold text-[#0F172A] mt-1">{{ number_format($totals['population']) }}</p></div>
    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4"><p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide">Overall Occupancy</p>
        @php $overall = $totals['capacity'] > 0 ? round(($totals['population']/$totals['capacity'])*100, 1) : 0; @endphp
        <p class="text-2xl font-bold {{ $overall >= 90 ? 'text-red-600' : 'text-[#0F172A]' }} mt-1">{{ $overall }}%</p>
    </div>
</div>

{{-- Chart --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm mb-5">
    <div class="px-5 pt-5 pb-4 border-b border-[#E2E8F0]">
        <h2 class="text-sm font-semibold text-[#0F172A]">Capacity vs Population by Facility</h2>
        <p class="text-xs text-[#64748B]">Grouped bar chart — authorized database records</p>
    </div>
    <div class="px-5 py-5">
        <div class="relative" style="height:280px;">
            <canvas id="chart-capacity" role="img" aria-label="Bar chart showing capacity vs population by facility"></canvas>
        </div>
        
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-[#E2E8F0]">
        <h2 class="text-sm font-semibold text-[#0F172A]">Detailed Capacity Table</h2>
        <p class="text-xs text-[#64748B]">Accessible table alongside chart — recorded data</p>
    </div>
    <div class="table-responsive">
        <table class="w-full text-sm text-left" aria-label="Facility capacity table">
            <thead class="bg-[#F8FAFC] border-b border-[#E2E8F0]">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Facility</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Capacity</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Population</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide">Occupancy</th>
                    <th class="px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wide text-right">Profile</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
                @foreach($facilities as $f)
                <tr class="hover:bg-[#F8FAFC]">
                    <td class="px-4 py-3">
                        <a href="{{ route('facilities.show', $f['id']) }}" class="text-sm font-medium text-[#0F172A] hover:text-[#2563EB]">{{ $f['name'] }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-[#64748B]">{{ $f['type'] }}</td>
                    <td class="px-4 py-3 text-sm tabular-nums">{{ $f['capacity'] === null ? 'Unknown' : number_format($f['capacity']) }}</td>
                    <td class="px-4 py-3 text-sm tabular-nums">{{ number_format($f['current_population']) }}</td>
                    <td class="px-4 py-3">
                        <x-occupancy-bar :population="$f['current_population']" :capacity="$f['capacity']" :pct="$f['occupancy_pct']" class="min-w-32"/>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('facilities.show', $f['id']) }}" class="text-xs text-[#2563EB] hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = JSON.parse({{ Js::from($capacityJson) }});
    const ctx = document.getElementById('chart-capacity');
    if (!ctx || typeof Chart === 'undefined') { return; }
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(f => f.name),
            datasets: [
                {
                    label: 'Population',
                    data: data.map(f => f.population),
                    backgroundColor: 'rgba(37,99,235,0.75)',
                    borderColor: '#2563EB',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Capacity (authenticated)',
                    data: data.map(f => f.capacity),
                    backgroundColor: 'rgba(226,232,240,0.6)',
                    borderColor: '#CBD5E1',
                    borderWidth: 1,
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end' },
                tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.raw.toLocaleString()}` } },
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                x: { grid: { display: false }, ticks: { maxRotation: 30, minRotation: 0 } },
            },
        },
    });
});
</script>
@endsection
