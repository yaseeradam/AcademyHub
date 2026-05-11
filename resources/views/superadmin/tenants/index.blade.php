@extends('layouts.superadmin')

@section('header_title', 'School Instances')
@section('header_subtitle', 'Manage all registered school tenants')

@section('header_actions')
    {{-- Broadcast --}}
    <button onclick="document.getElementById('broadcastModal').style.display='flex'" class="sa-btn sa-btn-ghost">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        Broadcast
    </button>
    <a href="{{ route('superadmin.tenants.create') }}" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
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
            <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Add to <strong style="color:#e2e8f0;">C:\Windows\System32\drivers\etc\hosts</strong>:</div>
            <code style="font-family:monospace;font-size:13px;color:#86efac;">127.0.0.1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $newHost }}</code>
        </div>
        @endif
    </div>
@endif

@if(session('error'))
    <div style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#991b1b;font-size:13px;font-weight:600;">
        &#10007; {{ session('error') }}
    </div>
@endif

{{-- Summary stats --}}
<div class="sa-stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
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
                    <th>Health</th>
                    <th>Limits</th>
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
                        @php $mainHost = parse_url(config('app.url'), PHP_URL_HOST); $subHost = $tenant->domain ?: ($tenant->slug.'.'.$mainHost); @endphp
                        <a href="http://{{ $subHost }}" target="_blank"
                           style="font-family:monospace;font-size:12px;background:#f1f5f9;padding:2px 8px;border-radius:6px;color:#4f46e5;text-decoration:none;">
                            {{ $subHost }}
                        </a>
                    </td>
                    <td><span class="sa-badge {{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span></td>
                    <td>
                        <span class="sa-badge {{ $tenant->status }}">
                            <span class="sa-badge-dot"></span>
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td>
                        {{-- Health indicators loaded via JS --}}
                        <div id="health-{{ $tenant->id }}" style="display:flex;gap:4px;flex-wrap:wrap;">
                            <span style="font-size:11px;color:#94a3b8;">Loading...</span>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:12.5px;">
                            <span style="font-weight:700;color:#1e293b;">{{ number_format($tenant->max_students) }}</span>
                            <span style="color:#94a3b8;"> stu</span>
                        </div>
                        <div style="font-size:12.5px;">
                            <span style="font-weight:700;color:#1e293b;">{{ number_format($tenant->max_teachers) }}</span>
                            <span style="color:#94a3b8;"> tea</span>
                        </div>
                    </td>
                    <td style="color:#94a3b8;font-size:12.5px;white-space:nowrap;">
                        {{ $tenant->created_at->format('M j, Y') }}
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">

                            {{-- Impersonate --}}
                            <form id="impersonate-{{ $tenant->id }}" action="{{ route('superadmin.tenants.impersonate', $tenant) }}" method="POST">
                                @csrf
                                <button type="button"
                                        onclick="saConfirm('impersonate-{{ $tenant->id }}', 'Login as admin of {{ addslashes($tenant->name) }}?')"
                                        class="sa-btn sa-btn-ghost sa-btn-icon" title="Login as Admin"
                                        style="background:#ede9fe;color:#7c3aed;border:none;">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </form>

                            {{-- Manage (opens panel) --}}
                            <button onclick="openManage({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                    class="sa-btn sa-btn-ghost sa-btn-icon" title="Manage"
                                    style="background:#dbeafe;color:#1d4ed8;border:none;">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>

                            {{-- Edit --}}
                            <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                               class="sa-btn sa-btn-ghost sa-btn-icon" title="Edit">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>

                            {{-- Delete --}}
                            <form id="delete-{{ $tenant->id }}" action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button"
                                        onclick="saConfirm('delete-{{ $tenant->id }}', 'Permanently delete {{ addslashes($tenant->name) }}? This cannot be undone.')"
                                        class="sa-btn sa-btn-danger sa-btn-icon" title="Delete">
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
                    <td colspan="8" style="text-align:center;padding:56px 24px;">
                        <div style="font-size:15px;font-weight:700;color:#475569;margin-bottom:8px;">No schools found</div>
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

