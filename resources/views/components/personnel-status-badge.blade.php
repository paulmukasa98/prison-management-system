@props(['status'])

@php
$styles = [
    'Active'      => 'badge-active',
    'On Leave'    => 'badge-warning',
    'Suspended'   => 'badge-danger',
    'Retired'     => 'badge-inactive',
    'Terminated'  => 'badge-inactive',
];
$dots = [
    'Active'      => 'bg-green-500',
    'On Leave'    => 'bg-amber-500',
    'Suspended'   => 'bg-red-500',
    'Retired'     => 'bg-slate-400',
    'Terminated'  => 'bg-slate-400',
];
$cls = $styles[$status] ?? 'badge-inactive';
$dot = $dots[$status]   ?? 'bg-slate-400';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium $cls"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} flex-shrink-0" aria-hidden="true"></span>
    {{ $status }}
</span>
