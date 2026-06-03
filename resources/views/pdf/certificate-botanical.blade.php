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
            color: #2d3b2d;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(170deg, #f7faf4 0%, #eef5e8 30%, #f0f7ec 60%, #fafcf8 100%);
            padding: 10px;
        }

        /* Decorative top bar */
        .top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #4a7c59, #6b9e7a, #8bb89a, #6b9e7a, #4a7c59);
        }

        /* Bottom bar */
        .bottom-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #4a7c59, #6b9e7a, #8bb89a, #6b9e7a, #4a7c59);
        }

        /* Main border frame */
        .frame {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #4a7c59;
        }

        .frame-inner {
            position: absolute;
            top: 26px;
            left: 26px;
            right: 26px;
            bottom: 26px;
            border: 1px solid #a8c8a0;
        }

        /* Leaf-shaped corner ornaments */
        .leaf-corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border-radius: 0 50% 0 50%;
            background: rgba(74, 124, 89, 0.12);
            border: 1px solid rgba(74, 124, 89, 0.25);
        }

        .leaf-tl {
            top: 30px;
            left: 30px;
        }

        .leaf-tr {
            top: 30px;
            right: 30px;
            border-radius: 50% 0 50% 0;
        }

        .leaf-bl {
            bottom: 30px;
            left: 30px;
            border-radius: 50% 0 50% 0;
        }

        .leaf-br {
            bottom: 30px;
            right: 30px;
            border-radius: 0 50% 0 50%;
        }

        /* Side vine decorations */
        .vine-left {
            position: absolute;
            top: 50%;
            left: 36px;
            transform: translateY(-50%);
            width: 3px;
            height: 200px;
            background: linear-gradient(180deg, transparent, #a8c8a0, #4a7c59, #a8c8a0, transparent);
            border-radius: 2px;
        }

        .vine-right {
            position: absolute;
            top: 50%;
            right: 36px;
            transform: translateY(-50%);
            width: 3px;
            height: 200px;
            background: linear-gradient(180deg, transparent, #a8c8a0, #4a7c59, #a8c8a0, transparent);
            border-radius: 2px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            width: 450px;
            height: 450px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 50px 70px 40px;
        }

        .logo-section {
            margin-bottom: 8px;
        }

        .logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 50%;
            padding: 6px;
            background: #fff;
            border: 2px solid #4a7c59;
        }

        .school-name {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #4a7c59;
        }

        .ornament-line {
            margin: 12px auto;
            width: 180px;
            position: relative;
        }

        .ornament-line::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #4a7c59, transparent);
        }

        .ornament-line::after {
            content: '❧';
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #f0f7ec;
            padding: 0 10px;
            color: #4a7c59;
            font-size: 14px;
        }

        .title {
            margin-top: 18px;
            font-size: 36px;
            font-weight: 700;
            color: #2d3b2d;
            letter-spacing: 2px;
        }

        .type {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #6b9e7a;
        }

        .presented {
            margin-top: 22px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #7a8e7a;
            font-style: italic;
        }

        .student {
            margin-top: 10px;
            font-size: 32px;
            font-weight: 700;
            color: #2d3b2d;
            font-style: italic;
        }

        .name-underline {
            width: 260px;
            margin: 6px auto 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #4a7c59, transparent);
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #7a8e7a;
            font-weight: 600;
        }

        .body {
            margin: 16px auto 0;
            width: 78%;
            font-size: 12px;
            line-height: 1.8;
            color: #4a5e4a;
            font-style: italic;
        }

        .wreath {
            margin: 14px auto 0;
            width: 66px;
            height: 66px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a7c59, #6b9e7a);
            border: 3px solid #3a6248;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f5f0e1;
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
            color: #8a9e8a;
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
            border-top: 1px solid #4a7c59;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #4a7c59;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #7a8e7a;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="top-bar"></div>
        <div class="bottom-bar"></div>
        <div class="frame"></div>
        <div class="frame-inner"></div>
        <div class="leaf-corner leaf-tl"></div>
        <div class="leaf-corner leaf-tr"></div>
        <div class="leaf-corner leaf-bl"></div>
        <div class="leaf-corner leaf-br"></div>
        <div class="vine-left"></div>
        <div class="vine-right"></div>

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

            <div class="ornament-line"></div>

            <div class="title">{{ $certificate->title }}</div>
            <div class="type">{{ $certificate->type }}</div>

            <div class="presented">This Certificate is Gratefully Presented To</div>
            <div class="student">{{ $student?->full_name }}</div>
            <div class="name-underline"></div>

            @if($student?->admission_number || $student?->schoolClass?->name)
                <div class="student-meta">
                    {{ $student?->admission_number }}
                    @if($student?->schoolClass?->name)
                        &bull; {{ $student?->schoolClass?->name }} {{ $student?->section?->name }}
                    @endif
                </div>
            @endif

            <div class="body">{{ $certificate->body }}</div>

            <div class="wreath">
                <span>MERIT<br>AWARD</span>
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
                <div class="wreath" style="width:58px; height:58px; margin:0 auto; font-size:8px;">
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