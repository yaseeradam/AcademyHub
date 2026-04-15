<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo e($exam->title); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; line-height: 1.6; color: #1a1a1a; padding: 30px; }
        .school-header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px double #667eea; }
        .school-logo { width: 80px; height: 80px; margin: 0 auto 10px; }
        .school-name { font-size: 20pt; font-weight: bold; color: #667eea; margin-bottom: 5px; }
        .school-address { font-size: 9pt; color: #718096; margin-bottom: 3px; }
        .exam-title { text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .exam-title h1 { font-size: 18pt; margin-bottom: 8px; }
        .exam-info { display: table; width: 100%; margin-bottom: 20px; border: 2px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; padding: 10px 15px; background: #f7fafc; font-weight: bold; width: 30%; border-bottom: 1px solid #e2e8f0; color: #667eea; }
        .info-value { display: table-cell; padding: 10px 15px; border-bottom: 1px solid #e2e8f0; }
        .instructions { background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; margin-bottom: 25px; font-size: 10pt; }
        .instructions strong { color: #667eea; }
        .question { margin-bottom: 25px; page-break-inside: avoid; background: white; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; }
        .question-number { font-weight: bold; font-size: 12pt; margin-bottom: 10px; color: #667eea; }
        .question-text { margin-bottom: 12px; color: #2d3748; }
        .options { margin-left: 0; }
        .option { padding: 8px 12px; margin-bottom: 6px; background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 5px; }
        .option-label { font-weight: bold; color: #667eea; margin-right: 8px; }
        .marks { float: right; background: #667eea; color: white; padding: 2px 8px; border-radius: 12px; font-size: 9pt; }
        .footer { margin-top: 40px; text-align: center; font-size: 9pt; color: #718096; border-top: 2px solid #e2e8f0; padding-top: 15px; }
        .answer-section { margin-top: 30px; page-break-before: always; }
        .answer-section h2 { text-align: center; color: #667eea; margin-bottom: 20px; font-size: 16pt; }
        .answer-grid { display: table; width: 100%; border-collapse: collapse; }
        .answer-row { display: table-row; }
        .answer-cell { display: table-cell; padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-size: 10pt; }
        .answer-header { background: #667eea; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <div class="school-header">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(file_exists(public_path('images/logo.png'))): ?>
            <img src="<?php echo e(public_path('images/logo.png')); ?>" alt="School Logo" class="school-logo">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="school-name"><?php echo e(config('myacademy.school_name', 'MyAcademy International School')); ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_address')): ?>
            <div class="school-address"><?php echo e(config('myacademy.school_address')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_phone')): ?>
            <div class="school-address">Tel: <?php echo e(config('myacademy.school_phone')); ?> | Email: <?php echo e(config('myacademy.school_email')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="exam-title">
        <h1><?php echo e($exam->title); ?></h1>
        <div style="font-size: 10pt;">Computer Based Test (CBT) Examination</div>
    </div>

    <div class="exam-info">
        <div class="info-row">
            <div class="info-label">Class</div>
            <div class="info-value"><?php echo e($exam->schoolClass?->name ?? '-'); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Subject</div>
            <div class="info-value"><?php echo e($exam->subject?->name ?? '-'); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Session / Term</div>
            <div class="info-value"><?php echo e($exam->session ?? '-'); ?> / Term <?php echo e($exam->term ?? '-'); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Duration</div>
            <div class="info-value"><?php echo e($exam->duration_minutes); ?> minutes</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Questions</div>
            <div class="info-value"><?php echo e($exam->questions->count()); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Marks</div>
            <div class="info-value"><?php echo e($exam->questions->sum('marks')); ?></div>
        </div>
    </div>

    <div class="instructions">
        <strong>Instructions:</strong> Read each question carefully and select the best answer from the options provided. Theory questions should be answered in the space provided. Each question carries the marks indicated. Write your answers clearly.
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exam->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="question">
            <div class="question-number">
                Question <?php echo e($loop->iteration); ?>

                <span class="marks"><?php echo e($q->marks); ?> mark<?php echo e($q->marks > 1 ? 's' : ''); ?></span>
            </div>
            <div class="question-text"><?php echo e($q->prompt); ?></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q->type === 'theory'): ?>
                <div style="margin-top: 10px;">
                    <div style="font-weight: bold; color: #667eea; margin-bottom: 6px;">Answer:</div>
                    <div style="border-bottom: 1px solid #e2e8f0; height: 18px;"></div>
                    <div style="border-bottom: 1px solid #e2e8f0; height: 18px;"></div>
                    <div style="border-bottom: 1px solid #e2e8f0; height: 18px;"></div>
                </div>
            <?php else: ?>
                <div class="options">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $q->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="option">
                            <span class="option-label"><?php echo e(chr(65 + $loop->index)); ?>.</span>
                            <span><?php echo e($opt->label); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="answer-section">
        <h2>Answer Sheet</h2>
        <div class="answer-grid">
            <div class="answer-row">
                <div class="answer-cell answer-header">Question</div>
                <div class="answer-cell answer-header">Answer</div>
                <div class="answer-cell answer-header">Question</div>
                <div class="answer-cell answer-header">Answer</div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < ceil($exam->questions->count() / 2); $i++): ?>
                <div class="answer-row">
                    <div class="answer-cell"><?php echo e($i + 1); ?></div>
                    <div class="answer-cell"></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($i + 1 + ceil($exam->questions->count() / 2)) <= $exam->questions->count()): ?>
                        <div class="answer-cell"><?php echo e($i + 1 + ceil($exam->questions->count() / 2)); ?></div>
                        <div class="answer-cell"></div>
                    <?php else: ?>
                        <div class="answer-cell"></div>
                        <div class="answer-cell"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <strong><?php echo e(config('myacademy.school_name', 'MyAcademy')); ?></strong><br>
        Generated on <?php echo e(now()->format('F j, Y \a\t g:i A')); ?>

    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/cbt-exam.blade.php ENDPATH**/ ?>