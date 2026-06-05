@php
    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = config('academyhub.school_logo');
    $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;

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
            margin: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Serif, Georgia, serif;
            color: #f5f0e1;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(160deg, #0a1628 0%, #0f2241 30%, #162d50 60%, #0a1628 100%);
            padding: 10px;
        }

        /* Triple border layers */
        .border-1 {
            position: absolute;
            top: 14px;
            left: 14px;
            right: 14px;
            bottom: 14px;
            border: 3px solid #c9a84c;
        }

        .border-2 {
            position: absolute;
            top: 22px;
            left: 22px;
            right: 22px;
            bottom: 22px;
            border: 1px solid rgba(201, 168, 76, 0.4);
        }

        .border-3 {
            position: absolute;
            top: 28px;
            left: 28px;
            right: 28px;
            bottom: 28px;
            border: 2px solid #c9a84c;
        }

        /* Corner ornaments */
        .corner {
            position: absolute;
            width: 50px;
            height: 50px;
        }

        .corner-tl {
            top: 34px;
            left: 34px;
            border-top: 4px solid #e8d48b;
            border-left: 4px solid #e8d48b;
        }

        .corner-tr {
            top: 34px;
            right: 34px;
            border-top: 4px solid #e8d48b;
            border-right: 4px solid #e8d48b;
        }

        .corner-bl {
            bottom: 34px;
            left: 34px;
            border-bottom: 4px solid #e8d48b;
            border-left: 4px solid #e8d48b;
        }

        .corner-br {
            bottom: 34px;
            right: 34px;
            border-bottom: 4px solid #e8d48b;
            border-right: 4px solid #e8d48b;
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
            padding: 50px 60px 40px;
        }

        .logo-section {
            margin-bottom: 8px;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 50%;
            padding: 5px;
            background: rgba(201, 168, 76, 0.15);
            border: 2px solid #c9a84c;
        }

        .school-name {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #c9a84c;
        }

        .divider {
            margin: 12px auto;
            width: 300px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #c9a84c, transparent);
        }

        .title {
            font-size: 40px;
            font-weight: 700;
            color: #f5f0e1;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .type {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #c9a84c;
        }

        .presented {
            margin-top: 22px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #8899aa;
            font-style: italic;
        }

        .student {
            margin-top: 10px;
            font-size: 34px;
            font-weight: 700;
            color: #f5f0e1;
            font-style: italic;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .name-line {
            width: 280px;
            margin: 8px auto 0;
            border-top: 2px solid #c9a84c;
            position: relative;
        }

        .name-line::after {
            content: '★';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #0f2241;
            padding: 0 12px;
            color: #c9a84c;
            font-size: 12px;
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #8899aa;
            font-weight: 600;
        }

        .body {
            margin: 16px auto 0;
            width: 80%;
            font-size: 12px;
            line-height: 1.8;
            color: #b8c4d0;
            font-style: italic;
        }

        .rosette {
            margin: 14px auto 0;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c9a84c, #e8d48b, #c9a84c);
            border: 3px solid #a08530;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f2241;
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
            color: #6b7d8d;
            font-weight: 700;
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
            border-top: 1px solid #c9a84c;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #c9a84c;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #8899aa;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="border-1"></div>
        <div class="border-2"></div>
        <div class="border-3"></div>
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

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

            <div class="presented">This Certificate is Proudly Awarded To</div>
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

            <div class="rosette">
                <span>HONOR<br>AWARD</span>
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
                <div class="rosette" style="width:60px; height:60px; margin:0 auto; font-size:8px;">
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