<x-page-heading :title="ucwords(str_replace('-', ' ', $action)).' Visit'" :description="$visit['visit_ref']" :breadcrumbs="[['label'=>'Visit Records','route'=>'visitors.records'],['label'=>'Confirm Action']]"/>
@if($errors->any())<x-alert type="error" class="mb-5"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert>@endif
<x-card class="max-w-3xl">
    <dl class="mb-5">
        <x-detail-row label="Visitor">{{ $visit['visitor_name'] }}</x-detail-row>
        <x-detail-row label="Inmate">{{ $visit['inmate_name'] }} · {{ $visit['prisoner_number'] }}</x-detail-row>
        <x-detail-row label="Facility">{{ $visit['facility'] }}</x-detail-row>
        <x-detail-row label="State"><x-visit-status-badge :status="$visit['status']"/></x-detail-row>
        <x-detail-row label="Window">{{ $visit['visit_date'] ?? 'Unscheduled' }} {{ $visit['start_time'] }} · {{ $visit['duration_mins'] ?? '—' }} min ({{ config('visitation.timezone') }})</x-detail-row>
    </dl>
    <form method="POST" action="{{ route('visitors.visits.'.$action.'.store', $visit['id']) }}" class="space-y-4">
        @csrf
        @if($action === 'review')
            <x-form.select name="decision" label="Decision" required><option value="">Choose decision</option><option value="Approve">Approve</option><option value="Deny">Deny</option></x-form.select>
        @endif
        <x-form.textarea name="reason" label="Reason / Reception Notes" required>{{ old('reason') }}</x-form.textarea>
        @if($action === 'check-in')
            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="identity_verified" value="1" class="mt-1" @required(config('visitation.require_identity_verification'))><span>I verified this visitor's identity at reception.</span></label>
        @endif
        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="confirmed" value="1" required class="mt-1"><span>I confirm this action and understand it will be recorded in the visit history.</span></label>
        <div class="flex flex-wrap gap-3"><x-button.primary type="submit">Confirm {{ ucwords(str_replace('-', ' ', $action)) }}</x-button.primary><x-button.secondary href="{{ route('visitors.visits.show', $visit['id']) }}">Back to Visit</x-button.secondary></div>
    </form>
</x-card>