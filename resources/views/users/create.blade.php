@extends('layouts.app')
@section('title', 'Provision Account')
@section('content')
<x-page-heading title="Provision Account" description="Explicit national user-management and role-assignment authority is required." :breadcrumbs="[['label'=>'Users','route'=>'users.index'],['label'=>'Provision']]"/>
@if($errors->any())<x-alert type="error" class="mb-5">{{ $errors->first() }}</x-alert>@endif
<x-card><form method="POST" action="{{ route('users.store') }}" class="space-y-5">@csrf
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<x-form.input name="name" label="Full Name" required :value="old('name')"/>
<x-form.input name="email" label="Email" type="email" required :value="old('email')"/>
<x-form.select name="personnel_id" label="Personnel Link (optional)"><option value="">No personnel link</option>@foreach($candidates as $person)<option value="{{ $person->id }}">{{ $person->staff_number }} · {{ $person->first_name }} {{ $person->last_name }}</option>@endforeach</x-form.select>
<x-form.select name="role_key" label="Role" required><option value="">Select role</option>@foreach($roles as $role)<option value="{{ $role->slug }}">{{ $role->name }}</option>@endforeach</x-form.select>
<x-form.select name="facility_scope" label="Scope" required><option value="">Select scope explicitly</option><option value="National">National</option>@foreach($facilities as $facility)<option>{{ $facility->name }}</option>@endforeach</x-form.select>
<x-form.input name="initial_password" label="Initial Password" type="password" autocomplete="new-password" required minlength="12" helper="At least 12 characters. Communicate through an approved secure channel."/>
</div><x-button.primary type="submit">Provision Account</x-button.primary>
</form></x-card>
@endsection