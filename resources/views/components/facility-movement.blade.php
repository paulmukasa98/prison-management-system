@props(['from', 'to', 'size' => 'sm'])

@php $textSize = $size === 'lg' ? 'text-sm font-semibold' : 'text-xs font-medium'; @endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <span class="{{ $textSize }} text-[#0F172A] truncate max-w-32 sm:max-w-none">{{ $from }}</span>
    <svg class="w-4 h-4 text-[#2563EB] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="to" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
    </svg>
    <span class="{{ $textSize }} text-[#0F172A] truncate max-w-32 sm:max-w-none">{{ $to }}</span>
</div>
