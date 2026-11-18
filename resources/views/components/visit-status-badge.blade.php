@props(['status'])

@php
$styles = [
    'Approved'       => 'bg-cyan-100 text-cyan-700',
    'Requested'      => 'bg-slate-100 text-slate-600',
    'Pending Review' => 'bg-amber-100 text-amber-700',
    'Scheduled'      => 'bg-blue-100 text-blue-700',
    'Denied'         => 'bg-red-100 text-red-700',
    'Cancelled'      => 'bg-slate-100 text-slate-500',
    'Checked In'     => 'bg-purple-100 text-purple-700',
    'Completed'      => 'bg-green-100 text-green-700',
    'No-Show'        => 'bg-orange-100 text-orange-700',
];
$dots = [
    'Approved'       => 'bg-cyan-500',
    'Requested'      => 'bg-slate-400',
    'Pending Review' => 'bg-amber-500',
    'Scheduled'      => 'bg-blue-500',
    'Denied'         => 'bg-red-500',
    'Cancelled'      => 'bg-slate-400',
    'Checked In'     => 'bg-purple-500',
    'Completed'      => 'bg-green-500',
    'No-Show'        => 'bg-orange-500',
];
$cls = $styles[$status] ?? 'bg-slate-100 text-slate-600';
$dot = $dots[$status]   ?? 'bg-slate-400';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium $cls"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} flex-shrink-0" aria-hidden="true"></span>
    {{ $status }}
</span>
