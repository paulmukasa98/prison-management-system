@props([
    'items' => [],  // array of ['label' => '', 'route' => ''] — last item is current page (no route needed)
])

<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1 text-sm">
        @foreach($items as $index => $item)
            @if(!$loop->last)
                <li>
                    @php($breadcrumbRoute = ((array) ($item['route'] ?? []))[0] ?? null)
                    @if($breadcrumbRoute === null || app(\App\Support\InterfacePermissions::class)->destination($breadcrumbRoute))
                    <a
                        href="{{ isset($item['route']) ? route(...(array) $item['route']) : ($item['url'] ?? '#') }}"
                        class="text-[#64748B] hover:text-[#2563EB] transition-colors"
                    >{{ $item['label'] }}</a>
                    @else
                    <span class="text-[#64748B]">{{ $item['label'] }}</span>
                    @endif
                </li>
                <li aria-hidden="true">
                    <svg class="w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </li>
            @else
                <li aria-current="page" class="text-[#0F172A] font-medium">{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
