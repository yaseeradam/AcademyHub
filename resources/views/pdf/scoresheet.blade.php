<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scoresheet - {{ $class->name }} - {{ $subject->name }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4 landscape;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .header .address {
            font-size: 11px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .header .subtitle {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
        }
        
        .info-section {
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 11px;
        }
        
        .info-item {
            display: inline-block;
            padding: 6px 12px;
            background: #f5f5f5;
            border: 1px solid #ccc;
            border-radius: 3px;
            margin-right: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th {
            background: #333;
            color: white;
            padding: 8px 4px;
            border: 1px solid #000;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        
        td {
            border: 1px solid #333;
            padding: 6px 4px;
            text-align: center;
            min-height: 25px;
        }
        
        .student-name {
            text-align: left;
            font-weight: bold;
            padding-left: 8px;
        }
        
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .footer {
            margin-top: 30px;
        }
        
        .signature {
            display: inline-block;
            text-align: center;
            width: 200px;
            margin-right: 50px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        @if($schoolAddress)
            <div class="address">{{ $schoolAddress }}</div>
        @endif
        <div class="subtitle">SCORE SHEET</div>
    </div>

    <div class="info-section">
        <div class="info-item">Class: {{ $class->name }}</div>
        <div class="info-item">Subject: {{ $subject->name }}</div>
        <div class="info-item">Term: {{ $term }}</div>
        <div class="info-item">Session: {{ $session }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px;">S/N</th>
                <th style="width:180px;">STUDENT NAME</th>
                <th style="width:60px;">CA1<br>({{ $maxMarks['ca1'] }})</th>
                <th style="width:60px;">CA2<br>({{ $maxMarks['ca2'] }})</th>
                <th style="width:60px;">EXAM<br>({{ $maxMarks['exam'] }})</th>
                <th style="width:70px;">TOTAL<br>({{ $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam'] }})</th>
                <th style="width:50px;">GRADE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                @php
                    $score = $scores->get($student->id);
                    $ca1 = $score?->ca1 ?? 0;
                    $ca2 = $score?->ca2 ?? 0;
                    $exam = $score?->exam ?? 0;
                    $total = $ca1 + $ca2 + $exam;
                    $totalMax = $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam'];
                    $grade = \App\Models\Score::gradeForTotal($total, $totalMax);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="student-name">{{ $student->full_name }}</td>
                    <td>{{ $ca1 }}</td>
                    <td>{{ $ca2 }}</td>
                    <td>{{ $exam }}</td>
                    <td><strong>{{ $total }}</strong></td>
                    <td><strong>{{ $grade }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <div class="signature-line">Teacher's Signature</div>
        </div>
        <div class="signature">
            <div class="signature-line">Head Teacher's Signature</div>
        </div>
    </div>
</body>
</html>