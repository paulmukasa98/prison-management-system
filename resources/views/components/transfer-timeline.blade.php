@props(['events' => []])

@if(empty($events))
    <x-empty-state title="No timeline events" description="No transfer events have been recorded yet."/>
@else
<ol class="relative border-l-2 border-[#E2E8F0] ml-3 space-y-0">
    @foreach($events as $event)
    @php
    $typeStyles = [
        'created'   => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>'],
        'submitted' => ['bg' => 'bg-slate-100',  'text' => 'text-slate-600',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
        'approved'  => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'rejected'  => ['bg' => 'bg-red-100',    'text' => 'text-red-600',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'dispatched'=> ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>'],
        'arrived'   => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'],
        'completed' => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ];
    $s = $typeStyles[$event['type']] ?? $typeStyles['created'];
    @endphp
    <li class="mb-6 ml-6">
        <span class="absolute -left-4 flex items-center justify-center w-7 h-7 rounded-full {{ $s['bg'] }} {{ $s['text'] }} ring-2 ring-white">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                {!! $s['icon'] !!}
            </svg>
        </span>
        <div class="p-3 bg-white rounded-lg border border-[#E2E8F0] shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-semibold text-[#0F172A]">{{ $event['event'] }}</p>
                <time class="text-xs text-[#94A3B8] whitespace-nowrap flex-shrink-0">
                    {{ \Carbon\Carbon::parse($event['ts'])->format('d M Y, H:i') }}
                </time>
            </div>
            <p class="text-xs text-[#64748B] mt-1 leading-relaxed">{{ $event['detail'] }}</p>
            @if(!empty($event['terminal']))
                <span class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium {{ $event['type'] === 'rejected' ? 'text-red-600' : 'text-green-600' }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                    </svg>
                    Terminal event
                </span>
            @endif
        </div>
    </li>
    @endforeach
</ol>
@endif
