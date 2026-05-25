@extends('layouts.superadmin')

@section('header_title', 'School Instances')
@section('header_subtitle', 'Manage all registered school tenants')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add New School
    </a>
@endsection

@section('content')

@if(session('status'))
    @php preg_match('/Access it at: (\S+)/', session('status'), $m); $newHost = $m[1] ?? ''; @endphp
    <div style="margin-bottom:20px;padding:14px 18px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;">
        <div style="color:#166534;font-size:13px;font-weight:600;">&#10003; {{ session('status') }}</div>
        @if($newHost)
        <div style="margin-top:10px;padding:10px 14px;background:#1e293b;border-radius:8px;">
            <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Add this line to <strong style="color:#e2e8f0;">C:\Windows\System32\drivers\etc\hosts</strong> (open Notepad as Administrator):</div>
            <code style="font-family:monospace;font-size:13px;color:#86efac;">127.0.0.1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $newHost }}</code>
        </div>
        @endif
    </div>
@endif

{{-- Summary quick-stats --}}
<div class="sa-stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
    <div class="sa-stat-card orange" style="padding:14px 18px;">
        <div class="sa-stat-icon" style="width:40px;height:40px;border-radius:10px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-value" style="font-size:22px;">{{ $tenants->total() }}</div>
        </div>
    </div>
    <div class="sa-stat-card emerald" style="padding:14px 18px;">
        <div class="sa-stat-icon" style="width:40px;height:40px;border-radius:10px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Active</div>
            <div class="sa-stat-value" style="font-size:22px;">{{ $tenants->getCollection()->where('status','active')->count() }}</div>
        </div>
    </div>
    <div class="sa-stat-card rose" style="padding:14px 18px;">
        <div class="sa-stat-icon" style="width:40px;height:40px;border-radius:10px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Suspended</div>
            <div class="sa-stat-value" style="font-size:22px;">{{ $tenants->getCollection()->where('status','suspended')->count() }}</div>
        </div>
    </div>
</div>

{{-- Tenants Table --}}
<div class="sa-panel">
    <div class="sa-panel-header">
        <span class="sa-panel-title">All Schools ({{ $tenants->total() }})</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>School Name</th>
                    <th>Domain / Slug</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Subscription Due Date</th>
                    <th>Student / Teacher Cap</th>
                    <th>Created</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td>
                        <div style="font-weight:700;color:#1e293b;font-size:14px;">{{ $tenant->name }}</div>
                        @if($tenant->contact_email)
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;">{{ $tenant->contact_email }}</div>
                        @endif
                    </td>
                    <td>
                        @if($tenant->domain)
                            <a href="https://{{ $tenant->domain }}" target="_blank" 
                               style="color:#4f46e5;font-weight:600;font-size:13px;text-decoration:none;">
                                {{ $tenant->domain }}
                                <svg style="display:inline;width:10px;height:10px;vertical-align:1px;margin-left:3px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @else
                            @php $mainHost = parse_url(config('app.url'), PHP_URL_HOST); $subHost = $tenant->slug.'.'.$mainHost; @endphp
                            <a href="http://{{ $subHost }}" target="_blank"
                               style="font-family:monospace;font-size:12px;background:#f1f5f9;padding:2px 8px;border-radius:6px;color:#4f46e5;text-decoration:none;">
                                {{ $subHost }}
                            </a>
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
                        @if($tenant->expires_at)
                            <div style="font-size:12.5px; font-weight:700; color: {{ $tenant->expires_at->isPast() ? '#e11d48' : '#1e293b' }};">
                                {{ $tenant->expires_at->format('M j, Y') }}
                            </div>
                            <div style="font-size:10px; color:#94a3b8; font-weight: 600; text-transform: uppercase;">
                                @if($tenant->expires_at->isPast())
                                    <span style="color:#ef4444;">Expired {{ $tenant->expires_at->diffForHumans() }}</span>
                                @else
                                    <span>Expires {{ $tenant->expires_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        @else
                            <span style="font-size:12.5px; color:#94a3b8; font-style:italic;">No Expiry Set</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:12.5px;">
                            <span style="font-weight:700;color:#1e293b;">{{ number_format($tenant->max_students) }}</span>
                            <span style="color:#94a3b8;"> students</span>
                        </div>
                        <div style="font-size:12.5px;">
                            <span style="font-weight:700;color:#1e293b;">{{ number_format($tenant->max_teachers) }}</span>
                            <span style="color:#94a3b8;"> teachers</span>
                        </div>
                    </td>
                    <td style="color:#94a3b8;font-size:12.5px;white-space:nowrap;">
                        {{ $tenant->created_at->format('M j, Y') }}
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                            <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                               class="sa-btn sa-btn-ghost sa-btn-icon" title="Edit">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST" 
                                  onsubmit="return confirm('Permanently delete {{ addslashes($tenant->name) }}? This cannot be undone.')"
                                  style="display: inline-block; margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sa-btn sa-btn-danger sa-btn-icon" title="Delete">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:56px 24px;">
                        <svg style="width:44px;height:44px;color:#cbd5e1;margin:0 auto 14px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div style="font-size:15px;font-weight:700;color:#475569;margin-bottom:8px;">No schools found</div>
                        <div style="font-size:13px;color:#94a3b8;margin-bottom:18px;">Get started by creating your first school instance.</div>
                        <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary">Create New School</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
        <div style="padding:16px 22px;border-top:1px solid #f1f5f9;">
            {{ $tenants->links() }}
        </div>
    @endif
</div>

@endsection
