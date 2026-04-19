<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Super Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.5) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.5) 0, transparent 50%);
            background-attachment: fixed;
            color: #f8fafc;
        }
        
        /* Glassmorphism utilities */
        .glass-panel {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
        
        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s;
        }
        
        .glass-input:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: rgba(56, 189, 248, 0.5);
            outline: none;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }
        
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.8);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 1);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex selection:bg-sky-500/30">

    <!-- Sidebar -->
    <aside class="w-72 fixed inset-y-0 left-0 z-50 flex flex-col glass-panel flex-shrink-0">
        <div class="h-20 flex items-center px-8 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-400 to-indigo-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-sky-500/30">
                    S
                </div>
                <div>
                    <div class="font-black text-white text-lg tracking-tight leading-tight">SuperAdmin</div>
                    <div class="text-sky-400 text-xs font-bold uppercase tracking-widest">Workspace</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
            <a href="{{ route('superadmin.dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('superadmin.dashboard') ? 'bg-white/10 text-white shadow-inner border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('superadmin.dashboard') ? 'text-sky-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="font-semibold text-sm">Dashboard</span>
            </a>

            <a href="{{ route('superadmin.tenants.index') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('superadmin.tenants.*') ? 'bg-white/10 text-white shadow-inner border border-white/5' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('superadmin.tenants.*') ? 'text-indigo-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="font-semibold text-sm">Schools (Tenants)</span>
            </a>
            
            <a href="{{ url('/') }}" target="_blank"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-slate-400 hover:text-white hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="font-semibold text-sm">Main Site</span>
            </a>
        </nav>

        <div class="p-6 border-t border-white/10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center text-slate-300 font-bold">
                    {{ substr(auth()->user()->first_name ?? 'S', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-white truncate">{{ auth()->user()->full_name ?? 'Super Admin' }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-500/10 text-rose-400 text-sm font-bold hover:bg-rose-500/20 transition-colors border border-rose-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-72 flex flex-col min-w-0">
        <header class="h-20 glass-panel border-b border-l-0 border-white/10 flex items-center justify-between px-8 sticky top-0 z-40">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-white shadow-sm">@yield('header_title', 'Dashboard')</h1>
                <p class="text-sm font-medium text-slate-400">@yield('header_subtitle', 'Manage your multi-tenant workspace')</p>
            </div>
            
            <div class="flex items-center gap-4">
                @hasSection('header_actions')
                    @yield('header_actions')
                @endif
                <div class="h-8 w-px bg-white/10"></div>
                <div class="flex items-center gap-2 text-sm text-slate-300 bg-white/5 px-3 py-1.5 rounded-full border border-white/5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    System Online
                </div>
            </div>
        </header>

        <div class="flex-1 p-8 overflow-x-hidden">
            <div class="max-w-7xl mx-auto">
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <!-- Global Loading Spinner for Livewire Navigation -->
    <div x-data="{ loading: false }"
         x-on:livewire:navigating.window="loading = true"
         x-on:livewire:navigated.window="loading = false"
         x-show="loading"
         style="display: none;"
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity duration-300">
        <div class="h-14 w-14 animate-spin rounded-full border-4 border-slate-700 border-t-sky-500 shadow-lg"></div>
    </div>
</body>
</html>
