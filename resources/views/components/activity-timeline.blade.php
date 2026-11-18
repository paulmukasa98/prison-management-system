@props(['events' => [], 'emptyMessage' => 'No activity recorded.'])

@if(empty($events))
    <x-empty-state title="No activity" description="{{ $emptyMessage }}" />
@else
<ol class="relative border-l-2 border-[#E2E8F0] ml-3 space-y-0">
    @foreach($events as $event)
    @php
    $typeColors = [
        'admission'  => 'bg-blue-100 text-blue-600',
        'discharge'  => 'bg-purple-100 text-purple-600',
        'edit'       => 'bg-amber-100 text-amber-700',
        'transfer'   => 'bg-green-100 text-green-700',
        'system'     => 'bg-slate-100 text-slate-600',
        'visit'      => 'bg-pink-100 text-pink-600',
    ];
    $typeIcons = [
        'admission'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',
        'discharge'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        'edit'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>',
        'transfer'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>',
        'system'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'visit'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
    ];
    $tc   = $typeColors[$event['type']] ?? $typeColors['system'];
    $icon = $typeIcons[$event['type']]  ?? $typeIcons['system'];
    @endphp
    <li class="mb-6 ml-6">
        <span class="absolute -left-4 flex items-center justify-center w-7 h-7 rounded-full {{ $tc }} ring-2 ring-white">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                {!! $icon !!}
            </svg>
        </span>
        <div class="p-3 bg-white rounded-lg border border-[#E2E8F0] shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-semibold text-[#0F172A]">{{ $event['event'] }}</p>
                <time class="text-xs text-[#94A3B8] whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('d M Y, H:i') }}
                </time>
            </div>
            <p class="text-xs text-[#64748B] mt-1">{{ $event['detail'] }}</p>
            @if(!empty($event['actor']))
                <p class="text-xs text-[#94A3B8] mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $event['actor'] }} <em class="not-italic">(demo role)</em>
                </p>
            @endif
        </div>
    </li>
    @endforeach
</ol>
@endif
