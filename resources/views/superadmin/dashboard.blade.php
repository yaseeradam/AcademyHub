@extends('layouts.superadmin')

@section('header_title', 'Dashboard')
@section('header_subtitle', 'System health · Multi-tenant overview')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New School
    </a>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     ROW 1 — Primary stat cards
═══════════════════════════════════════════════════════════════ --}}
<div class="sa-stats-grid" style="margin-bottom:24px;">

    <div class="sa-stat-card orange">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Schools</div>
            <div class="sa-stat-value">{{ number_format($stats['total_tenants']) }}</div>
            <div class="sa-stat-sub">Registered instances</div>
        </div>
    </div>

    <div class="sa-stat-card emerald">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Active Schools</div>
            <div class="sa-stat-value">{{ number_format($stats['active_tenants']) }}</div>
            <div class="sa-stat-sub">
                <span style="width:7px;height:7px;background:currentColor;border-radius:50%;display:inline-block;margin-right:4px;"></span>Live &amp; running
            </div>
        </div>
    </div>

    <div class="sa-stat-card indigo">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Pending Setup</div>
            <div class="sa-stat-value">{{ number_format($stats['pending_tenants']) }}</div>
            <div class="sa-stat-sub">Awaiting activation</div>
        </div>
    </div>

    <div class="sa-stat-card rose">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Suspended</div>
            <div class="sa-stat-value">{{ number_format($stats['suspended_tenants']) }}</div>
            <div class="sa-stat-sub">Disabled instances</div>
        </div>
    </div>

    <div class="sa-stat-card teal">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Users</div>
            <div class="sa-stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="sa-stat-sub">Across all schools</div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     ROW 2 — Cross-tenant stats
═══════════════════════════════════════════════════════════════ --}}
<div class="sa-stats-grid" style="margin-bottom:24px;">

    <div class="sa-stat-card teal">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Students</div>
            <div class="sa-stat-value">{{ number_format($stats['total_students']) }}</div>
            <div class="sa-stat-sub">Across all schools</div>
        </div>
    </div>

    <div class="sa-stat-card indigo">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Teachers</div>
            <div class="sa-stat-value">{{ number_format($stats['total_teachers']) }}</div>
            <div class="sa-stat-sub">Across all schools</div>
        </div>
    </div>

    <div class="sa-stat-card orange">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Exams</div>
            <div class="sa-stat-value">{{ number_format($stats['total_exams']) }}</div>
            <div class="sa-stat-sub">CBT exams created</div>
        </div>
    </div>

    <div class="sa-stat-card emerald">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Revenue</div>
            <div class="sa-stat-value">₦{{ number_format($stats['total_revenue']) }}</div>
            <div class="sa-stat-sub">All transactions</div>
        </div>
    </div>

    <div class="sa-stat-card rose">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Dormant Schools</div>
            <div class="sa-stat-value">{{ number_format($stats['dormant_tenants']) }}</div>
            <div class="sa-stat-sub">No login in 30+ days</div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     ROW 3 — Growth chart + Plan breakdown
