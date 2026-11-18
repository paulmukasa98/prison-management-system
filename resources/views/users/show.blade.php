@extends('layouts.app')
@section('title', 'Account Details')
@section('content')
<x-page-heading :title="$user->name" :description="$user->account_ref ?? 'Account '.$user->id" :breadcrumbs="[['label'=>'Users','route'=>'users.index'],['label'=>'Account']]">@if(auth()->id() !== $user->id && app(\App\Support\InterfacePermissions::class)->national('users.manage'))
<x-button.secondary href="{{ route('users.status', $user) }}">Change Status</x-button.secondary>
@endif
@if(auth()->id() !== $user->id && app(\App\Support\InterfacePermissions::class)->national('users.manage') && app(\App\Support\InterfacePermissions::class)->national('roles.assign'))
<x-button.secondary href="{{ route('users.assign-role', $user) }}">Assign Role</x-button.secondary>
@endif</x-page-heading>
@if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
<x-card><dl><x-detail-row label="Email">{{ $user->email }}</x-detail-row><x-detail-row label="Status">{{ $user->status }}</x-detail-row><x-detail-row label="Last Login">{{ $user->last_login_at?->format('Y-m-d H:i') ?? 'Never' }}</x-detail-row></dl>
<h2 class="font-semibold mt-5 mb-3">Linked Personnel Record</h2>
@if($user->personnel)<p>{{ $user->personnel->staff_number }} · {{ $user->personnel->first_name }} {{ $user->personnel->last_name }}</p>@else<p class="text-sm text-[#64748B]">No linked personnel record.</p>@endif
<h2 class="font-semibold mt-5 mb-3">Active Assignments</h2>
@forelse($user->activeRoleAssignments as $assignment)<p class="text-sm mt-2">{{ $assignment->role->name }} · {{ $assignment->scope->facility?->name ?? $assignment->scope->kind }}</p>@empty<p>No roles assigned.</p>@endforelse
</x-card>
@endsection