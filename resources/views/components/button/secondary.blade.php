@props([
    'type'     => 'button',
    'href'     => null,
    'disabled' => false,
    'size'     => 'md',
])

@php
$sizeClasses = match($size) {
    'sm'  => 'px-3 py-1.5 text-xs',
    'lg'  => 'px-6 py-3 text-base',
    default => 'px-4 py-2 text-sm',
};
$base = "inline-flex items-center gap-2 font-medium rounded-lg border transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand $sizeClasses";
$colors = $disabled
    ? 'bg-[#F1F5F9] text-[#94A3B8] border-[#E2E8F0] cursor-not-allowed'
    : 'bg-white text-[#0F172A] border-[#E2E8F0] hover:bg-[#F8FAFC] active:bg-[#F1F5F9]';
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $colors"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => "$base $colors"]) }}>
        {{ $slot }}
    </button>
@endif
