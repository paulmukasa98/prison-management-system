<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $reportData['title'] }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])<link rel="stylesheet" href="{{ asset('ups-theme.css') }}?v={{ filemtime(public_path('ups-theme.css')) }}"></head>
<body class="bg-white text-[#0F172A] p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-5">
            <x-ups-logo :size="64" />
            <div>
                <p class="font-bold">Uganda Prisons Service</p>
                <p class="text-sm">Prison Management System</p>
            </div>
        </div>
        <h1 class="text-2xl font-bold">{{ $reportData['title'] }}</h1>
        <p class="mt-2 text-sm">{{ config('reporting.classification') }}</p>
        <p class="mt-2 text-xs">Period: {{ $from }} → {{ $to }} · {{ $timezone }} · Generated {{ now()->format('Y-m-d H:i') }} · Audit: {{ $auditReference }}</p>
        <div class="flex flex-wrap gap-5 my-5">@foreach($reportData['summary'] as $label => $value)<p>{{ $label }}: <strong>{{ number_format($value) }}</strong></p>@endforeach</div>
        <div class="overflow-x-auto"><table class="w-full text-sm text-left border-collapse">
            <thead><tr>@foreach($reportData['columns'] as $column)<th class="border border-[#E2E8F0] p-3">{{ $column }}</th>@endforeach</tr></thead>
            <tbody>@forelse($reportData['rows'] as $row)<tr>@foreach($row as $value)<td class="border border-[#E2E8F0] p-3">{{ $value }}</td>@endforeach</tr>@empty<tr><td class="p-4" colspan="{{ count($reportData['columns']) }}">No records in this scope.</td></tr>@endforelse</tbody>
        </table></div>
        <p class="mt-5 text-sm">{{ $reportData['note'] }}</p>
        <button type="button" onclick="window.print()" class="no-print mt-5 px-4 py-2 bg-brand text-white rounded-lg">Print Report</button>
    </div>
</body></html>
