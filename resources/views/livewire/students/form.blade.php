<div class="space-y-6 font-sans">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $student ? 'Edit Student Profile' : 'Add New Student' }}</h1>
            <p class="mt-1 text-sm text-gray-500">Admissions, parent linkage, and student bio-data profile</p>
        </div>
        <a href="{{ route('students.index') }}" 
           class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50">
            <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Students
        </a>
    </div>

    {{-- Main Form --}}
    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left Column (Takes 2/3 space) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Bio Information --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Bio Information</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-900">Admission Number *</label>
                            @if(!$student)
                                <label class="flex items-center gap-2 text-xs font-semibold text-gray-600 cursor-pointer">
                                    <input type="checkbox" wire:model.live="auto_admission" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    Auto-generate
                                </label>
                            @endif
                        </div>
                        <input
                            wire:model="admission_number"
                            type="text"
                            @if($auto_admission && !$student) readonly @endif
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400 {{ $auto_admission && !$student ? 'bg-gray-50 border-gray-200 text-gray-500 cursor-not-allowed' : '' }}"
                        />
                        @error('admission_number')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">First Name *</label>
                        <input
                            wire:model="first_name"
                            type="text"
                            placeholder="e.g., John"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('first_name')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Last Name *</label>
                        <input
                            wire:model="last_name"
                            type="text"
                            placeholder="e.g., Doe"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('last_name')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Gender *</label>
                        <select
                            wire:model.live="gender"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        >
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Date of Birth</label>
                        <input
                            wire:model="dob"
                            type="date"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('dob')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Class *</label>
                        <select
                            wire:model.live="class_id"
                            wire:change="$refresh"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        >
                            <option value="">Select class</option>
                            @foreach ($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Section *</label>
                        <select
                            wire:model="section_id"
                            wire:key="sections-{{ $class_id }}"
                            {{ !$class_id ? 'disabled' : '' }}
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400 {{ !$class_id ? 'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed' : '' }}"
                        >
                            <option value="">{{ $class_id ? 'Select section' : 'Select class first' }}</option>
                            @foreach ($this->sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Blood Group</label>
                        <input
                            wire:model="blood_group"
                            type="text"
                            placeholder="e.g., O+"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('blood_group')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Custom Fields --}}
                    @foreach($this->customFields as $field)
                        <div class="{{ $field->type === 'textarea' ? 'sm:col-span-2' : '' }}">
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                {{ $field->label }}
                                @if($field->required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            
                            @if($field->type === 'text')
                                <input
                                    wire:model="customFieldValues.{{ $field->name }}"
                                    type="text"
                                    placeholder="{{ $field->placeholder }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                            @elseif($field->type === 'number')
                                <input
                                    wire:model="customFieldValues.{{ $field->name }}"
                                    type="number"
                                    placeholder="{{ $field->placeholder }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                            @elseif($field->type === 'date')
                                <input
                                    wire:model="customFieldValues.{{ $field->name }}"
                                    type="date"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                            @elseif($field->type === 'select')
                                <select
                                    wire:model="customFieldValues.{{ $field->name }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                >
                                    <option value="">{{ $field->placeholder ?: 'Select option' }}</option>
                                    @if($field->options)
                                        @foreach($field->options as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            @elseif($field->type === 'textarea')
                                <textarea
                                    wire:model="customFieldValues.{{ $field->name }}"
                                    rows="3"
                                    placeholder="{{ $field->placeholder }}"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                ></textarea>
                            @elseif($field->type === 'checkbox')
                                <div class="mt-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            wire:model="customFieldValues.{{ $field->name }}"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 h-5 w-5"
                                        />
                                        <span class="ml-2.5 text-sm font-semibold text-gray-700">{{ $field->placeholder ?: 'Yes' }}</span>
                                    </label>
                                </div>
                            @endif
                            
                            @error("customFieldValues.{$field->name}")
                                <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Parent Account Creation Toggle --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4 gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Create Parent Account</h2>
                        <p class="text-xs text-gray-500 mt-1">Create a new parent account and link to this student automatically</p>
                    </div>
                    <label class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 px-4 hover:bg-gray-100 transition-colors">
                        <span class="text-sm font-bold text-gray-900 mr-4">Enable Creation</span>
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" wire:model.live="create_parent_account" class="sr-only peer" />
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500 peer-focus:ring-offset-2 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </div>
                    </label>
                </div>

                @if($create_parent_account)
                    <div class="space-y-6 pt-2 animate-fadeIn">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Parent Name *</label>
                                <input
                                    wire:model="parent_name"
                                    type="text"
                                    placeholder="e.g., John Doe Snr"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                                @error('parent_name')
                                    <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Email Address *</label>
                                <input
                                    wire:model="parent_email"
                                    type="email"
                                    placeholder="e.g., parent@school.edu"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                                @error('parent_email')
                                    <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Phone Number</label>
                                <input
                                    wire:model="parent_phone"
                                    type="text"
                                    placeholder="e.g., +23480..."
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                                @error('parent_phone')
                                    <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-2">Login Password *</label>
                                <input
                                    wire:model="parent_password"
                                    type="password"
                                    placeholder="Minimum 6 characters"
                                    class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                />
                                @error('parent_password')
                                    <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <strong>Portal Access Granted:</strong> The parent will automatically receive login credentials and can manage/track student homework, performance, report cards, and fee status.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Link Existing Parents --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Linked Parents</h2>
                <p class="text-xs text-gray-500 mb-4">Link this student profile to existing parent accounts in the system.</p>
                <div>
                    <select
                        wire:model="parent_ids"
                        multiple
                        class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        style="min-height: 140px;"
                    >
                        @foreach ($this->availableParents as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->email }})</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500 font-semibold uppercase tracking-wider">💡 Tip: Hold Ctrl (Windows) or Cmd (Mac) to select multiple parents.</p>
                    @error('parent_ids')
                        <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Emergency & Guardian Info --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Emergency & Guardian Info</h2>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Guardian Name</label>
                        <input
                            wire:model="guardian_name"
                            type="text"
                            placeholder="e.g., Uncle Adams"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('guardian_name')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Guardian Phone</label>
                        <input
                            wire:model="guardian_phone"
                            type="text"
                            placeholder="e.g., +234..."
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('guardian_phone')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-bold text-gray-900 mb-2">Guardian Address</label>
                        <textarea
                            wire:model="guardian_address"
                            rows="3"
                            placeholder="Full home or office address"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        ></textarea>
                        @error('guardian_address')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column (Takes 1/3 space) --}}
        <div class="space-y-6">
            {{-- Profile Photo --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Profile Photo</h2>
                <label for="passport" class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors group relative overflow-hidden">
                    @if ($passport)
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <img src="{{ $passport->temporaryUrl() }}" class="w-16 h-16 rounded-full object-cover mb-3 ring-2 ring-gray-100 shadow-sm" />
                            <p class="text-xs text-gray-700 font-bold truncate px-4 max-w-full">Preview (not saved yet)</p>
                            <p class="text-[10px] text-gray-500 mt-1">Click to change photo</p>
                        </div>
                    @elseif ($student?->passport_photo_url)
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <img src="{{ $student->passport_photo_url }}" class="w-16 h-16 rounded-full object-cover mb-3 ring-2 ring-gray-100 shadow-sm" />
                            <p class="text-xs text-gray-700 font-bold truncate px-4 max-w-full">Current Photo</p>
                            <p class="text-[10px] text-gray-500 mt-1">Click to change photo</p>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="mb-3 w-8 h-8 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-1 text-sm font-bold text-gray-700">Click to upload photo</p>
                            <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                        </div>
                    @endif
                    <input id="passport" type="file" wire:model="passport" accept="image/*" class="hidden" />
                </label>
                <div wire:loading wire:target="passport" class="mt-2 flex items-center gap-2 text-xs font-bold text-brand-600 animate-pulse">
                    <svg class="h-4 w-4 animate-spin text-brand-600" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Uploading image...
                </div>
                @error('passport')
                    <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Student Portal Password --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-1 border-b border-gray-100 pb-2">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Portal Password</h2>
                </div>
                <p class="text-xs text-gray-500 mb-4 mt-2">
                    @if($student)
                        Leave blank to keep current password.
                    @else
                        Required. Used along with their <strong>admission number</strong> to log in.
                    @endif
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">
                            {{ $student ? 'New Password' : 'Password *' }}
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <input
                                wire:model="student_password"
                                :type="show ? 'text' : 'password'"
                                placeholder="Enter secure password"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 pr-10 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                            />
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600">
                                <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('student_password')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Confirm Password {{ !$student ? '*' : '' }}</label>
                        <input
                            wire:model="student_password_confirmation"
                            type="password"
                            placeholder="Repeat password"
                            class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                        />
                        @error('student_password_confirmation')
                            <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Status & Actions --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                <h2 class="text-base font-semibold text-gray-900 mb-2 border-b border-gray-100 pb-2">Status & Actions</h2>
                
                <div>
                    <label class="block text-sm font-bold text-gray-900 mb-2">Account Status</label>
                    <select
                        wire:model.live="status"
                        class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                    >
                        <option value="Active">Active</option>
                        <option value="Graduated">Graduated</option>
                        <option value="Expelled">Expelled</option>
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 space-y-3">
                    <button type="submit" 
                            class="flex w-full justify-center items-center gap-2 rounded-lg bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Save Student Profile
                    </button>
                    <a href="{{ route('students.index') }}" 
                       class="flex w-full justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Save Processing Modal --}}
    <div wire:loading.flex wire:target="save" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/40 backdrop-blur-[2px]" style="display: none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 transform transition-all">
            <div class="text-center">
                <div class="mx-auto mb-5 h-16 w-16 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-200">
                    <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Saving Student Profile</h3>
                <p class="text-gray-600 mb-6 text-sm">Please hold on while we register or update the student profile...</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-500 to-indigo-600 rounded-full animate-progress" style="width: 0%;"></div>
                </div>
            </div>
        </div>
    </div>

@script
    let progressModal = null;

    $wire.on('validation-error', () => {
        if (progressModal) { progressModal.remove(); progressModal = null; }
        showModal('error', 'Validation Error', 'Please fix the validation errors before saving.');
    });

    $wire.on('upload-error', (event) => {
        if (progressModal) { progressModal.remove(); progressModal = null; }
        showModal('error', 'Upload Failed', event[0].message);
    });

    $wire.on('student-saved', (event) => {
        if (progressModal) { progressModal.remove(); progressModal = null; }
        const data = event?.[0] ?? event;
        showStudentSavedModal(data);
    });

    function showProgressModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform" style="animation: slideUp 0.3s ease-out">
                <div class="text-center">
                    <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                        <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Saving Student...</h3>
                    <p class="text-gray-600 mb-4">Please wait while we save the student information.</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="progress-bar h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full" style="width: 0%; animation: progress 2s ease-in-out infinite"></div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        return modal;
    }

    function showModal(type, title, message, onClose = null) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        
        const colors = {
            success: { bg: 'from-emerald-500 to-teal-500', icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            error: { bg: 'from-rose-500 to-red-500', icon: 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
        };
        
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full transform" style="animation: slideUp 0.3s ease-out">
                <div class="bg-gradient-to-r ${colors[type].bg} p-6 rounded-t-3xl">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="${colors[type].icon}" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">${title}</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 text-lg leading-relaxed">${message}</p>
                </div>
                <div class="p-6 pt-0 flex gap-3">
                    <button onclick="this.closest('.fixed').remove(); ${onClose ? 'arguments[0]()' : ''}" class="flex-1 bg-gradient-to-r ${colors[type].bg} text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all">
                        ${type === 'success' ? 'View Students' : 'Close'}
                    </button>
                </div>
            </div>
        `;
        
        if (onClose) {
            modal.querySelector('button').onclick = () => { modal.remove(); onClose(); };
        }
        
        document.body.appendChild(modal);
        modal.onclick = (e) => { if (e.target === modal) { modal.remove(); if (onClose) onClose(); } };
    }

    function showStudentSavedModal(data) {
        const studentsUrl = '{{ route('students.index') }}';
        const downloadUrl = data?.downloadUrl;
        const isNew = !!data?.isNew;
        const parentCreated = !!data?.parentCreated;
        const parentEmail = data?.parentEmail;
        const parentPassword = data?.parentPassword;

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        overlay.style.animation = 'fadeIn 0.2s ease-out';

        const panel = document.createElement('div');
        panel.className = 'bg-white rounded-3xl shadow-2xl max-w-lg w-full transform';
        panel.style.animation = 'slideUp 0.3s ease-out';

        const header = document.createElement('div');
        header.className = 'bg-gradient-to-r from-emerald-500 to-teal-500 p-6 rounded-t-3xl';

        const headerRow = document.createElement('div');
        headerRow.className = 'flex items-center gap-4';

        const iconWrap = document.createElement('div');
        iconWrap.className = 'flex-shrink-0';
        iconWrap.innerHTML = `
            <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        `;

        const title = document.createElement('h3');
        title.className = 'text-2xl font-bold text-white';
        title.textContent = isNew ? 'Student Created Successfully' : 'Student Updated Successfully';

        headerRow.appendChild(iconWrap);
        headerRow.appendChild(title);
        header.appendChild(headerRow);

        const body = document.createElement('div');
        body.className = 'p-6';

        const message = document.createElement('p');
        message.className = 'text-gray-700 text-lg leading-relaxed mb-4';
        const actionText = isNew ? 'created' : 'updated';
        const nameText = data?.name ? String(data.name) : 'Student';
        const admissionText = data?.admission ? String(data.admission) : '';
        message.textContent = admissionText
            ? `${nameText} (${admissionText}) has been ${actionText} successfully.`
            : `${nameText} has been ${actionText} successfully.`;

        body.appendChild(message);

        // Add parent account info if created
        if (parentCreated && parentEmail && parentPassword) {
            const parentInfo = document.createElement('div');
            parentInfo.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4';
            parentInfo.innerHTML = `
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-2">Parent Account Created</h4>
                        <div class="text-sm text-blue-800 space-y-1">
                            <div><strong>Email:</strong> ${parentEmail}</div>
                            <div><strong>Password:</strong> ${parentPassword}</div>
                            <div class="text-xs text-blue-600 mt-2">Please share these login details with the parent.</div>
                        </div>
                    </div>
                </div>
            `;
            body.appendChild(parentInfo);
        }

        const actions = document.createElement('div');
        actions.className = 'p-6 pt-0 flex gap-3';

        const goStudents = () => { window.location.href = studentsUrl; };
        const close = () => { overlay.remove(); };

        if (isNew && downloadUrl) {
            const downloadBtn = document.createElement('button');
            downloadBtn.type = 'button';
            downloadBtn.className = 'flex-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all';
            downloadBtn.textContent = 'Download Admission Letter';
            downloadBtn.onclick = () => {
                window.open(downloadUrl, '_blank', 'noopener');
                goStudents();
            };

            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.className = 'flex-1 bg-white text-emerald-700 font-bold py-3 px-6 rounded-xl border border-emerald-200 hover:shadow transition-all';
            viewBtn.textContent = 'View Students';
            viewBtn.onclick = goStudents;

            actions.appendChild(downloadBtn);
            actions.appendChild(viewBtn);
        } else {
            const okBtn = document.createElement('button');
            okBtn.type = 'button';
            okBtn.className = 'flex-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all';
            okBtn.textContent = 'View Students';
            okBtn.onclick = goStudents;
            actions.appendChild(okBtn);
        }

        panel.appendChild(header);
        panel.appendChild(body);
        panel.appendChild(actions);
        overlay.appendChild(panel);
        document.body.appendChild(overlay);

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                close();
                goStudents();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                close();
                goStudents();
            }
        }, { once: true });
    }
@endscript

<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes progress {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 90%; }
    }
    .animate-progress {
        animation: progress 2s ease-in-out infinite;
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
</style>
</div>
