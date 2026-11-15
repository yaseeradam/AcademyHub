<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>K40 Student ID Directory – {{ $classLabel }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 7mm 10mm 7mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            color: #0F172A;
            background: #ffffff;
            line-height: 1.25;
        }

        /* ── Poster Decorative Border ── */
        .poster-outer {
            border: 2px solid #C59B27;
            padding: 3px;
            background: #ffffff;
        }

        .poster-inner {
            border: 1.5px solid #0C1E40;
            padding: 6px 8px;
            background: #ffffff;
        }

        /* ── Header ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .logo-box {
            width: 52px;
            height: 52px;
            text-align: center;
        }

        .logo-img {
            max-width: 52px;
            max-height: 52px;
        }

        .logo-placeholder {
            width: 48px;
            height: 48px;
            border: 2px solid #C59B27;
            border-radius: 50%;
            text-align: center;
            background: #0C1E40;
            color: #C59B27;
            font-size: 18px;
            line-height: 44px;
            font-weight: bold;
        }

        .school-title {
            font-size: 13px;
            font-weight: 900;
            color: #0C1E40;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .motto-line {
            text-align: center;
            font-size: 7.5px;
            color: #8C6D15;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 2px;
            letter-spacing: 0.4px;
        }

        .contact-line {
            text-align: center;
            font-size: 6.8px;
            color: #64748B;
        }

        /* ── Title Ribbon ── */
        .doc-title-box {
            background-color: #0C1E40;
            color: #ffffff;
            text-align: center;
            padding: 5px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
            border-bottom: 2px solid #C59B27;
        }

        .doc-title {
            font-size: 9.5px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #F8FAFC;
        }

        .doc-subtitle {
            font-size: 6.8px;
            color: #CBD5E1;
            margin-top: 1.5px;
            font-weight: 600;
        }

        /* ── KPI Summary Grid ── */
        .info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px;
            margin-bottom: 6px;
        }

        .kpi-card {
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 4px;
            padding: 3.5px 5px;
            text-align: center;
        }

        .kpi-label {
            font-size: 5.8px;
            color: #64748B;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .kpi-val {
            font-size: 9.5px;
            font-weight: 900;
            color: #0C1E40;
            margin-top: 1px;
        }

        /* ── Main Student Table ── */
        .att-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 6px;
        }

        .att-table thead {
            display: table-header-group;
        }

        .att-table tr {
            page-break-inside: avoid;
        }

        .att-table th {
            background: #0C1E40;
            color: #ffffff;
            font-size: 6.6px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 2px;
            text-align: center;
            border: 1px solid #0C1E40;
            letter-spacing: 0.2px;
            overflow: hidden;
        }

        .att-table th.th-left {
            text-align: left;
            padding-left: 4px;
        }

        .att-table td {
            border: 1px solid #CBD5E1;
            padding: 3px 2px;
            text-align: center;
            font-size: 7px;
            vertical-align: middle;
            overflow: hidden;
        }

        .att-table td.td-left {
            text-align: left;
            padding-left: 4px;
        }

        .att-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        /* ── K40 UID Badges ── */
        .k40-badge {
            display: inline-block;
            background: #EFF6FF;
            border: 1.2px solid #2563EB;
            color: #1E3A8A;
            font-family: monospace;
            font-weight: 900;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 3px;
        }

        .k40-pending {
            display: inline-block;
            background: #F1F5F9;
            border: 1px dashed #94A3B8;
            color: #64748B;
            font-size: 6px;
            font-weight: bold;
            padding: 1px 3px;
            border-radius: 3px;
        }

        /* ── Status Pills ── */
        .status-pill {
            display: inline-block;
            padding: 1px 3px;
            border-radius: 3px;
            font-size: 6px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status-active {
            background: #DCFCE7;
            color: #15803D;
            border: 0.5px solid #86EFAC;
        }

        .status-pending {
            background: #FEF3C7;
            color: #B45309;
            border: 0.5px solid #FCD34D;
        }

        .shift-tag {
            font-size: 6.2px;
            color: #334155;
            font-weight: 600;
        }

        .check-box {
            display: inline-block;
            width: 7px;
            height: 7px;
            border: 1px solid #475569;
            margin: 0 1px;
            vertical-align: middle;
            background: #ffffff;
        }

        /* ── Sign-off Footer ── */
        .signoff-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: avoid;
        }

        .signoff-table td {
            vertical-align: top;
            border: none;
            padding: 0 8px;
        }

        .signature-line {
            border-top: 1px dotted #475569;
            width: 140px;
            margin: 16px auto 0 auto;
            text-align: center;
            font-size: 6.5px;
            font-weight: bold;
            color: #1E293B;
            padding-top: 2px;
        }

        .stamp-box {
            width: 75px;
            height: 38px;
            border: 1px dashed #94A3B8;
            border-radius: 4px;
            margin: 0 auto;
            text-align: center;
            line-height: 38px;
            font-size: 6px;
            color: #94A3B8;
            text-transform: uppercase;
        }

        .brand-footer {
            text-align: center;
            font-size: 5.8px;
            color: #94A3B8;
            margin-top: 5px;
            border-top: 1px solid #E2E8F0;
            padding-top: 2px;
        }
    </style>
</head>
<body>

