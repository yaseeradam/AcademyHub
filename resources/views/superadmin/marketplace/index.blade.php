@extends('layouts.superadmin')

@php
    $iconSvgs = [
        'whatsapp'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
        'student'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5"/></svg>',
        'exam'            => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'finance'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'messages'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'document'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
        'parent-portal'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'e-learning'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
    ];
    $iconColors = [
        'whatsapp'      => '#22c55e',
        'student'       => '#6366f1',
        'exam'          => '#a855f7',
        'finance'       => '#14b8a6',
        'messages'      => '#f59e0b',
        'document'      => '#38bdf8',
        'parent-portal' => '#ec4899',
        'e-learning'    => '#3b82f6',
    ];
@endphp

@section('header_title', 'Marketplace Control Center')
@section('header_subtitle', 'Manage available marketplace components, pricing, and active status.')

@section('header_actions')
    <a href="{{ route('superadmin.marketplace.create') }}" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add New Component
    </a>
@endsection

@section('content')
<div class="sa-panel">
    <div class="sa-panel-header">
        <span class="sa-panel-title">All Components</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Price</th>
                    <th>Installs</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($components as $component)
                    <tr>
                        <td style="font-weight:700; color:var(--sa-text);">
                            <div style="display:flex; align-items:center; gap:10px;">
                                @php
                                    $iconKey = $iconSvgs[$component->icon] ?? $iconSvgs[$component->slug] ?? null;
                                    $iconColor = $iconColors[$component->icon] ?? $iconColors[$component->slug] ?? '#6b7280';
                                @endphp
                                @if(!empty($component->icon) && str_contains($component->icon, '<svg'))
                                    <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#7c3aed20; color:#7c3aed; flex-shrink:0;">
                                        <span class="superadmin-svg-icon" style="width:18px; height:18px; display:flex; align-items:center; justify-content:center;">{!! $component->icon !!}</span>
                                    </span>
                                @elseif($iconKey)
                                    <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:{{ $iconColor }}20; color:{{ $iconColor; }}; flex-shrink:0;">
                                        <span style="width:18px; height:18px; display:flex;">{!! $iconKey !!}</span>
                                    </span>
                                @elseif($component->icon)
                                    <span style="font-size:18px;">{!! $component->icon !!}</span>
                                @endif
                                {{ $component->name }}
                            </div>
                        </td>
                        <td style="font-family:monospace; color:var(--sa-muted);">{{ $component->slug }}</td>
                        <td>
                            @if($component->setup_fee > 0 || $component->usage_fee_per_student > 0)
                                <div style="font-weight:700; color:var(--sa-text); font-size:13px;">
                                    Setup: {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->setup_fee, 2) }}
                                </div>
                                <div style="font-size:11px; color:var(--sa-muted); margin-top:2px;">
                                    Usage: {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->usage_fee_per_student, 2) }}/student
                                </div>
                            @else
                                <span class="sa-badge free">Free</span>
                            @endif
                        </td>
                        <td>
                            <strong style="color:var(--sa-text); font-size:13px;">{{ $component->real_installs }}</strong> 
                            <span style="font-size:11px; color:var(--sa-muted);">{{ Str::plural('install', $component->real_installs) }}</span>
                        </td>
                        <td>
                            @if($component->real_rating_count > 0)
                                <div style="display:flex; align-items:center; gap:4px; font-weight:700; color:#b45309; font-size:13px;">
                                    ★ {{ number_format($component->real_rating_avg, 1) }}
                                    <span style="font-weight:normal; color:var(--sa-muted); font-size:11px;">({{ $component->real_rating_count }})</span>
                                </div>
                            @else
                                <span style="font-size:11px; color:var(--sa-muted); font-style:italic;">No reviews</span>
                            @endif
                        </td>
                        <td>
                            @if($component->is_active)
                                <span class="sa-badge active"><span class="sa-badge-dot"></span>Active</span>
                            @else
                                <span class="sa-badge suspended"><span class="sa-badge-dot"></span>Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <a href="{{ route('superadmin.marketplace.edit', $component) }}" class="sa-btn sa-btn-ghost sa-btn-icon" title="Edit">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <form action="{{ route('superadmin.marketplace.destroy', $component) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this component?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-icon" title="Delete">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:var(--sa-muted);">
                            No marketplace components found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .superadmin-svg-icon svg {
        width: 18px !important;
        height: 18px !important;
        max-width: 100% !important;
        max-height: 100% !important;
        display: inline-block !important;
    }
</style>
@endsection

