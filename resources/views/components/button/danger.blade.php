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
$base = "inline-flex items-center gap-2 font-medium rounded-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 $sizeClasses";
$colors = $disabled
    ? 'bg-red-200 text-white cursor-not-allowed'
    : 'bg-[#DC2626] text-white hover:bg-red-700 active:bg-red-800';
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $colors"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => "$base $colors"]) }}>
        {{ $slot }}
    </button>
@endif
