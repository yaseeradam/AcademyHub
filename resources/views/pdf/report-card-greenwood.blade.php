<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Report Sheet - {{ $student->admission_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 3mm 5mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7.5px; color: #0f172a; background: #fff; line-height: 1.15; }

        /* Outer Frame & Borders */
        .page { border: 2.5px solid #004b49; padding: 4px; background: #fff; page-break-inside: avoid; }
        .page-inner { border: 1px solid #c5a059; padding: 5px; position: relative; }

        /* Top Header */
        .header-table { display: table; width: 100%; border-bottom: 2px solid #004b49; padding-bottom: 3px; margin-bottom: 4px; }
        .header-cell { display: table-cell; vertical-align: middle; }
        .logo-wrap { width: 55px; text-align: left; }
        .logo { width: 44px; height: 44px; object-fit: contain; border: 1px solid #c5a059; border-radius: 4px; padding: 1px; background: #fff; }
        .school-info { text-align: left; padding-left: 4px; }
        .school-name { font-size: 15px; font-weight: 900; color: #004b49; text-transform: uppercase; letter-spacing: 0.5px; }
        .school-tagline { font-size: 8px; font-weight: bold; color: #c5a059; font-style: italic; margin-top: 1px; }
        .school-meta { font-size: 7px; color: #475569; margin-top: 1px; }

        .session-card-wrap { width: 130px; text-align: right; vertical-align: top; }
        .session-card { background: #004b49; border: 1px solid #c5a059; border-radius: 4px; padding: 4px 6px; text-align: center; color: #ffffff; }
        .session-title { font-size: 9px; font-weight: 900; text-transform: uppercase; color: #ffffff; letter-spacing: 0.5px; }
        .session-value { font-size: 7.5px; font-weight: bold; color: #c5a059; margin-top: 1px; }

        /* Student Metadata Summary Grid */
        .meta-table { display: table; width: 100%; margin-bottom: 4px; border: 1px solid #004b49; border-radius: 3px; border-collapse: collapse; overflow: hidden; }
        .meta-row { display: table-row; }
        .meta-cell { display: table-cell; padding: 3px 5px; font-size: 7.5px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .meta-cell:last-child { border-right: none; }
        .meta-label { font-weight: 800; color: #004b49; text-transform: uppercase; font-size: 6.5px; display: block; margin-bottom: 1px; }
        .meta-value { font-weight: 700; color: #0f172a; font-size: 8px; }
        .stat-highlight { background: #f0faf9; text-align: center; }
        .stat-highlight .meta-value { color: #004b49; font-weight: 900; font-size: 9px; }

        /* Section Titles */
        .section-header { background: #004b49; color: #ffffff; padding: 2.5px 6px; font-weight: bold; font-size: 7.5px; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 2px; border-radius: 2px; }

        /* Academic Scores Table */
        table.scores-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; border: 1px solid #004b49; }
        table.scores-table th { background: #004b49; color: #ffffff; padding: 2.5px 3px; font-size: 7px; font-weight: bold; text-transform: uppercase; text-align: center; border: 1px solid #004b49; }
        table.scores-table td { padding: 2px 3px; border: 1px solid #cbd5e1; text-align: center; font-size: 7.5px; }
        table.scores-table tr:nth-child(even) td { background: #f8fafc; }
        table.scores-table td.subject-name { text-align: left; font-weight: bold; color: #004b49; padding-left: 5px; }
        table.scores-table td.bold { font-weight: 900; color: #004b49; }

        /* Middle Grid Layout (Attendance + Affective + Grading Scale) */
        .middle-grid { display: table; width: 100%; margin-bottom: 4px; table-layout: fixed; }
        .middle-row { display: table-row; }
        .middle-cell { display: table-cell; vertical-align: top; padding-right: 4px; }
        .middle-cell:last-child { padding-right: 0; }

        .sub-table { width: 100%; border-collapse: collapse; border: 1px solid #004b49; font-size: 7px; }
        .sub-table th { background: #004b49; color: #ffffff; padding: 2px 4px; font-size: 6.5px; text-transform: uppercase; font-weight: bold; text-align: center; }
        .sub-table td { padding: 2px 4px; border: 1px solid #cbd5e1; color: #334155; }
        .sub-table td.val { text-align: center; font-weight: bold; color: #004b49; }

        .scale-box { border: 1px solid #c5a059; background: #fffbeb; padding: 3px 5px; border-radius: 3px; font-size: 6.5px; color: #78350f; }
        .scale-box-title { font-weight: 900; text-transform: uppercase; color: #004b49; margin-bottom: 2px; font-size: 6.5px; border-bottom: 1px solid #c5a059; padding-bottom: 1px; }

        /* Remarks & Signatures Grid */
        .remarks-grid { display: table; width: 100%; margin-bottom: 3px; table-layout: fixed; }
        .remarks-row { display: table-row; }
        .remarks-cell { display: table-cell; vertical-align: top; padding-right: 4px; }
        .remarks-cell:last-child { padding-right: 0; }

        .remarks-box { border: 1px solid #004b49; border-radius: 3px; background: #ffffff; padding: 5px 8px; }
        .remarks-title { font-size: 7.5px; font-weight: 900; text-transform: uppercase; color: #004b49; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px; margin-bottom: 3px; }
        .remarks-text { font-size: 8px; color: #334155; line-height: 1.35; }
        .sig-container { margin-top: 2px; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 1px; }
        .sig-img { max-height: 18px; max-width: 75px; object-fit: contain; display: block; margin: 0 auto; }
        .sig-name { font-size: 7px; font-weight: 800; color: #004b49; }
        .sig-role { font-size: 5.5px; color: #64748b; font-style: italic; }

        /* Next Term & Fees Banner */
        .fees-banner { border: 1px solid #c5a059; background: #fffbeb; padding: 3px 6px; border-radius: 3px; margin-bottom: 3px; font-size: 7px; color: #78350f; }
        .resumes-banner { background: #004b49; color: #ffffff; text-align: center; padding: 2.5px; font-size: 7.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 2px; margin-bottom: 3px; }

        /* Signatures & Footer Line */
        .parent-sig-table { display: table; width: 100%; margin-top: 3px; border-top: 1px solid #cbd5e1; padding-top: 3px; }
        .parent-sig-cell { display: table-cell; width: 50%; font-size: 7px; font-weight: 800; color: #004b49; vertical-align: bottom; }
        .parent-sig-line { width: 140px; border-bottom: 1px solid #004b49; display: inline-block; }

        .footer { border-top: 1px solid #004b49; margin-top: 3px; padding-top: 2px; text-align: center; font-size: 6.5px; color: #004b49; font-weight: bold; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.03; width: 260px; height: 260px; }
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
    $showPerformance               = $opts['show_performance'] ?? false;

    // Resolve Homeroom Teacher
    $homeroomTeacher = null;
    if (!empty($rows) && count($rows) > 0) {
        $firstRow = collect($rows)->first();
        if ($firstRow && isset($firstRow['subject'])) {
            $allocation = \App\Models\SubjectAllocation::where('class_id', $student->class_id)
                ->where('subject_id', $firstRow['subject']->id)
                ->with('teacher')
                ->first();
            if ($allocation && $allocation->teacher) {
                $homeroomTeacher = $allocation->teacher->name;
            }
        }
    }
    if (!$homeroomTeacher) {
        $allocation = \App\Models\SubjectAllocation::where('class_id', $student->class_id)
            ->with('teacher')
            ->first();
        if ($allocation && $allocation->teacher) {
            $homeroomTeacher = $allocation->teacher->name;
        }
    }
    $homeroomTeacher = $homeroomTeacher ?? 'Class Teacher';

    // Attendance Calculations
    $enrolled = $timesOpened ?? 0;
    $present = $timesPresent ?? 0;
    $absent = $timesAbsent ?? 0;
    $attendanceRate = $enrolled > 0 ? round(($present / $enrolled) * 100) : 0;

    // Helper for performance label
    $getPerformanceText = function($total) {
        if ($total >= 80) return '★ Excellent';
        if ($total >= 70) return '★ Very Good';
        if ($total >= 60) return '★ Good';
        if ($total >= 50) return 'Pass';
        return 'Needs Work';
    };
@endphp

@if($logoExists && $showWatermark)
    <div class="watermark"><img src="{{ $logoDataUri }}" alt="" style="width:100%;height:100%;object-fit:contain;" /></div>
@endif

<div class="page">
    <div class="page-inner">

        <!-- Top Header -->
        <div class="header-table">
            <div class="header-cell logo-wrap">
                @if($logoExists)
                    <img class="logo" src="{{ $logoDataUri }}" alt="Logo">
                @endif
            </div>
            <div class="header-cell school-info">
                <div class="school-name">{{ $schoolName }}</div>
                @if(config('academyhub.school_address'))
                    <div class="school-meta">{{ config('academyhub.school_address') }}</div>
                @endif
                @if(config('academyhub.school_phone'))
                    <div class="school-meta">Tel: {{ config('academyhub.school_phone') }}</div>
                @endif
                <div class="school-tagline">{{ config('academyhub.school_motto') ?: 'Learn Today, Lead Tomorrow' }}</div>
            </div>
            <div class="header-cell session-card-wrap">
                <div class="session-card">
                    <div class="session-title">REPORT SHEET</div>
                    <div class="session-value">{{ $session }} • Term {{ $term }}</div>
                </div>
            </div>
        </div>

        <!-- Student Meta Summary Grid -->
        <div class="meta-table">
            <div class="meta-row">
                <div class="meta-cell">
                    <span class="meta-label">Student Name:</span>
                    <span class="meta-value">{{ $student->full_name }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Reporting Period:</span>
                    <span class="meta-value">Term {{ $term }}</span>
                </div>
                <div class="meta-cell stat-highlight">
                    <span class="meta-label">Total Score:</span>
                    <span class="meta-value">{{ $grandTotal }}</span>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-cell">
                    <span class="meta-label">Grade / Class:</span>
                    <span class="meta-value">{{ $student->schoolClass?->name ?? 'N/A' }}{{ $student->section?->name ? ' - ' . $student->section->name : '' }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Academic Year:</span>
                    <span class="meta-value">{{ $session }}</span>
                </div>
                <div class="meta-cell stat-highlight">
                    <span class="meta-label">Average Score:</span>
                    <span class="meta-value">{{ number_format($average, 1) }}%</span>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-cell">
                    <span class="meta-label">Student ID:</span>
                    <span class="meta-value">{{ $student->admission_number }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Gender:</span>
                    <span class="meta-value">{{ $student->gender ?? 'N/A' }}</span>
                </div>
                <div class="meta-cell stat-highlight">
                    <span class="meta-label">Class Position:</span>
                    <span class="meta-value">{{ $showPosition ? $position : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Academic Performance Table Section -->
        <div class="section-header">ACADEMIC PERFORMANCE</div>
        <table class="scores-table">
            <thead>
                <tr>
                    <th style="width: 28%; text-align: left; padding-left: 5px;">SUBJECT</th>
                    <th style="width: 9%;">CA1</th>
                    <th style="width: 9%;">CA2</th>
                    <th style="width: 9%;">EXAM</th>
                    <th style="width: 10%;">TOTAL</th>
                    <th style="width: 7%;">GRADE</th>
                    @if($showClassAverage)<th style="width: 8%;">AVG</th>@endif
                    @if($showClassHighestLowest)
                        <th style="width: 6%;">HIGH</th>
                        <th style="width: 6%;">LOW</th>
                    @endif
                    @if($showPosition)<th style="width: 6%;">POS</th>@endif
                    @if($showSubjectTeacherRemarks)<th style="width: 10%; text-align: left; padding-left: 4px;">REMARK</th>@endif
                    @if($showPerformance)<th style="width: 14%;">PERFORMANCE</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    @php
                        $g = strtoupper($r['grade'] ?? '-');
                        $badgeBg = match($g) { 'A'=>'#dcfce7','B'=>'#dbeafe','C'=>'#fef9c3','D'=>'#ffedd5','F','U'=>'#fee2e2',default=>'transparent' };
                        $badgeFg = match($g) { 'A'=>'#166534','B'=>'#1e40af','C'=>'#854d0e','D'=>'#9a3412','F','U'=>'#991b1b',default=>'#004b49' };
                    @endphp
                    <tr>
                        <td class="subject-name">{{ $r['subject']?->name ?? '-' }}</td>
                        <td>{{ $r['ca1'] ?? '-' }}</td>
                        <td>{{ $r['ca2'] ?? '-' }}</td>
                        <td>{{ $r['exam'] ?? '-' }}</td>
                        <td class="bold">{{ $r['total'] ?? '-' }}</td>
                        <td class="bold">
                            @if($showColorBadges && ($r['grade'] ?? null))
                                <span style="background:{{ $badgeBg }};color:{{ $badgeFg }};padding:1px 4px;border-radius:2px;font-weight:bold;">{{ $g }}</span>
                            @else
                                {{ $r['grade'] ?? '-' }}
                            @endif
                        </td>
                        @if($showClassAverage)<td>{{ $r['class_avg'] ?? '-' }}</td>@endif
                        @if($showClassHighestLowest)
                            <td>{{ $r['highest'] ?? '-' }}</td>
                            <td>{{ $r['lowest'] ?? '-' }}</td>
                        @endif
                        @if($showPosition)<td>{{ $r['position'] ?? '-' }}</td>@endif
                        @if($showSubjectTeacherRemarks)<td style="font-size:7px;text-align:left;padding-left:4px;font-style:italic;">{{ $r['teacher_remark'] ?? 'Good' }}</td>@endif
                        @if($showPerformance)<td style="font-weight: 700; color: #004b49;">{{ $getPerformanceText($r['total'] ?? 0) }}</td>@endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Middle Grid: Attendance & Affective & Grading Scale -->
        <div class="middle-grid">
            <div class="middle-row">
                <!-- Attendance -->
                @if($showAttendance)
                    <div class="middle-cell" style="width: 32%;">
                        <table class="sub-table">
                            <thead>
                                <tr>
                                    <th colspan="2">ATTENDANCE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Days Enrolled</td>
                                    <td class="val">{{ $enrolled }}</td>
                                </tr>
                                <tr>
                                    <td>Days Present</td>
                                    <td class="val">{{ $present }}</td>
                                </tr>
                                <tr>
                                    <td>Days Absent</td>
                                    <td class="val">{{ $absent }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Attendance Rate</strong></td>
                                    <td class="val" style="color: #004b49; font-weight: 900;">{{ $attendanceRate }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Affective & Behavior Domain -->
                @if($showPsychomotor && !empty($psychomotorTraits))
                <div class="middle-cell" style="width: 36%;">
                    <table class="sub-table">
                        <thead>
                            <tr>
                                <th>BEHAVIOR &amp; WORK HABITS</th>
                                <th>SCORE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($psychomotorTraits as $trait => $score)
                            <tr><td>{{ $trait }}</td><td class="val">{{ $score }}/5</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif(!$showPsychomotor)
                {{-- hidden --}}
                @else
                <div class="middle-cell" style="width: 36%;">
                    <table class="sub-table">
                        <thead><tr><th>BEHAVIOR &amp; WORK HABITS</th><th>GRADE</th></tr></thead>
                        <tbody>
                            <tr><td>Attitude</td><td class="val">B+</td></tr>
                            <tr><td>Class Participation</td><td class="val">B+</td></tr>
                            <tr><td>Completes Assignments</td><td class="val">B+</td></tr>
                            <tr><td>Works Independently</td><td class="val">B+</td></tr>
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Grading Scale Box -->
                @if($showGradingKey)
                    <div class="middle-cell" style="width: 32%;">
                        <div class="scale-box">
                            <div class="scale-box-title">GRADING SCALE</div>
                            <div><strong>A+</strong> 97-100% | <strong>B+</strong> 87-89% | <strong>C+</strong> 77-79%</div>
                            <div><strong>A</strong> 93-96% | <strong>B</strong> 83-86% | <strong>C</strong> 73-76%</div>
                            <div><strong>A-</strong> 90-92% | <strong>B-</strong> 80-82% | <strong>D</strong> 70-72%</div>
                            <div><strong>F</strong> Below 70%</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Remarks & Comments Grid -->
        @php
            $remarksCount = 0;
            if ($showTeacherRemarks) $remarksCount++;
            if ($showPrincipalRemarks) $remarksCount++;
            $remWidth = $remarksCount > 0 ? (100 / $remarksCount) . '%' : '100%';
        @endphp

        @if($remarksCount > 0)
            <div class="remarks-grid">
                <div class="remarks-row">
                    @if($showTeacherRemarks)
                        <div class="remarks-cell" style="width: {{ $remWidth }};">
                            <div class="remarks-box">
                                <div class="remarks-title">TEACHER COMMENTS</div>
                                <div class="remarks-text">
                                    {{ $teacherRemarks ?? 'An excellent student with outstanding performance.' }}
                                </div>
                                <div class="sig-container">
                                    @if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher']))
                                        <img src="{{ $signatureImages['teacher'] }}" class="sig-img" alt="Teacher Signature" />
                                    @endif
                                    <div class="sig-name">{{ $homeroomTeacher }}</div>
                                    <div class="sig-role">Class Teacher</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($showPrincipalRemarks)
                        <div class="remarks-cell" style="width: {{ $remWidth }};">
                            <div class="remarks-box">
                                <div class="remarks-title">PRINCIPAL'S REMARKS</div>
                                <div class="remarks-text">
                                    {{ $principalRemarks ?? 'A commendable result. Continue to strive for excellence.' }}
                                </div>
                                <div class="sig-container">
                                    @if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal']))
                                        <img src="{{ $signatureImages['principal'] }}" class="sig-img" alt="Principal Signature" />
                                    @endif
                                    <div class="sig-name">{{ $principalName ?: ($principalTitle ?? 'Principal') }}</div>
                                    <div class="sig-role">{{ $principalTitle ?? 'Principal' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Cumulative Summary --}}
        @if($showCumulativeSummary && !empty($cumulativeSummary))
        <div style="border:1px solid #004b49;border-radius:3px;background:#f0fdf4;padding:5px 8px;margin-bottom:3px;">
            <div style="font-size:7.5px;font-weight:900;text-transform:uppercase;color:#004b49;margin-bottom:3px;">Annual Cumulative Summary ({{ $session }})</div>
            <table style="width:100%;border-collapse:collapse;text-align:center;font-size:8px;">
                <thead><tr style="border-bottom:1px solid #bbf7d0;color:#004b49;"><th style="padding:2px;">Term 1</th><th style="padding:2px;">Term 2</th><th style="padding:2px;">Term 3</th><th style="padding:2px;">Cumulative Avg</th></tr></thead>
                <tbody><tr>
                    <td style="padding:3px;font-weight:600;">{{ $cumulativeSummary['term_1']['total'] ?? '—' }}</td>
                    <td style="padding:3px;font-weight:600;">{{ $cumulativeSummary['term_2']['total'] ?? '—' }}</td>
                    <td style="padding:3px;font-weight:600;">{{ $cumulativeSummary['term_3']['total'] ?? '—' }}</td>
                    <td style="padding:3px;font-weight:800;">{{ $average }}%</td>
                </tr></tbody>
            </table>
        </div>
        @endif

        {{-- QR Code --}}
        @if($showQrCode)
        <div style="margin-top:3px;border-top:1px dashed #004b49;padding-top:3px;text-align:center;font-size:7px;color:#004b49;">&#128274; <strong>Official Verified Record</strong> &bull; Ref: <strong>{{ $student->admission_number }}</strong></div>
        @endif

        <!-- School Fees Information (if enabled) -->
        @if($showSchoolFees && isset($schoolFees))
            <div class="fees-banner">
                <strong>OUTSTANDING / NEXT TERM FEES INFORMATION:</strong> 
                Fee Amount: {{ $schoolFees['currency'] ?? '₦' }}{{ number_format($schoolFees['amount'] ?? 45000, 2) }} | 
                Bank Name: {{ $schoolFees['bank_name'] ?? 'Zenith Bank' }} | 
                Account Number: {{ $schoolFees['account_number'] ?? '1023456789' }}
            </div>
        @endif

        <!-- Next Term Resumes Banner -->
        @if($showNextTermDate)
            <div class="resumes-banner">
                NEXT TERM BEGINS: {{ $nextTermDate ?? 'SEPTEMBER 8, 2025' }}
            </div>
        @endif

        <!-- Parent Signature & Date Line -->
        <div class="parent-sig-table">
            <div class="parent-sig-cell">
                PARENT/GUARDIAN SIGNATURE: <span class="parent-sig-line"></span>
            </div>
            <div class="parent-sig-cell" style="text-align: right;">
                DATE: <span class="parent-sig-line" style="width: 120px;"></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            {{ $schoolName }} • Powered by AcademyHub SMS
        </div>

    </div>
</div>
</body>
</html>
