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
            color: #3c1f1f;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(150deg, #fdf6ec 0%, #faf0de 30%, #f5e8d0 60%, #fdf6ec 100%);
            padding: 10px;
        }

        /* Outermost ornate border */
        .border-ornate-1 {
            position: absolute;
            top: 14px;
            left: 14px;
            right: 14px;
            bottom: 14px;
            border: 5px solid #8b1a1a;
        }

        .border-ornate-2 {
            position: absolute;
            top: 22px;
            left: 22px;
            right: 22px;
            bottom: 22px;
            border: 1px solid #d4af37;
        }

        .border-ornate-3 {
            position: absolute;
            top: 26px;
            left: 26px;
            right: 26px;
            bottom: 26px;
            border: 1px solid #d4af37;
        }

        .border-ornate-4 {
            position: absolute;
            top: 32px;
            left: 32px;
            right: 32px;
            bottom: 32px;
            border: 3px solid #8b1a1a;
        }

        /* Ornamental corners */
        .orn-corner {
            position: absolute;
            width: 55px;
            height: 55px;
        }

        .orn-tl {
            top: 38px;
            left: 38px;
            border-top: 5px solid #d4af37;
            border-left: 5px solid #d4af37;
        }

        .orn-tr {
            top: 38px;
            right: 38px;
            border-top: 5px solid #d4af37;
            border-right: 5px solid #d4af37;
        }

        .orn-bl {
            bottom: 38px;
            left: 38px;
            border-bottom: 5px solid #d4af37;
            border-left: 5px solid #d4af37;
        }

        .orn-br {
            bottom: 38px;
            right: 38px;
            border-bottom: 5px solid #d4af37;
            border-right: 5px solid #d4af37;
        }

        /* Inner corner details */
        .inner-corner {
            position: absolute;
            width: 30px;
            height: 30px;
        }

        .ic-tl {
            top: 44px;
            left: 44px;
            border-top: 2px solid #8b1a1a;
            border-left: 2px solid #8b1a1a;
        }

        .ic-tr {
            top: 44px;
            right: 44px;
            border-top: 2px solid #8b1a1a;
            border-right: 2px solid #8b1a1a;
        }

        .ic-bl {
            bottom: 44px;
            left: 44px;
            border-bottom: 2px solid #8b1a1a;
            border-left: 2px solid #8b1a1a;
        }

        .ic-br {
            bottom: 44px;
            right: 44px;
            border-bottom: 2px solid #8b1a1a;
            border-right: 2px solid #8b1a1a;
        }

        /* Side ornamental bars */
        .side-bar-left {
            position: absolute;
            top: 50%;
            left: 18px;
            transform: translateY(-50%);
            width: 4px;
            height: 150px;
            background: linear-gradient(180deg, transparent, #d4af37, #8b1a1a, #d4af37, transparent);
            border-radius: 2px;
        }

        .side-bar-right {
            position: absolute;
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
            width: 4px;
            height: 150px;
            background: linear-gradient(180deg, transparent, #d4af37, #8b1a1a, #d4af37, transparent);
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
            margin-bottom: 6px;
        }

        .logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            border-radius: 50%;
            padding: 5px;
            background: #fff;
            border: 3px solid #8b1a1a;
        }

        .school-name {
            margin-top: 8px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #8b1a1a;
        }

        .flourish {
            margin: 10px auto;
            width: 240px;
            position: relative;
        }

        .flourish::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: #8b1a1a;
        }

        .flourish::after {
            content: '✦';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #f5e8d0;
            padding: 0 10px;
            color: #d4af37;
            font-size: 12px;
        }

        .title {
            margin-top: 14px;
            font-size: 38px;
            font-weight: 700;
            color: #8b1a1a;
            letter-spacing: 2px;
        }

        .type {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #d4af37;
        }

        .presented {
            margin-top: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #8b6b4a;
            font-style: italic;
        }

        .student {
            margin-top: 10px;
            font-size: 34px;
            font-weight: 700;
            color: #3c1f1f;
            font-style: italic;
        }

        .name-rule {
            width: 280px;
            margin: 6px auto 0;
            position: relative;
        }

        .name-rule::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #8b1a1a, #d4af37, #8b1a1a, transparent);
        }

        .name-rule::after {
            content: '◆';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #f5e8d0;
            padding: 0 8px;
            color: #d4af37;
            font-size: 10px;
        }

        .student-meta {
            margin-top: 10px;
            font-size: 10px;
            color: #8b6b4a;
            font-weight: 600;
        }

        .body {
            margin: 16px auto 0;
            width: 78%;
            font-size: 12px;
            line-height: 1.8;
            color: #5c3d3d;
            font-style: italic;
        }

        .medallion {
            margin: 14px auto 0;
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37, #e8d48b, #d4af37);
            border: 4px solid #8b1a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8b1a1a;
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
            color: #a08a6e;
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
            border-top: 1px solid #8b1a1a;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #8b1a1a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #8b6b4a;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="border-ornate-1"></div>
        <div class="border-ornate-2"></div>
        <div class="border-ornate-3"></div>
        <div class="border-ornate-4"></div>
        <div class="orn-corner orn-tl"></div>
        <div class="orn-corner orn-tr"></div>
        <div class="orn-corner orn-bl"></div>
        <div class="orn-corner orn-br"></div>
        <div class="inner-corner ic-tl"></div>
        <div class="inner-corner ic-tr"></div>
        <div class="inner-corner ic-bl"></div>
        <div class="inner-corner ic-br"></div>
        <div class="side-bar-left"></div>
        <div class="side-bar-right"></div>

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

            <div class="flourish"></div>

            <div class="title">{{ $certificate->title }}</div>
            <div class="type">{{ $certificate->type }}</div>

            <div class="presented">This Certificate is Honourably Bestowed Upon</div>
            <div class="student">{{ $student?->full_name }}</div>
            <div class="name-rule"></div>

            @if($student?->admission_number || $student?->schoolClass?->name)
                <div class="student-meta">
                    {{ $student?->admission_number }}
                    @if($student?->schoolClass?->name)
                        &bull; {{ $student?->schoolClass?->name }} {{ $student?->section?->name }}
                    @endif
                </div>
            @endif

            <div class="body">{{ $certificate->body }}</div>

            <div class="medallion">
                <span>DISTINCTION<br>AWARD</span>
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
                <div class="medallion" style="width:60px; height:60px; margin:0 auto; font-size:8px;">
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