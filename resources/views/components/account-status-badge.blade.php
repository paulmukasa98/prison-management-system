@props(['status'])

@php
$classes = match($status) {
    'Active' => 'badge-active',
    'Invited' => 'badge-pending',
    'Suspended' => 'badge-warning',
    'Deactivated' => 'badge-inactive',
    default => 'badge-inactive',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $status === 'Active' ? 'bg-green-500' : ($status === 'Invited' ? 'bg-yellow-500' : ($status === 'Suspended' ? 'bg-amber-500' : 'bg-slate-400')) }}" aria-hidden="true"></span>
    {{ $status }}
</span>
