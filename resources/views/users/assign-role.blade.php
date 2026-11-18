@extends('layouts.app')
@section('title', 'Assign Role')
@section('content')
<x-page-heading title="Assign Role" :description="$user->name" :breadcrumbs="[['label'=>'Users','route'=>'users.index'],['label'=>'Role']]"/>
@if($errors->any())<x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>@endif
<x-card class="mb-5"><x-section-heading title="Current Permissions"/>
@foreach($user->activeRoleAssignments as $assignment)<h3 class="font-semibold">{{ $assignment->role->name }}</h3><ul class="mt-2 text-sm">@foreach($assignment->role->permissions as $permission)<li>{{ $permission->name }}</li>@endforeach</ul>@endforeach
</x-card>
<x-card><form method="POST" action="{{ route('users.role.update', $user) }}" class="space-y-4">@csrf
<x-form.select name="role_key" label="Role" required><option value="">Select replacement role</option>@foreach($roles as $role)<option value="{{ $role->slug }}">{{ $role->name }}</option>@endforeach</x-form.select>
<x-form.select name="facility_scope" label="Scope" required><option value="">Select scope explicitly</option><option value="National">National</option>@foreach($facilities as $facility)<option>{{ $facility->name }}</option>@endforeach</x-form.select>
<p class="text-sm text-[#64748B]">The current assignments will end. Their history is preserved and active sessions will be revoked.</p>
<x-button.primary type="submit">Assign Role</x-button.primary>
</form></x-card>
@endsection