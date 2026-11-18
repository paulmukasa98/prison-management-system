@props([
    'name',
    'label'       => null,
    'rows'        => 4,
    'placeholder' => '',
    'required'    => false,
    'disabled'    => false,
    'helper'      => null,
    'error'       => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-[#0F172A] mb-1.5">
            {{ $label }}
            @if($required)<span class="text-[#DC2626] ml-0.5" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except(['class'])->merge([
            'class' => 'w-full px-3 py-2 text-sm text-[#0F172A] bg-white border rounded-lg transition-colors resize-y
                        placeholder-[#94A3B8]
                        focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand
                        disabled:bg-[#F8FAFC] disabled:text-[#94A3B8] disabled:cursor-not-allowed '
                        . ($error ? 'border-[#DC2626]' : 'border-[#E2E8F0]')
        ]) }}
    >{{ $slot }}</textarea>

    @if($error)
        <p class="mt-1.5 text-xs text-[#DC2626]" role="alert">{{ $error }}</p>
    @elseif($helper)
        <p class="mt-1.5 text-xs text-[#64748B]">{{ $helper }}</p>
    @endif
</div>
