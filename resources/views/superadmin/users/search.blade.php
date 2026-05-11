@extends('layouts.superadmin')

@section('header_title', 'Global User Search')
@section('header_subtitle', 'Find any user across all schools')

@section('content')

<div class="sa-panel" style="margin-bottom:20px;">
    <div style="padding:20px 24px;">
        <form method="GET" action="{{ route('superadmin.users.search') }}" style="display:flex;gap:10px;">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search by name or email..."
                   autofocus
                   style="flex:1;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;">
            <button type="submit" class="sa-btn sa-btn-primary">Search</button>
        </form>
    </div>
</div>

@if($q && $results->isEmpty())
    <div class="sa-panel" style="padding:40px;text-align:center;color:#94a3b8;font-size:14px;">
        No users found for <strong style="color:#475569;">{{ $q }}</strong>
    </div>
@elseif($results->isNotEmpty())
    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">{{ $results->count() }} result(s) for "{{ $q }}"</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>School</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $user)
                    <tr>
                        <td>
                            <div style="font-weight:700;color:#1e293b;">{{ $user->name }}</div>
                            <div style="font-size:12px;color:#94a3b8;">{{ $user->email }}</div>
                        </td>
                        <td>
                            <span class="sa-badge {{ $user->role }}" style="text-transform:capitalize;">{{ $user->role }}</span>
                        </td>
                        <td>
                            @if($user->tenant)
                                <div style="font-size:13px;font-weight:600;color:#1e293b;">{{ $user->tenant->name }}</div>
                                <div style="font-size:11px;color:#94a3b8;font-family:monospace;">{{ $user->tenant->slug }}</div>
                            @else
                                <span style="color:#94a3b8;font-size:12px;">No school</span>
                            @endif
                        </td>
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;padding:3px 8px;border-radius:99px;
                                background:{{ $user->is_active ? '#dcfce7' : '#fee2e2' }};
                                color:{{ $user->is_active ? '#166534' : '#991b1b' }};">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            @if($user->tenant)
                                @php
                                    $mainHost = parse_url(config('app.url'), PHP_URL_HOST);
                                    $subHost  = $user->tenant->slug . '.' . $mainHost;
                                @endphp
                                <a href="http://{{ $subHost }}/dashboard" target="_blank"
                                   class="sa-btn sa-btn-ghost" style="font-size:12px;">
                                    Visit School →
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
