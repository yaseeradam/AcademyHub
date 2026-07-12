<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Console — AcademyHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Load Alpine.js directly since there is no Livewire on Superadmin views --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        [x-cloak] { display: none !important; }

        :root {
            --sa-bg:       #f5f6fa;
            --sa-sidebar:  #f5f6fa;
            --sa-primary:  #7c3aed; /* school violet-600 */
            --sa-accent:   #7c3aed;
            --sa-text:     #0f172a; /* slate-900 */
            --sa-muted:    #64748b; /* slate-500 */
            --sa-border:   rgba(226, 232, 240, 0.8);
            --sa-card:     #ffffff;
            --sa-shadow:   0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.02);
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
            box-shadow: 2px 0 16px rgba(0,0,0,.02);
        }

        .sa-brand {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sa-border);
        }

        .sa-brand-icon {
            width: 44px; height: 44px;
            background: #7c3aed;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(124,58,237,.25);
        }

        .sa-brand-text { line-height: 1.2; }
        .sa-brand-title { font-size: 15px; font-weight: 800; color: var(--sa-text); }
        .sa-brand-sub   { font-size: 10px; font-weight: 700; color: var(--sa-primary); letter-spacing: .05em; text-transform: uppercase; }

        .sa-nav { flex: 1; overflow-y: auto; padding: 16px 12px; }

        .sa-nav-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--sa-muted);
            padding: 8px 8px 6px;
            margin-top: 10px;
        }

        .sa-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 700;
            color: #475569; /* slate-600 */
            transition: all .2s;
            margin-bottom: 4px;
            border: 1px solid transparent;
        }

        .sa-nav a:hover {
            background: #ffffff;
            color: var(--sa-text);
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            border-color: rgba(226, 232, 240, 0.5);
        }

        .sa-nav a.active {
            background: #7c3aed;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
            border-color: #7c3aed;
        }
        .sa-nav a.active svg { color: #ffffff; }

        .sa-nav a svg { width: 18px; height: 18px; flex-shrink: 0; transition: color .2s; color: #64748b; }

        .sa-nav-badge {
            margin-left: auto;
            background: #ffffff;
            color: #7c3aed;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
        }

        /* Sidebar user footer */
        .sa-user-footer {
            padding: 16px;
            border-top: 1px solid var(--sa-border);
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
        }
        .sa-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: #7c3aed;
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            font-size: 13px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(124, 58, 237, 0.2);
        }
        .sa-user-name  { font-size: 13px; font-weight: 800; color: var(--sa-text); }
        .sa-user-role  { font-size: 11px; font-weight: 600; color: var(--sa-muted); }
        .sa-logout-btn {
            margin-left: auto;
            width: 32px; height: 32px;
            border-radius: 10px;
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fecdd3;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all .2s;
        }
        .sa-logout-btn:hover { background: #fee2e2; color: #dc2626; transform: scale(1.05); }
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
            height: 72px;
            background: white;
            border-bottom: 1px solid var(--sa-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 1px 4px rgba(0,0,0,.02);
        }

        .sa-page-title { font-size: 18px; font-weight: 900; color: var(--sa-text); letter-spacing: -0.02em; }
        .sa-page-sub   { font-size: 12px; font-weight: 600; color: var(--sa-muted); }

        .sa-status-pill {
            display: flex; align-items: center; gap: 6px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #16a34a;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
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
            padding: 16px;
            box-shadow: var(--sa-shadow);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .sa-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,.06);
        }

        .sa-stat-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sa-stat-icon svg { width: 22px; height: 22px; }

        .sa-stat-info { flex: 1; min-width: 0; }
        .sa-stat-value {
            font-size: 22px; font-weight: 800;
            line-height: 1.1;
            margin-bottom: 2px;
        }
        .sa-stat-label {
            font-size: 10.5px; font-weight: 600;
            color: var(--sa-muted);
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .sa-stat-sub {
            display: flex; align-items: center; gap: 4px;
            margin-top: 4px;
            font-size: 10.5px; font-weight: 600;
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
            border-radius: 20px;
            border: 1px solid var(--sa-border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04), 0 2px 4px -1px rgba(0,0,0,0.01);
            overflow: visible;
        }

        .sa-panel-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid var(--sa-border);
            background: #ffffff;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
        .sa-panel-title { font-size: 14.5px; font-weight: 800; color: var(--sa-text); }

        .sa-panel-link {
            font-size: 12px; font-weight: 700;
            color: #7c3aed;
            text-decoration: none;
            padding: 5px 12px;
            background: rgba(124,58,237,.08);
            border-radius: 8px;
            transition: background .15s;
        }
        .sa-panel-link:hover { background: rgba(124,58,237,.14); }

        /* Responsive Table Wrapper */
        .sa-table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (min-width: 1024px) {
            .sa-table-responsive {
                overflow: visible !important;
            }
        }

        /* ── Table ───────────────────────────────────── */
        .sa-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .sa-table thead tr:first-child th:first-child { border-top-left-radius: 20px; }
        .sa-table thead tr:first-child th:last-child { border-top-right-radius: 20px; }
        .sa-table tbody tr:last-child td:first-child { border-bottom-left-radius: 20px; }
        .sa-table tbody tr:last-child td:last-child { border-bottom-right-radius: 20px; }

        .sa-table thead th {
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--sa-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--sa-border);
        }
        .sa-table tbody td {
            padding: 14px 20px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .sa-table tbody tr:last-child td { border-bottom: none; }
        .sa-table tbody tr:hover td { background: #f8fafc; }

        /* Status + Plan badges */
        .sa-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px;
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
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all .2s;
        }
        .sa-btn svg { width: 16px; height: 16px; }

        .sa-btn-primary {
            background: #7c3aed;
            color: white;
            box-shadow: 0 4px 12px rgba(124, 58, 237, .2);
        }
        .sa-btn-primary:hover {
            background: #6d28d9;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(124, 58, 237, .3);
        }

        .sa-btn-ghost {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid rgba(226, 232, 240, 0.5);
        }
        .sa-btn-ghost:hover { background: #e2e8f0; color: var(--sa-text); }

        .sa-btn-danger { background: #ef4444; color: #ffffff; border: 1px solid #dc2626; }
        .sa-btn-danger:hover { background: #dc2626; color: #ffffff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,0.3); }

        .sa-btn-icon {
            width: 32px; height: 32px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 8px;
        }

        /* ── Actions Dropdown ────────────────────────── */
        .sa-dropdown {
            position: relative;
            display: inline-block;
            text-align: left;
        }
        .sa-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 6px;
            width: 170px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            z-index: 100;
            overflow: hidden;
            transform-origin: top right;
        }
        .sa-dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            background: transparent;
            border: none;
            text-align: left;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .sa-dropdown-item:hover {
            background: #f8fafc;
            color: #7c3aed;
        }
        .sa-dropdown-item.danger {
            color: #ef4444;
        }
        .sa-dropdown-item.danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }
        .sa-dropdown-item svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* ── Forms ────────────────────────────────────── */
        .sa-form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }
        .sa-form-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--sa-border);
            border-radius: 12px;
            font-size: 13.5px;
            font-family: inherit;
            color: var(--sa-text);
            background: white;
            transition: all .15s;
            outline: none;
        }
        .sa-form-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
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
        .sa-alert.error   { background: #fef2f2; color: #ef4444; border: 1px solid #fecdd3; }
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

        /* ── Responsiveness ─────────────────────────── */
        .lg-hidden { display: none !important; }

        @media (max-width: 1023px) {
            .lg-hidden { display: inline-flex !important; }
            #sa-sidebar {
                transform: translateX(-100%);
                transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            #sa-sidebar.open {
                transform: translateX(0);
            }
            #sa-main {
                margin-left: 0 !important;
            }
            #sa-topbar {
                padding: 0 16px;
            }
            .sa-grid-2, .sa-grid-3 {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .sa-stats-grid {
                grid-template-columns: 1fr;
            }
            .sa-page-title {
                font-size: 16px;
            }
            .sa-page-sub {
                display: none !important;
            }
        }
        
        @media (max-width: 639px) {
            .hidden-xs {
                display: none !important;
            }
        }
    </style>
