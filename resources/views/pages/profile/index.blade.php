@php
    /** @var \App\Models\User $user */
    $meta = $user->email;
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header title="My Profile" :subtitle="$meta" accent="settings">
            <x-slot:leading>
                @if ($user->profile_photo_url)
                    <img
                        src="{{ $user->profile_photo_url }}"
                        alt="{{ $user->name }}"
                        class="h-32 w-32 rounded-full object-cover ring-2 ring-white shadow-sm"
                    />
                @else
                    <x-avatar :name="$user->name" size="128" class="ring-2 ring-white shadow-sm" />
                @endif
            </x-slot:leading>
            <x-slot:actions>
                <x-status-badge variant="{{ $user->is_active ? 'success' : 'warning' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </x-status-badge>
                <a href="{{ route('dashboard') }}" class="btn-outline">
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
                <div class="text-sm font-semibold text-slate-900">Profile Photo</div>
                <div class="mt-1 text-sm text-slate-600">Upload your photo to show on your dashboard and in messages.</div>

                <form id="profile-photo-form" method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Choose photo</label>
                        <input
                            id="photo-input"
                            type="file"
                            name="photo"
                            accept="image/*"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-brand-400 focus:ring-brand-300"
                            required
                        />
                        <div id="photo-error" class="mt-2 text-xs text-slate-500">JPG/PNG up to 2MB.</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" id="photo-submit-btn" class="btn-primary">Upload</button>
                    </div>
                </form>

                <script>
                    document.getElementById('photo-input').addEventListener('change', function() {
                        const file = this.files[0];
                        const errorDiv = document.getElementById('photo-error');
                        const submitBtn = document.getElementById('photo-submit-btn');
                        
                        if (file) {
                            const fileSizeInMB = file.size / (1024 * 1024);
                            if (fileSizeInMB > 2) {
                                errorDiv.innerHTML = '<span class="text-red-600 font-medium">⚠️ This file is too large (' + fileSizeInMB.toFixed(2) + ' MB). Please choose a photo smaller than 2 MB.</span>';
                                this.value = ''; // Clear file input
                                submitBtn.disabled = true;
                                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            } else {
                                errorDiv.innerHTML = '<span class="text-green-600 font-medium">✓ File size is okay (' + fileSizeInMB.toFixed(2) + ' MB).</span>';
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                            }
                        } else {
                            errorDiv.textContent = 'JPG/PNG up to 2MB.';
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    });
                </script>

                @if ($user->profile_photo_url)
                    <form
                        method="POST"
                        action="{{ route('profile.photo.destroy') }}"
                        onsubmit="return confirm('Remove your profile photo?')"
                        class="mt-2"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-outline text-red-700 ring-red-200 hover:bg-red-50">
                            Remove Photo
                        </button>
                    </form>
                @endif
            </div>

            <div class="card-padded">
                <div class="text-sm font-semibold text-slate-900">Account Details</div>
                <div class="mt-1 text-sm text-slate-600">Update your name and email.</div>

                <form method="POST" action="{{ route('profile.details') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Name</label>
                        <input
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            class="mt-2 input w-full"
                            required
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Email</label>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            class="mt-2 input w-full"
                            required
                        />
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm ring-1 ring-inset ring-slate-200">
                        <div class="text-slate-600">Role</div>
                        <div class="font-semibold text-slate-900">{{ ucfirst($user->role ?? 'user') }}</div>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">Save Details</button>
                </form>

                <div class="mt-6 border-t border-slate-200/60 pt-6">
                    <div class="text-sm font-semibold text-slate-900">Change Password</div>
                    <div class="mt-1 text-sm text-slate-600">Use a strong password (min 8 characters).</div>

                    <form method="POST" action="{{ route('profile.password') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Current password</label>
                            <input name="current_password" type="password" class="mt-2 input w-full" required />
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">New password</label>
                            <input name="password" type="password" class="mt-2 input w-full" required />
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Confirm new password</label>
                            <input name="password_confirmation" type="password" class="mt-2 input w-full" required />
                        </div>
                        <button type="submit" class="btn-outline w-full justify-center">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
