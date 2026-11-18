@extends('layouts.app')
@section('title', 'Account Status')
@section('content')
<x-page-heading title="Account Status" :description="$user->name.' · '.$user->status" :breadcrumbs="[['label'=>'Users','route'=>'users.index'],['label'=>'Status']]"/>
@if($errors->any())
    <x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>
@endif
<x-card class="max-w-3xl">
    <form method="POST" action="{{ route('users.status.update', $user) }}" class="space-y-4">
        @csrf
        <x-form.select name="status" label="New Status" required>
            <option value="">Select new status</option>
            @foreach(['Active', 'Suspended', 'Deactivated', 'Disabled'] as $status)
                @if($status !== $user->status)
                    <option value="{{ $status }}">{{ $status }}</option>
                @endif
            @endforeach
        </x-form.select>
        <x-form.textarea name="reason" label="Reason" required>{{ old('reason') }}</x-form.textarea>
        <p class="text-sm text-[#64748B]">Existing sessions will be revoked. Account history is retained.</p>
        <x-button.primary type="submit">Change Status</x-button.primary>
    </form>
</x-card>
@endsection