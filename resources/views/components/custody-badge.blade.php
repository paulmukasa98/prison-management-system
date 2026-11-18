@props(['classification'])

@php
$map = [
    'Convicted'    => 'bg-red-50 text-red-700',
    'Remand'       => 'bg-amber-50 text-amber-700',
    'Civil Debtor' => 'bg-purple-50 text-purple-700',
];
$cls = $map[$classification] ?? 'bg-slate-50 text-slate-700';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $cls"]) }}>
    {{ $classification }}
</span>
