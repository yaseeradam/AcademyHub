<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page {
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #e2e8f0;
            background: #0f172a;
        }

        /* ─── Modern Bold Dark ─── */
        .page {
            background: #0f172a;
            padding: 0;
        }

        .header-block {
            background: linear-gradient(135deg, #1e293b, #334155);
            padding: 18px 20px 14px;
            border-bottom: 3px solid #38bdf8;
        }

        .header-table {
            display: table;
            width: 100%;
        }

        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .logo-cell {
            width: 80px;
        }

        .logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 4px;
        }

        .school-name {
            font-size: 20px;
            font-weight: 800;
            color: #f1f5f9;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        .school-details {
            font-size: 8px;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .badge-row {
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            background: #38bdf8;
            color: #0f172a;
            padding: 4px 14px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 4px;
        }

        .session-badge {
            display: inline-block;
            background: #1e293b;
            border: 1px solid #475569;
            color: #94a3b8;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: 700;
            border-radius: 4px;
            margin-left: 6px;
        }

        .content {
            padding: 16px 20px;
        }

        .student-bar {
            display: table;
            width: 100%;
            background: #1e293b;
            border-radius: 8px;
            margin-bottom: 14px;
            border: 1px solid #334155;
            overflow: hidden;
        }

        .student-info {
            display: table-cell;
            vertical-align: top;
            padding: 12px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 3px 6px;
            font-size: 8px;
            font-weight: 700;
            color: #38bdf8;
            text-transform: uppercase;
            width: 40%;
        }

        .info-value {
            display: table-cell;
            padding: 3px 6px;
            font-size: 10px;
            font-weight: 700;
            color: #f1f5f9;
        }

        .photo-cell {
            display: table-cell;
            width: 90px;
            vertical-align: top;
            text-align: center;
            padding: 10px;
        }

        .photo {
            width: 75px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #38bdf8;
        }

        /* ─── Stat Cards ─── */
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            padding: 3px;
        }

        .stat-inner {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            text-align: center;
            padding: 10px 4px;
        }

        .stat-label {
            font-size: 7px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #38bdf8;
        }

        .stat-inner.amber .stat-value {
            color: #fbbf24;
        }

        .stat-inner.green .stat-value {
            color: #34d399;
        }

        .stat-inner.purple .stat-value {
            color: #a78bfa;
        }

        /* ─── Table ─── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-radius: 8px;
            overflow: hidden;
        }

        th {
            background: #38bdf8;
            color: #0f172a;
            padding: 8px 4px;
            text-align: center;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 6px 4px;
            text-align: center;
            font-size: 9px;
            color: #cbd5e1;
            border-bottom: 1px solid #1e293b;
        }

        tr:nth-child(even) td {
            background: #1e293b;
        }

        tr:nth-child(odd) td {
            background: #0f172a;
        }

        .subj {
            text-align: left;
            font-weight: 700;
            color: #f1f5f9;
            padding-left: 8px;
        }

        .bold {
            font-weight: 800;
            color: #f1f5f9;
        }

        .grading-bar {
            display: table;
            width: 100%;
            background: #1e293b;
            border-radius: 6px;
            margin-bottom: 14px;
            border: 1px solid #334155;
        }

        .gr-cell {
            display: table-cell;
            padding: 6px 4px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-right: 1px solid #334155;
        }

        .gr-cell:last-child {
            border-right: none;
        }

        .gr-cell strong {
            color: #38bdf8;
            font-size: 10px;
        }

        .att-bar {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .att-cell {
            display: table-cell;
            width: 33.33%;
            padding: 3px;
        }

        .att-inner {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 6px;
            text-align: center;
            padding: 8px;
        }

        .att-label {
            font-size: 7px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .att-value {
            font-size: 16px;
            font-weight: 800;
            color: #f1f5f9;
        }

        .remarks-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .remarks-label {
            font-size: 8px;
            font-weight: 800;
            color: #38bdf8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .remarks-text {
            font-size: 9px;
            color: #cbd5e1;
            min-height: 22px;
            border-bottom: 1px solid #475569;
            padding-bottom: 3px;
        }

        .next-term-bar {
            background: #38bdf8;
            color: #0f172a;
            text-align: center;
            padding: 8px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 14px;
        }

        .sig {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 6px;
        }

        .sig-line {
            border-top: 1.5px solid #38bdf8;
            margin-top: 30px;
            padding-top: 4px;
            font-size: 9px;
            font-weight: 800;
            color: #f1f5f9;
        }

        .sig-sub {
            font-size: 7px;
            color: #64748b;
            font-style: italic;
            margin-top: 2px;
        }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #334155;
            padding-top: 6px;
            text-align: center;
            font-size: 7px;
            color: #475569;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0.02;
            width: 380px;
            height: 380px;
        }
    </style>
</head>

<body>
    @php
        $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
        $logo = config('myacademy.school_logo');
        $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;
        $logoExists = $logoPath && file_exists($logoPath);
    @endphp
        @php
    $opts = $rcOptions ?? [];
    $showPosition = $opts['show_position'] ?? true;
    $showAttendance = $opts['show_attendance'] ?? true;
    $showGradingKey = $opts['show_grading_key'] ?? true;
    $showClassAverage = $opts['show_class_average'] ?? true;
    $showWatermark = $opts['show_watermark'] ?? true;
    $showNextTermDate = $opts['show_next_term_date'] ?? true;
    $showTeacherRemarks = $opts['show_teacher_remarks'] ?? true;
    $showPrincipalRemarks = $opts['show_principal_remarks'] ?? true;
    $showPsychomotor = $opts['show_psychomotor'] ?? false;
    $showSchoolFees = $opts['show_school_fees'] ?? false;
    $showSignatures = $opts['show_signatures'] ?? false;
@endphp

    @if($logoExists && $showWatermark)
        <div class="watermark">
            <img src="{{ $logoPath }}" alt="" style="width: 100%; height: 100%; object-fit: contain;" />
        </div>
    @endif

    <div class="page">
        <div class="header-block">
            <div class="header-table">
                @if($logoExists)
                    <div class="header-cell logo-cell">
                        <img src="{{ $logoPath }}" alt="Logo" class="logo" />
                    </div>
                @endif
                <div class="header-cell">
                    <div class="school-name">{{ $schoolName }}</div>
                    @if(config('myacademy.school_motto'))
                        <div class="school-details" style="font-style: italic;">{{ config('myacademy.school_motto') }}</div>
                    @endif
                    @if(config('myacademy.school_address'))
                        <div class="school-details">{{ config('myacademy.school_address') }}</div>
                    @endif
                    @if(config('myacademy.school_phone') || config('myacademy.school_email'))
                        <div class="school-details">
                            {{ config('myacademy.school_phone') }}
                            @if(config('myacademy.school_phone') && config('myacademy.school_email')) | @endif
                            {{ config('myacademy.school_email') }}
                        </div>
                    @endif
                    <div class="badge-row">
                        <span class="badge">Report Card</span>
                        <span class="session-badge">{{ $session }} • Term {{ $term }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="student-bar">
                
                @php($siBorderColor = '#06b6d4')
                @php($siBgColor = '#0f172a')
                @php($siLabelColor = '#06b6d4')
                @php($siValueColor = '#e2e8f0')
                @php($siDotColor = '#1e293b')
                @include('pdf.partials.rc-student-info')

                @if($student->passport_photo)
                    <div class="photo-cell">
                        <img src="{{ public_path('uploads/' . str_replace('\\', '/', $student->passport_photo)) }}"
                            alt="Photo" class="photo" />
                    </div>
                @endif
            </div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-inner amber">
                        <div class="stat-label">Total Score</div>
                        <div class="stat-value">{{ $grandTotal }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-inner green">
                        <div class="stat-label">Average</div>
                        <div class="stat-value">{{ number_format($average, 1) }}%</div>
                    </div>
                </div>
                @if($showPosition)
                <div class="stat-card">
                    <div class="stat-inner">
                        <div class="stat-label">Position</div>
                        <div class="stat-value">{{ $position }}</div>
                    </div>
                </div>
                @endif
                @if($showClassAverage)
                <div class="stat-card">
                    <div class="stat-inner purple">
                        <div class="stat-label">Class Avg</div>
                        <div class="stat-value">{{ number_format($classAverage, 1) }}%</div>
                    </div>
                </div>
                @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 30%; text-align: left; padding-left: 8px;">Subject</th>
                        <th style="width: 10%;">CA1</th>
                        <th style="width: 10%;">CA2</th>
                        <th style="width: 10%;">Exam</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 10%;">Grade</th>
                        @if($showClassAverage)<th style="width: 10%;">Class Avg</th>@endif
                        @if($showPosition)<th style="width: 10%;">Position</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="subj">{{ $row['subject']->name }}</td>
                            <td>{{ $row['ca1'] ?? '—' }}</td>
                            <td>{{ $row['ca2'] ?? '—' }}</td>
                            <td>{{ $row['exam'] ?? '—' }}</td>
                            <td class="bold">{{ $row['total'] ?? '—' }}</td>
                            <td class="bold">{{ $row['grade'] ?? '—' }}</td>
                            @if($showClassAverage)<td>{{ $row['class_avg'] ?? '—' }}</td>@endif
                            @if($showPosition)<td>{{ $row['position'] ?? '—' }}</td>@endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($showGradingKey)
            <div class="grading-bar">
                <div class="gr-cell"><strong>A:</strong> 70-100</div>
                <div class="gr-cell"><strong>B:</strong> 60-69</div>
                <div class="gr-cell"><strong>C:</strong> 50-59</div>
                <div class="gr-cell"><strong>D:</strong> 40-49</div>
                <div class="gr-cell"><strong>F:</strong> 0-39</div>
            </div>
            @endif

            @if($showAttendance)
            <div class="att-bar">
                <div class="att-cell">
                    <div class="att-inner">
                        <div class="att-label">Times Opened</div>
                        <div class="att-value">{{ $timesOpened ?? '—' }}</div>
                    </div>
                </div>
                <div class="att-cell">
                    <div class="att-inner">
                        <div class="att-label">Times Present</div>
                        <div class="att-value">{{ $timesPresent ?? '—' }}</div>
                    </div>
                </div>
                <div class="att-cell">
                    <div class="att-inner">
                        <div class="att-label">Times Absent</div>
                        <div class="att-value">{{ $timesAbsent ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if($showTeacherRemarks)
            <div class="remarks-card">
                <div class="remarks-label">Class Teacher's Remarks</div>
                <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
            </div>
            @endif

            @if($showPrincipalRemarks)
            <div class="remarks-card">
                <div class="remarks-label">Principal's Remarks</div>
                <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
            </div>
            @endif

            @if($showNextTermDate)
            <div class="next-term-bar">
                Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}
            </div>
            @endif

            
                @php($rcBorderColor = '#06b6d4')
                @php($rcBgLight = '#1e293b')
                @php($rcTitleColor = '#06b6d4')
                @php($rcLabelColor = '#e2e8f0')
                @include('pdf.partials.rc-psychomotor')
                @include('pdf.partials.rc-school-fees')

@if($showSignatures)
<div class="signatures">
                <div class="sig">
                    @if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher']))
                            <img src="{{ $signatureImages['teacher'] }}" alt="Teacher Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        @endif
                        <div class="sig-line">Class Teacher</div>
                    <div class="sig-sub">Signature & Date</div>
                </div>
                <div class="sig">
                    @if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal']))
                            <img src="{{ $signatureImages['principal'] }}" alt="Principal Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        @endif
                        <div class="sig-line">Principal</div>
                    <div class="sig-sub">Signature & Stamp</div>
                </div>
                <div class="sig">
                    <div class="sig-line">Parent/Guardian</div>
                    <div class="sig-sub">Signature & Date</div>
                </div>
            </div>
@endif

            <div class="footer">
                Generated on {{ now()->format('l, F j, Y \a\t g:i A') }} • {{ $schoolName }} • Powered by MyAcademy SMS
            </div>
        </div>
    </div>
</body>

</html>