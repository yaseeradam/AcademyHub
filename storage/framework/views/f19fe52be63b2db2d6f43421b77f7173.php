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
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            color: #1a0000;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            background: #fafafa;
            overflow: hidden;
        }

        /* Bold left red panel */
        .left-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(180deg, #8b0000, #c41e1e, #e63946, #c41e1e, #8b0000);
        }

        /* Subtle pattern on red panel */
        .left-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100px;
            height: 100%;
            background: repeating-linear-gradient(0deg,
                    transparent, transparent 20px,
                    rgba(0, 0, 0, 0.06) 20px, rgba(0, 0, 0, 0.06) 22px);
        }

        /* Top black accent bar */
        .top-bar {
            position: absolute;
            top: 0;
            left: 100px;
            right: 0;
            height: 8px;
            background: #1a0000;
        }

        /* Bottom black accent bar */
        .bottom-bar {
            position: absolute;
            bottom: 0;
            left: 100px;
            right: 0;
            height: 8px;
            background: #1a0000;
        }

        /* Red accent line below top bar */
        .accent-red-line {
            position: absolute;
            top: 8px;
            left: 100px;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #e63946, #c41e1e, transparent);
        }

        /* Content area frame */
        .frame {
            position: absolute;
            top: 24px;
            left: 116px;
            right: 24px;
            bottom: 24px;
            border: 1px solid #e0d0d0;
        }

        /* School name in left panel */
        .panel-label {
            position: absolute;
            top: 30px;
            left: 10px;
            width: 80px;
            text-align: center;
        }

        .panel-label-text {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1.4;
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

        .content {
            position: absolute;
            top: 40px;
            left: 130px;
            right: 40px;
            bottom: 40px;
            z-index: 1;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-section {
            margin-bottom: 8px;
        }

        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 8px;
            padding: 4px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.4);
        }

        .title-block {
            margin-bottom: 6px;
        }

        .title-main {
            font-size: 38px;
            font-weight: 800;
            color: #1a0000;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .title-of {
            font-size: 16px;
            font-style: italic;
            color: #e63946;
            font-weight: 600;
            margin: 2px 0;
        }

        .title-type {
            font-size: 22px;
            font-weight: 800;
            color: #c41e1e;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .red-line {
            width: 80px;
            height: 3px;
            margin: 14px auto;
            background: #e63946;
        }

        .presented {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #888;
        }

        .student {
            margin-top: 10px;
            font-size: 30px;
            font-weight: 800;
            color: #1a0000;
            font-style: italic;
        }

        .name-accent {
            width: 260px;
            margin: 6px auto 0;
            border-top: 3px solid #e63946;
        }

        .student-meta {
            margin-top: 8px;
            font-size: 10px;
            color: #888;
            font-weight: 600;
        }

        .body {
            margin: 14px auto 0;
            width: 80%;
            font-size: 12px;
            line-height: 1.8;
            color: #555;
            font-style: italic;
        }

        .red-badge {
            margin: 12px auto 0;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b0000, #c41e1e, #e63946);
            border: 3px solid #5a0000;
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
            color: #aaa;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            left: 140px;
            right: 40px;
            bottom: 44px;
            display: table;
            width: calc(100% - 180px);
        }

        .sig {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 8px;
        }

        .sig-img {
            height: 40px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .sig-line {
            margin: 6px auto 4px;
            width: 80%;
            border-top: 1px solid #c41e1e;
        }

        .sig-label {
            font-size: 8px;
            font-weight: 700;
            color: #c41e1e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sig-name {
            margin-top: 2px;
            font-size: 8px;
            color: #888;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="page">
        <div class="left-panel"></div>
        <div class="left-pattern"></div>
        <div class="top-bar"></div>
        <div class="bottom-bar"></div>
        <div class="accent-red-line"></div>
        <div class="frame"></div>

        <div class="panel-label">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLogo && $logoPath && file_exists($logoPath)): ?>
                <img class="logo" src="<?php echo e($logoPath); ?>" alt="" style="margin-bottom:8px;">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="panel-label-text"><?php echo e($schoolName); ?></div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($watermarkPath && file_exists($watermarkPath)): ?>
            <img class="watermark" src="<?php echo e($watermarkPath); ?>" alt="">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="content">
            <div class="title-block">
                <div class="title-main">Certificate</div>
                <div class="title-of">of</div>
                <div class="title-type"><?php echo e($certificate->type ?: 'Achievement'); ?></div>
            </div>

            <div class="red-line"></div>

            <div class="presented">This is Proudly Presented To</div>
            <div class="student"><?php echo e($student?->full_name); ?></div>
            <div class="name-accent"></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->admission_number || $student?->schoolClass?->name): ?>
                <div class="student-meta">
                    <?php echo e($student?->admission_number); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->schoolClass?->name): ?>
                        &bull; <?php echo e($student?->schoolClass?->name); ?> <?php echo e($student?->section?->name); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="body"><?php echo e($certificate->body); ?></div>
            <div class="red-badge"><span>SUPREME<br>AWARD</span></div>

            <div class="meta-row">
                <span><?php echo e($certificate->serial_number); ?></span>
                <span style="margin-left: 14px;"><?php echo e($certificate->issued_on?->format('F j, Y')); ?></span>
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
            <div class="sig"></div>
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

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/certificate-crimson.blade.php ENDPATH**/ ?>