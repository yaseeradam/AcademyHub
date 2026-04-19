<?php
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $logo = config('myacademy.school_logo');
    $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;

    $borderColor = '#d4af37'; // Gold
    $accentColor = '#1e3a5f'; // Navy Blue
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
            margin: 14px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Serif, Georgia, serif;
            color: #1e3a5f;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #fffef5 0%, #fef9e7 50%, #fff8e1 100%);
            padding: 12px;
        }

        .border-outer {
            position: absolute;
            top: 18px;
            left: 18px;
            right: 18px;
            bottom: 18px;
            border: 6px solid
                <?php echo e($borderColor); ?>

            ;
            border-radius: 4px;
        }

        .border-inner {
            position: absolute;
            top: 32px;
            left: 32px;
            right: 32px;
            bottom: 32px;
            border: 2px solid
                <?php echo e($accentColor); ?>

            ;
        }

        /* Decorative corners */
        .corner {
            position: absolute;
            width: 60px;
            height: 60px;
        }

        .corner-tl {
            top: 44px;
            left: 44px;
            border-top: 4px solid
                <?php echo e($borderColor); ?>

            ;
            border-left: 4px solid
                <?php echo e($borderColor); ?>

            ;
        }

        .corner-tr {
            top: 44px;
            right: 44px;
            border-top: 4px solid
                <?php echo e($borderColor); ?>

            ;
            border-right: 4px solid
                <?php echo e($borderColor); ?>

            ;
        }

        .corner-bl {
            bottom: 44px;
            left: 44px;
            border-bottom: 4px solid
                <?php echo e($borderColor); ?>

            ;
            border-left: 4px solid
                <?php echo e($borderColor); ?>

            ;
        }

        .corner-br {
            bottom: 44px;
            right: 44px;
            border-bottom: 4px solid
                <?php echo e($borderColor); ?>

            ;
            border-right: 4px solid
                <?php echo e($borderColor); ?>

            ;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            width: 500px;
            height: 500px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 60px 60px 40px;
        }

        .logo-section {
            margin-bottom: 12px;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border: 3px solid
                <?php echo e($borderColor); ?>

            ;
            border-radius: 50%;
            padding: 6px;
            background: #fff;
        }

        .school-name {
            margin-top: 8px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            color:
                <?php echo e($accentColor); ?>

            ;
        }

        .ornament {
            margin: 14px auto;
            width: 200px;
            border-top: 1px solid
                <?php echo e($borderColor); ?>

            ;
            position: relative;
        }

        .ornament::after {
            content: '◆';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            background: #fffef5;
            padding: 0 10px;
            color:
                <?php echo e($borderColor); ?>

            ;
            font-size: 12px;
        }

        .title {
            font-size: 38px;
            font-weight: 700;
            color:
                <?php echo e($accentColor); ?>

            ;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        .type {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color:
                <?php echo e($borderColor); ?>

            ;
        }

        .presented {
            margin-top: 22px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #666;
            font-style: italic;
        }

        .student {
            margin-top: 12px;
            font-size: 32px;
            font-weight: 700;
            color:
                <?php echo e($accentColor); ?>

            ;
            font-style: italic;
            border-bottom: 2px solid
                <?php echo e($borderColor); ?>

            ;
            display: inline-block;
            padding: 4px 30px;
        }

        .student-meta {
            margin-top: 6px;
            font-size: 10px;
            color: #666;
            font-weight: 600;
        }

        .body {
            margin: 20px auto 0;
            width: 80%;
            font-size: 13px;
            line-height: 1.7;
            color: #333;
            font-style: italic;
        }

        .meta-row {
            margin-top: 10px;
            font-size: 10px;
            color: #888;
            font-weight: 600;
        }

        .seal {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 3px solid
                <?php echo e($borderColor); ?>

            ;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color:
                <?php echo e($accentColor); ?>

            ;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            height: 44px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .sig-line {
            margin: 8px auto 5px;
            width: 80%;
            border-top: 1px solid
                <?php echo e($accentColor); ?>

            ;
        }

        .sig-label {
            font-size: 9px;
            font-weight: 700;
            color:
                <?php echo e($accentColor); ?>

            ;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 3px;
            font-size: 9px;
            color: #888;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="border-outer"></div>
        <div class="border-inner"></div>
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

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

            <div class="ornament"></div>

            <div class="title"><?php echo e($certificate->title); ?></div>
            <div class="type"><?php echo e($certificate->type); ?></div>

            <div class="presented">This Certificate is Proudly Presented To</div>
            <div class="student"><?php echo e($student?->full_name); ?></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->admission_number || $student?->schoolClass?->name): ?>
                <div class="student-meta">
                    <?php echo e($student?->admission_number); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->schoolClass?->name): ?>
                        &bull; <?php echo e($student?->schoolClass?->name); ?> <?php echo e($student?->section?->name); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="body"><?php echo e($certificate->body); ?></div>

            <div class="meta-row">
                <span>Serial: <?php echo e($certificate->serial_number); ?></span>
                <span style="margin-left: 16px;">Date: <?php echo e($certificate->issued_on?->format('F j, Y')); ?></span>
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

            <div class="sig">
                <div class="seal">SEAL</div>
            </div>

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

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/certificate-elegant.blade.php ENDPATH**/ ?>