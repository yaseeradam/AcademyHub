<!doctype html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Report Sheet - {{ $student->admission_number }}</title>
        <style>
            @page { margin: 4mm 6mm; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 9px;
                color: #1e293b;
                background: #fff;
                line-height: 1.25;
            }

            /* ─── Elegant Navy & Gold ─── */
            .page {
                border: 3.5px solid #1e3a5f;
                padding: 4px;
                background: white;
            }
            .page-inner {
                border: 1px solid #c4975a;
                padding: 6px;
            }

            .header {
                text-align: center;
                padding-bottom: 4px;
                margin-bottom: 6px;
                border-bottom: 2px solid #1e3a5f;
            }
            .header-table { display: table; width: 100%; }
            .header-cell { display: table-cell; vertical-align: middle; }
            .logo-cell { width: 70px; text-align: center; }
            .logo {
                width: 52px;
                height: 52px;
                object-fit: contain;
                border: 1.5px solid #c4975a;
                border-radius: 6px;
                padding: 2px;
                background: white;
            }
            .school-name {
                font-size: 18px;
                font-weight: 800;
                color: #1e3a5f;
                text-transform: uppercase;
                letter-spacing: 2px;
                margin-bottom: 2px;
            }
            .school-motto {
                font-size: 8px;
                color: #c4975a;
                font-style: italic;
                font-weight: 600;
                margin-bottom: 2px;
            }
            .school-info {
                font-size: 7.5px;
                color: #64748b;
                margin-bottom: 1px;
            }
            .report-badge {
                display: inline-block;
                margin-top: 4px;
                background: #1e3a5f;
                color: #c4975a;
                padding: 3px 16px;
                font-size: 9px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 2px;
                border-radius: 3px;
            }

            .meta-bar {
                display: table;
                width: 100%;
                margin-bottom: 4px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 4px;
            }
            .meta-cell {
                display: table-cell;
                width: 33.33%;
                padding: 4px;
                text-align: center;
                border-right: 1px solid #e2e8f0;
            }
            .meta-cell:last-child { border-right: none; }
            .meta-label {
                font-size: 7px;
                font-weight: 800;
                color: #c4975a;
                text-transform: uppercase;
                display: block;
            }
            .meta-value {
                font-size: 9px;
                font-weight: 800;
                color: #1e3a5f;
            }

            .stats-bar {
                display: table;
                width: 100%;
                margin-bottom: 4px;
            }
            .stat {
                display: table-cell;
                padding: 1.5px;
            }
            .stat-inner {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 4px;
                padding: 4px 2px;
                text-align: center;
            }
            .stat-inner.gold {
                background: #fef3c7;
                border-color: #c4975a;
            }
            .stat-label {
                font-size: 6.5px;
                font-weight: 800;
                color: #1e3a5f;
                text-transform: uppercase;
                margin-bottom: 1px;
            }
            .stat-value {
                font-size: 13px;
                font-weight: 800;
                color: #1e3a5f;
            }

            table.scores {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 4px;
                border: 1px solid #1e3a5f;
            }
            table.scores th {
                background: #1e3a5f;
                color: white;
                padding: 3px 4px;
                font-size: 7.5px;
                font-weight: 800;
                text-transform: uppercase;
                text-align: center;
                border: 1px solid #1e3a5f;
            }
            table.scores td {
                padding: 2px 4px;
                border: 1px solid #e2e8f0;
                text-align: center;
                font-size: 8.5px;
            }
            table.scores tr:nth-child(even) {
                background: #f8fafc;
            }
            .subj {
                text-align: left;
                font-weight: 700;
                color: #1e3a5f;
                padding-left: 6px;
            }
            .bold { font-weight: 800; }

            .grading {
                display: table;
                width: 100%;
                border: 1px solid #c4975a;
                margin-bottom: 4px;
                background: #fffbeb;
                border-radius: 3px;
            }
            .grading-cell {
                display: table-cell;
                padding: 3px 2px;
                text-align: center;
                font-size: 7px;
                font-weight: 600;
                color: #78350f;
                border-right: 1px solid #fde68a;
            }
            .grading-cell:last-child { border-right: none; }
            .grading-cell strong { color: #1e3a5f; }

            .attendance {
                display: table;
                width: 100%;
                margin-bottom: 4px;
                border: 1px solid #1e3a5f;
                border-radius: 3px;
            }
            .att-cell {
                display: table-cell;
                width: 33.33%;
                text-align: center;
                padding: 4px;
                border-right: 1px solid #e2e8f0;
                background: #f8fafc;
            }
            .att-cell:last-child { border-right: none; }
            .att-label {
                font-size: 7px;
                font-weight: 800;
                color: #1e3a5f;
                text-transform: uppercase;
                margin-bottom: 1px;
            }
            .att-value {
                font-size: 12px;
                font-weight: 800;
                color: #1e3a5f;
            }

            .remarks {
                border: 1px solid #c4975a;
                padding: 3px 5px;
                margin-bottom: 3px;
                background: #fffbeb;
                border-radius: 3px;
            }
            .remarks-label {
                font-size: 7px;
                font-weight: 800;
                color: #1e3a5f;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 1px;
            }
            .remarks-text {
                font-size: 8px;
                color: #1e293b;
                line-height: 1.25;
            }

            .next-term {
                background: #1e3a5f;
                color: #c4975a;
                text-align: center;
                padding: 4px;
                font-size: 8.5px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 4px;
                border-radius: 3px;
            }

            .signatures {
                display: table;
                width: 100%;
                margin-top: 4px;
            }
            .sig {
                display: table-cell;
                width: 33.33%;
                text-align: center;
                padding: 3px;
                vertical-align: bottom;
            }
            .sig-line {
                border-top: 1.5px solid #1e3a5f;
                margin-top: 8px;
                padding-top: 2px;
                font-size: 8px;
                font-weight: 800;
                color: #1e3a5f;
            }
            .sig-sub {
                font-size: 6.5px;
                color: #64748b;
                font-style: italic;
            }

            .footer {
                margin-top: 4px;
                border-top: 1px solid #c4975a;
                padding-top: 3px;
                text-align: center;
                font-size: 6.5px;
                color: #94a3b8;
            }

            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: -1;
                opacity: 0.03;
                width: 300px;
                height: 300px;
            }
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
                <img src="{{ $logoDataUri }}" alt="" style="width: 100%; height: 100%; object-fit: contain;" />
            </div>
        @endif

        <div class="page">
            <div class="page-inner">

                <div class="header">
                    <div class="header-table">
                        @if($logoExists)
                            <div class="header-cell logo-cell">
                                <img src="{{ $logoDataUri }}" alt="Logo" class="logo" />
                            </div>
                        @endif
                        <div class="header-cell" style="text-align: center;">
                            <div class="school-name">{{ $schoolName }}</div>
                            @if(config('academyhub.school_motto'))
                                <div class="school-motto">"{{ config('academyhub.school_motto') }}"</div>
                            @endif
                            @if(config('academyhub.school_address'))
                                <div class="school-info">{{ config('academyhub.school_address') }}</div>
                            @endif
                            @if(config('academyhub.school_phone'))
                                <div class="school-info">
                                    Phone: {{ config('academyhub.school_phone') }}
                                </div>
                            @endif
                            <div class="report-badge">Report Sheet</div>
                        </div>
                        @if($logoExists)
                            <div class="header-cell logo-cell">
                                <img src="{{ $logoDataUri }}" alt="Logo" class="logo" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="meta-bar">
                    <div class="meta-cell">
                        <span class="meta-label">Session</span>
                        <span class="meta-value">{{ $session }}</span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Term</span>
                        <span class="meta-value">Term {{ $term }}</span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Date Issued</span>
                        <span class="meta-value">{{ now()->format('d M, Y') }}</span>
                    </div>
                </div>

                @php($siBorderColor = '#1e3a5f')
                @php($siBgColor = '#f8fafc')
                @php($siLabelColor = '#1e3a5f')
                @php($siValueColor = '#1e293b')
                @php($siDotColor = '#c4975a')
                @include('pdf.partials.rc-student-info')

                <div class="stats-bar">
                    <div class="stat">
                        <div class="stat-inner gold">
                            <div class="stat-label">Total Score</div>
                            <div class="stat-value">{{ $grandTotal }}</div>
                        </div>
                    </div>
                    <div class="stat">
                        <div class="stat-inner">
                            <div class="stat-label">Average</div>
                            <div class="stat-value">{{ number_format($average, 1) }}%</div>
                        </div>
                    </div>
                    @if($showPosition)
                    <div class="stat">
                        <div class="stat-inner">
                            <div class="stat-label">Position</div>
                            <div class="stat-value">{{ $position }}</div>
                        </div>
                    </div>
                    @endif
                    @if($showClassAverage)
                    <div class="stat">
                        <div class="stat-inner">
                            <div class="stat-label">Class Average</div>
                            <div class="stat-value">{{ number_format($classAverage, 1) }}%</div>
                        </div>
                    </div>
                    @endif
                </div>

                <table class="scores">
                    <thead>
                        <tr>
                            <th style="width: 30%; text-align: left; padding-left: 6px;">Subject</th>
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
                <div class="grading">
                    <div class="grading-cell"><strong>A:</strong> 70-100 Excellent</div>
                    <div class="grading-cell"><strong>B:</strong> 60-69 Very Good</div>
                    <div class="grading-cell"><strong>C:</strong> 50-59 Good</div>
                    <div class="grading-cell"><strong>D:</strong> 40-49 Pass</div>
                    <div class="grading-cell"><strong>F:</strong> 0-39 Fail</div>
                </div>
                @endif

                @if($showAttendance)
                <div class="attendance">
                    <div class="att-cell">
                        <div class="att-label">Times Opened</div>
                        <div class="att-value">{{ $timesOpened ?? '—' }}</div>
                    </div>
                    <div class="att-cell">
                        <div class="att-label">Times Present</div>
                        <div class="att-value">{{ $timesPresent ?? '—' }}</div>
                    </div>
                    <div class="att-cell">
                        <div class="att-label">Times Absent</div>
                        <div class="att-value">{{ $timesAbsent ?? '—' }}</div>
                    </div>
                </div>
                @endif

                @php($rcBorderColor = '#1e3a5f')
                @php($rcBgLight = '#f8fafc')
                @php($rcTitleColor = '#1e3a5f')
                @php($rcLabelColor = '#1e3a5f')
                @include('pdf.partials.rc-psychomotor')
                @include('pdf.partials.rc-school-fees')

                @if($showTeacherRemarks)
                <div class="remarks">
                    <div class="remarks-label">Class Teacher's Remarks</div>
                    <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
                </div>
                @endif

                @if($showPrincipalRemarks)
                <div class="remarks">
                    <div class="remarks-label">Principal's Remarks</div>
                    <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
                </div>
                @endif

                @if($showNextTermDate)
                <div class="next-term">
                    Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}
                </div>
                @endif

                @if($showSignatures)
                <div class="signatures">
                    <div class="sig">
                        @if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher']))
                            <img src="{{ $signatureImages['teacher'] }}" alt="Teacher Signature" style="max-height: 30px; max-width: 80px; object-fit: contain; margin-bottom: 2px;" />
                        @endif
                        <div class="sig-line">Class Teacher</div>
                        <div class="sig-sub">Signature &amp; Date</div>
                    </div>
                    <div class="sig">
                        @if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal']))
                            <img src="{{ $signatureImages['principal'] }}" alt="Principal Signature" style="max-height: 30px; max-width: 80px; object-fit: contain; margin-bottom: 2px;" />
                        @endif
                        <div class="sig-line">Principal</div>
                        <div class="sig-sub">Signature &amp; Stamp</div>
                    </div>
                    <div class="sig">
                        <div class="sig-line">Parent/Guardian</div>
                        <div class="sig-sub">Signature &amp; Date</div>
                    </div>
                </div>
                @endif

                <div class="footer">
                    Generated on {{ now()->format('l, F j, Y \a\t g:i A') }} • {{ $schoolName }} • Powered by AcademyHub SMS
                </div>

            </div>
        </div>
    </body>
</html>
