<div class="space-y-6">
    <x-page-header
        :title="$student ? 'Edit Student' : 'Add Student'"
        subtitle="Admissions and student bio-data."
        accent="students"
    >
        <x-slot:actions>
            <a href="{{ route('students.index') }}" class="btn-ghost">Back</a>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="text-sm font-semibold text-gray-900">Bio</div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-gray-700">Admission Number</label>
                            @if(!$student)
                                <label class="flex items-center gap-2 text-xs text-gray-600">
                                    <input type="checkbox" wire:model.live="auto_admission" class="rounded">
                                    Auto-generate
                                </label>
                            @endif
                        </div>
                        <input
                            wire:model.live="admission_number"
                            type="text"
                            @if($auto_admission && !$student) readonly @endif
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500 {{ $auto_admission && !$student ? 'bg-gray-50' : '' }}"
                        />
                        @error('admission_number')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Gender</label>
                        <select
                            wire:model.live="gender"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        @error('gender')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">First Name</label>
                        <input
                            wire:model.live="first_name"
                            type="text"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        @error('first_name')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Last Name</label>
                        <input
                            wire:model.live="last_name"
                            type="text"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        @error('last_name')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Class</label>
                        <select
                            wire:model.live="class_id"
                            wire:change="$refresh"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="">Select class</option>
                            @foreach ($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Section</label>
                        <select
                            wire:model="section_id"
                            wire:key="sections-{{ $class_id }}"
                            {{ !$class_id ? 'disabled' : '' }}
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500 {{ !$class_id ? 'bg-gray-50 text-gray-400' : '' }}"
                        >
                            <option value="">{{ $class_id ? 'Select section' : 'Select class first' }}</option>
                            @foreach ($this->sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Date of Birth</label>
                        <input
                            wire:model.live="dob"
                            type="date"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        @error('dob')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Blood Group</label>
                        <input
                            wire:model.live="blood_group"
                            type="text"
                            placeholder="e.g. O+"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        @error('blood_group')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Custom Fields -->
                    @foreach($this->customFields as $field)
                        <div class="{{ $field->type === 'textarea' ? 'sm:col-span-2' : '' }}">
                            <label class="text-sm font-semibold text-gray-700">
                                {{ $field->label }}
                                @if($field->required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            
                            @if($field->type === 'text')
                                <input
                                    wire:model.live="customFieldValues.{{ $field->name }}"
                                    type="text"
                                    placeholder="{{ $field->placeholder }}"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            @elseif($field->type === 'number')
                                <input
                                    wire:model.live="customFieldValues.{{ $field->name }}"
                                    type="number"
                                    placeholder="{{ $field->placeholder }}"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            @elseif($field->type === 'date')
                                <input
                                    wire:model.live="customFieldValues.{{ $field->name }}"
                                    type="date"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            @elseif($field->type === 'select')
                                <select
                                    wire:model.live="customFieldValues.{{ $field->name }}"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
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
                                    wire:model.live="customFieldValues.{{ $field->name }}"
                                    rows="3"
                                    placeholder="{{ $field->placeholder }}"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                ></textarea>
                            @elseif($field->type === 'checkbox')
                                <div class="mt-2">
                                    <label class="flex items-center">
                                        <input
                                            wire:model.live="customFieldValues.{{ $field->name }}"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">{{ $field->placeholder ?: 'Yes' }}</span>
                                    </label>
                                </div>
                            @endif
                            
                            @error("customFieldValues.{$field->name}")
                                <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Passport Photo</div>

                    <div class="mt-5">
                        @if ($passport)
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $passport->temporaryUrl() }}"
                                    alt="Passport preview"
                                    class="h-20 w-20 rounded-xl object-cover ring-1 ring-inset ring-gray-200"
                                />
                                <div class="text-xs text-gray-500">Preview (not saved yet)</div>
                            </div>
                        @elseif ($student?->passport_photo_url)
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $student->passport_photo_url }}"
                                    alt="{{ $student->full_name }}"
                                    class="h-20 w-20 rounded-xl object-cover ring-1 ring-inset ring-gray-200"
                                />
                                <div class="text-xs text-gray-500">Current photo</div>
                            </div>
                        @endif

                        <input
                            type="file"
                            wire:model="passport"
                            accept="image/*"
                            class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                        />
                        <div wire:loading wire:target="passport" class="mt-2 flex items-center gap-2 text-xs font-medium text-brand-600">
                            <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading image...
                        </div>
                        @error('passport')
                            <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Guardian</div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Name</label>
                            <input
                                wire:model.live="guardian_name"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                            @error('guardian_name')
                                <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700">Phone</label>
                            <input
                                wire:model.live="guardian_phone"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                            @error('guardian_phone')
                                <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700">Address</label>
                            <textarea
                                wire:model.live="guardian_address"
                                rows="3"
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            ></textarea>
                            @error('guardian_address')
                                <div class="mt-2 text-sm text-orange-700">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Status</div>

                    <div class="mt-5 space-y-4">
                        <select
                            wire:model.live="status"
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="Active">Active</option>
                            <option value="Graduated">Graduated</option>
                            <option value="Expelled">Expelled</option>
                        </select>
                        @error('status')
                            <div class="text-sm text-orange-700">{{ $message }}</div>
                        @enderror

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600"
                        >
                            Save Student
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Save Processing Modal -->
    <div wire:loading.flex wire:target="save" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/40 backdrop-blur-[2px]" style="display: none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 transform transition-all">
            <div class="text-center">
                <div class="mx-auto mb-5 h-16 w-16 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-200">
                    <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Saving Changes</h3>
                <p class="text-gray-600 mb-6 text-sm">Please hold on while we update the student record. This usually takes just a moment.</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-500 to-indigo-600 rounded-full" style="width: 0%; animation: progress 2s ease-in-out infinite"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
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

    // Modals are now handled by wire:loading for reliability

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

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        overlay.style.animation = 'fadeIn 0.2s ease-out';

        const panel = document.createElement('div');
        panel.className = 'bg-white rounded-3xl shadow-2xl max-w-md w-full transform';
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
        message.className = 'text-gray-700 text-lg leading-relaxed';
        const actionText = isNew ? 'created' : 'updated';
        const nameText = data?.name ? String(data.name) : 'Student';
        const admissionText = data?.admission ? String(data.admission) : '';
        message.textContent = admissionText
            ? `${nameText} (${admissionText}) has been ${actionText} successfully.`
            : `${nameText} has been ${actionText} successfully.`;

        body.appendChild(message);

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
</script>
<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes progress {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 90%; }
    }
</style>
@endscript
