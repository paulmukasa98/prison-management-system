@props([
    'module'      => 'Module',
    'description' => 'This module will be fully implemented in a future phase.',
    'phase'       => '2',
    'breadcrumbs' => [],
])

<x-page-heading
    :title="$module"
    :description="$description"
    :breadcrumbs="$breadcrumbs"
/>

<x-card>
    <div class="flex flex-col items-center py-12 text-center">
        <div class="w-16 h-16 bg-[#DBEAFE] rounded-full flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <h2 class="text-base font-semibold text-[#0F172A] mb-2">{{ $module }} — Coming in Phase {{ $phase }}</h2>
        <p class="text-sm text-[#64748B] max-w-md mb-6">{{ $description }}</p>
        <x-badge variant="pending">
            Under Development — Phase {{ $phase }}
        </x-badge>

        @if($slot->isNotEmpty())
            <div class="mt-6">{{ $slot }}</div>
        @endif
    </div>
</x-card>
