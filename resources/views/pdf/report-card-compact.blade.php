<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Permanent Record - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 8.5pt; color: #1e293b; background: #ffffff; line-height: 1.35; }

        /* ─── 90% Main Page Wrapper (Centered with 5% Left & 5% Right Margins) ─── */
        .page { width: 90%; margin: 0 auto; background: #ffffff; }

        /* ─── Header Banner (Spans 100% of the 90% Centered Wrapper) ─── */
        .banner { background: #1e3a8a; color: #ffffff; text-align: center; padding: 18px 16px 14px 16px; width: 100%; margin-bottom: 22px; border-radius: 3px; }
        .banner-title { font-size: 22pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff; line-height: 1.1; margin-bottom: 4px; }
        .banner-subtitle { font-size: 8.5pt; font-weight: 600; text-transform: uppercase; letter-spacing: 2.5px; color: #93c5fd; }

        .container { width: 100%; }

        /* ─── Student Profile Section (Circular Photo + Light Grey Pills) ─── */
        .student-profile { display: table; width: 100%; margin-bottom: 22px; }
        .photo-cell { display: table-cell; width: 78px; vertical-align: middle; padding-right: 18px; }
        .photo { width: 68px; height: 68px; border-radius: 50%; object-fit: cover; display: block; }
        .photo-placeholder { width: 68px; height: 68px; border-radius: 50%; background: #f1f5f9; border: 1px solid #cbd5e1; text-align: center; line-height: 66px; font-size: 22pt; font-weight: 800; color: #64748b; }

        .info-cell { display: table-cell; vertical-align: middle; }

        .pill-row { display: table; width: 100%; margin-bottom: 7px; }
        .pill-row:last-child { margin-bottom: 0; }
        .pill-col { display: table-cell; padding-right: 8px; }
        .pill-col:last-child { padding-right: 0; }

        .pill { background: #f1f5f9; border-radius: 4px; padding: 6px 12px; font-size: 8.5pt; color: #334155; }
        .pill strong { font-weight: 700; color: #0f172a; }
        .pill span.label { color: #64748b; font-weight: 500; }

        /* ─── Academic Record Table ─── */
        @php
            $rowCount = count($rows ?? []);
            $padY = match(true) {
                $rowCount >= 16 => '3px',
                $rowCount >= 12 => '5px',
                $rowCount >= 9  => '7px',
                default         => '9.5px',
            };
            $padX = match(true) {
                $rowCount >= 16 => '4px',
                $rowCount >= 12 => '6px',
                $rowCount >= 9  => '8px',
                default         => '10px',
            };
            $fontSize = match(true) {
                $rowCount >= 16 => '7.5pt',
                $rowCount >= 12 => '8pt',
                default         => '8.5pt',
            };
        @endphp
        table.scores-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; table-layout: fixed; }
        table.scores-table th { background: #f8fafc; color: #0f172a; padding: {{ $padY }} {{ $padX }}; font-size: 7.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; text-align: center; }
        table.scores-table th.subj-th { text-align: left; padding-left: 12px; width: 34%; }
        table.scores-table td { padding: {{ $padY }} {{ $padX }}; border-bottom: 1px solid #f1f5f9; text-align: center; font-size: {{ $fontSize }}; color: #334155; }
        table.scores-table tr:nth-child(even) td { background: #fafafa; }
        table.scores-table td.subj-td { text-align: left; padding-left: 12px; font-weight: 600; color: #0f172a; word-wrap: break-word; }
        table.scores-table td.bold { font-weight: 700; color: #0f172a; }

        /* ─── Intermediate Achievement Legend Box ─── */
        .legend-box { background: #f8fafc; border-radius: 4px; overflow: hidden; margin-bottom: 14px; border: 1px solid #f1f5f9; }
        .legend-header { background: #f1f5f9; text-align: center; font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; padding: 6px; }
        .legend-body { display: table; width: 100%; padding: 8px 0; }
        .legend-item { display: table-cell; text-align: center; font-size: 7.5pt; color: #475569; font-weight: 500; padding: 2px 4px; }
        .legend-item strong { color: #0f172a; font-weight: 700; }

        /* ─── Remarks Cards ─── */
        .remark-card { background: #f8fafc; border-radius: 5px; border: 1px solid #e2e8f0; padding: 12px 16px; margin-bottom: 14px; min-height: 65px; }
        .remark-title { font-size: 8pt; font-weight: 800; text-transform: uppercase; color: #1e3a8a; letter-spacing: 0.5px; margin-bottom: 5px; }
        .remark-body { font-size: 9pt; color: #1e293b; line-height: 1.4; font-style: italic; min-height: 42px; }

        /* ─── Next Term Bar ─── */
        .next-term-bar { background: #1e3a8a; color: #ffffff; text-align: center; padding: 8px; font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; width: 100%; margin-bottom: 14px; border-radius: 3px; }

        /* ─── Signatures Table ─── */
        .sigs-table { display: table; width: 100%; margin-top: 12px; }
        .sig-cell { display: table-cell; width: 33.33%; text-align: center; padding: 0 8px; vertical-align: bottom; }
        .sig-img { max-height: 28px; max-width: 80px; object-fit: contain; margin-bottom: 2px; }
        .sig-line { border-top: 1px solid #94a3b8; margin-top: 16px; padding-top: 3px; font-size: 8pt; font-weight: 700; color: #0f172a; }
        .sig-line.has-img { margin-top: 2px; }
        .sig-sub { font-size: 6.5pt; color: #64748b; font-style: italic; }

        .footer { margin-top: 14px; text-align: center; font-size: 7pt; color: #94a3b8; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.03; width: 260px; height: 260px; }
    </style>
</head>
<body>
@php
    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = config('academyhub.school_logo');
    $logoPath = $logo ? public_path('uploads/'.str_replace('\\', '/', $logo)) : null;
    
    // Base64 helper for DomPDF bulletproof image embedding
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

    $logoDataUri = $toBase64($logoPath);

    // Resolve student photo and encode as Base64 Data URI
    $photoDataUri = null;
    if ($student->passport_photo) {
        $cleanRel = str_replace('\\', '/', $student->passport_photo);
        if (file_exists(public_path('uploads/' . $cleanRel))) {
            $photoDataUri = $toBase64(public_path('uploads/' . $cleanRel));
        } elseif (file_exists(public_path($cleanRel))) {
            $photoDataUri = $toBase64(public_path($cleanRel));
        }
    }

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

@if($logoDataUri && $showWatermark)
    <div class="watermark"><img src="{{ $logoDataUri }}" alt="" style="width:100%;height:100%;object-fit:contain;" /></div>
@endif

<div class="page">
    {{-- Header Banner (Spans 100% of 90% Wrapper) --}}
    <div class="banner">
        <div class="banner-title">Permanent Record</div>
        <div class="banner-subtitle">TERM {{ $term }} REPORT SHEET &bull; {{ $session }}</div>
    </div>

    <div class="container">
        {{-- Student Profile Section (Circular Photo + Light Grey Pills) --}}
        <div class="student-profile">
            <div class="photo-cell">
                @if($photoDataUri)
                    <img class="photo" src="{{ $photoDataUri }}" alt="Student Photo" />
                @else
                    <div class="photo-placeholder">{{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}</div>
                @endif
            </div>
            <div class="info-cell">
                <div class="pill-row">
                    <div class="pill-col" style="width: 100%;">
                        <div class="pill">
                            <span class="label">Student Name:</span> <strong>{{ $student->full_name }}</strong>
                        </div>
                    </div>
                </div>
                <div class="pill-row">
                    <div class="pill-col" style="width: 50%;">
                        <div class="pill">
                            <span class="label">Date of birth:</span> <strong>{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d/m/Y') : ($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y') : 'N/A') }}</strong>
                        </div>
                    </div>
                    <div class="pill-col" style="width: 25%;">
                        <div class="pill">
                            <span class="label">Male:</span> <strong>{{ strtolower($student->gender) === 'male' ? 'X' : '' }}</strong>
                        </div>
                    </div>
                    <div class="pill-col" style="width: 25%;">
                        <div class="pill">
                            <span class="label">Female:</span> <strong>{{ strtolower($student->gender) === 'female' ? 'X' : '' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="pill-row">
                    <div class="pill-col" style="width: 50%;">
                        <div class="pill">
                            <span class="label">Admission No:</span> <strong>{{ $student->admission_number }}</strong>
                        </div>
                    </div>
                    <div class="pill-col" style="width: 50%;">
                        <div class="pill">
                            <span class="label">Class / Section:</span> <strong>{{ $student->schoolClass?->name }} {{ $student->section?->name ? '('.$student->section->name.')' : '' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Academic Record Table --}}
        <table class="scores-table">
            <thead>
                <tr>
                    <th class="subj-th">ACADEMIC RECORD</th>
                    <th style="width:9%;">CA 1 ({{ config('academyhub.results_ca1_max',20) }})</th>
                    <th style="width:9%;">CA 2 ({{ config('academyhub.results_ca2_max',20) }})</th>
                    <th style="width:9%;">EXAM ({{ config('academyhub.results_exam_max',60) }})</th>
                    <th style="width:8%;">TOTAL</th>
                    <th style="width:8%;">GRADE</th>
                    @if($showClassAverage)<th style="width:9%;">AVG</th>@endif
                    @if($showClassHighestLowest)
                        <th style="width:7%;">HIGH</th>
                        <th style="width:7%;">LOW</th>
                    @endif
                    @if($showPosition)<th style="width:7%;">POS</th>@endif
                    @if($showSubjectTeacherRemarks)<th style="width:14%;">REMARK</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                @php
                    $g = strtoupper($r['grade'] ?? '-');
                    $badgeBg = match($g) {
                        'A' => '#dcfce7',
                        'B' => '#dbeafe',
                        'C' => '#fef9c3',
                        'D' => '#ffedd5',
                        'F', 'U' => '#fee2e2',
                        default => 'transparent',
                    };
                    $badgeFg = match($g) {
                        'A' => '#166534',
                        'B' => '#1e40af',
                        'C' => '#854d0e',
                        'D' => '#9a3412',
                        'F', 'U' => '#991b1b',
                        default => '#0f172a',
                    };
                @endphp
                <tr>
                    <td class="subj-td">{{ $r['subject']?->name ?? '-' }}</td>
                    <td>{{ $r['ca1'] ?? '-' }}</td>
                    <td>{{ $r['ca2'] ?? '-' }}</td>
                    <td>{{ $r['exam'] ?? '-' }}</td>
                    <td class="bold">{{ $r['total'] ?? '-' }}</td>
                    <td class="bold">
                        @if($showColorBadges && $r['grade'])
                            <span style="background:{{ $badgeBg }};color:{{ $badgeFg }};padding:2px 6px;border-radius:3px;font-weight:700;">{{ $g }}</span>
                        @else
                            {{ $g }}
                        @endif
                    </td>
                    @if($showClassAverage)<td>{{ $r['class_avg'] ?? '—' }}</td>@endif
                    @if($showClassHighestLowest)
                        <td>{{ $r['highest'] ?? '—' }}</td>
                        <td>{{ $r['lowest'] ?? '—' }}</td>
                    @endif
                    @if($showPosition)<td>{{ $r['position'] ?? '—' }}</td>@endif
                    @if($showSubjectTeacherRemarks)<td style="font-size:7.5pt;font-style:italic;">{{ $r['teacher_remark'] ?? 'Good' }}</td>@endif
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Cumulative Annual Summary (If enabled) --}}
        @if($showCumulativeSummary && !empty($cumulativeSummary))
        <div style="margin-bottom: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px 12px;">
            <div style="font-size: 8pt; font-weight: 700; color: #1e3a8a; text-transform: uppercase; margin-bottom: 4px;">ANNUAL CUMULATIVE SUMMARY ({{ $session }})</div>
            <table style="width:100%; border-collapse: collapse; text-align: center; font-size: 8pt;">
                <thead>
                    <tr style="border-bottom: 1px solid #cbd5e1; color: #475569;">
                        <th style="padding: 3px;">Term 1 Total</th>
                        <th style="padding: 3px;">Term 2 Total</th>
                        <th style="padding: 3px;">Term 3 Total</th>
                        <th style="padding: 3px;">Cumulative Avg</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 4px; font-weight: 600;">{{ $cumulativeSummary['term_1']['total'] ?? '—' }}</td>
                        <td style="padding: 4px; font-weight: 600;">{{ $cumulativeSummary['term_2']['total'] ?? '—' }}</td>
                        <td style="padding: 4px; font-weight: 600;">{{ $cumulativeSummary['term_3']['total'] ?? '—' }}</td>
                        <td style="padding: 4px; font-weight: 800; color: #1e3a8a;">{{ $average }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- Intermediate Achievement Legend Box --}}
        @if($showGradingKey)
        <div class="legend-box">
            <div class="legend-header">INTERMEDIATE ACHIEVEMENT</div>
            <div class="legend-body">
                <div class="legend-item"><strong>A</strong> = Excellent (70-100%)</div>
                <div class="legend-item"><strong>B</strong> = Above Average (60-69%)</div>
                <div class="legend-item"><strong>C</strong> = Average (50-59%)</div>
                <div class="legend-item"><strong>D</strong> = Below Average (40-49%)</div>
                <div class="legend-item"><strong>U</strong> = Unsatisfactory (0-39%)</div>
            </div>
        </div>
        @endif

        {{-- Remarks --}}
        @if($showTeacherRemarks && !empty($teacherRemarks))
        <div class="remark-card">
            <div class="remark-title">TEACHER'S REMARKS</div>
            <div class="remark-body">{{ $teacherRemarks }}</div>
        </div>
        @endif

        @if($showPrincipalRemarks && !empty($principalRemarks))
        <div class="remark-card">
            <div class="remark-title">PRINCIPAL'S REMARKS</div>
            <div class="remark-body">{{ $principalRemarks }}</div>
        </div>
        @endif

        {{-- Next Term Bar --}}
        @if($showNextTermDate)
        <div class="next-term-bar">NEXT TERM RESUMES: {{ strtoupper($nextTermDate ?? 'TO BE ANNOUNCED') }}</div>
        @endif

        {{-- Signatures --}}
        @if($showSignatures)
        <div class="sigs-table">
            <div class="sig-cell">
                @php($tSigUri = $toBase64($signatureImages['teacher'] ?? null))
                @if($tSigUri)<img src="{{ $tSigUri }}" class="sig-img" /><div class="sig-line has-img">Class Teacher</div>
                @else<div class="sig-line">Class Teacher</div>@endif
                <div class="sig-sub">Signature &amp; Date</div>
            </div>
            <div class="sig-cell">
                @php($pSigUri = $toBase64($signatureImages['principal'] ?? null))
                @if($pSigUri)<img src="{{ $pSigUri }}" class="sig-img" /><div class="sig-line has-img">{{ $principalTitle ?? 'Principal' }}</div>
                @else<div class="sig-line">{{ $principalTitle ?? 'Principal' }}</div>@endif
                <div class="sig-sub">{{ $principalName ? $principalName . ' &bull; ' : '' }}Signature &amp; Stamp</div>
            </div>
            <div class="sig-cell"><div class="sig-line">Parent/Guardian</div><div class="sig-sub">Signature &amp; Date</div></div>
        </div>
        @endif

        {{-- Verification QR Code Badge (If enabled) --}}
        @if($showQrCode)
        <div style="margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 6px; text-align: center; font-size: 7.5pt; color: #475569;">
            🔒 <strong>Official Verified Record</strong> &bull; Scan QR Code to verify document authenticity &bull; Ref: <strong>{{ $student->admission_number }}</strong>
        </div>
        @endif

        <div class="footer">Generated {{ now()->format('d M Y, g:i A') }} &bull; {{ $schoolName }} &bull; Powered by AcademyHub SMS</div>
    </div>
</div>
</body>
</html>
