@extends('layouts.superadmin')

@section('header_title', 'School Control Center')
@section('header_subtitle')
    {{ $tenant->name }} &middot; Central Management Dashboard
@endsection

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">
        &larr; Return to Schools
    </a>
@endsection

@section('content')
<div x-data="{ 
    activeTab: 'overview',
    dnsLoading: false,
    dnsData: null,
    runDnsCheck() {
        this.dnsLoading = true;
        this.dnsData = null;
        fetch('{{ route('superadmin.tenants.check-dns', $tenant) }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            this.dnsData = data;
            this.dnsLoading = false;
        })
        .catch(err => {
            console.error(err);
            this.dnsLoading = false;
        });
    }
}" class="control-center-layout" style="max-width: 1100px; margin: 0 auto;">

    {{-- ── Tab Navigation ─────────────────────────────────── --}}
    <div class="cc-tabs">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/></svg>
            Overview
        </button>
        <button @click="activeTab = 'settings'" :class="activeTab === 'settings' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </button>
        <button @click="activeTab = 'admins'" :class="activeTab === 'admins' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Administrators
        </button>
        <button @click="activeTab = 'flags'" :class="activeTab === 'flags' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Feature Flags &amp; Quotas
        </button>
        <button @click="activeTab = 'plugins'" :class="activeTab === 'plugins' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            Plugins Config
        </button>
        <button @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Billing Ledger
        </button>
        <button @click="activeTab = 'backup'" :class="activeTab === 'backup' ? 'active' : ''" class="cc-tab-btn">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            Backup & Restore
        </button>
    </div>

    {{-- ── Tab Contents ─────────────────────────────────── --}}
    
    {{-- A. OVERVIEW TAB --}}
    <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        <div class="sa-stats-grid">
            <div class="sa-stat-card indigo">
                <div class="sa-stat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="sa-stat-info">
                    <div class="sa-stat-value">{{ number_format($studentCount) }}</div>
                    <div class="sa-stat-label">Students</div>
                    <div class="sa-stat-sub">Enrollment Cap: {{ $tenant->max_students }}</div>
                </div>
            </div>
            <div class="sa-stat-card teal">
                <div class="sa-stat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div class="sa-stat-info">
                    <div class="sa-stat-value">{{ number_format($teacherCount) }}</div>
                    <div class="sa-stat-label">Teachers</div>
                    <div class="sa-stat-sub">Capacity Cap: {{ $tenant->max_teachers }}</div>
                </div>
            </div>
            <div class="sa-stat-card orange">
                <div class="sa-stat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="sa-stat-info">
                    <div class="sa-stat-value">{{ number_format($parentCount) }}</div>
                    <div class="sa-stat-label">Parents</div>
                    <div class="sa-stat-sub">Registered accounts</div>
                </div>
            </div>
            <div class="sa-stat-card rose">
                <div class="sa-stat-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="sa-stat-info">
                    <div class="sa-stat-value">{{ number_format($adminCount) }}</div>
                    <div class="sa-stat-label">Administrators</div>
                    <div class="sa-stat-sub">School management</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- DNS Diagnostics --}}
            <div class="sa-panel md:col-span-2">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">Subdomain &amp; DNS Diagnostics</span>
                    <button type="button" @click="runDnsCheck()" class="sa-btn sa-btn-primary" style="padding: 5px 12px; font-size: 11px;">
                        Run Network Diagnosis
                    </button>
                </div>
                <div style="padding: 24px;">
                    <div class="dns-status-row">
                        <div class="dns-status-label">Resolved Address:</div>
                        <div class="dns-status-val">
                            <span class="badge badge-purple" style="font-family: monospace;">{{ $tenant->domain ?: ($tenant->slug . '.' . parse_url(config('app.url'), PHP_URL_HOST)) }}</span>
                        </div>
                    </div>

                    <div x-show="dnsLoading" class="dns-loading-overlay">
                        <div class="spinner"></div>
                        <div class="loading-text">Pinging servers, querying SSL certs, and fetching A records...</div>
                    </div>

                    <div x-show="!dnsLoading && dnsData" class="dns-results-box" style="display: none;">
                        <div class="dns-result-item">
                            <div class="dri-icon dns-ok">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="dri-content">
                                <div class="dri-title">DNS A-Records</div>
                                <div class="dri-desc" x-text="dnsData ? dnsData.dns : ''"></div>
                            </div>
                        </div>
                        <div class="dns-result-item">
                            <div class="dri-icon" :class="dnsData && dnsData.ping.includes('Online') ? 'dns-ok' : 'dns-err'">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div class="dri-content">
                                <div class="dri-title">Ping Latency Check</div>
                                <div class="dri-desc" x-text="dnsData ? dnsData.ping : ''"></div>
                            </div>
                        </div>
                        <div class="dns-result-item">
                            <div class="dri-icon" :class="dnsData && dnsData.ssl.includes('Valid') ? 'dns-ok' : 'dns-err'">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div class="dri-content">
                                <div class="dri-title">SSL Certificate Diagnostic</div>
                                <div class="dri-desc" x-text="dnsData ? dnsData.ssl : ''"></div>
                            </div>
                        </div>
                    </div>

                    <div x-show="!dnsLoading && !dnsData" class="dns-placeholder">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Click "Run Network Diagnosis" to verify custom domain resolves and has secure SSL handshakes.
                    </div>
                </div>
            </div>

            {{-- Impersonate Admin --}}
            <div class="sa-panel">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">Superpower Access</span>
                </div>
                <div style="padding: 24px; text-align: center;">
                    <div style="font-size: 40px; margin-bottom: 12px;">🔑</div>
                    <h3 style="font-weight: 700; margin: 0 0 8px; color: var(--sa-text);">Impersonate Admin</h3>
                    <p style="font-size: 12.5px; color: var(--sa-muted); line-height: 1.5; margin-bottom: 20px;">
                        Access this school's dashboard instantly. You will have full access as a primary administrator to debug issues or view configs.
                    </p>

                    @if($admins->isNotEmpty())
                        <form action="{{ route('superadmin.tenants.impersonate', $tenant) }}" method="POST" data-confirm-password="Log in and impersonate the school administrator of '{{ $tenant->name }}'">
                            @csrf
                            <button type="submit" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center; padding: 11px;">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Log In as {{ $admins->first()->name }}
                            </button>
                        </form>
                    @else
                        <div class="sa-alert error" style="margin-bottom:0; font-size:12px; justify-content:center;">
                            Create an Admin account first in the "Administrators" tab.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- B. SETTINGS TAB --}}
    <div x-show="activeTab === 'settings'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="sa-panel">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">Core School Settings</span>
                    <div style="font-size:11.5px;color:#94a3b8;font-family:monospace;background:#f1f5f9;padding:3px 10px;border-radius:6px;">
                        ID: {{ $tenant->id }} &middot; slug: {{ $tenant->slug }}
                    </div>
                </div>
                <div style="padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <label class="sa-form-label">School Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="sa-form-input">
                        @error('name')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Custom Domain <span style="color:#94a3b8;">(optional)</span></label>
                        <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}" class="sa-form-input" placeholder="portal.school.edu">
                        @error('domain')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Pricing Plan Tier <span style="color:#ef4444;">*</span></label>
                        <div style="position:relative;">
                            <select name="plan" required class="sa-form-input" style="appearance:none;padding-right:36px;">
                                <option value="free" @selected(old('plan',$tenant->plan)=='free')>Free Tier</option>
                                <option value="pro" @selected(old('plan',$tenant->plan)=='pro')>Pro Tier</option>
                                <option value="enterprise" @selected(old('plan',$tenant->plan)=='enterprise')>Enterprise Tier</option>
                            </select>
                            <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="sa-form-label">Instance Status <span style="color:#ef4444;">*</span></label>
                        <div style="position:relative;">
                            <select name="status" required class="sa-form-input" style="appearance:none;padding-right:36px;">
                                <option value="pending" @selected(old('status',$tenant->status)=='pending')>Pending Setup</option>
                                <option value="active" @selected(old('status',$tenant->status)=='active')>Active / Live</option>
                                <option value="suspended" @selected(old('status',$tenant->status)=='suspended')>Suspended</option>
                            </select>
                            <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="sa-form-label">Maximum Student Capacity <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="max_students" value="{{ old('max_students', $tenant->max_students) }}" required min="1" class="sa-form-input">
                        @error('max_students')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Maximum Teacher Capacity <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="max_teachers" value="{{ old('max_teachers', $tenant->max_teachers) }}" required min="1" class="sa-form-input">
                        @error('max_teachers')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" class="sa-form-input">
                        @error('contact_email')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}" class="sa-form-input">
                        @error('contact_phone')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Subscription Expiry / Due Date</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at', $tenant->expires_at ? $tenant->expires_at->format('Y-m-d') : '') }}" class="sa-form-input">
                        @error('expires_at')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="sa-panel-header" style="background: #f8fafc; border-top: 1px solid var(--sa-border); justify-content: flex-end; padding: 14px 24px;">
                    <button type="submit" class="sa-btn sa-btn-primary">
                        Save General Settings
                    </button>
                </div>
            </div>
        </form>

        <div style="margin-top:24px; padding:24px; background:white; border-radius:16px; border:1px solid #fee2e2; box-shadow: var(--sa-shadow);">
            <div style="font-size:12px; color:#be123c; margin-bottom:8px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">Danger Zone</div>
            <p style="font-size:13px; color:#475569; margin: 0 0 16px; line-height:1.5;">
                Permanently delete all databases, student records, grades, billing, configuration files, and assets of this school. This action is absolute and cannot be undone under any circumstances.
            </p>
            <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST"
                  onsubmit="return confirm('Type the school name to confirm deletion.\n\nYou are about to permanently delete:\n{{ addslashes($tenant->name) }}\n\nThis CANNOT be undone. All data will be lost.\n\nClick OK only if you are absolutely sure.')"
                  data-confirm-password="Permanently terminate the school '{{ $tenant->name }}' and delete all databases, records, and uploaded assets">
                @csrf
                @method('DELETE')
                <button type="submit" class="sa-btn sa-btn-danger">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Terminate &amp; Erase School Central Records
                </button>
            </form>
        </div>
    </div>

    {{-- C. ADMINISTRATORS TAB --}}
    <div x-show="activeTab === 'admins'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        <div class="sa-panel">
            <div class="sa-panel-header">
                <span class="sa-panel-title">Administrators Profile Settings</span>
            </div>
            <div style="padding: 24px;">
                @forelse($admins as $admin)
                    <form action="{{ route('superadmin.tenants.admins.update', [$tenant, $admin]) }}" method="POST" style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #f1f5f9;">
                        @csrf
                        @method('PUT')
                        
                        <div style="font-weight: 700; font-size: 14.5px; color: var(--sa-primary); margin-bottom: 16px; display:flex; align-items:center; gap:8px;">
                            <span style="background: rgba(79,70,229,0.1); width: 24px; height: 24px; border-radius: 50%; display:inline-flex; align-items:center; justify-content:center; font-size: 11px;">#</span>
                            Administrator: {{ $admin->name }} ({{ $admin->email }})
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="sa-form-label">Full Name <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $admin->name) }}" required class="sa-form-input">
                            </div>
                            <div>
                                <label class="sa-form-label">Email Address <span style="color:#ef4444;">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="sa-form-input">
                            </div>
                            <div>
                                <label class="sa-form-label">Password <span style="color:#94a3b8;">(leave blank to keep current)</span></label>
                                <input type="password" name="password" class="sa-form-input" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="sa-form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="sa-form-input" placeholder="••••••••">
                            </div>
                            <div class="md:col-span-2" style="text-align: right; margin-top: 8px;">
                                <button type="submit" class="sa-btn sa-btn-primary" style="padding: 8px 20px; font-size: 12.5px;">
                                    Update Credentials
                                </button>
                            </div>
                        </div>
                    </form>
                @empty
                    <p style="color:#94a3b8; font-size:14px; text-align: center; padding: 20px 0;">No administrator accounts defined for this tenant.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- D. FEATURE FLAGS & QUOTAS TAB --}}
    <div x-show="activeTab === 'flags'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Flags & Disk --}}
            <div class="sa-panel md:col-span-2">
                <form action="{{ route('superadmin.tenants.save-flags', $tenant) }}" method="POST" data-confirm-password="Modify resource limits, disk space quota, and premium feature access for '{{ $tenant->name }}'">
                    @csrf
                    <div class="sa-panel-header">
                        <span class="sa-panel-title">Feature Toggles &amp; Storage Quotas</span>
                    </div>
                    <div style="padding: 24px;">
                        
                        {{-- Feature Flags Checklist --}}
                        <div class="sa-section-title">Toggle Features</div>
                        
                        @php
                            $flags = $tenant->feature_flags ?? [];
                        @endphp

                        <div class="ff-toggle-list" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom: 28px;">
                            <label class="ff-toggle-item">
                                <input type="checkbox" name="feature_flags[whatsapp_bot]" value="1" @checked(!empty($flags['whatsapp_bot'])) class="ff-checkbox">
                                <div class="ff-details">
                                    <span class="ff-title">WhatsApp Bot API</span>
                                    <span class="ff-desc">Enable automated homework &amp; invoice alerts to parents' WhatsApp.</span>
                                </div>
                            </label>

                            <label class="ff-toggle-item">
                                <input type="checkbox" name="feature_flags[sms_alerts]" value="1" @checked(!empty($flags['sms_alerts'])) class="ff-checkbox">
                                <div class="ff-details">
                                    <span class="ff-title">Bulk SMS Alerts</span>
                                    <span class="ff-desc">Integrate standard SMS APIs to send termly reports and grade alerts.</span>
                                </div>
                            </label>

                            <label class="ff-toggle-item">
                                <input type="checkbox" name="feature_flags[parent_portal]" value="1" @checked(!empty($flags['parent_portal'])) class="ff-checkbox">
                                <div class="ff-details">
                                    <span class="ff-title">Interactive Parent Portal</span>
                                    <span class="ff-desc">Allow parents to log in, view live grades, and print custom bills.</span>
                                </div>
                            </label>

                            <label class="ff-toggle-item">
                                <input type="checkbox" name="feature_flags[bulk_reports]" value="1" @checked(!empty($flags['bulk_reports'])) class="ff-checkbox">
                                <div class="ff-details">
                                    <span class="ff-title">Premium Bulk Grade Reports</span>
                                    <span class="ff-desc">Enable dynamic PDF compilation and zip archives of report sheets.</span>
                                </div>
                            </label>
                        </div>

                        {{-- Disk Quotas --}}
                        <div class="sa-section-title">Disk Storage Limit</div>
                        <div style="margin-bottom: 12px;">
                            <label class="sa-form-label">Max Disk Quota (Megabytes)</label>
                            <div style="display: flex; align-items:center; gap:12px;">
                                <input type="range" min="100" max="50000" step="100" value="{{ $tenant->max_disk_usage_mb ?? 500 }}" 
                                       oninput="document.getElementById('disk_value_text').value = this.value" class="quota-slider" style="flex:1;">
                                <input type="number" id="disk_value_text" name="max_disk_usage_mb" value="{{ $tenant->max_disk_usage_mb ?? 500 }}" required min="50" max="100000"
                                       class="sa-form-input" style="width:110px; text-align:center; font-family:monospace; font-weight:700;">
                                <span style="font-weight:700; color:var(--sa-muted);">MB</span>
                            </div>
                            <div class="sa-form-hint">Defines maximum combined size of student photos, documents, and uploads.</div>
                        </div>

                    </div>
                    <div class="sa-panel-header" style="background:#f8fafc; border-top:1px solid var(--sa-border); justify-content: flex-end; padding: 14px 24px;">
                        <button type="submit" class="sa-btn sa-btn-primary">
                            Save Flags &amp; Limits
                        </button>
                    </div>
                </form>
            </div>

            {{-- Broadcast Banner --}}
            <div class="sa-panel">
                <form action="{{ route('superadmin.tenants.save-broadcast', $tenant) }}" method="POST">
                    @csrf
                    <div class="sa-panel-header">
                        <span class="sa-panel-title">Dashboard Alert Broadcast</span>
                    </div>
                    <div style="padding: 24px;">
                        <label class="sa-form-label">Warning / Announcement Banner</label>
                        <textarea name="active_broadcast_banner" rows="5" class="sa-form-input" style="resize:none;"
                                  placeholder="e.g. '⚠️ School fee deadline is extended to next Friday.' or 'System maintenance is scheduled for Sunday.'">{{ $tenant->active_broadcast_banner }}</textarea>
                        <div class="sa-form-hint" style="margin-top: 10px;">
                            This message will immediately appear as a premium amber alert banner at the top of every dashboard view for school admins, teachers, and parents of this school instance. Keep blank to clear.
                        </div>
                    </div>
                    <div class="sa-panel-header" style="background:#f8fafc; border-top:1px solid var(--sa-border); justify-content: flex-end; padding: 14px 24px;">
                        <button type="submit" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center;">
                            Publish Alert Banner
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- E. PLUGINS TAB --}}
    <div x-show="activeTab === 'plugins'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        <div class="sa-panel">
            <div class="sa-panel-header">
                <span class="sa-panel-title">School Marketplace Components &amp; Override Configs</span>
            </div>
            <div style="padding: 24px;">
                <div style="display:grid; grid-template-columns: 1fr; gap: 20px;">
                    @foreach($components as $component)
                        @php
                            $installedRelation = $tenant->marketplaceComponents->firstWhere('id', $component->id);
                            $pivot = $installedRelation ? $installedRelation->pivot : null;
                            $allowedClasses = $pivot && $pivot->allowed_class_ids ? $pivot->allowed_class_ids : [];
                        @endphp

                        <div class="plugin-config-card" style="border:1.5px solid #e2e8f0; border-radius:14px; overflow:hidden; background:white; transition:all .2s;">
                            
                            {{-- Header --}}
                            <div style="padding: 16px 20px; background:#f8fafc; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #e2e8f0;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    @if(!empty($component->icon) && str_contains($component->icon, '<svg'))
                                        <span class="tenant-edit-svg-icon" style="display:inline-flex; width:24px; height:24px; align-items:center; justify-content:center; flex-shrink:0;">
                                            {!! $component->icon !!}
                                        </span>
                                    @else
                                        <span style="font-size: 20px;">{!! $component->icon ?: '🧩' !!}</span>
                                    @endif
                                    <div>
                                        <div style="font-weight: 700; color: var(--sa-text); font-size:14.5px;">{{ $component->name }}</div>
                                        <div style="font-size:11.5px; color:var(--sa-muted);">{{ $component->description }}</div>
                                    </div>
                                </div>
                                <div>
                                    @if($pivot)
                                        @if($pivot->status === 'active')
                                            <span class="sa-badge active"><span class="sa-badge-dot"></span> Active Usage</span>
                                        @else
                                            <span class="sa-badge suspended"><span class="sa-badge-dot"></span> Suspended</span>
                                        @endif
                                    @else
                                        <span class="sa-badge free">Not Installed</span>
                                    @endif
                                </div>
                            </div>

                            @if($pivot)
                                {{-- Configuration Overrides Form --}}
                                <form action="{{ route('superadmin.tenants.plugins.update', [$tenant, $component]) }}" method="POST" style="padding: 20px;">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4" style="margin-bottom: 16px;">
                                        <div>
                                            <label class="sa-form-label">Setup Fee (override)</label>
                                            <div style="position:relative;">
                                                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-weight:700; color:var(--sa-muted);">{{ config('myacademy.currency_symbol', '₦') }}</span>
                                                <input type="number" name="setup_fee" value="{{ old('setup_fee', $pivot->setup_fee ?? $component->setup_fee) }}" required min="0" step="0.01" class="sa-form-input" style="padding-left:30px; font-family:monospace; font-weight:600;">
                                            </div>
                                            <div class="sa-form-hint">Central Base: {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->setup_fee, 2) }}</div>
                                        </div>

                                        <div>
                                            <label class="sa-form-label">Usage Fee/Student (override)</label>
                                            <div style="position:relative;">
                                                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-weight:700; color:var(--sa-muted);">{{ config('myacademy.currency_symbol', '₦') }}</span>
                                                <input type="number" name="usage_fee_per_student" value="{{ old('usage_fee_per_student', $pivot->usage_fee_per_student ?? $component->usage_fee_per_student) }}" required min="0" step="0.01" class="sa-form-input" style="padding-left:30px; font-family:monospace; font-weight:600;">
                                            </div>
                                            <div class="sa-form-hint">Central Base: {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($component->usage_fee_per_student, 2) }}</div>
                                        </div>

                                        <div>
                                            <label class="sa-form-label">Access State</label>
                                            <div style="position:relative;">
                                                <select name="status" required class="sa-form-input" style="appearance:none;padding-right:36px; font-weight:600;">
                                                    <option value="active" @selected($pivot->status === 'active')>Active / Unlocked</option>
                                                    <option value="suspended" @selected($pivot->status === 'suspended')>Suspended / Terminated</option>
                                                </select>
                                                <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="display:flex; align-items:flex-end;">
                                            <button type="submit" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
                                                Update Overrides
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Class targeting configured by school admin (read-only for superadmin) --}}
                                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius:10px; padding: 14px 18px;">
                                        <div style="font-size:12px; font-weight:700; color:var(--sa-text); margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                            <span>Target Classrooms for Usage Calculation</span>
                                            <span style="font-size: 11px; font-weight: 600; color: #475569; background: #e2e8f0; padding: 2px 8px; border-radius: 4px;">Configured by School Admin</span>
                                        </div>
                                        
                                        @if($classes->isNotEmpty())
                                            @php
                                                $selectedClassesObjects = $classes->filter(fn($c) => in_array($c->id, $allowedClasses));
                                            @endphp
                                            @if($selectedClassesObjects->isNotEmpty())
                                                <div style="display:flex; flex-wrap: wrap; gap: 8px;">
                                                    @foreach($selectedClassesObjects as $c)
                                                        <div style="display:inline-flex; align-items:center; gap:6px; font-size:11.5px; color:#1e293b; background:#e2e8f0; border:1px solid #cbd5e1; border-radius:6px; padding: 4px 10px; font-weight:600;">
                                                            <span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--sa-primary);"></span>
                                                            {{ $c->name }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div style="font-size:11.5px; color:#94a3b8; font-style: italic; text-align:center;">
                                                    No specific classes targeted. Defaulting to all active students in school.
                                                </div>
                                            @endif
                                        @else
                                            <div style="font-size:11.5px; color:var(--sa-muted); text-align:center;">No school classes defined yet.</div>
                                        @endif
                                    </div>
                                </form>
                            @else
                                {{-- Not installed, display a descriptive guide --}}
                                <div style="padding: 20px; text-align: center; color: var(--sa-muted); font-size: 13px;">
                                    This plugin is not yet installed by the school administrator. When installed, you can configure override prices and view target classrooms here.
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- F. BILLING LEDGER TAB --}}
    <div x-show="activeTab === 'billing'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Bills List --}}
            <div class="sa-panel md:col-span-2">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">Invoices &amp; Ledger Records</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="sa-table">
                        <thead>
                            <tr>
                                <th>Item / Type</th>
                                <th>Period / Session</th>
                                <th>Quantity / Price</th>
                                <th>Total Due</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700;">{{ $bill->marketplaceComponent?->name ?? 'Plugin' }}</div>
                                        <div style="font-size: 10px; color:var(--sa-muted); text-transform:uppercase; font-weight:700;">
                                            {{ $bill->bill_type === 'setup' ? 'Setup Fee' : 'Usage Fee' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600;">{{ $bill->term_name }}</div>
                                        <div style="font-size: 11px; color: var(--sa-muted);">{{ $bill->session_name }}</div>
                                    </td>
                                    <td>
                                        @if($bill->bill_type === 'usage')
                                            <div style="font-size:12.5px; font-weight:600; font-family:monospace;">{{ $bill->student_count }} studs</div>
                                            <div style="font-size:11px; color:var(--sa-muted); font-family:monospace;">&times; {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->usage_fee_per_student, 2) }}</div>
                                        @else
                                            <div style="font-size:12.5px; font-weight:600; font-family:monospace;">Single Fee</div>
                                            <div style="font-size:11px; color:var(--sa-muted); font-family:monospace;">{{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->setup_fee, 2) }}</div>
                                        @endif
                                    </td>
                                    <td style="font-family:monospace; font-weight: 700; color:var(--sa-primary);">
                                        {{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->total_due, 2) }}
                                    </td>
                                    <td>
                                        @if($bill->status === 'paid')
                                            <span class="sa-badge active" title="Paid at: {{ $bill->paid_at ? $bill->paid_at->format('M j, Y H:i') : 'N/A' }}">
                                                <span class="sa-badge-dot"></span> Paid
                                            </span>
                                        @elseif($bill->status === 'void')
                                            <span class="sa-badge free"><span class="sa-badge-dot"></span> Voided</span>
                                        @else
                                            <span class="sa-badge suspended"><span class="sa-badge-dot"></span> Unpaid</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($bill->status === 'unpaid')
                                            <div style="display:flex; gap:6px;">
                                                <form action="{{ route('superadmin.tenants.bills.pay', [$tenant, $bill]) }}" method="POST" data-confirm-password="Mark term bill for {{ $bill->term_name }} ({{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->total_due, 2) }}) as fully paid">
                                                    @csrf
                                                    <button type="submit" class="sa-btn sa-btn-primary" style="padding: 4px 8px; font-size: 11px;">Mark Paid</button>
                                                </form>
                                                <form action="{{ route('superadmin.tenants.bills.void', [$tenant, $bill]) }}" method="POST" data-confirm-password="Void term bill for {{ $bill->term_name }} ({{ config('myacademy.currency_symbol', '₦') }}{{ number_format($bill->total_due, 2) }})">
                                                    @csrf
                                                    <button type="submit" class="sa-btn sa-btn-ghost" style="padding: 4px 8px; font-size: 11px;">Void</button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="font-size: 11px; color:var(--sa-muted); font-weight:600;">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color:var(--sa-muted); padding: 30px 0;">No ledger entries found. Setup fees appear when plugins are installed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Generate Termly Bill Form --}}
            <div class="sa-panel">
                <form action="{{ route('superadmin.tenants.bills.generate', $tenant) }}" method="POST">
                    @csrf
                    <div class="sa-panel-header">
                        <span class="sa-panel-title">Calculate Termly Usage</span>
                    </div>
                    <div style="padding: 24px;">
                        
                        <div style="margin-bottom: 14px;">
                            <label class="sa-form-label">Target Plugin Component</label>
                            <div style="position:relative;">
                                <select name="component_id" required class="sa-form-input" style="appearance:none;padding-right:36px; font-weight:600;">
                                    <option value="" disabled selected>Select dynamic plugin</option>
                                    @foreach($tenant->marketplaceComponents as $mc)
                                        <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                                    @endforeach
                                </select>
                                <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="sa-form-label">Academic Term</label>
                            <input type="text" name="term_name" required class="sa-form-input" placeholder="e.g. '1st Term', '2nd Term', '3rd Term'" value="{{ old('term_name') }}">
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label class="sa-form-label">Academic Session</label>
                            <input type="text" name="session_name" required class="sa-form-input" placeholder="e.g. '2025/2026', '2026/2027'" value="{{ old('session_name') }}">
                        </div>

                        <p style="font-size: 11.5px; color: var(--sa-muted); line-height: 1.5; margin-bottom: 18px;">
                            ⚠️ Generating this bill will query the current active student count enrolled in the classes configured for this plugin, apply either the custom override or base usage pricing, and log the unpaid record in the ledger.
                        </p>

                        <button type="submit" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center; padding: 11px;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            Compute &amp; Ledger Termly Bill
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- G. BACKUP & RESTORE TAB --}}
    <div x-show="activeTab === 'backup'" x-transition:enter="transition ease-out duration-200" class="cc-tab-content">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- Backup school data panel --}}
            <div class="sa-panel">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">Export School Database Backup</span>
                </div>
                <div style="padding: 24px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <span style="font-size: 48px; display: block; margin-bottom: 12px;">📥</span>
                        <h4 style="font-weight: 700; color: var(--sa-text); margin: 0 0 6px;">School Data Export</h4>
                        <p style="font-size: 12.5px; color: var(--sa-muted); line-height: 1.5; max-width: 320px; margin: 0 auto;">
                            Export every record associated with this school, including classes, sections, students, teachers, grades, billing, and plugin configurations.
                        </p>
                    </div>

                    <div style="background: #f8fafc; border: 1.5px solid var(--sa-border); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; font-size: 12px; color: var(--sa-text); line-height: 1.5;">
                        <strong>Backup Includes:</strong>
                        <ul style="margin: 6px 0 0 16px; padding: 0; list-style-type: disc;">
                            <li>Core School Profile Details</li>
                            <li>Active Students, Parents, and Teachers Credentials</li>
                            <li>Academic Sessions, Terms, Classes, and Sections</li>
                            <li>Scores, Broadsheets, and Grade Books</li>
                            <li>Dynamic Homework and CBT Exams Configurations</li>
                            <li>Active Marketplace Plugin Setup Fees &amp; Billing Ledger</li>
                        </ul>
                    </div>

                    <form action="{{ route('superadmin.tenants.backup', $tenant) }}" method="POST">
                        @csrf
                        <button type="submit" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-weight: 700;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px; margin-right: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Generate &amp; Download Backup JSON
                        </button>
                    </form>
                </div>
            </div>

            {{-- Import school data panel --}}
            <div class="sa-panel">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">Import &amp; Restore School Backup</span>
                </div>
                <div style="padding: 24px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <span style="font-size: 48px; display: block; margin-bottom: 12px;">📤</span>
                        <h4 style="font-weight: 700; color: var(--sa-text); margin: 0 0 6px;">School Data Import</h4>
                        <p style="font-size: 12.5px; color: var(--sa-muted); line-height: 1.5; max-width: 320px; margin: 0 auto;">
                            Upload a previously generated `.json` backup file to completely restore the school's historical databases, students, and grade configurations.
                        </p>
                    </div>

                    <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; font-size: 12px; color: #991b1b; line-height: 1.5; display: flex; gap: 10px; align-items: flex-start; text-align: left;">
                        <span style="font-size: 16px; line-height: 1;">⚠️</span>
                        <div>
                            <strong>Critical Warning:</strong>
                            <p style="margin: 2px 0 0; color: #b91c1c;">
                                Restoring a backup will **permanently overwrite and replace** all current academic sessions, students, teachers, grades, and configuration data of this school. This action is absolute and cannot be rolled back.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('superadmin.tenants.restore', $tenant) }}" method="POST" enctype="multipart/form-data" 
                          data-confirm-password="Restore backup for '{{ $tenant->name }}'. This will permanently overwrite all current academic sessions, students, teachers, grades, and configuration data.">
                        @csrf
                        
                        <div style="margin-bottom: 20px; text-align: left;">
                            <label class="sa-form-label">Select Backup JSON File (.json)</label>
                            <input type="file" name="backup_file" accept=".json,application/json" required class="sa-form-input" style="padding: 8px;">
                            @error('backup_file')
                                <div class="sa-form-error" style="margin-top: 6px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="sa-btn sa-btn-danger" style="width: 100%; justify-content: center; padding: 12px; font-weight: 700;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px; margin-right: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Restore School Records from File
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

