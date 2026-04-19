<?php
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo e($certificate->title ?? 'Certificate'); ?></title>
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
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1a1a2e;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: #fefcf3;
            overflow: hidden;
        }

        /* Top wave decoration */
        .wave-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 140px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a855f7 30%, #ec4899 60%, #f59e0b 100%);
            border-radius: 0 0 50% 50% / 0 0 100% 100%;
        }

        .wave-top-inner {
            position: absolute;
            top: 0;
            left: 20px;
            right: 20px;
            height: 130px;
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 30%, #d946ef 60%, #fbbf24 100%);
            border-radius: 0 0 50% 50% / 0 0 100% 100%;
            opacity: 0.6;
        }

        /* Bottom wave decoration */
        .wave-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(135deg, #f59e0b 0%, #06b6d4 40%, #6c5ce7 80%, #a855f7 100%);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .wave-bottom-inner {
            position: absolute;
            bottom: 0;
            left: 20px;
            right: 20px;
            height: 90px;
            background: linear-gradient(135deg, #fbbf24 0%, #22d3ee 40%, #7c3aed 80%, #8b5cf6 100%);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            opacity: 0.5;
        }

        /* Sparkle dots */
        .sparkle {
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #fbbf24;
            opacity: 0.6;
        }

        .sparkle-1 {
            top: 155px;
            left: 80px;
        }

        .sparkle-2 {
            top: 180px;
            right: 120px;
            width: 4px;
            height: 4px;
            background: #a855f7;
        }

        .sparkle-3 {
            top: 200px;
            left: 200px;
            width: 5px;
            height: 5px;
            background: #ec4899;
        }

        .sparkle-4 {
            bottom: 120px;
            right: 80px;
            width: 4px;
            height: 4px;
            background: #06b6d4;
        }

        .sparkle-5 {
            bottom: 140px;
            left: 150px;
            width: 3px;
            height: 3px;
            background: #f59e0b;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 500px;
            height: 500px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 50px 50px 30px;
        }

        .logo-section {
            position: relative;
            margin-bottom: 8px;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 12px;
            background: #fff;
            padding: 6px;
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        .school-name {
            margin-top: 6px;
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .title-section {
            margin-top: 30px;
        }

        .title-main {
            font-size: 34px;
            font-weight: 800;
            color: #1a1a2e;
            letter-spacing: 1px;
        }

        .title-of {
            font-size: 20px;
            font-style: italic;
            color: #a855f7;
            font-weight: 600;
        }

        .title-type {
            font-size: 26px;
            font-weight: 800;
            color: #6c5ce7;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .presented {
            margin-top: 16px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #64748b;
        }

        .student {
            margin-top: 10px;
            font-size: 30px;
            font-weight: 800;
            color: #1a1a2e;
            font-style: italic;
            font-family: DejaVu Serif, Georgia, serif;
        }

        .name-underline {
            width: 300px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #a855f7, transparent);
            margin: 6px auto 0;
        }

        .student-meta {
            margin-top: 6px;
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
        }

        .body {
            margin: 18px auto 0;
            width: 78%;
            font-size: 13px;
            line-height: 1.6;
            color: #475569;
            font-style: italic;
        }

        .badge {
            margin: 16px auto 0;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border: 3px solid #d97706;
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
            margin-top: 8px;
            font-size: 9px;
            color: #94a3b8;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            left: 50px;
            right: 50px;
            bottom: 115px;
            display: table;
            width: calc(100% - 100px);
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
            margin: 6px auto 4px;
            width: 80%;
            border-top: 1px solid #cbd5e1;
        }

        .sig-label {
            font-size: 10px;
            font-weight: 700;
            color: #334155;
        }

        .sig-name {
            margin-top: 2px;
            font-size: 9px;
            color: #94a3b8;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Wave decorations -->
        <div class="wave-top"></div>
        <div class="wave-top-inner"></div>
        <div class="wave-bottom"></div>
        <div class="wave-bottom-inner"></div>

        <!-- Sparkles -->
        <div class="sparkle sparkle-1"></div>
        <div class="sparkle sparkle-2"></div>
        <div class="sparkle sparkle-3"></div>
        <div class="sparkle sparkle-4"></div>
        <div class="sparkle sparkle-5"></div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($watermarkPath && file_exists($watermarkPath)): ?>
            <img class="watermark" src="<?php echo e($watermarkPath); ?>" alt="">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="content">
            <div class="logo-section">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoPath && file_exists($logoPath)): ?>
                    <img class="logo" src="<?php echo e($logoPath); ?>" alt="">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="school-name"><?php echo e($schoolName); ?></div>
            </div>

            <div class="title-section">
                <div class="title-main">Certificate</div>
                <div class="title-of">of</div>
                <div class="title-type"><?php echo e($certificate->type ?: 'Achievement'); ?></div>
            </div>

            <div class="presented">This Certificate is Presented To</div>
            <div class="student"><?php echo e($student?->full_name); ?></div>
            <div class="name-underline"></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->admission_number || $student?->schoolClass?->name): ?>
                <div class="student-meta">
                    <?php echo e($student?->admission_number); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->schoolClass?->name): ?>
                        &bull; <?php echo e($student?->schoolClass?->name); ?> <?php echo e($student?->section?->name); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="body"><?php echo e($certificate->body); ?></div>

            <div class="badge">
                <span>EXCELLENCE<br>AWARD</span>
            </div>

            <div class="meta-row">
                <span>Serial: <?php echo e($certificate->serial_number); ?></span>
                <span style="margin-left: 14px;">Date: <?php echo e($certificate->issued_on?->format('F j, Y')); ?></span>
            </div>
        </div>

        <div class="footer">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig1Label): ?>
                <div class="sig">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig1ImagePath && file_exists($sig1ImagePath)): ?>
                        <img class="sig-img" src="<?php echo e($sig1ImagePath); ?>" alt="">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="sig-line"></div>
                    <div class="sig-label"><?php echo e($sig1Label); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig1Name): ?>
                        <div class="sig-name"><?php echo e($sig1Name); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="sig"></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig2Label): ?>
                <div class="sig">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig2ImagePath && file_exists($sig2ImagePath)): ?>
                        <img class="sig-img" src="<?php echo e($sig2ImagePath); ?>" alt="">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="sig-line"></div>
                    <div class="sig-label"><?php echo e($sig2Label); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig2Name): ?>
                        <div class="sig-name"><?php echo e($sig2Name); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</body>

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/certificate-vibrant.blade.php ENDPATH**/ ?>