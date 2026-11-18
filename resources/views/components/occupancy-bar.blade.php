@props([
    'population',
    'capacity',
    'pct'       => null,  // pre-computed or computed here
    'showLabel' => true,
])

@php
$safePct = $capacity > 0 ? round(($population / $capacity) * 100, 1) : 0;
if ($pct !== null) { $safePct = $pct; }

$barColor = match(true) {
    $safePct >= 100 => 'bg-red-600',
    $safePct >= 90  => 'bg-red-500',
    $safePct >= 75  => 'bg-amber-500',
    default         => 'bg-[#2563EB]',
};
$textLabel = match(true) {
    $safePct >= 100 => 'text-red-600',
    $safePct >= 90  => 'text-red-500',
    $safePct >= 75  => 'text-amber-600',
    default         => 'text-[#64748B]',
};
$ariaLabel = $safePct >= 100
    ? 'Over capacity'
    : ($safePct >= 90 ? 'Near capacity' : 'Within capacity');
@endphp

<div {{ $attributes }}>
    @if($capacity === null)
        <p class="text-xs text-[#64748B]">{{ number_format($population) }} in custody · Capacity unknown</p>
    @elseif($capacity === 0)
        <p class="text-xs text-[#64748B]">{{ number_format($population) }} in custody · Recorded capacity 0 · Occupancy undefined</p>
    @else
    @if($showLabel)
    <div class="flex justify-between text-xs mb-1">
        <span class="text-[#64748B]">{{ number_format($population) }} / {{ number_format($capacity) }}</span>
        <span class="{{ $textLabel }} font-medium">
            {{ $safePct }}%
            @if($safePct >= 100) <span class="ml-1" aria-label="Over capacity">⚠</span> @endif
        </span>
    </div>
    @endif
    <div class="w-full bg-[#F1F5F9] rounded-full h-2" role="progressbar"
         aria-valuenow="{{ $safePct }}" aria-valuemin="0" aria-valuemax="100"
         aria-label="{{ $ariaLabel }} — {{ $safePct }}%">
        <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ min($safePct, 100) }}%"></div>
    </div>
    @endif
</div>
