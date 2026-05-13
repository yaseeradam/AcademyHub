@extends('layouts.superadmin')

@section('header_title', 'Create New School')
@section('header_subtitle', 'Provision a new school instance')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">
        ← Back to List
    </a>
@endsection

@section('content')
<div style="max-width:860px; margin:0 auto;">

    <form action="{{ route('superadmin.tenants.store') }}" method="POST">
        @csrf

        {{-- ── School Information ───────────────────────────── --}}
        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    School Information
                </span>
            </div>
            <div style="padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px;">

                <div>
                    <label class="sa-form-label">School Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" id="schoolName" value="{{ old('name') }}" required
                           class="sa-form-input" placeholder="e.g. Greenwood High School"
                           oninput="previewSlug(this.value)">
                    @error('name')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">
                        Subdomain Slug
                        <span style="color:#94a3b8;font-weight:500;"> — how the school is accessed</span>
                    </label>
                    <div style="display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#fff;">
                        <span style="padding:0 10px;font-size:12px;color:#94a3b8;background:#f8fafc;border-right:1px solid #e2e8f0;white-space:nowrap;line-height:40px;">
                            {{ parse_url(config('app.url'), PHP_URL_HOST) }}/
                        </span>
                        <input type="text" name="slug" id="slugInput" value="{{ old('slug') }}"
                               class="sa-form-input" style="border:none;border-radius:0;flex:1;font-family:monospace;font-size:13px;"
                               placeholder="e.g. yis  (leave blank to auto-generate)"
                               pattern="[a-z0-9]+(-[a-z0-9]+)*"
                               oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9-]/g,'')">
                    </div>
                    <div style="font-size:11.5px;color:#94a3b8;margin-top:4px;">
                        Lowercase letters, numbers and hyphens only. Leave blank to auto-generate from the school name.
                        Cannot be: <span style="font-family:monospace;color:#ef4444;">superadmin, api, students, teachers, uploads, storage</span> or other reserved names.
                    </div>
                    @error('slug')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Custom Domain <span style="color:#94a3b8;">(optional)</span></label>
                    <input type="text" name="domain" value="{{ old('domain') }}"
                           class="sa-form-input" placeholder="e.g. portal.greenwood.edu">
                    @error('domain')<div class="sa-form-error">{{ $message }}</div>@enderror
                    <div class="sa-form-hint">Full custom domain — overrides the subdomain slug for access.</div>
                </div>

                <div>
                    <label class="sa-form-label">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                           class="sa-form-input" placeholder="admin@school.com">
                    @error('contact_email')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                           class="sa-form-input" placeholder="+234 ...">
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
                            <option value="free"       @selected(old('plan','free')=='free')>Free Tier</option>
                            <option value="pro"        @selected(old('plan')=='pro')>Pro Tier</option>
                            <option value="enterprise" @selected(old('plan')=='enterprise')>Enterprise Tier</option>
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
                            <option value="pending"   @selected(old('status','pending')=='pending')>Pending Setup</option>
                            <option value="active"    @selected(old('status')=='active')>Active / Live</option>
                            <option value="suspended" @selected(old('status')=='suspended')>Suspended</option>
                        </select>
                        <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="sa-form-label">Max Students <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="max_students" value="{{ old('max_students', 500) }}" required min="1"
                           class="sa-form-input" style="font-family:monospace;">
                    @error('max_students')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="sa-form-label">Max Teachers <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="max_teachers" value="{{ old('max_teachers', 50) }}" required min="1"
                           class="sa-form-input" style="font-family:monospace;">
                    @error('max_teachers')<div class="sa-form-error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- ── Initial Admin Account ───────────────────────── --}}
        {{-- Single-school upgrade adoption (first tenant only) --}}
        @if(!empty($isFirstTenant) && !empty($legacyDataExists))
            <div class="sa-panel" style="margin-bottom:20px;">
                <div class="sa-panel-header">
                    <span class="sa-panel-title">
                        <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Existing Data Detected
                    </span>
                </div>
                <div style="padding:20px 24px;">
                    <p style="margin:0 0 12px; color:#64748b; font-size:13px; line-height:1.6;">
                        This database already contains school data created before multi-school mode (records with no <span style="font-family:monospace;">tenant_id</span>).
                        To keep your current data working, you can adopt it into this first school.
                    </p>
                    <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                        <input type="hidden" name="adopt_existing_data" value="0">
                        <input type="checkbox" name="adopt_existing_data" value="1" checked style="margin-top:3px;">
                        <div>
                            <div style="font-weight:700; color:#0f172a;">Adopt existing data into this school</div>
                            <div style="color:#94a3b8; font-size:12px; line-height:1.5;">
                                Recommended for upgrading a single-school install. Do not use if you already have multiple schools mixed in one database.
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        @endif

        <div class="sa-panel" style="margin-bottom:24px;" x-data="{ createAdmin: {{ old('create_admin') ? 'true' : 'false' }} }">
            <div class="sa-panel-header" style="cursor:pointer;" @click="createAdmin = !createAdmin">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Create Initial Admin Account
                    <span style="font-size:11px;font-weight:500;color:#94a3b8;margin-left:8px;">— optional</span>
                </span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:12px;color:#94a3b8;" x-text="createAdmin ? 'Collapse' : 'Expand to set up admin'"></span>
                    <svg :class="createAdmin ? 'rotate-180' : ''" style="width:16px;height:16px;color:#94a3b8;transition:transform .2s;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <div x-show="createAdmin" x-cloak style="padding:24px; border-top:1px solid #f1f5f9;">
                <input type="hidden" name="create_admin" value="1">
                <p style="font-size:13px;color:#64748b;margin:0 0 18px;line-height:1.6;">
                    Provision an administrator account for this school. The admin can log in immediately and begin configuring the school.
                </p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <label class="sa-form-label">Admin Full Name</label>
                        <input type="text" name="admin_name" value="{{ old('admin_name') }}"
                               class="sa-form-input" placeholder="e.g. John Doe">
                        @error('admin_name')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="sa-form-label">Admin Email</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                               class="sa-form-input" placeholder="admin@school.com">
                        @error('admin_email')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="sa-form-label">Admin Password</label>
                        <input type="password" name="admin_password"
                               class="sa-form-input" placeholder="Min. 8 characters">
                        @error('admin_password')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="sa-form-label">Confirm Password</label>
                        <input type="password" name="admin_password_confirmation"
                               class="sa-form-input" placeholder="Repeat password">
                    </div>
                </div>
            </div>

            <div x-show="!createAdmin" style="padding:16px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;">
                <p style="font-size:12.5px;color:#94a3b8;margin:0;">
                    ℹ️ No admin will be created now. You can still add users later from the school's settings page.
                </p>
            </div>
        </div>

        {{-- ── Actions ─────────────────────────────────────── --}}
        <div style="display:flex; align-items:center; justify-content:flex-end; gap:12px;">
            <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">Cancel</a>
            <button type="submit" class="sa-btn sa-btn-primary" style="padding:10px 24px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Create School Instance
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function previewSlug(name) {
    const slugInput = document.getElementById('slugInput');
    // Only auto-fill if the user hasn't typed their own slug
    if (slugInput.dataset.userEdited) return;
    slugInput.placeholder = name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        || 'auto-generated';
}

document.getElementById('slugInput').addEventListener('input', function () {
    this.dataset.userEdited = this.value ? '1' : '';
});
</script>
@endpush
@endsection
