<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 12mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #064e3b; }

        /* FRESH: Nature-inspired, green/teal, organic shapes, leaf motifs */
        .page { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #d1fae5 100%); padding: 14px; border-radius: 16px; border: 4px solid #059669; }
        
        .leaf-corner { position: absolute; font-size: 32px; color: #10b981; opacity: 0.3; }
        .leaf-tl { top: 8px; left: 8px; }
        .leaf-tr { top: 8px; right: 8px; }
        .leaf-bl { bottom: 8px; left: 8px; }
        .leaf-br { bottom: 8px; right: 8px; }
        
        .header { text-align: center; margin-bottom: 10px; position: relative; }
        .eco-badge { width: 50px; height: 50px; background: linear-gradient(135deg, #059669, #10b981); border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; border: 3px solid white; box-shadow: 0 2px 8px rgba(5,150,105,0.3); }
        .school-name { font-size: 18px; font-weight: 900; color: #047857; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .fresh-badge { display: inline-block; background: linear-gradient(135deg, #059669, #0d9488); color: white; padding: 5px 16px; border-radius: 20px; font-size: 9px; font-weight: 800; letter-spacing: 1px; }
        
        .wave-divider { height: 20px; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 20"><path fill="%23059669" d="M0,10 Q300,0 600,10 T1200,10 L1200,20 L0,20 Z"/></svg>'); background-size: cover; margin: 8px 0; }
        
        .info-cards { display: table; width: 100%; margin-bottom: 10px; }
        .info-card { display: table-cell; width: 50%; padding: 3px; }
        .info-inner { background: white; border-radius: 10px; padding: 8px; border: 2px solid #6ee7b7; }
        .info-item { margin-bottom: 4px; }
        .info-label { font-size: 7px; color: #059669; font-weight: 800; text-transform: uppercase; }
        .info-value { font-size: 9px; color: #064e3b; font-weight: 700; }
        
        .stats-fresh { display: table; width: 100%; margin-bottom: 10px; }
        .stat-fresh { display: table-cell; width: 16.66%; padding: 2px; }
        .stat-leaf { background: white; border: 2px solid #6ee7b7; border-radius: 12px; text-align: center; padding: 8px 4px; position: relative; }
        .stat-leaf::before { content: '🌿'; position: absolute; top: -10px; right: -10px; font-size: 16px; }
        .stat-label { font-size: 6px; color: #059669; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .stat-value { font-size: 14px; color: #047857; font-weight: 900; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; background: white; border-radius: 10px; overflow: hidden; border: 2px solid #6ee7b7; }
        th { background: linear-gradient(135deg, #059669, #0d9488); color: white; padding: 6px 3px; font-size: 7px; font-weight: 800; text-transform: uppercase; }
        td { padding: 5px 3px; font-size: 8px; border-bottom: 1px solid #d1fae5; text-align: center; }
        tr:nth-child(even) td { background: #f0fdf4; }
        .subj { text-align: left; font-weight: 700; color: #047857; padding-left: 6px; }
        
        .grade-fresh { background: white; border: 2px solid #6ee7b7; border-radius: 8px; padding: 6px; margin-bottom: 10px; text-align: center; font-size: 7px; color: #6b7280; }
        .grade-fresh strong { color: #059669; font-size: 9px; margin: 0 4px; }
        
        .remarks-fresh { background: white; border: 2px solid #6ee7b7; border-radius: 10px; padding: 8px; margin-bottom: 8px; position: relative; }
        .remarks-fresh::before { content: '🍃'; position: absolute; top: -8px; left: 10px; background: #f0fdf4; padding: 0 6px; font-size: 14px; }
        .remarks-title { font-size: 8px; color: #059669; font-weight: 800; text-transform: uppercase; margin-bottom: 3px; }
        .remarks-text { font-size: 8px; color: #4b5563; min-height: 20px; }
        
        .next-fresh { background: linear-gradient(135deg, #059669, #0d9488); color: white; text-align: center; padding: 6px; border-radius: 10px; font-size: 9px; font-weight: 800; margin-bottom: 8px; }
        
        .sigs { display: table; width: 100%; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; }
        .sig-line { border-top: 2px solid #059669; margin-top: 25px; padding-top: 3px; font-size: 8px; font-weight: 700; color: #047857; }
        
        .footer { text-align: center; font-size: 6px; color: #9ca3af; margin-top: 6px; }
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
    
    <div class="page" style="position: relative;">
        <div class="leaf-corner leaf-tl">🌿</div>
        <div class="leaf-corner leaf-tr">🌿</div>
        <div class="leaf-corner leaf-bl">🌿</div>
        <div class="leaf-corner leaf-br">🌿</div>
        
        <div class="header">
            <div class="eco-badge">🌱</div>
            <div class="school-name">{{ $schoolName }}</div>
            <div class="fresh-badge">STUDENT REPORT CARD</div>
        </div>
        
        <div style="text-align: center; font-size: 7px; color: #059669; font-weight: 700; margin-bottom: 8px;">
            Session {{ $session }} • Term {{ $term }} • {{ now()->format('F d, Y') }}
        </div>
        
        @php($siBorderColor = '#10b981') @php($siBgColor = '#ecfdf5') @php($siLabelColor = '#065f46') @php($siValueColor = '#1f2937') @php($siDotColor = '#6ee7b7')
        @include('pdf.partials.rc-student-info')
        
        <div class="stats-fresh">
            <div class="stat-fresh">
                <div class="stat-leaf">
                    <div class="stat-label">Total</div>
                    <div class="stat-value">{{ $grandTotal }}</div>
                </div>
            </div>
            <div class="stat-fresh">
                <div class="stat-leaf">
                    <div class="stat-label">Average</div>
                    <div class="stat-value">{{ number_format($average, 1) }}%</div>
                </div>
            </div>
            @if($showPosition)
            <div class="stat-fresh">
                <div class="stat-leaf">
                    <div class="stat-label">Position</div>
                    <div class="stat-value">{{ $position }}</div>
                </div>
            </div>
            @endif
            @if($showClassAverage)
            <div class="stat-fresh">
                <div class="stat-leaf">
                    <div class="stat-label">Class Avg</div>
                    <div class="stat-value">{{ number_format($classAverage, 1) }}%</div>
                </div>
            </div>
            @endif
            <div class="stat-fresh">
                <div class="stat-leaf">
                    <div class="stat-label">Highest</div>
                    <div class="stat-value">{{ number_format($highestAverage ?? 0, 1) }}%</div>
                </div>
            </div>
            <div class="stat-fresh">
                <div class="stat-leaf">
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
        <div class="grade-fresh">
            <strong>A:</strong> 70-100 | <strong>B:</strong> 60-69 | <strong>C:</strong> 50-59 | <strong>D:</strong> 40-49 | <strong>F:</strong> 0-39
        </div>
        @endif
        
        @if($showTeacherRemarks)
        <div class="remarks-fresh">
            <div class="remarks-title">Class Teacher's Remarks</div>
            <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        @if($showPrincipalRemarks)
        <div class="remarks-fresh">
            <div class="remarks-title">Principal's Remarks</div>
            <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        @php($rcBorderColor='#059669') @php($rcBgLight='#f0fdf4') @php($rcTitleColor='#047857') @php($rcLabelColor='#064e3b')
        @include('pdf.partials.rc-psychomotor')
        @include('pdf.partials.rc-school-fees')
        
        @if($showNextTermDate)
        <div class="next-fresh">🌿 Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}</div>
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
        
        <div class="footer">{{ $schoolName }} • Growing Excellence Together</div>
    </div>
</body>
</html>
