<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dev Console — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- NOTE: Alpine is already bundled in app.js — DO NOT add a CDN Alpine script here, it causes double-init and breaks Livewire --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        [x-cloak] { display: none !important; }

        :root {
            --sa-bg:       #f0f4f9;
            --sa-sidebar:  #ffffff;
            --sa-primary:  #4f46e5; /* indigo-600 */
            --sa-accent:   #f59e0b; /* amber */
            --sa-text:     #1e293b;
            --sa-muted:    #64748b;
            --sa-border:   #e2e8f0;
            --sa-card:     #ffffff;
            --sa-shadow:   0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.06);
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--sa-bg);
            color: var(--sa-text);
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────────────────── */
        #sa-sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 260px;
            background: var(--sa-sidebar);
            border-right: 1px solid var(--sa-border);
            display: flex;
            flex-direction: column;
            z-index: 50;
            box-shadow: 2px 0 16px rgba(0,0,0,.04);
        }

        .sa-brand {
            padding: 22px 20px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sa-border);
        }

        .sa-brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(79,70,229,.35);
        }

        .sa-brand-text { line-height: 1.2; }
        .sa-brand-title { font-size: 15px; font-weight: 800; color: var(--sa-text); }
        .sa-brand-sub   { font-size: 11px; font-weight: 600; color: var(--sa-muted); letter-spacing: .05em; text-transform: uppercase; }

        .sa-nav { flex: 1; overflow-y: auto; padding: 16px 12px; }

        .sa-nav-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--sa-muted);
            padding: 8px 8px 6px;
            margin-top: 4px;
        }

        .sa-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--sa-muted);
            transition: background .15s, color .15s;
            margin-bottom: 2px;
        }

        .sa-nav a:hover { background: #f1f5f9; color: var(--sa-text); }

        .sa-nav a.active {
            background: linear-gradient(135deg, rgba(79,70,229,.1), rgba(124,58,237,.06));
            color: #4f46e5;
        }
        .sa-nav a.active svg { color: #4f46e5; }

        .sa-nav a svg { width: 17px; height: 17px; flex-shrink: 0; transition: color .15s; }

        .sa-nav-badge {
            margin-left: auto;
            background: #4f46e5;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
        }

        /* Sidebar user footer */
        .sa-user-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--sa-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sa-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .sa-user-name  { font-size: 13px; font-weight: 700; color: var(--sa-text); }
        .sa-user-role  { font-size: 11px; font-weight: 500; color: var(--sa-muted); }
        .sa-logout-btn {
            margin-left: auto;
            width: 30px; height: 30px;
            border-radius: 8px;
            background: #fef2f2;
            color: #ef4444;
            border: none;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .sa-logout-btn:hover { background: #fee2e2; }
        .sa-logout-btn svg { width: 14px; height: 14px; }

        /* ── Main ────────────────────────────────────── */
        #sa-main {
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Topbar */
        #sa-topbar {
            height: 64px;
            background: white;
            border-bottom: 1px solid var(--sa-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }

        .sa-page-title { font-size: 20px; font-weight: 800; color: var(--sa-text); }
        .sa-page-sub   { font-size: 12px; font-weight: 500; color: var(--sa-muted); }

        .sa-status-pill {
            display: flex; align-items: center; gap: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #16a34a;
        }
        .sa-status-dot {
            width: 7px; height: 7px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .4; }
        }

        /* Content */
        #sa-content {
            flex: 1;
            padding: 28px 28px 40px;
        }

        /* ── Stat Cards ──────────────────────────────── */
        .sa-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .sa-stat-card {
            background: var(--sa-card);
            border-radius: 16px;
            padding: 20px 22px;
            box-shadow: var(--sa-shadow);
            display: flex;
            align-items: flex-start;
            gap: 14px;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .sa-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.10);
        }

        .sa-stat-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sa-stat-icon svg { width: 26px; height: 26px; }

        .sa-stat-info { flex: 1; }
        .sa-stat-value {
            font-size: 30px; font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .sa-stat-label {
            font-size: 12px; font-weight: 600;
            color: var(--sa-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .sa-stat-sub {
            display: flex; align-items: center; gap: 4px;
            margin-top: 8px;
            font-size: 11px; font-weight: 600;
        }

        /* Card color variants */
        .sa-stat-card.orange .sa-stat-icon  { background: linear-gradient(135deg, #ff8c42, #fb923c); color: white; }
        .sa-stat-card.orange .sa-stat-value { color: #ea580c; }
        .sa-stat-card.orange .sa-stat-sub   { color: #fb923c; }

        .sa-stat-card.indigo .sa-stat-icon  { background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; }
        .sa-stat-card.indigo .sa-stat-value { color: #4338ca; }
        .sa-stat-card.indigo .sa-stat-sub   { color: #6366f1; }

        .sa-stat-card.teal .sa-stat-icon  { background: linear-gradient(135deg, #14b8a6, #0d9488); color: white; }
        .sa-stat-card.teal .sa-stat-value { color: #0f766e; }
        .sa-stat-card.teal .sa-stat-sub   { color: #14b8a6; }

        .sa-stat-card.rose .sa-stat-icon  { background: linear-gradient(135deg, #f43f5e, #e11d48); color: white; }
        .sa-stat-card.rose .sa-stat-value { color: #be123c; }
        .sa-stat-card.rose .sa-stat-sub   { color: #f43f5e; }

        .sa-stat-card.emerald .sa-stat-icon { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .sa-stat-card.emerald .sa-stat-value { color: #047857; }
        .sa-stat-card.emerald .sa-stat-sub  { color: #10b981; }

        /* ── Panel / Card ────────────────────────────── */
        .sa-panel {
            background: white;
            border-radius: 16px;
            box-shadow: var(--sa-shadow);
            overflow: hidden;
        }

        .sa-panel-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid var(--sa-border);
        }
        .sa-panel-title { font-size: 15px; font-weight: 700; color: var(--sa-text); }

        .sa-panel-link {
            font-size: 12px; font-weight: 700;
            color: #4f46e5;
            text-decoration: none;
            padding: 5px 12px;
            background: rgba(79,70,229,.08);
            border-radius: 8px;
            transition: background .15s;
        }
        .sa-panel-link:hover { background: rgba(79,70,229,.14); }

        /* ── Table ───────────────────────────────────── */
        .sa-table { width: 100%; border-collapse: collapse; }
        .sa-table thead th {
            padding: 12px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--sa-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--sa-border);
        }
        .sa-table tbody td {
            padding: 13px 18px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--sa-text);
        }
        .sa-table tbody tr:last-child td { border-bottom: none; }
        .sa-table tbody tr:hover td { background: #f8fafc; }

        /* Status + Plan badges */
        .sa-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }
        .sa-badge.active   { background: #f0fdf4; color: #15803d; }
        .sa-badge.pending  { background: #fffbeb; color: #b45309; }
        .sa-badge.suspended{ background: #fff1f2; color: #be123c; }
        .sa-badge.free     { background: #f1f5f9; color: #475569; }
        .sa-badge.pro      { background: #eff6ff; color: #1d4ed8; }
        .sa-badge.enterprise { background: #faf5ff; color: #7c3aed; }
        .sa-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        /* ── Buttons ──────────────────────────────────── */
        .sa-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all .2s;
        }
        .sa-btn svg { width: 15px; height: 15px; }

        .sa-btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            box-shadow: 0 4px 12px rgba(79,70,229,.3);
        }
        .sa-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(79,70,229,.4); }

        .sa-btn-ghost {
            background: #f1f5f9;
            color: var(--sa-muted);
        }
        .sa-btn-ghost:hover { background: #e2e8f0; color: var(--sa-text); }

        .sa-btn-danger {
            background: #fff1f2;
            color: #be123c;
        }
        .sa-btn-danger:hover { background: #ffe4e6; }

        .sa-btn-icon {
            width: 32px; height: 32px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 8px;
        }

        /* ── Forms ────────────────────────────────────── */
        .sa-form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--sa-text);
            margin-bottom: 6px;
        }
        .sa-form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--sa-border);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--sa-text);
            background: white;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }
        .sa-form-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.12);
        }
        .sa-form-input::placeholder { color: #94a3b8; }
        .sa-form-error { font-size: 11.5px; color: #ef4444; font-weight: 600; margin-top: 4px; }
        .sa-form-hint  { font-size: 11.5px; color: var(--sa-muted); margin-top: 4px; }

        .sa-section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--sa-muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--sa-border);
            margin-bottom: 18px;
        }

        /* Flash Alert */
        .sa-alert {
            border-radius: 12px;
            padding: 12px 18px;
            display: flex; align-items: center; gap: 10px;
            font-size: 13.5px; font-weight: 600;
            margin-bottom: 20px;
        }
        .sa-alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .sa-alert.error   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .sa-alert svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* Grid helpers */
        .sa-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .sa-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

        /* Chart container */
        .sa-chart-wrap {
            padding: 20px 22px 24px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside id="sa-sidebar">
        <!-- Brand -->
        <div class="sa-brand">
            <div class="sa-brand-icon">✦</div>
            <div class="sa-brand-text">
                <div class="sa-brand-title">DevConsole</div>
                <div class="sa-brand-sub">Multi-Tenant Admin</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sa-nav">
            <div class="sa-nav-label">Overview</div>

            <a href="{{ route('superadmin.dashboard') }}"
               class="{{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>

            <div class="sa-nav-label" style="margin-top:12px;">School Instances</div>

            <a href="{{ route('superadmin.tenants.index') }}"
               class="{{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                All Schools
            </a>

            <a href="{{ route('superadmin.tenants.create') }}"
               class="{{ request()->routeIs('superadmin.tenants.create') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8"/>
                </svg>
                Create School
            </a>

            <div class="sa-nav-label" style="margin-top:12px;">Marketplace</div>

            <a href="{{ route('superadmin.marketplace.index') }}"
               class="{{ request()->routeIs('superadmin.marketplace.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Plugins
            </a>


        </nav>

        <!-- User Footer -->
        <div class="sa-user-footer">
            <div class="sa-avatar">{{ mb_substr(auth()->user()->name ?? 'S', 0, 1) }}</div>
            <div style="flex:1; min-width:0;">
                <div class="sa-user-name" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ auth()->user()->name ?? 'Super Admin' }}
                </div>
                <div class="sa-user-role">Super Admin</div>
            </div>
            <form method="POST" action="{{ route('superadmin.logout') }}" id="sa-logout-form">
                @csrf
                <button type="submit" class="sa-logout-btn" title="Logout">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main -->
    <div id="sa-main">
        <!-- Topbar -->
        <header id="sa-topbar">
            <div>
                <div class="sa-page-title">@yield('header_title', 'Dashboard')</div>
                <div class="sa-page-sub">@yield('header_subtitle', 'Manage your multi-tenant workspace')</div>
            </div>

            <div style="display:flex; align-items:center; gap:12px;">
                @hasSection('header_actions')
                    @yield('header_actions')
                @endif
                <div class="sa-status-pill">
                    <span class="sa-status-dot"></span>
                    System Online
                </div>
            </div>
        </header>

        <!-- Content -->
        <div id="sa-content">
            @if(session('status'))
                <div class="sa-alert success">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="sa-alert error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

@stack('scripts')
</body>
</html>
