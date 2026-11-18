@props([
    'variant' => 'active',  // active | inactive | pending | released | danger | success | warning
    'dot'     => false,
])

@php
$classes = match($variant) {
    'active'   => 'badge-active',
    'inactive' => 'badge-inactive',
    'pending'  => 'badge-pending',
    'released' => 'badge-released',
    'danger'   => 'badge-danger',
    'success'  => 'badge-success',
    'warning'  => 'badge-warning',
    default    => 'badge-inactive',
};
$dotColors = match($variant) {
    'active'   => 'bg-green-500',
    'inactive' => 'bg-slate-400',
    'pending'  => 'bg-yellow-500',
    'released' => 'bg-blue-500',
    'danger'   => 'bg-red-500',
    'success'  => 'bg-green-500',
    'warning'  => 'bg-amber-500',
    default    => 'bg-slate-400',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
