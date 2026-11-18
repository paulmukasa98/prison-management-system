@props([
    'type'        => 'info',   // info | success | warning | danger
    'title'       => null,
    'dismissible' => true,
    'duration'    => 0,        // 0 = no auto-dismiss
])

@php
$styles = match($type) {
    'success' => ['bg' => 'bg-green-50',  'border' => 'border-green-200', 'text' => 'text-green-800',  'icon_color' => 'text-green-500'],
    'warning' => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200', 'text' => 'text-amber-800',  'icon_color' => 'text-amber-500'],
    'danger'  => ['bg' => 'bg-red-50',    'border' => 'border-red-200',   'text' => 'text-red-800',    'icon_color' => 'text-red-500'],
    default   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',  'text' => 'text-blue-800',   'icon_color' => 'text-blue-500'],
};
$icons = [
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    'danger'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'info'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];
@endphp

<div
    x-data="alert({{ $duration }})"
    x-show="visible"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    role="alert"
    {{ $attributes->merge(['class' => "flex gap-3 p-4 rounded-lg border {$styles['bg']} {$styles['border']}"]) }}
>
    <svg class="w-5 h-5 flex-shrink-0 {{ $styles['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        {!! $icons[$type] ?? $icons['info'] !!}
    </svg>
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="text-sm font-semibold {{ $styles['text'] }}">{{ $title }}</p>
        @endif
        <div class="text-sm {{ $styles['text'] }} {{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
    </div>
    @if($dismissible)
        <button
            @click="dismiss()"
            class="flex-shrink-0 p-0.5 rounded {{ $styles['icon_color'] }} hover:opacity-70 transition-opacity"
            aria-label="Dismiss alert"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</div>
