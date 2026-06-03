<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1e1b4b; background: #fff; }

        /* ─── Aurora Vibrant Neon Modern Theme ─── */
        .page { border: 4px solid #8b5cf6; border-radius: 16px; padding: 12px; background: #fff; position: relative; }
        .page-inner { padding: 10px; }

        /* Header Card with Vibrant Gradient Backdrop */
        .header { background: linear-gradient(135deg, #7c3aed 0%, #db2777 60%, #4f46e5 100%); border-radius: 12px; padding: 16px; margin-bottom: 12px; color: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header-table { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 70px; }
        .logo { width: 56px; height: 56px; object-fit: contain; border: 2px solid rgba(255,255,255,0.4); border-radius: 10px; padding: 3px; background: rgba(255,255,255,0.9); }
        .school-name { font-size: 17px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .school-meta { margin-top: 2px; font-size: 8px; color: rgba(255,255,255,0.85); font-weight: 600; }
        .badge { display: inline-block; margin-top: 6px; background: rgba(255,255,255,0.2); color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 8px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.3); }
        .meta-right { font-size: 8.5px; color: rgba(255,255,255,0.9); font-weight: 600; text-align: right; line-height: 1.4; }

        /* Stats Cards Block */
        .stats { display: table; width: 100%; margin-bottom: 12px; }
        .stat { display: table-cell; padding: 3px; }
        .stat-inner { background: #f5f3ff; border: 2px solid #ddd6fe; border-radius: 8px; text-align: center; padding: 6px; }
        .stat-inner.green { background: #ecfdf5; border-color: #a7f3d0; }
        .stat-inner.green .stat-value { color: #059669; }
        .stat-inner.blue { background: #eff6ff; border-color: #bfdbfe; }
        .stat-inner.blue .stat-value { color: #2563eb; }
        .stat-inner.purple { background: #fdf2f8; border-color: #fbcfe8; }
        .stat-inner.purple .stat-value { color: #db2777; }
        .stat-inner.teal { background: #f0fdfa; border-color: #99f6e4; }
        .stat-inner.teal .stat-value { color: #0d9488; }
        .stat-inner.rose { background: #fff5f5; border-color: #fed7d7; }
        .stat-inner.rose .stat-value { color: #e53e3e; }
        .stat-label { font-size: 7px; color: #6d28d9; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .stat-value { font-size: 14px; font-weight: 900; color: #4c1d95; }

        /* Table */
        table.scores { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 12px; border: 2px solid #ddd6fe; border-radius: 10px; overflow: hidden; }
        table.scores th { background: #7c3aed; color: #fff; padding: 6px 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; text-align: center; border-bottom: 2px solid #6d28d9; }
        table.scores td { padding: 5px 4px; border-bottom: 1px solid #f3e8ff; border-right: 1px solid #f3e8ff; text-align: center; font-size: 8.5px; color: #1e1b4b; }
        table.scores td:last-child { border-right: none; }
        table.scores tr:last-child td { border-bottom: none; }
        table.scores tr:nth-child(even) td { background: #faf5ff; }
        .subj { text-align: left !important; font-weight: 800; color: #6d28d9; padding-left: 8px !important; }
        .bold { font-weight: 900; color: #db2777; }

        /* Grading Key */
        .grading { display: table; width: 100%; border: 2px solid #ddd6fe; margin-bottom: 12px; border-radius: 8px; overflow: hidden; }
        .gr-cell { display: table-cell; padding: 5px 4px; text-align: center; font-size: 7.5px; font-weight: 700; color: #4c1d95; border-right: 1px solid #ddd6fe; background: #faf8ff; }
        .gr-cell:last-child { border-right: none; }
        .gr-cell strong { color: #db2777; font-size: 8.5px; }

        /* Attendance */
        .att { display: table; width: 100%; margin-bottom: 12px; border: 2px solid #ddd6fe; border-radius: 8px; overflow: hidden; }
        .att-cell { display: table-cell; width: 33.33%; text-align: center; padding: 6px; border-right: 1px solid #ddd6fe; background: #fdf2f8; }
        .att-cell:last-child { border-right: none; }
        .att-label { font-size: 8px; font-weight: 800; text-transform: uppercase; color: #db2777; margin-bottom: 2px; }
        .att-value { font-size: 14px; font-weight: 900; color: #4c1d95; }

        /* Remarks */
        .remarks { border: 2px solid #fbcfe8; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; background: #fff5f5; }
        .remarks-label { font-size: 8px; font-weight: 900; color: #db2777; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .remarks-text { font-size: 8.5px; color: #4a044e; min-height: 16px; line-height: 1.3; font-weight: 600; }

        /* Next Term Banner */
        .next-term { background: linear-gradient(90deg, #7c3aed 0%, #db2777 100%); color: #fff; text-align: center; padding: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px; border-radius: 8px; }

        /* Signatures */
        .sigs { display: table; width: 100%; margin-top: 12px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; vertical-align: bottom; }
        .sig-img { max-height: 30px; max-width: 80px; object-fit: contain; margin-bottom: 2px; }
        .sig-line { border-top: 2px solid #7c3aed; margin-top: 24px; padding-top: 3px; font-size: 8.5px; font-weight: 800; color: #6d28d9; }
        .sig-line.has-img { margin-top: 2px; }
        .sig-sub { font-size: 7px; color: #7c3aed; font-style: italic; margin-top: 1px; }

        .footer { margin-top: 8px; border-top: 2px solid #f3e8ff; padding-top: 4px; text-align: center; font-size: 7.5px; color: #a21caf; font-weight: 600; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.03; width: 320px; height: 320px; }
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
                    <div class="badge">Student Academic Profile</div>
                </div>
                <div class="header-cell" style="width:130px;">
                    <div class="meta-right">Session: <strong>{{ $session }}</strong></div>
                    <div class="meta-right">Term: <strong>Term {{ $term }}</strong></div>
                    <div class="meta-right">Date: <strong>{{ now()->format('d M, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Student Info --}}
        @php($siBorderColor = '#db2777') @php($siBgColor = '#fff5f7') @php($siLabelColor = '#db2777') @php($siValueColor = '#4c1d95') @php($siDotColor = '#fbcfe8')
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
        @php($rcBorderColor='#7c3aed') @php($rcBgLight='#f5f3ff') @php($rcTitleColor='#7c3aed') @php($rcLabelColor='#4c1d95')
        @include('pdf.partials.rc-psychomotor')

        {{-- Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks"><div class="remarks-label">Class Teacher's Remarks</div><div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div></div>
        @endif
        @if($showPrincipalRemarks)
        <div class="remarks"><div class="remarks-label">Principal's Remarks</div><div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div></div>
        @endif

        {{-- School Fees --}}
        @php($rcBorderColor='#db2777') @php($rcBgLight='#fff5f7') @php($rcTitleColor='#db2777') @php($rcLabelColor='#db2777')
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
