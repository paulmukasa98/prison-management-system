@props(['visits' => [], 'heading' => 'Upcoming Schedule'])

@php
// Group open visits by date for the schedule summary panel.
$byDate = [];
foreach ($visits as $v) {
    if (! in_array($v['status'], ['Requested', 'Pending Review', 'Scheduled', 'Checked In'])) {
        continue;
    }
    $byDate[$v['visit_date']][] = $v;
}
ksort($byDate);
@endphp

<x-card>
    <x-section-heading :title="$heading" description="Open requests and scheduled slots from demo fixtures — not a reservation system."/>
    @if(empty($byDate))
        <x-empty-state title="Nothing scheduled" description="No open visit requests or scheduled slots."/>
    @else
    <div class="space-y-4">
        @foreach($byDate as $date => $slots)
        <div>
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-2">
                {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
                <span class="normal-case font-normal text-[#94A3B8]">({{ count($slots) }} slot{{ count($slots) === 1 ? '' : 's' }})</span>
            </p>
            <div class="space-y-2">
                @foreach($slots as $slot)
                <a href="{{ route('visitors.visits.show', $slot['id']) }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-[#E2E8F0] hover:border-brand hover:bg-[#F8FAFC] transition-colors">
                    <span class="text-xs font-mono font-semibold text-[#2563EB] w-12 flex-shrink-0">{{ $slot['start_time'] }}</span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm font-medium text-[#0F172A] truncate">{{ \App\Support\Demo\VisitorDemoData::enrich(\App\Support\Demo\VisitorDemoData::find($slot['visitor_id']))['full_name'] }} → {{ $slot['inmate_name'] }}</span>
                        <span class="block text-xs text-[#64748B]">{{ $slot['facility'] }} · {{ $slot['purpose'] }}</span>
                    </span>
                    @if(!empty($slot['conflicts']))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700 flex-shrink-0" title="Another open visit shares this slot">
                            conflict
                        </span>
                    @endif
                    <x-visit-status-badge :status="$slot['status']" class="flex-shrink-0"/>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-card>
