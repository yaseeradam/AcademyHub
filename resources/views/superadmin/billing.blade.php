@extends('layouts.superadmin')

@section('header_title', 'Billing & Subscriptions')
@section('header_subtitle', 'School revenue · Subscription status · Payment tracking')

@section('header_actions')
    <a href="{{ route('superadmin.tenants.index') }}" class="sa-btn sa-btn-ghost">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
        </svg>
        All Schools
    </a>
@endsection

@section('content')

{{-- ── Summary Stats ──────────────────────────────────────────── --}}
<div class="sa-stats-grid" style="grid-template-columns:repeat(5,1fr);margin-bottom:24px;">

    <div class="sa-stat-card emerald">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Revenue</div>
            <div class="sa-stat-value">₦{{ number_format($totalRevenue) }}</div>
            <div class="sa-stat-sub">All school transactions</div>
        </div>
    </div>

    <div class="sa-stat-card teal">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Students</div>
            <div class="sa-stat-value">{{ number_format($totalStudents) }}</div>
            <div class="sa-stat-sub">Across all schools</div>
        </div>
    </div>

    <div class="sa-stat-card indigo">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total Teachers</div>
            <div class="sa-stat-value">{{ number_format($totalTeachers) }}</div>
            <div class="sa-stat-sub">Across all schools</div>
        </div>
    </div>

    <div class="sa-stat-card rose">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Overdue</div>
            <div class="sa-stat-value">{{ $overdueCount }}</div>
            <div class="sa-stat-sub">Past subscription date</div>
        </div>
    </div>

    <div class="sa-stat-card orange">
        <div class="sa-stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">In Grace Period</div>
            <div class="sa-stat-value">{{ $graceCount }}</div>
            <div class="sa-stat-sub">Within 7-day grace</div>
        </div>
    </div>

</div>

