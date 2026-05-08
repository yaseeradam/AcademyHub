@extends('layouts.superadmin')

@section('header_title', 'Edit School')
@section('header_subtitle', '{{ $tenant->name }} — update instance settings')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">
        ← Back to List
    </a>
@endsection

@section('content')
<div style="max-width:860px; margin:0 auto;">

    <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ── School Information ───────────────────────────── --}}
        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    School Information
                </span>
                <div style="font-size:11.5px;color:#94a3b8;font-family:monospace;background:#f1f5f9;padding:3px 10px;border-radius:6px;">
                    slug: {{ $tenant->slug }}
                </div>
            </div>
            <div style="padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px;">

                <div>
                    <label class="sa-form-label">School Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="sa-form-input">
                    @error('name')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Custom Domain <span style="color:#94a3b8;">(optional)</span></label>
                    <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}"
                           class="sa-form-input" placeholder="portal.school.edu">
                    @error('domain')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}"
                           class="sa-form-input">
                    @error('contact_email')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}"
                           class="sa-form-input">
                    @error('contact_phone')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ── Plan & Limits ────────────────────────────────── --}}
        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Subscription &amp; Limits
                </span>
            </div>
            <div style="padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px;">

                <div>
                    <label class="sa-form-label">Pricing Plan <span style="color:#ef4444;">*</span></label>
                    <div style="position:relative;">
                        <select name="plan" required class="sa-form-input" style="appearance:none;padding-right:36px;">
                            <option value="free"       @selected(old('plan',$tenant->plan)=='free')>Free Tier</option>
                            <option value="pro"        @selected(old('plan',$tenant->plan)=='pro')>Pro Tier</option>
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
                            <option value="pending"   @selected(old('status',$tenant->status)=='pending')>Pending Setup</option>
                            <option value="active"    @selected(old('status',$tenant->status)=='active')>Active / Live</option>
                            <option value="suspended" @selected(old('status',$tenant->status)=='suspended')>Suspended</option>
                        </select>
                        <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="sa-form-label">Max Students <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="max_students" value="{{ old('max_students', $tenant->max_students) }}" required min="1"
                           class="sa-form-input" style="font-family:monospace;">
                    @error('max_students')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Max Teachers <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="max_teachers" value="{{ old('max_teachers', $tenant->max_teachers) }}" required min="1"
                           class="sa-form-input" style="font-family:monospace;">
                    @error('max_teachers')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ── Metadata ─────────────────────────────────────── --}}
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;padding:14px 18px;background:white;border-radius:12px;border:1px solid #f1f5f9;font-size:12px;color:#94a3b8;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Created <strong style="color:#475569;">{{ $tenant->created_at->format('M j, Y H:i') }}</strong>
            &nbsp;·&nbsp;
            Last updated <strong style="color:#475569;">{{ $tenant->updated_at->format('M j, Y H:i') }}</strong>
        </div>

        {{-- ── Actions ─────────────────────────────────────── --}}
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
            <div>
                {{-- Delete form is OUTSIDE the update form to avoid nested form bug --}}
            </div>
            <div style="display:flex; gap:12px;">
                <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">Cancel</a>
                <button type="submit" class="sa-btn sa-btn-primary" style="padding:10px 24px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>

    </form>

    {{-- Delete form outside the update form --}}
    <div style="margin-top:12px; padding-top:20px; border-top:1px solid #f1f5f9;">
        <div style="font-size:12px; color:#94a3b8; margin-bottom:10px; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Danger Zone</div>
        <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST"
              onsubmit="return confirm('Type the school name to confirm deletion.\n\nYou are about to permanently delete:\n{{ addslashes($tenant->name) }}\n\nThis CANNOT be undone. All data will be lost.\n\nClick OK only if you are absolutely sure.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="sa-btn sa-btn-danger">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete This School Permanently
            </button>
        </form>
    </div>
</div>
@endsection
