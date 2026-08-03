<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Sheet - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 4mm 6mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Georgia, Times, serif; font-size: 8.5px; color: #1e293b; background: #fff; line-height: 1.25; }

        /* ─── Heritage Royal Academic Theme ─── */
        .page { border: 4px double #a16207; padding: 6px; background: #fff; }
        .page-inner { border: 1.5px solid #1e3a8a; padding: 6px; }

        /* Traditional Classic Header */
        .header { border-bottom: 3px double #a16207; padding-bottom: 3px; margin-bottom: 4px; }
        .header-table { display: table; width: 100%; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 60px; }
        .logo { width: 48px; height: 48px; object-fit: contain; border: 1.5px solid #a16207; border-radius: 4px; padding: 2px; background: #fff; }
        .school-name { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1e3a8a; font-family: Georgia, serif; }
        .school-meta { margin-top: 1px; font-size: 7.5px; color: #475569; font-weight: bold; font-style: italic; }
        .badge { display: inline-block; margin-top: 3px; background: #1e3a8a; color: #fef08a; padding: 2px 10px; border-radius: 2px; font-size: 7.5px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; border: 1px solid #a16207; }
        .meta-right { font-size: 7.5px; color: #334155; font-weight: bold; text-align: right; line-height: 1.3; }

        /* Stats Panel */
        .stats { display: table; width: 100%; margin-bottom: 4px; border-collapse: separate; border-spacing: 2px; }
        .stat { display: table-cell; width: 16.66%; }
        .stat-inner { background: #fefcbf; border: 1.5px solid #d97706; border-radius: 3px; text-align: center; padding: 4px 2px; }
        .stat-inner.green { background: #f0fdf4; border-color: #16a34a; }
        .stat-inner.green .stat-value { color: #15803d; }
        .stat-inner.blue { background: #eff6ff; border-color: #2563eb; }
        .stat-inner.blue .stat-value { color: #1d4ed8; }
        .stat-inner.purple { background: #faf5ff; border-color: #7c3aed; }
        .stat-inner.purple .stat-value { color: #6b21a8; }
        .stat-inner.teal { background: #f0fdfa; border-color: #0d9488; }
        .stat-inner.teal .stat-value { color: #0f766e; }
        .stat-inner.rose { background: #fff5f5; border-color: #e53e3e; }
        .stat-inner.rose .stat-value { color: #b91c1c; }
        .stat-label { font-size: 6px; color: #1e3a8a; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1px; }
        .stat-value { font-size: 12px; font-weight: bold; color: #854d0e; }

        /* Score Table */
        table.scores { width: 100%; border-collapse: collapse; margin-bottom: 4px; border: 1.5px solid #a16207; }
        table.scores th { background: #1e3a8a; color: #fff; padding: 2.5px 3px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; text-align: center; border: 1px solid #a16207; letter-spacing: 0.5px; }
        table.scores td { padding: 2px 3px; border: 1px solid #a16207; text-align: center; font-size: 8px; color: #0f172a; }
        table.scores tr:nth-child(even) td { background: #fafaf9; }
        .subj { text-align: left !important; font-weight: bold; color: #1e3a8a; padding-left: 6px !important; font-family: Georgia, serif; }
        .bold { font-weight: bold; color: #854d0e; }

        /* Grading Key */
        .grading { display: table; width: 100%; border: 1.5px solid #a16207; margin-bottom: 4px; overflow: hidden; }
        .gr-cell { display: table-cell; padding: 3px 2px; text-align: center; font-size: 6.5px; font-weight: bold; color: #334155; border-right: 1px solid #a16207; background: #fffbeb; }
        .gr-cell:last-child { border-right: none; }
        .gr-cell strong { color: #1e3a8a; font-size: 7.5px; }

        /* Attendance */
        .att { display: table; width: 100%; margin-bottom: 4px; border: 1.5px solid #1e3a8a; overflow: hidden; }
        .att-cell { display: table-cell; width: 33.33%; text-align: center; padding: 4px; border-right: 1px solid #1e3a8a; background: #eff6ff; }
        .att-cell:last-child { border-right: none; }
        .att-label { font-size: 7px; font-weight: bold; text-transform: uppercase; color: #1e3a8a; margin-bottom: 1px; }
        .att-value { font-size: 11px; font-weight: bold; color: #0f172a; }

        /* Remarks */
        .remarks { border: 1.5px solid #a16207; padding: 6px 10px; margin-bottom: 6px; background: #fffbeb; border-radius: 3px; }
        .remarks-label { font-size: 8px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .remarks-text { font-size: 8.5px; color: #334155; line-height: 1.35; font-style: italic; }

        /* Next Term */
        .next-term { background: #1e3a8a; border: 1.5px solid #a16207; color: #fef08a; text-align: center; padding: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }

        /* Signatures */
        .sigs { display: table; width: 100%; margin-top: 3px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 3px; vertical-align: bottom; }
        .sig-img { max-height: 25px; max-width: 70px; object-fit: contain; margin-bottom: 1px; }
        .sig-line { border-top: 1.5px solid #1e3a8a; margin-top: 8px; padding-top: 2px; font-size: 7.5px; font-weight: bold; color: #1e3a8a; }
        .sig-line.has-img { margin-top: 1px; }
        .sig-sub { font-size: 6px; color: #475569; font-style: italic; }

        .footer { margin-top: 4px; border-top: 1.5px double #a16207; padding-top: 3px; text-align: center; font-size: 6.5px; color: #475569; font-weight: bold; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.03; width: 280px; height: 280px; }
    </style>
</head>
<body>
@php
    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = config('academyhub.school_logo');
    
    $toBase64 = function(?string $path): ?string {
        if (!$path || !file_exists($path)) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
        $data = @file_get_contents($path);
        return $data ? 'data:' . $mime . ';base64,' . base64_encode($data) : null;
    };

    $logoCandidates = array_filter([
        $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null,
        $logo ? public_path(str_replace('\\', '/', $logo)) : null,
        $logo ? storage_path('app/public/' . str_replace('\\', '/', $logo)) : null,
        public_path('academy.png'),
        public_path('logo.png'),
        public_path('images/logo.png'),
        public_path('uploads/school_logo.png'),
    ]);

    $logoPath = null;
    foreach ($logoCandidates as $cand) {
        if ($cand && file_exists($cand)) {
            $logoPath = $cand;
            break;
        }
    }

    $logoDataUri = $logoPath ? $toBase64($logoPath) : null;
    $logoExists = (bool) $logoDataUri;

    $opts = $rcOptions ?? [];
    $showPosition                  = $opts['show_position'] ?? true;
    $showAttendance                = $opts['show_attendance'] ?? true;
    $showGradingKey                = $opts['show_grading_key'] ?? true;
    $showClassAverage              = $opts['show_class_average'] ?? true;
    $showWatermark                 = $opts['show_watermark'] ?? true;
    $showNextTermDate              = $opts['show_next_term_date'] ?? true;
    $showTeacherRemarks            = $opts['show_teacher_remarks'] ?? true;
    $showPrincipalRemarks          = $opts['show_principal_remarks'] ?? true;
    $showPsychomotor               = $opts['show_psychomotor'] ?? false;
    $showSchoolFees                = $opts['show_school_fees'] ?? false;
    $showSignatures                = $opts['show_signatures'] ?? false;
    $showClassHighestLowest        = $opts['show_class_highest_lowest'] ?? false;
    $showSubjectTeacherRemarks     = $opts['show_subject_teacher_remarks'] ?? false;
    $showQrCode                    = $opts['show_qr_code'] ?? true;
    $showCumulativeSummary         = $opts['show_cumulative_summary'] ?? false;
    $showColorBadges               = $opts['show_color_badges'] ?? true;
@endphp

@if($logoExists && $showWatermark)
    <div class="watermark"><img src="{{ $logoDataUri }}" alt="" style="width:100%;height:100%;object-fit:contain;" /></div>
@endif

<div class="page">
    <div class="page-inner">

        {{-- Header --}}
        <div class="header">
            <div class="header-table">
                <div class="header-cell logo-wrap">
                    @if($logoExists)<img class="logo" src="{{ $logoDataUri }}" alt="">@endif
                </div>
                <div class="header-cell">
                    <div class="school-name">{{ $schoolName }}</div>
                    @if(config('academyhub.school_address'))<div class="school-meta">{{ config('academyhub.school_address') }}</div>@endif
                    @if(config('academyhub.school_phone'))
                        <div class="school-meta">Phone: {{ config('academyhub.school_phone') }}</div>
                    @endif
                    <div class="badge">Official Report of Learning</div>
                </div>
                <div class="header-cell" style="width:130px;">
                    <div class="meta-right">Session: <strong>{{ $session }}</strong></div>
                    <div class="meta-right">Term: <strong>Term {{ $term }}</strong></div>
                    <div class="meta-right">Date: <strong>{{ now()->format('d M, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Student Info --}}
        @php($siBorderColor = '#a16207') @php($siBgColor = '#fffbeb') @php($siLabelColor = '#1e3a8a') @php($siValueColor = '#1e293b') @php($siDotColor = '#fef08a')
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
                    <th style="width:28%;text-align:left;padding-left:6px;">Subject</th>
                    <th style="width:9%;">CA1<br/>({{ config('academyhub.results_ca1_max',20) }})</th>
                    <th style="width:9%;">CA2<br/>({{ config('academyhub.results_ca2_max',20) }})</th>
                    <th style="width:9%;">Exam<br/>({{ config('academyhub.results_exam_max',60) }})</th>
                    <th style="width:9%;">Total</th>
                    <th style="width:8%;">Grade</th>
                    @if($showClassAverage)<th style="width:8%;">Avg</th>@endif
                    @if($showClassHighestLowest)
                        <th style="width:6%;">High</th>
                        <th style="width:6%;">Low</th>
                    @endif
                    @if($showPosition)<th style="width:6%;">Pos</th>@endif
                    @if($showSubjectTeacherRemarks)<th style="width:10%;text-align:left;padding-left:4px;">Remark</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                @php
                    $g = strtoupper($r['grade'] ?? '-');
                    $badgeBg = match($g) { 'A'=>'#dcfce7','B'=>'#dbeafe','C'=>'#fef9c3','D'=>'#ffedd5','F','U'=>'#fee2e2',default=>'transparent' };
                    $badgeFg = match($g) { 'A'=>'#166534','B'=>'#1e40af','C'=>'#854d0e','D'=>'#9a3412','F','U'=>'#991b1b',default=>'#0f172a' };
                @endphp
                <tr>
                    <td class="subj">{{ $r['subject']?->name ?? '-' }}</td>
                    <td>{{ $r['ca1'] ?? '' }}</td>
                    <td>{{ $r['ca2'] ?? '' }}</td>
                    <td>{{ $r['exam'] ?? '' }}</td>
                    <td class="bold">{{ $r['total'] ?? '' }}</td>
                    <td class="bold">
                        @if($showColorBadges && ($r['grade'] ?? null))
                            <span style="background:{{ $badgeBg }};color:{{ $badgeFg }};padding:1px 4px;border-radius:2px;font-weight:bold;">{{ $g }}</span>
                        @else
                            {{ $r['grade'] ?? '' }}
                        @endif
                    </td>
                    @if($showClassAverage)<td>{{ $r['class_avg'] ?? '—' }}</td>@endif
                    @if($showClassHighestLowest)
                        <td>{{ $r['highest'] ?? '—' }}</td>
                        <td>{{ $r['lowest'] ?? '—' }}</td>
                    @endif
                    @if($showPosition)<td>{{ $r['position'] ?? '—' }}</td>@endif
                    @if($showSubjectTeacherRemarks)<td style="font-size:7px;text-align:left;padding-left:4px;font-style:italic;">{{ $r['teacher_remark'] ?? 'Good' }}</td>@endif
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

        {{-- Cumulative Summary --}}
        @if($showCumulativeSummary && !empty($cumulativeSummary))
        <div style="border:1.5px solid #a16207;background:#fffbeb;border-radius:3px;padding:5px 8px;margin-bottom:5px;">
            <div style="font-size:7.5px;font-weight:bold;text-transform:uppercase;color:#1e3a8a;margin-bottom:3px;">Annual Cumulative Summary ({{ $session }})</div>
            <table style="width:100%;border-collapse:collapse;text-align:center;font-size:8px;">
                <thead><tr style="border-bottom:1px solid #d1d5db;color:#374151;"><th style="padding:2px;">Term 1</th><th style="padding:2px;">Term 2</th><th style="padding:2px;">Term 3</th><th style="padding:2px;">Cumulative Avg</th></tr></thead>
                <tbody><tr>
                    <td style="padding:3px;font-weight:600;">{{ $cumulativeSummary['term_1']['total'] ?? '—' }}</td>
                    <td style="padding:3px;font-weight:600;">{{ $cumulativeSummary['term_2']['total'] ?? '—' }}</td>
                    <td style="padding:3px;font-weight:600;">{{ $cumulativeSummary['term_3']['total'] ?? '—' }}</td>
                    <td style="padding:3px;font-weight:800;">{{ $average }}%</td>
                </tr></tbody>
            </table>
        </div>
        @endif

        {{-- Psychomotor --}}
        @php($rcBorderColor='#a16207') @php($rcBgLight='#fffbeb') @php($rcTitleColor='#1e3a8a') @php($rcLabelColor='#1e3a8a')
        @include('pdf.partials.rc-psychomotor')

        {{-- Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks"><div class="remarks-label">Class Teacher's Remarks</div><div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div></div>
        @endif
        @if($showPrincipalRemarks)
        <div class="remarks"><div class="remarks-label">Principal's Remarks</div><div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div></div>
        @endif

        {{-- School Fees --}}
        @php($rcBorderColor='#1e3a8a') @php($rcBgLight='#eff6ff') @php($rcTitleColor='#1e3a8a') @php($rcLabelColor='#1e3a8a')
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
                @if(($signatureImages['principal']??null) && file_exists($signatureImages['principal']))<img src="{{ $signatureImages['principal'] }}" class="sig-img" /><div class="sig-line has-img">{{ $principalTitle ?? 'Principal' }}</div>
                @else<div class="sig-line">{{ $principalTitle ?? 'Principal' }}</div>@endif
                <div class="sig-sub">{{ $principalName ? $principalName . ' &bull; ' : '' }}Signature &amp; Stamp</div>
            </div>
            <div class="sig"><div class="sig-line">Parent/Guardian</div><div class="sig-sub">Signature &amp; Date</div></div>
        </div>
        @endif

        {{-- QR Code --}}
        @if($showQrCode)
        <div style="margin-top:4px;border-top:1px dashed #a16207;padding-top:4px;text-align:center;font-size:7px;color:#78716c;">&#128274; <strong>Official Verified Record</strong> &bull; Ref: <strong>{{ $student->admission_number }}</strong></div>
        @endif

        <div class="footer">Generated {{ now()->format('d M Y, g:i A') }} &bull; {{ $schoolName }} &bull; Powered by AcademyHub SMS</div>
    </div>
</div>
</body>
</html>
