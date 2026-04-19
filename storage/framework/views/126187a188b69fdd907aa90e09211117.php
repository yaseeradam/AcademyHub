<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Timetable – <?php echo e($class->name); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Header layout ── */
        .header-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 60px;
            padding-right: 12px;
        }

        .logo-cell img {
            width: 55px;
            height: 55px;
            border-radius: 6px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #0c4a6e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .school-details {
            font-size: 8px;
            color: #64748b;
        }

        /* ── Title bar ── */
        .title-bar {
            width: 100%;
            border: none;
            border-collapse: collapse;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .title-bar td {
            background: #0ea5e9;
            color: white;
            padding: 8px 16px;
            border: none;
            vertical-align: middle;
        }

        .title-bar td:first-child {
            border-radius: 6px 0 0 6px;
        }

        .title-bar td:last-child {
            border-radius: 0 6px 6px 0;
            text-align: right;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            font-size: 9px;
            opacity: 0.9;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        /* ── Timetable grid ── */
        .timetable {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #cbd5e1;
        }

        .timetable thead tr {
            background: #1e40af;
        }

        .timetable th {
            color: #ffffff;
            padding: 8px 4px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #3b82f6;
        }

        .timetable th.time-col {
            width: 11%;
            background: #1e3a8a;
        }

        .timetable tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .timetable tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .timetable td {
            padding: 4px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .timetable td.time-cell {
            background: #f1f5f9;
            font-weight: bold;
            text-align: center;
            color: #334155;
            font-size: 8px;
            vertical-align: middle;
            border-right: 2px solid #cbd5e1;
        }

        /* ── Entry card ── */
        .entry-card {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-left: 3px solid #3b82f6;
            border-radius: 4px;
            padding: 4px 5px;
        }

        .entry-subject {
            font-size: 9px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 2px;
        }

        .entry-teacher {
            font-size: 7.5px;
            color: #3b82f6;
            margin-bottom: 1px;
        }

        .entry-room {
            font-size: 7px;
            color: #60a5fa;
            font-weight: 600;
        }

        .empty-cell {
            text-align: center;
            color: #cbd5e1;
            font-size: 11px;
            padding: 8px 0;
        }

        /* ── Footer ── */
        .footer-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-top: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        .footer-table td {
            border: none;
            font-size: 7px;
            color: #94a3b8;
            padding: 4px 0 0 0;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    
    <table class="header-table">
        <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoBase64): ?>
                <td class="logo-cell">
                    <img src="<?php echo e($logoBase64); ?>" alt="Logo">
                </td>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <td>
                <div class="school-name"><?php echo e($schoolName); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolAddress || $schoolPhone || $schoolEmail): ?>
                    <div class="school-details">
                        <?php echo e(implode('  •  ', array_filter([$schoolAddress, $schoolPhone, $schoolEmail]))); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </td>
        </tr>
    </table>

    
    <table class="title-bar">
        <tr>
            <td>
                <div class="doc-title">Weekly Timetable</div>
                <div class="doc-subtitle">
                    <?php echo e($class->name); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($section): ?> — <?php echo e($section->name); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </td>
            <td style="text-align: right;">
                <span class="badge"><?php echo e($termLabel); ?></span>
                <span class="badge" style="margin-left: 4px;"><?php echo e($sessionLabel); ?></span>
            </td>
        </tr>
    </table>

    
    <table class="timetable">
        <thead>
            <tr>
                <th class="time-col">Time</th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayNum => $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($dayName); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="time-cell"><?php echo e($slot['label']); ?></td>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayNum => $dayName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($slotMap[$dayNum][$slot['key']])): ?>
                    <?php ($entry = $slotMap[$dayNum][$slot['key']]); ?>
                        <div class="entry-card">
                            <div class="entry-subject"><?php echo e($entry->subject?->name ?? 'N/A'); ?></div>
                            <div class="entry-teacher"><?php echo e($entry->teacher?->name ?? 'No Teacher'); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($entry->room): ?>
                                <div class="entry-room">Room: <?php echo e($entry->room); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                    <div class="empty-cell">—</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8; font-size: 11px;">
                    No timetable entries found for this class.
                </td>
            </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>

    
    <table class="footer-table">
        <tr>
            <td><?php echo e($schoolName); ?> &bull; <?php echo e($termLabel); ?> &bull; <?php echo e($sessionLabel); ?> Session</td>
            <td style="text-align: right;">Generated on <?php echo e(now()->format('F j, Y \a\t g:i A')); ?></td>
        </tr>
    </table>

</body>

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/timetable.blade.php ENDPATH**/ ?>