═══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;margin-bottom:24px;">

    {{-- Growth Chart --}}
    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">
                <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                School Instance Growth
            </span>
            <span style="font-size:12px;color:#94a3b8;font-weight:500;">Last 6 months</span>
        </div>
        <div style="height:240px;padding:16px 16px 8px;">
            <canvas id="growthChart"></canvas>
        </div>
    </div>

    {{-- Plan Breakdown --}}
    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">Plan Breakdown</span>
        </div>
        <div style="padding:20px 22px;">
            <div style="position:relative;width:150px;height:150px;margin:0 auto 20px;">
                <canvas id="planChart"></canvas>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;">
                    <div style="font-size:26px;font-weight:800;color:#1e293b;line-height:1;">{{ $stats['total_tenants'] }}</div>
                    <div style="font-size:10px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;">Total</div>
                </div>
            </div>
            @php
                $planTotal = $stats['total_tenants'] ?: 1;
                $plans = [
                    ['Free',       $stats['free_tenants'],       '#94a3b8'],
                    ['Pro',        $stats['pro_tenants'],        '#3b82f6'],
                    ['Enterprise', $stats['enterprise_tenants'], '#8b5cf6'],
                ];
            @endphp
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($plans as [$label, $count, $color])
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                    <span style="font-size:13px;font-weight:600;color:#475569;flex:1;">{{ $label }}</span>
                    <span style="font-size:13px;font-weight:700;color:#1e293b;">{{ number_format($count) }}</span>
                    <span style="font-size:11px;color:#94a3b8;width:34px;text-align:right;">{{ round($count / $planTotal * 100) }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     ROW 4 — Recently Added Schools
═══════════════════════════════════════════════════════════════ --}}
<div class="sa-panel" style="margin-bottom:24px;">
    <div class="sa-panel-header">
        <span class="sa-panel-title">
            <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Recently Added Schools
        </span>
        <a href="{{ route('superadmin.tenants.index') }}" class="sa-panel-link">View All →</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>School Name</th>
                    <th>Domain / Slug</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Limits</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                <tr>
                    <td>
                        <div style="font-weight:700;color:#1e293b;">{{ $tenant->name }}</div>
                        @if($tenant->contact_email)
                            <div style="font-size:11.5px;color:#94a3b8;">{{ $tenant->contact_email }}</div>
                        @endif
                    </td>
                    <td>
                        @if($tenant->domain)
                            <span style="color:#4f46e5;font-weight:600;font-size:13px;">{{ $tenant->domain }}</span>
                        @else
                            <span style="font-family:monospace;font-size:12px;background:#f1f5f9;padding:2px 8px;border-radius:6px;color:#475569;">{{ $tenant->slug }}</span>
                        @endif
                    </td>
                    <td><span class="sa-badge {{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span></td>
                    <td>
                        <span class="sa-badge {{ $tenant->status }}">
                            <span class="sa-badge-dot"></span>{{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px;color:#475569;">{{ number_format($tenant->max_students) }} stu / {{ number_format($tenant->max_teachers) }} tea</td>
                    <td style="color:#94a3b8;font-size:12.5px;">{{ $tenant->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px;color:#94a3b8;">
                        <svg style="width:40px;height:40px;color:#cbd5e1;margin:0 auto 12px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div style="font-weight:600;font-size:14px;color:#475569;margin-bottom:12px;">No schools yet</div>
                        <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary" style="display:inline-flex;">Create First School</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     ROW 5 — Renewals + Storage
═══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">
                <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Upcoming Renewals
            </span>
            <span style="font-size:11px;color:#94a3b8;">Next 30 days</span>
        </div>
        @if(empty($upcomingRenewals))
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px;">
                <svg style="width:32px;height:32px;color:#d1fae5;margin:0 auto 10px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                No renewals due in the next 30 days.
            </div>
        @else
        <table class="sa-table">
            <thead><tr><th>School</th><th>Due Date</th><th>Days Left</th></tr></thead>
            <tbody>
            @foreach($upcomingRenewals as $r)
            <tr>
                <td style="font-weight:700;color:#1e293b;font-size:13px;">{{ $r['name'] }}</td>
                <td style="font-size:12px;color:#64748b;">{{ $r['due'] }}</td>
                <td>
                    <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;
                        background:{{ $r['days'] <= 7 ? '#fee2e2' : '#fef3c7' }};
                        color:{{ $r['days'] <= 7 ? '#991b1b' : '#92400e' }};">
                        {{ $r['days'] }}d
                    </span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">
                <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
                Storage Usage
            </span>
            <span style="font-size:11px;color:#94a3b8;">By school</span>
        </div>
        @if(empty($storageStats) || collect($storageStats)->sum('bytes') === 0)
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px;">
                <svg style="width:32px;height:32px;color:#e2e8f0;margin:0 auto 10px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                No uploads found.
            </div>
        @else
        <table class="sa-table">
            <thead><tr><th>School</th><th style="text-align:right;">Size</th></tr></thead>
            <tbody>
            @foreach(array_slice($storageStats, 0, 8) as $s)
            @if($s['bytes'] > 0)
            <tr>
                <td style="font-weight:600;color:#1e293b;font-size:13px;">{{ $s['name'] }}</td>
                <td style="text-align:right;font-size:12px;color:#64748b;font-family:monospace;">
                    @php $mb = round($s['bytes'] / 1048576, 1); @endphp
                    {{ $mb >= 1 ? $mb . ' MB' : round($s['bytes'] / 1024) . ' KB' }}
                </td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     ROW 6 — WhatsApp + Recent Errors
═══════════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:8px;">

    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">
                <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                WhatsApp Bot Activity
            </span>
        </div>
        @if(empty($whatsappStats))
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px;">
                <svg style="width:32px;height:32px;color:#e2e8f0;margin:0 auto 10px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                No schools have WhatsApp linked yet.
            </div>
        @else
        <table class="sa-table">
            <thead><tr><th>School</th><th style="text-align:right;">Linked Users</th></tr></thead>
            <tbody>
            @foreach($whatsappStats as $w)
            <tr>
                <td style="font-weight:600;color:#1e293b;font-size:13px;">{{ $w['name'] }}</td>
                <td style="text-align:right;">
                    <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;background:#dcfce7;color:#166534;">
                        {{ $w['linked'] }} users
                    </span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">
                <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Recent Errors
            </span>
            <span style="font-size:11px;color:#94a3b8;">Last 10 from laravel.log</span>
        </div>
        @if(empty($recentErrors))
            <div style="padding:32px;text-align:center;color:#94a3b8;font-size:13px;">
                <svg style="width:32px;height:32px;color:#d1fae5;margin:0 auto 10px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                No recent errors.
            </div>
        @else
        <div style="padding:12px 16px;display:flex;flex-direction:column;gap:8px;max-height:320px;overflow-y:auto;">
            @foreach($recentErrors as $err)
            <div style="padding:10px 12px;background:#fef2f2;border-left:3px solid #ef4444;border-radius:6px;">
                <div style="font-size:10px;color:#94a3b8;margin-bottom:3px;">{{ $err['time'] }}</div>
                <div style="font-size:11.5px;color:#991b1b;font-family:monospace;word-break:break-all;line-height:1.5;">{{ $err['message'] }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    // ── Growth Chart ──────────────────────────────────────────────
    const gCtx = document.getElementById('growthChart').getContext('2d');
    const gLabels = @json(collect($monthlyGrowth)->pluck('month'));
    const gData   = @json(collect($monthlyGrowth)->pluck('count'));

    const gGrad = gCtx.createLinearGradient(0, 0, 0, 200);
    gGrad.addColorStop(0, 'rgba(79,70,229,.22)');
    gGrad.addColorStop(1, 'rgba(79,70,229,0)');

    new Chart(gCtx, {
        type: 'line',
        data: {
            labels: gLabels,
            datasets: [{
                label: 'Schools Created',
                data: gData,
                fill: true,
                backgroundColor: gGrad,
                borderColor: '#4f46e5',
                borderWidth: 2.5,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: ctx => ` ${ctx.parsed.y} school${ctx.parsed.y !== 1 ? 's' : ''}`
            }}},
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
                    grid: { color: '#f1f5f9' },
                    border: { display: false },
                },
                x: {
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { display: false },
                    border: { display: false },
                }
            }
        }
    });

    // ── Plan Donut ────────────────────────────────────────────────
    const pCtx = document.getElementById('planChart').getContext('2d');
    const free = {{ $stats['free_tenants'] }};
    const pro  = {{ $stats['pro_tenants'] }};
    const ent  = {{ $stats['enterprise_tenants'] }};

    new Chart(pCtx, {
        type: 'doughnut',
        data: {
            labels: ['Free', 'Pro', 'Enterprise'],
            datasets: [{
                data: [free || 0.001, pro || 0.001, ent || 0.001],
                backgroundColor: ['#94a3b8', '#3b82f6', '#8b5cf6'],
                borderWidth: 0,
                hoverOffset: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '74%',
            plugins: { legend: { display: false } }
        }
    });
})();
</script>
@endpush
