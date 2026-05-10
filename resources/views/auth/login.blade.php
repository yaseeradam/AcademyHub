<x-layouts.guest>
    <div class="min-h-screen flex items-center justify-start relative overflow-hidden p-4 sm:p-6 lg:p-8">
        <!-- Dynamic Background Images -->
        <div class="absolute inset-0 z-0">
            <!-- Staff Background -->
            <div id="staff-bg" class="login-bg absolute inset-0" style="opacity:1">
                <img src="{{ asset('bgs/admin.png') }}" alt="Staff background" class="login-bg-img w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-br from-blue-900/20 via-indigo-900/10 to-slate-900/20"></div>
            </div>
            <!-- Parent Background -->
            <div id="parent-bg" class="login-bg absolute inset-0" style="opacity:0">
                <img src="{{ asset('bgs/parent.png') }}" alt="Parent background" class="login-bg-img w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-br from-pink-900/20 via-rose-900/10 to-slate-900/20"></div>
            </div>
            <!-- Student Background -->
            <div id="student-bg" class="login-bg absolute inset-0" style="opacity:0">
                <img src="{{ asset('bgs/student.png') }}" alt="Student background" class="login-bg-img w-full h-full object-cover" />
                <div class="absolute inset-0 bg-gradient-to-br from-green-900/20 via-emerald-900/10 to-slate-900/20"></div>
            </div>
        </div>

        <div class="w-full max-w-md relative z-10">
            <!-- Login Card -->
            <div class="relative">
                <!-- Card -->
                <div class="relative rounded-3xl bg-white/10 backdrop-blur-2xl shadow-2xl border border-white/20 overflow-hidden">
                    <!-- Header -->
                    <div class="p-8 text-center border-b border-white/10">
                        <h1 class="text-2xl font-black text-white mb-1">
                            {{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}
                        </h1>
                        <p class="text-sm text-white/70">School Management System</p>
                        <div class="mt-3">
                            <h3 id="login-type-title" class="text-lg font-bold text-white">Staff Login</h3>
                        </div>
                    </div>

                    <!-- Session expired / status notice -->
                    @if(session('status'))
                        <div class="mx-8 mt-4 flex items-center gap-3 rounded-xl bg-amber-500/20 border border-amber-400/30 px-4 py-3 backdrop-blur-sm">
                            <svg class="h-5 w-5 flex-shrink-0 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <p class="text-sm font-semibold text-amber-200">{{ session('status') }}</p>
                        </div>
                    @endif

                    <!-- Login Type Tabs -->
                    <div class="px-8 pt-6">
                        <div class="flex rounded-xl bg-white/5 p-1 backdrop-blur-sm border border-white/10">
                            <button 
                                type="button" 
                                id="staff-tab" 
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-bold text-white transition-all duration-200 bg-white/20 shadow-sm"
                                onclick="switchLoginType('staff')"
                            >
                                <div class="flex items-center justify-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>School Staff</span>
                                </div>
                            </button>
                            <button 
                                type="button" 
                                id="parent-tab" 
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-bold text-white/70 transition-all duration-200 hover:text-white hover:bg-white/10"
                                onclick="switchLoginType('parent')"
                            >
                                <div class="flex items-center justify-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span>Parent</span>
                                </div>
                            </button>
                            <button 
                                type="button" 
                                id="student-tab" 
                                class="flex-1 rounded-lg px-3 py-2 text-xs font-bold text-white/70 transition-all duration-200 hover:text-white hover:bg-white/10"
                                onclick="switchLoginType('student')"
                            >
                                <div class="flex items-center justify-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    </svg>
                                    <span>Student</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Login Form Content -->
                    <div class="p-8 pt-6">
                        <!-- Staff Login Form -->
                        <div id="staff-form" class="login-form">
                            <form class="space-y-5" method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="login_type" value="staff">

                                <div>
                                    <label class="text-sm font-semibold text-white/90 mb-2 block" for="staff-email">Email Address</label>
                                    <input
                                        id="staff-email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        autocomplete="username"
                                        required
                                        class="w-full rounded-xl border-0 bg-white/10 backdrop-blur-sm px-4 py-3 text-sm text-white placeholder:text-white/50 ring-1 ring-white/20 focus:ring-2 focus:ring-blue-400 transition"
                                        placeholder="admin@myacademy.local"
                                    />
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-white/90 mb-2 block" for="staff-password">Password</label>
                                    <input
                                        id="staff-password"
                                        name="password"
                                        type="password"
                                        autocomplete="current-password"
                                        required
                                        class="w-full rounded-xl border-0 bg-white/10 backdrop-blur-sm px-4 py-3 text-sm text-white placeholder:text-white/50 ring-1 ring-white/20 focus:ring-2 focus:ring-blue-400 transition"
                                        placeholder="••••••••"
                                    />
                                </div>


                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200"
                                >
                                    <span class="btn-text">Sign in to Dashboard</span>
                                    <span class="btn-loading hidden">Signing in...</span>
                                </button>
                            </form>
                        </div>

                        <!-- Parent Login Form -->
                        <div id="parent-form" class="login-form hidden">
                            <form class="space-y-5" method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="login_type" value="parent">

                                <div>
                                    <label class="text-sm font-semibold text-white/90 mb-2 block" for="parent-email">Email Address</label>
                                    <input
                                        id="parent-email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        autocomplete="username"
                                        required
                                        class="w-full rounded-xl border-0 bg-white/10 backdrop-blur-sm px-4 py-3 text-sm text-white placeholder:text-white/50 ring-1 ring-white/20 focus:ring-2 focus:ring-pink-400 transition"
                                        placeholder="parent@email.com"
                                    />
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-white/90 mb-2 block" for="parent-password">Password</label>
                                    <input
                                        id="parent-password"
                                        name="password"
                                        type="password"
                                        autocomplete="current-password"
                                        required
                                        class="w-full rounded-xl border-0 bg-white/10 backdrop-blur-sm px-4 py-3 text-sm text-white placeholder:text-white/50 ring-1 ring-white/20 focus:ring-2 focus:ring-pink-400 transition"
                                        placeholder="••••••••"
                                    />
                                </div>


                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg hover:shadow-xl hover:from-pink-600 hover:to-rose-700 transition-all duration-200"
                                >
                                    <span class="btn-text">Access Parent Portal</span>
                                    <span class="btn-loading hidden">Signing in...</span>
                                </button>
                            </form>
                        </div>

                        <!-- Student Login Form -->
                        <div id="student-form" class="login-form hidden">
                            <form class="space-y-5" method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="login_type" value="student">

                                <div>
                                    <label class="text-sm font-semibold text-white/90 mb-2 block" for="student-admission">Admission Number</label>
                                    <input
                                        id="student-admission"
                                        name="admission_number"
                                        type="text"
                                        value="{{ old('admission_number') }}"
                                        autocomplete="username"
                                        required
                                        class="w-full rounded-xl border-0 bg-white/10 backdrop-blur-sm px-4 py-3 text-sm text-white placeholder:text-white/50 ring-1 ring-white/20 focus:ring-2 focus:ring-green-400 transition"
                                        placeholder="STU20240001"
                                    />
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-white/90 mb-2 block" for="student-password">Password</label>
                                    <input
                                        id="student-password"
                                        name="password"
                                        type="password"
                                        autocomplete="current-password"
                                        required
                                        class="w-full rounded-xl border-0 bg-white/10 backdrop-blur-sm px-4 py-3 text-sm text-white placeholder:text-white/50 ring-1 ring-white/20 focus:ring-2 focus:ring-green-400 transition"
                                        placeholder="••••••••"
                                    />
                                </div>


                                <button
                                    type="submit"
                                    class="w-full rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg hover:shadow-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200"
                                >
                                    <span class="btn-text">Access Student Portal</span>
                                    <span class="btn-loading hidden">Signing in...</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="px-8 pb-6">
                            <div class="rounded-xl bg-green-500/20 border border-green-400/30 p-4 flex items-start gap-3">
                                <svg class="h-5 w-5 text-green-300 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-green-200">{{ session('success') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>


        </div>
    </div>

    <!-- Error Modal -->
    @if ($errors->any())
        <div id="error-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-fade-in">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Login Failed</h3>
                        <p class="text-sm text-white/80">Please check the following errors</p>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="px-6 py-5 space-y-3">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-start gap-3 text-gray-700">
                            <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <p class="text-sm">{{ $error }}</p>
                        </div>
                    @endforeach
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 flex justify-end">
                    <button 
                        onclick="closeErrorModal()" 
                        class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white text-sm font-semibold rounded-lg hover:from-red-600 hover:to-rose-700 transition-all duration-200 shadow-md hover:shadow-lg"
                    >
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: scale(0.95) translateY(-10px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }

        @keyframes form-in {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-form-animate { animation: form-in 0.35s ease-out forwards; }

        /* Smooth crossfade */
        .login-bg {
            transition: opacity 0.9s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Ken Burns on active bg image */
        @keyframes kenburns {
            0%   { transform: scale(1)    translate(0, 0); }
            50%  { transform: scale(1.1)  translate(-1.5%, -1%); }
            100% { transform: scale(1)    translate(0, 0); }
        }
        .login-bg-img {
            transform-origin: center center;
            will-change: transform;
        }
        .login-bg.is-active .login-bg-img {
            animation: kenburns 14s ease-in-out infinite;
        }
    </style>

    <script>
        function closeErrorModal() {
            const modal = document.getElementById('error-modal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => modal.remove(), 200);
            }
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeErrorModal();
            }
        });
        
        // Close modal on backdrop click
        document.getElementById('error-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeErrorModal();
            }
        });
    </script>

    <script>
        function switchLoginType(type) {
            // Hide all forms
            document.querySelectorAll('.login-form').forEach(f => f.classList.add('hidden'));

            // Fade out all backgrounds & stop Ken Burns
            document.querySelectorAll('.login-bg').forEach(bg => {
                bg.style.opacity = '0';
                bg.classList.remove('is-active');
                const img = bg.querySelector('.login-bg-img');
                if (img) img.style.animation = 'none';
            });

            // Reset all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('bg-white/20', 'shadow-sm', 'text-white');
                tab.classList.add('text-white/70');
            });

            // Show form with animation
            const activeForm = document.getElementById(type + '-form');
            activeForm.classList.remove('hidden');
            activeForm.classList.remove('login-form-animate');
            void activeForm.offsetWidth;
            activeForm.classList.add('login-form-animate');

            // Fade in background & restart Ken Burns
            const activeBg = document.getElementById(type + '-bg');
            activeBg.style.opacity = '1';
            const img = activeBg.querySelector('.login-bg-img');
            img.style.animation = 'none';
            void img.offsetWidth; // force reflow to restart keyframe
            img.style.animation = '';
            activeBg.classList.add('is-active');

            // Activate tab
            const activeTab = document.getElementById(type + '-tab');
            activeTab.classList.add('bg-white/20', 'shadow-sm', 'text-white');
            activeTab.classList.remove('text-white/70');

            // Update title
            const titles = { staff: 'Staff Login', parent: 'Parent Portal', student: 'Student Portal' };
            document.getElementById('login-type-title').textContent = titles[type];
        }

        document.addEventListener('DOMContentLoaded', () => switchLoginType('staff'));

        document.querySelectorAll('.login-form form').forEach(form => {
            form.addEventListener('submit', function () {
                const btn = this.querySelector('button[type="submit"]');
                if (!btn) return;
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('hidden');
                btn.querySelector('.btn-loading').classList.remove('hidden');
            });
        });
    </script>
</x-layouts.guest>
