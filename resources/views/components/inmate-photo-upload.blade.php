@props(['photo' => null, 'required' => false])

<div {{ $attributes->merge(['class' => 'mt-5 pt-4 border-t border-[#E2E8F0]']) }} x-data="{
    currentPhoto: {{ Js::from($photo) }},
    preview: null,
    filename: '',
    error: {{ Js::from($errors->first('photo')) }},
    init() { this.$nextTick(() => this.$refs.photoInput.setCustomValidity(this.error)); },
    choose(event) {
        const file = event.target.files[0];
        if (this.preview) { URL.revokeObjectURL(this.preview); }
        this.preview = null;
        this.filename = '';
        this.error = '';
        event.target.setCustomValidity('');
        if (!file) { return; }
        if (file.size > 2 * 1024 * 1024 || (file.type && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))) {
            this.error = 'Choose a JPEG, PNG or WebP photograph no larger than 2 MB.';
            event.target.value = '';
            event.target.setCustomValidity(this.error);
        } else {
            this.preview = URL.createObjectURL(file);
            this.filename = file.name;
        }
        this.$dispatch('photo-changed');
    },
    remove() {
        if (this.preview) { URL.revokeObjectURL(this.preview); }
        this.preview = null;
        this.filename = '';
        this.error = '';
        this.$refs.photoInput.value = '';
        this.$refs.photoInput.setCustomValidity('');
        this.$dispatch('photo-changed');
    },
    destroy() { if (this.preview) { URL.revokeObjectURL(this.preview); } }
}">
    <p class="text-sm font-medium text-[#0F172A] mb-2">Photograph @if($required)<span class="text-[#DC2626]" aria-hidden="true">*</span>@endif</p>
    <div class="flex items-start gap-4">
        <div class="w-24 h-28 bg-[#F1F5F9] rounded-lg border border-[#E2E8F0] flex items-center justify-center overflow-hidden" style="flex-shrink: 0;">
            <template x-if="preview || currentPhoto"><img :src="preview || currentPhoto" alt="Inmate photograph" class="w-full h-full object-cover"></template>
            <span x-show="!preview && !currentPhoto" class="text-xs text-[#94A3B8]">No photo</span>
        </div>
        <div>
            <label for="photo_upload" class="inline-flex items-center px-3 py-2 text-sm font-medium border border-[#E2E8F0] rounded-lg cursor-pointer hover:bg-brand-soft">Choose photograph</label>
            <input id="photo_upload" name="photo" type="file" @required($required) accept="image/jpeg,image/png,image/webp" class="sr-only" x-ref="photoInput" @change="choose($event)" @invalid.prevent="error = $event.target.validationMessage" aria-label="Upload inmate photograph" aria-describedby="inmate-photo-help inmate-photo-error" :aria-invalid="error ? 'true' : 'false'">
            <p id="inmate-photo-help" class="text-xs text-[#64748B] mt-2">JPEG, PNG or WebP, up to 2 MB and 3000 × 3000 pixels. Uploaded when you save and visible only to authorized users.</p>
            <p x-show="filename" x-text="filename" class="text-xs text-[#64748B] mt-1"></p>
            <p id="inmate-photo-error" x-show="error" x-text="error" role="alert" class="text-xs text-[#DC2626] mt-1"></p>
            <button type="button" x-show="preview || error" @click="remove()" class="text-xs text-[#DC2626] hover:underline mt-1">Remove selection</button>
        </div>
    </div>
</div>