<div class="poster-outer">
    <div class="poster-inner">

        {{-- Header with Logo and School Info --}}
        <table class="header-table">
            <tr>
                <td style="width: 55px;">
                    <div class="logo-box">
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" class="logo-img" alt="School Logo" />
                        @else
                            <div class="logo-placeholder">★</div>
                        @endif
                    </div>
                </td>
                <td style="text-align: center;">
                    <div class="school-title">{{ $schoolName }}</div>
                    @if(!empty($schoolMotto))
                        <div class="motto-line">&ldquo;{{ $schoolMotto }}&rdquo;</div>
                    @endif
                    <div class="contact-line">
                        @if(!empty($schoolAddress)){{ $schoolAddress }} @endif
                        @if(!empty($schoolPhone)) &bull; Tel: {{ $schoolPhone }} @endif
                        @if(!empty($schoolEmail)) &bull; Email: {{ $schoolEmail }} @endif
                    </div>
                </td>
                <td style="width: 120px; text-align: right;">
                    <div style="font-size: 6.8px; color: #475569; line-height: 1.35; border-left: 2px solid #C59B27; padding-left: 5px; text-align: left;">
                        <strong>Session:</strong> {{ $sessionLabel }}<br/>
                        <strong>Term:</strong> {{ $termLabel }}<br/>
                        <strong>Generated:</strong> {{ $generatedAt }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Document Title Ribbon --}}
        <div class="doc-title-box">
            <div class="doc-title">
                ZKTeco K40 Biometric Hardware Enrollment &amp; Student ID Directory
            </div>
            <div class="doc-subtitle">
                Scope: {{ $classLabel }} &bull; Shift Filter: {{ ucfirst($shiftFilter) }} &bull; Status: {{ ucfirst($statusFilter) }}
            </div>
        </div>

        {{-- KPI Metrics Grid --}}
        <table class="info-grid">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Students</div>
                        <div class="kpi-val">{{ $totalCount }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card" style="border-color: #86EFAC; background: #F0FDF4;">
                        <div class="kpi-label" style="color: #166534;">K40 Hardware IDs Assigned</div>
                        <div class="kpi-val" style="color: #15803D;">{{ $assignedCount }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card" style="border-color: #FCD34D; background: #FFFBEB;">
                        <div class="kpi-label" style="color: #92400E;">Pending ID Assignment</div>
                        <div class="kpi-val" style="color: #B45309;">{{ $unassignedCount }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card" style="border-color: #93C5FD; background: #EFF6FF;">
                        <div class="kpi-label" style="color: #1E40AF;">Enrollment Coverage</div>
                        <div class="kpi-val" style="color: #1D4ED8;">{{ $coveragePercent }}%</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Main Student Table (100% total column width) --}}
        <table class="att-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 12%;">K40 UID</th>
                    <th class="th-left" style="width: 24%;">Student Full Name</th>
                    <th style="width: 14%;">ADM No</th>
                    <th style="width: 16%;">Class &amp; Section</th>
                    <th style="width: 5%;">Sex</th>
                    <th style="width: 8%;">Shift</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 9%;">Checklist</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $idx => $student)
                    @php
                        $k40Uid = $student->k40_uid;
                        $hasK40 = !empty($k40Uid);
                        $gender = strtoupper(substr((string) ($student->gender ?? 'M'), 0, 1));
                        $shift = method_exists($student, 'getShift') ? $student->getShift() : ($student->shift ?? 'Western');
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            @if($hasK40)
                                <span class="k40-badge">#{{ $k40Uid }}</span>
                            @else
                                <span class="k40-pending">PENDING</span>
                            @endif
                        </td>
                        <td class="td-left" style="font-weight: bold; color: #0C1E40;">
                            {{ $student->full_name }}
                        </td>
                        <td style="font-family: monospace; font-size: 6.6px; color: #334155;">
                            {{ $student->admission_number ?: '—' }}
                        </td>
                        <td style="font-size: 6.6px; color: #334155;">
                            {{ $student->schoolClass?->name ?? '—' }}
                            @if($student->section)
                                <span style="color: #64748B;">({{ $student->section->name }})</span>
                            @endif
                        </td>
                        <td style="font-weight: bold; color: {{ $gender === 'M' ? '#1E40AF' : '#9D174D' }};">
                            {{ $gender }}
                        </td>
                        <td class="shift-tag">
                            {{ $shift }}
                        </td>
                        <td>
                            @if($hasK40)
                                <span class="status-pill status-active">Active</span>
                            @else
                                <span class="status-pill status-pending">Pending</span>
                            @endif
                        </td>
                        <td style="font-size: 5.8px; color: #475569; text-align: center;">
                            <span class="check-box"></span>FP <span class="check-box"></span>Card
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 16px; color: #64748B; font-size: 8px;">
                            No students found matching the selected filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Sign-off & Verification Block --}}
        <table class="signoff-table">
            <tr>
                <td style="width: 35%; text-align: center;">
                    <div class="signature-line">
                        Biometric Hardware Officer
                    </div>
                    <div style="font-size: 5.5px; color: #64748B; margin-top: 2px;">
                        Date &amp; Signature
                    </div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div class="stamp-box">
                        Official Stamp
                    </div>
                </td>
                <td style="width: 35%; text-align: center;">
                    <div class="signature-line">
                        Principal / School Administrator
                    </div>
                    <div style="font-size: 5.5px; color: #64748B; margin-top: 2px;">
                        Verification &amp; Approval
                    </div>
                </td>
            </tr>
        </table>

        <div class="brand-footer">
            AcademyHub &bull; ZKTeco K40 Fingerprint &amp; RFID Hardware Management System &bull; Confidential &amp; Proprietary
        </div>

    </div>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("dejavu sans", "normal");
        $size = 6.5;
        $color = array(0.4, 0.45, 0.5);
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $pdf->page_text(520, 820, $text, $font, $size, $color);
    }
</script>

</body>
</html>
