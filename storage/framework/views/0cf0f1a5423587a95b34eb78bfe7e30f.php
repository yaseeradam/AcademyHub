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
            font-family: DejaVu Serif, Georgia, serif;
            color: #5c3a1e;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(155deg, #faf3e6 0%, #f0e0c4 30%, #e8d5b0 60%, #f5eadb 100%);
            overflow: hidden;
        }

        /* Top terracotta band with geometric pattern */
        .top-band {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(90deg, #c2703e, #d4874e, #c2703e, #b85c30, #c2703e);
        }

        .top-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: repeating-linear-gradient(90deg,
                    transparent, transparent 40px,
                    rgba(255, 255, 255, 0.08) 40px, rgba(255, 255, 255, 0.08) 42px);
        }

        /* Bottom matching band */
        .bottom-band {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 35px;
            background: linear-gradient(90deg, #c2703e, #d4874e, #c2703e, #b85c30, #c2703e);
        }

        .bottom-pattern {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 35px;
            background: repeating-linear-gradient(90deg,
                    transparent, transparent 40px,
                    rgba(255, 255, 255, 0.08) 40px, rgba(255, 255, 255, 0.08) 42px);
        }

        /* Copper frame */
        .frame {
            position: absolute;
            top: 60px;
            left: 24px;
            right: 24px;
            bottom: 45px;
            border: 2px solid #b87333;
        }

        .frame-inner {
            position: absolute;
            top: 66px;
            left: 30px;
            right: 30px;
            bottom: 51px;
            border: 1px solid rgba(184, 115, 51, 0.4);
        }

        /* Diamond corner accents */
        .diamond {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #c2703e;
            transform: rotate(45deg);
        }

        .d-tl {
            top: 56px;
            left: 19px;
        }

        .d-tr {
            top: 56px;
            right: 19px;
        }

        .d-bl {
            bottom: 41px;
            left: 19px;
        }

        .d-br {
            bottom: 41px;
            right: 19px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            width: 420px;
            height: 420px;
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
            width: 65px;
            height: 65px;
            object-fit: contain;
            border-radius: 50%;
            padding: 5px;
            background: rgba(255, 255, 255, 0.6);
            border: 2px solid #b87333;
        }

        .school-name {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .copper-divider {
            margin: 20px auto;
            width: 220px;
            position: relative;
        }

        .copper-divider::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #b87333, #d4874e, #b87333, transparent);
        }

        .copper-divider::after {
            content: '❖';
            position: absolute;
            top: -9px;
            left: 50%;
            transform: translateX(-50%);
            background: #f0e0c4;
            padding: 0 12px;
            color: #c2703e;
            font-size: 13px;
        }

        .title {
            margin-top: 14px;
            font-size: 36px;
            font-weight: 700;
            color: #5c3a1e;
            letter-spacing: 2px;
        }

        .type {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #c2703e;
        }

        .presented {
            margin-top: 24px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #8b6b4a;
            font-style: italic;
        }

        .student {
            margin-top: 10px;
            font-size: 32px;
            font-weight: 700;
            color: #5c3a1e;
            font-style: italic;
        }

        .name-line {
            width: 260px;
            margin: 8px auto 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #c2703e, #d4874e, #c2703e, transparent);
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #8b6b4a;
            font-weight: 600;
        }

        .body {
            margin: 16px auto 0;
            width: 78%;
            font-size: 12px;
            line-height: 1.8;
            color: #6b4e30;
            font-style: italic;
        }

        .copper-seal {
            margin: 14px auto 0;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #b87333, #d4874e, #b87333);
            border: 3px solid #8b5a2b;
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
            color: #a08a6e;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            left: 70px;
            right: 70px;
            bottom: 52px;
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
            border-top: 1px solid #b87333;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color: #b87333;
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
        <div class="top-band"></div>
        <div class="top-pattern"></div>
        <div class="bottom-band"></div>
        <div class="bottom-pattern"></div>
        <div class="frame"></div>
        <div class="frame-inner"></div>
        <div class="diamond d-tl"></div>
        <div class="diamond d-tr"></div>
        <div class="diamond d-bl"></div>
        <div class="diamond d-br"></div>

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

            <div class="copper-divider"></div>

            <div class="title"><?php echo e($certificate->title); ?></div>
            <div class="type"><?php echo e($certificate->type); ?></div>

            <div class="presented">This Honor is Bestowed Upon</div>
            <div class="student"><?php echo e($student?->full_name); ?></div>
            <div class="name-line"></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->admission_number || $student?->schoolClass?->name): ?>
                <div class="student-meta">
                    <?php echo e($student?->admission_number); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->schoolClass?->name): ?>
                        &bull; <?php echo e($student?->schoolClass?->name); ?> <?php echo e($student?->section?->name); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="body"><?php echo e($certificate->body); ?></div>
            <div class="copper-seal"><span>HONOR<br>SEAL</span></div>

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
                    <div class="sig-name"><?php echo e($sig1Name); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="sig">
                <div class="copper-seal" style="width:56px;height:56px;margin:0 auto;font-size:8px;"><span>SEAL</span>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig2Label): ?>
                <div class="sig">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig2ImagePath && file_exists($sig2ImagePath)): ?>
                        <img class="sig-img" src="<?php echo e($sig2ImagePath); ?>" alt="">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="sig-line"></div>
                    <div class="sig-label"><?php echo e($sig2Label); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sig2Name): ?>
                    <div class="sig-name"><?php echo e($sig2Name); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</body>

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/certificate-sahara.blade.php ENDPATH**/ ?>