@extends('layouts.superadmin')

@section('header_title', 'Dashboard')
@section('header_subtitle', 'System health · Multi-tenant overview')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New School
    </a>
@endsection

@section('content')

{{-- ── Stat Cards ─────────────────────────────────────────── --}}
<div class="sa-stats-grid">

    {{-- Total Schools --}}
    <div class="sa-stat-card orange">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Schools</div>
            <div class="sa-stat-value">{{ number_format($stats['total_tenants']) }}</div>
            <div class="sa-stat-sub">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                Registered instances
            </div>
        </div>
    </div>

    {{-- Active Schools --}}
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
                <span style="width:7px;height:7px;background:currentColor;border-radius:50%;display:inline-block;"></span>
                Live &amp; running
            </div>
        </div>
    </div>

    {{-- Pending --}}
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

    {{-- Total Users --}}
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

    {{-- Suspended --}}
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

</div>

{{-- ── Middle Row: Chart + Plan Breakdown ────────────────────── --}}
<div style="display:grid; grid-template-columns:1fr 340px; gap:20px; margin-bottom:20px;">

    {{-- Growth Chart --}}
    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">
                <svg style="display:inline; width:15px; height:15px; vertical-align:-2px; margin-right:6px; color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                School Instance Growth
            </span>
            <span style="font-size:12px; color:#94a3b8; font-weight:500;">Last 6 months</span>
        </div>
        <div class="sa-chart-wrap" style="height:260px; padding-top:16px;">
            <canvas id="growthChart"></canvas>
        </div>
    </div>

    {{-- Plan Breakdown --}}
    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">Plan Breakdown</span>
        </div>
        <div style="padding:20px 22px;">

            {{-- Donut chart --}}
            <div style="position:relative; width:160px; height:160px; margin:0 auto 20px;">
                <canvas id="planChart"></canvas>
                <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                    <div style="font-size:24px; font-weight:800; color:#1e293b;">{{ $stats['total_tenants'] }}</div>
                    <div style="font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">Total</div>
                </div>
            </div>

            {{-- Legend --}}
            <div style="display:flex; flex-direction:column; gap:10px;">
                @php
                    $total = $stats['total_tenants'] ?: 1;
                    $plans = [
                        ['Free', $stats['free_tenants'], '#94a3b8'],
                        ['Pro', $stats['pro_tenants'], '#3b82f6'],
                        ['Enterprise', $stats['enterprise_tenants'], '#8b5cf6'],
                    ];
                @endphp
                @foreach($plans as [$label, $count, $color])
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></span>
                    <span style="font-size:13px;font-weight:600;color:#475569;flex:1;">{{ $label }}</span>
                    <span style="font-size:13px;font-weight:700;color:#1e293b;">{{ number_format($count) }}</span>
                    <span style="font-size:11px;color:#94a3b8;width:36px;text-align:right;">{{ $total > 0 ? round($count / $total * 100) : 0 }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ── Recent Schools Table ────────────────────────────────────── --}}
<div class="sa-panel">
    <div class="sa-panel-header">
        <span class="sa-panel-title">
            <svg style="display:inline; width:15px; height:15px; vertical-align:-2px; margin-right:6px; color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                            <span style="color:#4f46e5;font-weight:600;">{{ $tenant->domain }}</span>
                        @else
                            <span style="font-family:monospace;font-size:12px;background:#f1f5f9;padding:2px 8px;border-radius:6px;color:#475569;">{{ $tenant->slug }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="sa-badge {{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span>
                    </td>
                    <td>
                        <span class="sa-badge {{ $tenant->status }}">
                            <span class="sa-badge-dot"></span>
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:12.5px;color:#475569;">{{ number_format($tenant->max_students) }} stu / {{ number_format($tenant->max_teachers) }} tea</span>
                    </td>
                    <td style="color:#94a3b8;font-size:12.5px;">{{ $tenant->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px;color:#94a3b8;">
                        <svg style="width:40px;height:40px;color:#cbd5e1;margin:0 auto 12px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div style="font-weight:600;font-size:14px;color:#475569;margin-bottom:8px;">No schools yet</div>
                        <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary" style="margin:0 auto;">Create First School</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Growth Chart
(function() {
    const ctx = document.getElementById('growthChart').getContext('2d');
    const labels = @json(collect($monthlyGrowth)->pluck('month'));
    const data   = @json(collect($monthlyGrowth)->pluck('count'));

    const gradient = ctx.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(79,70,229,.25)');
    gradient.addColorStop(1, 'rgba(79,70,229,0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Schools Created',
                data,
                fill: true,
                backgroundColor: gradient,
                borderColor: '#4f46e5',
                borderWidth: 2.5,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
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
})();

// Plan Donut Chart
(function() {
    const ctx = document.getElementById('planChart').getContext('2d');
    const free = {{ $stats['free_tenants'] }};
    const pro  = {{ $stats['pro_tenants'] }};
    const ent  = {{ $stats['enterprise_tenants'] }};

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Free', 'Pro', 'Enterprise'],
            datasets: [{
                data: [free || 0.001, pro || 0.001, ent || 0.001],
                backgroundColor: ['#94a3b8', '#3b82f6', '#8b5cf6'],
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { display: false } }
        }
    });
})();
</script>
@endpush
