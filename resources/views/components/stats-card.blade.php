@props([
    'title',
    'value',
    'subtitle'  => null,
    'icon'      => 'default',
    'color'     => 'blue',   // blue | green | yellow | red | purple | gray
    'trend'     => null,     // e.g. '+3.2%'
    'trendUp'   => true,
])

@php
$colorMap = [
    'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-[#2563EB]',  'ring' => 'ring-blue-100'],
    'green'  => ['bg' => 'bg-green-50',  'icon' => 'text-[#16A34A]',  'ring' => 'ring-green-100'],
    'yellow' => ['bg' => 'bg-amber-50',  'icon' => 'text-[#D97706]',  'ring' => 'ring-amber-100'],
    'red'    => ['bg' => 'bg-red-50',    'icon' => 'text-[#DC2626]',  'ring' => 'ring-red-100'],
    'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600', 'ring' => 'ring-purple-100'],
    'gray'   => ['bg' => 'bg-slate-50',  'icon' => 'text-slate-500',  'ring' => 'ring-slate-100'],
];
$c = $colorMap[$color] ?? $colorMap['blue'];
$defaultIcons = [
    'blue'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
    'green'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'yellow' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'red'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    'purple' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
    'gray'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5 card-hover transition-shadow']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-1">{{ $title }}</p>
            <p class="text-2xl font-bold text-[#0F172A] tabular-nums">{{ $value }}</p>
            @if($subtitle)
                <p class="text-xs text-[#64748B] mt-1">{{ $subtitle }}</p>
            @endif
            @if($trend)
                <p class="text-xs mt-1 {{ $trendUp ? 'text-[#16A34A]' : 'text-[#DC2626]' }}">
                    {{ $trendUp ? '↑' : '↓' }} {{ $trend }} vs last month
                </p>
            @endif
        </div>
        <div class="flex-shrink-0 w-10 h-10 {{ $c['bg'] }} {{ $c['ring'] }} ring-1 rounded-lg flex items-center justify-center">
            <div class="{{ $c['icon'] }}">
                @if(trim($slot) === '')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $defaultIcons[$color] ?? $defaultIcons['blue'] !!}</svg>
                @else
                    {{ $slot }}
                @endif
            </div>
        </div>
    </div>
</div>