{{-- ── Schools Billing Table ──────────────────────────────────── --}}
<div class="sa-panel">
    <div class="sa-panel-header">
        <span class="sa-panel-title">
            <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
            </svg>
            Schools — Sorted by Revenue
        </span>
        <span style="font-size:12px;color:#94a3b8;">{{ $rows->count() }} schools</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>School</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th style="text-align:center;">Students</th>
                    <th style="text-align:center;">Teachers</th>
                    <th style="text-align:right;">Revenue Collected</th>
                    <th style="text-align:right;">Sub. Fee</th>
                    <th>Expiry</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                @php $t = $row['tenant']; @endphp
                <tr style="{{ $row['is_past_due'] && !$row['in_grace'] ? 'background:#fff5f5;' : ($row['in_grace'] ? 'background:#fffbeb;' : '') }}">

                    {{-- Rank --}}
                    <td style="font-size:13px;font-weight:700;width:40px;text-align:center;">
                        @if($i === 0)
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#fef3c7;color:#d97706;font-size:11px;font-weight:800;">1</span>
                        @elseif($i === 1)
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#f1f5f9;color:#64748b;font-size:11px;font-weight:800;">2</span>
                        @elseif($i === 2)
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#fdf4e7;color:#b45309;font-size:11px;font-weight:800;">3</span>
                        @else
                            <span style="color:#94a3b8;font-size:12px;">{{ $i + 1 }}</span>
                        @endif
                    </td>

                    {{-- School --}}
                    <td>
                        <div style="font-weight:700;color:#1e293b;font-size:13.5px;">{{ $t->name }}</div>
                        @if($t->contact_email)
                            <div style="font-size:11px;color:#94a3b8;">{{ $t->contact_email }}</div>
                        @endif
                        <div style="font-size:11px;color:#94a3b8;font-family:monospace;">{{ $t->slug }}</div>
                    </td>

                    {{-- Plan --}}
                    <td><span class="sa-badge {{ $t->plan }}">{{ ucfirst($t->plan) }}</span></td>

                    {{-- Status --}}
                    <td>
                        <span class="sa-badge {{ $t->status }}">
                            <span class="sa-badge-dot"></span>{{ ucfirst($t->status) }}
                        </span>
                    </td>

                    {{-- Students --}}
                    <td style="text-align:center;">
                        <div style="font-size:15px;font-weight:800;color:#0f172a;">{{ number_format($row['student_count']) }}</div>
                        <div style="font-size:10px;color:#94a3b8;">/ {{ number_format($t->max_students) }} max</div>
                        @php $stuPct = $t->max_students > 0 ? min(100, round($row['student_count'] / $t->max_students * 100)) : 0; @endphp
                        <div style="margin-top:4px;height:4px;background:#f1f5f9;border-radius:99px;overflow:hidden;width:60px;margin-inline:auto;">
                            <div style="height:100%;width:{{ $stuPct }}%;background:{{ $stuPct >= 90 ? '#ef4444' : ($stuPct >= 70 ? '#f59e0b' : '#10b981') }};border-radius:99px;"></div>
                        </div>
                    </td>

                    {{-- Teachers --}}
                    <td style="text-align:center;">
                        <div style="font-size:15px;font-weight:800;color:#0f172a;">{{ number_format($row['teacher_count']) }}</div>
                        <div style="font-size:10px;color:#94a3b8;">/ {{ number_format($t->max_teachers) }} max</div>
                    </td>

                    {{-- Revenue --}}
                    <td style="text-align:right;">
                        <div style="font-size:15px;font-weight:800;color:#059669;">₦{{ number_format($row['revenue_collected']) }}</div>
                        <div style="font-size:10px;color:#94a3b8;">school fees</div>
                    </td>

                    {{-- Subscription Fee --}}
                    <td style="text-align:right;">
                        @if($row['subscription_fee'] > 0)
                            <div style="font-size:13px;font-weight:700;color:#4f46e5;">₦{{ number_format($row['subscription_fee']) }}</div>
                            <div style="font-size:10px;color:#94a3b8;">platform fee</div>
                        @else
                            <span style="font-size:12px;color:#94a3b8;">—</span>
                        @endif
                    </td>

                    {{-- Expiry --}}
                    <td style="min-width:120px;">
                        @if($row['due_date'])
                            <div style="font-size:12.5px;font-weight:700;color:{{ $row['is_past_due'] ? '#dc2626' : ($row['days_left'] !== null && $row['days_left'] <= 14 ? '#d97706' : '#1e293b') }};">
                                {{ $row['due_date']->format('M j, Y') }}
                            </div>
                            @if($row['is_past_due'])
                                @if($row['in_grace'])
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:#fef3c7;color:#92400e;">
                                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Grace: {{ 7 - $row['days_past_due'] }}d left
                                    </span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:#fee2e2;color:#991b1b;">
                                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $row['days_past_due'] }}d overdue
                                    </span>
                                @endif
                            @elseif($row['days_left'] !== null && $row['days_left'] <= 14)
                                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:#fef3c7;color:#92400e;">
                                    {{ $row['days_left'] }}d left
                                </span>
                            @else
                                <span style="font-size:11px;color:#94a3b8;">{{ $row['days_left'] }}d left</span>
                            @endif
                        @else
                            <span style="font-size:12px;color:#94a3b8;">Not set</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td style="text-align:right;">
                        <button onclick="openPaymentModal({{ $t->id }}, '{{ addslashes($t->name) }}', '{{ $t->plan }}', '{{ $row['due_date'] ? $row['due_date']->format('Y-m-d') : '' }}', {{ $row['subscription_fee'] }})"
                                class="sa-btn sa-btn-primary" style="font-size:12px;padding:6px 12px;">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Update
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center;padding:48px;color:#94a3b8;">No schools found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Payment / Subscription Modal ──────────────────────────── --}}
<div id="paymentModal" style="display:none;position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.5);align-items:center;justify-content:center;"
     onclick="if(event.target===this)closePaymentModal()">
    <div style="background:#fff;border-radius:20px;padding:28px;width:460px;max-width:92vw;box-shadow:0 24px 64px rgba(0,0,0,.2);">

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:44px;height:44px;background:#ede9fe;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;color:#7c3aed;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:16px;font-weight:800;color:#1e293b;" id="payModalTitle">Update Subscription</div>
                <div style="font-size:12px;color:#94a3b8;" id="payModalSub">Set plan, fee and expiry date</div>
            </div>
        </div>

        <form id="paymentForm" method="POST" action="">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                <div>
                    <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Plan</label>
                    <select name="plan" id="payPlan" style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;background:#fff;">
                        <option value="free">Free</option>
                        <option value="pro">Pro</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:5px;">Platform Fee (₦)</label>
                    <input type="number" name="subscription_fee" id="payFee" min="0" step="500"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:monospace;"
                           placeholder="e.g. 50000">
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:11px;font-weight:700;color:#475569;display:block;margin-bottom:5px;">New Subscription Expiry Date <span style="color:#ef4444;">*</span></label>
                <input type="date" name="subscription_due_date" id="payDueDate" required
                       style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;">
                <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;">
                    <button type="button" onclick="setExpiry(1)"  style="font-size:11px;padding:3px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;cursor:pointer;font-weight:600;">+1 month</button>
                    <button type="button" onclick="setExpiry(3)"  style="font-size:11px;padding:3px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;cursor:pointer;font-weight:600;">+3 months</button>
                    <button type="button" onclick="setExpiry(6)"  style="font-size:11px;padding:3px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;cursor:pointer;font-weight:600;">+6 months</button>
                    <button type="button" onclick="setExpiry(12)" style="font-size:11px;padding:3px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#f8fafc;cursor:pointer;font-weight:600;">+1 year</button>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closePaymentModal()"
                        style="flex:1;padding:11px;background:#f1f5f9;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;color:#475569;">
                    Cancel
                </button>
                <button type="submit"
                        style="flex:2;padding:11px;background:#4f46e5;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;color:#fff;box-shadow:0 2px 8px rgba(79,70,229,.3);display:flex;align-items:center;justify-content:center;gap:7px;">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Subscription
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openPaymentModal(tenantId, name, plan, dueDate, fee) {
    document.getElementById('payModalTitle').textContent = name;
    document.getElementById('payModalSub').textContent = 'Update subscription for ' + name;
    document.getElementById('payPlan').value = plan;
    document.getElementById('payDueDate').value = dueDate || '';
    document.getElementById('payFee').value = fee > 0 ? fee : '';
    document.getElementById('paymentForm').action = '/superadmin/tenants/' + tenantId + '/payment';
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'flex';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
}

function setExpiry(months) {
    const current = document.getElementById('payDueDate').value;
    const base = current ? new Date(current) : new Date();
    base.setMonth(base.getMonth() + months);
    document.getElementById('payDueDate').value = base.toISOString().split('T')[0];
}
</script>
@endpush
