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
                                    <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-500">No allocations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table>
                </div>
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
                    <div class="card-padded">
                        <div class="text-sm font-semibold text-slate-900">Assign Class & Subject</div>
                        <div class="mt-1 text-sm text-slate-600">Allocate subjects to teach per class.</div>

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

                            <div>
                                @php
                                    $selectedClassIds = collect(old('class_ids', old('class_id') ? [old('class_id')] : []))
                                        ->map(fn ($id) => (string) $id)
                                        ->all();
                                @endphp
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Classes</label>
                                <select name="class_ids[]" class="mt-2 select" multiple size="6" required>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected(in_array((string) $class->id, $selectedClassIds, true))>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-1 text-xs text-slate-500">Hold Ctrl (Windows) / Cmd (Mac) to pick multiple.</div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Subject</label>
                                <select name="subject_id" class="mt-2 select" required>
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>
                                            {{ $subject->code }} — {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn-primary w-full justify-center">
                                Assign
                            </button>
                            </form>

                            <div class="mt-4 text-xs text-slate-500">
                                Assign the same subject across multiple classes in one go.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
