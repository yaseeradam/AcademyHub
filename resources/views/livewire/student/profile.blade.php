<div class="space-y-6 max-w-2xl mx-auto">

    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 p-8 shadow-xl">
        <div class="relative flex flex-col sm:flex-row items-center gap-6">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
                @if($student->passport_photo_url)
                    <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}"
                         class="h-24 w-24 rounded-full border-4 border-white shadow-lg object-cover">
                @else
                    <div class="h-24 w-24 rounded-full border-4 border-white bg-white/20 flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                        {{ substr($student->first_name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="text-center sm:text-left">
                <h1 class="text-2xl font-bold text-white">{{ $student->full_name }}</h1>
                <p class="text-green-100 text-sm mt-1">{{ $student->admission_number }}</p>
                <p class="text-green-100 text-sm">{{ $student->schoolClass->name ?? '' }}{{ $student->section ? ' — ' . $student->section->name : '' }}</p>
            </div>
        </div>
    </div>

    <!-- Upload Photo -->
    <div class="rounded-2xl bg-white shadow-lg p-6">
        <h2 class="text-base font-bold text-gray-900 mb-4">Profile Photo</h2>

        @if(session('photo_success'))
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-3 text-sm text-green-700 font-medium">
                {{ session('photo_success') }}
            </div>
        @endif

        <form wire:submit="uploadPhoto" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <input type="file" wire:model="photo" accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition" />
            <button type="submit"
                    class="flex-shrink-0 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow hover:shadow-md transition-all">
                <span wire:loading.remove wire:target="uploadPhoto">Upload</span>
                <span wire:loading wire:target="uploadPhoto">Uploading...</span>
            </button>
        </form>

        @error('photo')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div wire:loading wire:target="photo" class="mt-2 text-xs text-gray-400">Processing image...</div>
    </div>

    <!-- Personal Info (read-only) -->
    <div class="rounded-2xl bg-white shadow-lg p-6">
        <h2 class="text-base font-bold text-gray-900 mb-4">Personal Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Full Name</span>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $student->full_name }}</p>
            </div>
            <div>
                <span class="text-gray-500">Admission No.</span>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $student->admission_number }}</p>
            </div>
            <div>
                <span class="text-gray-500">Gender</span>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $student->gender }}</p>
            </div>
            <div>
                <span class="text-gray-500">Date of Birth</span>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $student->dob?->format('d M Y') ?? '—' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Blood Group</span>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $student->blood_group ?? '—' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Status</span>
                <p class="font-semibold text-gray-900 mt-0.5">{{ $student->status }}</p>
            </div>
        </div>
    </div>

    <!-- Guardian / Contact Info -->
    <div class="rounded-2xl bg-white shadow-lg p-6">
        <h2 class="text-base font-bold text-gray-900 mb-4">Guardian / Contact Info</h2>

        @if(session('info_success'))
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-3 text-sm text-green-700 font-medium">
                {{ session('info_success') }}
            </div>
        @endif

        <form wire:submit="saveInfo" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Guardian Name</label>
                <input wire:model="guardian_name" type="text"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                       placeholder="Guardian full name" />
                @error('guardian_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Guardian Phone</label>
                <input wire:model="guardian_phone" type="text"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                       placeholder="Phone number" />
                @error('guardian_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Guardian Address</label>
                <input wire:model="guardian_address" type="text"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                       placeholder="Home address" />
                @error('guardian_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow hover:shadow-md transition-all">
                Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="rounded-2xl bg-white shadow-lg p-6">
        <h2 class="text-base font-bold text-gray-900 mb-4">Change Password</h2>

        @if(session('password_success'))
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-3 text-sm text-green-700 font-medium">
                {{ session('password_success') }}
            </div>
        @endif

        <form wire:submit="savePassword" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Current Password</label>
                <input wire:model="current_password" type="password"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                       placeholder="Your current password" />
                @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">New Password</label>
                <input wire:model="new_password" type="password"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                       placeholder="Min. 6 characters" />
                @error('new_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm New Password</label>
                <input wire:model="new_password_confirmation" type="password"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-green-400 focus:border-transparent transition"
                       placeholder="Repeat new password" />
            </div>
            <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-slate-700 to-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow hover:shadow-md transition-all">
                Update Password
            </button>
        </form>
    </div>

</div>
