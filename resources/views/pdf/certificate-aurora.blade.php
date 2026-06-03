@php
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $logo = config('myacademy.school_logo');
    $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;

    $showLogo = (bool) config('myacademy.certificate_show_logo', true);
    $showWatermark = (bool) config('myacademy.certificate_show_watermark', false);
    $watermark = config('myacademy.certificate_watermark_image');
    $watermarkPath = ($watermark && $showWatermark) ? public_path('uploads/' . str_replace('\\', '/', $watermark)) : null;

    $sig1Label = config('myacademy.certificate_signature_label', 'Authorized Signature');
    $sig1Name = config('myacademy.certificate_signature_name');
    $sig1Image = config('myacademy.certificate_signature_image');
    $sig1ImagePath = $sig1Image ? public_path('uploads/' . str_replace('\\', '/', $sig1Image)) : null;

    $sig2Label = config('myacademy.certificate_signature2_label');
    $sig2Name = config('myacademy.certificate_signature2_name');
    $sig2Image = config('myacademy.certificate_signature2_image');
    $sig2ImagePath = $sig2Image ? public_path('uploads/' . str_replace('\\', '/', $sig2Image)) : null;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $certificate->title ?? 'Certificate' }}</title>
    <style>
        @page {
            margin: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Serif, Georgia, serif;
            color: #1a1a2e;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: #fafbff;
            overflow: hidden;
        }

        /* Top aurora gradient band */
        .aurora-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(135deg, #0d9488, #2dd4bf, #7c3aed, #a855f7, #ec4899);
            opacity: 0.9;
        }

        /* Bottom aurora gradient band */
        .aurora-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(135deg, #a855f7, #7c3aed, #2dd4bf, #0d9488);
            opacity: 0.85;
        }

        /* Semi-transparent frost overlay for content area */
        .frost-panel {
            position: absolute;
            top: 50px;
            left: 40px;
            right: 40px;
            bottom: 30px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(13, 148, 136, 0.2);
            border-radius: 4px;
        }

        /* Accent line under top band */
        .accent-line {
            position: absolute;
            top: 100px;
            left: 60px;
            right: 60px;
            height: 2px;
            background: linear-gradient(90deg, #0d9488, #7c3aed, #ec4899, #7c3aed, #0d9488);
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 450px;
            height: 450px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 30px 70px 40px;
        }

        .logo-section {
            margin-bottom: 6px;
        }

        .logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            border-radius: 50%;
            padding: 5px;
            background: #fff;
            border: 2px solid #0d9488;
        }

        .school-name {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #fff;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .title-section {
            margin-top: 26px;
        }

        .title {
            font-size: 38px;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: 2px;
        }

        .gradient-underline {
            width: 200px;
            height: 3px;
            margin: 8px auto 0;
            background: linear-gradient(90deg, #0d9488, #7c3aed, #ec4899);
            border-radius: 2px;
        }

        .type {
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #7c3aed;
        }

        .presented {
            margin-top: 22px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #64748b;
            font-style: italic;
        }

        .student {
            margin-top: 10px;
            font-size: 32px;
            font-weight: 700;
            color: #0d9488;
            font-style: italic;
        }

        .name-decoration {
            width: 260px;
            margin: 6px auto 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #0d9488, #7c3aed, #0d9488, transparent);
            border-radius: 1px;
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
        }

        .body {
            margin: 16px auto 0;
            width: 78%;
            font-size: 12px;
            line-height: 1.8;
            color: #475569;
            font-style: italic;
        }

        .badge {
            margin: 14px auto 0;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d9488, #2dd4bf);
            border: 3px solid #0f766e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            line-height: 1.2;
        }

        .meta-row {
            margin-top: 6px;
            font-size: 9px;
            color: #94a3b8;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            left: 70px;
            right: 70px;
            bottom: 50px;
            display: table;
            width: calc(100% - 140px);
        }

        .sig {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 10px;
        }

        .sig-img {
            height: 42px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .sig-line {
            margin: 8px auto 5px;
            width: 80%;
            border-top: 1px solid #0d9488;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #0d9488;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #64748b;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="aurora-top"></div>
        <div class="aurora-bottom"></div>
        <div class="frost-panel"></div>
        <div class="accent-line"></div>

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

            <div class="title-section">
                <div class="title">{{ $certificate->title }}</div>
                <div class="gradient-underline"></div>
                <div class="type">{{ $certificate->type }}</div>
            </div>

            <div class="presented">This Certificate is Presented With Distinction To</div>
            <div class="student">{{ $student?->full_name }}</div>
            <div class="name-decoration"></div>

            @if($student?->admission_number || $student?->schoolClass?->name)
                <div class="student-meta">
                    {{ $student?->admission_number }}
                    @if($student?->schoolClass?->name)
                        &bull; {{ $student?->schoolClass?->name }} {{ $student?->section?->name }}
                    @endif
                </div>
            @endif

            <div class="body">{{ $certificate->body }}</div>

            <div class="badge">
                <span>HONOR<br>ROLL</span>
            </div>

            <div class="meta-row">
                <span>Serial: {{ $certificate->serial_number }}</span>
                <span style="margin-left: 14px;">Date: {{ $certificate->issued_on?->format('F j, Y') }}</span>
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

            <div class="sig">
                <div class="badge" style="width:58px; height:58px; margin:0 auto; font-size:8px;">
                    <span>SEAL</span>
                </div>
            </div>

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