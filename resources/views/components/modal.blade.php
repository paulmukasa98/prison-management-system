@props([
    'id'    => 'modal',
    'title' => '',
    'size'  => 'md',  // sm | md | lg | xl
])

@php
$sizeClass = match($size) {
    'sm'  => 'max-w-sm',
    'lg'  => 'max-w-2xl',
    'xl'  => 'max-w-4xl',
    default => 'max-w-lg',
};
@endphp

<div
    x-data="modal()"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @keydown.escape.window="close()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    id="{{ $id }}"
    style="display:none"
>
    <div
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="w-full {{ $sizeClass }} bg-white rounded-xl shadow-xl"
        @click.stop
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0]">
            <h2 id="{{ $id }}-title" class="text-base font-semibold text-[#0F172A]">{{ $title }}</h2>
            <button
                @click="close()"
                class="p-1.5 rounded-lg text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] transition-colors"
                aria-label="Close dialog"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        {{-- Body --}}
        <div class="px-6 py-5">
            {{ $slot }}
        </div>
        {{-- Footer slot --}}
        @isset($footer)
            <div class="px-6 py-4 border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
