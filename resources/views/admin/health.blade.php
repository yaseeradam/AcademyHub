@extends('layouts.app')

@section('content')
<style>
    .diagnostic-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 8px 0;
    }
    .welcome-banner {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 24px;
        padding: 32px;
        color: #ffffff;
        margin-bottom: 32px;
        box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        position: relative;
        overflow: hidden;
    }
    .welcome-banner::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        top: -100px;
        right: -100px;
    }
    .diagnose-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }
    .diag-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.2s ease-in-out;
        display: flex;
        gap: 20px;
        align-items: start;
    }
    .diag-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }
    .icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .icon-healthy {
        background: #f0fdf4;
        color: #10b981;
        border: 1px solid #bbf7d0;
    }
    .icon-unhealthy {
        background: #fef2f2;
        color: #ef4444;
        border: 1px solid #fecaca;
    }
    .pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 750;
        padding: 4px 10px;
        border-radius: 99px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .pill-healthy {
        background: #ecfdf5;
        color: #047857;
    }
    .pill-unhealthy {
        background: #fef2f2;
        color: #b91c1c;
    }
    .refresh-btn {
        background: #ffffff;
        color: #4f46e5;
        font-weight: 800;
        font-size: 13.5px;
        padding: 12px 24px;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        z-index: 10;
    }
    .refresh-btn:hover {
        background: #f8fafc;
        transform: scale(1.02);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="diagnostic-container">
    
    <!-- Header Banner -->
    <div class="welcome-banner">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; letter-spacing: -0.5px; margin-bottom: 6px;">
                School System Diagnostics
            </h1>
            <p style="font-size: 14px; color: rgba(255, 255, 255, 0.85); font-weight: 500; max-width: 600px;">
                Here is a simplified health status of your digital school portals. We verify your records database, parent notifications dispatcher, and security networks in real-time.
            </p>
        </div>
        <form action="{{ route('admin.health.diagnose') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="refresh-btn">
                <svg style="width:16px; height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3m0 0l3-3m-3 3V2"/>
                </svg>
                Run Diagnostics Check
            </button>
        </form>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; border-radius:16px; padding:16px 20px; margin-bottom:28px; font-size:14px; font-weight:700;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Health Cards Grid -->
    <div class="diagnose-grid">
        @foreach($diagnostics as $diag)
            <div class="diag-card">
                <!-- Icon container -->
                <div class="icon-wrapper {{ $diag['healthy'] ? 'icon-healthy' : 'icon-unhealthy' }}">
                    @if($diag['icon'] === 'database')
                        <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    @elseif($diag['icon'] === 'lightning')
                        <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    @elseif($diag['icon'] === 'folder')
                        <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    @elseif($diag['icon'] === 'mail')
                        <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    @elseif($diag['icon'] === 'credit-card')
                        <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    @else
                        <svg style="width:24px; height:24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>

                <!-- Text metadata -->
                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; gap:8px;">
                        <h3 style="font-size:16px; font-weight:800; color:#1e293b;">{{ $diag['name'] }}</h3>
                        <span class="pill {{ $diag['healthy'] ? 'pill-healthy' : 'pill-unhealthy' }}">
                            {{ $diag['status'] }}
                        </span>
                    </div>
                    
                    <p style="font-size:13px; color:#475569; line-height:1.5; font-weight:500; margin-bottom:10px;">
                        {{ $diag['description'] }}
                    </p>
                    
                    <div style="font-size:11px; color:#94a3b8; font-weight:600; display:flex; align-items:center; gap:4px;">
                        <svg style="width:12px; height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Technical indicator: {{ $diag['tech'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Supportive reassuring notice -->
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:18px; padding:24px; display:flex; gap:16px; align-items:start;">
        <div style="width:36px; height:36px; border-radius:50%; background:#e0e7ff; color:#4f46e5; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <svg style="width:18px; height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:800; color:#1e293b; margin-bottom:4px;">Need deeper details?</h4>
            <p style="font-size:12.5px; color:#64748b; font-weight:500; line-height:1.5;">
                If your system shows anything but green health lights, your local internet provider may be experiencing network fluctuations. Our superadmins are continually watching core logs and server metrics in the background. If you have questions, please reach out to your Dedicated Success Coordinator.
            </p>
        </div>
    </div>
</div>
@endsection
