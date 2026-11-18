@extends('layouts.app')
@section('title', 'System Users')
@section('content')
<x-page-heading title="System Users" description="Login accounts are separate from personnel records." :breadcrumbs="[['label'=>'Users']]">@if(app(\App\Support\InterfacePermissions::class)->national('users.manage') && app(\App\Support\InterfacePermissions::class)->national('roles.assign'))
<x-button.primary href="{{ route('users.create') }}">Provision Account</x-button.primary>
@endif
@nationalPermission('users.manage')
<x-button.secondary href="{{ route('users.roles.index') }}">Roles and Permissions</x-button.secondary>
@endnationalPermission</x-page-heading>
<x-card class="mb-5"><form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end"><x-form.input name="search" label="Name or Email" :value="request('search')" class="grow"/><x-button.primary type="submit">Search</x-button.primary></form></x-card>
<x-card :padding="false"><div class="table-responsive"><table class="w-full text-sm text-left"><thead class="bg-[#F8FAFC]"><tr>@foreach(['Name','Email','Roles','Status'] as $heading)<th class="px-4 py-3">{{ $heading }}</th>@endforeach</tr></thead><tbody class="divide-y divide-[#E2E8F0]">
@forelse($users as $user)<tr><td class="px-4 py-3"><a href="{{ route('users.show', $user) }}" class="text-[#2563EB] hover:underline">{{ $user->name }}</a></td><td class="px-4 py-3">{{ $user->email }}</td><td class="px-4 py-3">{{ $user->activeRoleAssignments->pluck('role.name')->implode(', ') }}</td><td class="px-4 py-3">{{ $user->status }}</td></tr>@empty<tr><td class="p-4" colspan="4">No accounts found.</td></tr>@endforelse
</tbody></table></div><div class="p-4">{{ $users->links() }}</div></x-card>
@endsection