<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<script>document.documentElement.classList.remove('dark'); try { localStorage.removeItem('darkMode') } catch(e){}</script>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .nav-icon-box { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sidebar-scroll::-webkit-scrollbar { width:6px; }
        .sidebar-scroll::-webkit-scrollbar-track { background:rgba(148, 163, 184, 0.1); border-radius:99px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:99px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background:#94a3b8; }
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 rgba(148, 163, 184, 0.1); }
    </style>
</head>
<body class="h-full bg-[#f5f6fa] text-slate-900">

@php
$hasCbt            = true;
$appMode           = (string) config('myacademy.mode', 'full');
$cbtLocked         = false;
$user              = auth()->user();
$schoolLogo        = config('myacademy.school_logo');
$schoolName        = config('myacademy.school_name', config('app.name', 'MyAcademy'));
$userInitial       = mb_strtoupper(mb_substr($user?->name ?? 'U', 0, 1));

// Role-aware accent colour (used for active state)
$accent = match($user?->role) {
    'admin'   => 'violet',
    'bursar'  => 'emerald',
    'teacher' => 'sky',
    'parent'  => 'pink',
    default   => 'violet',
};
$activeBg  = "bg-{$accent}-500";
$activeShadow = "shadow-{$accent}-200";
@endphp

<div id="app" class="flex h-screen overflow-hidden" x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">

    {{-- Mobile overlay --}}
    <div id="mobileSidebarOverlay" class="fixed inset-0 z-40 hidden bg-black/40 backdrop-blur-sm lg:hidden"></div>

    {{-- ══════════════════════════════════════
         MOBILE SIDEBAR
    ══════════════════════════════════════ --}}
    <aside id="mobileSidebar"
           class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-[#f5f6fa] transition-transform duration-300 lg:hidden">

        <div class="flex items-center justify-end px-4 pt-4">
            <button id="closeMobileSidebar"
                    class="rounded-xl bg-white p-2 text-slate-400 shadow-sm hover:text-slate-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Branding --}}
        <div class="mx-3 mb-2 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-50 ring-2 ring-violet-100">
                    @if($schoolLogo)
                        <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1"/>
                    @else
                        <svg class="h-7 w-7 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900">{{ $schoolName }}</div>
                    <div class="mt-0.5 text-[11px] font-semibold text-violet-500">Smart Learning System</div>
                </div>
            </div>
        </div>

        <div class="px-5 pb-1 pt-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Main Menu</span>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5 min-h-0">
            @include('layouts.partials.app-nav', ['mobile' => true])
        </nav>

        <div class="p-3">
            <form method="POST" action="{{ route('logout') }}" id="mobileLogoutForm">
                @csrf
                <button type="button" onclick="doLogout('mobileLogoutForm')"
                        class="w-full flex items-center gap-3 rounded-2xl bg-slate-800 px-4 py-3.5 hover:bg-slate-900 transition-all">
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

    {{-- ══════════════════════════════════════
         DESKTOP SIDEBAR
    ══════════════════════════════════════ --}}
    <aside x-bind:class="sidebarCollapsed ? 'w-20' : 'w-72'" class="hidden flex-shrink-0 flex-col bg-[#f5f6fa] lg:flex transition-[width] duration-300 ease-in-out border-r border-slate-200/50">

        {{-- Branding --}}
        <div class="mx-3 mt-4 mb-2 rounded-2xl bg-white shadow-sm transition-all duration-300" x-bind:class="sidebarCollapsed ? 'p-2 mx-2' : 'p-4'">
            <div class="flex items-center" x-bind:class="sidebarCollapsed ? 'justify-center' : 'gap-3'">
                <div class="flex flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-50 ring-2 ring-violet-100 transition-all duration-300" x-bind:class="sidebarCollapsed ? 'h-10 w-10' : 'h-12 w-12'">
                    @if($schoolLogo)
                        <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1"/>
                    @else
                        <svg class="text-violet-500 transition-all duration-300" x-bind:class="sidebarCollapsed ? 'h-5 w-5' : 'h-7 w-7'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                        </svg>
                    @endif
                </div>
                <div class="min-w-0" x-show="!sidebarCollapsed" x-transition.opacity>
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900">{{ $schoolName }}</div>
                    <div class="mt-0.5 text-[11px] font-semibold text-violet-500">Smart Learning System</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5 min-h-0 mt-2">
            @include('layouts.partials.app-nav', ['mobile' => false])
        </nav>

        <div class="p-3 transition-all duration-300" x-bind:class="sidebarCollapsed ? 'px-2' : 'p-3'">
            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <button type="button" onclick="doLogout('logoutForm')" title="Logout"
                        class="w-full flex items-center rounded-2xl bg-slate-800 hover:bg-slate-900 transition-all duration-300 shadow-sm" x-bind:class="sidebarCollapsed ? 'p-2 justify-center' : 'px-4 py-3.5 gap-3'">
                    <div class="flex flex-shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-md transition-all duration-300" x-bind:class="sidebarCollapsed ? 'h-10 w-10' : 'h-9 w-9'">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" x-transition.opacity class="text-left flex-1 whitespace-nowrap overflow-hidden">
                        <div class="text-sm font-extrabold text-white">Logout</div>
                        <div class="text-[11px] font-medium text-slate-400">See you later 👋</div>
                    </div>
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════════════════════════
         MAIN AREA
    ══════════════════════════════════════ --}}
    <div class="flex flex-1 min-w-0 flex-col">

        {{-- Header --}}
        <header class="sticky top-0 z-10 flex h-[72px] items-center justify-between gap-4 border-b border-slate-200/60 bg-white px-6 shadow-sm">

            <div class="flex items-center gap-4">
                <button id="openMobileSidebar"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:hidden">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <button @click="sidebarCollapsed = !sidebarCollapsed" title="Toggle Menu"
                        class="hidden h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:flex transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight text-slate-900">{{ $schoolName }}</h1>
                    <p class="text-xs font-medium text-slate-400">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">

                {{-- Bell --}}
                <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-colors">
                    <livewire:notifications.bell />
                </div>

                {{-- User chip --}}
                <a href="{{ route('profile') }}"
                   class="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-4 shadow-sm hover:shadow-md transition-all">
                    @if($user?->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                             class="h-8 w-8 flex-shrink-0 rounded-full object-cover"/>
                    @else
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-500 text-white shadow-sm">
                            <span class="text-sm font-extrabold leading-none">{{ $userInitial }}</span>
                        </div>
                    @endif
                    <div class="hidden leading-tight sm:block">
                        <div class="text-sm font-bold text-slate-800">{{ $user?->name ?? 'User' }}</div>
                        <div class="text-[11px] font-semibold text-slate-400">{{ ucfirst($user?->role ?? 'user') }}</div>
                    </div>
                </a>

                {{-- Logout --}}
                <button type="button" onclick="doLogout('logoutForm')"
                        class="flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition-all">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">Logout</span>
                </button>

            </div>
        </header>

        {{-- Subscription banners --}}
        @if(isset($subscriptionDueDate) && $user?->role === 'admin')
            @if($subscriptionIsPastDue && $subscriptionDaysPastDue <= 14)
                <div class="fixed inset-x-0 bottom-0 z-50 p-3">
                    <div class="mx-auto max-w-4xl rounded-2xl bg-red-600 px-5 py-3 shadow-xl flex items-center justify-between gap-4">
                        <p class="text-sm font-bold text-white">Subscription expired — edit features are locked. <a href="{{ route('billing.index') }}" class="underline">Renew now</a></p>
                    </div>
                </div>
                <style>#mainContent main input,#mainContent main select,#mainContent main textarea,#mainContent main button:not(.allow-billing){pointer-events:none!important;opacity:.6!important}</style>
            @elseif(!$subscriptionIsPastDue && $subscriptionDaysUntilDue <= 7)
                <div class="fixed inset-x-0 bottom-0 z-50 p-3">
                    <div class="mx-auto max-w-4xl rounded-2xl bg-amber-500 px-5 py-3 shadow-xl flex items-center justify-between gap-4">
                        <p class="text-sm font-bold text-white">Subscription expires in {{ $subscriptionDaysUntilDue }} days. <a href="{{ route('billing.index') }}" class="underline">Renew now</a></p>
                        <button onclick="this.closest('.fixed').remove()" class="text-white/80 hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-6 py-6">
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        <livewire:global-modal />
    </div>

</div>

@livewireScripts
@stack('scripts')
<x-notifications />

<script>
    // CSRF-safe logout
    function doLogout(formId = 'logoutForm') {
        fetch('/csrf-token', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                if (d.token) {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', d.token);
                    document.querySelectorAll('input[name="_token"]').forEach(el => el.value = d.token);
                }
            })
            .catch(() => {})
            .finally(() => document.getElementById(formId).submit());
    }

    // Mobile sidebar
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileSidebarOverlay');
    document.getElementById('openMobileSidebar')?.addEventListener('click', () => {
        mobileSidebar.classList.remove('-translate-x-full');
        mobileOverlay.classList.remove('hidden');
    });
    const closeMobile = () => {
        mobileSidebar.classList.add('-translate-x-full');
        mobileOverlay.classList.add('hidden');
    };
    document.getElementById('closeMobileSidebar')?.addEventListener('click', closeMobile);
    mobileOverlay?.addEventListener('click', closeMobile);

    // Push notifications
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
    function sendBrowserNotification(title, body, url = '/') {
        if ('Notification' in window && Notification.permission === 'granted') {
            const n = new Notification(title, { body, icon: '/favicon.ico' });
            n.onclick = () => { window.focus(); if (url !== '/') window.location.href = url; n.close(); };
        }
    }
    window.addEventListener('load', () => {
        if ('Notification' in window && Notification.permission === 'default') {
            setTimeout(() => Notification.requestPermission(), 3000);
        }
    });
    document.addEventListener('livewire:init', () => {
        Livewire.on('browser-notification', (event) => {
            const d = event[0] || event;
            sendBrowserNotification(d.title || 'MyAcademy', d.message || 'New notification', d.url || '/');
        });
    });
</script>

{{-- Livewire navigation loading spinner --}}
<div x-data="{ loading: false }"
     x-on:livewire:navigating.window="loading = true"
     x-on:livewire:navigated.window="loading = false"
     x-show="loading" style="display:none"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/50 backdrop-blur-sm">
    <div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-violet-500 shadow-lg"></div>
</div>

</body>
</html>