<style>
    /* Premium Dashboard Aesthetic CSS */
    .control-center-layout {
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .cc-tabs {
        display: flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.7);
        padding: 6px;
        border-radius: 14px;
        border: 1px solid var(--sa-border);
        margin-bottom: 24px;
        box-shadow: var(--sa-shadow);
        backdrop-blur: 10px;
        overflow-x: auto;
    }

    .cc-tab-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--sa-muted);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
    }

    .cc-tab-btn svg {
        width: 16px;
        height: 16px;
        transition: transform 0.2s;
    }

    .cc-tab-btn:hover {
        background: #f1f5f9;
        color: var(--sa-text);
    }

    .cc-tab-btn:hover svg {
        transform: translateY(-1px);
    }

    .cc-tab-btn.active {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    .cc-tab-content {
        animation: fadeScale 0.25s ease-out;
    }

    @keyframes fadeScale {
        from {
            opacity: 0;
            transform: scale(0.985);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* DNS Diagnostics layout */
    .dns-status-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--sa-border);
        margin-bottom: 18px;
    }
    
    .dns-status-label {
        font-weight: 700;
        font-size: 13.5px;
        color: var(--sa-text);
    }

    .badge-purple {
        background: rgba(124, 58, 237, 0.08);
        color: #7c3aed;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid rgba(124, 58, 237, 0.15);
    }

    .dns-placeholder {
        padding: 40px 20px;
        text-align: center;
        color: var(--sa-muted);
        font-size: 13px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        border: 2px dashed var(--sa-border);
        border-radius: 12px;
        background: #fafafa;
        line-height: 1.5;
    }

    .dns-placeholder svg {
        width: 32px;
        height: 32px;
        color: #cbd5e1;
    }

    .dns-loading-overlay {
        padding: 40px 20px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 14px;
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 4.5px solid #e2e8f0;
        border-top-color: #4f46e5;
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loading-text {
        font-size: 12px;
        font-weight: 600;
        color: var(--sa-muted);
    }

    .dns-results-box {
        display: flex;
        flex-direction: column;
        gap: 14px;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dns-result-item {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #f8fafc;
        border: 1.5px solid var(--sa-border);
        border-radius: 12px;
        padding: 14px 16px;
    }

    .dri-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .dri-icon.dns-ok {
        background: #f0fdf4;
        color: #16a34a;
    }

    .dri-icon.dns-err {
        background: #fff1f2;
        color: #ef4444;
    }

    .dri-icon svg {
        width: 18px;
        height: 18px;
    }

    .dri-content {
        flex: 1;
    }

    .dri-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--sa-text);
        margin-bottom: 2px;
    }

    .dri-desc {
        font-size: 12px;
        color: var(--sa-muted);
        font-weight: 500;
        font-family: monospace;
    }

    /* Toggle items */
    .ff-toggle-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: #f8fafc;
        border: 1.5px solid var(--sa-border);
        border-radius: 12px;
        padding: 14px 16px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .ff-toggle-item:hover {
        border-color: var(--sa-primary);
        background: white;
    }

    .ff-checkbox {
        accent-color: var(--sa-primary);
        width: 18px;
        height: 18px;
        margin-top: 2px;
        flex-shrink: 0;
        cursor: pointer;
    }

    .ff-details {
        display: flex;
        flex-direction: column;
    }

    .ff-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--sa-text);
        margin-bottom: 2px;
    }

    .ff-desc {
        font-size: 11.5px;
        color: var(--sa-muted);
        line-height: 1.4;
    }

    /* Slider styling */
    .quota-slider {
        -webkit-appearance: none;
        height: 8px;
        border-radius: 6px;
        background: #e2e8f0;
        outline: none;
    }

    .quota-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--sa-primary);
        cursor: pointer;
        transition: background 0.15s;
    }

    .quota-slider::-webkit-slider-thumb:hover {
        background: #7c3aed;
    }

    .tenant-edit-svg-icon svg {
        width: 24px !important;
        height: 24px !important;
        max-width: 100% !important;
        max-height: 100% !important;
        display: inline-block !important;
    }
</style>
@endsection
