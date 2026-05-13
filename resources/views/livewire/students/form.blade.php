
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student ? 'Edit Student' : 'Add New Student' }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $student ? 'Update student bio-data and enrolment.' : 'Register a new student and set up their profile.' }}</p>
        </div>
        <a href="{{ route('students.index') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50">
            <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Students
        </a>
    </div>

    @if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 p-4">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- LEFT 2/3 --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Bio Data --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Bio Data</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    {{-- Admission Number --}}
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-bold text-gray-900">Admission Number *</label>
                            @if(!$student)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" wire:model.live="auto_admission" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-brand-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-600">Auto-generate</span>
                            </label>
                            @endif
                        </div>
                        <input wire:model.live="admission_number" type="text"
                               @if($auto_admission && !$student) readonly @endif
                               placeholder="{{ $auto_admission && !$student ? 'Will be generated on save' : 'e.g. ADM-2026-0001' }}"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 {{ $auto_admission && !$student ? 'bg-gray-50 text-gray-400' : '' }}"/>
                        @error('admission_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- First Name --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">First Name *</label>
                        <input wire:model.live="first_name" type="text" placeholder="e.g. Amina"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 hover:border-gray-400"/>
                        @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Last Name --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Last Name *</label>
                        <input wire:model.live="last_name" type="text" placeholder="e.g. Yusuf"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 hover:border-gray-400"/>
                        @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Gender *</label>
                        <select wire:model.live="gender"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Date of Birth --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Date of Birth</label>
                        <input wire:model.live="dob" type="date"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('dob')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Blood Group --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Blood Group</label>
                        <input wire:model.live="blood_group" type="text" placeholder="e.g. O+"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('blood_group')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            {{-- Enrolment --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Enrolment</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Class *</label>
                        <select wire:model.live="class_id"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20">
                            <option value="">Select class</option>
                            @foreach($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Section *</label>
                        <select wire:model="section_id" wire:key="sections-{{ $class_id }}"
                                {{ !$class_id ? 'disabled' : '' }}
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 {{ !$class_id ? 'opacity-50' : '' }}">
                            <option value="">{{ $class_id ? 'Select section' : 'Select class first' }}</option>
                            @foreach($this->sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('section_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            {{-- Guardian --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Guardian / Emergency Contact</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Guardian Name</label>
                        <input wire:model.live="guardian_name" type="text" placeholder="Full name"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('guardian_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Guardian Phone</label>
                        <input wire:model.live="guardian_phone" type="text" placeholder="+234 ..."
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('guardian_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-bold text-gray-900 mb-2">Guardian Address</label>
                        <textarea wire:model.live="guardian_address" rows="2" placeholder="Home address"
                                  class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 resize-none"></textarea>
                        @error('guardian_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            {{-- Parent Account --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Create Parent Account</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Create a new parent login and link to this student</p>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" wire:model.live="create_parent_account" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-brand-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-600">Enable</span>
                    </label>
                </div>

                @if($create_parent_account)
                <div class="border-t border-gray-100 pt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Parent Name *</label>
                        <input wire:model.live="parent_name" type="text" placeholder="Full name"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('parent_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Email *</label>
                        <input wire:model.live="parent_email" type="email" placeholder="parent@email.com"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('parent_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Phone</label>
                        <input wire:model.live="parent_phone" type="text" placeholder="+234 ..."
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('parent_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Password *</label>
                        <input wire:model.live="parent_password" type="password" placeholder="Min. 6 characters"
                               class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @error('parent_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                @endif
            </div>

            {{-- Link Existing Parents --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Link Existing Parents</h2>
                <p class="text-xs text-gray-500 mb-4">Select parent accounts already in the system to grant portal access.</p>
                <select wire:model="parent_ids" multiple
                        class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"
                        style="min-height:100px;">
                    @foreach($this->availableParents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->email }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Hold Ctrl / Cmd to select multiple.</p>
                @error('parent_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Custom Fields --}}
            @if($this->customFields->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Additional Information</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach($this->customFields as $field)
                    <div class="{{ $field->type === 'textarea' ? 'sm:col-span-2' : '' }}">
                        <label class="block text-sm font-bold text-gray-900 mb-2">
                            {{ $field->label }}@if($field->required)<span class="text-red-500 ml-0.5">*</span>@endif
                        </label>
                        @if($field->type === 'select')
                            <select wire:model.live="customFieldValues.{{ $field->name }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20">
                                <option value="">{{ $field->placeholder ?: 'Select' }}</option>
                                @foreach($field->options ?? [] as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif($field->type === 'textarea')
                            <textarea wire:model.live="customFieldValues.{{ $field->name }}" rows="3"
                                      placeholder="{{ $field->placeholder }}"
                                      class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 resize-none"></textarea>
                        @elseif($field->type === 'checkbox')
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input wire:model.live="customFieldValues.{{ $field->name }}" type="checkbox"
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"/>
                                <span class="text-sm text-gray-700">{{ $field->placeholder ?: 'Yes' }}</span>
                            </label>
                        @else
                            <input wire:model.live="customFieldValues.{{ $field->name }}"
                                   type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') }}"
                                   placeholder="{{ $field->placeholder }}"
                                   class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"/>
                        @endif
                        @error("customFieldValues.{$field->name}")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT 1/3 --}}
        <div class="space-y-6">

            {{-- Passport Photo --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Passport Photo</h2>

                @if($passport)
                    <div class="flex flex-col items-center gap-3 mb-4">
                        <img src="{{ $passport->temporaryUrl() }}" class="h-24 w-24 rounded-xl object-cover ring-2 ring-gray-200"/>
                        <span class="text-xs text-gray-500">Preview — not saved yet</span>
                    </div>
                @elseif($student?->passport_photo_url)
                    <div class="flex flex-col items-center gap-3 mb-4">
                        <img src="{{ $student->passport_photo_url }}" class="h-24 w-24 rounded-xl object-cover ring-2 ring-gray-200"/>
                        <span class="text-xs text-gray-500">Current photo</span>
                    </div>
                @endif

                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                    <svg class="mb-2 w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-700">Click to upload</p>
                    <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                    <input type="file" wire:model="passport" accept="image/*" class="hidden"/>
                </label>
                <div wire:loading wire:target="passport" class="mt-2 flex items-center gap-2 text-xs font-medium text-brand-600">
                    <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Uploading...
                </div>
                @error('passport')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Status & Save --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2">Status</h2>
                <select wire:model.live="status"
                        class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20">
                    <option value="Active">Active</option>
                    <option value="Graduated">Graduated</option>
                    <option value="Expelled">Expelled</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <button type="submit"
                        class="flex w-full justify-center items-center gap-2 rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-500">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span wire:loading.remove wire:target="save">{{ $student ? 'Update Student' : 'Save Student' }}</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>

                <a href="{{ route('students.index') }}"
                   class="flex w-full justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50">
                    Cancel
                </a>
            </div>

        </div>

    </form>

    {{-- Saving overlay --}}
    <div wire:loading.flex wire:target="save"
         class="fixed inset-0 z-[100] items-center justify-center bg-black/40 backdrop-blur-sm"
         style="display:none;">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center">
            <svg class="animate-spin h-10 w-10 text-brand-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Saving Student</h3>
            <p class="text-sm text-gray-500">Please wait...</p>
        </div>
    </div>

</div>

@script
<script>
$wire.on('student-saved', (event) => {
    const data = event?.[0] ?? event;
    if (data.isNew) {
        window.location.href = '{{ route("students.index") }}';
    } else {
        window.location.href = '{{ route("students.index") }}';
    }
});

$wire.on('validation-error', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>
@endscript
