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
            color: #0a2540;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(165deg, #e8f4f8 0%, #d4eef5 30%, #c0e6f0 60%, #e0f0f5 100%);
            overflow: hidden;
        }

        /* Wave-like top decoration */
        .wave-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(180deg, #0a4d68 0%, #0e6b8a 40%, #1090b0 70%, transparent 100%);
        }

        .wave-mid {
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(180deg, rgba(16, 144, 176, 0.4) 0%, transparent 100%);
        }

        /* Bottom wave */
        .wave-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 55px;
            background: linear-gradient(0deg, #0a4d68 0%, #0e6b8a 40%, #1090b0 70%, transparent 100%);
        }

        /* Left accent stripe */
        .left-stripe {
            position: absolute;
            top: 100px;
            left: 0;
            width: 6px;
            height: 300px;
            background: linear-gradient(180deg, transparent, #0e6b8a, #56c5d0, #0e6b8a, transparent);
            border-radius: 0 3px 3px 0;
        }

        /* Right accent stripe */
        .right-stripe {
            position: absolute;
            top: 100px;
            right: 0;
            width: 6px;
            height: 300px;
            background: linear-gradient(180deg, transparent, #0e6b8a, #56c5d0, #0e6b8a, transparent);
            border-radius: 3px 0 0 3px;
        }

        /* Seafoam frame */
        .frame {
            position: absolute;
            top: 90px;
            left: 22px;
            right: 22px;
            bottom: 65px;
            border: 2px solid rgba(10, 77, 104, 0.3);
            border-radius: 3px;
        }

        /* Floating bubbles/dots */
        .dot-1 {
            position: absolute;
            top: 95px;
            left: 36px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(86, 197, 208, 0.4);
        }

        .dot-2 {
            position: absolute;
            top: 110px;
            right: 50px;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(86, 197, 208, 0.3);
        }

        .dot-3 {
            position: absolute;
            bottom: 85px;
            left: 60px;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(86, 197, 208, 0.35);
        }

        .dot-4 {
            position: absolute;
            bottom: 100px;
            right: 40px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(86, 197, 208, 0.25);
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
            padding: 24px 70px 40px;
        }

        .logo-section {
            margin-bottom: 6px;
        }

        .logo {
            width: 65px;
            height: 65px;
            object-fit: contain;
            border-radius: 50%;
            padding: 5px;
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.9);
        }

        .school-name {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #fff;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .wave-divider {
            margin: 24px auto;
            width: 160px;
            position: relative;
        }

        .wave-divider::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #0e6b8a, #56c5d0, #0e6b8a, transparent);
        }

        .wave-divider::after {
            content: '◈';
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #d4eef5;
            padding: 0 10px;
            color: #0e6b8a;
            font-size: 12px;
        }

        .title {
            margin-top: 10px;
            font-size: 36px;
            font-weight: 700;
            color: #0a2540;
            letter-spacing: 1px;
        }

        .type {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #1090b0;
        }

        .presented {
            margin-top: 24px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #5a8a9a;
            font-style: italic;
        }

        .student {
            margin-top: 10px;
            font-size: 32px;
            font-weight: 700;
            color: #0a2540;
            font-style: italic;
        }

        .name-wave {
            width: 260px;
            height: 2px;
            margin: 8px auto 0;
            background: linear-gradient(90deg, transparent, #56c5d0, #0e6b8a, #56c5d0, transparent);
            border-radius: 1px;
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #5a8a9a;
            font-weight: 600;
        }

        .body {
            margin: 16px auto 0;
            width: 78%;
            font-size: 12px;
            line-height: 1.8;
            color: #2a5a6a;
            font-style: italic;
        }

        .ocean-badge {
            margin: 14px auto 0;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0e6b8a, #1090b0, #56c5d0);
            border: 3px solid #0a4d68;
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
            color: #7aabb8;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            left: 70px;
            right: 70px;
            bottom: 70px;
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
            border-top: 1px solid #0e6b8a;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #0e6b8a;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #5a8a9a;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="wave-top"></div>
        <div class="wave-mid"></div>
        <div class="wave-bottom"></div>
        <div class="left-stripe"></div>
        <div class="right-stripe"></div>
        <div class="frame"></div>
        <div class="dot-1"></div>
        <div class="dot-2"></div>
        <div class="dot-3"></div>
        <div class="dot-4"></div>

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

            <div class="wave-divider"></div>

            <div class="title">{{ $certificate->title }}</div>
            <div class="type">{{ $certificate->type }}</div>

            <div class="presented">This Certificate is Proudly Presented To</div>
            <div class="student">{{ $student?->full_name }}</div>
            <div class="name-wave"></div>

            @if($student?->admission_number || $student?->schoolClass?->name)
                <div class="student-meta">
                    {{ $student?->admission_number }}
                    @if($student?->schoolClass?->name)
                        &bull; {{ $student?->schoolClass?->name }} {{ $student?->section?->name }}
                    @endif
                </div>
            @endif

            <div class="body">{{ $certificate->body }}</div>
            <div class="ocean-badge"><span>NAUTICAL<br>HONOR</span></div>

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
                    <div class="sig-name">{{ $sig1Name }}</div>@endif
                </div>
            @endif
            <div class="sig">
                <div class="ocean-badge" style="width:56px;height:56px;margin:0 auto;font-size:8px;"><span>SEAL</span>
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