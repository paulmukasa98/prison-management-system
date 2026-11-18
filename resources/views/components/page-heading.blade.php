@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if(count($breadcrumbs))
        <x-breadcrumb :items="$breadcrumbs" class="mb-2"/>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-[#0F172A]">{{ $title }}</h1>
            @if($description)
                <p class="text-sm text-[#64748B] mt-1">{{ $description }}</p>
            @endif
        </div>
        @if($slot->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
