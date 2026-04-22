<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Report Card - <?php echo e($student->admission_number); ?></title>
    <style>
        @page {
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            background: white;
        }

        /* ─── Classic Traditional ─── */
        .page {
            border: 2px solid #111827;
            padding: 3px;
            background: white;
        }

        .page-inner {
            border: 1px solid #6b7280;
            padding: 18px;
        }

        .header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 14px;
            border-bottom: 3px double #111827;
        }

        .header-table {
            display: table;
            width: 100%;
        }

        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
        }

        .logo {
            width: 66px;
            height: 66px;
            object-fit: contain;
            border: 2px solid #374151;
            border-radius: 6px;
            padding: 3px;
            background: white;
        }

        .school-name {
            font-size: 22px;
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 3px;
        }

        .school-info {
            font-size: 9px;
            color: #4b5563;
            margin-bottom: 2px;
        }

        .report-title {
            display: inline-block;
            margin-top: 7px;
            font-size: 13px;
            font-weight: 800;
            color: #111827;
            border: 2px solid #111827;
            padding: 5px 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .meta-cell {
            display: table-cell;
            width: 33.33%;
            font-size: 10px;
        }

        .meta-label {
            font-weight: 800;
            color: #111827;
            text-transform: uppercase;
            font-size: 8px;
            display: inline;
        }

        .meta-value {
            font-weight: 700;
            color: #374151;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
        }

        .info-table td {
            padding: 5px 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }

        .info-label {
            background: #f9fafb;
            font-weight: 800;
            font-size: 8px;
            color: #374151;
            text-transform: uppercase;
            width: 18%;
        }

        .info-value {
            font-weight: 700;
            color: #111827;
            width: 32%;
        }

        .photo-td {
            text-align: center;
            vertical-align: top;
            width: 90px;
            padding: 6px;
        }

        .photo {
            width: 78px;
            height: 92px;
            object-fit: cover;
            border: 1px solid #6b7280;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px 4px;
            border-right: 1px solid #d1d5db;
        }

        .summary-cell:last-child {
            border-right: none;
        }

        .summary-label {
            font-size: 8px;
            font-weight: 800;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 800;
            color: #111827;
        }

        table.scores {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #374151;
        }

        table.scores th {
            background: #111827;
            color: white;
            padding: 7px 4px;
            text-align: center;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #374151;
        }

        table.scores td {
            padding: 5px 4px;
            text-align: center;
            font-size: 9px;
            border: 1px solid #d1d5db;
        }

        table.scores tr:nth-child(even) {
            background: #f9fafb;
        }

        .subj {
            text-align: left;
            font-weight: 700;
            padding-left: 8px;
        }

        .bold {
            font-weight: 800;
        }

        .grading-row {
            display: table;
            width: 100%;
            border: 1px solid #d1d5db;
            margin-bottom: 12px;
        }

        .gr-cell {
            display: table-cell;
            padding: 5px 4px;
            text-align: center;
            font-size: 8px;
            font-weight: 600;
            color: #4b5563;
            border-right: 1px solid #d1d5db;
        }

        .gr-cell:last-child {
            border-right: none;
        }

        .gr-cell strong {
            font-size: 10px;
            color: #111827;
        }

        .att-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
        }

        .att-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 8px;
            border-right: 1px solid #d1d5db;
        }

        .att-cell:last-child {
            border-right: none;
        }

        .att-label {
            font-size: 8px;
            font-weight: 800;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .att-value {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }

        .remarks-box {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            margin-bottom: 10px;
        }

        .remarks-label {
            font-size: 8px;
            font-weight: 800;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .remarks-text {
            font-size: 9px;
            color: #374151;
            min-height: 22px;
            border-bottom: 1px solid #9ca3af;
            padding-bottom: 4px;
        }

        .next-term {
            background: #111827;
            color: white;
            text-align: center;
            padding: 7px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 16px;
        }

        .sig {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 6px;
        }

        .sig-line {
            border-top: 1.5px solid #111827;
            margin-top: 30px;
            padding-top: 4px;
            font-size: 9px;
            font-weight: 800;
            color: #111827;
        }

        .sig-sub {
            font-size: 7px;
            color: #6b7280;
            font-style: italic;
            margin-top: 2px;
        }

        .footer {
            margin-top: 10px;
            border-top: 3px double #111827;
            padding-top: 6px;
            text-align: center;
            font-size: 7px;
            color: #9ca3af;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            opacity: 0.03;
            width: 380px;
            height: 380px;
        }
    </style>
</head>

<body>
    <?php
        $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
        $logo = config('myacademy.school_logo');
        $logoPath = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;
        $logoExists = $logoPath && file_exists($logoPath);
    ?>
        <?php
    $opts = $rcOptions ?? [];
    $showPosition = $opts['show_position'] ?? true;
    $showAttendance = $opts['show_attendance'] ?? true;
    $showGradingKey = $opts['show_grading_key'] ?? true;
    $showClassAverage = $opts['show_class_average'] ?? true;
    $showWatermark = $opts['show_watermark'] ?? true;
    $showNextTermDate = $opts['show_next_term_date'] ?? true;
    $showTeacherRemarks = $opts['show_teacher_remarks'] ?? true;
    $showPrincipalRemarks = $opts['show_principal_remarks'] ?? true;
    $showPsychomotor = $opts['show_psychomotor'] ?? false;
    $showSchoolFees = $opts['show_school_fees'] ?? false;
    $showSignatures = $opts['show_signatures'] ?? false;
?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists && $showWatermark): ?>
        <div class="watermark">
            <img src="<?php echo e($logoPath); ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;" />
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="page">
        <div class="page-inner">
            <div class="header">
                <div class="header-table">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists): ?>
                        <div class="header-cell logo-cell">
                            <img src="<?php echo e($logoPath); ?>" alt="Logo" class="logo" />
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="header-cell" style="text-align: center;">
                        <div class="school-name"><?php echo e($schoolName); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_motto')): ?>
                            <div class="school-info" style="font-style: italic;">"<?php echo e(config('myacademy.school_motto')); ?>"
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_address')): ?>
                            <div class="school-info"><?php echo e(config('myacademy.school_address')); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_phone') || config('myacademy.school_email')): ?>
                            <div class="school-info">
                                <?php echo e(config('myacademy.school_phone')); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_phone') && config('myacademy.school_email')): ?> | <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php echo e(config('myacademy.school_email')); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="report-title">Student Report Card</div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists): ?>
                        <div class="header-cell logo-cell">
                            <img src="<?php echo e($logoPath); ?>" alt="Logo" class="logo" />
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="meta-row">
                <div class="meta-cell"><span class="meta-label">Session: </span><span
                        class="meta-value"><?php echo e($session); ?></span></div>
                <div class="meta-cell" style="text-align: center;"><span class="meta-label">Term: </span><span
                        class="meta-value">Term <?php echo e($term); ?></span></div>
                <div class="meta-cell" style="text-align: right;"><span class="meta-label">Date: </span><span
                        class="meta-value"><?php echo e(now()->format('d/m/Y')); ?></span></div>
            </div>

            
                <?php ($siBorderColor = '#374151'); ?>
                <?php ($siBgColor = '#f9fafb'); ?>
                <?php ($siLabelColor = '#374151'); ?>
                <?php ($siValueColor = '#1f2937'); ?>
                <?php ($siDotColor = '#d1d5db'); ?>
                <?php echo $__env->make('pdf.partials.rc-student-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-label">Total Score</div>
                    <div class="summary-value"><?php echo e($grandTotal); ?></div>
                </div>
                <div class="summary-cell">
                    <div class="summary-label">Average</div>
                    <div class="summary-value"><?php echo e(number_format($average, 1)); ?>%</div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPosition): ?>
                <div class="summary-cell">
                    <div class="summary-label">Position</div>
                    <div class="summary-value"><?php echo e($position); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassAverage): ?>
                <div class="summary-cell">
                    <div class="summary-label">Class Average</div>
                    <div class="summary-value"><?php echo e(number_format($classAverage, 1)); ?>%</div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <table class="scores">
                <thead>
                    <tr>
                        <th style="width: 30%; text-align: left; padding-left: 8px;">Subject</th>
                        <th style="width: 10%;">CA1</th>
                        <th style="width: 10%;">CA2</th>
                        <th style="width: 10%;">Exam</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 10%;">Grade</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassAverage): ?><th style="width: 10%;">Class Avg</th><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPosition): ?><th style="width: 10%;">Position</th><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="subj"><?php echo e($row['subject']->name); ?></td>
                            <td><?php echo e($row['ca1'] ?? '—'); ?></td>
                            <td><?php echo e($row['ca2'] ?? '—'); ?></td>
                            <td><?php echo e($row['exam'] ?? '—'); ?></td>
                            <td class="bold"><?php echo e($row['total'] ?? '—'); ?></td>
                            <td class="bold"><?php echo e($row['grade'] ?? '—'); ?></td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassAverage): ?><td><?php echo e($row['class_avg'] ?? '—'); ?></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPosition): ?><td><?php echo e($row['position'] ?? '—'); ?></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGradingKey): ?>
            <div class="grading-row">
                <div class="gr-cell"><strong>A:</strong> 70-100 (Excellent)</div>
                <div class="gr-cell"><strong>B:</strong> 60-69 (Very Good)</div>
                <div class="gr-cell"><strong>C:</strong> 50-59 (Good)</div>
                <div class="gr-cell"><strong>D:</strong> 40-49 (Pass)</div>
                <div class="gr-cell"><strong>F:</strong> 0-39 (Fail)</div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAttendance): ?>
            <div class="att-row">
                <div class="att-cell">
                    <div class="att-label">Times Opened</div>
                    <div class="att-value"><?php echo e($timesOpened ?? '—'); ?></div>
                </div>
                <div class="att-cell">
                    <div class="att-label">Times Present</div>
                    <div class="att-value"><?php echo e($timesPresent ?? '—'); ?></div>
                </div>
                <div class="att-cell">
                    <div class="att-label">Times Absent</div>
                    <div class="att-value"><?php echo e($timesAbsent ?? '—'); ?></div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTeacherRemarks): ?>
            <div class="remarks-box">
                <div class="remarks-label">Class Teacher's Remarks</div>
                <div class="remarks-text"><?php echo e($teacherRemarks ?? 'No remarks provided.'); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPrincipalRemarks): ?>
            <div class="remarks-box">
                <div class="remarks-label">Principal's Remarks</div>
                <div class="remarks-text"><?php echo e($principalRemarks ?? 'No remarks provided.'); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNextTermDate): ?>
            <div class="next-term">
                Next Term Begins: <?php echo e($nextTermDate ?? 'To be announced'); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
                <?php ($rcBorderColor = '#374151'); ?>
                <?php ($rcBgLight = '#f9fafb'); ?>
                <?php ($rcTitleColor = '#374151'); ?>
                <?php ($rcLabelColor = '#374151'); ?>
                <?php echo $__env->make('pdf.partials.rc-psychomotor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make('pdf.partials.rc-school-fees', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSignatures): ?>
<div class="signatures">
                <div class="sig">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher'])): ?>
                            <img src="<?php echo e($signatureImages['teacher']); ?>" alt="Teacher Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="sig-line">Class Teacher</div>
                    <div class="sig-sub">Signature & Date</div>
                </div>
                <div class="sig">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal'])): ?>
                            <img src="<?php echo e($signatureImages['principal']); ?>" alt="Principal Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="sig-line">Principal</div>
                    <div class="sig-sub">Signature & Stamp</div>
                </div>
                <div class="sig">
                    <div class="sig-line">Parent/Guardian</div>
                    <div class="sig-sub">Signature & Date</div>
                </div>
            </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="footer">
                Generated on <?php echo e(now()->format('l, F j, Y \a\t g:i A')); ?> • <?php echo e($schoolName); ?> • Powered by MyAcademy SMS
            </div>
        </div>
    </div>
</body>

</html><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/report-card-classic.blade.php ENDPATH**/ ?>