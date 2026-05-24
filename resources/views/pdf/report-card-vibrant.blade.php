<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1e1b4b; background: white; }

        /* ─── VIBRANT Modern High-Fidelity Style ─── */
        .page { border: 4px solid #a855f7; padding: 4px; background: white; border-radius: 16px; overflow: hidden; }
        .page-inner { border: 1.5px solid #ec4899; padding: 18px; border-radius: 12px; background: linear-gradient(135deg, #fdf4ff 0%, #ffffff 50%, #fdf4ff 100%); }

        .header { text-align: center; border-bottom: 2px solid #a855f7; padding-bottom: 12px; margin-bottom: 12px; }
        .header-table { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 75px; text-align: center; }
        .logo { width: 62px; height: 62px; object-fit: contain; border: 2.5px solid #a855f7; border-radius: 50%; padding: 4px; background: white; }
        .school-name { font-size: 20px; font-weight: 900; color: #7e22ce; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2px; }
        .school-motto { font-size: 8.5px; color: #db2777; font-style: italic; font-weight: 700; margin-bottom: 2px; }
        .school-meta { font-size: 8px; color: #4b5563; margin-bottom: 1px; }
        .badge { display: inline-block; background: linear-gradient(135deg, #ec4899, #a855f7); color: white; padding: 5px 20px; border-radius: 20px; font-size: 9px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; margin-top: 6px; box-shadow: 0 3px 6px rgba(168,85,247,0.15); }
        .meta-right { font-size: 8px; color: #7e22ce; font-weight: 750; text-align: right; }

        /* Stats Circular Badges */
        .circles { display: table; width: 100%; margin-bottom: 12px; }
        .circle-wrap { display: table-cell; width: 16.66%; text-align: center; padding: 2px; }
        .circle { width: 52px; height: 52px; border-radius: 50%; margin: 0 auto; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 3px 8px rgba(168,85,247,0.15); }
        .circle.pink { background: linear-gradient(135deg, #ec4899, #db2777); }
        .circle.purple { background: linear-gradient(135deg, #a855f7, #7e22ce); }
        .circle.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .circle.green { background: linear-gradient(135deg, #10b981, #047857); }
        .circle.orange { background: linear-gradient(135deg, #f97316, #ea580c); }
        .circle.yellow { background: linear-gradient(135deg, #eab308, #ca8a04); }
        .circle-label { font-size: 6px; color: white; font-weight: 800; text-transform: uppercase; margin-bottom: 1px; }
        .circle-value { font-size: 12px; color: white; font-weight: 900; }

        /* Table */
        table.scores { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 2px solid #e9d5ff; border-radius: 8px; overflow: hidden; }
        table.scores th { background: linear-gradient(135deg, #a855f7, #ec4899); color: white; padding: 6px 4px; font-size: 7.5px; font-weight: 800; text-transform: uppercase; border: 1px solid #d8b4fe; }
        table.scores td { padding: 5px 4px; border: 1px solid #fae8ff; text-align: center; font-size: 8.5px; color: #1e1b4b; }
        table.scores tr:nth-child(even) td { background: #fdf4ff; }
        .subj { text-align: left !important; font-weight: 700; color: #7e22ce; padding-left: 8px !important; }
        .bold { font-weight: 800; color: #7e22ce; }

        /* Grading Key */
        .grading { display: table; width: 100%; border: 2px solid #fae8ff; margin-bottom: 12px; border-radius: 8px; overflow: hidden; background: white; }
        .gr-cell { display: table-cell; padding: 5px 4px; text-align: center; font-size: 7.5px; font-weight: 700; color: #7e22ce; border-right: 1px solid #fae8ff; }
        .gr-cell:last-child { border-right: none; }
        .gr-cell strong { color: #ec4899; font-size: 9px; }

        /* Attendance */
        .att { display: table; width: 100%; margin-bottom: 12px; border: 2px solid #fae8ff; border-radius: 8px; overflow: hidden; background: white; }
        .att-cell { display: table-cell; width: 33.33%; text-align: center; padding: 6px; border-right: 1px solid #fae8ff; }
        .att-cell:last-child { border-right: none; }
        .att-label { font-size: 7.5px; font-weight: 800; text-transform: uppercase; color: #7e22ce; margin-bottom: 2px; }
        .att-value { font-size: 14px; font-weight: 900; color: #1e1b4b; }

        /* Remarks */
        .remarks { border: 2px solid #ec4899; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; background: white; }
        .remarks-label { font-size: 7.5px; font-weight: 900; color: #7e22ce; text-transform: uppercase; margin-bottom: 3px; }
        .remarks-text { font-size: 8px; color: #374151; min-height: 20px; line-height: 1.3; }

        /* Next Term */
        .next-term { background: linear-gradient(135deg, #ec4899, #a855f7); color: white; text-align: center; padding: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; border-radius: 8px; box-shadow: 0 3px 6px rgba(236,72,153,0.15); }

        /* Signatures */
        .sigs { display: table; width: 100%; margin-top: 12px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; vertical-align: bottom; }
        .sig-img { max-height: 32px; max-width: 90px; object-fit: contain; margin-bottom: 3px; }
        .sig-line { border-top: 2px solid #a855f7; margin-top: 24px; padding-top: 3px; font-size: 8.5px; font-weight: 800; color: #7e22ce; }
        .sig-line.has-img { margin-top: 3px; }
        .sig-sub { font-size: 6.5px; color: #a855f7; font-style: italic; margin-top: 1px; opacity: 0.8; }

        .footer { text-align: center; font-size: 7px; color: #9ca3af; margin-top: 6px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.02; width: 320px; height: 320px; }
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
                    @if(config('myacademy.school_motto'))<div class="school-motto">"{{ config('myacademy.school_motto') }}"</div>@endif
                    @if(config('myacademy.school_address'))<div class="school-meta">{{ config('myacademy.school_address') }}</div>@endif
                    @if(config('myacademy.school_phone') || config('myacademy.school_email'))
                        <div class="school-meta">{{ config('myacademy.school_phone') }}@if(config('myacademy.school_phone') && config('myacademy.school_email')) • @endif{{ config('myacademy.school_email') }}</div>
                    @endif
                    <div class="badge">Student Report Card</div>
                </div>
                <div class="header-cell" style="width:145px;">
                    <div class="meta-right">Session: <strong>{{ $session }}</strong></div>
                    <div class="meta-right">Term: <strong>Term {{ $term }}</strong></div>
                    <div class="meta-right">Issued: <strong>{{ now()->format('d M, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Student Info --}}
        @php($siBorderColor = '#a855f7') @php($siBgColor = '#faf5ff') @php($siLabelColor = '#7e22ce') @php($siValueColor = '#1e1b4b') @php($siDotColor = '#d8b4fe')
        @include('pdf.partials.rc-student-info')

        {{-- Vibrant Circles Stats --}}
        <div class="circles">
            <div class="circle-wrap">
                <div class="circle pink">
                    <div class="circle-label">Total</div>
                    <div class="circle-value">{{ $grandTotal }}</div>
                </div>
            </div>
            <div class="circle-wrap">
                <div class="circle purple">
                    <div class="circle-label">Average</div>
                    <div class="circle-value">{{ number_format($average, 1) }}%</div>
                </div>
            </div>
            @if($showPosition)
            <div class="circle-wrap">
                <div class="circle blue">
                    <div class="circle-label">Position</div>
                    <div class="circle-value">{{ $position }}</div>
                </div>
            </div>
            @endif
            @if($showClassAverage)
            <div class="circle-wrap">
                <div class="circle green">
                    <div class="circle-label">Class Avg</div>
                    <div class="circle-value">{{ number_format($classAverage, 1) }}%</div>
                </div>
            </div>
            @endif
            <div class="circle-wrap">
                <div class="circle orange">
                    <div class="circle-label">Highest</div>
                    <div class="circle-value">{{ number_format($highestAverage ?? 0, 1) }}%</div>
                </div>
            </div>
            <div class="circle-wrap">
                <div class="circle yellow">
                    <div class="circle-label">Lowest</div>
                    <div class="circle-value">{{ number_format($lowestAverage ?? 0, 1) }}%</div>
                </div>
            </div>
        </div>

        {{-- Scores Table --}}
        <table class="scores">
            <thead>
                <tr>
                    <th style="width:32%;text-align:left;padding-left:8px;">Subject</th>
                    <th style="width:10%;">CA1 ({{ config('myacademy.results_ca1_max',20) }})</th>
                    <th style="width:10%;">CA2 ({{ config('myacademy.results_ca2_max',20) }})</th>
                    <th style="width:10%;">Exam ({{ config('myacademy.results_exam_max',60) }})</th>
                    <th style="width:10%;">Total</th>
                    <th style="width:9%;">Grade</th>
                    @if($showClassAverage)<th style="width:9%;">Class Avg</th>@endif
                    @if($showPosition)<th style="width:9%;">Position</th>@endif
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
        @php($rcBorderColor='#a855f7') @php($rcBgLight='#faf5ff') @php($rcTitleColor='#7e22ce') @php($rcLabelColor='#7e22ce')
        @include('pdf.partials.rc-psychomotor')

        {{-- Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks"><div class="remarks-label">Class Teacher's Remarks</div><div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div></div>
        @endif
        @if($showPrincipalRemarks)
        <div class="remarks"><div class="remarks-label">Principal's Remarks</div><div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div></div>
        @endif

        {{-- School Fees --}}
        @include('pdf.partials.rc-school-fees')

        {{-- Next Term --}}
        @if($showNextTermDate)
        <div class="next-term">🗓️ Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}</div>
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

        <div class="footer">Generated {{ now()->format('M d, Y') }} • {{ $schoolName }} • Powered by MyAcademy SMS</div>
    </div>
</div>
</body>
</html>
