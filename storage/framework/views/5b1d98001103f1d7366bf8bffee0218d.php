<?php
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $logo = config('myacademy.school_logo');
    $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;

    $borderColor = '#6b21a8'; // Purple-700
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
            font-family: DejaVu Serif, Georgia, serif;
            color: #1e1040;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: #faf8ff;
            overflow: hidden;
        }

        /* Left banner */
        .banner-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 180px;
            height: 100%;
            background: linear-gradient(180deg, #581c87 0%, #7c3aed 40%, #a855f7 70%, #d946ef 100%);
        }

        /* Ribbon overlay on banner */
        .ribbon-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 180px;
            height: 100%;
            background: linear-gradient(180deg,
                    transparent 0%,
                    rgba(251, 191, 36, 0.3) 20%,
                    transparent 40%,
                    rgba(251, 191, 36, 0.2) 60%,
                    transparent 80%,
                    rgba(251, 191, 36, 0.3) 100%);
        }

        /* Gold border frame on right portion */
        .frame-outer {
            position: absolute;
            top: 30px;
            left: 200px;
            right: 30px;
            bottom: 30px;
            border: 2px solid #d4af37;
        }

        .frame-inner {
            position: absolute;
            top: 38px;
            left: 208px;
            right: 38px;
            bottom: 38px;
            border: 1px solid #7c3aed;
        }

        /* Logo in top-left banner area */
        .banner-logo {
            position: absolute;
            top: 35px;
            left: 25px;
            text-align: center;
            width: 130px;
        }

        .logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.15);
            padding: 6px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .banner-school {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 55%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 400px;
            height: 400px;
            object-fit: contain;
            z-index: 0;
        }

        /* Main content area — right portion */
        .content {
            position: absolute;
            top: 50px;
            left: 220px;
            right: 50px;
            bottom: 50px;
            z-index: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .title-word {
            font-size: 40px;
            font-weight: 700;
            color: #1e1040;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .title-of {
            font-size: 18px;
            font-style: italic;
            color: #a855f7;
            font-weight: 600;
            margin: 2px 0;
        }

        .title-type {
            font-size: 24px;
            font-weight: 700;
            color: #7c3aed;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .presented {
            margin-top: 22px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #64748b;
        }

        .student {
            margin-top: 10px;
            font-size: 30px;
            font-weight: 700;
            color: #1e1040;
            font-style: italic;
        }

        .name-deco {
            width: 260px;
            margin: 6px auto 0;
            border-top: 2px solid #d4af37;
            position: relative;
        }

        .name-deco::after {
            content: '◇';
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #faf8ff;
            padding: 0 8px;
            color: #d4af37;
            font-size: 10px;
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
        }

        .body {
            margin: 18px auto 0;
            width: 85%;
            font-size: 13px;
            line-height: 1.7;
            color: #475569;
            font-style: italic;
        }

        .badge {
            margin: 14px auto 0;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border: 3px solid #b45309;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7c2d12;
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
            left: 220px;
            right: 50px;
            bottom: 55px;
            display: table;
            width: calc(100% - 270px);
        }

        .sig {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 8px;
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
            border-top: 1px solid #a78bfa;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #1e1040;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 2px;
            font-size: 9px;
            color: #7c3aed;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Left purple banner with ribbon -->
        <div class="banner-left"></div>
        <div class="ribbon-overlay"></div>

        <!-- Gold & purple frame -->
        <div class="frame-outer"></div>
        <div class="frame-inner"></div>

        <!-- Logo in banner -->
        <div class="banner-logo">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoPath && file_exists($logoPath)): ?>
                <img class="logo" src="<?php echo e($logoPath); ?>" alt="">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="banner-school"><?php echo e($schoolName); ?></div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($watermarkPath && file_exists($watermarkPath)): ?>
            <img class="watermark" src="<?php echo e($watermarkPath); ?>" alt="">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="content">
            <div class="title-word">Certificate</div>
            <div class="title-of">of</div>
            <div class="title-type"><?php echo e($certificate->type ?: 'Achievement'); ?></div>

            <div class="presented">This is Proudly Presented To</div>
            <div class="student"><?php echo e($student?->full_name); ?></div>
            <div class="name-deco"></div>

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

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/certificate-royal.blade.php ENDPATH**/ ?>