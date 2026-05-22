@extends('layouts.superadmin')

@section('header_title', 'Endpoint Diagnostics')
@section('header_subtitle', 'Real-time routing diagnostics & health checks')

@section('header_actions')
    <a href="{{ route('superadmin.health') }}" class="sa-btn sa-btn-ghost">
        &larr; General System Health
    </a>
@endsection

@section('content')
<div x-data="{
    routes: @js($routes),
    searchQuery: '',
    results: {},
    currentIndex: -1,
    pinging: false,
    
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
        
        for (let i = 0; i < this.routes.length; i++) {
            this.currentIndex = i;
            const route = this.routes[i];
            
            // Set loading state
            this.results[route.uri] = { loading: true };
            
            try {
                const response = await fetch('{{ route('superadmin.health.ping-endpoint') }}?uri=' + encodeURIComponent(route.uri), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                this.results[route.uri] = data;
            } catch (err) {
                this.results[route.uri] = {
                    status_code: 0,
                    response_time_ms: 0,
                    status_message: 'JS Request Error: ' + err.message
                };
            }
            
            // Small delay to prevent network throttling and ensure sequential animation smoothness
            await new Promise(resolve => setTimeout(resolve, 80));
        }
        
        this.pinging = false;
        this.currentIndex = -1;
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
}" class="health-monitor-layout" style="max-width: 1200px; margin: 0 auto;">

    {{-- ── Diagnostics Control Header ────────────────────────── --}}
    <div class="sa-panel mb-6" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white; border: none; overflow: hidden; position: relative;">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 rounded-full bg-violet-600/10 blur-2xl"></div>
        <div class="absolute -left-16 -top-16 w-64 h-64 rounded-full bg-teal-500/5 blur-2xl"></div>
        
        <div style="padding: 28px; position: relative; z-index: 10;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom: 24px;">
                <div>
                    <h2 style="font-size: 22px; font-weight:800; margin:0 0 6px;">Live Routing Health Engine</h2>
                    <p style="font-size: 13px; color: #94a3b8; margin: 0; max-width: 600px; line-height: 1.5;">
                        Runs sequential local curl diagnostics on school routes by dynamically injecting mock active database IDs for wildcards (e.g. <span style="font-family:monospace; color:#a7f3d0;">{student}</span>).
                    </p>
                </div>
                <div>
                    <button type="button" @click="pingAll()" :disabled="pinging" class="sa-btn" 
                            style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color:white; padding:12px 24px; font-size:14px; box-shadow:0 10px 20px rgba(79,70,229,0.3);"
                            :style="pinging ? 'opacity:0.6; cursor:not-allowed;' : ''">
                        <svg class="h-5 w-5" :class="pinging ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 12H16" />
                        </svg>
                        <span x-text="pinging ? 'Analyzing Routing Suite...' : 'Initialize Endpoint Ping Suite'"></span>
                    </button>
                </div>
            </div>

            {{-- Progress Indicators --}}
            <div x-show="pinging || completedCount > 0" class="progress-section" style="animation: slideDown 0.3s ease-out;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px; font-size:12px; font-weight:700;">
                    <span style="color:#cbd5e1;">Progress: <span x-text="completedCount" style="color:white;"></span> / <span x-text="totalCount"></span> endpoints analyzed</span>
                    <span x-text="progressPercent + '%'" style="color: #a7f3d0;"></span>
                </div>
                <div class="progress-bar-bg" style="width:100%; height:8px; background:rgba(255,255,255,0.08); border-radius:999px; overflow:hidden; margin-bottom:16px;">
                    <div class="progress-bar-fill" :style="'width: ' + progressPercent + '%'" style="height:100%; background: linear-gradient(90deg, #10b981, #3b82f6); border-radius:999px; transition: width 0.15s ease-out;"></div>
                </div>

                <div style="display:flex; gap:16px; font-size:11.5px; font-weight:700;">
                    <span style="display:inline-flex; align-items:center; gap:6px; color:#a7f3d0;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#10b981;"></span>
                        Reachable: <span x-text="successCount"></span>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; color:#fecdd3;">
                        <span style="width:7px; height:7px; border-radius:50%; background:#ef4444;"></span>
                        Errors/500s: <span x-text="errorCount"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Search and Filter Controls ────────────────────────── --}}
    <div class="sa-panel mb-6" style="padding: 16px 20px;">
        <div style="display:flex; gap:12px; align-items:center;">
            <div style="position:relative; flex:1;">
                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8;">
                    <svg style="width:18px; height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" x-model="searchQuery" class="sa-form-input" placeholder="Search endpoints by name, URL route, or dynamic parameters..." style="padding-left:42px;">
            </div>
            <div style="font-size:12px; color:var(--sa-muted); font-weight:700; background:#f1f5f9; padding:10px 16px; border-radius:10px; white-space:nowrap;">
                Showing <span x-text="filteredRoutes.length" style="color:var(--sa-text);"></span> endpoints
            </div>
        </div>
    </div>

    {{-- ── Endpoints Table ───────────────────────────────────── --}}
    <div class="sa-panel">
        <div style="overflow-x: auto;">
            <table class="sa-table" style="vertical-align: middle;">
                <thead>
                    <tr>
                        <th style="width: 25%;">Route / Alias</th>
                        <th style="width: 35%;">URI Template</th>
                        <th style="width: 20%;">Diagnostic Status</th>
                        <th style="width: 12%;">Latency</th>
                        <th style="width: 8%; text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(route, index) in filteredRoutes" :key="route.uri">
                        <tr class="route-table-row" :class="currentIndex === index ? 'currently-pinging' : ''">
                            <td>
                                <div style="font-weight: 700; color: var(--sa-text); font-size:13.5px;" x-text="route.name"></div>
                                <div style="font-size:11.5px; color:var(--sa-muted); margin-top:2px;" x-text="route.action"></div>
                            </td>
                            <td>
                                <span class="uri-badge" x-text="route.uri"></span>
                                <div x-show="results[route.uri] && results[route.uri].resolved_url" 
                                     style="font-size:10.5px; color:var(--sa-muted); margin-top:5px; font-family:monospace;"
                                     x-text="'Ping Target: ' + results[route.uri].resolved_url"></div>
                            </td>
                            <td>
                                {{-- Loading state --}}
                                <div x-show="results[route.uri] && results[route.uri].loading" style="display:flex; align-items:center; gap:8px;">
                                    <div class="ping-pulse-indicator animate-ping"></div>
                                    <span style="font-size:11.5px; color:var(--sa-primary); font-weight:700;">Pinging...</span>
                                </div>
                                
                                {{-- Result ready state --}}
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

                                {{-- Idle State --}}
                                <div x-show="!results[route.uri]" style="color:#cbd5e1; font-size:12px; font-weight:700; display:flex; align-items:center; gap:6px;">
                                    <span style="width:6px; height:6px; border-radius:50%; background:#cbd5e1;"></span>
                                    Pending Check
                                </div>
                            </td>
                            <td style="font-family:monospace; font-weight:700;">
                                <div x-show="results[route.uri] && !results[route.uri].loading && results[route.uri].response_time_ms">
                                    <span :class="results[route.uri].response_time_ms > 1500 ? 'text-red-500' : 'text-slate-700'"
                                          x-text="results[route.uri].response_time_ms + ' ms'"></span>
                                </div>
                                <div x-show="!results[route.uri] || results[route.uri].loading" style="color:#cbd5e1;">&mdash;</div>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" @click="pingSingle(route.uri)" class="sa-btn sa-btn-ghost sa-btn-icon" title="Ping endpoint singly">
                                    <svg style="width:13px; height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    /* Premium Health Console styling */
    .health-monitor-layout {
        animation: fadeScale 0.25s ease-out;
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
        background: var(--sa-primary);
    }

    /* Badges */
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

    /* Table animation */
    .route-table-row {
        transition: background 0.2s, border-left 0.2s;
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

    @keyframes fadeScale {
        from {
            opacity: 0;
            transform: scale(0.99);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
@endsection