</head>
<body x-data="superAdminGlobal()" @submit="handleFormSubmit($event)">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div x-cloak x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.55); z-index: 45;" class="lg-hidden"></div>

    <!-- Sidebar -->
    <aside id="sa-sidebar" :class="mobileSidebarOpen ? 'open' : ''">
        <!-- Brand -->
        <div class="sa-brand" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="{{ asset('full.png') }}" alt="AcademyHub" style="height:40px;width:auto;flex-shrink:0;">
                <div class="sa-brand-text">
                    <div class="sa-brand-title">AcademyHub</div>
                    <div class="sa-brand-sub">Super Admin Console</div>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button @click="mobileSidebarOpen = false" class="lg-hidden" aria-label="Close Sidebar" style="background: none; border: none; cursor: pointer; padding: 6px; border-radius: 8px; color: var(--sa-muted); display: inline-flex; align-items: center; justify-content: center;">
                <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
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

            <a href="{{ route('superadmin.health') }}"
               class="{{ request()->routeIs('superadmin.health') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                System Health
            </a>

            <a href="{{ route('superadmin.notifications.list') }}"
               class="{{ request()->routeIs('superadmin.notifications.list') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Notifications
                @php
                    $saUnreadCount = \App\Models\SuperadminNotification::unreadCount();
                @endphp
                @if($saUnreadCount > 0)
                    <span class="sa-nav-badge" style="background:#ef4444; color:white; animation: pulse 2s infinite;">{{ $saUnreadCount }}</span>
                @endif
            </a>

            <div class="sa-nav-label" style="margin-top:12px;">School Instances</div>

            <a href="{{ route('superadmin.tenants.index') }}"
               class="{{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                All Schools
                @php
                    $pendingPayoutCount = \App\Models\Tenant::where('settings->payment_gateway->subaccount_status', 'pending')->count();
                @endphp
                @if($pendingPayoutCount > 0)
                    <span class="sa-nav-badge" style="background:#ef4444; color:white; animation: pulse 2s infinite;">{{ $pendingPayoutCount }}</span>
                @endif
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

            <div class="sa-nav-label" style="margin-top:12px;">System Settings</div>

            <a href="{{ route('superadmin.settings.pricing') }}"
               class="{{ request()->routeIs('superadmin.settings.pricing') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pricing Settings
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
        </div>
    </aside>

    <!-- Main -->
    <div id="sa-main">
        <!-- Topbar -->
        <header id="sa-topbar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <!-- Hamburger Menu Button -->
                <button @click="mobileSidebarOpen = true" class="lg-hidden" aria-label="Open Sidebar" style="background: none; border: none; cursor: pointer; padding: 6px; border-radius: 8px; color: var(--sa-muted); display: inline-flex; align-items: center; justify-content: center;">
                    <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div>
                    <div class="sa-page-title">@yield('header_title', 'Dashboard')</div>
                    <div class="sa-page-sub">@yield('header_subtitle', 'Manage your multi-tenant workspace')</div>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:12px;">
                @hasSection('header_actions')
                    <div class="hidden-xs" style="display: flex; align-items: center; gap: 8px;">
                        @yield('header_actions')
                    </div>
                @endif
                <!-- Superadmin Notifications Bell -->
                <div x-data="saNotifications()" x-init="initNotifications()" style="position: relative; margin-right: 4px;">
                    <button @click="toggleDropdown()" class="sa-bell-btn" style="position: relative; background: none; border: 1px solid var(--sa-border); padding: 8px; border-radius: 8px; color: var(--sa-muted); cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; height: 38px; width: 38px;">
                        <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" class="sa-bell-badge" style="position: absolute; top: -4px; right: -4px; height: 16px; min-width: 16px; border-radius: 999px; background: #ef4444; color: white; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; padding: 0 4px; box-shadow: 0 0 0 2px white; animation: pulse 2s infinite;" x-text="unreadCount"></span>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-cloak x-show="isOpen" @click.away="isOpen = false" style="position: absolute; right: 0; top: 48px; width: 360px; background: white; border: 1px solid var(--sa-border); border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05); z-index: 100; overflow: hidden;">
                        <!-- Header -->
                        <div style="padding: 14px 18px; border-bottom: 1px solid var(--sa-border); display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                            <span style="font-size: 14px; font-weight: 800; color: var(--sa-text);">Notifications</span>
                            <button x-show="unreadCount > 0" @click="markAllRead()" style="background: none; border: none; font-size: 11.5px; font-weight: 700; color: #7c3aed; cursor: pointer; padding: 0; margin: 0;">Mark all as read</button>
                        </div>

                        <!-- Scrollable Area -->
                        <div style="max-height: 320px; overflow-y: auto;">
                            <!-- Loading State -->
                            <template x-if="loading">
                                <div style="padding: 30px; text-align: center; color: var(--sa-muted); display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <div class="spinner" style="width: 20px; height: 20px; border: 2.5px solid transparent; border-top-color: #7c3aed; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                                    <span style="font-size: 12.5px; font-weight: 500;">Loading...</span>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <template x-if="!loading && notifications.length === 0">
                                <div style="padding: 40px 20px; text-align: center; color: var(--sa-muted); display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 700; color: #475569;">All caught up!</div>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">No new alerts at this time.</div>
                                    </div>
                                </div>
                            </template>

                            <!-- Notifications List -->
                            <template x-if="!loading && notifications.length > 0">
                                <div>
                                    <template x-for="item in notifications" :key="item.id">
                                        <div @click="handleItemClick(item)" class="sa-notification-item" :style="item.read_at ? 'background: white; border-bottom: 1px solid #f1f5f9; padding: 14px 18px; cursor: pointer; transition: background 0.15s; display: flex; gap: 12px; align-items: flex-start;' : 'background: #faf5ff; border-bottom: 1px solid #f1f5f9; padding: 14px 18px; cursor: pointer; transition: background 0.15s; display: flex; gap: 12px; align-items: flex-start; border-left: 3px solid #7c3aed;'">
                                            <!-- Icon / Badge based on notification type -->
                                            <div :style="getIconStyle(item.type)" style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: white;">
                                                <template x-if="item.type === 'payout_request'">
                                                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </template>
                                                <template x-if="item.type === 'app_rating'">
                                                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.17 0l-3.971 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 10.3c-.783-.57-.38-1.81.588-1.81h4.907a1 1 0 00.95-.69l1.52-4.674z" />
                                                    </svg>
                                                </template>
                                                <template x-if="item.type !== 'payout_request' && item.type !== 'app_rating'">
                                                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </template>
                                            </div>

                                            <!-- Text Content -->
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 4px;">
                                                    <span style="font-size: 11px; font-weight: 800; color: #7c3aed; text-transform: uppercase;" x-text="item.tenant || 'System'"></span>
                                                    <span style="font-size: 10px; color: #94a3b8; white-space: nowrap;" x-text="item.created_at"></span>
                                                </div>
                                                <div style="font-size: 13px; font-weight: 700; color: var(--sa-text); margin-top: 2px;" x-text="item.title"></div>
                                                <div style="font-size: 12px; color: #64748b; margin-top: 4px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" x-text="item.message"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="sa-status-pill">
                    <span class="sa-status-dot"></span>
                    <span class="hidden-xs">System Online</span>
                    <span class="lg-hidden">Online</span>
                </div>
                <form method="POST" action="{{ route('superadmin.logout') }}" id="saLogoutForm" style="margin: 0; display: inline-flex;">
                    @csrf
                    <button type="button" onclick="doSaLogout()" title="Logout" style="display: inline-flex; align-items: center; justify-content: center; background: none; border: 1px solid var(--sa-border); padding: 8px 12px; border-radius: 8px; font-weight: bold; color: var(--sa-muted); cursor: pointer; transition: all 0.2s; font-size: 13px; gap: 6px;">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden-xs">Logout</span>
                    </button>
                </form>
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

    <!-- Global Password Confirmation Modal -->
    <div x-cloak x-show="passwordConfirmOpen" 
         class="global-modal-container"
         style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 16px;">
        <!-- Backdrop with soft blur -->
        <div x-show="passwordConfirmOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="passwordConfirmOpen = false"
             style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.55);"></div>

        <!-- Modal Box -->
        <div x-show="passwordConfirmOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             style="position: relative; width: 100%; max-width: 480px; background: #ffffff; border-radius: 20px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; overflow: hidden; z-index: 10;">
            
            <!-- Visual Accent (Shield / Security Icon with warm gradient) -->
            <div style="background: linear-gradient(135deg, #fef2f2, #fee2e2); padding: 24px; display: flex; flex-direction: column; align-items: center; border-bottom: 1px solid #f1f5f9; text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #ef4444; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 14px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);">
                    <svg style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 800; color: #1e293b; margin: 0 0 6px 0;">Verify Security Credentials</h3>
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0; max-width: 360px;" x-text="passwordConfirmMessage"></p>
            </div>

            <!-- Form Fields -->
            <form @submit.prevent="verifyAndSubmit()" style="margin: 0; padding: 24px;">
                <!-- Error Display -->
                <div x-cloak x-show="passwordError" 
                     class="sa-alert error"
                     style="margin-bottom: 16px; font-size: 12.5px; border-radius: 10px; display: flex; align-items: center; gap: 8px;"
                     x-transition:enter="transition ease-out duration-200">
                    <svg style="width: 16px; height: 16px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-text="passwordError"></span>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="sa-confirm-password-input" style="display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">Super Admin Password</label>
                    <input type="password" 
                           id="sa-confirm-password-input" 
                           x-model="passwordInput" 
                           :disabled="passwordSubmitting"
                           class="sa-form-input" 
                           placeholder="••••••••" 
                           style="width: 100%; height: 42px; font-size: 15px; text-align: center; font-family: monospace; letter-spacing: 0.1em; border-radius: 10px; border: 1px solid #cbd5e1; outline: none; transition: border-color 0.15s;"
                           required>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="button" 
                            @click="passwordConfirmOpen = false" 
                            :disabled="passwordSubmitting"
                            class="sa-btn sa-btn-ghost" 
                            style="flex: 1; justify-content: center; height: 42px; border-radius: 10px; font-size: 13.5px; font-weight: 700;">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="passwordSubmitting"
                            class="sa-btn sa-btn-danger" 
                            style="flex: 1; justify-content: center; height: 42px; border-radius: 10px; font-size: 13.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                        <span x-show="passwordSubmitting" class="spinner" style="width: 16px; height: 16px; border: 2.5px solid transparent; border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite;"></span>
                        <span x-text="passwordSubmitting ? 'Verifying...' : 'Verify Security'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Spinner Custom Rotation Style -->
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        function superAdminGlobal() {
            return {
                mobileSidebarOpen: false,
                passwordConfirmOpen: false,
                passwordConfirmMessage: 'Please enter your password to confirm this action.',
                passwordInput: '',
                passwordError: '',
                passwordSubmitting: false,
                activeForm: null,

                handleFormSubmit(e) {
                    const form = e.target;
                    const confirmMsg = form.getAttribute('data-confirm-password');
                    if (confirmMsg) {
                        e.preventDefault();
                        this.activeForm = form;
                        this.passwordConfirmMessage = confirmMsg;
                        this.passwordInput = '';
                        this.passwordError = '';
                        this.passwordConfirmOpen = true;

                        // Focus input field on next tick
                        this.$nextTick(() => {
                            const input = document.getElementById('sa-confirm-password-input');
                            if (input) input.focus();
                        });
                    }
                },

                async verifyAndSubmit() {
                    if (!this.passwordInput) {
                        this.passwordError = 'Password is required.';
                        return;
                    }

                    this.passwordSubmitting = true;
                    this.passwordError = '';

                    try {
                        const response = await fetch('{{ route('superadmin.verify-password') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                password: this.passwordInput
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.verified) {
                            this.passwordConfirmOpen = false;
                            if (this.activeForm) {
                                // Safe native submit method invocation
                                HTMLFormElement.prototype.submit.call(this.activeForm);
                            }
                        } else {
                            this.passwordError = data.message || 'The provided password does not match our records.';
                        }
                    } catch (err) {
                        this.passwordError = 'An error occurred. Please try again.';
                        console.error(err);
                    } finally {
                        this.passwordSubmitting = false;
                    }
                }
            }
        }

        function saNotifications() {
            return {
                isOpen: false,
                loading: false,
                notifications: [],
                unreadCount: 0,
                pollingInterval: null,

                initNotifications() {
                    this.fetchNotifications();
                    // Poll every 30 seconds
                    this.pollingInterval = setInterval(() => {
                        this.fetchNotifications();
                    }, 30000);
                },

                toggleDropdown() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.fetchNotifications();
                    }
                },

                async fetchNotifications() {
                    this.loading = this.notifications.length === 0;
                    try {
                        const res = await fetch('{{ route('superadmin.notifications.index') }}');
                        const data = await res.json();
                        this.notifications = data.notifications;
                        this.unreadCount = data.unread_count;
                    } catch (err) {
                        console.error('Failed to fetch notifications:', err);
                    } finally {
                        this.loading = false;
                    }
                },

                async markAllRead() {
                    try {
                        const res = await fetch('{{ route('superadmin.notifications.mark-all-read') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });
                        if (res.ok) {
                            this.notifications.forEach(n => n.read_at = new Date().toISOString());
                            this.unreadCount = 0;
                        }
                    } catch (err) {
                        console.error('Failed to mark all read:', err);
                    }
                },

                async handleItemClick(item) {
                    if (!item.read_at) {
                        try {
                            await fetch(`/superadmin/notifications/${item.id}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                            item.read_at = new Date().toISOString();
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        } catch (err) {
                            console.error('Failed to mark read:', err);
                        }
                    }
                    this.isOpen = false;
                    if (item.action_url) {
                        window.location.href = item.action_url;
                    }
                },

                getIconStyle(type) {
                    if (type === 'payout_request') {
                        return 'background: linear-gradient(135deg, #10b981, #059669);';
                    } else if (type === 'app_rating') {
                        return 'background: linear-gradient(135deg, #ff8c42, #fb923c);';
                    } else {
                        return 'background: linear-gradient(135deg, #6366f1, #4f46e5);';
                    }
                }
            };
        }

        function doSaLogout(formId = 'saLogoutForm') {
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
    </script>

@stack('scripts')
</body>
</html>


