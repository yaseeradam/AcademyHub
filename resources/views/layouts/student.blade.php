<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }} — Student Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .nav-icon-box { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sidebar-scroll::-webkit-scrollbar { width:4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background:transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:99px; }
    </style>
</head>
<body class="h-full bg-[#f5f6fa] text-slate-900">

@php
$schoolLogo      = config('myacademy.school_logo');
$schoolName      = config('myacademy.school_name', 'MyAcademy');
$studentName     = session('student_name', 'Student');
$studentAdmission= session('student_admission', '');
$studentInitial  = mb_strtoupper(mb_substr($studentName, 0, 1));

$navItems = [
    ['route'=>'student.dashboard',     'label'=>'Dashboard',     'iconBg'=>'bg-blue-100',   'iconColor'=>'text-blue-500',   'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
    ['route'=>'student.homework',      'label'=>'Homework',      'iconBg'=>'bg-purple-100', 'iconColor'=>'text-purple-500', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>'],
    ['route'=>'student.exams',         'label'=>'Exams',         'iconBg'=>'bg-orange-100', 'iconColor'=>'text-orange-500', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
    ['route'=>'student.results',       'label'=>'Results',       'iconBg'=>'bg-blue-100',   'iconColor'=>'text-blue-500',   'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
    ['route'=>'student.attendance',    'label'=>'Attendance',    'iconBg'=>'bg-teal-100',   'iconColor'=>'text-teal-500',   'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
    ['route'=>'student.performance',   'label'=>'Performance',   'iconBg'=>'bg-cyan-100',   'iconColor'=>'text-cyan-500',   'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
    ['route'=>'student.notifications', 'label'=>'Notifications', 'iconBg'=>'bg-yellow-100', 'iconColor'=>'text-yellow-500', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'],
    ['route'=>'student.profile',       'label'=>'My Profile',    'iconBg'=>'bg-violet-100', 'iconColor'=>'text-violet-500', 'icon'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
];

$pageLabel = 'Student Portal';
@endphp

<div id="app" class="flex min-h-screen" 
     x-data="{ 
        mobileSidebarOpen: false 
     }">

    {{-- Mobile overlay --}}
    <div x-show="mobileSidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"></div>

    {{-- ── Mobile Sidebar ── --}}
    <aside id="mobileSidebar"
           x-show="mobileSidebarOpen"
           x-transition:enter="transition ease-out duration-300 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#f5f6fa] lg:hidden shadow-2xl">

        <div class="flex items-center justify-end px-4 pt-4">
            <button @click="mobileSidebarOpen = false"
                    class="rounded-xl bg-white p-2 text-slate-400 shadow-sm hover:text-slate-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- School branding --}}
        <div class="mx-3 mb-2 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-50 ring-2 ring-emerald-100">
                    @if($schoolLogo)
                        <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1"/>
                    @else
                        <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900">{{ $schoolName }}</div>
                    <div class="mt-0.5 text-[11px] font-semibold text-emerald-500">Smart Learning System</div>
                </div>
            </div>
        </div>

        <div class="px-5 pb-1 pt-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Main Menu</span>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5">
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-2xl px-3 py-2.5 transition-all
                          {{ $active ? 'bg-emerald-500 shadow-lg shadow-emerald-200' : 'hover:bg-white hover:shadow-sm' }}">
                    <div class="nav-icon-box {{ $active ? 'bg-white/20' : $item['iconBg'] }}">
                        <svg class="h-5 w-5 {{ $active ? 'text-white' : $item['iconColor'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $item['icon'] !!}</svg>
                    </div>
                    <span class="flex-1 text-sm font-bold {{ $active ? 'text-white' : 'text-slate-700' }}">{{ $item['label'] }}</span>
                    @if($active)
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-3">
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 rounded-2xl bg-slate-800 px-4 py-3.5 hover:bg-slate-900 transition-all">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-md">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-white">Logout</div>
                        <div class="text-[11px] font-medium text-slate-400">See you later 👋</div>
                    </div>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Desktop Sidebar ── --}}
    <aside class="hidden w-72 flex-shrink-0 flex-col bg-[#f5f6fa] lg:flex">

        {{-- School branding --}}
        <div class="mx-3 mt-4 mb-2 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-50 ring-2 ring-emerald-100">
                    @if($schoolLogo)
                        <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1"/>
                    @else
                        <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900">{{ $schoolName }}</div>
                    <div class="mt-0.5 text-[11px] font-semibold text-emerald-500">Smart Learning System</div>
                </div>
            </div>
        </div>

        <div class="px-5 pb-1 pt-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Main Menu</span>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5">
            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="flex items-center gap-3 rounded-2xl px-3 py-2.5 transition-all
                          {{ $active ? 'bg-emerald-500 shadow-lg shadow-emerald-200' : 'hover:bg-white hover:shadow-sm' }}">
                    <div class="nav-icon-box {{ $active ? 'bg-white/20' : $item['iconBg'] }}">
                        <svg class="h-5 w-5 {{ $active ? 'text-white' : $item['iconColor'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $item['icon'] !!}</svg>
                    </div>
                    <span class="flex-1 text-sm font-bold {{ $active ? 'text-white' : 'text-slate-700' }}">{{ $item['label'] }}</span>
                    @if($active)
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-3">
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 rounded-2xl bg-slate-800 px-4 py-3.5 hover:bg-slate-900 transition-all">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-md">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-white">Logout</div>
                        <div class="text-[11px] font-medium text-slate-400">See you later 👋</div>
                    </div>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main area ── --}}
    <div class="flex flex-1 min-w-0 flex-col">

        {{-- Header --}}
        <header class="sticky top-0 z-30 flex h-[72px] items-center justify-between gap-4 border-b border-slate-200/60 bg-white px-6 shadow-sm">

            {{-- Left --}}
            <div class="flex items-center gap-3 overflow-hidden">
                <button @click="mobileSidebarOpen = true"
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:hidden transition-colors">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="min-w-0 overflow-hidden">
                    <h1 class="truncate text-base font-extrabold tracking-tight text-slate-900 sm:text-lg">{{ $pageLabel }}</h1>
                    <p class="truncate text-[10px] font-medium text-slate-400 sm:text-xs">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>

            {{-- Right --}}
            <div class="flex items-center gap-3">

                {{-- Bell --}}
                <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-colors">
                    <livewire:student.notification-bell />
                </div>

                {{-- Student chip --}}
                <div class="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-4 shadow-sm">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm">
                        <span class="text-sm font-extrabold leading-none">{{ $studentInitial }}</span>
                    </div>
                    <div class="hidden leading-tight sm:block">
                        <div class="text-sm font-bold text-slate-800">{{ $studentName }}</div>
                        <div class="text-[11px] font-semibold text-slate-400">{{ $studentAdmission }}</div>
                    </div>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('student.logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition-all">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>

            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-6 py-6">
            @if(session('success'))
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 p-4">
                    <svg class="h-5 w-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-semibold text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>

</div>

@livewireScripts
@stack('scripts')

{{-- Mobile sidebar JS is handled in app.js --}}

{{-- Livewire navigation loading spinner --}}
<div x-data="{ loading: false }"
     x-on:livewire:navigating.window="loading = true"
     x-on:livewire:navigated.window="loading = false"
     x-show="loading"
     style="display:none"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/50 backdrop-blur-sm">
    <div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-emerald-500 shadow-lg"></div>
</div>

</body>
</html>
