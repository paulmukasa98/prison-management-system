@props(['label', 'mono' => false, 'sensitive' => false])

<div class="py-3 border-b border-[#F1F5F9] last:border-0">
    <dt class="text-xs font-semibold text-[#64748B] uppercase tracking-wide mb-0.5">{{ $label }}</dt>
    <dd class="text-sm text-[#0F172A] {{ $mono ? 'font-mono' : '' }} {{ $sensitive ? 'select-none' : '' }}">
        {{ $slot->isEmpty() ? '—' : $slot }}
    </dd>
</div>
