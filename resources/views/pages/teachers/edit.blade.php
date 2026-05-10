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
                        />
                    </div>
                </div>
            </div>

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
