

<?php
    $siBorderColor = $siBorderColor ?? '#d97706';
    $siBgColor = $siBgColor ?? '#fffbeb';
    $siLabelColor = $siLabelColor ?? '#92400e';
    $siValueColor = $siValueColor ?? '#1f2937';
    $siDotColor = $siDotColor ?? '#fbbf24';
?>
<div style="border: 2px solid <?php echo e($siBorderColor); ?>; border-radius: 8px; padding: 8px 10px; margin-bottom: 12px; background: <?php echo e($siBgColor); ?>;">
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: middle;">
            
            <div style="display: table; width: 100%; border-bottom: 1px dotted <?php echo e($siDotColor); ?>;">
                <div style="display: table-cell; padding: 3px 6px; vertical-align: middle;">
                    <span style="color: <?php echo e($siLabelColor); ?>; font-weight: 700; font-size: 7px; text-transform: uppercase; white-space: nowrap;">Student Name:</span>
                    <span style="font-weight: 700; color: <?php echo e($siValueColor); ?>; font-size: 9px;"> <?php echo e($student->full_name); ?></span>
                </div>
                <div style="display: table-cell; padding: 3px 6px; vertical-align: middle;">
                    <span style="color: <?php echo e($siLabelColor); ?>; font-weight: 700; font-size: 7px; text-transform: uppercase; white-space: nowrap;">Admission No:</span>
                    <span style="font-weight: 700; color: <?php echo e($siValueColor); ?>; font-size: 9px;"> <?php echo e($student->admission_number); ?></span>
                </div>
                <div style="display: table-cell; padding: 3px 6px; vertical-align: middle;">
                    <span style="color: <?php echo e($siLabelColor); ?>; font-weight: 700; font-size: 7px; text-transform: uppercase; white-space: nowrap;">Class / Section:</span>
                    <span style="font-weight: 700; color: <?php echo e($siValueColor); ?>; font-size: 9px;"> <?php echo e($student->schoolClass?->name); ?> <?php echo e($student->section?->name ? '— ' . $student->section->name : ''); ?></span>
                </div>
            </div>
            
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; padding: 3px 6px; vertical-align: middle;">
                    <span style="color: <?php echo e($siLabelColor); ?>; font-weight: 700; font-size: 7px; text-transform: uppercase; white-space: nowrap;">Gender:</span>
                    <span style="font-weight: 700; color: <?php echo e($siValueColor); ?>; font-size: 9px;"> <?php echo e($student->gender ?? 'N/A'); ?></span>
                </div>
                <div style="display: table-cell; padding: 3px 6px; vertical-align: middle;">
                    <span style="color: <?php echo e($siLabelColor); ?>; font-weight: 700; font-size: 7px; text-transform: uppercase; white-space: nowrap;">Date of Birth:</span>
                    <span style="font-weight: 700; color: <?php echo e($siValueColor); ?>; font-size: 9px;"> <?php echo e($student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M, Y') : ($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A')); ?></span>
                </div>
                <div style="display: table-cell; padding: 3px 6px; vertical-align: middle;">
                    <span style="color: <?php echo e($siLabelColor); ?>; font-weight: 700; font-size: 7px; text-transform: uppercase; white-space: nowrap;">No. in Class:</span>
                    <span style="font-weight: 700; color: <?php echo e($siValueColor); ?>; font-size: 9px;"> <?php echo e($totalStudents ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo): ?>
            <div style="display: table-cell; width: 65px; text-align: center; vertical-align: middle; padding-left: 6px;">
                <img src="<?php echo e(public_path('uploads/' . str_replace('\\', '/', $student->passport_photo))); ?>"
                    alt="Photo" style="width: 55px; height: 66px; border: 2px solid <?php echo e($siBorderColor); ?>; border-radius: 5px; object-fit: cover;" />
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/partials/rc-student-info.blade.php ENDPATH**/ ?>