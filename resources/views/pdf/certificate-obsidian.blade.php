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
        @page { margin: 10px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; color: #e0e0e0; }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(145deg, #1a1a1a 0%, #2d2d2d 35%, #1f1f1f 65%, #111111 100%);
            overflow: hidden;
        }

        /* Subtle diagonal silver stripes */
        .stripe-1 {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 250px;
            height: 600px;
            background: linear-gradient(135deg, transparent 45%, rgba(192,192,192,0.04) 45%, rgba(192,192,192,0.04) 55%, transparent 55%);
            transform: rotate(15deg);
        }
        .stripe-2 {
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 250px;
            height: 600px;
            background: linear-gradient(135deg, transparent 45%, rgba(192,192,192,0.04) 45%, rgba(192,192,192,0.04) 55%, transparent 55%);
            transform: rotate(15deg);
        }

        /* Thin silver border */
        .frame {
            position: absolute;
            top: 18px; left: 18px; right: 18px; bottom: 18px;
            border: 1px solid rgba(192,192,192,0.3);
        }
        .frame-accent {
            position: absolute;
            top: 22px; left: 22px; right: 22px; bottom: 22px;
            border: 2px solid rgba(192,192,192,0.15);
        }

        /* Top silver bar accent */
        .top-accent {
            position: absolute;
            top: 0; left: 80px; right: 80px;
            height: 4px;
            background: linear-gradient(90deg, transparent, #c0c0c0, #e8e8e8, #c0c0c0, transparent);
        }

        .watermark {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.02;
            width: 450px; height: 450px;
            object-fit: contain; z-index: 0;
        }

        .content {
            position: relative; z-index: 1;
            text-align: center;
            padding: 55px 70px 40px;
        }

        .logo-section { margin-bottom: 10px; }
        .logo {
            width: 65px; height: 65px;
            object-fit: contain;
            border-radius: 8px;
            padding: 5px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(192,192,192,0.3);
        }
        .school-name {
            margin-top: 8px;
            font-size: 11px; font-weight: 700;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: #999;
        }

        .silver-line {
            margin: 16px auto;
            width: 100px; height: 1px;
            background: linear-gradient(90deg, transparent, #888, transparent);
        }

        .title {
            font-size: 36px; font-weight: 300;
            color: #ffffff;
            letter-spacing: 6px;
            text-transform: uppercase;
        }
        .type {
            margin-top: 6px;
            font-size: 10px; font-weight: 700;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #c0c0c0;
        }

        .presented {
            margin-top: 26px;
            font-size: 10px; font-weight: 400;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #777;
        }
        .student {
            margin-top: 10px;
            font-size: 32px; font-weight: 300;
            color: #ffffff;
            letter-spacing: 2px;
        }
        .name-bar {
            width: 240px; height: 1px;
            margin: 10px auto 0;
            background: linear-gradient(90deg, transparent, #c0c0c0, transparent);
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px; color: #777;
            font-weight: 600;
        }
        .body {
            margin: 18px auto 0;
            width: 75%; font-size: 11px;
            line-height: 1.9; color: #999;
        }

        .platinum-badge {
            margin: 16px auto 0;
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #888, #ccc, #888);
            border: 2px solid #666;
            display: flex; align-items: center; justify-content: center;
            color: #222; font-size: 8px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px;
            text-align: center; line-height: 1.2;
        }

        .meta-row {
            margin-top: 8px;
            font-size: 8px; color: #666;
            font-weight: 600; letter-spacing: 1px;
        }

        .footer {
            position: absolute;
            left: 70px; right: 70px; bottom: 46px;
            display: table;
            width: calc(100% - 140px);
        }
        .sig {
            display: table-cell; width: 33.33%;
            text-align: center; vertical-align: bottom;
            padding: 0 10px;
        }
        .sig-img {
            height: 40px; object-fit: contain;
            display: block; margin: 0 auto;
        }
        .sig-line {
            margin: 8px auto 5px; width: 80%;
            border-top: 1px solid rgba(192,192,192,0.3);
        }
        .sig-label {
            font-size: 8px; font-weight: 700;
            color: #999; text-transform: uppercase;
            letter-spacing: 2px;
        }
        .sig-name {
            margin-top: 3px; font-size: 8px;
            color: #666; font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="stripe-1"></div>
        <div class="stripe-2"></div>
        <div class="frame"></div>
        <div class="frame-accent"></div>
        <div class="top-accent"></div>

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

            <div class="silver-line"></div>

            <div class="title">{{ $certificate->title }}</div>
            <div class="type">{{ $certificate->type }}</div>

            <div class="presented">Awarded to</div>
            <div class="student">{{ $student?->full_name }}</div>
            <div class="name-bar"></div>

            @if($student?->admission_number || $student?->schoolClass?->name)
                <div class="student-meta">
                    {{ $student?->admission_number }}
                    @if($student?->schoolClass?->name)
                        &bull; {{ $student?->schoolClass?->name }} {{ $student?->section?->name }}
                    @endif
                </div>
            @endif

            <div class="body">{{ $certificate->body }}</div>

            <div class="platinum-badge"><span>PLATINUM<br>GRADE</span></div>

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
                    @if($sig1Name)<div class="sig-name">{{ $sig1Name }}</div>@endif
                </div>
            @endif
            <div class="sig">
                <div class="platinum-badge" style="width:54px;height:54px;margin:0 auto;font-size:7px;"><span>SEAL</span></div>
            </div>
            @if($sig2Label)
                <div class="sig">
                    @if($sig2ImagePath && file_exists($sig2ImagePath))
                        <img class="sig-img" src="{{ $sig2ImagePath }}" alt="">
                    @endif
                    <div class="sig-line"></div>
                    <div class="sig-label">{{ $sig2Label }}</div>
                    @if($sig2Name)<div class="sig-name">{{ $sig2Name }}</div>@endif
                </div>
            @endif
        </div>
    </div>
</body>
</html>
