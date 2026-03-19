<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1e293b; }

        /* PROFESSIONAL: Sidebar layout, corporate blue, clean lines */
        .page { display: table; width: 100%; height: 100%; }
        
        .sidebar { display: table-cell; width: 180px; background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%); color: white; padding: 20px 12px; vertical-align: top; }
        .sidebar-logo { width: 60px; height: 60px; background: white; border-radius: 8px; margin: 0 auto 12px; padding: 4px; }
        .sidebar-school { font-size: 12px; font-weight: 900; text-align: center; margin-bottom: 16px; letter-spacing: 1px; }
        .sidebar-section { margin-bottom: 14px; }
        .sidebar-label { font-size: 7px; font-weight: 700; text-transform: uppercase; opacity: 0.7; margin-bottom: 3px; }
        .sidebar-value { font-size: 10px; font-weight: 700; }
        .sidebar-divider { height: 1px; background: rgba(255,255,255,0.2); margin: 12px 0; }
        .sidebar-stat { background: rgba(255,255,255,0.1); border-radius: 6px; padding: 8px; margin-bottom: 6px; text-align: center; }
        .sidebar-stat-label { font-size: 6px; font-weight: 700; text-transform: uppercase; opacity: 0.8; margin-bottom: 2px; }
        .sidebar-stat-value { font-size: 16px; font-weight: 900; }
        
        .main { display: table-cell; vertical-align: top; padding: 20px; background: #f8fafc; }
        
        .header-bar { background: white; border-left: 4px solid #1e40af; padding: 12px 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header-title { font-size: 18px; font-weight: 900; color: #1e40af; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .header-meta { font-size: 8px; color: #64748b; font-weight: 600; }
        
        .info-grid { display: table; width: 100%; background: white; border-radius: 8px; padding: 12px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 4px 8px; border-bottom: 1px solid #e2e8f0; }
        .info-cell:first-child { width: 30%; font-size: 7px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .info-cell:last-child { font-size: 9px; font-weight: 700; color: #1e293b; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th { background: #1e40af; color: white; padding: 8px 4px; font-size: 7px; font-weight: 800; text-transform: uppercase; text-align: center; }
        td { padding: 6px 4px; font-size: 8px; border-bottom: 1px solid #e2e8f0; text-align: center; }
        tr:hover td { background: #f1f5f9; }
        .subj { text-align: left; font-weight: 700; color: #1e40af; padding-left: 8px; }
        
        .grade-bar { background: white; border-radius: 6px; padding: 8px; margin-bottom: 12px; text-align: center; font-size: 7px; color: #64748b; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .grade-bar strong { color: #1e40af; font-size: 9px; margin: 0 6px; }
        
        .remarks { background: white; border-radius: 8px; padding: 10px; margin-bottom: 10px; border-left: 3px solid #1e40af; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .remarks-label { font-size: 8px; color: #1e40af; font-weight: 800; text-transform: uppercase; margin-bottom: 4px; }
        .remarks-text { font-size: 8px; color: #475569; min-height: 22px; }
        
        .next-bar { background: #1e40af; color: white; text-align: center; padding: 8px; border-radius: 6px; font-size: 9px; font-weight: 800; margin-bottom: 10px; }
        
        .sigs { display: table; width: 100%; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 6px; }
        .sig-line { border-top: 2px solid #1e40af; margin-top: 28px; padding-top: 4px; font-size: 8px; font-weight: 700; color: #1e40af; }
        
        .footer { text-align: center; font-size: 7px; color: #94a3b8; margin-top: 10px; }
    </style>
</head>
<body>
    @php $schoolName = config('myacademy.school_name', 'MyAcademy'); @endphp
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
        <div class="sidebar">
            <div class="sidebar-school">{{ $schoolName }}</div>
            
            <div class="sidebar-section">
                <div class="sidebar-label">Student</div>
                <div class="sidebar-value">{{ $student->full_name }}</div>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-label">Admission No</div>
                <div class="sidebar-value">{{ $student->admission_number }}</div>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-label">Class</div>
                <div class="sidebar-value">{{ $student->schoolClass?->name }}</div>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-label">Section</div>
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
            
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Position</div>
                <div class="sidebar-stat-value">{{ $position }}</div>
            </div>
            
            <div class="sidebar-stat">
                <div class="sidebar-stat-label">Class Avg</div>
                <div class="sidebar-stat-value">{{ number_format($classAverage, 1) }}%</div>
            </div>
            
            <div class="sidebar-divider"></div>
            
            <div class="sidebar-section">
                <div class="sidebar-label">Highest in Class</div>
                <div class="sidebar-value">{{ number_format($highestAverage ?? 0, 1) }}%</div>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-label">Lowest in Class</div>
                <div class="sidebar-value">{{ number_format($lowestAverage ?? 0, 1) }}%</div>
            </div>
        </div>
        
        <div class="main">
            <div class="header-bar">
                <div class="header-title">Academic Report Card</div>
                <div class="header-meta">Session: {{ $session }} | Term: {{ $term }} | Generated: {{ now()->format('F d, Y') }}</div>
            </div>
            
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-cell">Gender</div>
                    <div class="info-cell">{{ $student->gender ?? 'N/A' }}</div>
                    <div class="info-cell">Date of Birth</div>
                    <div class="info-cell">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('M d, Y') : 'N/A' }}</div>
                    <div class="info-cell">Students in Class</div>
                    <div class="info-cell">{{ $totalStudents ?? 'N/A' }}</div>
                </div>
                @if($showAttendance)
                <div class="info-row">
                    <div class="info-cell">Times Opened</div>
                    <div class="info-cell">{{ $timesOpened ?? '—' }}</div>
                    <div class="info-cell">Times Present</div>
                    <div class="info-cell">{{ $timesPresent ?? '—' }}</div>
                    <div class="info-cell">Times Absent</div>
                    <div class="info-cell">{{ $timesAbsent ?? '—' }}</div>
                </div>
                @endif
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%; text-align: left; padding-left: 8px;">Subject</th>
                        <th style="width: 10%;">CA1</th>
                        <th style="width: 10%;">CA2</th>
                        <th style="width: 10%;">Exam</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 10%;">Grade</th>
                        <th style="width: 10%;">Class Avg</th>
                        <th style="width: 10%;">Position</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="subj">{{ $row['subject']->name }}</td>
                            <td>{{ $row['ca1'] ?? '—' }}</td>
                            <td>{{ $row['ca2'] ?? '—' }}</td>
                            <td>{{ $row['exam'] ?? '—' }}</td>
                            <td><strong>{{ $row['total'] ?? '—' }}</strong></td>
                            <td><strong>{{ $row['grade'] ?? '—' }}</strong></td>
                            <td>{{ $row['class_avg'] ?? '—' }}</td>
                            <td>{{ $row['position'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="grade-bar">
                <strong>A:</strong> 70-100 (Excellent) | <strong>B:</strong> 60-69 (Very Good) | <strong>C:</strong> 50-59 (Good) | <strong>D:</strong> 40-49 (Pass) | <strong>F:</strong> 0-39 (Fail)
            </div>
            
            <div class="remarks">
                <div class="remarks-label">Class Teacher's Remarks</div>
                <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
            </div>
            
            <div class="remarks">
                <div class="remarks-label">Principal's Remarks</div>
                <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
            </div>
            
            <div class="next-bar">Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}</div>
            
            <div class="sigs">
                <div class="sig">
                    @if($showSignatures && ($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher']))
                            <img src="{{ $signatureImages['teacher'] }}" alt="Teacher Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        @endif
                        <div class="sig-line">Class Teacher</div>
                </div>
                <div class="sig">
                    @if($showSignatures && ($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal']))
                            <img src="{{ $signatureImages['principal'] }}" alt="Principal Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        @endif
                        <div class="sig-line">Principal</div>
                </div>
                <div class="sig">
                    <div class="sig-line">Parent/Guardian</div>
                </div>
            </div>
            
            <div class="footer">{{ $schoolName }} • Powered by MyAcademy SMS</div>
        </div>
    </div>
</body>
</html>
