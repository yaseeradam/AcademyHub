@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-600 p-8 shadow-2xl">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white/5"></div>
            
            <div class="relative flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-black text-white">Add New Teacher</h1>
                            <p class="text-indigo-100">Create a teacher account with access to academic modules</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('teachers') }}" class="flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/30">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Teachers
                </a>
            </div>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Please fix the following errors:</h3>
                        <ul class="mt-2 space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center gap-2">
                                    <div class="h-1 w-1 rounded-full bg-red-400"></div>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Form -->
        <form method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <!-- Personal Information Card -->
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-gray-200">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Personal Information</h2>
                            <p class="text-sm text-gray-600">Basic details and contact information</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-900">Full Name *</label>
                            <input
                                name="name"
                                type="text"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                                value="{{ old('name') }}"
                                placeholder="e.g., Mrs. Anita Okoye"
                                required
                                autocomplete="name"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-900">Email Address *</label>
                            <input
                                name="email"
                                type="email"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-900 transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                                value="{{ old('email') }}"
                                placeholder="e.g., anita@school.edu"
                                required
                                autocomplete="email"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Photo Card -->
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-gray-200">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100">
                            <svg class="h-5 w-5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="9" cy="9" r="2"/>
                                <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Profile Photo</h2>
                            <p class="text-sm text-gray-600">Upload a professional photo (optional)</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="flex items-center justify-center w-full">
                        <label for="photo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500 font-semibold">Click to upload photo</p>
                                <p class="text-xs text-gray-400">PNG, JPG or JPEG (MAX. 2MB)</p>
                            </div>
                            <input id="photo" name="photo" type="file" accept="image/*" class="hidden" />
                        </label>
                    </div>
                </div>
            </div>

            <!-- Security Settings Card -->
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl ring-1 ring-gray-200">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-8 py-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Security Settings</h2>
                            <p class="text-sm text-gray-600">Set up login credentials and account status</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-900">Password *</label>
                            <input
                                name="password"
                                type="password"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-900 transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20"
                                required
                                autocomplete="new-password"
                                placeholder="Enter secure password"
                            />
                            <p class="text-xs text-gray-500">Minimum 8 characters required</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-gray-900">Confirm Password *</label>
                            <input
                                name="password_confirmation"
                                type="password"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-900 transition-all focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-between rounded-xl bg-gray-50 p-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Account Status</h3>
                            <p class="text-xs text-gray-600">Enable login access for this teacher</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="sr-only peer" />
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>

                    <div class="mt-4 flex items-center justify-between rounded-xl bg-amber-50 border border-amber-200 p-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Class Teacher</h3>
                            <p class="text-xs text-gray-600">This teacher manages a class and takes attendance. Subject teachers do not get the Attendance menu.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_class_teacher" value="1" @checked(old('is_class_teacher', false)) class="sr-only peer" />
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Class Teacher</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between rounded-3xl bg-white p-8 shadow-xl ring-1 ring-gray-200">
                <a href="{{ route('teachers') }}" class="flex items-center gap-2 rounded-xl border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </a>
                
                <button type="submit" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-all hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Create Teacher Account
                </button>
            </div>
        </form>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="rounded-3xl bg-white p-8 shadow-2xl">
            <div class="text-center">
                <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Creating Teacher Account</h3>
                <p class="text-gray-600">Please wait while we set up the new teacher profile...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('loadingModal').classList.remove('hidden');
    document.getElementById('loadingModal').classList.add('flex');
});

// File upload preview
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const label = document.querySelector('label[for="photo"]');
            label.innerHTML = `
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <img src="${e.target.result}" class="w-16 h-16 rounded-full object-cover mb-3" />
                    <p class="text-sm text-gray-700 font-semibold">${file.name}</p>
                    <p class="text-xs text-gray-400">Click to change photo</p>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
