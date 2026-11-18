@props(['title', 'description' => null])

<div class="mb-4 pb-3 border-b border-[#E2E8F0]">
    <h2 class="text-sm font-semibold text-[#0F172A]">{{ $title }}</h2>
    @if($description)
        <p class="text-xs text-[#64748B] mt-0.5">{{ $description }}</p>
    @endif
</div>
