<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<script>document.documentElement.classList.remove('dark'); try { localStorage.removeItem('darkMode') } catch(e){}</script>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <script>
        window.onerror = function(message, source, line, col, error) {
            fetch('/log-error', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    message: message,
                    source: source,
                    line: line,
                    col: col,
                    stack: error ? error.stack : '',
                    url: window.location.href
                })
            }).catch(function() {});
            return false;
        };
        window.addEventListener('unhandledrejection', function(event) {
            var reason = event.reason;
            fetch('/log-error', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    message: reason ? reason.message || String(reason) : 'Unhandled Rejection',
                    source: '',
                    line: '',
                    col: '',
                    stack: reason ? reason.stack : '',
                    url: window.location.href
                })
            }).catch(function() {});
        });
    </script>
    <title>{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}</title>
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

@if(session()->has('original_superadmin_id'))
    <div class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] w-[90%] max-w-xl">
        <div class="bg-slate-900/95 backdrop-blur-md border border-violet-500/30 rounded-2xl px-5 py-3 shadow-2xl flex items-center justify-between gap-4 animate-bounce-subtle">
            <div class="flex items-center gap-3 relative">
                <span class="flex h-2.5 w-2.5 rounded-full bg-violet-400 animate-ping absolute"></span>
                <span class="flex h-2.5 w-2.5 rounded-full bg-violet-500 relative"></span>
                <div class="text-xs sm:text-sm font-medium text-white pl-2">
                    Superpower Mode: Impersonating <span class="font-extrabold text-violet-300">{{ auth()->user()->name }}</span>
                </div>
            </div>
            <a href="{{ route('impersonate.stop') }}" class="flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white font-extrabold text-xs px-3.5 py-1.5 rounded-xl shadow-md transition-all whitespace-nowrap">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Return to Superadmin
            </a>
        </div>
    </div>
    <style>
        @keyframes bounce-subtle {
            0%, 100% { transform: translate(-50%, 0); }
            50% { transform: translate(-50%, -4px); }
        }
        .animate-bounce-subtle {
            animation: bounce-subtle 3s ease-in-out infinite;
        }
    </style>
@endif

