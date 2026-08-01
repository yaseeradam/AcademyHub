<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Sheet - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 4mm 6mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Garamond, Georgia, serif; font-size: 8.5px; color: #3b0712; background: #fff; line-height: 1.25; }

        /* ─── Signature Imperial Prestigious Theme ─── */
        .page { border: 3.5px solid #991b1b; padding: 6px; background: #fff; }
        .page-inner { border: 1.5px solid #d97706; padding: 6px; }

        /* Elegant Academic Header */
        .header { border-bottom: 2.5px solid #991b1b; padding-bottom: 3px; margin-bottom: 4px; text-align: center; }
        .header-table { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 60px; }
        .logo { width: 48px; height: 48px; object-fit: contain; border: 1.5px solid #d97706; border-radius: 50%; padding: 2px; background: #fff; }
        .school-name { font-size: 17px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #7f1d1d; font-family: Garamond, Georgia, serif; }
        .school-meta { margin-top: 1px; font-size: 7.5px; color: #7f1d1d; font-weight: bold; }
        .badge { display: inline-block; margin-top: 3px; background: #991b1b; color: #fffbeb; padding: 2.5px 12px; border-radius: 3px; font-size: 7.5px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; border: 1px solid #d97706; }
        .meta-right { font-size: 7.5px; color: #7f1d1d; font-weight: bold; text-align: right; line-height: 1.3; }

        /* Stats Cards */
        .stats { display: table; width: 100%; margin-bottom: 4px; }
        .stat { display: table-cell; padding: 1.5px; }
        .stat-inner { background: #fffbeb; border: 1.5px solid #d97706; border-radius: 4px; text-align: center; padding: 4px 2px; }
        .stat-inner.green { background: #f0fdf4; border-color: #16a34a; }
        .stat-inner.green .stat-value { color: #166534; }
        .stat-inner.blue { background: #eff6ff; border-color: #2563eb; }
        .stat-inner.blue .stat-value { color: #1e40af; }
        .stat-inner.purple { background: #fdf2f8; border-color: #db2777; }
        .stat-inner.purple .stat-value { color: #9d174d; }
        .stat-inner.teal { background: #f0fdfa; border-color: #0d9488; }
        .stat-inner.teal .stat-value { color: #115e59; }
        .stat-inner.rose { background: #fff5f5; border-color: #e53e3e; }
        .stat-inner.rose .stat-value { color: #991b1b; }
        .stat-label { font-size: 6.5px; color: #991b1b; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .stat-value { font-size: 11.5px; font-weight: bold; color: #7f1d1d; }

        /* Scores Table */
        table.scores { width: 100%; border-collapse: collapse; margin-bottom: 4px; border: 1.5px solid #991b1b; }
        table.scores th { background: #7f1d1d; color: #fffbeb; padding: 2.5px 3px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; text-align: center; border: 1px solid #d97706; }
        table.scores td { padding: 2px 3px; border: 1px solid #d97706; text-align: center; font-size: 8px; color: #450a0a; }
        table.scores tr:nth-child(even) td { background: #fffbeb; }
        .subj { text-align: left !important; font-weight: bold; color: #7f1d1d; padding-left: 6px !important; font-family: Garamond, Georgia, serif; }
        .bold { font-weight: bold; color: #b45309; }

        /* Grading Key */
        .grading { display: table; width: 100%; border: 1.5px solid #991b1b; margin-bottom: 4px; border-radius: 3px; overflow: hidden; }
        .gr-cell { display: table-cell; padding: 3px 2px; text-align: center; font-size: 6.5px; font-weight: bold; color: #7f1d1d; border-right: 1px solid #991b1b; background: #fffbeb; }
        .gr-cell:last-child { border-right: none; }
        .gr-cell strong { color: #991b1b; font-size: 7.5px; }

        /* Attendance */
        .att { display: table; width: 100%; margin-bottom: 4px; border: 1.5px solid #991b1b; border-radius: 3px; overflow: hidden; }
        .att-cell { display: table-cell; width: 33.33%; text-align: center; padding: 4px; border-right: 1px solid #991b1b; background: #fffbeb; }
        .att-cell:last-child { border-right: none; }
        .att-label { font-size: 7px; font-weight: bold; text-transform: uppercase; color: #7f1d1d; margin-bottom: 1px; }
        .att-value { font-size: 11px; font-weight: bold; color: #450a0a; }

        /* Remarks */
        .remarks { border: 1.5px solid #991b1b; border-radius: 3px; padding: 3px 5px; margin-bottom: 3px; background: #fffbeb; }
        .remarks-label { font-size: 7px; font-weight: bold; color: #7f1d1d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .remarks-text { font-size: 7.5px; color: #450a0a; line-height: 1.25; font-style: italic; }

        /* Next Term Banner */
        .next-term { background: #7f1d1d; border: 1px solid #d97706; color: #fffbeb; text-align: center; padding: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; border-radius: 3px; }

        /* Signatures */
        .sigs { display: table; width: 100%; margin-top: 3px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 3px; vertical-align: bottom; }
        .sig-img { max-height: 25px; max-width: 70px; object-fit: contain; margin-bottom: 1px; }
        .sig-line { border-top: 1.5px solid #7f1d1d; margin-top: 8px; padding-top: 2px; font-size: 7.5px; font-weight: bold; color: #7f1d1d; }
        .sig-line.has-img { margin-top: 1px; }
        .sig-sub { font-size: 6px; color: #7f1d1d; font-style: italic; }

        .footer { margin-top: 4px; border-top: 1.5px solid #d97706; padding-top: 3px; text-align: center; font-size: 6.5px; color: #7f1d1d; font-weight: bold; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.03; width: 300px; height: 300px; }
    </style>
</head>
<body>
@php
    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = config('academyhub.school_logo');
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
                    @if(config('academyhub.school_address'))<div class="school-meta">{{ config('academyhub.school_address') }}</div>@endif
                    @if(config('academyhub.school_phone'))
                        <div class="school-meta">Phone: {{ config('academyhub.school_phone') }}</div>
                    @endif
                    <div class="badge">Official Progress Statement</div>
                </div>
                <div class="header-cell" style="width:130px;">
                    <div class="meta-right">Session: <strong>{{ $session }}</strong></div>
                    <div class="meta-right">Term: <strong>Term {{ $term }}</strong></div>
                    <div class="meta-right">Date: <strong>{{ now()->format('d M, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Student Info --}}
        @php($siBorderColor = '#991b1b') @php($siBgColor = '#fffbeb') @php($siLabelColor = '#7f1d1d') @php($siValueColor = '#3b0712') @php($siDotColor = '#ca8a04')
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
                    <th style="width:32%;text-align:left;padding-left:6px;">Subject</th>
                    <th style="width:10%;">CA1<br/>({{ config('academyhub.results_ca1_max',20) }})</th>
                    <th style="width:10%;">CA2<br/>({{ config('academyhub.results_ca2_max',20) }})</th>
                    <th style="width:10%;">Exam<br/>({{ config('academyhub.results_exam_max',60) }})</th>
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
        @php($rcBorderColor='#991b1b') @php($rcBgLight='#fffbeb') @php($rcTitleColor='#7f1d1d') @php($rcLabelColor='#7f1d1d')
        @include('pdf.partials.rc-psychomotor')

        {{-- Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks"><div class="remarks-label">Class Teacher's Remarks</div><div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div></div>
        @endif
        @if($showPrincipalRemarks)
        <div class="remarks"><div class="remarks-label">Principal's Remarks</div><div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div></div>
        @endif

        {{-- School Fees --}}
        @php($rcBorderColor='#991b1b') @php($rcBgLight='#fffbeb') @php($rcTitleColor='#7f1d1d') @php($rcLabelColor='#7f1d1d')
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
                <div class="sig-sub">Signature &amp; Date</div>
            </div>
            <div class="sig">
                @if(($signatureImages['principal']??null) && file_exists($signatureImages['principal']))<img src="{{ $signatureImages['principal'] }}" class="sig-img" /><div class="sig-line has-img">Principal</div>
                @else<div class="sig-line">Principal</div>@endif
                <div class="sig-sub">Signature &amp; Stamp</div>
            </div>
            <div class="sig"><div class="sig-line">Parent/Guardian</div><div class="sig-sub">Signature &amp; Date</div></div>
        </div>
        @endif

        <div class="footer">Generated {{ now()->format('d M Y, g:i A') }} • {{ $schoolName }} • Powered by AcademyHub SMS</div>
    </div>
</div>
</body>
</html>
