<?php
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $logo = config('myacademy.school_logo');
    $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;

    $borderColor = '#3b82f6'; // Blue-500
    $accentColor = '#2563eb'; // Blue-600
    $innerBorderColor = '#60a5fa'; // Blue-400
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
            margin: 18px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            border: 4px solid
                <?php echo e($borderColor); ?>

            ;
            padding: 22px;
        }

        .inner {
            border: 2px solid
                <?php echo e($accentColor); ?>

            ;
            padding: 18px;
            height: 100%;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            width: 520px;
            height: 520px;
            object-fit: contain;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .header-left {
            width: 110px;
        }

        .logo {
            width: 88px;
            height: 88px;
            object-fit: contain;
            border: 3px solid
                <?php echo e($accentColor); ?>

            ;
            border-radius: 8px;
            padding: 6px;
            background: #fff;
        }

        .school-name {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .divider {
            margin: 12px auto 16px;
            width: 70%;
            border-top: 2px solid
                <?php echo e($accentColor); ?>

            ;
        }

        .title {
            font-size: 32px;
            font-weight: 800;
            margin-top: 6px;
        }

        .type {
            margin-top: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #334155;
        }

        .presented {
            margin-top: 18px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #475569;
        }

        .student {
            margin-top: 10px;
            font-size: 26px;
            font-weight: 800;
            color:
                <?php echo e($accentColor); ?>

            ;
        }

        .student-meta {
            margin-top: 6px;
            font-size: 10px;
            color: #475569;
            font-weight: 600;
        }

        .body {
            margin: 18px auto 0;
            width: 85%;
            font-size: 14px;
            line-height: 1.6;
            color: #0f172a;
        }

        .footer {
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 28px;
            display: table;
            width: calc(100% - 44px);
        }

        .sig {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 12px;
        }

        .sig-line {
            margin: 8px auto 6px;
            width: 85%;
            border-top: 1px solid #94a3b8;
        }

        .sig-label {
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 4px;
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
        }

        .sig-img {
            height: 46px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .meta-row {
            margin-top: 8px;
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="inner">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($watermarkPath && file_exists($watermarkPath)): ?>
                <img class="watermark" src="<?php echo e($watermarkPath); ?>" alt="">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="content">
                <div class="header">
                    <div class="header-cell header-left">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoPath && file_exists($logoPath)): ?>
                            <img class="logo" src="<?php echo e($logoPath); ?>" alt="">
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="header-cell">
                        <div class="school-name"><?php echo e($schoolName); ?></div>
                        <?php ($address = config('myacademy.school_address')); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($address): ?>
                            <div style="margin-top: 4px; font-size: 10px; color: #475569; font-weight: 600;">
                                <?php echo e($address); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="header-cell header-left"></div>
                </div>

                <div class="divider"></div>

                <div class="title"><?php echo e($certificate->title); ?></div>
                <div class="type"><?php echo e($certificate->type); ?></div>

                <div class="presented">Presented to</div>
                <div class="student"><?php echo e($student?->full_name); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->admission_number || $student?->schoolClass?->name): ?>
                    <div class="student-meta">
                        <?php echo e($student?->admission_number); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->schoolClass?->name): ?>
                            • <?php echo e($student?->schoolClass?->name); ?> <?php echo e($student?->section?->name); ?>

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
    </div>
</body>

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/certificate-classic.blade.php ENDPATH**/ ?>