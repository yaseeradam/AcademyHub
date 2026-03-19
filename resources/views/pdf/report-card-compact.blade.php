<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Report Card - {{ $student->admission_number }}</title>
        <style>
            @page { margin: 18px; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #0f172a; }

            .page {
                border: 3px solid #0ea5e9;
                padding: 16px;
                background: #fff;
            }
            .header {
                border-bottom: 2px solid #0ea5e9;
                padding-bottom: 10px;
                margin-bottom: 12px;
            }
            .header-table { display: table; width: 100%; }
            .header-cell { display: table-cell; vertical-align: middle; }
            .logo-wrap { width: 90px; }
            .logo {
                width: 72px;
                height: 72px;
                object-fit: contain;
                border: 2px solid #0ea5e9;
                border-radius: 10px;
                padding: 6px;
                background: #fff;
            }
            .school-name { font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #0c4a6e; }
            .school-meta { margin-top: 3px; font-size: 9px; color: #475569; font-weight: 600; }
            .badge {
                display: inline-block;
                margin-top: 8px;
                background: #0ea5e9;
                color: #fff;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 1px;
                text-transform: uppercase;
            }

            .grid {
                display: table;
                width: 100%;
                margin-top: 10px;
                border: 1px solid #e2e8f0;
            }
            .row { display: table-row; }
            .cell { display: table-cell; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
            .cell.label { width: 28%; background: #f8fafc; font-weight: 800; color: #334155; }
            .cell.value { font-weight: 700; color: #0f172a; }

            .scores {
                margin-top: 12px;
                border: 1px solid #e2e8f0;
            }
            .scores th, .scores td {
                padding: 7px 8px;
                border-bottom: 1px solid #e2e8f0;
                font-size: 10px;
            }
            .scores th {
                text-align: left;
                background: #0ea5e9;
                color: #fff;
                font-weight: 900;
                letter-spacing: 0.5px;
                text-transform: uppercase;
            }
            .scores td.num { text-align: right; width: 56px; }
            .scores td.grade { text-align: center; width: 52px; font-weight: 900; }
            .scores tr:nth-child(even) td { background: #f8fafc; }

            .summary {
                margin-top: 12px;
                border: 1px solid #e2e8f0;
                padding: 10px;
                background: #f0f9ff;
            }
            .summary-line { margin-top: 4px; font-size: 10px; font-weight: 800; color: #0c4a6e; }
            .muted { color: #475569; font-weight: 700; }
        </style>
    </head>
    <body>
        @php
            $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
            $logo = config('myacademy.school_logo');
            $logoPath = $logo ? public_path('uploads/'.str_replace('\\', '/', $logo)) : null;
            $termLabel = 'Term '.$term;
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
        <div class="page">
            <div class="header">
                <div class="header-table">
                    <div class="header-cell logo-wrap">
                        @if($logoPath && file_exists($logoPath))
                            <img class="logo" src="{{ $logoPath }}" alt="">
                        @endif
                    </div>
                    <div class="header-cell">
                        <div class="school-name">{{ $schoolName }}</div>
                        @php($address = config('myacademy.school_address'))
                        @if($address)
                            <div class="school-meta">{{ $address }}</div>
                        @endif
                        <div class="badge">Student Report Card</div>
                    </div>
                    <div class="header-cell" style="width: 170px; text-align: right;">
                        <div class="school-meta"><span class="muted">Session:</span> {{ $session }}</div>
                        <div class="school-meta"><span class="muted">Term:</span> {{ $termLabel }}</div>
                    </div>
                </div>
            </div>

            
                @php($siBorderColor = '#0ea5e9')
                @php($siBgColor = '#f0f9ff')
                @php($siLabelColor = '#0c4a6e')
                @php($siValueColor = '#0f172a')
                @php($siDotColor = '#bae6fd')
                @include('pdf.partials.rc-student-info')


            <table class="scores" width="100%" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th style="text-align:right;">CA1</th>
                        <th style="text-align:right;">CA2</th>
                        <th style="text-align:right;">Exam</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:center;">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                        <tr>
                            <td>{{ $r['subject']?->name ?? '-' }}</td>
                            <td class="num">{{ $r['ca1'] ?? '' }}</td>
                            <td class="num">{{ $r['ca2'] ?? '' }}</td>
                            <td class="num">{{ $r['exam'] ?? '' }}</td>
                            <td class="num" style="font-weight: 900;">{{ $r['total'] ?? '' }}</td>
                            <td class="grade">{{ $r['grade'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-line">Grand Total: <span class="muted">{{ $grandTotal }}</span></div>
                <div class="summary-line">Average: <span class="muted">{{ $average }}</span></div>
                @if($showPosition)
                <div class="summary-line">Position: <span class="muted">{{ $position }}</span></div>
                @endif
                @if($showClassAverage)
                <div class="summary-line">Class Average: <span class="muted">{{ $classAverage }}</span></div>
                @endif
                @if($showAttendance)
                <div class="summary-line">Days Opened: <span class="muted">{{ $timesOpened ?? '—' }}</span></div>
                <div class="summary-line">Days Present: <span class="muted">{{ $timesPresent ?? '—' }}</span></div>
                <div class="summary-line">Days Absent: <span class="muted">{{ $timesAbsent ?? '—' }}</span></div>
                @endif
            </div>

            @php($rcBorderColor = '#0ea5e9')
            @php($rcBgLight = '#f0f9ff')
            @php($rcTitleColor = '#0c4a6e')
            @php($rcLabelColor = '#334155')
            @include('pdf.partials.rc-psychomotor')
            @include('pdf.partials.rc-school-fees')

            @if($showTeacherRemarks)
            <div class="summary" style="margin-top: 8px;">
                <div class="summary-line">Teacher's Remarks: <span class="muted">{{ $teacherRemarks ?? '—' }}</span></div>
            </div>
            @endif
            @if($showPrincipalRemarks)
            <div class="summary" style="margin-top: 8px;">
                <div class="summary-line">Principal's Remarks: <span class="muted">{{ $principalRemarks ?? '—' }}</span></div>
            </div>
            @endif
            @if($showNextTermDate)
            <div class="summary" style="margin-top: 8px; text-align: center;">
                <div class="summary-line">Next Term Begins: <span class="muted">{{ $nextTermDate ?? 'To be announced' }}</span></div>
            </div>
            @endif
        </div>
    </body>
</html>

