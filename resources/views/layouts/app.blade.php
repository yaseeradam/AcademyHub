<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<script>document.documentElement.classList.remove('dark'); try { localStorage.removeItem('darkMode') } catch (e) { }</script>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Non-blocking font loading (preconnect + async link instead of @import) --}}
    {{--
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" media="print" onload="this.media='all'" /> --}}

    <style>
        body {
            font-family: 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
    </style>

</head>

<body
    class="h-full bg-gradient-to-br from-amber-50 via-white to-orange-50 text-slate-900 transition-colors duration-300">
    @php
        $hasCbt = true;
        $hasSavingsLoan = true;

        $appMode = (string) config('myacademy.mode', 'full');
        $premiumEnforce = false;
        $cbtLocked = false;
        $showSavingsLoan = true;
    @endphp

    <div id="app" class="min-h-screen">
        <!-- Mobile Sidebar Overlay -->
        <div id="mobileSidebarOverlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>

        <!-- Mobile Sidebar -->
        <aside id="mobileSidebar"
            class="fixed inset-y-0 left-0 z-50 w-80 transform -translate-x-full transition-transform duration-300 lg:hidden">
            <div class="flex h-full flex-col bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4">
                    <div class="flex items-center gap-3">
                        @php($schoolLogo = config('myacademy.school_logo'))
                        <div
                            class="grid h-10 w-10 place-items-center overflow-hidden rounded-xl bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-100">
                            @if ($schoolLogo)
                                <img src="{{ asset('uploads/' . str_replace('\\', '/', $schoolLogo)) }}" alt="School logo"
                                    class="h-full w-full bg-white object-contain p-1.5" />
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M12 3 1 9l11 6 9-4.91V17a2 2 0 0 1-1.1 1.79l-7.4 3.7a2 2 0 0 1-1.8 0l-7.4-3.7A2 2 0 0 1 2 17V9" />
                                    <path d="M12 21V9" />
                                </svg>
                            @endif
                        </div>
                        <div class="text-sm font-semibold tracking-tight text-slate-900">
                            {{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}
                        </div>
                    </div>
                    <button id="closeMobileSidebar" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Navigation Grid -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div class="grid grid-cols-2 gap-3">
                        @php($user = auth()->user())

                        <a href="{{ route('dashboard') }}" class="card-interactive p-4 text-center">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 21h8v-6H3v6z" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">Dashboard</div>
                        </a>

                        <a href="{{ route('profile') }}" class="card-interactive p-4 text-center">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-pink-50 text-pink-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">Profile</div>
                        </a>

                        @if ($user?->role !== 'parent')
                            <a href="{{ route('students.index') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">Students</div>
                            </a>
                        @endif

                        @if ($user?->role === 'parent')
                            <a href="{{ route('students.index') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-pink-50 text-pink-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">My Children</div>
                            </a>
                        @endif

                        @if ($user?->role === 'admin' || $user?->role === 'teacher')
                        @if ($user?->role === 'admin')
                            <a href="{{ route('teachers') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-orange-50 text-orange-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <rect x="2" y="3" width="20" height="13" rx="2"/>
                                        <polyline points="8 21 12 17 16 21"/>
                                        <line x1="7" y1="9" x2="17" y2="9"/>
                                        <line x1="7" y1="13" x2="12" y2="13"/>
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">Teachers</div>
                            </a>

                            <a href="{{ route('parents.index') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-pink-50 text-pink-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                        <polyline points="9 22 9 12 15 12 15 22"/>
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">Parents</div>
                            </a>
                        @endif

                        <a href="{{ route('classes.index') }}" class="card-interactive p-4 text-center">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-slate-50 text-slate-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M3 21h18M5 21V7l7-4 7 4v14M10 21v-6h4v6"/>
                                    <path d="M10 11h.01M14 11h.01M10 15h.01M14 15h.01"/>
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">Classes</div>
                        </a>

                        <a href="{{ route('subjects.index') }}" class="card-interactive p-4 text-center">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-slate-50 text-slate-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">Subjects</div>
                        </a>

                        <a href="{{ route('results.entry') }}" class="card-interactive p-4 text-center">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-green-50 text-green-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">Scores</div>
                        </a>

                        <a href="{{ route('attendance') }}" class="card-interactive p-4 text-center">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">Attendance</div>
                        </a>

                        @php($cbtHref = $cbtLocked ? ($user?->role === 'admin' ? route('marketplace') : route('more-features')) : route('cbt.index'))
                        <a href="{{ $cbtHref }}"
                            class="card-interactive p-4 text-center {{ $cbtLocked ? 'opacity-60' : '' }}">
                            <div
                                class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-violet-50 text-violet-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="4" width="18" height="12" rx="2" />
                                    <path d="M8 20h8" />
                                    <path d="M10 10l2 2 4-4" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700">CBT</div>
                            @if ($cbtLocked)
                                <div class="mt-1 text-[10px] font-black uppercase tracking-wider text-orange-700">Locked
                                </div>
                            @endif
                        </a>
                        @endif

                        @if (in_array($user?->role, ['admin', 'teacher', 'bursar'], true))
                            <a href="{{ route('more-features') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-slate-50 text-slate-700">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">More</div>
                            </a>
                        @endif

                        @if ($user?->role === 'admin')
                            <a href="{{ route('settings.index') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-gray-50 text-gray-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">Settings</div>
                            </a>

                            <a href="{{ route('settings.subscription') }}" class="card-interactive p-4 text-center">
                                <div class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-green-50 text-green-600">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect x="2" y="5" width="20" height="14" rx="2" />
                                        <line x1="2" y1="10" x2="22" y2="10" />
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">Billing</div>
                            </a>
                        @endif

                        @if ($user?->is_super_admin)
                            <a href="{{ route('superadmin.dashboard') }}" class="card-interactive p-4 text-center">
                                <div
                                    class="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-lg bg-slate-800 text-sky-400 border border-slate-700">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="text-xs font-semibold text-gray-700">Dev Dashboard</div>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Mobile Logout Button -->
                <div class="border-t border-gray-100 p-4">
                    <form method="POST" action="{{ route('logout') }}" id="mobileLogoutForm">
                        @csrf
                        <button type="button" onclick="doLogout('mobileLogoutForm')"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 px-4 py-3 text-sm font-bold text-white shadow-md hover:shadow-lg transition-all">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Desktop Sidebar -->
        <aside id="desktopSidebar"
            class="fixed inset-y-0 left-0 hidden w-64 flex-col bg-white shadow-xl transition-all duration-300 lg:flex">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
                @php($schoolLogo = config('myacademy.school_logo'))
                <div class="flex items-center gap-2.5 min-w-0">
                    <div
                        class="grid h-9 w-9 place-items-center overflow-hidden rounded-lg bg-amber-500 text-white shadow-sm flex-shrink-0">
                        @if ($schoolLogo)
                            <img src="{{ asset('uploads/' . str_replace('\\', '/', $schoolLogo)) }}" alt="Logo"
                                class="h-full w-full object-contain p-1 bg-white rounded-md" />
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path
                                    d="M12 3 1 9l11 6 9-4.91V17a2 2 0 0 1-1.1 1.79l-7.4 3.7a2 2 0 0 1-1.8 0l-7.4-3.7A2 2 0 0 1 2 17V9" />
                                <path d="M12 21V9" />
                            </svg>
                        @endif
                    </div>
                    <div class="sidebar-text truncate text-sm font-bold text-slate-900">
                        {{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}
                    </div>
                </div>
                <button id="sidebarToggle"
                    class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors flex-shrink-0">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                @php($user = auth()->user())

                <a href="{{ route('dashboard') }}" wire:navigate
                    class="{{ request()->routeIs('dashboard') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                    <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-amber-600' }}"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                @if ($user?->role !== 'parent')
                    <a href="{{ route('students.index') }}" wire:navigate
                        class="{{ request()->routeIs('students.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('students.*') ? 'text-white' : 'text-blue-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                        <span class="sidebar-text">Students</span>
                    </a>
                @endif

                @if ($user?->role === 'parent')
                    <a href="{{ route('students.index') }}" wire:navigate
                        class="{{ request()->routeIs('students.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('students.*') ? 'text-white' : 'text-pink-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span class="sidebar-text">My Children</span>
                    </a>
                @endif

                @if ($user?->role === 'admin')
                    <a href="{{ route('teachers') }}" wire:navigate
                        class="{{ request()->routeIs('teachers') || request()->routeIs('teachers.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('teachers') || request()->routeIs('teachers.*') ? 'text-white' : 'text-orange-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="2" y="3" width="20" height="13" rx="2"/>
                            <polyline points="8 21 12 17 16 21"/>
                            <line x1="7" y1="9" x2="17" y2="9"/>
                            <line x1="7" y1="13" x2="12" y2="13"/>
                        </svg>
                        <span class="sidebar-text">Teachers</span>
                    </a>

                    <a href="{{ route('parents.index') }}" wire:navigate
                        class="{{ request()->routeIs('parents.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('parents.*') ? 'text-white' : 'text-pink-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span class="sidebar-text">Parents</span>
                    </a>
                @endif

                @if ($user?->role === 'admin' || $user?->role === 'teacher')
                    <a href="{{ route('classes.index') }}" wire:navigate
                        class="{{ request()->routeIs('classes.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('classes.*') ? 'text-white' : 'text-slate-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M10 21v-6h4v6"/>
                            <path d="M10 11h.01M14 11h.01M10 15h.01M14 15h.01"/>
                        </svg>
                        <span class="sidebar-text">Classes</span>
                    </a>

                    <a href="{{ route('subjects.index') }}" wire:navigate
                        class="{{ request()->routeIs('subjects.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('subjects.*') ? 'text-white' : 'text-indigo-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                        </svg>
                        <span class="sidebar-text">Subjects</span>
                    </a>

                    <a href="{{ route('results.entry') }}" wire:navigate
                        class="{{ request()->routeIs('results.entry') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('results.entry') ? 'text-white' : 'text-green-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        <span class="sidebar-text">Score Entry</span>
                    </a>
                @endif

                @if ($user?->role === 'admin')
                    <a href="{{ route('results.broadsheet') }}" wire:navigate
                        class="{{ request()->routeIs('results.broadsheet') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('results.broadsheet') ? 'text-white' : 'text-emerald-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>
                        </svg>
                        <span class="sidebar-text">Broadsheet</span>
                    </a>
                @endif

                @if ($user?->role === 'admin' || $user?->role === 'teacher')
                <a href="{{ route('attendance') }}" wire:navigate
                    class="{{ request()->routeIs('attendance') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                    <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('attendance') ? 'text-white' : 'text-blue-600' }}"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <polyline points="16 11 18 13 22 9" />
                    </svg>
                    <span class="sidebar-text">Attendance</span>
                </a>

                <a href="{{ route('homework.index') }}" wire:navigate
                    class="{{ request()->routeIs('homework.*') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                    <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('homework.*') ? 'text-white' : 'text-purple-600' }}"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <rect x="8" y="2" width="8" height="4" rx="1"/>
                        <line x1="16" y1="11" x2="8" y2="11"/>
                        <line x1="16" y1="15" x2="12" y2="15"/>
                    </svg>
                    <span class="sidebar-text">Homework</span>
                </a>

                <a href="{{ route('messages') }}" wire:navigate
                    class="{{ request()->routeIs('messages') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                    <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('messages') ? 'text-white' : 'text-purple-600' }}"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    <span class="sidebar-text">Messages</span>
                    <span class="ml-auto flex items-center">
                        <livewire:messages.unread-badge />
                    </span>
                </a>

                @php($cbtHref = $cbtLocked ? route('more-features') : route('cbt.index'))
                @php($cbtIsActive = !$cbtLocked && request()->routeIs('cbt.*'))
                <a href="{{ $cbtHref }}" wire:navigate
                    class="{{ $cbtIsActive ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} {{ $cbtLocked ? 'opacity-60' : '' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                    <svg class="h-5 w-5 flex-shrink-0 {{ $cbtIsActive ? 'text-white' : 'text-violet-600' }}"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="3" y="4" width="18" height="12" rx="2" ry="2" />
                        <path d="M8 20h8" />
                        <path d="M10 10l2 2 4-4" />
                    </svg>
                    <span class="sidebar-text">CBT</span>
                    @if ($cbtLocked)
                        <span
                            class="ml-auto rounded-full bg-orange-100 px-2 py-1 text-[10px] font-black uppercase tracking-wider text-orange-800">Locked</span>
                    @endif
                </a>
                @endif

                @if (in_array($user?->role, ['admin', 'teacher', 'bursar'], true))
                    <a href="{{ route('more-features') }}" wire:navigate
                        class="{{ request()->routeIs('more-features') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('more-features') ? 'text-white' : 'text-slate-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                        <span class="sidebar-text">More Features</span>
                    </a>
                @endif

                @if ($user?->role === 'admin')
                    <a href="{{ route('settings.index') }}" wire:navigate
                        class="{{ request()->routeIs('settings.*') && !request()->routeIs('settings.subscription') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('settings.*') && !request()->routeIs('settings.subscription') ? 'text-white' : 'text-gray-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span class="sidebar-text">Settings</span>
                    </a>

                    <a href="{{ route('settings.subscription') }}" wire:navigate
                        class="{{ request()->routeIs('settings.subscription') ? 'bg-amber-50 text-amber-700 border-l-2 border-amber-500' : 'text-slate-700 hover:bg-amber-50' }} mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('settings.subscription') ? 'text-white' : 'text-green-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                        <span class="sidebar-text">Billing</span>
                    </a>
                @endif

                @if ($user?->is_super_admin)
                    <a href="{{ route('superadmin.dashboard') }}" wire:navigate
                        class="{{ request()->routeIs('superadmin.*') ? 'bg-slate-800 text-sky-400 shadow-md' : 'text-slate-700 hover:bg-slate-100' }} mt-4 mb-0.5 group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all border border-slate-200">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs('superadmin.*') ? 'text-sky-400' : 'text-slate-600' }}"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="sidebar-text">Dev Dashboard</span>
                    </a>
                @endif
            </nav>

        </aside>

        <div id="mainContent" class="lg:pl-64 transition-all duration-300">
            <header class="sticky top-0 z-10 border-b border-slate-100 bg-white/80 backdrop-blur-xl shadow-sm">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Menu Button -->
                        <button id="openMobileSidebar"
                            class="rounded-xl p-2.5 text-gray-500 hover:bg-white hover:shadow-md transition-all lg:hidden">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path d="M3 12h18M3 6h18M3 18h18" />
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-black text-slate-900">
                                {{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}
                            </div>
                            <div class="text-xs font-semibold text-slate-600">
                                {{ now()->format('l, F j, Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <livewire:notifications.bell />
                        <a href="{{ route('profile') }}"
                            class="hidden md:flex items-center gap-2.5 rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-gray-200 hover:shadow-md transition-all">
                            @if ($user?->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                    class="h-9 w-9 rounded-lg object-cover ring-2 ring-slate-200" />
                            @else
                                <div
                                    class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 text-white ring-2 ring-slate-200">
                                    <span class="text-sm font-bold">
                                        {{ mb_substr($user?->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            <div class="hidden pr-2 leading-tight sm:block">
                                <div class="text-sm font-bold text-gray-900">{{ $user?->name ?? 'User' }}</div>
                                <div class="text-xs font-semibold text-gray-500">{{ ucfirst($user?->role ?? 'user') }}
                                </div>
                            </div>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden md:block">
                            @csrf
                            <button type="button" onclick="doLogout()"
                                class="inline-flex items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:shadow-lg transition-all">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            @if(isset($subscriptionDueDate) && auth()->user()?->role === 'admin')
                @if($subscriptionIsPastDue && $subscriptionDaysPastDue <= 14)
                    <div class="fixed inset-x-0 bottom-0 z-50 pb-2 sm:pb-5">
                        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
                            <div class="rounded-lg bg-red-600 p-2 shadow-lg sm:p-3">
                                <div class="flex flex-wrap items-center justify-between">
                                    <div class="flex w-0 flex-1 items-center">
                                        <span class="flex rounded-lg bg-red-800 p-2">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </span>
                                        <p class="ml-3 truncate font-medium text-white">
                                            <span class="md:hidden">Subscription Expired! Edit mode locked.</span>
                                            <span class="hidden md:inline">Your system subscription has expired. Edit features are locked. Please renew.</span>
                                        </p>
                                    </div>
                                    <div class="order-3 mt-2 w-full flex-shrink-0 sm:order-2 sm:mt-0 sm:w-auto">
                                        <a href="{{ route('billing.index') }}" class="flex items-center justify-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50">
                                            Pay Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style>
                        #mainContent main input,
                        #mainContent main select,
                        #mainContent main textarea,
                        #mainContent main button:not(.allow-billing) {
                            pointer-events: none !important;
                            opacity: 0.6 !important;
                        }
                    </style>
                @elseif(!$subscriptionIsPastDue && $subscriptionDaysUntilDue <= 7)
                    <div class="fixed inset-x-0 bottom-0 z-50 pb-2 sm:pb-5">
                        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
                            <div class="rounded-lg bg-amber-500 p-2 shadow-lg sm:p-3">
                                <div class="flex flex-wrap items-center justify-between">
                                    <div class="flex w-0 flex-1 items-center">
                                        <span class="flex rounded-lg bg-amber-600 p-2">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                        <p class="ml-3 truncate font-medium text-white">
                                            <span class="md:hidden">Subscription due in {{ $subscriptionDaysUntilDue }} days!</span>
                                            <span class="hidden md:inline">Your system subscription expires in {{ $subscriptionDaysUntilDue }} days. Please renew to avoid interruption.</span>
                                        </p>
                                    </div>
                                    <div class="order-3 mt-2 w-full flex-shrink-0 sm:order-2 sm:mt-0 sm:w-auto">
                                        <a href="{{ route('billing.index') }}" class="flex items-center justify-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-amber-600 shadow-sm hover:bg-amber-50">
                                            Renew Now
                                        </a>
                                    </div>
                                    <div class="order-2 flex-shrink-0 sm:order-3 sm:ml-2">
                                        <button type="button" onclick="this.closest('.fixed').remove()" class="-mr-1 flex p-2 rounded-md hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-white sm:-mr-2">
                                            <span class="sr-only">Dismiss</span>
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <main class="px-6 py-6">
                @yield('content')
                {{ $slot ?? '' }}
            </main>

            <!-- Global Modal -->
            <livewire:global-modal />


    </div>

    @livewireScripts
    @stack('scripts')

    <x-notifications />

    <script>
        // Browser Push Notifications
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            navigator.serviceWorker.register('/sw.js').then(function (registration) {
                console.log('Service Worker registered');
            }).catch(function (error) {
                console.log('Service Worker registration failed:', error);
            });
        }

        function requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        showNotification('Notifications enabled!', 'success');
                    }
                });
            }
        }

        function sendBrowserNotification(title, body, url = '/') {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body: body,
                    icon: '/favicon.ico',
                    badge: '/favicon.ico',
                    vibrate: [200, 100, 200]
                });

                notification.onclick = function () {
                    window.focus();
                    if (url !== '/') window.location.href = url;
                    notification.close();
                };
            }
        }

        // Auto-request permission on first visit
        window.addEventListener('load', function () {
            if ('Notification' in window && Notification.permission === 'default') {
                setTimeout(requestNotificationPermission, 3000);
            }
        });

        // Listen for Livewire events to trigger browser notifications
        document.addEventListener('livewire:init', () => {
            Livewire.on('browser-notification', (event) => {
                const data = event[0] || event;
                sendBrowserNotification(
                    data.title || 'MyAcademy',
                    data.message || 'You have a new notification',
                    data.url || '/'
                );
            });
        });
    </script>

    <script>
        function doLogout(formId = 'logoutForm') {
            // Fetch a fresh CSRF token to prevent 419 Page Expired on logout
            fetch('/csrf-token', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    if (data.token) {
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                        document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.token);
                    }
                })
                .catch(() => {})
                .finally(() => {
                    document.getElementById(formId).submit();
                });
        }
    </script>



    <script>
        const sidebar = document.getElementById('desktopSidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        let isCollapsed = false;

        toggleBtn.addEventListener('click', () => {
            isCollapsed = !isCollapsed;

            if (isCollapsed) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                mainContent.classList.remove('lg:pl-64');
                mainContent.classList.add('lg:pl-20');
                sidebarTexts.forEach(text => text.classList.add('hidden'));
                toggleBtn.querySelector('svg').style.transform = 'rotate(180deg)';
            } else {
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                mainContent.classList.remove('lg:pl-20');
                mainContent.classList.add('lg:pl-64');
                sidebarTexts.forEach(text => text.classList.remove('hidden'));
                toggleBtn.querySelector('svg').style.transform = 'rotate(0deg)';
            }
        });

        // Mobile sidebar
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileOverlay = document.getElementById('mobileSidebarOverlay');
        const openMobileBtn = document.getElementById('openMobileSidebar');
        const closeMobileBtn = document.getElementById('closeMobileSidebar');

        openMobileBtn.addEventListener('click', () => {
            mobileSidebar.classList.remove('-translate-x-full');
            mobileOverlay.classList.remove('hidden');
        });

        closeMobileBtn.addEventListener('click', () => {
            mobileSidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
        });

        mobileOverlay.addEventListener('click', () => {
            mobileSidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
        });
    </script>
    <!-- Global Loading Spinner for Livewire Navigation -->
    <div x-data="{ loading: false }"
         x-on:livewire:navigating.window="loading = true"
         x-on:livewire:navigated.window="loading = false"
         x-show="loading"
         style="display: none;"
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/50 backdrop-blur-sm transition-opacity duration-300">
        <div class="h-14 w-14 animate-spin rounded-full border-4 border-slate-200 border-t-amber-500 shadow-lg"></div>
    </div>
</body>

</html>
