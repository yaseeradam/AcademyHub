<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Report Card - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1f2937; background: #fff; }

        /* VIBRANT: Circular badges, colorful cards, playful layout */
        .page { background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 50%, #f5d0fe 100%); padding: 16px; border-radius: 12px; }
        
        .header { text-align: center; margin-bottom: 12px; }
        .school-name { font-size: 22px; font-weight: 900; color: #a855f7; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .report-badge { display: inline-block; background: linear-gradient(135deg, #ec4899, #a855f7); color: white; padding: 6px 20px; border-radius: 20px; font-size: 10px; font-weight: 800; letter-spacing: 1px; }
        
        .session-pills { text-align: center; margin-bottom: 12px; }
        .pill { display: inline-block; background: white; border: 2px solid #d946ef; color: #a855f7; padding: 4px 12px; border-radius: 15px; font-size: 8px; font-weight: 700; margin: 0 3px; }
        
        .student-card { background: white; border-radius: 12px; padding: 10px; margin-bottom: 10px; border: 3px solid #d946ef; }
        .student-grid { display: table; width: 100%; }
        .student-col { display: table-cell; width: 50%; padding: 4px; }
        .student-item { margin-bottom: 4px; }
        .student-label { font-size: 7px; color: #a855f7; font-weight: 800; text-transform: uppercase; }
        .student-value { font-size: 10px; color: #1f2937; font-weight: 700; }
        
        .circles { display: table; width: 100%; margin-bottom: 10px; }
        .circle-wrap { display: table-cell; width: 16.66%; text-align: center; padding: 2px; }
        .circle { width: 48px; height: 48px; border-radius: 50%; margin: 0 auto; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .circle.pink { background: linear-gradient(135deg, #ec4899, #db2777); }
        .circle.purple { background: linear-gradient(135deg, #a855f7, #9333ea); }
        .circle.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .circle.green { background: linear-gradient(135deg, #10b981, #059669); }
        .circle.orange { background: linear-gradient(135deg, #f97316, #ea580c); }
        .circle.yellow { background: linear-gradient(135deg, #eab308, #ca8a04); }
        .circle-label { font-size: 6px; color: white; font-weight: 700; text-transform: uppercase; }
        .circle-value { font-size: 11px; color: white; font-weight: 900; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; background: white; border-radius: 8px; overflow: hidden; }
        th { background: linear-gradient(135deg, #a855f7, #ec4899); color: white; padding: 6px 3px; font-size: 7px; font-weight: 800; text-transform: uppercase; }
        td { padding: 5px 3px; font-size: 8px; border-bottom: 1px solid #fae8ff; text-align: center; }
        tr:nth-child(even) td { background: #fdf4ff; }
        .subj { text-align: left; font-weight: 700; color: #a855f7; padding-left: 6px; }
        
        .grade-strip { background: white; border-radius: 8px; padding: 6px; margin-bottom: 10px; text-align: center; font-size: 7px; color: #6b7280; border: 2px solid #d946ef; }
        .grade-strip strong { color: #a855f7; font-size: 9px; margin: 0 4px; }
        
        .remarks-box { background: white; border-radius: 8px; padding: 8px; margin-bottom: 8px; border: 2px solid #d946ef; }
        .remarks-title { font-size: 8px; color: #a855f7; font-weight: 800; text-transform: uppercase; margin-bottom: 3px; }
        .remarks-text { font-size: 8px; color: #4b5563; min-height: 20px; }
        
        .next-term { background: linear-gradient(135deg, #ec4899, #a855f7); color: white; text-align: center; padding: 6px; border-radius: 8px; font-size: 9px; font-weight: 800; margin-bottom: 8px; }
        
        .sigs { display: table; width: 100%; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; }
        .sig-line { border-top: 2px solid #a855f7; margin-top: 25px; padding-top: 3px; font-size: 8px; font-weight: 700; color: #a855f7; }
        
        .footer { text-align: center; font-size: 6px; color: #9ca3af; margin-top: 8px; }
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
        <div class="header">
            <div class="school-name">{{ $schoolName }}</div>
            <div class="report-badge">STUDENT REPORT CARD</div>
        </div>
        
        <div class="session-pills">
            <span class="pill">📅 {{ $session }}</span>
            <span class="pill">📚 Term {{ $term }}</span>
            <span class="pill">📆 {{ now()->format('M d, Y') }}</span>
        </div>
        
        <div class="student-card">
            
                @php($siBorderColor = '#a855f7')
                @php($siBgColor = '#faf5ff')
                @php($siLabelColor = '#7e22ce')
                @php($siValueColor = '#1f2937')
                @php($siDotColor = '#d8b4fe')
                @include('pdf.partials.rc-student-info')

        </div>
        
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
        <div class="grade-strip">
            <strong>A:</strong> 70-100 | <strong>B:</strong> 60-69 | <strong>C:</strong> 50-59 | <strong>D:</strong> 40-49 | <strong>F:</strong> 0-39
        </div>
        @endif
        
        @if($showTeacherRemarks)
        <div class="remarks-box">
            <div class="remarks-title">👨‍🏫 Class Teacher's Remarks</div>
            <div class="remarks-text">{{ $teacherRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        @if($showPrincipalRemarks)
        <div class="remarks-box">
            <div class="remarks-title">👔 Principal's Remarks</div>
            <div class="remarks-text">{{ $principalRemarks ?? 'No remarks provided.' }}</div>
        </div>
        @endif
        
        @if($showNextTermDate)
        <div class="next-term">🗓️ Next Term Begins: {{ $nextTermDate ?? 'To be announced' }}</div>
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
        
        <div class="footer">Generated {{ now()->format('M d, Y') }} • {{ $schoolName }} • MyAcademy SMS</div>
    </div>
</body>
</html>
