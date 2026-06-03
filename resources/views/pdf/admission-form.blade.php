<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Admission Form - {{ $student->admission_number }}</title>
    <style>
        @page { margin: 20mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; }
        .school-name { font-size: 24pt; font-weight: bold; color: #1f2937; margin-bottom: 5px; }
        .form-title { font-size: 16pt; font-weight: bold; color: #f59e0b; margin-top: 10px; }
        .section { margin-bottom: 25px; }
        .section-title { background: #f59e0b; color: white; padding: 8px 12px; font-weight: bold; font-size: 12pt; margin-bottom: 15px; }
        .field { margin-bottom: 12px; }
        .field-label { font-weight: bold; color: #4b5563; display: inline-block; width: 180px; }
        .field-value { color: #1f2937; display: inline-block; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #e5e7eb; }
        .signature-box { display: inline-block; width: 45%; text-align: center; margin-top: 50px; }
        .signature-line { border-top: 2px solid #1f2937; padding-top: 8px; margin-top: 60px; font-weight: bold; }
        .page-break { page-break-before: always; }
        .timestamp-page { text-align: center; padding-top: 80px; }
        .timestamp-box { border: 2px solid #f59e0b; border-radius: 8px; padding: 24px 48px; background: #fffbeb; display: inline-block; }
        .timestamp-label { font-size: 9pt; color: #92400e; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; margin-bottom: 8px; }
        .timestamp-value { font-size: 14pt; font-weight: bold; color: #1f2937; }
        .stamp-area { margin-top: 80px; border-top: 2px solid #e5e7eb; padding-top: 20px; display: inline-block; width: 260px; text-align: center; }
        .stamp-box { height: 80px; border: 2px dashed #d1d5db; border-radius: 6px; color: #9ca3af; font-size: 9pt; line-height: 80px; margin-bottom: 10px; }
    </style>
</head>
<body>
    @php
        $studentName      = ucwords(strtolower($student->full_name ?? ''));
        $guardianName     = ucwords(strtolower($student->guardian_name ?? ''));
        $guardianAddress  = ucwords(strtolower($student->guardian_address ?? ''));
        $emergencyContact = ucwords(strtolower($student->emergency_contact ?? ''));
    @endphp

    {{-- PAGE 1: Main Content --}}
    <div class="header">
        <div class="school-name">{{ config('myacademy.school_name', 'MyAcademy') }}</div>
        <div style="color: #6b7280; font-size: 10pt;">{{ config('myacademy.school_tagline', 'Excellence in Education') }}</div>
        <div class="form-title">STUDENT ADMISSION FORM</div>
    </div>

    <div class="section">
        <div class="section-title">STUDENT INFORMATION</div>
        <div class="field">
            <span class="field-label">Admission Number:</span>
            <span class="field-value">{{ $student->admission_number }}</span>
        </div>
        <div class="field">
            <span class="field-label">Full Name:</span>
            <span class="field-value">{{ $studentName }}</span>
        </div>
        <div class="field">
            <span class="field-label">Gender:</span>
            <span class="field-value">{{ $student->gender ?? '—' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Date of Birth:</span>
            <span class="field-value">{{ $student->dob?->format('F j, Y') ?: '—' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Blood Group:</span>
            <span class="field-value">{{ $student->blood_group ?: '—' }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">ACADEMIC INFORMATION</div>
        <div class="field">
            <span class="field-label">Class:</span>
            <span class="field-value">{{ $student->schoolClass?->name ?: '—' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Section:</span>
            <span class="field-value">{{ $student->section?->name ?: '—' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Status:</span>
            <span class="field-value">{{ $student->status }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">GUARDIAN INFORMATION</div>
        <div class="field">
            <span class="field-label">Guardian Name:</span>
            <span class="field-value">{{ $guardianName ?: '—' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Guardian Phone:</span>
            <span class="field-value">{{ $student->guardian_phone ?: '—' }}</span>
        </div>
        <div class="field">
            <span class="field-label">Guardian Address:</span>
            <span class="field-value">{{ $guardianAddress ?: '—' }}</span>
        </div>
        @if($emergencyContact)
        <div class="field">
            <span class="field-label">Emergency Contact:</span>
            <span class="field-value">{{ $emergencyContact }}</span>
        </div>
        @endif
    </div>

    <div class="footer">
        <div class="signature-box" style="float: left;">
            <div class="signature-line">Guardian Signature</div>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-line">School Administrator</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    {{-- PAGE 2: Timestamp & Official Stamp --}}
    <div class="page-break"></div>

    <div class="header">
        <div class="school-name">{{ config('myacademy.school_name', 'MyAcademy') }}</div>
        <div class="form-title">ADMISSION FORM — OFFICE COPY</div>
    </div>

    <div class="timestamp-page">
        <div class="timestamp-box">
            <div class="timestamp-label">Date &amp; Time Generated</div>
            <div class="timestamp-value">{{ now()->format('l, F j, Y') }}</div>
            <div class="timestamp-value" style="font-size:11pt; margin-top:4px; color:#6b7280;">{{ now()->format('g:i A') }}</div>
        </div>

        <div style="margin-top: 30px; font-size: 10pt; color: #4b5563; line-height: 1.8;">
            <strong>Student:</strong> {{ $studentName }} &nbsp;&bull;&nbsp;
            <strong>Adm. No:</strong> {{ $student->admission_number }}<br>
            <strong>Class:</strong> {{ $student->schoolClass?->name ?: '—' }}
        </div>

        <div style="margin-top: 16px; font-size: 8pt; color: #9ca3af; font-style: italic;">
            This document was generated electronically &bull; {{ config('myacademy.school_name', 'MyAcademy') }} &bull; Powered by MyAcademy SMS
        </div>

        <div class="stamp-area">
            <div class="stamp-box">OFFICIAL STAMP</div>
            <div style="border-top: 2px solid #374151; padding-top: 8px; font-size: 9pt; font-weight: bold; color: #374151;">
                Authorized Signature
            </div>
        </div>
    </div>
</body>
</html>
