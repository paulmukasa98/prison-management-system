@extends('layouts.app')
@section('title', 'Correct Visitor Details')
@section('content')
<x-page-heading title="Correct Visitor Details" :description="$visitor['visitor_ref']" :breadcrumbs="[['label'=>'Visitors','route'=>'visitors.index'],['label'=>'Review Changes']]"/>
@include('visitors._identity-form', ['visitor' => $visitor])
<x-card class="mt-5">
    <x-section-heading title="Redact Identifiers" description="Remove document number, contact information and notes while preserving the visitor reference and visit history. Re-entry and verification will be required for later visits."/>
    <form method="POST" action="{{ route('visitors.redact', $visitor['id']) }}" class="flex flex-wrap items-center gap-4">
        @csrf
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="confirmed" value="1" required>Confirm identifier redaction</label>
        <x-button.danger type="submit">Redact Identifiers</x-button.danger>
    </form>
</x-card>
@endsection