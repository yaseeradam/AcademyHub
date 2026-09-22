@extends('layouts.superadmin')

@section('header_title', 'Pricing Settings')
@section('header_subtitle', 'Configure system-wide student subscription rates')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    
    @if (session('success'))
        <div class="sa-alert success">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="sa-alert error">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="sa-panel">
        <div class="sa-panel-header">
            <span class="sa-panel-title">Termly Pricing Settings</span>
        </div>
        <div style="padding: 24px;">
            <p style="margin: 0 0 24px; color: #64748b; font-size: 13.5px; line-height: 1.6;">
                Configure the subscription fee charged per active student per academic term. 
                This base pricing rate is applied system-wide to calculate invoices, school totals, 
                and core platform subscriptions.
            </p>

            <form action="{{ route('superadmin.settings.pricing.update') }}" method="POST">
                @csrf

                <div style="margin-bottom: 20px;">
                    <label for="student_termly_fee" class="sa-form-label">Subscription Fee Per Student (₦)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 700; font-size: 14px;">₦</span>
                        <input type="number" 
                               id="student_termly_fee" 
                               name="student_termly_fee" 
                               value="{{ old('student_termly_fee', $currentFee) }}" 
                               class="sa-form-input" 
                               placeholder="e.g. 1000" 
                               style="padding-left: 32px;"
                               required 
                               min="0" 
                               max="1000000" 
                               step="0.01">
                    </div>
                    @error('student_termly_fee')
                        <div class="sa-form-error">{{ $message }}</div>
                    @enderror
                    <div class="sa-form-hint">This value is currently set to ₦{{ number_format($currentFee) }}.</div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--sa-border); padding-top: 20px; margin-top: 20px;">
                    <button type="submit" class="sa-btn sa-btn-primary">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
