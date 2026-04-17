
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($showSchoolFees ?? false) && !empty($schoolFees)): ?>
<div style="border: 3px solid <?php echo e($rcBorderColor ?? '#d97706'); ?>; border-radius: 10px; padding: 12px; margin-bottom: 14px; background: <?php echo e($rcBgLight ?? '#fff7ed'); ?>;">
    <div style="font-size: 10px; font-weight: 900; color: <?php echo e($rcTitleColor ?? '#92400e'); ?>; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; text-align: center;">
        School Fees for Next Term
    </div>
    <div style="font-size: 16px; font-weight: 900; color: <?php echo e($rcTitleColor ?? '#92400e'); ?>; text-align: center; padding: 8px; background: white; border: 2px solid <?php echo e($rcBorderColor ?? '#d97706'); ?>; border-radius: 8px; margin-bottom: 8px;">
        <?php echo e($schoolFees['currency'] ?? '₦'); ?><?php echo e(number_format($schoolFees['amount'], 2)); ?>

    </div>
    <div style="display: table; width: 100%;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolFees['bank_name'] ?? null): ?>
        <div style="display: table-cell; padding: 4px 8px; vertical-align: top;">
            <span style="font-size: 7px; color: <?php echo e($rcTitleColor ?? '#92400e'); ?>; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Bank Name</span>
            <span style="font-size: 10px; font-weight: 800; color: <?php echo e($rcLabelColor ?? '#78350f'); ?>;"><?php echo e($schoolFees['bank_name']); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolFees['account_number'] ?? null): ?>
        <div style="display: table-cell; padding: 4px 8px; vertical-align: top;">
            <span style="font-size: 7px; color: <?php echo e($rcTitleColor ?? '#92400e'); ?>; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Account Number</span>
            <span style="font-size: 10px; font-weight: 800; color: <?php echo e($rcLabelColor ?? '#78350f'); ?>;"><?php echo e($schoolFees['account_number']); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolFees['account_name'] ?? null): ?>
        <div style="display: table-cell; padding: 4px 8px; vertical-align: top;">
            <span style="font-size: 7px; color: <?php echo e($rcTitleColor ?? '#92400e'); ?>; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Account Name</span>
            <span style="font-size: 10px; font-weight: 800; color: <?php echo e($rcLabelColor ?? '#78350f'); ?>;"><?php echo e($schoolFees['account_name']); ?></span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pdf/partials/rc-school-fees.blade.php ENDPATH**/ ?>