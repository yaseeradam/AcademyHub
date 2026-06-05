@php
    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = config('academyhub.school_logo');
    $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;

    $accentColor = '#0ea5e9'; // Sky Blue
    $showLogo = (bool) config('academyhub.certificate_show_logo', true);
    $showWatermark = (bool) config('academyhub.certificate_show_watermark', false);
    $watermark = config('academyhub.certificate_watermark_image');
    $watermarkPath = ($watermark && $showWatermark) ? public_path('uploads/' . str_replace('\\', '/', $watermark)) : null;

    $sig1Label = config('academyhub.certificate_signature_label', 'Authorized Signature');
    $sig1Name = config('academyhub.certificate_signature_name');
    $sig1Image = config('academyhub.certificate_signature_image');
    $sig1ImagePath = $sig1Image ? public_path('uploads/' . str_replace('\\', '/', $sig1Image)) : null;

    $sig2Label = config('academyhub.certificate_signature2_label');
    $sig2Name = config('academyhub.certificate_signature2_name');
    $sig2Image = config('academyhub.certificate_signature2_image');
    $sig2ImagePath = $sig2Image ? public_path('uploads/' . str_replace('\\', '/', $sig2Image)) : null;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $certificate->title ?? 'Certificate' }}</title>
    <style>
        @page {
            margin: 28px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: #ffffff;
            padding: 8px;
        }

        .border-frame {
            position: absolute;
            top: 24px;
            left: 24px;
            right: 24px;
            bottom: 24px;
            border: 1px solid #e2e8f0;
        }

        /* Accent line at top */
        .accent-top {
            position: absolute;
            top: 0;
            left: 24px;
            right: 24px;
            height: 4px;
            background:
                {{ $accentColor }}
            ;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 400px;
            height: 400px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 50px 60px 30px;
        }

        .logo-section {
            margin-bottom: 20px;
        }

        .logo {
            width: 55px;
            height: 55px;
            object-fit: contain;
            border-radius: 8px;
            background: #f8fafc;
            padding: 6px;
        }

        .school-name {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .divider {
            width: 60px;
            height: 1px;
            background:
                {{ $accentColor }}
            ;
            margin: 24px auto;
        }

        .title {
            font-size: 42px;
            font-weight: 300;
            color: #0f172a;
            letter-spacing: -1px;
        }

        .type {
            margin-top: 8px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color:
                {{ $accentColor }}
            ;
        }

        .presented {
            margin-top: 30px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .student {
            margin-top: 14px;
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 1px;
        }

        .name-line {
            width: 240px;
            height: 1px;
            background: #e2e8f0;
            margin: 8px auto 0;
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .body {
            margin: 28px auto 0;
            width: 70%;
            font-size: 12px;
            line-height: 1.8;
            color: #64748b;
        }

        .meta-row {
            margin-top: 14px;
            font-size: 9px;
            color: #cbd5e1;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .footer {
            position: absolute;
            left: 60px;
            right: 60px;
            bottom: 50px;
            display: table;
            width: calc(100% - 120px);
        }

        .sig {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 20px;
        }

        .sig-img {
            height: 40px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .sig-line {
            margin: 8px auto 5px;
            width: 70%;
            border-top: 1px solid #e2e8f0;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #94a3b8;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="accent-top"></div>
        <div class="border-frame"></div>

        @if($watermarkPath && file_exists($watermarkPath))
            <img class="watermark" src="{{ $watermarkPath }}" alt="">
        @endif

        <div class="content">
            <div class="logo-section">
                @if($showLogo && $logoPath && file_exists($logoPath))
                    <img class="logo" src="{{ $logoPath }}" alt="">
                @endif
                <div class="school-name">{{ $schoolName }}</div>
            </div>

            <div class="divider"></div>

            <div class="title">{{ $certificate->title }}</div>
            <div class="type">{{ $certificate->type }}</div>

            <div class="presented">Presented to</div>
            <div class="student">{{ $student?->full_name }}</div>
            <div class="name-line"></div>

            @if($student?->admission_number || $student?->schoolClass?->name)
                <div class="student-meta">
                    {{ $student?->admission_number }}
                    @if($student?->schoolClass?->name)
                        &bull; {{ $student?->schoolClass?->name }} {{ $student?->section?->name }}
                    @endif
                </div>
            @endif

            <div class="body">{{ $certificate->body }}</div>

            <div class="meta-row">
                <span>{{ $certificate->serial_number }}</span>
                <span style="margin-left: 20px;">{{ $certificate->issued_on?->format('F j, Y') }}</span>
            </div>
        </div>

        <div class="footer">
            @if($sig1Label)
                <div class="sig">
                    @if($sig1ImagePath && file_exists($sig1ImagePath))
                        <img class="sig-img" src="{{ $sig1ImagePath }}" alt="">
                    @endif
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $sig1Label }}</div>
                    @if($sig1Name)
                        <div class="sig-name">{{ $sig1Name }}</div>
                    @endif
                </div>
            @endif

            @if($sig2Label)
                <div class="sig">
                    @if($sig2ImagePath && file_exists($sig2ImagePath))
                        <img class="sig-img" src="{{ $sig2ImagePath }}" alt="">
                    @endif
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $sig2Label }}</div>
                    @if($sig2Name)
                        <div class="sig-name">{{ $sig2Name }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>

</html>