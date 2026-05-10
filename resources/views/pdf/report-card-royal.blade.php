<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Serif, Georgia, serif; font-size: 9px; color: #1f2937; }

        /* ROYAL: Ornate borders, gold/purple, luxury certificate-style */
        .page { border: 8px double #7c3aed; padding: 12px; background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); position: relative; }
        .page::before { content: ''; position: absolute; top: 16px; left: 16px; right: 16px; bottom: 16px; border: 2px solid #d97706; pointer-events: none; }
        
        .ornament-top { text-align: center; font-size: 24px; color: #d97706; margin-bottom: 8px; }
        
        .header { text-align: center; margin-bottom: 10px; position: relative; z-index: 1; }
        .crest { width: 50px; height: 50px; background: linear-gradient(135deg, #7c3aed, #d97706); border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: 900; border: 3px solid #d97706; }
        .school-name { font-size: 18px; font-weight: 900; color: #7c3aed; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 4px; }
        .royal-badge { display: inline-block; background: linear-gradient(135deg, #7c3aed, #d97706); color: white; padding: 5px 18px; border-radius: 20px; font-size: 9px; font-weight: 800; letter-spacing: 2px; box-shadow: 0 2px 6px rgba(124,58,237,0.3); }
        
        .session-ribbon { background: white; border: 2px solid #d97706; border-radius: 8px; padding: 6px; text-align: center; margin-bottom: 10px; font-size: 8px; font-weight: 700; color: #7c3aed; }
        
        .student-frame { background: white; border: 3px double #7c3aed; border-radius: 10px; padding: 10px; margin-bottom: 10px; position: relative; }
        .student-frame::before { content: '♔'; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #faf5ff; padding: 0 8px; font-size: 16px; color: #d97706; }
        .student-grid { display: table; width: 100%; }
        .student-col { display: table-cell; width: 50%; padding: 4px; }
        .student-item { margin-bottom: 5px; border-bottom: 1px dotted #d97706; padding-bottom: 3px; }
        .student-label { font-size: 7px; color: #7c3aed; font-weight: 800; text-transform: uppercase; font-style: italic; }
        .student-value { font-size: 10px; color: #1f2937; font-weight: 700; }
        
        .stats-royal { display: table; width: 100%; margin-bottom: 10px; }
        .stat-royal { display: table-cell; width: 16.66%; padding: 2px; }
        .stat-box { background: linear-gradient(135deg, #7c3aed, #6b21a8); border: 2px solid #d97706; border-radius: 8px; text-align: center; padding: 8px 4px; }
        .stat-label { font-size: 6px; color: #fbbf24; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .stat-value { font-size: 14px; color: white; font-weight: 900; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; background: white; border: 2px solid #7c3aed; }
        th { background: linear-gradient(135deg, #7c3aed, #6b21a8); color: #fbbf24; padding: 6px 3px; font-size: 7px; font-weight: 800; text-transform: uppercase; border: 1px solid #6b21a8; }
        td { padding: 5px 3px; font-size: 8px; border: 1px solid #e9d5ff; text-align: center; }
        tr:nth-child(even) td { background: #faf5ff; }
        .subj { text-align: left; font-weight: 700; color: #7c3aed; padding-left: 6px; font-style: italic; }
        
        .grade-royal { background: white; border: 2px solid #d97706; border-radius: 6px; padding: 6px; margin-bottom: 10px; text-align: center; font-size: 7px; color: #6b7280; }
        .grade-royal strong { color: #7c3aed; font-size: 9px; margin: 0 4px; }
        
        .remarks-royal { background: white; border: 2px solid #7c3aed; border-radius: 8px; padding: 8px; margin-bottom: 8px; position: relative; }
        .remarks-royal::before { content: '✦'; position: absolute; top: -8px; left: 10px; background: #faf5ff; padding: 0 6px; color: #d97706; font-size: 12px; }
        .remarks-title { font-size: 8px; color: #7c3aed; font-weight: 800; text-transform: uppercase; font-style: italic; margin-bottom: 3px; }
        .remarks-text { font-size: 8px; color: #4b5563; min-height: 20px; }
        
        .next-royal { background: linear-gradient(135deg, #7c3aed, #d97706); color: white; text-align: center; padding: 6px; border-radius: 8px; font-size: 9px; font-weight: 800; margin-bottom: 8px; border: 2px solid #fbbf24; }
        
        .sigs { display: table; width: 100%; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; }
        .sig-line { border-top: 2px solid #7c3aed; margin-top: 25px; padding-top: 3px; font-size: 8px; font-weight: 700; color: #7c3aed; font-style: italic; }
        
        .ornament-bottom { text-align: center; font-size: 20px; color: #d97706; margin-top: 6px; }
        
        .footer { text-align: center; font-size: 6px; color: #9ca3af; margin-top: 6px; font-style: italic; }
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
        <div class="ornament-top">❖ ❖ ❖</div>
        
        <div class="header">
            <div class="crest">♔</div>
            <div class="school-name">{{ $schoolName }}</div>
            <div class="royal-badge">ROYAL REPORT CARD</div>
        </div>
        
        <div class="session-ribbon">
            Academic Session {{ $session }} • Term {{ $term }} • Issued {{ now()->format('F d, Y') }}
        </div>
        
        <div class="student-frame">
            
                @php($siBorderColor = '#7c3aed')
                @php($siBgColor = '#f5f3ff')
                @php($siLabelColor = '#5b21b6')
                @php($siValueColor = '#1f2937')
                @php($siDotColor = '#c4b5fd')
                @include('pdf.partials.rc-student-info')

        </div>
        
        <div class="stats-royal">
            <div class="stat-royal">
                <div class="stat-box">
                    <div class="stat-label">Total</div>
                    <div class="stat-value">{{ $grandTotal }}</div>
                </div>
            </div>
            <div class="stat-royal">
                <div class="stat-box">
                    <div class="stat-label">Average</div>
                    <div class="stat-value">{{ number_format($average, 1) }}%</div>
                </div>
            </div>
            @if($showPosition)
            <div class="stat-royal">
                <div class="stat-box">
                    <div class="stat-label">Position</div>
                    <div class="stat-value">{{ $position }}</div>
                </div>
            </div>
            @endif
            @if($showClassAverage)
            <div class="stat-royal">
                <div class="stat-box">
                    <div class="stat-label">Class Avg</div>
                    <div class="stat-value">{{ number_format($classAverage, 1) }}%</div>
                </div>
            </div>
            @endif
            <div class="stat-royal">
                <div class="stat-box">
                    <div class="stat-label">Highest</div>
                    <div class="stat-value">{{ number_format($highestAverage ?? 0, 1) }}%</div>
                </div>
            </div>
            <div class="stat-royal">
                <div class="stat-box">
                    <div class="stat-label">Lowest</div>
                    <div class="stat-value">{{ number_format($lowestAverage ?? 0, 1) }}%</div>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 35%; text-align: left; padding-left: 6px;">Subject</th>
                    <th style="width: 11%;">CA1</th>
                    <th style="width: 11%;">CA2</th>
                    <th style="width: 11%;">Exam</th>
                    <th style="width: 11%;">Total</th>
                    <th style="width: 10%;">Grade</th>
                    <th style="width: 11%;">Avg</th>
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
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        @if($showGradingKey)
        <div class="grade-royal">
            <strong>A:</strong> 70-100 | <strong>B:</strong> 60-69 | <strong>C:</strong> 50-59 | <strong>D:</strong> 40-49 | <strong>F:</strong> 0-39
        </div>
        @endif
        
        @if($showTeacherRemarks)
        <div class="remarks-royal">
            <div class="remarks-title">Class Teacher's Remarks</div>
            <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        @if($showPrincipalRemarks)
        <div class="remarks-royal">
            <div class="remarks-title">Principal's Remarks</div>
            <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        @php($rcBorderColor='#7c3aed') @php($rcBgLight='#faf5ff') @php($rcTitleColor='#6b21a8') @php($rcLabelColor='#4c1d95')
        @include('pdf.partials.rc-psychomotor')
        @include('pdf.partials.rc-school-fees')
        
        @if($showNextTermDate)
        <div class="next-royal">♔ Next Term Begins: {{ $nextTermDate ?? 'To be announced' }} ♔</div>
        @endif
        
        @if($showSignatures)
        <div class="sigs">
            <div class="sig">
                @if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher']))
                            <img src="{{ $signatureImages['teacher'] }}" alt="Teacher Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        @endif
                        <div class="sig-line">Class Teacher</div>
            </div>
            <div class="sig">
                @if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal']))
                            <img src="{{ $signatureImages['principal'] }}" alt="Principal Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        @endif
                        <div class="sig-line">Principal</div>
            </div>
            <div class="sig">
                <div class="sig-line">Parent/Guardian</div>
            </div>
        </div>
        @endif
        
        <div class="ornament-bottom">❖ ❖ ❖</div>
        
        <div class="footer">{{ $schoolName }} • Excellence in Education</div>
    </div>
</body>
</html>
