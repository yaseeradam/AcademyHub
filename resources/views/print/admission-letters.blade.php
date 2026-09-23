<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Admission Letters - {{ $schoolName }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a;
            background: white;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .page {
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 12mm 15mm;
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .page:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
        }
        .watermark img {
            width: 440px;
            height: 440px;
            object-fit: contain;
        }
        .content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            height: 100%;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 8px;
            width: 100%;
        }
        .logo-circle {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }
        .logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .school-info {
            flex: 1;
        }
        .school-info h1 {
            font-size: 34px;
            font-weight: 900;
            color: #1B3A6B;
            text-transform: uppercase;
            line-height: 1.12;
            letter-spacing: -0.5px;
        }
        .motto-banner {
            display: inline-block;
            background: #D4851F;
            color: white;
            padding: 4px 18px;
            font-size: 13px;
            font-weight: 700;
            font-style: italic;
            border-radius: 3px;
            margin-top: 6px;
        }
        .contact-row {
            margin-top: 6px;
            font-size: 12px;
            color: #333;
        }
        .contact-row div {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 3px;
        }
        .divider {
            margin-top: 8px;
            margin-bottom: 10px;
        }
        .divider .line1 {
            height: 4px;
            background: #1B3A6B;
            border-radius: 1px;
        }
        .divider .line2 {
            height: 4px;
            background: #D4851F;
            border-radius: 1px;
            margin-top: 3px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .meta-info {
            font-size: 12.5px;
        }
        .meta-info div {
            margin-bottom: 4px;
        }
        .meta-info .label {
            color: #555;
            font-weight: 600;
        }
        .meta-info .value {
            font-weight: 800;
            color: #111;
            border-bottom: 1px solid #999;
            padding-bottom: 2px;
            padding-left: 4px;
            padding-right: 4px;
            text-transform: uppercase;
        }
        .right-col {
            text-align: right;
        }
        .date-line {
            font-size: 12.5px;
            font-weight: 700;
            color: #333;
            margin-bottom: 6px;
        }
        .date-line .value {
            border-bottom: 1px solid #999;
            padding-bottom: 2px;
            padding-left: 6px;
            font-weight: 600;
        }
        .passport-box {
            width: 90px;
            height: 105px;
            border: 2px solid #333;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            background: #fafafa;
        }
        .passport-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .passport-placeholder {
            font-size: 9px;
            color: #999;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
        }
        .subject {
            text-align: center;
            font-size: 15px;
            font-weight: 800;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 8px 0 12px;
        }
        .letter-body {
            font-size: 12.5px;
            line-height: 1.6;
            text-align: justify;
            color: #222;
        }
        .letter-body p {
            margin-bottom: 8px;
        }
        .letter-body .greeting {
            font-weight: 700;
            margin-bottom: 8px;
        }
        .letter-body strong {
            font-weight: 700;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 10px 0;
        }
        .summary-table td {
            padding: 6px 10px;
            border: 1px solid #888;
        }
        .summary-table .label-cell {
            font-weight: 700;
            color: #333;
            background: #f5f5f5;
            width: 42%;
        }
        .summary-table .value-cell {
            font-weight: 800;
            color: #111;
        }
        .signoff {
            margin-top: 16px;
            font-size: 12.5px;
        }
        .signoff .yours {
            font-weight: 600;
            margin-bottom: 24px;
        }
        .signoff .sig-line {
            width: 180px;
            border-bottom: 2px solid #333;
            margin-bottom: 3px;
        }
        .signoff .name {
            font-weight: 800;
            font-size: 13.5px;
        }
        .signoff .title {
            font-weight: 600;
            color: #444;
            font-size: 11.5px;
        }
        .signoff .school {
            font-weight: 700;
            font-size: 11.5px;
        }
    </style>
</head>
<body>
    @foreach($students as $student)
        @php
            $className = $student->schoolClass?->name ?? 'General Class';
            $sectionName = $student->section?->name;
            $classArm = $sectionName ? "{$className} {$sectionName}" : $className;
        @endphp
        <div class="page">
            <div class="watermark">
                <img src="{{ $logoUrl }}" alt="" />
            </div>
            <div class="content">
                <!-- Header -->
                <div class="header">
                    <div class="logo-circle">
                        <img src="{{ $logoUrl }}" alt="Logo" />
                    </div>
                    <div class="school-info">
                        <h1>{!! nl2br(e($schoolName)) !!}</h1>
                        <div class="motto-banner">Motto: <em>{{ $schoolMotto }}</em></div>
                    </div>
                </div>

                <!-- Contact with Vector SVG Icons -->
                <div class="contact-row">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ $schoolAddress }}</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ $schoolPhone }}</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1B3A6B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>{{ $schoolEmail }}</span>
                    </div>
                </div>

                <!-- Divider -->
                <div class="divider">
                    <div class="line1"></div>
                    <div class="line2"></div>
                </div>

                <!-- Info Row -->
                <div class="info-row">
                    <div class="meta-info">
                        <div><span class="label">Student Name: </span><span class="value">{{ $student->full_name }}</span></div>
                        <div><span class="label">Admission Number: </span><span class="value">{{ $student->admission_number }}</span></div>
                        <div><span class="label">Class: </span><span class="value">{{ $classArm }}</span></div>
                    </div>
                    <div class="right-col">
                        <div class="date-line">Date: <span class="value">{{ $admissionDate }}</span></div>
                        <div class="passport-box">
                            @if($student->passport_photo)
                                <img src="{{ $student->passport_photo_url }}" alt="Passport" />
                            @else
                                <div class="passport-placeholder">Passport<br>Photograph</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Subject -->
                <div class="subject">SUBJECT: ADMISSION LETTER</div>

                <!-- Body -->
                <div class="letter-body">
                    <p class="greeting">Dear Parent/Guardian,</p>
                    <p>We are pleased to inform you that your child has been offered admission into <strong>{{ $schoolName }}</strong> into <strong>{{ strtoupper($classArm) }}</strong> for the <strong>{{ $academicSession }}</strong> Academic Session.</p>
                    <p>The admission is offered based on the assessment and admission requirements of the school. We are delighted to welcome your child into our learning community and look forward to supporting his/her academic, moral, and personal development.</p>
                    <p>Please complete the registration process and settle the applicable school fees and other required charges on or before the stated deadline. Admission is subject to compliance with the school's rules, regulations, and code of conduct.</p>
                    <p>We kindly request that the parent/guardian report to the school for final registration and submission of the required documents.</p>
                    <p>We congratulate you and your child on this opportunity and look forward to a successful and rewarding academic journey together.</p>
                </div>

                <!-- Summary Table -->
                <table class="summary-table">
                    <tr><td class="label-cell">Student Name</td><td class="value-cell">{{ strtoupper($student->full_name) }}</td></tr>
                    <tr><td class="label-cell">Class / Level / Arm</td><td class="value-cell">{{ strtoupper($classArm) }}</td></tr>
                    <tr><td class="label-cell">Academic Session</td><td class="value-cell">{{ $academicSession }}</td></tr>
                    <tr><td class="label-cell">Resumption Date</td><td class="value-cell">{{ $resumptionDate }}</td></tr>
                </table>

                <!-- Sign-off -->
                <div class="signoff">
                    <p class="yours">Yours faithfully,</p>
                    <div class="sig-line"></div>
                    <p class="name">{{ $signatoryName }}</p>
                    <p class="title">{{ $signatoryTitle }}</p>
                    <p class="school">{{ $schoolName }}</p>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
