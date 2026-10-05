@php
    /** @var \App\Models\User $teacher */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SubjectAllocation> $allocations */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Subject> $subjects */

    $user = auth()->user();
    $meta = $teacher->email;
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header :title="$teacher->name" :subtitle="$meta" accent="teachers">
            <x-slot:leading>
                @if ($teacher->profile_photo_url)
                    <img
                        src="{{ $teacher->profile_photo_url }}"
                        alt="{{ $teacher->name }}"
                        class="h-32 w-32 rounded-full object-cover ring-2 ring-white shadow-sm"
                    />
                @else
                    <x-avatar :name="$teacher->name" size="128" class="ring-2 ring-white shadow-sm" />
                @endif
            </x-slot:leading>
            <x-slot:actions>
                <x-status-badge variant="{{ $teacher->is_active ? 'success' : 'warning' }}">
                    {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                </x-status-badge>
                @if ($user?->role === 'admin')
                    <a href="{{ route('teachers.edit', $teacher) }}" class="btn-outline">Edit</a>
                    <form
                        method="POST"
                        action="{{ route('teachers.destroy', $teacher) }}"
                        class="inline"
                        onsubmit="return confirm('Delete this teacher? This action cannot be undone.')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-warning">Delete</button>
                    </form>
                @endif
                <a href="{{ route('teachers') }}" class="btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </x-slot:actions>
        </x-page-header>

        @if (session('status'))
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card-padded border border-orange-200 bg-orange-50/60">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="card-padded lg:col-span-2">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm font-semibold text-slate-900">Allocations</div>
                    <div class="text-xs text-slate-500">{{ number_format((int) $allocations->count()) }} total</div>
                </div>

                <div class="mt-4">
                    <x-table>
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Class</th>
                                <th class="px-5 py-3">Arm / Subclass</th>
                                <th class="px-5 py-3">Subject</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($allocations as $allocation)
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-900">
                                        {{ $allocation->schoolClass?->name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($allocation->section)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                {{ $allocation->section->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                All Arms
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-slate-900">{{ $allocation->subject?->name ?? '—' }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $allocation->subject?->code ?? '' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($user?->role === 'admin')
                                            <form
                                                method="POST"
                                                action="{{ route('teachers.allocations.destroy', ['teacher' => $teacher, 'allocation' => $allocation]) }}"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost">
                                                    Remove
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">No allocations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>

                @if(isset($customFields) && $customFields->count() > 0 && !empty($teacher->custom_fields))
                    <div class="card-padded mt-4">
                        <div class="text-sm font-semibold text-slate-900 mb-4">Additional Information</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach($customFields as $field)
                                @if(isset($teacher->custom_fields[$field->name]) && $teacher->custom_fields[$field->name] !== '')
                                    <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                                        <div class="text-xs font-medium text-slate-500">{{ $field->label }}</div>
                                        <div class="mt-1 text-sm font-semibold text-slate-900">
                                            @if($field->type === 'checkbox')
                                                {{ $teacher->custom_fields[$field->name] ? 'Yes' : 'No' }}
                                            @else
                                                {{ $teacher->custom_fields[$field->name] }}
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="card-padded">
                    <div class="flex items-center justify-between gap-4">
                        <div class="text-sm font-semibold text-slate-900">Profile Photo</div>
                        @if ($user?->role === 'admin')
                            <span class="text-xs text-slate-500">Upload / replace</span>
                        @endif
                    </div>

                    <div class="mt-4">
                        @if ($teacher->profile_photo_url)
                            <img
                                src="{{ $teacher->profile_photo_url }}"
                                alt="{{ $teacher->name }}"
                                class="h-48 w-full rounded-2xl object-cover ring-1 ring-inset ring-gray-200/70"
                            />
                        @else
                            <div class="flex h-48 items-center justify-center rounded-2xl bg-slate-50 text-slate-600 ring-1 ring-inset ring-slate-200">
                                <div class="text-center">
                                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white ring-1 ring-inset ring-slate-200">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                            <path d="M5 21a7 7 0 0 1 14 0" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 text-sm font-semibold text-slate-900">No photo yet</div>
                                    <div class="mt-1 text-sm text-slate-600">Upload a teacher profile picture.</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($user?->role === 'admin')
                        <form
                            id="teacher-photo-form"
                            method="POST"
                            action="{{ route('teachers.photo', $teacher) }}"
                            enctype="multipart/form-data"
                            class="mt-4 space-y-3"
                        >
                            @csrf
                            <input
                                id="teacher-photo-input"
                                name="photo"
                                type="file"
                                accept="image/*"
                                class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                                required
                            />
                            <div id="teacher-photo-error" class="mt-2 text-xs text-slate-500">JPG/PNG up to 5MB.</div>
                            <button type="submit" id="teacher-photo-submit-btn" class="btn-primary w-full justify-center">Upload Photo</button>
                        </form>

                        <script>
                            document.getElementById('teacher-photo-input').addEventListener('change', function() {
                                const file = this.files[0];
                                const errorDiv = document.getElementById('teacher-photo-error');
                                const submitBtn = document.getElementById('teacher-photo-submit-btn');
                                
                                if (file) {
                                    const fileSizeInMB = file.size / (1024 * 1024);
                                    if (fileSizeInMB > 5) {
                                        errorDiv.innerHTML = '<span class="text-red-600 font-medium">⚠️ This file is too large (' + fileSizeInMB.toFixed(2) + ' MB). Please choose a photo smaller than 5 MB.</span>';
                                        this.value = ''; // Clear file input
                                        submitBtn.disabled = true;
                                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                                    } else {
                                        errorDiv.innerHTML = '<span class="text-green-600 font-medium">✓ File size is okay (' + fileSizeInMB.toFixed(2) + ' MB).</span>';
                                        submitBtn.disabled = false;
                                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                    }
                                } else {
                                    errorDiv.textContent = 'JPG/PNG up to 5MB.';
                                    submitBtn.disabled = false;
                                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                }
                            });
                        </script>
                    @else
                        <div class="mt-4 text-xs text-slate-500">Only admins can upload profile photos.</div>
                    @endif
                </div>

                <div class="card-padded">
                    <div class="text-sm font-semibold text-slate-900">Teacher Details</div>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-slate-500">Email</div>
                            <div class="font-semibold text-slate-900">{{ $teacher->email }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-slate-500">Role</div>
                            <div class="font-semibold text-slate-900">{{ ucfirst($teacher->role) }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-slate-500">Joined</div>
                            <div class="font-semibold text-slate-900">{{ $teacher->created_at?->format('M j, Y') }}</div>
                        </div>
                    </div>
                </div>

                @if ($user?->role === 'admin')
                    <div class="card-padded" x-data="{
                        classes: {{ Js::from($classes->map(fn($c) => [
                            'id' => $c->id,
                            'name' => $c->name,
                            'sections' => $c->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()
                        ])->values()) }},
                        subjects: {{ Js::from($subjects->map(fn($s) => [
                            'id' => (string) $s->id,
                            'name' => $s->name,
                            'code' => $s->code
                        ])->values()) }},
                        selectedClassId: '{{ old('class_id', $classes->first()?->id ?? '') }}',
                        selectedSectionId: '{{ old('section_id', '') }}',
                        selectedSubjects: {{ Js::from(array_map('strval', (array) old('subject_ids', old('subject_id') ? [old('subject_id')] : []))) }},
                        search: '',
                        
                        get availableSections() {
                            const c = this.classes.find(item => String(item.id) === String(this.selectedClassId));
                            return c ? c.sections : [];
                        },
                        get filteredSubjects() {
                            const q = this.search.toLowerCase().trim();
                            if (!q) return this.subjects;
                            return this.subjects.filter(s => s.name.toLowerCase().includes(q) || (s.code && s.code.toLowerCase().includes(q)));
                        },
                        toggleAll() {
                            const visibleIds = this.filteredSubjects.map(s => String(s.id));
                            const allVisibleSelected = visibleIds.length > 0 && visibleIds.every(id => this.selectedSubjects.includes(id));
                            if (allVisibleSelected) {
                                this.selectedSubjects = this.selectedSubjects.filter(id => !visibleIds.includes(id));
                            } else {
                                this.selectedSubjects = Array.from(new Set([...this.selectedSubjects, ...visibleIds]));
                            }
                        }
                    }">
                        <div class="text-sm font-semibold text-slate-900">Assign Class &amp; Subjects</div>
                        <div class="mt-1 text-xs text-slate-500">Allocate subjects to teach per class or specific subclass/arm.</div>

                        @if ($classes->isEmpty() || $subjects->isEmpty())
                            <div class="mt-4 rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-600 ring-1 ring-inset ring-slate-200">
                                Create at least one class and one subject before assigning.
                            </div>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                <a href="{{ route('classes.index') }}" class="btn-outline w-full justify-center sm:w-auto">Manage Classes</a>
                                <a href="{{ route('subjects.index') }}" class="btn-outline w-full justify-center sm:w-auto">Manage Subjects</a>
                            </div>
                        @else
                            <form method="POST" action="{{ route('teachers.allocations.store', $teacher) }}" class="mt-4 space-y-3">
                                @csrf

                                {{-- Class Selector --}}
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Class</label>
                                    <select name="class_id" x-model="selectedClassId" @change="selectedSectionId = ''" class="mt-1.5 select w-full" required>
                                        <template x-for="c in classes" :key="c.id">
                                            <option :value="c.id" x-text="c.name" :selected="String(c.id) === String(selectedClassId)"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Subclass / Arm Selector --}}
                                <div>
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Arm / Subclass</label>
                                        <span class="text-[11px] text-slate-400" x-show="availableSections.length === 0">Class-wide</span>
                                    </div>
                                    <select name="section_id" x-model="selectedSectionId" class="mt-1.5 select w-full">
                                        <option value="">All Arms (Entire Class)</option>
                                        <template x-for="sec in availableSections" :key="sec.id">
                                            <option :value="sec.id" x-text="sec.name" :selected="String(sec.id) === String(selectedSectionId)"></option>
                                        </template>
                                    </select>
                                    <p class="mt-1 text-[11px] text-slate-500 leading-tight">
                                        Selecting an arm (e.g. Gold) scopes the teacher so they only see and grade that arm's students.
                                    </p>
                                </div>

                                {{-- Bulk Subjects Selector --}}
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            Subjects (<span x-text="selectedSubjects.length"></span> selected)
                                        </label>
                                        <button type="button" @click="toggleAll()" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                                            <span x-text="filteredSubjects.length > 0 && filteredSubjects.every(s => selectedSubjects.includes(String(s.id))) ? 'Deselect All' : 'Select All'"></span>
                                        </button>
                                    </div>

                                    <!-- Search filter -->
                                    <div class="relative mb-2">
                                        <input type="text" x-model="search" placeholder="Search subjects..." class="w-full text-xs rounded-lg border-slate-300 pl-7 pr-3 py-1.5 focus:border-indigo-500 focus:ring-indigo-500" />
                                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>

                                    <!-- Scrollable checkbox list -->
                                    <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-xl bg-slate-50/50 p-2 space-y-0.5">
                                        <template x-for="subj in filteredSubjects" :key="subj.id">
                                            <label class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white cursor-pointer transition-colors text-xs select-none">
                                                <input type="checkbox" name="subject_ids[]" :value="subj.id" x-model="selectedSubjects" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                                <span class="font-medium text-slate-800 flex-1" x-text="subj.name"></span>
                                                <span class="text-[10px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded" x-text="subj.code"></span>
                                            </label>
                                        </template>
                                        <div x-show="filteredSubjects.length === 0" class="py-4 text-center text-xs text-slate-400">
                                            No subjects match your search.
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" 
                                        class="btn-primary w-full justify-center mt-3" 
                                        :disabled="selectedSubjects.length === 0"
                                        :class="selectedSubjects.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                    <span>Assign <span x-text="selectedSubjects.length || 0"></span> Subject(s)</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
