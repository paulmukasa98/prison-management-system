@props(['status'])

@php
$styles = [
    'Draft'                        => 'bg-slate-100 text-slate-600',
    'Pending Approval'             => 'bg-amber-100 text-amber-700',
    'Approved'                     => 'bg-blue-100 text-blue-700',
    'Rejected'                     => 'bg-red-100 text-red-700',
    'Awaiting Clarification'       => 'bg-orange-100 text-orange-700',
    'Cancelled'                    => 'bg-slate-100 text-slate-500',
    'Ready for Dispatch'           => 'bg-indigo-100 text-indigo-700',
    'In Transit'                   => 'bg-purple-100 text-purple-700',
    'Arrival Confirmation Pending' => 'bg-amber-100 text-amber-700',
    'Completed'                    => 'bg-green-100 text-green-700',
];
$dots = [
    'Draft'                        => 'bg-slate-400',
    'Pending Approval'             => 'bg-amber-500',
    'Approved'                     => 'bg-blue-500',
    'Rejected'                     => 'bg-red-500',
    'Awaiting Clarification'       => 'bg-orange-500',
    'Cancelled'                    => 'bg-slate-400',
    'Ready for Dispatch'           => 'bg-indigo-500',
    'In Transit'                   => 'bg-purple-500',
    'Arrival Confirmation Pending' => 'bg-amber-500',
    'Completed'                    => 'bg-green-500',
];
$cls = $styles[$status] ?? 'bg-slate-100 text-slate-600';
$dot = $dots[$status]   ?? 'bg-slate-400';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium $cls"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} flex-shrink-0" aria-hidden="true"></span>
    {{ $status }}
</span>
