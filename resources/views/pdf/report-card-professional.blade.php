<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1e293b; background: #f8fafc; }

        /* ─── PROFESSIONAL Sidebar Layout Style ─── */
        .page { display: table; width: 100%; height: 100%; min-height: 270mm; background: #f8fafc; border: 3px solid #1e3a8a; }
        
        .sidebar { display: table-cell; width: 190px; background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%); color: white; padding: 18px 12px; vertical-align: top; border-right: 2px solid #1d4ed8; }
        .sidebar-school { font-size: 13px; font-weight: 900; text-align: center; margin-bottom: 12px; letter-spacing: 1px; color: #ffffff; text-transform: uppercase; line-height: 1.3; }
        .sidebar-logo { width: 56px; height: 56px; object-fit: contain; border-radius: 8px; background: white; margin: 0 auto 12px; display: block; padding: 3px; border: 2px solid #93c5fd; }
        .sidebar-section { margin-bottom: 10px; }
        .sidebar-label { font-size: 6.5px; font-weight: 700; text-transform: uppercase; color: #93c5fd; margin-bottom: 2px; letter-spacing: 0.5px; }
        .sidebar-value { font-size: 9.5px; font-weight: 700; color: #ffffff; }
        .sidebar-divider { height: 1.5px; background: rgba(255,255,255,0.15); margin: 10px 0; }
        
        .sidebar-stat { background: rgba(255,255,255,0.1); border-radius: 6px; padding: 6px; margin-bottom: 5px; text-align: center; border: 1px solid rgba(255,255,255,0.15); }
        .sidebar-stat-label { font-size: 6px; font-weight: 700; text-transform: uppercase; color: #93c5fd; margin-bottom: 1px; }
        .sidebar-stat-value { font-size: 15px; font-weight: 900; color: #ffffff; }
        
        .main { display: table-cell; vertical-align: top; padding: 18px; background: #f8fafc; }
        
        .header-bar { background: white; border-left: 4px solid #1e40af; padding: 10px 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .header-title { font-size: 16px; font-weight: 900; color: #1e40af; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 3px; }
        .header-meta { font-size: 8px; color: #64748b; font-weight: 700; }
        
        /* Photo Grid */
        .info-grid { display: table; width: 100%; background: white; border-radius: 8px; padding: 10px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 4px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .info-cell.lbl { width: 25%; font-size: 7px; font-weight: 850; color: #64748b; text-transform: uppercase; }
        .info-cell.val { font-size: 8.5px; font-weight: 700; color: #1e293b; }
        
        .photo-wrap { display: table-cell; width: 65px; text-align: center; vertical-align: middle; padding-left: 8px; }
        .photo { width: 55px; height: 66px; object-fit: cover; border-radius: 4px; border: 2px solid #1e40af; }

        /* Scores Table */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        th { background: #1e40af; color: white; padding: 6px 3px; font-size: 7.5px; font-weight: 800; text-transform: uppercase; text-align: center; border: 1px solid #1d4ed8; }
        td { padding: 5px 3px; font-size: 8px; border-bottom: 1px solid #e2e8f0; text-align: center; color: #334155; }
        tr:nth-child(even) td { background: #f8fafc; }
        .subj { text-align: left !important; font-weight: 700; color: #1e40af; padding-left: 8px !important; }
        .bold { font-weight: 800; color: #1e293b; }
        
        /* Grading Key Bar */
        .grade-bar { background: white; border-radius: 6px; padding: 6px; margin-bottom: 12px; text-align: center; font-size: 7.5px; color: #64748b; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .grade-bar strong { color: #1e40af; font-size: 8.5px; margin: 0 4px; }
        
        /* Remarks Cards */
        .remarks { background: white; border-radius: 8px; padding: 8px 12px; margin-bottom: 8px; border-left: 3px solid #1e40af; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
        .remarks-label { font-size: 7.5px; color: #1e40af; font-weight: 900; text-transform: uppercase; margin-bottom: 3px; letter-spacing: 0.5px; }
        .remarks-text { font-size: 8px; color: #475569; min-height: 18px; line-height: 1.3; }
        
        .next-bar { background: #1e40af; color: white; text-align: center; padding: 6px; border-radius: 6px; font-size: 8.5px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Signatures Layout */
        .sigs { display: table; width: 100%; margin-top: 8px; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; vertical-align: bottom; }
        .sig-img { max-height: 30px; max-width: 85px; object-fit: contain; margin-bottom: 3px; }
        .sig-line { border-top: 1.5px solid #1e40af; margin-top: 22px; padding-top: 3px; font-size: 8px; font-weight: 850; color: #1e40af; text-transform: uppercase; }
        .sig-line.has-img { margin-top: 3px; }
        .sig-sub { font-size: 6.5px; color: #64748b; font-style: italic; margin-top: 1px; }
        
        .footer { text-align: center; font-size: 7px; color: #94a3b8; margin-top: 10px; border-top: 1.5px solid #e2e8f0; padding-top: 4px; }
        .watermark { position: fixed; top: 50%; left: 55%; transform: translate(-50%,-50%); z-index: -1; opacity: 0.02; width: 320px; height: 320px; }
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
    {{-- Left Sidebar Panel --}}
    <div class="sidebar">
        @if($logoExists)
            <img src="{{ $logoPath }}" alt="Logo" class="sidebar-logo" />
        @endif
        <div class="sidebar-school">{{ $schoolName }}</div>
        
        <div class="sidebar-divider"></div>
        
        <div class="sidebar-section">
            <div class="sidebar-label">Student Name</div>
            <div class="sidebar-value">{{ $student->full_name }}</div>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-label">Admission No</div>
            <div class="sidebar-value">{{ $student->admission_number }}</div>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-label">Class Level</div>
            <div class="sidebar-value">{{ $student->schoolClass?->name }}</div>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-label">Class Section</div>
            <div class="sidebar-value">{{ $student->section?->name ?? 'N/A' }}</div>
        </div>
        
        <div class="sidebar-divider"></div>
        
        <div class="sidebar-stat">
            <div class="sidebar-stat-label">Total Score</div>
            <div class="sidebar-stat-value">{{ $grandTotal }}</div>
        </div>
        
        <div class="sidebar-stat">
            <div class="sidebar-stat-label">Average</div>
            <div class="sidebar-stat-value">{{ number_format($average, 1) }}%</div>
        </div>
        
        @if($showPosition)
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Position / Rank</div>
                <div class="sidebar-stat-value">{{ $position }}</div>
            </div>
        @endif
        
        @if($showClassAverage)
            <div class="sidebar-divider"></div>
            <div class="sidebar-section">
                <div class="sidebar-label">Class Average</div>
                <div class="sidebar-value" style="font-size:12px;">{{ number_format($classAverage, 1) }}%</div>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Highest in Class</div>
                <div class="sidebar-value" style="font-size:11px;">{{ number_format($highestAverage ?? 0, 1) }}%</div>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-label">Lowest in Class</div>
                <div class="sidebar-value" style="font-size:11px;">{{ number_format($lowestAverage ?? 0, 1) }}%</div>
            </div>
        @endif
    </div>
    
    {{-- Right Main Panel --}}
    <div class="main">
        <div class="header-bar">
            <div class="header-title">Official Academic Report</div>
            <div class="header-meta">Session: <strong>{{ $session }}</strong> | Term: <strong>Term {{ $term }}</strong> | Date: <strong>{{ now()->format('d M, Y') }}</strong></div>
        </div>
        
        {{-- Student Profile Detail Grid --}}
        <div class="info-grid">
            <div style="display: table-cell; vertical-align: top;">
                <div style="display: table; width: 100%;">
                    <div class="info-row">
                        <div class="info-cell lbl">Gender</div>
                        <div class="info-cell val">{{ $student->gender ?? 'N/A' }}</div>
                        <div class="info-cell lbl">Date of Birth</div>
                        <div class="info-cell val">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M, Y') : ($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell lbl">No in Class</div>
                        <div class="info-cell val">{{ $totalStudents ?? 'N/A' }}</div>
                        <div class="info-cell lbl">Term Status</div>
                        <div class="info-cell val" style="color:#059669; font-weight:800;">Completed</div>
                    </div>
                </div>
            </div>
            @if($student->passport_photo)
                <div class="photo-wrap">
                    <img src="{{ public_path('uploads/' . str_replace('\\', '/', $student->passport_photo)) }}" alt="Photo" class="photo" />
                </div>
            @endif
        </div>
        
        {{-- Scores Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 32%; text-align: left; padding-left: 8px;">Subject</th>
                    <th style="width: 10%;">CA1 ({{ config('myacademy.results_ca1_max',20) }})</th>
                    <th style="width: 10%;">CA2 ({{ config('myacademy.results_ca2_max',20) }})</th>
                    <th style="width: 10%;">Exam ({{ config('myacademy.results_exam_max',60) }})</th>
                    <th style="width: 10%;">Total</th>
                    <th style="width: 9%;">Grade</th>
                    @if($showClassAverage)<th style="width: 9%;">Class Avg</th>@endif
                    @if($showPosition)<th style="width: 9%;">Position</th>@endif
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
        
        {{-- Grading Scale --}}
        @if($showGradingKey)
        <div class="grade-bar">
            <strong>A:</strong> 70-100 (Excellent) | <strong>B:</strong> 60-69 (Very Good) | <strong>C:</strong> 50-59 (Good) | <strong>D:</strong> 40-49 (Pass) | <strong>F:</strong> 0-39 (Fail)
        </div>
        @endif

        {{-- Attendance Indicators --}}
        @if($showAttendance)
            <div style="display: table; width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 12px; background: white;">
                <div style="display: table-cell; width: 33.33%; padding: 6px; text-align: center; border-right: 1px solid #f1f5f9;">
                    <div style="font-size: 7px; font-weight: 800; text-transform: uppercase; color: #1e40af;">Times Opened</div>
                    <div style="font-size: 13px; font-weight: 900; color: #1e293b; margin-top: 1px;">{{ $timesOpened ?? '—' }}</div>
                </div>
                <div style="display: table-cell; width: 33.33%; padding: 6px; text-align: center; border-right: 1px solid #f1f5f9;">
                    <div style="font-size: 7px; font-weight: 800; text-transform: uppercase; color: #1e40af;">Times Present</div>
                    <div style="font-size: 13px; font-weight: 900; color: #1e293b; margin-top: 1px;">{{ $timesPresent ?? '—' }}</div>
                </div>
                <div style="display: table-cell; width: 33.33%; padding: 6px; text-align: center;">
                    <div style="font-size: 7px; font-weight: 800; text-transform: uppercase; color: #1e40af;">Times Absent</div>
                    <div style="font-size: 13px; font-weight: 900; color: #1e293b; margin-top: 1px;">{{ $timesAbsent ?? '—' }}</div>
                </div>
            </div>
        @endif
        
        {{-- Psychomotor & Fees Partials --}}
        @php($rcBorderColor='#1e40af') @php($rcBgLight='#eff6ff') @php($rcTitleColor='#1e40af') @php($rcLabelColor='#1e3a8a')
        @include('pdf.partials.rc-psychomotor')
        @include('pdf.partials.rc-school-fees')
        
        {{-- Teacher Remarks --}}
        @if($showTeacherRemarks)
        <div class="remarks">
            <div class="remarks-label">Class Teacher's Remarks</div>
            <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        {{-- Principal Remarks --}}
        @if($showPrincipalRemarks)
        <div class="remarks">
            <div class="remarks-label">Principal's Endorsement</div>
            <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        {{-- Resumption Info --}}
        @if($showNextTermDate)
        <div class="next-bar">Next Term Resumption: <strong>{{ $nextTermDate ?? 'To be announced' }}</strong></div>
        @endif
        
        {{-- Signatures block --}}
        @if($showSignatures)
        <div class="sigs">
            <div class="sig">
                @if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher']))
                    <img src="{{ $signatureImages['teacher'] }}" alt="Teacher Signature" class="sig-img" />
                    <div class="sig-line has-img">Class Teacher</div>
                @else
                    <div class="sig-line">Class Teacher</div>
                @endif
                <div class="sig-sub">Signature & Date</div>
            </div>
            <div class="sig">
                @if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal']))
                    <img src="{{ $signatureImages['principal'] }}" alt="Principal Signature" class="sig-img" />
                    <div class="sig-line has-img">Principal</div>
                @else
                    <div class="sig-line">Principal</div>
                @endif
                <div class="sig-sub">Signature & Stamp</div>
            </div>
            <div class="sig">
                <div class="sig-line">Parent/Guardian</div>
                <div class="sig-sub">Signature & Date</div>
            </div>
        </div>
        @endif
        
        <div class="footer">Official School Document • {{ $schoolName }} • Powered by MyAcademy SMS</div>
    </div>
</div>
</body>
</html>
