@props([
    'type'     => 'button',
    'href'     => null,
    'disabled' => false,
    'loading'  => false,
    'size'     => 'md',  // sm | md | lg
])

@php
$sizeClasses = match($size) {
    'sm'  => 'px-3 py-1.5 text-xs',
    'lg'  => 'px-6 py-3 text-base',
    default => 'px-4 py-2 text-sm',
};
$base = "inline-flex items-center gap-2 font-medium rounded-lg transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand $sizeClasses";
$colors = $disabled
    ? 'bg-brand-muted text-white cursor-not-allowed'
    : 'bg-brand text-white hover:bg-brand-hover active:bg-brand-active';
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$base $colors"]) }}>
        @if($loading)
            <x-loading-spinner class="w-4 h-4"/>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => "$base $colors"]) }}
    >
        @if($loading)
            <x-loading-spinner class="w-4 h-4"/>
        @endif
        {{ $slot }}
    </button>
@endif
