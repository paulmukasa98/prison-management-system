@props(['size' => 56])

@php
    $logoSize = max(24, min(120, (int) $size));
    $logoSource = is_file(public_path('ups-logo.png'))
        ? asset('ups-logo.png')
        : 'https://images.seeklogo.com/logo-png/66/1/uganda-prisons-service-logo-png_seeklogo-663093.png';
@endphp

{{-- Online source: https://seeklogo.com/vector-logo/663093/uganda-prisons-service.
     A locally supplied public/ups-logo.png takes precedence over the online copy. --}}
<span
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-lg bg-white']) }}
    role="img"
    aria-label="Uganda Prisons Service"
    style="position: relative; width: {{ $logoSize }}px; height: {{ $logoSize }}px; flex-shrink: 0; overflow: hidden;"
>
    <span aria-hidden="true" style="color: #701B30; font-size: {{ max(10, (int) ($logoSize / 4)) }}px; font-weight: 700; letter-spacing: .04em;">UPS</span>
    <img
        src="{{ $logoSource }}"
        alt=""
        aria-hidden="true"
        width="{{ $logoSize }}"
        height="{{ $logoSize }}"
        decoding="async"
        referrerpolicy="no-referrer"
        onerror="this.style.display = 'none'"
        style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; padding: 4px; background: #fff;"
    >
</span>
