@extends('layouts.superadmin')

@section('content')
<style>
    .health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }
    .metric-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 100px;
    }
    .status-healthy {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .status-unhealthy {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .gauge-wrap {
        position: relative;
        height: 8px;
        background: #e2e8f0;
        border-radius: 99px;
        overflow: hidden;
        margin-top: 12px;
    }
    .gauge-fill {
        height: 100%;
        border-radius: 99px;
        transition: width 0.6s ease-out;
    }
    .uri-badge {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1.5px solid #e2e8f0;
    }
    .ping-pulse-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #7c3aed;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-green {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    .badge-amber {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .badge-red {
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #fecdd3;
    }
    .route-table-row {
        transition: background 0.2s;
    }
    .route-table-row.currently-pinging td {
        background: rgba(79, 70, 229, 0.04);
    }
    .text-red-500 {
        color: #ef4444;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    .action-panel {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #7c3aed;
        color: #ffffff;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 700;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
    }
    .action-btn:hover {
        background: #6d28d9;
        transform: translateY(-1px);
    }
    .action-btn-danger {
        background: #ef4444;
    }
    .action-btn-danger:hover {
        background: #dc2626;
    }
    .action-btn-secondary {
        background: #4b5563;
    }
    .action-btn-secondary:hover {
        background: #374151;
    }
    .masquerade-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
</style>

<div style="padding: 24px;">
    <!-- Head section -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
        <div>
            <h1 style="font-size:28px; font-weight:900; color:#0f172a; letter-spacing:-0.5px; margin-bottom:4px;">
                Platform Health & Diagnostics
            </h1>
            <p style="font-size:14px; color:#64748b; font-weight:500;">
                Live infrastructure monitoring, optimization logs, and high-power administrative controls.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; border-radius:12px; padding:14px 20px; margin-bottom:24px; font-size:14px; font-weight:700;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; border-radius:12px; padding:14px 20px; margin-bottom:24px; font-size:14px; font-weight:700;">
            {{ session('error') }}
        </div>
    @endif

    <!-- QUICK OPERATIONS CONTROL CENTER -->
    <h2 style="font-size:18px; font-weight:800; color:#1e293b; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
        <svg style="width:20px; height:20px; color:#7c3aed;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
        Superpower Control Center
    </h2>
    <div class="action-panel">
        <form action="{{ route('superadmin.health.clear-cache') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="action-btn">
                <svg style="width:18px; height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear System Cache
            </button>
        </form>
 
        <a href="{{ route('superadmin.backup.download') }}" class="action-btn action-btn-secondary">
            <svg style="width:18px; height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Trigger Complete SQL Backup
        </a>
    </div>

    <!-- METRICS GRID -->
    <div class="health-grid">
        <!-- Database Health -->
        <div class="metric-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700; uppercase">Database Connection</span>
                    <h3 style="font-size:24px; font-weight:900; color:#0f172a; margin-top:4px;">{{ $systemStats['db_latency'] }}</h3>
                </div>
                <span class="status-badge {{ $systemStats['db_status'] === 'Healthy' ? 'status-healthy' : 'status-unhealthy' }}">
                    {{ $systemStats['db_status'] }}
                </span>
            </div>
            <p style="font-size:12.5px; color:#64748b; margin-top:12px; font-weight:500;">
                PDO test completed successfully. Driver: MySQL.
            </p>
        </div>

        <!-- Cache Health -->
        <div class="metric-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700; uppercase">Cache Store (Redis/File)</span>
                    <h3 style="font-size:24px; font-weight:900; color:#0f172a; margin-top:4px;">{{ $systemStats['cache_latency'] }}</h3>
                </div>
                <span class="status-badge {{ $systemStats['cache_status'] === 'Healthy' ? 'status-healthy' : 'status-unhealthy' }}">
                    {{ $systemStats['cache_status'] }}
                </span>
            </div>
            <p style="font-size:12.5px; color:#64748b; margin-top:12px; font-weight:500;">
                Write-read roundtrip latencies recorded live.
            </p>
        </div>

        <!-- Storage Space -->
        <div class="metric-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700; uppercase">Local Disk Capacity</span>
                    <h3 style="font-size:24px; font-weight:900; color:#0f172a; margin-top:4px;">
                        {{ $systemStats['disk_used'] }} GB / {{ $systemStats['disk_total'] }} GB
                    </h3>
                </div>
                <span class="status-badge {{ $systemStats['disk_percent'] < 85 ? 'status-healthy' : 'status-unhealthy' }}">
                    {{ $systemStats['disk_percent'] }}% Used
                </span>
            </div>
            <div class="gauge-wrap">
                <div class="gauge-fill" style="width: {{ $systemStats['disk_percent'] }}%; background: {{ $systemStats['disk_percent'] < 85 ? '#10b981' : '#ef4444' }};"></div>
            </div>
        </div>

        <!-- Paystack gateway -->
        <div class="metric-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700; uppercase">Paystack API Ping</span>
                    <h3 style="font-size:24px; font-weight:900; color:#0f172a; margin-top:4px;">{{ $systemStats['paystack_latency'] }}</h3>
                </div>
                <span class="status-badge {{ $systemStats['paystack_status'] === 'Online' ? 'status-healthy' : 'status-unhealthy' }}">
                    {{ $systemStats['paystack_status'] }}
                </span>
            </div>
            <p style="font-size:12.5px; color:#64748b; margin-top:12px; font-weight:500;">
                Secured external handshake checks completed.
            </p>
        </div>

        <!-- Active memory -->
        <div class="metric-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700; uppercase">Peak Engine Memory</span>
                    <h3 style="font-size:24px; font-weight:900; color:#0f172a; margin-top:4px;">{{ $systemStats['mem_peak'] }} MB</h3>
                </div>
                <span class="status-badge status-healthy" style="background:#f0f9ff; color:#0284c7; border-color:#bae6fd;">
                    Limit: {{ $systemStats['mem_limit'] }}
                </span>
            </div>
            <p style="font-size:12.5px; color:#64748b; margin-top:12px; font-weight:500;">
                PHP core memory peak utilization for active request.
            </p>
        </div>

        <!-- Queue Status -->
        <div class="metric-card">
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <div>
                    <span style="font-size:12px; color:#64748b; font-weight:700; uppercase">Background Job Queue</span>
                    <h3 style="font-size:24px; font-weight:900; color:#0f172a; margin-top:4px;">{{ $systemStats['failed_jobs'] }} Failed</h3>
                </div>
                <span class="status-badge {{ $systemStats['failed_jobs'] === 0 ? 'status-healthy' : 'status-unhealthy' }}">
                    {{ $systemStats['failed_jobs'] === 0 ? 'Healthy' : 'Check Failures' }}
                </span>
            </div>
            <p style="font-size:12.5px; color:#64748b; margin-top:12px; font-weight:500;">
                Monitoring failed job logs and active queue configurations.
            </p>
        </div>
    </div>

    <!-- MAIN GRID FOR LOGS & MASQUERADE -->
    <div style="display:grid; grid-template-columns: 1fr; gap: 24px; margin-bottom: 24px;">
        <!-- MASQUERADE CONSOLE -->
        <div class="masquerade-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <h2 style="font-size:18px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px;">
                        <svg style="width:20px; height:20px; color:#7c3aed;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        School Impersonation & Masquerade
                    </h2>
                    <p style="font-size:12.5px; color:#64748b; font-weight:500; margin-top:3px;">
                        Securely impersonate any school admin to troubleshoot user accounts and settings.
                    </p>
                </div>
                
                <!-- Search bar -->
                <form action="{{ route('superadmin.health') }}" method="GET" style="display:flex; gap:8px; margin:0;">
                    <input type="text" name="search" placeholder="Search schools..." value="{{ $search }}"
                           style="border:1.5px solid #cbd5e1; border-radius:10px; padding:8px 14px; font-size:13px; outline:none; width:220px; font-weight:500;"/>
                    <button type="submit" class="sa-btn sa-btn-primary" style="padding:8px 16px; font-size:13px; font-weight:700;">
                        Search
                    </button>
                    @if($search)
                        <a href="{{ route('superadmin.health') }}" class="sa-btn sa-btn-ghost" style="padding:8px 16px; font-size:13px; display:inline-flex; align-items:center;">Clear</a>
                    @endif
                </form>
            </div>

            <!-- Schools Grid -->
            <div style="overflow-x:auto; border:1px solid #e2e8f0; border-radius:12px;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#64748b; font-weight:700;">
                            <th style="padding:14px 16px;">School Name</th>
                            <th style="padding:14px 16px;">Subdomain / Domain</th>
                            <th style="padding:14px 16px;">Current Plan</th>
                            <th style="padding:14px 16px;">Primary Administrator</th>
                            <th style="padding:14px 16px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr style="border-bottom:1px solid #e2e8f0; font-weight:500; color:#334155;">
                                <td style="padding:14px 16px;">
                                    <div style="font-weight:800; color:#0f172a;">{{ $tenant->name }}</div>
                                    <div style="font-size:11px; color:#94a3b8; margin-top:2px;">ID: {{ $tenant->id }}</div>
                                </td>
                                <td style="padding:14px 16px;">
                                    <code style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-size:12px; color:#475569;">
                                        {{ $tenant->domain ?: $tenant->slug . '.' . parse_url(config('app.url'), PHP_URL_HOST) }}
                                    </code>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="display:inline-flex; padding:4px 10px; border-radius:100px; font-size:11px; font-weight:700; text-transform:uppercase;
                                        background: {{ $tenant->plan === 'enterprise' ? '#faf5ff' : ($tenant->plan === 'pro' ? '#ecfdf5' : '#f8fafc') }};
                                        color: {{ $tenant->plan === 'enterprise' ? '#7e22ce' : ($tenant->plan === 'pro' ? '#047857' : '#475569') }};
                                        border: 1px solid {{ $tenant->plan === 'enterprise' ? '#e9d5ff' : ($tenant->plan === 'pro' ? '#a7f3d0' : '#cbd5e1') }};">
                                        {{ $tenant->plan }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($tenant->admin_user)
                                        <div>
                                            <div style="font-weight:700; color:#1e293b;">{{ $tenant->admin_user->name }}</div>
                                            <div style="font-size:11.5px; color:#64748b; margin-top:2px;">{{ $tenant->admin_user->email }}</div>
                                        </div>
                                    @else
                                        <span style="color:#94a3b8; font-style:italic;">No Admin Configured</span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px; text-align:right;">
                                    @if($tenant->admin_user)
                                        <form action="{{ route('superadmin.impersonate.start', $tenant->admin_user) }}" method="POST" style="margin:0; display:inline-block;" target="_blank">
                                            @csrf
                                            <button type="submit" class="sa-btn sa-btn-primary" style="padding:6px 14px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                                                <svg style="width:14px; height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0zm-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                                Impersonate Admin
                                            </button>
                                        </form>
                                    @else
                                        <button class="sa-btn" disabled style="padding:6px 14px; font-size:12px; opacity:0.5; cursor:not-allowed;">
                                            Impersonation Unavailable
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:32px; text-align:center; color:#64748b; font-weight:500;">
                                    No school tenants match your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ENDPOINTS HEALTH MONITOR PANEL -->
        <div x-data="{
            routes: @js($routes),
            initialState: @js($initialPingState),
            searchQuery: '',
            results: {},
            currentIndex: -1,
            pinging: false,
            pollInterval: null,
            
            init() {
                // Populate initial state if any exists
                if (this.initialState) {
                    this.results = this.initialState.results || {};
                    this.pinging = this.initialState.pinging || false;
                    this.currentIndex = this.initialState.current_index !== undefined ? this.initialState.current_index : -1;
                    
                    if (this.pinging) {
                        this.startPolling();
                    }
                }
            },
            
            get filteredRoutes() {
                if (!this.searchQuery) return this.routes;
                return this.routes.filter(r => 
                    r.uri.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                    r.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            },
            
            get completedCount() {
                return Object.keys(this.results).length;
            },
            
            get totalCount() {
                return this.routes.length;
            },
            
            get successCount() {
                return Object.values(this.results).filter(res => res.status_code > 0 && res.status_code < 500).length;
            },

            get errorCount() {
                return Object.values(this.results).filter(res => res.status_code === 0 || res.status_code >= 500).length;
            },

            get progressPercent() {
                if (this.totalCount === 0) return 0;
                return Math.round((this.completedCount / this.totalCount) * 100);
            },
            
            async pingAll() {
                if (this.pinging) return;
                this.pinging = true;
                this.results = {};
                this.currentIndex = 0;
                
                try {
                    const response = await fetch('{{ route('superadmin.health.start-background-ping') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    this.startPolling();
                } catch (err) {
                    console.error('Failed to trigger background ping:', err);
                    this.pinging = false;
                }
            },
            
            startPolling() {
                if (this.pollInterval) clearInterval(this.pollInterval);
                this.pollInterval = setInterval(async () => {
                    try {
                        const response = await fetch('{{ route('superadmin.health.ping-status') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        
                        this.results = data.results || {};
                        this.pinging = data.pinging || false;
                        this.currentIndex = data.current_index !== undefined ? data.current_index : -1;
                        
                        if (!this.pinging) {
                            clearInterval(this.pollInterval);
                            this.pollInterval = null;
                            this.currentIndex = -1;
                        }
                    } catch (err) {
                        console.error('Error polling status:', err);
                    }
                }, 1000);
            },
            
            async pingSingle(uri) {
                this.results[uri] = { loading: true };
                
                try {
                    const response = await fetch('{{ route('superadmin.health.ping-endpoint') }}?uri=' + encodeURIComponent(uri), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    this.results[uri] = data;
                } catch (err) {
                    this.results[uri] = {
                        status_code: 0,
                        response_time_ms: 0,
                        status_message: 'JS Request Error: ' + err.message
                    };
                }
            }
        }" style="background:#ffffff; border-radius:16px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
            
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom: 24px;">
                <div>
                    <h2 style="font-size:18px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px; margin:0 0 4px;">
                        <svg style="width:20px; height:20px; color:#7c3aed;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Routing Health Diagnostics
                    </h2>
                    <p style="font-size:12.5px; color:#64748b; font-weight:500; margin:0;">
                        Sequential automated diagnostics on school routes by dynamically injecting mock database active IDs.
                    </p>
                </div>
                <div>
                    <button type="button" @click="pingAll()" :disabled="pinging" class="action-btn"
                            style="background: linear-gradient(135deg, #7c3aed, #4f46e5); color:white; padding:10px 20px; font-size:13px;"
                            :style="pinging ? 'opacity:0.6; cursor:not-allowed;' : ''">
                        <svg style="width:16px; height:16px;" :class="pinging ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 12H16" />
                        </svg>
                        <span x-text="pinging ? 'Analyzing...' : 'Ping All Endpoints'"></span>
                    </button>
                </div>
            </div>

            <!-- Progress section -->
            <div x-show="pinging || completedCount > 0" style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px; font-size:12px; font-weight:700; color:var(--sa-text);">
                    <span>Progress: <span x-text="completedCount" style="color:var(--sa-primary);"></span> / <span x-text="totalCount"></span> endpoints analyzed</span>
                    <span x-text="progressPercent + '%'" style="color: #10b981;"></span>
                </div>
                <div style="width:100%; height:8px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-bottom:12px;">
                    <div :style="'width: ' + progressPercent + '%'" style="height:100%; background: linear-gradient(90deg, #10b981, #7c3aed); border-radius:999px; transition: width 0.15s ease-out;"></div>
                </div>
                <div style="display:flex; gap:16px; font-size:11.5px; font-weight:700;">
                    <span style="display:inline-flex; align-items:center; gap:6px; color:#16a34a;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#16a34a;"></span>
                        Reachable: <span x-text="successCount"></span>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; color:#dc2626;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#dc2626;"></span>
                        Errors: <span x-text="errorCount"></span>
                    </span>
                </div>
            </div>

            <!-- Search controls -->
            <div style="display:flex; gap:12px; align-items:center; margin-bottom:16px;">
                <div style="position:relative; flex:1;">
                    <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; display:flex;">
                        <svg style="width:16px; height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" x-model="searchQuery" class="sa-form-input" placeholder="Filter routes..." style="padding-left:38px; height: 38px;">
                </div>
            </div>

            <!-- Endpoints Table -->
            <div style="overflow-x: auto; border:1px solid #e2e8f0; border-radius:12px; max-height: 480px; overflow-y: auto;">
                <table class="sa-table" style="vertical-align: middle; margin:0;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="width: 25%;">Route Alias</th>
                            <th style="width: 35%;">URI Template</th>
                            <th style="width: 20%;">Status</th>
                            <th style="width: 12%;">Latency</th>
                            <th style="width: 8%; text-align:right;">Ping</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(route, index) in filteredRoutes" :key="route.uri">
                            <tr class="route-table-row" :class="currentIndex === index ? 'currently-pinging' : ''" style="border-bottom:1.5px solid #f1f5f9;">
                                <td style="padding: 10px 16px;">
                                    <div style="font-weight: 700; color: var(--sa-text); font-size:13px;" x-text="route.name"></div>
                                    <div style="font-size:11px; color:var(--sa-muted); margin-top:2px;" x-text="route.action"></div>
                                </td>
                                <td style="padding: 10px 16px;">
                                    <span class="uri-badge" x-text="route.uri" style="font-size:11px; padding:2px 6px;"></span>
                                    <div x-show="results[route.uri] && results[route.uri].resolved_url" 
                                         style="font-size:10px; color:var(--sa-muted); margin-top:4px; font-family:monospace;"
                                         x-text="'Target: ' + results[route.uri].resolved_url"></div>
                                </td>
                                <td style="padding: 10px 16px;">
                                    {{-- Loading state --}}
                                    <div x-show="results[route.uri] && results[route.uri].loading" style="display:flex; align-items:center; gap:8px;">
                                        <div class="ping-pulse-indicator animate-ping" style="background:#7c3aed;"></div>
                                        <span style="font-size:11.5px; color:#7c3aed; font-weight:700;">Checking...</span>
                                    </div>
                                    
                                    {{-- Result ready --}}
                                    <div x-show="results[route.uri] && !results[route.uri].loading">
                                        <template x-if="results[route.uri].status_code > 0">
                                            <span class="badge" 
                                                  :class="results[route.uri].status_code >= 500 ? 'badge-red' : (results[route.uri].status_code >= 400 ? 'badge-amber' : 'badge-green')"
                                                  x-text="results[route.uri].status_message">
                                            </span>
                                        </template>
                                        <template x-if="results[route.uri].status_code === 0">
                                            <span class="badge badge-red" x-text="results[route.uri].status_message"></span>
                                        </template>
                                    </div>

                                    {{-- Idle state --}}
                                    <div x-show="!results[route.uri]" style="color:#cbd5e1; font-size:11.5px; font-weight:700; display:flex; align-items:center; gap:6px;">
                                        <span style="width:6px; height:6px; border-radius:50%; background:#cbd5e1;"></span>
                                        Pending
                                    </div>
                                </td>
                                <td style="padding: 10px 16px; font-family:monospace; font-weight:700; font-size:12px;">
                                    <div x-show="results[route.uri] && !results[route.uri].loading && results[route.uri].response_time_ms">
                                        <span :class="results[route.uri].response_time_ms > 1500 ? 'text-red-500' : 'text-slate-700'"
                                              x-text="results[route.uri].response_time_ms + ' ms'"></span>
                                    </div>
                                    <div x-show="!results[route.uri] || results[route.uri].loading" style="color:#cbd5e1;">&mdash;</div>
                                </td>
                                <td style="padding: 10px 16px; text-align: right;">
                                    <button type="button" @click="pingSingle(route.uri)" class="sa-btn sa-btn-ghost sa-btn-icon" style="padding: 5px;" title="Ping single endpoint">
                                        <svg style="width:12px; height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
