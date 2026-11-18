@extends('layouts.app')
@section('title', 'Roles and Permissions')
@section('content')
<x-page-heading title="Roles and Permissions" description="Enforced permission catalog. Role grants are administered through controlled account assignments." :breadcrumbs="[['label'=>'Users','route'=>'users.index'],['label'=>'Roles']]"/>
<x-card :padding="false"><div class="table-responsive"><table class="w-full text-sm text-left"><thead class="bg-[#F8FAFC]"><tr><th class="px-4 py-3">Permission</th>@foreach($roles as $role)<th class="px-4 py-3">{{ $role->name }}</th>@endforeach</tr></thead><tbody class="divide-y divide-[#E2E8F0]">
@foreach($permissions as $permission)<tr><th class="px-4 py-3 font-normal">{{ $permission->module }} · {{ $permission->name }}<span class="block text-xs text-[#64748B]">{{ $permission->slug }}</span></th>@foreach($roles as $role)<td class="px-4 py-3">{{ $role->slug === 'system_admin' || $role->permissions->contains('id', $permission->id) ? 'Granted' : '—' }}</td>@endforeach</tr>@endforeach
</tbody></table></div></x-card>
@endsection