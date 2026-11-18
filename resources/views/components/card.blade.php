@props([
    'padding' => true,
    'hover'   => false,
])

<div {{ $attributes->merge([
    'class' => 'bg-white rounded-xl border border-[#E2E8F0] shadow-sm '
               . ($padding ? 'p-5 sm:p-6 ' : '')
               . ($hover   ? 'card-hover transition-shadow ' : '')
]) }}>
    {{ $slot }}
</div>
