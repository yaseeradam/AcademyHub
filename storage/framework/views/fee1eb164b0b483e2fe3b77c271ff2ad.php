<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Report Card - <?php echo e($student->admission_number); ?></title>
        <style>
            @page { margin: 15mm; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 10px;
                color: #1e293b;
                background: #fff;
            }

            /* ─── Elegant Navy & Gold ─── */
            .page {
                border: 4px solid #1e3a5f;
                padding: 4px;
                background: white;
            }
            .page-inner {
                border: 1px solid #c4975a;
                padding: 20px;
            }

            .header {
                text-align: center;
                padding-bottom: 14px;
                margin-bottom: 14px;
                border-bottom: 2px solid #1e3a5f;
            }
            .header::after {
                content: '';
                display: block;
                margin-top: 4px;
                height: 1px;
                background: #c4975a;
            }
            .header-table { display: table; width: 100%; }
            .header-cell { display: table-cell; vertical-align: middle; }
            .logo-cell { width: 85px; text-align: center; }
            .logo {
                width: 68px;
                height: 68px;
                object-fit: contain;
                border: 2px solid #c4975a;
                border-radius: 8px;
                padding: 4px;
                background: white;
            }
            .school-name {
                font-size: 22px;
                font-weight: 800;
                color: #1e3a5f;
                text-transform: uppercase;
                letter-spacing: 3px;
                margin-bottom: 4px;
            }
            .school-motto {
                font-size: 9px;
                color: #c4975a;
                font-style: italic;
                font-weight: 600;
                margin-bottom: 4px;
            }
            .school-info {
                font-size: 8px;
                color: #64748b;
                margin-bottom: 2px;
            }
            .report-badge {
                display: inline-block;
                margin-top: 8px;
                background: #1e3a5f;
                color: #c4975a;
                padding: 6px 22px;
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 4px;
                border: 1px solid #c4975a;
            }

            .meta-bar {
                display: table;
                width: 100%;
                margin-bottom: 12px;
                border: 1px solid #e2e8f0;
            }
            .meta-cell {
                display: table-cell;
                padding: 7px 10px;
                border-right: 1px solid #e2e8f0;
                text-align: center;
            }
            .meta-cell:last-child { border-right: none; }
            .meta-label {
                font-size: 7px;
                font-weight: 700;
                text-transform: uppercase;
                color: #1e3a5f;
                letter-spacing: 1px;
                display: block;
                margin-bottom: 2px;
            }
            .meta-value {
                font-size: 11px;
                font-weight: 800;
                color: #1e293b;
            }

            .info-section {
                display: table;
                width: 100%;
                margin-bottom: 12px;
                border: 1px solid #e2e8f0;
            }
            .info-left {
                display: table-cell;
                vertical-align: top;
                padding: 0;
            }
            .info-row {
                display: table;
                width: 100%;
                border-bottom: 1px solid #f1f5f9;
            }
            .info-label {
                display: table-cell;
                width: 35%;
                padding: 5px 8px;
                background: #f8fafc;
                font-size: 8px;
                font-weight: 700;
                color: #1e3a5f;
                text-transform: uppercase;
                border-right: 1px solid #e2e8f0;
            }
            .info-value {
                display: table-cell;
                padding: 5px 8px;
                font-size: 10px;
                font-weight: 700;
                color: #1e293b;
            }
            .photo-cell {
                display: table-cell;
                width: 95px;
                vertical-align: top;
                text-align: center;
                padding: 6px;
                border-left: 1px solid #e2e8f0;
            }
            .photo {
                width: 80px;
                height: 95px;
                object-fit: cover;
                border: 1px solid #c4975a;
            }

            .stats-bar {
                display: table;
                width: 100%;
                margin-bottom: 12px;
            }
            .stat {
                display: table-cell;
                width: 25%;
                padding: 3px;
            }
            .stat-inner {
                border: 1px solid #e2e8f0;
                text-align: center;
                padding: 8px 4px;
            }
            .stat-label {
                font-size: 7px;
                font-weight: 700;
                color: #1e3a5f;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 3px;
            }
            .stat-value {
                font-size: 18px;
                font-weight: 800;
                color: #1e3a5f;
            }
            .stat-inner.gold { border-color: #c4975a; }
            .stat-inner.gold .stat-value { color: #c4975a; }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 12px;
            }
            th {
                background: #1e3a5f;
                color: #c4975a;
                padding: 7px 4px;
                text-align: center;
                font-size: 8px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border: 1px solid #1e3a5f;
            }
            td {
                padding: 5px 4px;
                text-align: center;
                font-size: 9px;
                border: 1px solid #e2e8f0;
            }
            tr:nth-child(even) { background: #f8fafc; }
            .subj { text-align: left; font-weight: 700; color: #1e3a5f; padding-left: 8px; }
            .bold { font-weight: 800; }

            .grading {
                display: table;
                width: 100%;
                border: 1px solid #e2e8f0;
                margin-bottom: 12px;
            }
            .grading-cell {
                display: table-cell;
                padding: 6px 4px;
                text-align: center;
                font-size: 8px;
                font-weight: 600;
                color: #475569;
                border-right: 1px solid #e2e8f0;
            }
            .grading-cell:last-child { border-right: none; }
            .grading-cell strong { color: #1e3a5f; font-size: 10px; }

            .attendance {
                display: table;
                width: 100%;
                margin-bottom: 12px;
                border: 1px solid #e2e8f0;
            }
            .att-cell {
                display: table-cell;
                width: 33.33%;
                text-align: center;
                padding: 8px;
                border-right: 1px solid #e2e8f0;
            }
            .att-cell:last-child { border-right: none; }
            .att-label {
                font-size: 7px;
                font-weight: 700;
                text-transform: uppercase;
                color: #1e3a5f;
                margin-bottom: 3px;
            }
            .att-value { font-size: 16px; font-weight: 800; color: #1e293b; }

            .remarks {
                border: 1px solid #e2e8f0;
                padding: 8px 10px;
                margin-bottom: 10px;
            }
            .remarks-label {
                font-size: 8px;
                font-weight: 800;
                color: #1e3a5f;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 4px;
            }
            .remarks-text {
                font-size: 9px;
                color: #374151;
                min-height: 25px;
                border-bottom: 1px solid #c4975a;
                padding-bottom: 4px;
            }

            .next-term {
                background: #1e3a5f;
                color: #c4975a;
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
                margin-top: 18px;
            }
            .sig {
                display: table-cell;
                width: 33.33%;
                text-align: center;
                padding: 6px;
            }
            .sig-line {
                border-top: 1.5px solid #1e3a5f;
                margin-top: 32px;
                padding-top: 4px;
                font-size: 9px;
                font-weight: 800;
                color: #1e3a5f;
            }
            .sig-sub { font-size: 7px; color: #94a3b8; font-style: italic; margin-top: 2px; }

            .footer {
                margin-top: 12px;
                border-top: 2px solid #1e3a5f;
                padding-top: 6px;
                text-align: center;
                font-size: 7px;
                color: #94a3b8;
            }
            .footer::before {
                content: '';
                display: block;
                margin-bottom: 4px;
                height: 1px;
                background: #c4975a;
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
            $logo       = config('myacademy.school_logo');
            $logoPath   = $logo ? public_path('uploads/' . str_replace('\\', '/', $logo)) : null;
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
                                <div class="school-motto">"<?php echo e(config('myacademy.school_motto')); ?>"</div>
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
                            <div class="report-badge">Report Card</div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logoExists): ?>
                            <div class="header-cell logo-cell">
                                <img src="<?php echo e($logoPath); ?>" alt="Logo" class="logo" />
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="meta-bar">
                    <div class="meta-cell">
                        <span class="meta-label">Session</span>
                        <span class="meta-value"><?php echo e($session); ?></span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Term</span>
                        <span class="meta-value">Term <?php echo e($term); ?></span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Date Issued</span>
                        <span class="meta-value"><?php echo e(now()->format('d M, Y')); ?></span>
                    </div>
                </div>

                
                <?php ($siBorderColor = '#1e3a5f'); ?>
                <?php ($siBgColor = '#f8fafc'); ?>
                <?php ($siLabelColor = '#1e3a5f'); ?>
                <?php ($siValueColor = '#1e293b'); ?>
                <?php ($siDotColor = '#c4975a'); ?>
                <?php echo $__env->make('pdf.partials.rc-student-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


                <div class="stats-bar">
                    <div class="stat">
                        <div class="stat-inner gold">
                            <div class="stat-label">Total Score</div>
                            <div class="stat-value"><?php echo e($grandTotal); ?></div>
                        </div>
                    </div>
                    <div class="stat">
                        <div class="stat-inner">
                            <div class="stat-label">Average</div>
                            <div class="stat-value"><?php echo e(number_format($average, 1)); ?>%</div>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPosition): ?>
                    <div class="stat">
                        <div class="stat-inner">
                            <div class="stat-label">Position</div>
                            <div class="stat-value"><?php echo e($position); ?></div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassAverage): ?>
                    <div class="stat">
                        <div class="stat-inner">
                            <div class="stat-label">Class Average</div>
                            <div class="stat-value"><?php echo e(number_format($classAverage, 1)); ?>%</div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <table>
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
                <div class="grading">
                    <div class="grading-cell"><strong>A:</strong> 70-100 Excellent</div>
                    <div class="grading-cell"><strong>B:</strong> 60-69 Very Good</div>
                    <div class="grading-cell"><strong>C:</strong> 50-59 Good</div>
                    <div class="grading-cell"><strong>D:</strong> 40-49 Pass</div>
                    <div class="grading-cell"><strong>F:</strong> 0-39 Fail</div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAttendance): ?>
                <div class="attendance">
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
                <div class="remarks">
                    <div class="remarks-label">Class Teacher's Remarks</div>
                    <div class="remarks-text"><?php echo e($teacherRemarks ?? 'No remarks provided.'); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPrincipalRemarks): ?>
                <div class="remarks">
                    <div class="remarks-label">Principal's Remarks</div>
                    <div class="remarks-text"><?php echo e($principalRemarks ?? 'No remarks provided.'); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNextTermDate): ?>
                <div class="next-term">
                    Next Term Begins: <?php echo e($nextTermDate ?? 'To be announced'); ?>

                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php ($rcBorderColor = '#1e3a5f'); ?>
                <?php ($rcBgLight = '#f8fafc'); ?>
                <?php ($rcTitleColor = '#1e3a5f'); ?>
                <?php ($rcLabelColor = '#1e3a5f'); ?>
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
</html>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/report-card-elegant.blade.php ENDPATH**/ ?>