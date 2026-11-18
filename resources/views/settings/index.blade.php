@extends('layouts.app')
@section('title', 'System Settings')
@section('content')
<x-page-heading title="System Settings" description="Read-only deployment configuration. Changes are controlled through the environment configuration." :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Settings']]"/>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
<x-card><x-section-heading title="Operational Configuration"/><dl>
<x-detail-row label="Application">{{ config('app.name') }}</x-detail-row>
<x-detail-row label="Reporting Timezone">{{ config('reporting.timezone') }}</x-detail-row>
<x-detail-row label="Visitation Rules">{{ config('visitation.rules_confirmed') ? 'Confirmed by configuration' : 'Awaiting institutional validation' }}</x-detail-row>
<x-detail-row label="CSV Exports">{{ config('reporting.csv_exports_enabled') ? 'Enabled for authorized accounts' : 'Disabled' }}</x-detail-row>
<x-detail-row label="Offline Writes">Disabled — online authorization required</x-detail-row>
<x-detail-row label="Audit Retention">Preserved; no automatic purge configured</x-detail-row>
</dl></x-card>
<x-card><x-section-heading title="Permission Catalog"/>
<ul class="space-y-2 text-sm">@foreach($permissions as $permission)<li><span class="font-mono text-xs text-[#64748B]">{{ $permission->slug }}</span> · {{ $permission->name }}</li>@endforeach</ul>
</x-card>
</div>
@endsection