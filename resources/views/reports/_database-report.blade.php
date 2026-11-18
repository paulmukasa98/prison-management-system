<x-page-heading :title="$reportData['title']" description="Review recorded operational activity." :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'Reports','route'=>'reports.index'],['label'=>$reportData['title']]]">
    @if(auth()->user()->hasPermission('reports.export', $facilityId))
    <x-button.secondary :href="route('reports.print', ['report' => $reportData['key'], 'from' => $from, 'to' => $to, 'facility_id' => $facilityId])" target="_blank" rel="noopener">Print Report</x-button.secondary>
    @if(config('reporting.csv_exports_enabled'))<x-button.primary :href="route('reports.export', ['report' => $reportData['key'], 'from' => $from, 'to' => $to, 'facility_id' => $facilityId])">Download CSV</x-button.primary>@endif
    @endif
</x-page-heading>
@if($errors->any())<x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>@endif
<x-report-filters :from="$from" :to="$to" :facilities="$facilities" :facility-id="$facilityId" :base="route('reports.'.$reportData['key'])"/>
<div class="flex flex-wrap gap-4 my-5">@foreach($reportData['summary'] as $label => $value)<x-card><p class="text-xs text-[#64748B]">{{ $label }}</p><p class="text-2xl font-bold">{{ number_format($value) }}</p></x-card>@endforeach</div>
<x-card :padding="false">
    <div class="table-responsive"><table class="w-full text-sm text-left">
        <caption class="sr-only">{{ $reportData['title'] }} — {{ $from }} to {{ $to }}</caption>
        <thead class="bg-[#F8FAFC]"><tr>@foreach($reportData['columns'] as $column)<th class="px-4 py-3">{{ $column }}</th>@endforeach</tr></thead>
        <tbody class="divide-y divide-[#E2E8F0]">@forelse($reportData['rows'] as $row)<tr>@foreach($row as $value)<td class="px-4 py-3">{{ $value }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($reportData['columns']) }}" class="p-5"><x-empty-state title="No records in this scope" description="Try another reporting period or authorized facility."/></td></tr>@endforelse</tbody>
    </table></div>
</x-card>
<p class="mt-4 text-sm text-[#64748B]">{{ $reportData['note'] }}</p>
@if(isset($reportData['ageBands']))
<x-card class="mt-5"><h2 class="font-semibold mb-3">Age band counts</h2><dl class="flex flex-wrap gap-5">@foreach($reportData['ageBands'] as $band => $count)<div><dt class="text-sm text-[#64748B]">{{ $band }}</dt><dd class="font-semibold">{{ $count }}</dd></div>@endforeach</dl></x-card>
@endif
<p class="mt-2 text-xs text-[#64748B]">Reporting timezone: {{ $timezone }}. {{ config('reporting.classification') }}.</p>
