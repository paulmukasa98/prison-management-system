<div class="table-responsive"><table class="w-full text-sm text-left" aria-label="Visit records">
    <thead class="bg-[#F8FAFC]"><tr>@foreach(['Reference','Visitor','Inmate','Facility','Visit Window','Status'] as $heading)<th class="px-4 py-3">{{ $heading }}</th>@endforeach</tr></thead>
    <tbody class="divide-y divide-[#E2E8F0]">@forelse($visits as $visit)
        <tr>
            <td class="px-4 py-3 font-mono text-xs"><a class="text-[#2563EB] hover:underline" href="{{ route('visitors.visits.show', $visit['id']) }}">{{ $visit['visit_ref'] }}</a></td>
            <td class="px-4 py-3"><a class="hover:underline" href="{{ route('visitors.show', $visit['visitor_id']) }}">{{ $visit['visitor_name'] }}</a></td>
            <td class="px-4 py-3">{{ $visit['inmate_name'] }}<span class="block text-xs text-[#64748B]">{{ $visit['prisoner_number'] }}</span></td>
            <td class="px-4 py-3">{{ $visit['facility'] }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $visit['visit_date'] ?? 'Unscheduled' }}<span class="block text-xs text-[#64748B]">{{ $visit['start_time'] }} · {{ $visit['duration_mins'] ?? '—' }} min</span></td>
            <td class="px-4 py-3"><x-visit-status-badge :status="$visit['status']"/></td>
        </tr>
    @empty<tr><td colspan="6" class="p-5"><x-empty-state title="No visits recorded" description="No visits match your filters."/></td></tr>@endforelse</tbody>
</table></div>
@if($visits instanceof \Illuminate\Contracts\Pagination\Paginator)<div class="p-4">{{ $visits->links() }}</div>@endif