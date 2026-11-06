<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Weekly Attendance Bulletin – {{ $class->name }} {{ $section?->name ? '(' . $section->name . ')' : '' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
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
            border: 3px solid #C59B27;
            padding: 4px;
            background: #ffffff;
        }

        .poster-inner {
            border: 1.5px solid #0C1E40;
            padding: 8px 10px;
            background: #ffffff;
        }

        /* ── Header ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .logo-box {
            width: 58px;
            height: 58px;
            text-align: center;
        }

        .logo-img {
            max-width: 58px;
            max-height: 58px;
        }

        .logo-placeholder {
            width: 54px;
            height: 54px;
            border: 2px solid #C59B27;
            border-radius: 50%;
            text-align: center;
            background: #0C1E40;
            color: #C59B27;
            font-size: 20px;
            line-height: 50px;
            font-weight: bold;
        }

        .school-title {
            font-size: 14px;
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
            letter-spacing: 0.5px;
        }

        .contact-line {
            text-align: center;
            font-size: 7px;
            color: #64748B;
        }

        .doc-title-box {
            background: #0C1E40;
            color: #ffffff;
            text-align: center;
            padding: 4px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
            border-bottom: 2px solid #C59B27;
        }

        .doc-title {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #F8FAFC;
        }

        .doc-subtitle {
            font-size: 7.5px;
            color: #FBBF24;
            font-weight: bold;
            margin-top: 1px;
            letter-spacing: 0.3px;
        }

        /* ── Info Cards & KPIs ── */
        .info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin-bottom: 6px;
        }

        .info-grid td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        .kpi-card {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 4px;
            padding: 4px 6px;
            text-align: center;
        }

        .kpi-card.gold-card {
            background: #FEF9C3;
            border: 1px solid #F59E0B;
        }

        .kpi-label {
            font-size: 6.5px;
            color: #64748B;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .kpi-val {
            font-size: 11px;
            font-weight: 900;
            color: #0C1E40;
            margin-top: 1px;
        }

        .kpi-val.gold {
            color: #B45309;
        }

        /* ── Star Achievers Banner ── */
        .honor-roll-box {
            background: #FFFBEB;
            border: 1.5px solid #F59E0B;
            border-radius: 5px;
            padding: 4px 8px;
            margin-bottom: 6px;
        }

        .honor-title {
            font-size: 8px;
            font-weight: 900;
            color: #B45309;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            text-align: center;
        }

        .honor-badges {
            text-align: center;
        }

        .honor-badge {
            display: inline-block;
            background: #FEF3C7;
            border: 1px solid #D97706;
            color: #78350F;
            padding: 1.5px 6px;
            margin: 1.5px 2px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: bold;
        }

        /* ── Attendance Table ── */
        .att-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .att-table th {
            background: #0C1E40;
            color: #ffffff;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 3px;
            text-align: center;
            border: 1px solid #0C1E40;
            letter-spacing: 0.3px;
        }

        .att-table th.th-left {
            text-align: left;
            padding-left: 5px;
        }

        .att-table td {
            border: 1px solid #CBD5E1;
            padding: 3px 2px;
            text-align: center;
            font-size: 7.5px;
            vertical-align: middle;
        }

        .att-table td.td-left {
            text-align: left;
            padding-left: 5px;
        }

        .att-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        /* ── Status Pills ── */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6.5px;
            text-align: center;
            line-height: 1.1;
        }

        .badge-present {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #86EFAC;
        }

        .badge-late {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FCD34D;
        }

        .badge-absent {
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FCA5A5;
        }

        .badge-excused {
            background: #E0F2FE;
            color: #0369A1;
            border: 1px solid #7DD3FC;
        }

        .badge-none {
            color: #94A3B8;
            font-weight: normal;
        }

        .star-gold {
            color: #D97706;
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        .star-dim {
            color: #CBD5E1;
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        /* ── Legend & Signatures ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .footer-table td {
            vertical-align: top;
            border: none;
            font-size: 7px;
        }

        .legend-box {
            color: #475569;
            font-size: 6.5px;
        }

        .legend-item {
            margin-right: 8px;
            display: inline-block;
        }

        .signature-line {
            border-top: 1px dotted #64748B;
            width: 130px;
            margin-top: 16px;
            text-align: center;
            font-size: 6.5px;
            font-weight: bold;
            color: #334155;
            padding-top: 2px;
        }

        .brand-footer {
            text-align: center;
            font-size: 6px;
            color: #94A3B8;
            margin-top: 4px;
            border-top: 1px solid #E2E8F0;
            padding-top: 3px;
        }
    </style>
</head>
<body>

<div class="poster-outer">
    <div class="poster-inner">

        {{-- Header with Logo and School Info --}}
        <table class="header-table">
            <tr>
                <td style="width: 65px;">
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
                    <div style="font-size: 7px; color: #475569; line-height: 1.4; border-left: 2px solid #C59B27; padding-left: 6px; text-align: left;">
                        <strong>Term:</strong> {{ $termLabel }}<br/>
                        <strong>Session:</strong> {{ $sessionLabel }}<br/>
                        <strong>Issued:</strong> {{ now()->format('d M, Y') }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Document Title Ribbon --}}
        <div class="doc-title-box">
            <div class="doc-title">
                Student {{ !empty($isMonthly) ? 'Monthly' : 'Weekly' }} Attendance &amp; Punctuality Bulletin
            </div>
            <div class="doc-subtitle">
                Class: {{ $class->name }} @if($section) &bull; Section: {{ $section->name }} @if(method_exists($section, 'getShiftLabel')) ({{ $section->getShiftLabel() }}) @endif @endif &bull; {{ $periodRangeLabel ?? $weekRangeLabel }}
            </div>
        </div>

        {{-- KPI Summary Bar --}}
        <table class="info-grid">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <div class="kpi-label">Class Enrollment</div>
                        <div class="kpi-val">{{ count($students) }} Students</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <div class="kpi-label">{{ !empty($isMonthly) ? 'Monthly' : 'Weekly' }} Attendance Rate</div>
                        <div class="kpi-val" style="color: {{ $weeklyAttendanceRate >= 85 ? '#15803d' : ($weeklyAttendanceRate >= 70 ? '#b45309' : '#b91c1c') }}">
                            {{ $weeklyAttendanceRate }}%
                        </div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <div class="kpi-label">Average Present Daily</div>
                        <div class="kpi-val" style="color: #0C1E40;">
                            {{ $activeDaysCount > 0 ? round($totalPresentMarksAll / $activeDaysCount) : count($students) }} / {{ count($students) }}
                        </div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <div class="kpi-label">School Days in {{ !empty($isMonthly) ? 'Month' : 'Week' }}</div>
                        <div class="kpi-val">{{ $activeDaysCount }} Days</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Main Student Attendance Table --}}
        <table class="att-table">
            <thead>
                <tr>
                    <th style="width: 22px;">#</th>
                    <th class="th-left" style="{{ !empty($isMonthly) ? 'width: 140px;' : 'width: 155px;' }}">Student Full Name</th>
                    <th style="{{ !empty($isMonthly) ? 'width: 55px;' : 'width: 65px;' }}">ADM No</th>
                    @foreach($weekDays as $day)
                        <th style="{{ !empty($isMonthly) ? 'min-width: 16px; font-size: 6px; padding: 2px 1px;' : 'width: 58px;' }}">
                            {{ !empty($isMonthly) ? ($day['day_num'] ?? $day['name']) : $day['name'] }}<br/>
                            <span style="font-size: 5px; opacity: 0.85;">{{ $day['formatted'] }}</span>
                        </th>
                    @endforeach
                    <th style="{{ !empty($isMonthly) ? 'width: 32px;' : 'width: 48px;' }}">Present</th>
                    @if(!empty($isMonthly))
                        <th style="width: 28px;">Late</th>
                        <th style="width: 28px;">Absent</th>
                    @endif
                    <th style="{{ !empty($isMonthly) ? 'width: 34px;' : 'width: 44px;' }}">Rate</th>
                    @if(empty($isMonthly))
                        <th style="width: 62px;">Star Rating</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($studentRows as $idx => $row)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="td-left" style="font-weight: bold; color: #0C1E40; {{ !empty($isMonthly) ? 'font-size: 6.8px;' : '' }}">
                            {{ $row['name'] }}
                        </td>
                        <td style="font-family: monospace; font-size: {{ !empty($isMonthly) ? '6px' : '7px' }}; color: #475569;">
                            {{ $row['adm_no'] ?: '—' }}
                        </td>
                        @foreach($row['days'] as $d)
                            <td>
                                @if(!empty($isMonthly))
                                    @if($d['status'] === 'Present')
                                        <span class="badge badge-present" style="padding: 1px 2px; font-size: 5.5px;">P</span>
                                    @elseif($d['status'] === 'Late')
                                        <span class="badge badge-late" style="padding: 1px 2px; font-size: 5.5px;">L</span>
                                    @elseif($d['status'] === 'Absent')
                                        <span class="badge badge-absent" style="padding: 1px 2px; font-size: 5.5px;">A</span>
                                    @elseif($d['status'] === 'Excused')
                                        <span class="badge badge-excused" style="padding: 1px 2px; font-size: 5.5px;">E</span>
                                    @else
                                        <span class="badge badge-none" style="font-size: 5.5px;">—</span>
                                    @endif
                                @else
                                    @if($d['status'] === 'Present')
                                        <span class="badge badge-present">✓ Present</span>
                                    @elseif($d['status'] === 'Late')
                                        <span class="badge badge-late">Late</span>
                                    @elseif($d['status'] === 'Absent')
                                        <span class="badge badge-absent">&times; Absent</span>
                                    @elseif($d['status'] === 'Excused')
                                        <span class="badge badge-excused">Excused</span>
                                    @else
                                        <span class="badge badge-none">—</span>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                        <td style="font-weight: bold; {{ !empty($isMonthly) ? 'font-size: 6.5px;' : '' }}">
                            {{ $row['days_present'] }}/{{ $activeDaysCount }}
                        </td>
                        @if(!empty($isMonthly))
                            <td style="font-weight: bold; color: #b45309; font-size: 6.5px;">
                                {{ $row['days_late'] ?? 0 }}
                            </td>
                            <td style="font-weight: bold; color: #b91c1c; font-size: 6.5px;">
                                {{ $row['days_absent'] ?? 0 }}
                            </td>
                        @endif
                        <td style="font-weight: bold; color: {{ $row['rate'] >= 80 ? '#15803d' : ($row['rate'] >= 60 ? '#b45309' : '#b91c1c') }}; {{ !empty($isMonthly) ? 'font-size: 6.5px;' : '' }}">
                            {{ $row['rate'] }}%
                        </td>
                        @if(empty($isMonthly))
                            <td>
                                <span class="star-gold">{{ str_repeat('★', $row['stars']) }}</span><span class="star-dim">{{ str_repeat('☆', 5 - $row['stars']) }}</span>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($weekDays) + (empty($isMonthly) ? 6 : 8) }}" style="padding: 15px; text-align: center; color: #64748B;">
                            No active students found in this class/section.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer Legend & Endorsements --}}
        <table class="footer-table">
            <tr>
                <td style="width: 55%;">
                    <div class="legend-box">
                        <strong>Legend:</strong>
                        <span class="legend-item"><span class="badge badge-present">✓ Present</span> (In Class)</span>
                        <span class="legend-item"><span class="badge badge-late">Late</span> (Punctuality notice)</span>
                        <span class="legend-item"><span class="badge badge-absent">&times; Absent</span> (Unexcused)</span>
                        <span class="legend-item"><span class="badge badge-excused">Excused</span> (Authorized)</span>
                    </div>
                    <div style="margin-top: 4px; font-size: 6.5px; color: #64748B; font-style: italic;">
                        &ldquo;Punctuality and consistent attendance build the foundation for academic and lifelong success.&rdquo;
                    </div>
                </td>
                <td style="width: 22%; text-align: center;">
                    <div class="signature-line">
                        {{ $classTeacherName ?? 'Class Teacher' }}<br/>
                        <span style="font-size: 5.5px; color: #64748B;">Class Teacher Signature</span>
                    </div>
                </td>
                <td style="width: 23%; text-align: center;">
                    <div class="signature-line">
                        Principal / Administrator<br/>
                        <span style="font-size: 5.5px; color: #64748B;">Head of School Endorsement</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="brand-footer">
            Official Weekly Academic Bulletin &bull; Generated by {{ config('app.name', 'AcademyHub') }} on {{ now()->format('M d, Y \a\t g:i A') }} &bull; For distribution on Parents WhatsApp Forum
        </div>

    </div>
</div>

</body>
</html>
