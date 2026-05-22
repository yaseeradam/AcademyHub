@extends('layouts.superadmin')

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
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($components as $component)
                    <tr>
                        <td style="font-weight:700; color:var(--sa-text);">
                            <div style="display:flex; align-items:center; gap:8px;">
                                @if($component->icon)
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
@endsection
