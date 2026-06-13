@extends('layouts.superadmin')

@section('header_title', 'Provision New School')
@section('header_subtitle', 'Deploy a premium multi-tenant school instance')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">
        ← Back to List
    </a>
@endsection

@section('content')
<div style="max-width: 920px; margin: 0 auto;" x-data="schoolWizard()">

    <!-- Custom Styling for Premium Aesthetics -->
    <style>
        /* Wizard Steps Progress bar */
        .wizard-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border-radius: 20px;
            padding: 24px 32px;
            margin-bottom: 30px;
            border: 1px solid var(--sa-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            position: relative;
        }
        .wizard-progress-bar-bg {
            position: absolute;
            top: 50%;
            left: 10%;
            right: 10%;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
            transform: translateY(-50%);
        }
        .wizard-progress-bar-fill {
            position: absolute;
            top: 50%;
            left: 10%;
            height: 3px;
            background: linear-gradient(90deg, #7c3aed, #4f46e5);
            z-index: 2;
            transform: translateY(-50%);
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .wizard-step-item {
            position: relative;
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            padding: 0 10px;
        }
        .wizard-step-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            color: #64748b;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 0 0 4px #ffffff;
        }
        .wizard-step-item.active .wizard-step-circle {
            border-color: #7c3aed;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #ffffff;
            box-shadow: 0 0 0 4px #ffffff, 0 4px 12px rgba(124, 58, 237, 0.25);
        }
        .wizard-step-item.completed .wizard-step-circle {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 0 0 4px #ffffff;
        }
        .wizard-step-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            margin-top: 8px;
            transition: color 0.3s;
        }
        .wizard-step-item.active .wizard-step-label {
            color: #0f172a;
        }
        .wizard-step-item.completed .wizard-step-label {
            color: #10b981;
        }

        /* Premium Visual Plan Cards */
        .plan-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        .plan-card {
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 20px;
            padding: 24px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
            border-color: #94a3b8;
        }
        .plan-card.selected {
            border-color: #7c3aed;
            box-shadow: 0 12px 30px rgba(124, 58, 237, 0.08);
            background: linear-gradient(180deg, #ffffff 0%, rgba(124, 58, 237, 0.01) 100%);
        }
        .plan-card-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 999px;
            letter-spacing: 0.05em;
        }
        .plan-card.free-tier.selected { border-color: #64748b; }
        .plan-card.pro-tier.selected { border-color: #3b82f6; }
        .plan-card.enterprise-tier.selected { border-color: #7c3aed; }
        
        .badge-free { background: #f1f5f9; color: #475569; }
        .badge-pro { background: #eff6ff; color: #1d4ed8; }
        .badge-enterprise { background: #faf5ff; color: #7c3aed; }

        .plan-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        .plan-icon.free { background: #f8fafc; color: #64748b; }
        .plan-icon.pro { background: #eff6ff; color: #3b82f6; }
        .plan-icon.enterprise { background: #faf5ff; color: #7c3aed; }

        .plan-name { font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .plan-price { font-size: 26px; font-weight: 900; color: #0f172a; display: flex; align-items: baseline; gap: 2px; }
        .plan-price span { font-size: 13px; font-weight: 600; color: #64748b; }
        .plan-desc { font-size: 12px; color: #64748b; margin-top: 8px; margin-bottom: 16px; line-height: 1.5; }
        
        /* Plan feature checks */
        .plan-features-list { list-style: none; padding: 0; margin: 16px 0 0 0; border-top: 1px solid #f1f5f9; padding-top: 16px; }
        .plan-feature-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #334155; margin-bottom: 8px; font-weight: 500; }
        .plan-feature-item svg { width: 14px; height: 14px; color: #10b981; flex-shrink: 0; }

        /* Step Content Panels */
        .wizard-panel {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid var(--sa-border);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }
        .wizard-panel-header {
            padding: 24px 32px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .wizard-panel-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .wizard-panel-title svg {
            width: 20px;
            height: 20px;
            color: #7c3aed;
        }
        .wizard-panel-body {
            padding: 32px;
        }

        /* Active plugin badges grid */
        .plugins-indicator-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 16px;
        }
        .plugin-indicator-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .plugin-indicator-card:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }
        .plugin-indicator-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .plugin-indicator-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
        }
        .plugin-indicator-badge {
            font-size: 9px;
            font-weight: 800;
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 6px;
            border-radius: 999px;
            margin-left: auto;
        }

        /* Navigation Buttons */
        .nav-buttons-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 30px;
        }
        .wizard-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        .wizard-btn-next {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.2);
        }
        .wizard-btn-next:hover {
            background: linear-gradient(135deg, #6d28d9, #4338ca);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.3);
        }
        .wizard-btn-prev {
            background: #ffffff;
            color: #475569;
            border-color: #e2e8f0;
        }
        .wizard-btn-prev:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .wizard-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Inline visual cues */
        .step-info-badge {
            background: rgba(124, 58, 237, 0.08);
            color: #7c3aed;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 700;
        }
    </style>

    <!-- Top Wizard Progress Tracking Bar -->
    <div class="wizard-steps">
        <div class="wizard-progress-bar-bg"></div>
        <div class="wizard-progress-bar-fill" :style="'width: ' + progressPercent + '%'"></div>

        <!-- Step 1 -->
        <div class="wizard-step-item" :class="{ 'active': currentStep === 1, 'completed': currentStep > 1 }">
            <div class="wizard-step-circle">
                <template x-if="currentStep > 1">
                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="currentStep <= 1"><span>1</span></template>
            </div>
            <span class="wizard-step-label">School Profile</span>
        </div>

        <!-- Step 2 -->
        <div class="wizard-step-item" :class="{ 'active': currentStep === 2, 'completed': currentStep > 2 }">
            <div class="wizard-step-circle">
                <template x-if="currentStep > 2">
                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="currentStep <= 2"><span>2</span></template>
            </div>
            <span class="wizard-step-label">Choose Plan</span>
        </div>

        <!-- Step 3 -->
        <div class="wizard-step-item" :class="{ 'active': currentStep === 3, 'completed': currentStep > 3 }">
            <div class="wizard-step-circle">
                <template x-if="currentStep > 3">
                    <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="currentStep <= 3"><span>3</span></template>
            </div>
            <span class="wizard-step-label">Limits &amp; Status</span>
        </div>

        <!-- Step 4 -->
        <div class="wizard-step-item" :class="{ 'active': currentStep === 4, 'completed': currentStep > 4 }">
            <div class="wizard-step-circle">
                <span>4</span>
            </div>
            <span class="wizard-step-label">Admin User</span>
        </div>
    </div>

    <!-- Main Registration Form -->
    <form action="{{ route('superadmin.tenants.store') }}" method="POST" id="school-provisioning-form">
        @csrf

        {{-- ── STEP 1: School Profile ────────────────── --}}
        <div class="wizard-panel" x-show="currentStep === 1" x-transition.opacity.duration.300ms>
            <div class="wizard-panel-header">
                <span class="wizard-panel-title">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Step 1: School Information
                </span>
                <span class="step-info-badge">Required details</span>
            </div>
            <div class="wizard-panel-body">
                <p style="margin:0 0 24px; color:#64748b; font-size:13.5px; line-height:1.6;">
                    Specify basic details about the new school instance below. The school name will be used to automatically generate its default login URL.
                </p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div>
                        <label class="sa-form-label">School Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="name" x-model="schoolName" required
                               class="sa-form-input" placeholder="e.g. Greenwood High School">
                        @error('name')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Custom Subdomain / Domain <span style="color:#94a3b8;">(optional)</span></label>
                        <input type="text" name="domain" value="{{ old('domain') }}"
                               class="sa-form-input" placeholder="e.g. portal.greenwood.edu">
                        @error('domain')<div class="sa-form-error">{{ $message }}</div>@enderror
                        <div class="sa-form-hint">Leave blank — a secure automatic URL slug will be generated.</div>
                    </div>

                    <div>
                        <label class="sa-form-label">Contact Email Address</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                               class="sa-form-input" placeholder="admin@school.com">
                        @error('contact_email')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="sa-form-label">Contact Phone Number</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                               class="sa-form-input" placeholder="+234 ...">
                        @error('contact_phone')<div class="sa-form-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STEP 2: Choose Plan ────────────────────── --}}
        <div class="wizard-panel" x-show="currentStep === 2" x-transition.opacity.duration.300ms x-cloak>
            <div class="wizard-panel-header">
                <span class="wizard-panel-title">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    Step 2: Subscription &amp; Tier Pricing Plan
                </span>
                <span class="step-info-badge">Plan-Based Plugins</span>
            </div>
            <div class="wizard-panel-body">
                <p style="margin:0 0 24px; color:#64748b; font-size:13.5px; line-height:1.6;">
                    Select the billing tier for this school. The selected plan determines which core marketplace plugins are **automatically activated and deployed** for this school immediately, with their fees bundled inside their offline setup payment.
                </p>

                <!-- Hidden Input bound to Alpine plan selection -->
                <input type="hidden" name="plan" :value="selectedPlan">

                <!-- Plan Selection Cards -->
                <div class="plan-cards-grid">
                    <!-- Basic Card -->
                    <div class="plan-card free-tier" :class="{ 'selected': selectedPlan === 'basic' }" @click="selectPlan('basic')">
                        <span class="plan-card-badge badge-free">Basic</span>
                        <div>
                            <div class="plan-icon free">
                                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <h4 class="plan-name">Basic Plan</h4>
                            <div class="plan-price">₦1,000<span>/student/term</span></div>
                            <p class="plan-desc">Standard databases with student-based termly billing. Billing starts immediately.</p>
                        </div>
                        <ul class="plan-features-list">
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Max 50 Students</li>
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Max 5 Teachers</li>
                            <li class="plan-feature-item" style="color:#94a3b8;"><svg style="color:#94a3b8;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg> No Marketplace Plugins</li>
                        </ul>
                    </div>

                    <!-- Pro Card -->
                    <div class="plan-card pro-tier" :class="{ 'selected': selectedPlan === 'pro' }" @click="selectPlan('pro')">
                        <span class="plan-card-badge badge-pro">Popular</span>
                        <div>
                            <div class="plan-icon pro">
                                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h4 class="plan-name">Pro Plan</h4>
                            <div class="plan-price">₦15,000<span>/month</span></div>
                            <p class="plan-desc">Advanced educational suite + 5 core modules pre-loaded and auto-activated.</p>
                        </div>
                        <ul class="plan-features-list">
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Max 500 Students</li>
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Max 50 Teachers</li>
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> 5 Core Plugins Pre-loaded</li>
                        </ul>
                    </div>

                    <!-- Enterprise Card -->
                    <div class="plan-card enterprise-tier" :class="{ 'selected': selectedPlan === 'enterprise' }" @click="selectPlan('enterprise')">
                        <span class="plan-card-badge badge-enterprise">Unlimited</span>
                        <div>
                            <div class="plan-icon enterprise">
                                <svg style="width:20px;height:20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <h4 class="plan-name">Enterprise Plan</h4>
                            <div class="plan-price">₦50,000<span>/month</span></div>
                            <p class="plan-desc">Ultimate school management. Automatically unlocks &amp; provisions all system modules.</p>
                        </div>
                        <ul class="plan-features-list">
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Unlimited Students</li>
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Unlimited Teachers</li>
                            <li class="plan-feature-item"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> All Active Plugins Included</li>
                        </ul>
                    </div>
                </div>

                <!-- Dynamic Visual Plugin Provisioning Information -->
                <div style="background:#f8fafc; border-radius:16px; padding:20px; border:1px solid #e2e8f0; margin-top:28px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <svg style="width:18px;height:18px;color:#7c3aed;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <h5 style="margin:0; font-size:13.5px; font-weight:800; color:#0f172a;">Plan-Based Plugins Provisioning Summary</h5>
                    </div>

                    <!-- Dynamic plugin active summaries based on selected card -->
                    <div x-show="selectedPlan === 'basic'">
                        <p style="margin:0; font-size:12.5px; color:#64748b; line-height:1.6;">
                            ℹ️ The **Basic plan** does not include any active marketplace plugins. The school's admin can manually subscribe and pay for plugins individually inside their marketplace if needed later.
                        </p>
                    </div>

                    <div x-show="selectedPlan === 'pro'">
                        <p style="margin:0 0 14px; font-size:12.5px; color:#64748b; line-height:1.6;">
                            ✨ Selecting the **Pro Plan** will automatically deploy, register and pre-activate the following **5 core plugins** for this school immediately, with setup fees billed as paid directly:
                        </p>
                        <div class="plugins-indicator-grid">
                            <div class="plugin-indicator-card">
                                <div class="plugin-indicator-icon">📝</div>
                                <span class="plugin-indicator-name">CBT (Computer-Based Testing)</span>
                                <span class="plugin-indicator-badge">Enabled</span>
                            </div>
                            <div class="plugin-indicator-card">
                                <div class="plugin-indicator-icon">📚</div>
                                <span class="plugin-indicator-name">Homework &amp; Assignments</span>
                                <span class="plugin-indicator-badge">Enabled</span>
                            </div>
                            <div class="plugin-indicator-card">
                                <div class="plugin-indicator-icon">🌐</div>
                                <span class="plugin-indicator-name">E-Learning &amp; Study Notes</span>
                                <span class="plugin-indicator-badge">Enabled</span>
                            </div>
                            <div class="plugin-indicator-card">
                                <div class="plugin-indicator-icon">🤖</div>
                                <span class="plugin-indicator-name">WhatsApp Notification Bot</span>
                                <span class="plugin-indicator-badge">Enabled</span>
                            </div>
                            <div class="plugin-indicator-card">
                                <div class="plugin-indicator-icon">👥</div>
                                <span class="plugin-indicator-name">Student/Parent Dashboard Portal</span>
                                <span class="plugin-indicator-badge">Enabled</span>
                            </div>
                        </div>
                    </div>

                    <div x-show="selectedPlan === 'enterprise'">
                        <p style="margin:0 0 14px; font-size:12.5px; color:#64748b; line-height:1.6;">
                            🚀 Selecting the **Enterprise Plan** automatically provisions and activates **all active marketplace plugins** in the entire system immediately, with no action needed by the school administrators.
                        </p>
                        <div class="plugins-indicator-grid">
                            <div class="plugin-indicator-card">
                                <div class="plugin-indicator-icon" style="background:#fef2f2;color:#ef4444;">🛡️</div>
                                <span class="plugin-indicator-name">All Active Marketplace Components</span>
                                <span class="plugin-indicator-badge" style="background:#dcfce7;color:#15803d;">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STEP 3: Quotas & Limits ───────────────── --}}
        <div class="wizard-panel" x-show="currentStep === 3" x-transition.opacity.duration.300ms x-cloak>
            <div class="wizard-panel-header">
                <span class="wizard-panel-title">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    Step 3: Platform Quotas &amp; Limits Configuration
                </span>
                <span class="step-info-badge">Auto-tuned by Plan</span>
            </div>
            <div class="wizard-panel-body">
                <p style="margin:0 0 24px; color:#64748b; font-size:13.5px; line-height:1.6;">
                    Define the database limitations and baseline values for this school instance. We have **automatically prefilled recommended defaults** based on your selected plan.
                </p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px;">
                    <div>
                        <label class="sa-form-label">Max Student Capacity <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="max_students" x-model="maxStudents" required min="1"
                               class="sa-form-input" style="font-family:monospace; font-weight:700;">
                        @error('max_students')<div class="sa-form-error">{{ $message }}</div>@enderror
                        <div class="sa-form-hint">Suggested default for <strong x-text="selectedPlan.toUpperCase()"></strong> tier is used.</div>
                    </div>

                    <div>
                        <label class="sa-form-label">Max Teacher capacity <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="max_teachers" x-model="maxTeachers" required min="1"
                               class="sa-form-input" style="font-family:monospace; font-weight:700;">
                        @error('max_teachers')<div class="sa-form-error">{{ $message }}</div>@enderror
                        <div class="sa-form-hint">Suggested default for <strong x-text="selectedPlan.toUpperCase()"></strong> tier is used.</div>
                    </div>

                    <div>
                        <label class="sa-form-label">School Instance Deployment Status <span style="color:#ef4444;">*</span></label>
                        <div style="position:relative;">
                            <select name="status" required class="sa-form-input" style="appearance:none; padding-right:36px; font-weight:600;">
                                <option value="active" @selected(old('status', 'active')=='active')>Active / Live</option>
                                <option value="pending" @selected(old('status')=='pending')>Pending Setup</option>
                                <option value="suspended" @selected(old('status')=='suspended')>Suspended</option>
                            </select>
                            <div style="position:absolute; top:50%; right:12px; pointer-events:none; color:#94a3b8; transform:translateY(-50%);">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="sa-form-label">Subscription Expiry Date</label>
                        <input type="date" name="expires_at" x-model="expiresAt"
                               class="sa-form-input" style="font-family:monospace; font-weight:700;">
                        @error('expires_at')<div class="sa-form-error">{{ $message }}</div>@enderror
                        <div class="sa-form-hint">Leave blank for no expiry. Auto-prefilled for paid plans.</div>
                    </div>
                </div>

                {{-- Single-school upgrade adoption (first tenant only) --}}
                @if(!empty($isFirstTenant) && !empty($legacyDataExists))
                    <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:16px; padding:20px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                            <svg style="width:18px;height:18px;color:#f97316;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h5 style="margin:0; font-size:13.5px; font-weight:800; color:#c2410c;">Legacy Single-School Data Detected</h5>
                        </div>
                        <p style="margin:0 0 14px; color:#7c2d12; font-size:12.5px; line-height:1.6;">
                            This database contains existing records with no school identifier (`tenant_id`). You can merge/adopt this data into this first school instance.
                        </p>
                        <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer;">
                            <input type="hidden" name="adopt_existing_data" value="0">
                            <input type="checkbox" name="adopt_existing_data" value="1" checked style="margin-top:4px;">
                            <div>
                                <div style="font-weight:700; color:#7c2d12; font-size:13px;">Merge/adopt legacy single-school records</div>
                                <div style="color:#9a3412; font-size:11.5px; line-height:1.5; margin-top:2px;">
                                    Highly recommended to keep your original data active. Do not use if you are setting up a fresh multi-tenant node.
                                </div>
                            </div>
                        </label>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── STEP 4: Admin Account ──────────────────── --}}
        <div class="wizard-panel" x-show="currentStep === 4" x-transition.opacity.duration.300ms x-cloak>
            <div class="wizard-panel-header">
                <span class="wizard-panel-title">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Step 4: Initial School Administrator Account
                </span>
                <span class="step-info-badge">Optional</span>
            </div>
            <div class="wizard-panel-body" x-data="{ createAdmin: {{ old('create_admin', 1) ? 'true' : 'false' }} }">
                <p style="margin:0 0 24px; color:#64748b; font-size:13.5px; line-height:1.6;">
                    Optionally provision a primary administrator user for this school. The administrator can log in securely and start managing classes, students, and teachers.
                </p>

                <!-- Checkbox to toggle admin creation -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:18px 24px; margin-bottom:28px;">
                    <label style="display:flex; align-items:center; gap:12px; cursor:pointer;">
                        <input type="checkbox" name="create_admin" value="1" x-model="createAdmin" style="width:16px; height:16px; accent-color:#7c3aed;">
                        <div>
                            <strong style="color:#0f172a; font-size:13.5px;">Create a primary administrator user account now</strong>
                            <div style="color:#64748b; font-size:12px; margin-top:2px;">Check this to assign their username and password immediately.</div>
                        </div>
                    </label>
                </div>

                <!-- Admin Fields toggled via Alpine -->
                <div x-show="createAdmin" x-collapse x-transition:enter.duration.400ms x-transition:leave.duration.300ms>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:12px;">
                        <div>
                            <label class="sa-form-label">Admin Full Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="admin_name" value="{{ old('admin_name') }}"
                                   :required="createAdmin" class="sa-form-input" placeholder="e.g. John Doe">
                            @error('admin_name')<div class="sa-form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="sa-form-label">Admin Email <span style="color:#ef4444;">*</span></label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                                   :required="createAdmin" class="sa-form-input" placeholder="admin@school.com">
                            @error('admin_email')<div class="sa-form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="sa-form-label">Admin Password <span style="color:#ef4444;">*</span></label>
                            <input type="password" name="admin_password"
                                   :required="createAdmin" class="sa-form-input" placeholder="Min. 8 characters">
                            @error('admin_password')<div class="sa-form-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="sa-form-label">Confirm Password <span style="color:#ef4444;">*</span></label>
                            <input type="password" name="admin_password_confirmation"
                                   :required="createAdmin" class="sa-form-input" placeholder="Repeat password">
                        </div>
                    </div>
                </div>

                <div x-show="!createAdmin" style="padding:16px 20px; background:#fff1f2; border:1px solid #fecdd3; border-radius:12px;" x-transition>
                    <p style="font-size:12.5px; color:#be123c; margin:0; font-weight:500;">
                        ⚠️ You've disabled initial administrator setup. No admin user will be created. You must manually add a user to this school database node later to enable school login.
                    </p>
                </div>
            </div>
        </div>

        <!-- Dynamic Navigation Controls at bottom -->
        <div class="nav-buttons-container">
            <!-- Back Button -->
            <button type="button" class="wizard-btn wizard-btn-prev" 
                    @click="prevStep()" x-show="currentStep > 1" style="display:none;" :style="currentStep > 1 ? 'display:inline-flex;' : ''">
                ← Back
            </button>
            <div x-show="currentStep === 1" style="width: 20px;"></div> <!-- placeholder to keep Next aligned right -->

            <!-- Next & Submit Buttons -->
            <button type="button" class="wizard-btn wizard-btn-next" 
                    @click="nextStep()" x-show="currentStep < 4">
                Continue →
            </button>

            <button type="submit" class="wizard-btn wizard-btn-next" 
                    x-show="currentStep === 4" style="background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);" :style="currentStep === 4 ? 'display:inline-flex;' : 'display:none;'">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" style="width:14px;height:14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Deploy School Instance
            </button>
        </div>

    </form>
</div>

<script>
    function schoolWizard() {
        return {
            currentStep: 1,
            selectedPlan: 'basic',
            schoolName: '{{ old('name', '') }}',
            maxStudents: 50,
            maxTeachers: 5,
            expiresAt: '',
            
            get progressPercent() {
                // Return progress indicator bar percent mapping
                const steps = { 1: 10, 2: 40, 3: 70, 4: 100 };
                return steps[this.currentStep] || 10;
            },

            init() {
                // Initialize default plan if old plan input exists
                const oldPlan = '{{ old('plan', 'basic') }}';
                this.selectPlan(oldPlan);
            },

            selectPlan(plan) {
                this.selectedPlan = plan;
                
                // Prefill limits according to dynamic standard options
                if (plan === 'basic') {
                    this.maxStudents = 50;
                    this.maxTeachers = 5;
                } else if (plan === 'pro') {
                    this.maxStudents = 500;
                    this.maxTeachers = 50;
                } else if (plan === 'enterprise') {
                    this.maxStudents = 10000;
                    this.maxTeachers = 1000;
                }
                
                // Expiry is blank by default for all plans (billing starts right away)
                this.expiresAt = '';
            },

            nextStep() {
                // Step 1 Validation: Check school Name is filled
                if (this.currentStep === 1) {
                    if (!this.schoolName.trim()) {
                        alert('School Name is required.');
                        return;
                    }
                }
                
                if (this.currentStep < 4) {
                    this.currentStep++;
                }
            },

            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            }
        }
    }
</script>
@endsection
