<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="{{ config('academyhub.school_name', 'AcademyHub') }}" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="theme-color" content="#0f172a" />
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)" />
    <link rel="manifest" href="/manifest.json">
    @php
        $activeFavicon = null;
        if (config('academyhub.school_logo')) {
            $activeFavicon = asset('uploads/' . str_replace('\\', '/', config('academyhub.school_logo')));
        } elseif (app()->bound('currentTenant') && app('currentTenant') && !empty(app('currentTenant')->logo)) {
            $activeFavicon = asset('uploads/' . str_replace('\\', '/', app('currentTenant')->logo));
        } elseif (auth()->check() && auth()->user()->tenant && !empty(auth()->user()->tenant->logo)) {
            $activeFavicon = asset('uploads/' . str_replace('\\', '/', auth()->user()->tenant->logo));
        } else {
            $activeFavicon = asset('icon.png');
        }
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ $activeFavicon }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $activeFavicon }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $activeFavicon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $activeFavicon }}">
    <title>{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }} — Student Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .nav-icon-box { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        aside.w-20 .nav-icon-box { margin-left: auto; margin-right: auto; }
        .sidebar-scroll::-webkit-scrollbar { width:6px; }
        .sidebar-scroll::-webkit-scrollbar-track { background:rgba(148, 163, 184, 0.1); border-radius:99px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:99px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 rgba(148, 163, 184, 0.1); }
    </style>
</head>
<body class="h-full bg-[#f5f6fa] text-slate-900">

{{-- Native PWA Boot Splash Screen --}}
<x-pwa-splash-screen />

{{-- Native PWA Offline Indicator --}}
<div wire:offline class="fixed top-3 left-1/2 -translate-x-1/2 z-[99999] pointer-events-none transition-all duration-300">
    <div class="bg-amber-500/95 backdrop-blur-md text-slate-900 border border-amber-600/30 rounded-full px-4 py-1.5 shadow-xl flex items-center gap-2 text-xs font-bold animate-pulse">
        <span class="h-2 w-2 rounded-full bg-amber-950"></span>
        <span>Offline Mode — Browsing cached data</span>
    </div>
</div>

@php
    $schoolLogo       = config('academyhub.school_logo');
    $schoolName       = config('academyhub.school_name', 'AcademyHub');
    $studentName      = session('student_name', 'Student');
    $studentAdmission = session('student_admission', '');
    $studentInitial   = mb_strtoupper(mb_substr($studentName, 0, 1));

    $navItems = [
        ['route'=>'student.dashboard',     'label'=>'Dashboard',     'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
        ['route'=>'student.homework',      'label'=>'Homework',      'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>'],
        ['route'=>'student.e-learning',    'label'=>'E-Learning',    'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
        ['route'=>'student.exams',         'label'=>'Exams',         'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
        ['route'=>'student.results',       'label'=>'Results',       'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
        ['route'=>'student.attendance',    'label'=>'Attendance',    'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
        ['route'=>'student.performance',   'label'=>'Performance',   'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
        ['route'=>'student.hall-of-fame',  'label'=>'Hall of Fame',  'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'],
        ['route'=>'student.notifications', 'label'=>'Notifications', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'],
        ['route'=>'student.profile',       'label'=>'My Profile',    'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
    ];

    $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;
    $isHomeworkInstalled = $tenant && $tenant->activeMarketplaceComponents()->where('slug', 'homework')->exists();
    if (! $isHomeworkInstalled) {
        $navItems = array_values(array_filter($navItems, fn($item) => $item['route'] !== 'student.homework'));
    }
    $isELearningInstalled = $tenant && $tenant->activeMarketplaceComponents()->where('slug', 'e-learning')->exists();
    if (! $isELearningInstalled) {
        $navItems = array_values(array_filter($navItems, fn($item) => $item['route'] !== 'student.e-learning'));
    }
@endphp

<div id="app" class="flex h-screen overflow-hidden" 
     x-data="{ 
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        mobileSidebarOpen: false
     }" 
     x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">

    {{-- Mobile overlay --}}
    <div x-show="mobileSidebarOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden" style="display:none;"></div>

    {{-- Mobile Sidebar (drawer) --}}
    <aside id="mobileSidebar"
           x-show="mobileSidebarOpen"
           x-transition:enter="transition ease-out duration-300 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 flex w-80 max-w-[85vw] flex-col bg-[#f5f6fa] rounded-r-3xl lg:hidden shadow-2xl pt-safe pb-safe overscroll-contain select-none"
           style="display:none;">
        <div class="flex items-center justify-between px-4 pt-3 pb-1">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Student App</span>
            </div>
            <button @click="mobileSidebarOpen = false"
                    aria-label="Close menu"
                    class="rounded-xl bg-white p-2 text-slate-400 shadow-sm hover:text-slate-600 tap-bounce focus:outline-none">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @include('layouts.partials.student-sidebar-inner', ['mobile' => true])
    </aside>

    {{-- Desktop Fixed Sidebar --}}
    <aside x-bind:class="sidebarCollapsed ? 'w-20' : 'w-72'" 
           class="hidden flex-shrink-0 flex-col bg-[#f5f6fa] lg:flex transition-[width] duration-300 ease-in-out border-r border-slate-200/50">
        @include('layouts.partials.student-sidebar-inner', ['mobile' => false])
    </aside>

    {{-- Main Area --}}
    <div class="flex flex-1 min-w-0 flex-col">

        {{-- Top Header --}}
        <header class="sticky top-0 z-30 flex h-14 sm:h-16 lg:h-[72px] items-center justify-between gap-2 sm:gap-4 border-b border-slate-200/60 bg-white/95 backdrop-blur-md px-3 sm:px-6 shadow-sm pt-safe select-none">

            {{-- Left: Logo trigger + page label --}}
            <div class="flex items-center gap-2 sm:gap-3 overflow-hidden">
                <div class="lg:hidden flex-shrink-0 flex items-center">
                    <button type="button" @click="mobileSidebarOpen = true" aria-label="Open App Menu" class="tap-bounce flex items-center focus:outline-none">
                        <div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl bg-violet-50 ring-1 ring-violet-200/80 shadow-sm">
                            @if($schoolLogo)
                                <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-0.5" />
                            @else
                                <img src="{{ asset('full.png') }}" alt="Logo" class="h-full w-full object-contain p-0.5" />
                            @endif
                        </div>
                    </button>
                </div>
                <button @click="sidebarCollapsed = !sidebarCollapsed" title="Toggle Menu"
                        class="hidden h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:flex transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="min-w-0 overflow-hidden text-left">
                    <h1 class="truncate text-sm sm:text-lg font-extrabold tracking-tight text-slate-900 leading-tight">{{ $schoolName }}</h1>
                    <div class="flex items-center gap-1.5">
                        <span class="lg:hidden inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-green-50 text-green-700 capitalize">
                            Student
                        </span>
                        <p class="hidden sm:block truncate text-[10px] font-medium text-slate-400 sm:text-xs">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Right: bell + student chip + logout --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                <div class="relative flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-colors tap-bounce shadow-sm">
                    <livewire:student.notification-bell />
                </div>
                <div class="flex items-center gap-1 sm:gap-2.5 rounded-full border border-slate-200 bg-white p-0.5 sm:py-1 sm:pl-1 sm:pr-4 shadow-sm hover:shadow-md transition-all tap-bounce">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-green-600 text-white shadow-sm font-extrabold">
                        <span class="text-xs sm:text-sm leading-none">{{ $studentInitial }}</span>
                    </div>
                    <div class="hidden leading-tight sm:block text-left">
                        <div class="text-xs font-bold text-slate-800">{{ $studentName }}</div>
                        <div class="text-[10px] font-semibold text-slate-400">{{ $studentAdmission }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('student.logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-bold text-white hover:bg-slate-700 transition-all shadow-sm">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5 pb-24 lg:pb-6 min-h-0 overscroll-contain">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3">
                    <svg class="h-4 w-4 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs font-semibold text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 shadow-sm">
                    <svg class="h-5 w-5 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs font-semibold text-amber-800">{{ session('warning') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 shadow-sm">
                    <svg class="h-5 w-5 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs font-semibold text-red-800">{{ session('error') }}</p>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>

    {{-- Native Bottom Tab Bar for Student Mobile --}}
    @include('layouts.partials.student-mobile-bottom-nav')

</div>

@livewireScripts
@stack('scripts')

{{-- Nav loading indicator --}}
@php $studentSchoolLogo = config('academyhub.school_logo'); @endphp
<div x-data="{ loading: false }"
     x-on:livewire:navigating.window="loading = true"
     x-on:livewire:navigated.window="loading = false"
     x-show="loading" style="display:none"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-slate-50/90 via-white/95 to-violet-50/90 backdrop-blur-md">
    <div class="flex flex-col items-center gap-5">
        <div class="relative flex items-center justify-center">
            <div class="absolute h-20 w-20 rounded-2xl bg-violet-400/20 animate-ping" style="animation-duration:1.8s;"></div>
            <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-xl ring-1 ring-violet-100">
                @if($studentSchoolLogo)
                    <img src="{{ asset('uploads/'.str_replace('\\','/',$studentSchoolLogo)) }}" alt="Logo" class="h-10 w-10 object-contain"/>
                @else
                    <img src="{{ asset('full.png') }}" alt="AcademyHub" class="h-10 w-10 object-contain"/>
                @endif
            </div>
        </div>
        <div class="w-40 h-1 rounded-full bg-slate-200 overflow-hidden">
            <div class="h-full w-1/2 rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 animate-[loadSlide_1.2s_ease-in-out_infinite]"></div>
        </div>
        <span class="text-xs font-bold text-slate-400 tracking-widest uppercase">Loading…</span>
    </div>
</div>
<style>
    @keyframes loadSlide {
        0%   { transform: translateX(-100%); }
        100% { transform: translateX(300%); }
    }
</style>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js?v=3').catch(function() {});
        });
    }
</script>
<x-pwa-install-prompt />

</body>
</html>
