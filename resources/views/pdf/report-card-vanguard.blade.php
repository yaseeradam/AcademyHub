<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 8.5px; color: #0f172a; background: #fff; line-height: 1.3; }

        /* ─── Vanguard Tech Cyber Theme ─── */
        .page { border: 3px solid #0891b2; border-radius: 12px; padding: 12px; background: #fff; }
        .page-inner { padding: 4px; }

        /* Cyber Tech Header */
        .header { background: #0f172a; border-radius: 8px; padding: 14px; margin-bottom: 12px; color: #fff; border-left: 5px solid #0891b2; }
        .header-table { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 65px; }
        .logo { width: 50px; height: 50px; object-fit: contain; border: 1.5px solid #0891b2; border-radius: 6px; padding: 2px; background: #fff; }
        .school-name { font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #06b6d4; font-family: "DejaVu Sans", Arial, sans-serif; }
        .school-meta { margin-top: 2px; font-size: 7.5px; color: #94a3b8; font-weight: bold; font-family: monospace; }
        .badge { display: inline-block; margin-top: 4px; background: #0891b2; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 7.5px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        .meta-right { font-size: 8px; color: #cbd5e1; font-weight: 600; text-align: right; line-height: 1.4; font-family: monospace; }

        /* Tech Stats Panels */
        .stats { display: table; width: 100%; margin-bottom: 12px; }
        .stat { display: table-cell; padding: 2px; }
        .stat-inner { background: #0f172a; border: 1.5px solid #334155; border-radius: 6px; text-align: center; padding: 6px 4px; color: #fff; }
        .stat-inner.green .stat-value { color: #10b981; }
        .stat-inner.blue .stat-value { color: #3b82f6; }
        .stat-inner.purple .stat-value { color: #a855f7; }
        .stat-inner.teal .stat-value { color: #06b6d4; }
        .stat-inner.rose .stat-value { color: #f43f5e; }
        .stat-label { font-size: 6.5px; color: #94a3b8; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; font-family: monospace; }
        .stat-value { font-size: 13px; font-weight: 900; color: #06b6d4; font-family: monospace; }

        /* Tech Table */
        table.scores { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 12px; border: 1.5px solid #0891b2; border-radius: 8px; overflow: hidden; }
        table.scores th { background: #0f172a; color: #06b6d4; padding: 5px; font-size: 8px; font-weight: 800; text-transform: uppercase; text-align: center; border-bottom: 2px solid #0891b2; }
        table.scores td { padding: 5px; border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; text-align: center; font-size: 8.5px; color: #1e293b; }
        table.scores td:last-child { border-right: none; }
        table.scores tr:last-child td { border-bottom: none; }
        table.scores tr:nth-child(even) td { background: #f8fafc; }
        .subj { text-align: left !important; font-weight: 800; color: #0f172a; padding-left: 8px !important; }
        .bold { font-weight: bold; color: #0891b2; font-family: monospace; }

        /* Grading Key */
        .grading { display: table; width: 100%; border: 1.5px solid #0891b2; margin-bottom: 12px; border-radius: 6px; overflow: hidden; }
        .gr-cell { display: table-cell; padding: 4.5px; text-align: center; font-size: 7.5px; font-weight: bold; color: #475569; border-right: 1px solid #0891b2; background: #f8fafc; font-family: monospace; }
        .gr-cell:last-child { border-right: none; }
        .gr-cell strong { color: #0891b2; font-size: 8px; }

        /* Attendance */
        .att { display: table; width: 100%; margin-bottom: 12px; border: 1.5px solid #0891b2; border-radius: 6px; overflow: hidden; }
        .att-cell { display: table-cell; width: 33.33%; text-align: center; padding: 5px; border-right: 1px solid #0891b2; background: #faf5ff; }
        .att-cell:last-child { border-right: none; }
        .att-label { font-size: 7.5px; font-weight: 800; text-transform: uppercase; color: #0891b2; margin-bottom: 2px; font-family: monospace; }
        .att-value { font-size: 13px; font-weight: 900; color: #0f172a; font-family: monospace; }

        /* Remarks */
        .remarks { border: 1.5px solid #334155; border-radius: 6px; padding: 6px 8px; margin-bottom: 6px; background: #f8fafc; border-left: 4px solid #334155; }
        .remarks-label { font-size: 7.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; font-family: monospace; }
        .remarks-text { font-size: 8px; color: #334155; min-height: 16px; line-height: 1.3; }

        /* Next Term */
        .next-term { background: #0f172a; border-left: 5px solid #0891b2; color: #06b6d4; text-align: center; padding: 6px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px; border-radius: 4px; font-family: monospace; }

        /* Signatures */
        .sigs { display: table; width: 100%; margin-top: 10px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; vertical-align: bottom; }
        .sig-img { max-height: 28px; max-width: 75px; object-fit: contain; margin-bottom: 2px; }
        .sig-line { border-top: 1.5px solid #0f172a; margin-top: 20px; padding-top: 3px; font-size: 8.5px; font-weight: bold; color: #0f172a; }
        .sig-line.has-img { margin-top: 2px; }
        .sig-sub { font-size: 6.5px; color: #64748b; font-style: italic; margin-top: 1px; }

        .footer { margin-top: 6px; border-top: 1px solid #e2e8f0; padding-top: 3px; text-align: center; font-size: 7px; color: #94a3b8; font-family: monospace; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.02; width: 300px; height: 300px; }
    </style>
</head>
<body>
@php
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $logo = config('myacademy.school_logo');
    $logoPath = $logo ? public_path('uploads/'.str_replace('\\', '/', $logo)) : null;
    $logoExists = $logoPath && file_exists($logoPath);
    $opts = $rcOptions ?? [];
    $showPosition       = $opts['show_position'] ?? true;
    $showAttendance     = $opts['show_attendance'] ?? true;
    $showGradingKey     = $opts['show_grading_key'] ?? true;
    $showClassAverage   = $opts['show_class_average'] ?? true;
    $showWatermark      = $opts['show_watermark'] ?? true;
    $showNextTermDate   = $opts['show_next_term_date'] ?? true;
    $showTeacherRemarks = $opts['show_teacher_remarks'] ?? true;
    $showPrincipalRemarks = $opts['show_principal_remarks'] ?? true;
    $showPsychomotor    = $opts['show_psychomotor'] ?? false;
    $showSchoolFees     = $opts['show_school_fees'] ?? false;
    $showSignatures     = $opts['show_signatures'] ?? false;
@endphp

@if($logoExists && $showWatermark)
    <div class="watermark"><img src="{{ $logoPath }}" alt="" style="width:100%;height:100%;object-fit:contain;" /></div>
@endif

<div class="page">
    <div class="page-inner">
        {{-- Header --}}
        <div class="header">
            <div class="header-table">
                <div class="header-cell logo-wrap">
                    @if($logoExists)<img class="logo" src="{{ $logoPath }}" alt="">@endif
                </div>
                <div class="header-cell">
                    <div class="school-name">{{ $schoolName }}</div>
                    @if(config('myacademy.school_address'))<div class="school-meta">{{ config('myacademy.school_address') }}</div>@endif
                    @if(config('myacademy.school_phone') || config('myacademy.school_email'))
                        <div class="school-meta">{{ config('myacademy.school_phone') }}@if(config('myacademy.school_phone') && config('myacademy.school_email')) • @endif{{ config('myacademy.school_email') }}</div>
                    @endif
                    <div class="badge">Vanguard Report Card</div>
                </div>
                <div class="header-cell" style="width:130px;">
                    <div class="meta-right">Session: <strong>{{ $session }}</strong></div>
                    <div class="meta-right">Term: <strong>Term {{ $term }}</strong></div>
                    <div class="meta-right">Date: <strong>{{ now()->format('d M, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Student Info --}}
        @php($siBorderColor = '#0891b2') @php($siBgColor = '#f8fafc') @php($siLabelColor = '#0891b2') @php($siValueColor = '#0f172a') @php($siDotColor = '#cbd5e1')
        @include('pdf.partials.rc-student-info')

        {{-- Stats --}}
        <div class="stats">
            <div class="stat"><div class="stat-inner"><div class="stat-label">Total</div><div class="stat-value">{{ $grandTotal }}</div></div></div>
            <div class="stat"><div class="stat-inner green"><div class="stat-label">Average</div><div class="stat-value">{{ number_format($average,1) }}%</div></div></div>
            @if($showPosition)<div class="stat"><div class="stat-inner blue"><div class="stat-label">Position</div><div class="stat-value">{{ $position }}</div></div></div>@endif
            @if($showClassAverage)
            <div class="stat"><div class="stat-inner purple"><div class="stat-label">Class Avg</div><div class="stat-value">{{ number_format($classAverage,1) }}%</div></div></div>
            <div class="stat"><div class="stat-inner teal"><div class="stat-label">Highest</div><div class="stat-value">{{ number_format($highestAverage??0,1) }}%</div></div></div>
            <div class="stat"><div class="stat-inner rose"><div class="stat-label">Lowest</div><div class="stat-value">{{ number_format($lowestAverage??0,1) }}%</div></div></div>
            @endif
        </div>

        {{-- Scores Table --}}
        <table class="scores">
            <thead>
                <tr>
                    <th style="width:32%;text-align:left;padding-left:8px;">Subject</th>
                    <th style="width:10%;">CA1<br/>({{ config('myacademy.results_ca1_max',20) }})</th>
                    <th style="width:10%;">CA2<br/>({{ config('myacademy.results_ca2_max',20) }})</th>
                    <th style="width:10%;">Exam<br/>({{ config('myacademy.results_exam_max',60) }})</th>
                    <th style="width:10%;">Total</th>
                    <th style="width:9%;">Grade</th>
                    @if($showClassAverage)<th style="width:9%;">Avg</th>@endif
                    @if($showPosition)<th style="width:9%;">Pos</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                <tr>
                    <td class="subj">{{ $r['subject']?->name ?? '-' }}</td>
                    <td>{{ $r['ca1'] ?? '' }}</td>
                    <td>{{ $r['ca2'] ?? '' }}</td>
                    <td>{{ $r['exam'] ?? '' }}</td>
                    <td class="bold">{{ $r['total'] ?? '' }}</td>
                    <td class="bold">{{ $r['grade'] ?? '' }}</td>
                    @if($showClassAverage)<td>{{ $r['class_avg'] ?? '—' }}</td>@endif
                    @if($showPosition)<td>{{ $r['position'] ?? '—' }}</td>@endif
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Grading Key --}}
        @if($showGradingKey)
        <div class="grading">
            <div class="gr-cell"><strong>A:</strong> 70-100 (Excellent)</div>
            <div class="gr-cell"><strong>B:</strong> 60-69 (Very Good)</div>
            <div class="gr-cell"><strong>C:</strong> 50-59 (Good)</div>
            <div class="gr-cell"><strong>D:</strong> 40-49 (Pass)</div>
            <div class="gr-cell"><strong>F:</strong> 0-39 (Fail)</div>
        </div>
        @endif

        {{-- Attendance --}}
        @if($showAttendance)
        <div class="att">
            <div class="att-cell"><div class="att-label">Times Opened</div><div class="att-value">{{ $timesOpened ?? '—' }}</div></div>
            <div class="att-cell"><div class="att-label">Times Present</div><div class="att-value">{{ $timesPresent ?? '—' }}</div></div>
            <div class="att-cell"><div class="att-label">Times Absent</div><div class="att-value">{{ $timesAbsent ?? '—' }}</div></div>
        </div>
        @endif

        {{-- Psychomotor --}}
        @php($rcBorderColor='#0891b2') @php($rcBgLight='#f0fbfd') @php($rcTitleColor='#0891b2') @php($rcLabelColor='#0f172a')
        @include('pdf.partials.rc-psychomotor')

        {{-- Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks"><div class="remarks-label">Class Teacher's Remarks</div><div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div></div>
        @endif
        @if($showPrincipalRemarks)
        <div class="remarks"><div class="remarks-label">Principal's Remarks</div><div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div></div>
        @endif

        {{-- School Fees --}}
        @php($rcBorderColor='#0891b2') @php($rcBgLight='#f0fbfd') @php($rcTitleColor='#0891b2') @php($rcLabelColor='#0f172a')
        @include('pdf.partials.rc-school-fees')

        {{-- Next Term --}}
        @if($showNextTermDate)
        <div class="next-term">Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}</div>
        @endif

        {{-- Signatures --}}
        @if($showSignatures)
        <div class="sigs">
            <div class="sig">
                @if(($signatureImages['teacher']??null) && file_exists($signatureImages['teacher']))<img src="{{ $signatureImages['teacher'] }}" class="sig-img" /><div class="sig-line has-img">Class Teacher</div>
                @else<div class="sig-line">Class Teacher</div>@endif
                <div class="sig-sub">Signature & Date</div>
            </div>
            <div class="sig">
                @if(($signatureImages['principal']??null) && file_exists($signatureImages['principal']))<img src="{{ $signatureImages['principal'] }}" class="sig-img" /><div class="sig-line has-img">Principal</div>
                @else<div class="sig-line">Principal</div>@endif
                <div class="sig-sub">Signature & Stamp</div>
            </div>
            <div class="sig"><div class="sig-line">Parent/Guardian</div><div class="sig-sub">Signature & Date</div></div>
        </div>
        @endif

        <div class="footer">Generated {{ now()->format('d M Y, g:i A') }} • {{ $schoolName }} • Powered by MyAcademy SMS</div>
    </div>
</div>
</body>
</html>
