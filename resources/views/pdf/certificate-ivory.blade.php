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
            margin: 14px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Serif, Georgia, serif;
            color: #3a2f2f;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: #fffef9;
            overflow: hidden;
        }

        /* Soft rose-gold circular glow top-right */
        .glow-tr {
            position: absolute;
            top: -60px;
            right: -60px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(183, 110, 121, 0.12) 0%, transparent 70%);
        }

        /* Soft rose-gold circular glow bottom-left */
        .glow-bl {
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(183, 110, 121, 0.10) 0%, transparent 70%);
        }

        /* Thin rose-gold border */
        .frame {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 1px solid rgba(183, 110, 121, 0.25);
        }

        .frame-inner {
            position: absolute;
            top: 26px;
            left: 26px;
            right: 26px;
            bottom: 26px;
            border: 1px solid rgba(183, 110, 121, 0.12);
        }

        /* Rose gold small accent dots at corners */
        .corner-dot {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(135deg, #b76e79, #d4a0a7);
        }

        .cd-tl {
            top: 17px;
            left: 17px;
        }

        .cd-tr {
            top: 17px;
            right: 17px;
        }

        .cd-bl {
            bottom: 17px;
            left: 17px;
        }

        .cd-br {
            bottom: 17px;
            right: 17px;
        }

        /* Top center rose-gold thin line */
        .top-line {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #b76e79, transparent);
        }

        .bottom-line {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #b76e79, transparent);
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 420px;
            height: 420px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 50px 80px 40px;
        }

        .logo-section {
            margin-bottom: 10px;
        }

        .logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
            border-radius: 50%;
            padding: 5px;
            background: #fff;
            border: 1px solid rgba(183, 110, 121, 0.3);
        }

        .school-name {
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #b76e79;
        }

        .rosegold-ornament {
            margin: 16px auto;
            width: 160px;
            position: relative;
        }

        .rosegold-ornament::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(183, 110, 121, 0.5), transparent);
        }

        .rosegold-ornament::after {
            content: '⬥';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #fffef9;
            padding: 0 10px;
            color: #b76e79;
            font-size: 10px;
        }

        .title {
            margin-top: 10px;
            font-size: 38px;
            font-weight: 400;
            color: #3a2f2f;
            letter-spacing: 1px;
        }

        .type {
            margin-top: 6px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #b76e79;
        }

        .presented {
            margin-top: 26px;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #c0a0a5;
        }

        .student {
            margin-top: 10px;
            font-size: 34px;
            font-weight: 400;
            color: #3a2f2f;
            font-style: italic;
        }

        .name-underline {
            width: 240px;
            height: 1px;
            margin: 8px auto 0;
            background: linear-gradient(90deg, transparent, #b76e79, transparent);
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #c0a0a5;
            font-weight: 600;
        }

        .body {
            margin: 18px auto 0;
            width: 72%;
            font-size: 12px;
            line-height: 1.9;
            color: #7a6a6a;
            font-style: italic;
        }

        .rosegold-seal {
            margin: 16px auto 0;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #b76e79, #d4a0a7, #e8c4c9, #d4a0a7, #b76e79);
            border: 2px solid #a05a65;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            line-height: 1.2;
        }

        .meta-row {
            margin-top: 8px;
            font-size: 8px;
            color: #c0a0a5;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .footer {
            position: absolute;
            left: 80px;
            right: 80px;
            bottom: 46px;
            display: table;
            width: calc(100% - 160px);
        }

        .sig {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 10px;
        }

        .sig-img {
            height: 40px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .sig-line {
            margin: 8px auto 5px;
            width: 75%;
            border-top: 1px solid rgba(183, 110, 121, 0.35);
        }

        .sig-label {
            font-size: 8px;
            font-weight: 700;
            color: #b76e79;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 8px;
            color: #c0a0a5;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="glow-tr"></div>
        <div class="glow-bl"></div>
        <div class="frame"></div>
        <div class="frame-inner"></div>
        <div class="corner-dot cd-tl"></div>
        <div class="corner-dot cd-tr"></div>
        <div class="corner-dot cd-bl"></div>
        <div class="corner-dot cd-br"></div>
        <div class="top-line"></div>
        <div class="bottom-line"></div>

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

            <div class="rosegold-ornament"></div>

            <div class="title">{{ $certificate->title }}</div>
            <div class="type">{{ $certificate->type }}</div>

            <div class="presented">Gracefully Presented To</div>
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
            <div class="rosegold-seal"><span>GRACE<br>AWARD</span></div>

            <div class="meta-row">
                <span>{{ $certificate->serial_number }}</span>
                <span style="margin-left: 16px;">{{ $certificate->issued_on?->format('F j, Y') }}</span>
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
                    <div class="sig-name">{{ $sig1Name }}</div>@endif
                </div>
            @endif
            <div class="sig">
                <div class="rosegold-seal" style="width:54px;height:54px;margin:0 auto;font-size:8px;"><span>SEAL</span>
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
                    <div class="sig-name">{{ $sig2Name }}</div>@endif
                </div>
            @endif
        </div>
    </div>
</body>

</html>