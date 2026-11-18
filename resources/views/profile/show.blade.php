@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
<x-page-heading title="My Profile" description="Manage your account details and password." :breadcrumbs="[['label'=>'Dashboard','route'=>'dashboard'],['label'=>'My Profile']]"/>
@if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <x-card>
        <h2 class="text-lg font-semibold mb-4">Account Details</h2>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Full name" :required="true" :value="old('name', $user->name)" :error="$errors->first('name')" autocomplete="name"/>
            <x-form.input name="email" type="email" label="Email address" :required="true" :value="old('email', $user->email)" :error="$errors->first('email')" autocomplete="email"/>
            <x-form.input name="current_password" type="password" label="Current password" helper="Required when changing your email address." :error="$errors->first('current_password')" autocomplete="current-password"/>
            <x-button.primary type="submit">Save Changes</x-button.primary>
        </form>
        <dl class="mt-5">
            <x-detail-row label="Status">{{ $user->status }}</x-detail-row>
            <x-detail-row label="Last login">{{ $user->last_login_at?->format('Y-m-d H:i') ?? 'Never' }}</x-detail-row>
            <x-detail-row label="Personnel">{{ $user->personnel ? $user->personnel->staff_number.' · '.$user->personnel->first_name.' '.$user->personnel->last_name : 'No linked personnel record' }}</x-detail-row>
        </dl>
        <h3 class="font-semibold mt-5 mb-3">Roles and Facilities</h3>
        @forelse($user->activeRoleAssignments as $assignment)
            <p class="text-sm mt-2">{{ $assignment->role?->name ?? 'Unavailable role' }} · {{ $assignment->scope?->facility?->name ?? $assignment->scope?->kind }}</p>
        @empty
            <p class="text-sm text-[#64748B]">No roles assigned.</p>
        @endforelse
    </x-card>
    <x-card>
        <h2 class="text-lg font-semibold mb-4">Change Password</h2>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf
            <x-form.input name="current_password" id="password_current" type="password" label="Current password" :required="true" :error="$errors->passwordChange->first('current_password')" autocomplete="current-password"/>
            <x-form.input name="password" type="password" label="New password" :required="true" helper="Use at least 12 characters." :error="$errors->passwordChange->first('password')" autocomplete="new-password"/>
            <x-form.input name="password_confirmation" type="password" label="Confirm new password" :required="true" autocomplete="new-password"/>
            <p class="text-sm text-[#64748B]">Changing your password signs out your other sessions.</p>
            <x-button.primary type="submit">Update Password</x-button.primary>
        </form>
    </x-card>
</div>
@endsection