{{-- ── Manage Panel (slide-in) ──────────────────────────────── --}}
<div id="managePanel" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);" onclick="if(event.target===this)closeManage()">
    <div style="position:absolute;right:0;top:0;bottom:0;width:480px;background:#fff;overflow-y:auto;box-shadow:-4px 0 24px rgba(0,0,0,.15);">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:16px;font-weight:800;color:#1e293b;" id="managePanelTitle">Manage School</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Subscription · Features · Actions</div>
            </div>
            <button onclick="closeManage()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;">&times;</button>
        </div>

        <div style="padding:20px 24px;space-y:20px;" id="managePanelBody">
            {{-- Subscription --}}
            @foreach($tenants as $tenant)
            <div id="manage-{{ $tenant->id }}" style="display:none;">

                {{-- Subscription Section --}}
                <div style="margin-bottom:24px;">
                    <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:12px;">Subscription</div>
                    <form id="sub-{{ $tenant->id }}" action="{{ route('superadmin.tenants.subscription', $tenant) }}" method="POST">
                        @csrf
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Plan</label>
                                <select name="plan" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                                    <option value="free" {{ $tenant->plan==='free' ? 'selected' : '' }}>Free</option>
                                    <option value="pro" {{ $tenant->plan==='pro' ? 'selected' : '' }}>Pro</option>
                                    <option value="enterprise" {{ $tenant->plan==='enterprise' ? 'selected' : '' }}>Enterprise</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Status</label>
                                <select name="status" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                                    <option value="active" {{ $tenant->status==='active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ $tenant->status==='suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="pending" {{ $tenant->status==='pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Max Students</label>
                                <input type="number" name="max_students" value="{{ $tenant->max_students }}" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Max Teachers</label>
                                <input type="number" name="max_teachers" value="{{ $tenant->max_teachers }}" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                            </div>
                            <div style="grid-column:span 2;">
                                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Subscription Expiry</label>
                                <input type="date" name="subscription_due_date" style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                            </div>
                        </div>
                        <button type="button" onclick="saConfirm('sub-{{ $tenant->id }}', 'Update subscription for {{ addslashes($tenant->name) }}?')"
                            class="sa-btn sa-btn-primary" style="width:100%;">Save Subscription</button>
                    </form>
                </div>

                {{-- Feature Flags --}}
                <div style="margin-bottom:24px;">
                    <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:12px;">Feature Flags</div>
                    <form id="feat-{{ $tenant->id }}" action="{{ route('superadmin.tenants.features', $tenant) }}" method="POST">
                        @csrf
                        @php
                            $settingsPath = storage_path('app/myacademy/tenants/'.$tenant->id.'/settings.json');
                            $tSettings = file_exists($settingsPath) ? (json_decode(file_get_contents($settingsPath), true) ?: []) : [];
                        @endphp
                        @foreach([
                            'feature_cbt'           => ['CBT Exams',       '#7c3aed'],
                            'feature_whatsapp'       => ['WhatsApp Bot',    '#16a34a'],
                            'feature_parent_portal'  => ['Parent Portal',   '#0369a1'],
                            'feature_ai'             => ['AI Features',     '#9333ea'],
                            'feature_analytics'      => ['Analytics',       '#0891b2'],
                            'feature_billing'        => ['Billing Module',  '#b45309'],
                        ] as $flag => [$label, $color])
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#f8fafc;border-radius:8px;margin-bottom:6px;cursor:pointer;">
                            <span style="font-size:13px;font-weight:600;color:#1e293b;">{{ $label }}</span>
                            <input type="checkbox" name="{{ $flag }}" value="1"
                                   {{ ($tSettings[$flag] ?? true) ? 'checked' : '' }}
                                   style="width:16px;height:16px;accent-color:{{ $color }};">
                        </label>
                        @endforeach
                        <button type="button" onclick="saConfirm('feat-{{ $tenant->id }}', 'Update feature flags for {{ addslashes($tenant->name) }}?')"
                            class="sa-btn sa-btn-primary" style="width:100%;margin-top:8px;">Save Features</button>
                    </form>
                </div>

                {{-- More Actions --}}
                <div style="margin-bottom:16px;">
                    <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:12px;">Actions</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">

                        {{-- Force Password Reset --}}
                        <form id="freset-{{ $tenant->id }}" action="{{ route('superadmin.tenants.force-reset', $tenant) }}" method="POST">
                            @csrf
                            <button type="button"
                                    onclick="saConfirm('freset-{{ $tenant->id }}', 'Force all users of {{ addslashes($tenant->name) }} to reset passwords?')"
                                    style="width:100%;padding:10px;background:#fefce8;border:1.5px solid #fde68a;border-radius:8px;color:#92400e;font-size:13px;font-weight:700;cursor:pointer;text-align:left;">
                                🔑 Force Password Reset
                            </button>
                        </form>

                        {{-- Trigger Backup --}}
                        <form id="backup-{{ $tenant->id }}" action="{{ route('superadmin.tenants.backup', $tenant) }}" method="POST">
                            @csrf
                            <button type="button"
                                    onclick="saConfirm('backup-{{ $tenant->id }}', 'Request backup for {{ addslashes($tenant->name) }}?')"
                                    style="width:100%;padding:10px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:8px;color:#1e40af;font-size:13px;font-weight:700;cursor:pointer;text-align:left;">
                                💾 Request Backup
                            </button>
                        </form>

                        {{-- Clone School --}}
                        <form id="clone-{{ $tenant->id }}" action="{{ route('superadmin.tenants.clone', $tenant) }}" method="POST">
                            @csrf
                            <button type="button"
                                    onclick="saConfirm('clone-{{ $tenant->id }}', 'Clone {{ addslashes($tenant->name) }}? A new pending school will be created.')"
                                    style="width:100%;padding:10px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:8px;color:#166534;font-size:13px;font-weight:700;cursor:pointer;text-align:left;">
                                📋 Clone School
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div style="border:1.5px solid #fecaca;border-radius:10px;padding:16px;">
                    <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#dc2626;margin-bottom:12px;">⚠ Danger Zone</div>
                    <form id="reset-{{ $tenant->id }}" action="{{ route('superadmin.tenants.reset', $tenant) }}" method="POST">
                        @csrf
                        <button type="button"
                                onclick="saConfirm('reset-{{ $tenant->id }}', '⚠ Reset ALL data for {{ addslashes($tenant->name) }}? This cannot be undone.')"
                                style="width:100%;padding:10px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:8px;color:#dc2626;font-size:13px;font-weight:700;cursor:pointer;">
                            🗑 Reset School Data
                        </button>
                    </form>
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Broadcast Modal ──────────────────────────────────────── --}}
<div id="broadcastModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;width:480px;max-width:90vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="font-size:16px;font-weight:800;color:#1e293b;margin-bottom:4px;">📢 Broadcast Announcement</div>
        <div style="font-size:12px;color:#94a3b8;margin-bottom:20px;">Send a notice to all school admins</div>
        <form id="broadcastForm" action="{{ route('superadmin.broadcast') }}" method="POST">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Target</label>
                <select name="target" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    <option value="all">All Schools</option>
                    <option value="active">Active Schools Only</option>
                    <option value="suspended">Suspended Schools Only</option>
                </select>
            </div>
            <div style="margin-bottom:16px;">
                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">Message</label>
                <textarea name="message" rows="4" placeholder="Type your announcement..." required
                          style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="document.getElementById('broadcastModal').style.display='none'"
                        style="flex:1;padding:10px;background:#f1f5f9;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;color:#475569;">
                    Cancel
                </button>
                <button type="button" onclick="document.getElementById('broadcastModal').style.display='none'; saConfirm('broadcastForm', 'Send broadcast to selected schools?')" class="sa-btn sa-btn-primary" style="flex:1;">Send Broadcast</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Load health indicators for each school
document.addEventListener('DOMContentLoaded', function () {
    @foreach($tenants as $tenant)
    fetch('{{ route('superadmin.tenants.health', $tenant) }}')
        .then(r => r.json())
        .then(h => {
            const el = document.getElementById('health-{{ $tenant->id }}');
            const dot = (ok, label) => `<span title="${label}" style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 6px;border-radius:99px;background:${ok?'#dcfce7':'#fee2e2'};color:${ok?'#166534':'#991b1b'};">${ok?'✓':'✗'} ${label}</span>`;
            el.innerHTML = dot(h.has_admin,'Admin') + dot(h.has_active_term,'Term') + dot(h.has_students,'Students');
        })
        .catch(() => {
            document.getElementById('health-{{ $tenant->id }}').innerHTML = '<span style="font-size:11px;color:#94a3b8;">—</span>';
        });
    @endforeach
});

function openManage(id, name) {
    document.getElementById('managePanelTitle').textContent = name;
    document.querySelectorAll('[id^="manage-"]').forEach(el => el.style.display = 'none');
    const target = document.getElementById('manage-' + id);
    if (target) target.style.display = 'block';
    document.getElementById('managePanel').style.display = 'block';
}

function closeManage() {
    document.getElementById('managePanel').style.display = 'none';
}
</script>
@endpush
