@props(['status'])

@php
$styles = [
    'Active'    => 'badge-active',
    'Completed' => 'badge-released',
    'Suspended' => 'badge-warning',
    'Cancelled' => 'badge-inactive',
];
$cls = $styles[$status] ?? 'badge-inactive';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $cls"]) }}>
    {{ $status }}
</span>