@php
$hasCbt            = true;
$appMode           = (string) config('academyhub.mode', 'full');
$cbtLocked         = false;
$user              = auth()->user();
$schoolLogo        = config('academyhub.school_logo');
$schoolName        = config('academyhub.school_name', config('app.name', 'AcademyHub'));
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
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"></div>

    {{-- ══════════════════════════════════════
         MOBILE SIDEBAR
    ══════════════════════════════════════ --}}
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

        {{-- Branding --}}
        <div class="mx-3 mb-2 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-50 ring-2 ring-violet-100">
                    @if($schoolLogo)
                        <img src="{{ asset('uploads/'.str_replace('\\','/',$schoolLogo)) }}" alt="Logo" class="h-full w-full object-contain p-1"/>
                    @else
                        <img src="{{ asset('full.png') }}" alt="AcademyHub" class="h-full w-full object-contain p-0.5"/>
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
                        <img src="{{ asset('full.png') }}" alt="AcademyHub" class="h-full w-full object-contain p-0.5"/>
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

    </aside>

    {{-- ══════════════════════════════════════
         MAIN AREA
    ══════════════════════════════════════ --}}
    <div class="flex flex-1 min-w-0 flex-col">

        {{-- Header --}}
        <header class="sticky top-0 z-30 flex h-[72px] items-center justify-between gap-4 border-b border-slate-200/60 bg-white px-4 sm:px-6 shadow-sm">

            <div class="flex items-center gap-3 overflow-hidden">
                <button @click="mobileSidebarOpen = true"
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:hidden transition-colors">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <button @click="sidebarCollapsed = !sidebarCollapsed" title="Toggle Menu"
                        class="hidden h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:flex transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="min-w-0 overflow-hidden">
                    <h1 class="truncate text-base font-extrabold tracking-tight text-slate-900 sm:text-lg">{{ $schoolName }}</h1>
                    <p class="truncate text-[10px] font-medium text-slate-400 sm:text-xs">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">

                {{-- Bell --}}
                <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-colors">
                    <livewire:notifications.bell />
                </div>

                {{-- User chip --}}
                <a href="{{ route('profile') }}"
                   class="flex items-center gap-1 sm:gap-2.5 rounded-full border border-slate-200 bg-white p-1 sm:py-1 sm:pl-1 sm:pr-4 shadow-sm hover:shadow-md transition-all">
                    @if($user?->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                             class="h-8 w-8 flex-shrink-0 rounded-full object-cover"/>
                    @else
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-500 text-white shadow-sm">
                            <span class="text-sm font-extrabold leading-none">{{ $userInitial }}</span>
                        </div>
                    @endif
                    <div class="hidden leading-tight sm:block text-left">
                        <div class="text-sm font-bold text-slate-800">{{ $user?->name ?? 'User' }}</div>
                        <div class="text-[11px] font-semibold text-slate-400">{{ ucfirst($user?->role ?? 'user') }}</div>
                    </div>
                </a>

                {{-- Header Logout Button --}}
                <button type="button" onclick="doLogout('logoutForm')" title="Logout"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors shadow-sm flex-shrink-0">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>



            </div>
        </header>

        {{-- Subscription banners --}}
        {{-- Subscription banners --}}
        @if(isset($subscriptionDueDate) && $user && !$user->is_super_admin && $user->tenant_id)
            @if($subscriptionIsPastDue)
                <!-- Subscription Expired Side Alert Toast -->
                <div class="fixed bottom-4 right-4 z-[999] max-w-sm bg-white/95 backdrop-blur-md border-l-4 border-red-500 rounded-2xl shadow-2xl p-4 animate-bounce-subtle border border-slate-100">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shadow-inner">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">Subscription Expired</h4>
                            <p class="text-[11px] font-semibold text-slate-500 mt-0.5 leading-relaxed">
                                @if($user->role === 'admin')
                                    School portal is in read-only mode. <a href="{{ route('settings.subscription') }}" class="text-red-600 hover:text-red-700 font-extrabold underline allow-billing">Renew subscription</a>
                                @else
                                    School portal is in read-only mode. Please notify your administrator to renew.
                                @endif
                            </p>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Subscription Expired Interactive Modal -->
                <div id="subscription-expired-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full border border-red-100 overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="subscription-modal-content">
                        <!-- Header Banner with Icon -->
                        <div class="bg-gradient-to-br from-red-500 to-rose-600 p-6 text-white text-center relative overflow-hidden">
                            <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-white/10"></div>
                            <div class="absolute -left-10 -bottom-10 w-24 h-24 rounded-full bg-white/10"></div>
                            
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 backdrop-blur-md text-white mb-3 shadow-inner">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-black tracking-tight uppercase">Feature Locked</h3>
                            <p class="text-[10px] uppercase text-red-100 mt-1 font-bold tracking-wider">Read-Only Mode Active</p>
                        </div>
                        
                        <!-- Body -->
                        <div class="p-6 text-center">
                            <p class="text-slate-600 text-xs sm:text-sm font-medium leading-relaxed mb-6">
                                @if($user->role === 'admin')
                                    Your school's subscription has ended. Creating, updating, or deleting data is disabled in read-only mode. Please renew to restore full access.
                                @else
                                    This school's subscription has ended. Creating, updating, or deleting data is disabled in read-only mode. Please contact your school administrator to renew the subscription.
                                @endif
                            </p>
                            
                            <div class="flex flex-col gap-2">
                                @if($user->role === 'admin')
                                    <a href="{{ route('settings.subscription') }}" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-red-500/20 transition-all gap-2 transform hover:-translate-y-0.5 active:translate-y-0 allow-billing">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"></path>
                                        </svg>
                                        Renew Subscription Now
                                    </a>
                                @endif
                                <button type="button" onclick="closeSubscriptionModal()" class="w-full inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 font-bold text-xs uppercase tracking-wider transition-all">
                                    Dismiss
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    main input:not(.allow-billing), main select:not(.allow-billing), main textarea:not(.allow-billing), main button:not(.allow-billing) {
                        opacity: 0.7 !important;
                        cursor: not-allowed !important;
                    }
                </style>

                <script>
                    window.subscriptionExpired = true;

                    function showSubscriptionModal() {
                        const modal = document.getElementById('subscription-expired-modal');
                        const content = document.getElementById('subscription-modal-content');
                        if (modal && content) {
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                            setTimeout(() => {
                                content.classList.remove('scale-95', 'opacity-0');
                                content.classList.add('scale-100', 'opacity-100');
                            }, 50);
                        }
                    }

                    function closeSubscriptionModal() {
                        const modal = document.getElementById('subscription-expired-modal');
                        const content = document.getElementById('subscription-modal-content');
                        if (modal && content) {
                            content.classList.remove('scale-100', 'opacity-100');
                            content.classList.add('scale-95', 'opacity-0');
                            setTimeout(() => {
                                modal.classList.remove('flex');
                                modal.classList.add('hidden');
                            }, 300);
                        }
                    }

                    // Global capture-phase listener to intercept input and submit clicks
                    document.addEventListener('click', function(e) {
                        if (window.subscriptionExpired) {
                            let element = e.target;
                            while (element && element !== document.body) {
                                const tagName = element.tagName;
                                
                                // Don't intercept clicks inside the modal itself or on allow-billing targets
                                if (element.closest('#subscription-expired-modal') || element.classList.contains('allow-billing')) {
                                    return;
                                }

                                const isInput = ['INPUT', 'SELECT', 'TEXTAREA'].includes(tagName);
                                const isButton = tagName === 'BUTTON';
                                
                                // Intercept links designed to create/edit/delete/save or with Wire attributes that call actions
                                const href = element.getAttribute('href');
                                const isMutatingLink = tagName === 'A' && (
                                    (href && (href.includes('create') || href.includes('edit') || href.includes('delete') || href.includes('store') || href.includes('update'))) || 
                                    element.getAttribute('wire:click') || 
                                    element.classList.contains('btn-primary') || 
                                    element.classList.contains('btn-danger') || 
                                    element.classList.contains('btn-success')
                                );

                                if (isInput || isButton || isMutatingLink) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    showSubscriptionModal();
                                    return;
                                }
                                element = element.parentElement;
                            }
                        }
                    }, true);
                </script>
            @elseif(!$subscriptionIsPastDue && $subscriptionDaysUntilDue <= 7)
                <!-- Subscription Expiring Soon Side Alert Toast -->
                <div class="fixed bottom-4 right-4 z-[999] max-w-sm bg-white/95 backdrop-blur-md border-l-4 border-amber-500 rounded-2xl shadow-2xl p-4 animate-bounce-subtle border border-slate-100">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shadow-inner">
                            <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">Expiring Soon</h4>
                            <p class="text-[11px] font-semibold text-slate-500 mt-0.5 leading-relaxed">
                                @if($user->role === 'admin')
                                    Expires in {{ $subscriptionDaysUntilDue }} days. Please <a href="{{ route('settings.subscription') }}" class="text-amber-600 hover:text-amber-700 font-extrabold underline allow-billing">renew now</a> to avoid service interruption.
                                @else
                                    Expires in {{ $subscriptionDaysUntilDue }} days. Please notify your administrator to renew.
                                @endif
                            </p>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-6">
            @php
                $resolvedTenant = app()->bound('currentTenant') ? app('currentTenant') : null;
            @endphp
            @if($resolvedTenant && $resolvedTenant->active_broadcast_banner)
                <div class="mb-6 rounded-2xl bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 p-4 shadow-md backdrop-blur-md flex items-start gap-3.5 relative overflow-hidden group transition-all hover:shadow-lg">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-amber-500/5 group-hover:scale-150 transition-all duration-700"></div>
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-600 shadow-inner">
                        <svg class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 pr-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-amber-600/90 mb-0.5">Global Announcement</div>
                        <p class="text-sm font-semibold text-slate-800 leading-relaxed">{{ $resolvedTenant->active_broadcast_banner }}</p>
                    </div>
                </div>
            @endif
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        <livewire:global-modal />
    </div>

</div>

<form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">@csrf</form>
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

    {{-- Mobile sidebar JS is handled in app.js --}}

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
            sendBrowserNotification(d.title || 'AcademyHub', d.message || 'New notification', d.url || '/');
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
