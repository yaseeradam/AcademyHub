<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 8.5px; color: #334155; background: #fff; line-height: 1.3; }

        /* ─── Nordic Minimalist Slate Theme ─── */
        .page { border: 2px solid #cbd5e1; padding: 12px; background: #fff; }
        .page-inner { padding: 4px; }

        /* Nordic Stark Header */
        .header { border-bottom: 1.5px solid #475569; padding-bottom: 8px; margin-bottom: 12px; }
        .header-table { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 60px; }
        .logo { width: 48px; height: 48px; object-fit: contain; filter: grayscale(100%); opacity: 0.85; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px; }
        .school-name { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b; }
        .school-meta { margin-top: 2px; font-size: 7.5px; color: #64748b; font-weight: 500; }
        .badge { display: inline-block; margin-top: 4px; background: #475569; color: #fff; padding: 2.5px 8px; border-radius: 3px; font-size: 7px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; }
        .meta-right { font-size: 8px; color: #475569; font-weight: 500; text-align: right; line-height: 1.3; }

        /* Nordic Grid Stats */
        .stats { display: table; width: 100%; margin-bottom: 12px; border-collapse: collapse; border: 1.5px solid #cbd5e1; border-radius: 4px; overflow: hidden; }
        .stat { display: table-cell; text-align: center; border-right: 1.5px solid #cbd5e1; padding: 6px 4px; background: #f8fafc; }
        .stat:last-child { border-right: none; }
        .stat.highlight { background: #f1f5f9; }
        .stat-label { font-size: 6.5px; color: #475569; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
        .stat-value { font-size: 12px; font-weight: bold; color: #0f172a; }

        /* Table */
        table.scores { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1.5px solid #cbd5e1; }
        table.scores th { background: #f1f5f9; color: #334155; padding: 5px 4px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; text-align: center; border: 1.5px solid #cbd5e1; border-bottom: 2px solid #94a3b8; }
        table.scores td { padding: 4.5px 4px; border: 1px solid #cbd5e1; text-align: center; font-size: 8px; color: #334155; }
        table.scores tr:nth-child(even) td { background: #fafafb; }
        .subj { text-align: left !important; font-weight: bold; color: #1e293b; padding-left: 8px !important; }
        .bold { font-weight: bold; color: #0f172a; }

        /* Grading Key */
        .grading { display: table; width: 100%; border: 1.5px solid #cbd5e1; margin-bottom: 12px; border-radius: 3px; overflow: hidden; }
        .gr-cell { display: table-cell; padding: 4px; text-align: center; font-size: 7px; font-weight: bold; color: #475569; border-right: 1px solid #cbd5e1; background: #f8fafc; }
        .gr-cell:last-child { border-right: none; }
        .gr-cell strong { color: #1e293b; font-size: 8px; }

        /* Attendance */
        .att { display: table; width: 100%; margin-bottom: 12px; border: 1.5px solid #cbd5e1; border-radius: 3px; overflow: hidden; }
        .att-cell { display: table-cell; width: 33.33%; text-align: center; padding: 5px; border-right: 1px solid #cbd5e1; background: #f8fafc; }
        .att-cell:last-child { border-right: none; }
        .att-label { font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #475569; margin-bottom: 2px; }
        .att-value { font-size: 12px; font-weight: bold; color: #0f172a; }

        /* Remarks */
        .remarks { border: 1.5px solid #cbd5e1; border-radius: 4px; padding: 6px 8px; margin-bottom: 6px; background: #f8fafc; }
        .remarks-label { font-size: 7.5px; font-weight: bold; color: #1e293b; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; }
        .remarks-text { font-size: 8px; color: #334155; min-height: 16px; line-height: 1.3; }

        /* Next Term */
        .next-term { background: #475569; color: #fff; text-align: center; padding: 5px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; border-radius: 3px; }

        /* Signatures */
        .sigs { display: table; width: 100%; margin-top: 10px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; vertical-align: bottom; }
        .sig-img { max-height: 26px; max-width: 70px; object-fit: contain; margin-bottom: 2px; }
        .sig-line { border-top: 1.5px solid #475569; margin-top: 20px; padding-top: 3px; font-size: 8px; font-weight: bold; color: #1e293b; }
        .sig-line.has-img { margin-top: 2px; }
        .sig-sub { font-size: 6.5px; color: #64748b; font-style: italic; margin-top: 1px; }

        .footer { margin-top: 6px; border-top: 1px solid #cbd5e1; padding-top: 3px; text-align: center; font-size: 7px; color: #94a3b8; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.02; width: 280px; height: 280px; }
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
                    @if(config('academyhub.school_phone') || config('academyhub.school_email'))
                        <div class="school-meta">{{ config('academyhub.school_phone') }}@if(config('academyhub.school_phone') && config('academyhub.school_email')) • @endif{{ config('academyhub.school_email') }}</div>
                    @endif
                    <div class="badge">Student Roster Assessment</div>
                </div>
                <div class="header-cell" style="width:130px;">
                    <div class="meta-right">Session: <strong>{{ $session }}</strong></div>
                    <div class="meta-right">Term: <strong>Term {{ $term }}</strong></div>
                    <div class="meta-right">Date: <strong>{{ now()->format('d M, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Student Info --}}
        @php($siBorderColor = '#cbd5e1') @php($siBgColor = '#f8fafc') @php($siLabelColor = '#475569') @php($siValueColor = '#1e293b') @php($siDotColor = '#cbd5e1')
        @include('pdf.partials.rc-student-info')

        {{-- Stats --}}
        <div class="stats">
            <div class="stat"><div class="stat-label">Total</div><div class="stat-value">{{ $grandTotal }}</div></div>
            <div class="stat highlight"><div class="stat-label">Average</div><div class="stat-value">{{ number_format($average,1) }}%</div></div>
            @if($showPosition)<div class="stat"><div class="stat-label">Position</div><div class="stat-value">{{ $position }}</div></div>@endif
            @if($showClassAverage)
            <div class="stat"><div class="stat-label">Class Avg</div><div class="stat-value">{{ number_format($classAverage,1) }}%</div></div>
            <div class="stat"><div class="stat-label">Highest</div><div class="stat-value">{{ number_format($highestAverage??0,1) }}%</div></div>
            <div class="stat"><div class="stat-label">Lowest</div><div class="stat-value">{{ number_format($lowestAverage??0,1) }}%</div></div>
            @endif
        </div>

        {{-- Scores Table --}}
        <table class="scores">
            <thead>
                <tr>
                    <th style="width:32%;text-align:left;padding-left:8px;">Subject</th>
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
        @php($rcBorderColor='#cbd5e1') @php($rcBgLight='#f8fafc') @php($rcTitleColor='#475569') @php($rcLabelColor='#475569')
        @include('pdf.partials.rc-psychomotor')

        {{-- Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks"><div class="remarks-label">Class Teacher's Remarks</div><div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div></div>
        @endif
        @if($showPrincipalRemarks)
        <div class="remarks"><div class="remarks-label">Principal's Remarks</div><div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div></div>
        @endif

        {{-- School Fees --}}
        @php($rcBorderColor='#cbd5e1') @php($rcBgLight='#f8fafc') @php($rcTitleColor='#475569') @php($rcLabelColor='#475569')
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

        <div class="footer">Generated {{ now()->format('d M Y, g:i A') }} • {{ $schoolName }} • Powered by AcademyHub SMS</div>
    </div>
</div>
</body>
</html>
