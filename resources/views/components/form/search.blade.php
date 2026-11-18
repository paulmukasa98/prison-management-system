@props([
    'name'        => 'search',
    'placeholder' => 'Search...',
    'value'       => '',
])

<div class="relative {{ $attributes->get('class', '') }}">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3" aria-hidden="true">
        <svg class="h-4 w-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
    <input
        type="search"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->except('class') }}
        class="w-full pl-9 pr-4 py-2 text-sm text-[#0F172A] bg-white border border-[#E2E8F0] rounded-lg
               placeholder-[#94A3B8]
               focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
    >
</div>
