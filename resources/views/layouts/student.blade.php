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
        body { font-family: 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-green-50 via-white to-emerald-50 text-slate-900">

@php
$navItems = [
    ['route' => 'student.dashboard',   'label' => 'Dashboard',   'color' => 'text-green-600',  'icon' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'],
    ['route' => 'student.homework',    'label' => 'Homework',    'color' => 'text-purple-600', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
    ['route' => 'student.results',     'label' => 'Results',     'color' => 'text-blue-600',   'icon' => '<path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
    ['route' => 'student.attendance',  'label' => 'Attendance',  'color' => 'text-teal-600',   'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>'],
    ['route' => 'student.performance', 'label' => 'Performance', 'color' => 'text-orange-600', 'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
];
@endphp

<div id="app" class="min-h-screen">

    <!-- Mobile Overlay -->
    <div id="mobileSidebarOverlay" class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>

    <!-- Mobile Sidebar -->
    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-full transition-transform duration-300 lg:hidden">
        <div class="flex h-full flex-col bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4">
                <div class="flex items-center gap-3">
                    @php($schoolLogo = config('myacademy.school_logo'))
                    <div class="grid h-9 w-9 place-items-center overflow-hidden rounded-lg bg-green-500 text-white shadow-sm flex-shrink-0">
                        @if($schoolLogo)
                            <img src="{{ asset('uploads/' . str_replace('\\', '/', $schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1 bg-white rounded-md" />
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 3 1 9l11 6 9-4.91V17a2 2 0 0 1-1.1 1.79l-7.4 3.7a2 2 0 0 1-1.8 0l-7.4-3.7A2 2 0 0 1 2 17V9"/><path d="M12 21V9"/>
                            </svg>
                        @endif
                    </div>
                    <span class="text-sm font-bold text-slate-900 truncate">{{ config('myacademy.school_name', 'MyAcademy') }}</span>
                </div>
                <button id="closeMobileSidebar" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Student Info -->
            <div class="px-4 py-4 border-b border-gray-100 bg-green-50">
                <div class="text-sm font-bold text-gray-900">{{ session('student_name') }}</div>
                <div class="text-xs text-green-700 font-semibold">{{ session('student_admission') }}</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ session('student_class') }}</div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="{{ request()->routeIs($item['route']) ? 'bg-green-500 text-white shadow-md' : 'text-slate-700 hover:bg-green-50' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                        <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs($item['route']) ? 'text-white' : $item['color'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">{!! $item['icon'] !!}</svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-gray-100 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 px-4 py-3 text-sm font-bold text-white shadow-md hover:shadow-lg transition-all">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Desktop Sidebar -->
    <aside id="desktopSidebar" class="fixed inset-y-0 left-0 hidden w-64 flex-col bg-white shadow-xl transition-all duration-300 lg:flex">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
            @php($schoolLogo = config('myacademy.school_logo'))
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="grid h-9 w-9 place-items-center overflow-hidden rounded-lg bg-green-500 text-white shadow-sm flex-shrink-0">
                    @if($schoolLogo)
                        <img src="{{ asset('uploads/' . str_replace('\\', '/', $schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1 bg-white rounded-md" />
                    @else
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 3 1 9l11 6 9-4.91V17a2 2 0 0 1-1.1 1.79l-7.4 3.7a2 2 0 0 1-1.8 0l-7.4-3.7A2 2 0 0 1 2 17V9"/><path d="M12 21V9"/>
                        </svg>
                    @endif
                </div>
                <div class="sidebar-text truncate text-sm font-bold text-slate-900">
                    {{ config('myacademy.school_name', 'MyAcademy') }}
                </div>
            </div>
            <button id="sidebarToggle" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors flex-shrink-0">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </div>

        <!-- Student Info Block -->
        <div class="sidebar-text px-4 py-3 border-b border-slate-100 bg-green-50">
            <div class="text-xs font-bold text-gray-900 truncate">{{ session('student_name') }}</div>
            <div class="text-xs text-green-700 font-semibold">{{ session('student_admission') }}</div>
            <div class="text-xs text-gray-400 truncate">{{ session('student_class') }}</div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="{{ request()->routeIs($item['route']) ? 'bg-green-500 text-white shadow-md' : 'text-slate-700 hover:bg-green-50' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition-all">
                    <svg class="h-5 w-5 flex-shrink-0 {{ request()->routeIs($item['route']) ? 'text-white' : $item['color'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">{!! $item['icon'] !!}</svg>
                    <span class="sidebar-text">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-slate-100 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:shadow-lg transition-all">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span class="sidebar-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div id="mainContent" class="lg:pl-64 transition-all duration-300">

        <!-- Topbar -->
        <header class="sticky top-0 z-10 border-b border-slate-100 bg-white/80 backdrop-blur-xl shadow-md">
            <div class="h-1.5 bg-gradient-to-r from-green-500 via-emerald-500 to-teal-500"></div>
            <div class="flex h-16 items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button id="openMobileSidebar" class="rounded-xl p-2.5 text-gray-500 hover:bg-white hover:shadow-md transition-all lg:hidden">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                    </button>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-black text-slate-900">Student Portal</div>
                        <div class="text-xs font-semibold text-slate-500">{{ now()->format('l, F j, Y') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2.5 rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-gray-200">
                        <div class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 text-white">
                            <span class="text-sm font-bold">{{ mb_substr(session('student_name', 'S'), 0, 1) }}</span>
                        </div>
                        <div class="hidden pr-2 leading-tight sm:block">
                            <div class="text-sm font-bold text-gray-900">{{ session('student_name') }}</div>
                            <div class="text-xs font-semibold text-gray-500">{{ session('student_admission') }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:shadow-lg transition-all">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="px-6 py-6">
            @if(session('success'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-4 flex items-center gap-3">
                    <svg class="h-5 w-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
@stack('scripts')

<script>
    // Desktop sidebar toggle
    const sidebar = document.getElementById('desktopSidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    let isCollapsed = false;

    toggleBtn.addEventListener('click', () => {
        isCollapsed = !isCollapsed;
        if (isCollapsed) {
            sidebar.classList.replace('w-64', 'w-20');
            mainContent.classList.replace('lg:pl-64', 'lg:pl-20');
            sidebarTexts.forEach(t => t.classList.add('hidden'));
            toggleBtn.querySelector('svg').style.transform = 'rotate(180deg)';
        } else {
            sidebar.classList.replace('w-20', 'w-64');
            mainContent.classList.replace('lg:pl-20', 'lg:pl-64');
            sidebarTexts.forEach(t => t.classList.remove('hidden'));
            toggleBtn.querySelector('svg').style.transform = 'rotate(0deg)';
        }
    });

    // Mobile sidebar
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileSidebarOverlay');
    document.getElementById('openMobileSidebar').addEventListener('click', () => {
        mobileSidebar.classList.remove('-translate-x-full');
        mobileOverlay.classList.remove('hidden');
    });
    const closeMobile = () => {
        mobileSidebar.classList.add('-translate-x-full');
        mobileOverlay.classList.add('hidden');
    };
    document.getElementById('closeMobileSidebar').addEventListener('click', closeMobile);
    mobileOverlay.addEventListener('click', closeMobile);
</script>
</body>
</html>
