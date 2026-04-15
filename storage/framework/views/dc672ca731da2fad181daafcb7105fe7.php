<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Report Card - <?php echo e($student->admission_number); ?></title>
    <style>
        @page { margin: 10mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #7c2d12; }

        /* SUNSET: Warm orange/red, sun rays, energetic design */
        .page { background: linear-gradient(180deg, #fff7ed 0%, #fed7aa 50%, #fdba74 100%); padding: 14px; position: relative; overflow: hidden; }
        
        .sun-rays { position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 200px; height: 100px; background: radial-gradient(circle, rgba(251,146,60,0.2) 0%, transparent 70%); }
        
        .header { text-align: center; margin-bottom: 10px; position: relative; z-index: 1; }
        .sun-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; border: 4px solid #fed7aa; box-shadow: 0 0 20px rgba(249,115,22,0.4); }
        .school-name { font-size: 18px; font-weight: 900; color: #c2410c; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; text-shadow: 1px 1px 2px rgba(194,65,12,0.2); }
        .sunset-badge { display: inline-block; background: linear-gradient(135deg, #f97316, #dc2626); color: white; padding: 5px 16px; border-radius: 20px; font-size: 9px; font-weight: 800; letter-spacing: 1px; box-shadow: 0 2px 8px rgba(249,115,22,0.3); }
        
        .horizon-bar { height: 3px; background: linear-gradient(90deg, transparent 0%, #f97316 50%, transparent 100%); margin: 8px 0; }
        
        .session-strip { background: white; border-left: 4px solid #f97316; border-right: 4px solid #dc2626; padding: 6px; text-align: center; margin-bottom: 10px; font-size: 8px; font-weight: 700; color: #c2410c; }
        
        .student-sunset { background: white; border-radius: 12px; padding: 10px; margin-bottom: 10px; border: 3px solid #fb923c; box-shadow: 0 4px 12px rgba(249,115,22,0.2); }
        .student-grid { display: table; width: 100%; }
        .student-col { display: table-cell; width: 50%; padding: 4px; }
        .student-item { margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1px dashed #fdba74; }
        .student-label { font-size: 7px; color: #ea580c; font-weight: 800; text-transform: uppercase; }
        .student-value { font-size: 9px; color: #7c2d12; font-weight: 700; }
        
        .stats-sunset { display: table; width: 100%; margin-bottom: 10px; }
        .stat-sunset { display: table-cell; width: 16.66%; padding: 2px; }
        .stat-glow { background: white; border: 2px solid #fb923c; border-radius: 10px; text-align: center; padding: 8px 4px; box-shadow: 0 2px 8px rgba(251,146,60,0.2); }
        .stat-label { font-size: 6px; color: #ea580c; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .stat-value { font-size: 14px; color: #c2410c; font-weight: 900; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; background: white; border-radius: 10px; overflow: hidden; border: 2px solid #fb923c; }
        th { background: linear-gradient(135deg, #f97316, #dc2626); color: white; padding: 6px 3px; font-size: 7px; font-weight: 800; text-transform: uppercase; }
        td { padding: 5px 3px; font-size: 8px; border-bottom: 1px solid #fed7aa; text-align: center; }
        tr:nth-child(even) td { background: #fff7ed; }
        .subj { text-align: left; font-weight: 700; color: #c2410c; padding-left: 6px; }
        
        .grade-sunset { background: white; border: 2px solid #fb923c; border-radius: 8px; padding: 6px; margin-bottom: 10px; text-align: center; font-size: 7px; color: #6b7280; }
        .grade-sunset strong { color: #ea580c; font-size: 9px; margin: 0 4px; }
        
        .remarks-sunset { background: white; border: 2px solid #fb923c; border-radius: 10px; padding: 8px; margin-bottom: 8px; box-shadow: 0 2px 6px rgba(251,146,60,0.15); }
        .remarks-title { font-size: 8px; color: #ea580c; font-weight: 800; text-transform: uppercase; margin-bottom: 3px; }
        .remarks-text { font-size: 8px; color: #4b5563; min-height: 20px; }
        
        .next-sunset { background: linear-gradient(135deg, #f97316, #dc2626); color: white; text-align: center; padding: 6px; border-radius: 10px; font-size: 9px; font-weight: 800; margin-bottom: 8px; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        
        .sigs { display: table; width: 100%; }
        .sig { display: table-cell; width: 33.33%; text-align: center; padding: 4px; }
        .sig-line { border-top: 2px solid #f97316; margin-top: 25px; padding-top: 3px; font-size: 8px; font-weight: 700; color: #c2410c; }
        
        .footer { text-align: center; font-size: 6px; color: #9ca3af; margin-top: 6px; }
    </style>
</head>
<body>
    <?php $schoolName = config('myacademy.school_name', 'MyAcademy'); ?>
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
    
    <div class="page">
        <div class="sun-rays"></div>
        
        <div class="header">
            <div class="sun-icon">☀</div>
            <div class="school-name"><?php echo e($schoolName); ?></div>
            <div class="sunset-badge">STUDENT REPORT CARD</div>
        </div>
        
        <div class="horizon-bar"></div>
        
        <div class="session-strip">
            Academic Session <?php echo e($session); ?> | Term <?php echo e($term); ?> | Generated <?php echo e(now()->format('F d, Y')); ?>

        </div>
        
        <div class="student-sunset">
            
                <?php ($siBorderColor = '#f97316'); ?>
                <?php ($siBgColor = '#fff7ed'); ?>
                <?php ($siLabelColor = '#c2410c'); ?>
                <?php ($siValueColor = '#1f2937'); ?>
                <?php ($siDotColor = '#fdba74'); ?>
                <?php echo $__env->make('pdf.partials.rc-student-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </div>
        
        <div class="stats-sunset">
            <div class="stat-sunset">
                <div class="stat-glow">
                    <div class="stat-label">Total</div>
                    <div class="stat-value"><?php echo e($grandTotal); ?></div>
                </div>
            </div>
            <div class="stat-sunset">
                <div class="stat-glow">
                    <div class="stat-label">Average</div>
                    <div class="stat-value"><?php echo e(number_format($average, 1)); ?>%</div>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPosition): ?>
            <div class="stat-sunset">
                <div class="stat-glow">
                    <div class="stat-label">Position</div>
                    <div class="stat-value"><?php echo e($position); ?></div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showClassAverage): ?>
            <div class="stat-sunset">
                <div class="stat-glow">
                    <div class="stat-label">Class Avg</div>
                    <div class="stat-value"><?php echo e(number_format($classAverage, 1)); ?>%</div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="stat-sunset">
                <div class="stat-glow">
                    <div class="stat-label">Highest</div>
                    <div class="stat-value"><?php echo e(number_format($highestAverage ?? 0, 1)); ?>%</div>
                </div>
            </div>
            <div class="stat-sunset">
                <div class="stat-glow">
                    <div class="stat-label">Lowest</div>
                    <div class="stat-value"><?php echo e(number_format($lowestAverage ?? 0, 1)); ?>%</div>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 35%; text-align: left; padding-left: 6px;">Subject</th>
                    <th style="width: 11%;">CA1</th>
                    <th style="width: 11%;">CA2</th>
                    <th style="width: 11%;">Exam</th>
                    <th style="width: 11%;">Total</th>
                    <th style="width: 10%;">Grade</th>
                    <th style="width: 11%;">Avg</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="subj"><?php echo e($row['subject']->name); ?></td>
                        <td><?php echo e($row['ca1'] ?? '—'); ?></td>
                        <td><?php echo e($row['ca2'] ?? '—'); ?></td>
                        <td><?php echo e($row['exam'] ?? '—'); ?></td>
                        <td><strong><?php echo e($row['total'] ?? '—'); ?></strong></td>
                        <td><strong><?php echo e($row['grade'] ?? '—'); ?></strong></td>
                        <td><?php echo e($row['class_avg'] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGradingKey): ?>
        <div class="grade-sunset">
            <strong>A:</strong> 70-100 | <strong>B:</strong> 60-69 | <strong>C:</strong> 50-59 | <strong>D:</strong> 40-49 | <strong>F:</strong> 0-39
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPrincipalRemarks): ?>
        <div class="remarks-sunset">
            <div class="remarks-title">Principal's Remarks</div>
            <div class="remarks-text"><?php echo e($principalRemarks ?? 'No remarks provided.'); ?></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNextTermDate): ?>
        <div class="next-sunset">☀ Next Term Begins: <?php echo e($nextTermDate ?? 'To be announced'); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showSignatures): ?>
        <div class="sigs">
            <div class="sig">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($signatureImages['teacher'] ?? null) && file_exists($signatureImages['teacher'])): ?>
                            <img src="<?php echo e($signatureImages['teacher']); ?>" alt="Teacher Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="sig-line">Class Teacher</div>
            </div>
            <div class="sig">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($signatureImages['principal'] ?? null) && file_exists($signatureImages['principal'])): ?>
                            <img src="<?php echo e($signatureImages['principal']); ?>" alt="Principal Signature" style="max-height: 40px; max-width: 100px; object-fit: contain; margin-bottom: 4px;" />
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="sig-line">Principal</div>
            </div>
            <div class="sig">
                <div class="sig-line">Parent/Guardian</div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <div class="footer"><?php echo e($schoolName); ?> • Shining Bright Together</div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/report-card-sunset.blade.php ENDPATH**/ ?>