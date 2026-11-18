@props(['events' => []])

@if(empty($events))
    <x-empty-state title="No visit events" description="No lifecycle events have been recorded for this visit."/>
@else
<ol class="relative border-l-2 border-[#E2E8F0] ml-3 space-y-0">
    @foreach($events as $event)
    <li class="mb-6 ml-6">
        <span class="absolute -left-4 flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-600 ring-2 ring-white">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <div class="p-3 bg-white rounded-lg border border-[#E2E8F0] shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-semibold text-[#0F172A]">{{ $event['event'] }}</p>
                <time class="text-xs text-[#94A3B8] whitespace-nowrap flex-shrink-0">
                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('d M Y, H:i') }}
                </time>
            </div>
            <p class="text-xs text-[#64748B] mt-1 leading-relaxed">{{ $event['detail'] }}</p>
        </div>
    </li>
    @endforeach
</ol>
@endif
