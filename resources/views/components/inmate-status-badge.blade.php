@props(['status'])

@php
$map = [
    'In Custody' => ['variant' => 'active',   'label' => 'In Custody'],
    'Remanded'   => ['variant' => 'pending',  'label' => 'Remanded'],
    'Released'   => ['variant' => 'released', 'label' => 'Released'],
    'Transferred'=> ['variant' => 'warning',  'label' => 'Transferred'],
    'Deceased'   => ['variant' => 'inactive', 'label' => 'Deceased'],
];
$b = $map[$status] ?? ['variant' => 'inactive', 'label' => $status];
@endphp
<x-badge :variant="$b['variant']" dot {{ $attributes }}>{{ $b['label'] }}</x-badge>
