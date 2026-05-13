<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dev Console — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <style>
        /* ── Scrollbar ─────────────────────────────────────────── */
        [x-cloak] { display: none !important; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }

        /* ── Stat Cards ────────────────────────────────────────── */
        .sa-stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
        }
        @media (max-width: 1280px) { .sa-stats-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 900px)  { .sa-stats-grid { grid-template-columns: repeat(2, 1fr); } }

        .sa-stat-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
            border: 1px solid rgba(0,0,0,.05);
            transition: box-shadow .2s, transform .2s;
        }
        .sa-stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.09); transform: translateY(-1px); }

        .sa-stat-icon {
            flex-shrink: 0;
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .sa-stat-icon svg { width: 22px; height: 22px; }

        .sa-stat-card.orange  .sa-stat-icon { background: #fff7ed; color: #ea580c; }
        .sa-stat-card.emerald .sa-stat-icon { background: #f0fdf4; color: #059669; }
        .sa-stat-card.indigo  .sa-stat-icon { background: #eef2ff; color: #4f46e5; }
        .sa-stat-card.teal    .sa-stat-icon { background: #f0fdfa; color: #0d9488; }
        .sa-stat-card.rose    .sa-stat-icon { background: #fff1f2; color: #e11d48; }
        .sa-stat-card.violet  .sa-stat-icon { background: #f5f3ff; color: #7c3aed; }

        .sa-stat-info { min-width: 0; }
        .sa-stat-label { font-size: 11.5px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
        .sa-stat-value { font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1.1; margin-bottom: 4px; }
        .sa-stat-sub   { font-size: 11.5px; font-weight: 500; color: #94a3b8; display: flex; align-items: center; gap: 4px; }

        .sa-stat-card.orange  .sa-stat-sub { color: #ea580c; }
        .sa-stat-card.emerald .sa-stat-sub { color: #059669; }
        .sa-stat-card.indigo  .sa-stat-sub { color: #6366f1; }
        .sa-stat-card.teal    .sa-stat-sub { color: #0d9488; }
        .sa-stat-card.rose    .sa-stat-sub { color: #e11d48; }

        /* ── Panels ────────────────────────────────────────────── */
        .sa-panel {
            background: #fff;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,.06);
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
            overflow: hidden;
        }
        .sa-panel-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .sa-panel-title {
            font-size: 13.5px; font-weight: 700; color: #1e293b;
            display: flex; align-items: center; gap: 6px;
        }
        .sa-panel-link {
            font-size: 12px; font-weight: 600; color: #6366f1;
            text-decoration: none; transition: color .15s;
        }
        .sa-panel-link:hover { color: #4f46e5; }

        /* ── Tables ────────────────────────────────────────────── */
        .sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .sa-table thead tr { background: #f8fafc; }
        .sa-table th {
            padding: 10px 16px; text-align: left;
            font-size: 11px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: .05em;
            border-bottom: 1px solid #f1f5f9;
        }
        .sa-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f8fafc;
            color: #475569;
            vertical-align: middle;
        }
        .sa-table tbody tr:last-child td { border-bottom: none; }
        .sa-table tbody tr:hover td { background: #fafbff; }

        /* ── Badges ────────────────────────────────────────────── */
        .sa-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 99px;
            font-size: 11.5px; font-weight: 700;
        }
        .sa-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        .sa-badge.active     { background: #dcfce7; color: #15803d; }
        .sa-badge.pending    { background: #fef9c3; color: #a16207; }
        .sa-badge.suspended  { background: #fee2e2; color: #b91c1c; }
        .sa-badge.free       { background: #f1f5f9; color: #475569; }
        .sa-badge.pro        { background: #dbeafe; color: #1d4ed8; }
        .sa-badge.enterprise { background: #ede9fe; color: #6d28d9; }

        /* ── Buttons ───────────────────────────────────────────── */
        .sa-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 700;
            text-decoration: none; cursor: pointer; border: none;
            transition: all .15s;
        }
        .sa-btn-primary {
            background: #4f46e5; color: #fff;
            box-shadow: 0 2px 8px rgba(79,70,229,.3);
        }
        .sa-btn-primary:hover { background: #4338ca; box-shadow: 0 4px 12px rgba(79,70,229,.4); }
        .sa-btn-danger  { background: #ef4444; color: #fff; }
        .sa-btn-danger:hover  { background: #dc2626; }
        .sa-btn-ghost   { background: #f1f5f9; color: #475569; }
        .sa-btn-ghost:hover   { background: #e2e8f0; }

        /* ── Chart wrap ────────────────────────────────────────── */
        .sa-chart-wrap { position: relative; }
    </style>
</head>
<body class="h-full bg-[#f5f6fa] text-slate-900 font-sans">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside class="hidden lg:flex w-72 flex-shrink-0 flex-col bg-[#f5f6fa] border-r border-slate-200/50">

        {{-- Brand --}}
        <div class="mx-3 mt-4 mb-2 rounded-2xl bg-white shadow-sm p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-violet-50 ring-2 ring-violet-100">
                    <svg class="h-7 w-7 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900">Dev Console</div>
                    <div class="mt-0.5 text-[11px] font-semibold text-violet-500">Multi-Tenant Admin</div>
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5 min-h-0 mt-2">
            <p class="px-3 pt-2 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Overview</p>

            <a href="{{ route('superadmin.dashboard') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all
                      {{ request()->routeIs('superadmin.dashboard') ? 'bg-violet-500 text-white shadow-md shadow-violet-200' : 'text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('superadmin.dashboard') ? 'bg-white/20' : 'bg-white shadow-sm' }}">
                    <svg class="h-4 w-4 {{ request()->routeIs('superadmin.dashboard') ? 'text-white' : 'text-violet-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                Dashboard
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Schools</p>

            <a href="{{ route('superadmin.tenants.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all
                      {{ request()->routeIs('superadmin.tenants.index') ? 'bg-violet-500 text-white shadow-md shadow-violet-200' : 'text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('superadmin.tenants.index') ? 'bg-white/20' : 'bg-white shadow-sm' }}">
                    <svg class="h-4 w-4 {{ request()->routeIs('superadmin.tenants.index') ? 'text-white' : 'text-violet-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                All Schools
            </a>

            <a href="{{ route('superadmin.tenants.create') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all
                      {{ request()->routeIs('superadmin.tenants.create') ? 'bg-violet-500 text-white shadow-md shadow-violet-200' : 'text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('superadmin.tenants.create') ? 'bg-white/20' : 'bg-white shadow-sm' }}">
                    <svg class="h-4 w-4 {{ request()->routeIs('superadmin.tenants.create') ? 'text-white' : 'text-violet-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                Create School
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Users</p>

            <a href="{{ route('superadmin.users.search') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all
                      {{ request()->routeIs('superadmin.users.*') ? 'bg-violet-500 text-white shadow-md shadow-violet-200' : 'text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('superadmin.users.*') ? 'bg-white/20' : 'bg-white shadow-sm' }}">
                    <svg class="h-4 w-4 {{ request()->routeIs('superadmin.users.*') ? 'text-white' : 'text-violet-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                </div>
                User Search
            </a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-black uppercase tracking-widest text-slate-400">System</p>

            <a href="{{ route('superadmin.billing') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all
                      {{ request()->routeIs('superadmin.billing') ? 'bg-violet-500 text-white shadow-md shadow-violet-200' : 'text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm' }}">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ request()->routeIs('superadmin.billing') ? 'bg-white/20' : 'bg-white shadow-sm' }}">
                    <svg class="h-4 w-4 {{ request()->routeIs('superadmin.billing') ? 'text-white' : 'text-emerald-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                Billing
            </a>

            <form id="autoSuspendForm" method="POST" action="{{ route('superadmin.auto-suspend') }}">
                @csrf
            </form>
            <button type="button"
                    onclick="saConfirm('autoSuspendForm', 'Run auto-suspend for all expired schools?')"
                    class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-white shadow-sm">
                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                Auto-Suspend
            </button>
        </nav>

        {{-- User footer --}}
        <div class="p-3">
            <form method="POST" action="{{ route('superadmin.logout') }}" id="sa-logout-form">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 rounded-2xl bg-slate-800 px-4 py-3.5 hover:bg-slate-900 transition-all">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-violet-500 shadow-md text-white font-extrabold text-sm">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="flex-1 text-left min-w-0">
                        <div class="text-sm font-extrabold text-white truncate">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                        <div class="text-[11px] font-medium text-slate-400">Super Admin</div>
                    </div>
                    <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main ─────────────────────────────────────────────────── --}}
    <div class="flex flex-1 min-w-0 flex-col">

        {{-- Topbar --}}
        <header class="sticky top-0 z-10 flex h-[72px] items-center justify-between gap-4 border-b border-slate-200/60 bg-white px-6 shadow-sm">
            <div>
                <h1 class="text-lg font-extrabold tracking-tight text-slate-900">@yield('header_title', 'Dashboard')</h1>
                <p class="text-xs font-medium text-slate-400">@yield('header_subtitle', 'Manage your multi-tenant workspace')</p>
            </div>
            <div class="flex items-center gap-3">
                @hasSection('header_actions')
                    @yield('header_actions')
                @endif
                <div class="flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    System Online
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto px-6 py-6">

            @if(session('status'))
                <div class="mb-5 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-semibold text-emerald-700">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 flex items-center gap-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-semibold text-red-700">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- ── Password Confirm Modal ──────────────────────────────────── --}}
<div id="saPasswordModal"
     class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/50 backdrop-blur-sm"
     onclick="if(event.target===this) closeSaModal()">
    <div class="w-full max-w-sm rounded-2xl bg-white shadow-2xl p-6 mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100">
                <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <div class="text-base font-extrabold text-slate-900">Confirm Action</div>
                <div class="text-xs text-slate-400" id="saModalSubtitle">Enter your password to continue</div>
            </div>
        </div>
        <input type="password" id="saModalPasswordInput"
               placeholder="Your superadmin password"
               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-800 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 mb-4"
               onkeydown="if(event.key==='Enter') confirmSaAction()">
        <div class="flex gap-3">
            <button onclick="closeSaModal()"
                    class="flex-1 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-200 transition-colors">
                Cancel
            </button>
            <button onclick="confirmSaAction()"
                    class="flex-1 rounded-xl bg-violet-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-600 transition-colors shadow-md shadow-violet-200">
                Confirm
            </button>
        </div>
    </div>
</div>

@stack('scripts')

<script>
let _saTargetForm = null;

function saConfirm(formId, subtitle) {
    _saTargetForm = document.getElementById(formId);
    document.getElementById('saModalSubtitle').textContent = subtitle || 'Enter your password to continue';
    document.getElementById('saModalPasswordInput').value = '';
    const modal = document.getElementById('saPasswordModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => document.getElementById('saModalPasswordInput').focus(), 100);
}

function closeSaModal() {
    const modal = document.getElementById('saPasswordModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    // NOTE: do NOT null out _saTargetForm here — confirmSaAction needs it after close
}

function confirmSaAction() {
    if (!_saTargetForm) return;
    const pw = document.getElementById('saModalPasswordInput').value;
    if (!pw) {
        document.getElementById('saModalPasswordInput').focus();
        return;
    }
    // Inject password into hidden input
    let hidden = _saTargetForm.querySelector('input[name="sa_password"]');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'sa_password';
        _saTargetForm.appendChild(hidden);
    }
    hidden.value = pw;

    // Close modal first, then grab reference before nulling
    const formToSubmit = _saTargetForm;
    _saTargetForm = null;
    closeSaModal();
    formToSubmit.submit();
}
</script>

</body>
</html>
