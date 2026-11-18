@props(['steps' => [], 'current' => 1, 'reactiveCurrent' => null])

<div {{ $attributes->merge(['class' => 'flex items-center w-full']) }} role="list" aria-label="Form progress">
    @foreach($steps as $index => $step)
    @php
    $num      = $index + 1;
    $done     = $num < $current;
    $active   = $num === $current;
    $isLast   = $loop->last;
    @endphp
    <div class="flex items-center {{ $isLast ? '' : 'flex-1' }}" role="listitem">
        {{-- Step circle --}}
        <div class="flex flex-col items-center gap-1">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 ring-2 transition-all
                {{ $reactiveCurrent === null && $done ? 'bg-brand text-white ring-brand' : '' }}
                {{ $reactiveCurrent === null && $active ? 'bg-brand text-white ring-brand ring-offset-2' : '' }}
                {{ $reactiveCurrent === null && !$done && !$active ? 'bg-white text-[#94A3B8] ring-[#E2E8F0]' : '' }}"
                @if($reactiveCurrent !== null)
                    :class="{
                        'bg-brand text-white ring-brand': {{ $num }} <= ({{ $reactiveCurrent }}),
                        'ring-offset-2': {{ $num }} === ({{ $reactiveCurrent }}),
                        'bg-white text-[#94A3B8] ring-[#E2E8F0]': {{ $num }} > ({{ $reactiveCurrent }})
                    }"
                    :aria-current="{{ $num }} === ({{ $reactiveCurrent }}) ? 'step' : 'false'"
                @else
                    aria-current="{{ $active ? 'step' : 'false' }}"
                @endif
            >
                @if($reactiveCurrent !== null || $done)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"
                        @if($reactiveCurrent !== null) x-show="{{ $num }} < ({{ $reactiveCurrent }})" x-cloak @endif>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
                @if($reactiveCurrent !== null)
                    <span x-show="{{ $num }} >= ({{ $reactiveCurrent }})">{{ $num }}</span>
                @elseif(!$done)
                    {{ $num }}
                @endif
            </div>
            <span class="hidden sm:block text-xs font-medium leading-tight text-center max-w-16
                {{ $reactiveCurrent === null ? ($active ? 'text-brand' : ($done ? 'text-[#0F172A]' : 'text-[#94A3B8]')) : '' }}"
                @if($reactiveCurrent !== null)
                    :class="{{ $num }} === ({{ $reactiveCurrent }}) ? 'text-brand' : ({{ $num }} < ({{ $reactiveCurrent }}) ? 'text-[#0F172A]' : 'text-[#94A3B8]')"
                @endif>
                {{ $step }}
            </span>
        </div>
        {{-- Connector line --}}
        @if(!$isLast)
        <div class="flex-1 h-0.5 mx-2 mt-[-16px]
            {{ $reactiveCurrent === null ? ($done ? 'bg-brand' : 'bg-[#E2E8F0]') : '' }}"
            @if($reactiveCurrent !== null)
                :class="{{ $num }} < ({{ $reactiveCurrent }}) ? 'bg-brand' : 'bg-[#E2E8F0]'"
            @endif></div>
        @endif
    </div>
    @endforeach
</div>
