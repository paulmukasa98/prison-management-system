@if($errors->any())
<x-alert type="error" class="mb-5"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-alert>
@endif
@if(session('status'))<x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>@endif
<form action="{{ isset($visitor) ? route('visitors.update', $visitor['id']) : route('visitors.store') }}" method="POST" class="space-y-5">
    @csrf
    <x-card>
        <x-section-heading title="Identity Details" description="Registration does not approve any visit. Identity numbers are encrypted and displayed masked."/>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-form.input name="first_name" label="First Name" required :value="old('first_name', $visitor['first_name'] ?? '')" :error="$errors->first('first_name')"/>
            <x-form.input name="middle_name" label="Middle Name" :value="old('middle_name', $visitor['middle_name'] ?? '')"/>
            <x-form.input name="last_name" label="Last Name" required :value="old('last_name', $visitor['last_name'] ?? '')" :error="$errors->first('last_name')"/>
            <x-form.select name="gender" label="Gender"><option value="">Unspecified</option>@foreach(['Male','Female','Other'] as $gender)<option @selected(old('gender', $visitor['gender'] ?? '') === $gender)>{{ $gender }}</option>@endforeach</x-form.select>
            <x-form.select name="id_type" label="Identity Document Type"><option value="">Not presented</option>@foreach(config('visitation.id_types') as $type)<option @selected(old('id_type', $visitor['id_type'] ?? '') === $type)>{{ $type }}</option>@endforeach</x-form.select>
            <x-form.input name="id_number" label="Document Number" maxlength="128" autocomplete="off" :helper="isset($visitor) ? 'Current: '.$visitor['id_number_masked'].'. Leave blank to retain it.' : 'Enter only when a document is presented. Verify it separately at reception.'" :error="$errors->first('id_number')"/>
        </div>
    </x-card>
    <x-card>
        <x-section-heading title="Contact Details"/>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-form.input name="phone" label="Phone" type="tel" :value="old('phone', $visitor['phone'] ?? '')"/>
            <x-form.input name="email" label="Email" type="email" :value="old('email', $visitor['email'] ?? '')" :error="$errors->first('email')"/>
            <x-form.input name="residence" label="Residence / District" :value="old('residence', $visitor['residence'] ?? '')"/>
            <x-form.textarea name="notes" label="Notes">{{ old('notes', $visitor['notes'] ?? '') }}</x-form.textarea>
        </div>
    </x-card>
    <x-card>
        @if(isset($visitor))
            <x-section-heading title="Review Changes" description="Corrections are audited. The registration facility and visit history remain unchanged."/>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-form.select name="status" label="Identity Record Status" required>@foreach(['Under Review','Registered','Blocked'] as $status)<option @selected(old('status', $visitor['status']) === $status)>{{ $status }}</option>@endforeach</x-form.select>
                <x-form.textarea name="correction_reason" label="Reason for Correction" required>{{ old('correction_reason') }}</x-form.textarea>
            </div>
        @else
            <x-section-heading title="Registration Facility"/>
            <x-form.select name="facility_id" label="Facility" required><option value="">Select an authorized facility</option>@foreach($facilities as $facility)<option value="{{ $facility->id }}" @selected((string) old('facility_id') === (string) $facility->id)>{{ $facility->name }}</option>@endforeach</x-form.select>
        @endif
        <label class="flex items-start gap-2 mt-5 text-sm"><input name="confirmed" type="checkbox" value="1" required class="mt-1"><span>I have reviewed these details. Each visit requires its own authorization.</span></label>
        <div class="flex flex-wrap gap-3 mt-5">
            <x-button.primary type="submit">{{ isset($visitor) ? 'Save Corrections' : 'Register Visitor' }}</x-button.primary>
            @permission('visitors.read')
<x-button.secondary href="{{ route('visitors.index') }}">Back to Directory</x-button.secondary>
@endpermission
        </div>
    </x-card>
</form>