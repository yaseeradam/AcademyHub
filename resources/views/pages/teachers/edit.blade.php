@php
    /** @var \App\Models\User $teacher */
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Edit Teacher" subtitle="Update teacher details and access status." accent="teachers">
            <x-slot:actions>
                <a href="{{ route('teachers.show', $teacher) }}" class="btn-outline">
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

        <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="card-padded">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-900">Full name</label>
                    <div class="mt-2">
                        <input
                            name="name"
                            class="input"
                            value="{{ old('name', $teacher->name) }}"
                            placeholder="e.g., Mrs. Anita Okoye"
                            required
                            autocomplete="name"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">Email</label>
                    <div class="mt-2">
                        <input
                            name="email"
                            type="email"
                            class="input"
                            value="{{ old('email', $teacher->email) }}"
                            placeholder="e.g., anita@school.edu"
                            required
                            autocomplete="email"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">WhatsApp Phone Number</label>
                    <div class="mt-2">
                        <input
                            name="whatsapp_phone"
                            type="tel"
                            class="input"
                            value="{{ old('whatsapp_phone', $teacher->whatsapp_phone) }}"
                            placeholder="e.g., 08012345678 or +2348012345678"
                            autocomplete="tel"
                        />
                    </div>
                    <div class="mt-1 text-xs text-slate-500">Used for 1-click WhatsApp messaging in the Parent Portal directory.</div>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-bold text-amber-950 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
                            Biometric K40 Hardware ID
                        </label>
                        <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-300">
                            Staff Range: 1 - 199
                        </span>
                    </div>
                    <div class="mt-1">
                        <input
                            name="k40_uid"
                            type="number"
                            min="1"
                            max="65535"
                            class="input font-mono font-bold text-amber-950 border-amber-300 focus:border-amber-500 focus:ring-amber-500/20"
                            value="{{ old('k40_uid', $teacher->k40_uid ?? ($teacher->custom_fields['k40_uid'] ?? '')) }}"
                            placeholder="e.g. 17"
                        />
                    </div>
                    @error('k40_uid')
                        <div class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</div>
                    @enderror
                    <div class="mt-1.5 text-xs text-amber-800/80">
                        Exact User ID programmed on the ZKTeco K40 device for this teacher.
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">New password</label>
                    <div class="mt-2">
                        <input
                            name="password"
                            type="password"
                            class="input"
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="mt-2 text-xs text-slate-500">Leave blank to keep the current password.</div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">Confirm new password</label>
                    <div class="mt-2">
                        <input
                            name="password_confirmation"
                            type="password"
                            class="input"
                            autocomplete="new-password"
                    </div>
                </div>
            </div>

            @if(isset($customFields) && $customFields->count() > 0)
                <div class="mt-6 border-t border-gray-200/70 pt-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">Additional Information</h3>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($customFields as $field)
                            @php
                                $val = old("custom_fields.{$field->name}", $teacher->custom_fields[$field->name] ?? null);
                            @endphp
                            <div>
                                <label class="text-sm font-semibold text-slate-900">
                                    {{ $field->label }}
                                    @if ($field->required) <span class="text-red-500">*</span> @endif
                                </label>
                                <div class="mt-2">
                                    @if ($field->type === 'select')
                                        <select
                                            name="custom_fields[{{ $field->name }}]"
                                            class="select"
                                            @if($field->required) required @endif
                                        >
                                            <option value="">Select...</option>
                                            @foreach ($field->options ?? [] as $opt)
                                                <option value="{{ $opt }}" @selected($val == $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($field->type === 'textarea')
                                        <textarea
                                            name="custom_fields[{{ $field->name }}]"
                                            rows="2"
                                            class="input"
                                            placeholder="{{ $field->placeholder }}"
                                            @if($field->required) required @endif
                                        >{{ $val }}</textarea>
                                    @elseif ($field->type === 'checkbox')
                                        <div class="flex items-center mt-2">
                                            <input
                                                type="checkbox"
                                                name="custom_fields[{{ $field->name }}]"
                                                value="1"
                                                id="cf_{{ $field->name }}"
                                                @checked($val)
                                                class="checkbox-custom"
                                            />
                                            <label for="cf_{{ $field->name }}" class="ml-2 text-sm text-slate-700">
                                                {{ $field->placeholder ?: 'Yes' }}
                                            </label>
                                        </div>
                                    @else
                                        <input
                                            type="{{ $field->type }}"
                                            name="custom_fields[{{ $field->name }}]"
                                            value="{{ $val }}"
                                            class="input"
                                            placeholder="{{ $field->placeholder }}"
                                            @if($field->required) required @endif
                                        />
                                    @endif
                                    @error("custom_fields.{$field->name}")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-3 border-t border-gray-200/70 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" class="checkbox-custom" name="is_active" value="1"
                            @checked(old('is_active', $teacher->is_active)) />
                        Active (can log in)
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-amber-700">
                        <input type="checkbox" class="checkbox-custom" name="is_class_teacher" value="1"
                            @checked(old('is_class_teacher', $teacher->is_class_teacher)) />
                        Class Teacher <span class="text-xs text-gray-500 font-normal">(gets Attendance menu)</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('teachers.show', $teacher) }}" class="btn-ghost">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
