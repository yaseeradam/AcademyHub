<?php $__env->startSection('header_title', 'Create New School'); ?>
<?php $__env->startSection('header_subtitle', 'Provision a new school instance'); ?>

<?php $__env->startSection('header_actions'); ?>
    <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="sa-btn sa-btn-ghost">
        ← Back to List
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:860px; margin:0 auto;">

    <form action="<?php echo e(route('superadmin.tenants.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        
        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#f59e0b;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    School Information
                </span>
            </div>
            <div style="padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px;">

                <div>
                    <label class="sa-form-label">School Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                           class="sa-form-input" placeholder="e.g. Greenwood High School">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label class="sa-form-label">Custom Domain <span style="color:#94a3b8;">(optional)</span></label>
                    <input type="text" name="domain" value="<?php echo e(old('domain')); ?>"
                           class="sa-form-input" placeholder="e.g. portal.greenwood.edu">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['domain'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="sa-form-hint">Leave blank — an automatic slug will be generated.</div>
                </div>

                <div>
                    <label class="sa-form-label">Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo e(old('contact_email')); ?>"
                           class="sa-form-input" placeholder="admin@school.com">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label class="sa-form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" value="<?php echo e(old('contact_phone')); ?>"
                           class="sa-form-input" placeholder="+234 ...">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

            </div>
        </div>

        
        <div class="sa-panel" style="margin-bottom:20px;">
            <div class="sa-panel-header">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Subscription &amp; Limits
                </span>
            </div>
            <div style="padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:18px;">

                <div>
                    <label class="sa-form-label">Pricing Plan <span style="color:#ef4444;">*</span></label>
                    <div style="position:relative;">
                        <select name="plan" required class="sa-form-input" style="appearance:none;padding-right:36px;">
                            <option value="free"       <?php if(old('plan','free')=='free'): echo 'selected'; endif; ?>>Free Tier</option>
                            <option value="pro"        <?php if(old('plan')=='pro'): echo 'selected'; endif; ?>>Pro Tier</option>
                            <option value="enterprise" <?php if(old('plan')=='enterprise'): echo 'selected'; endif; ?>>Enterprise Tier</option>
                        </select>
                        <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="sa-form-label">Instance Status <span style="color:#ef4444;">*</span></label>
                    <div style="position:relative;">
                        <select name="status" required class="sa-form-input" style="appearance:none;padding-right:36px;">
                            <option value="pending"   <?php if(old('status','pending')=='pending'): echo 'selected'; endif; ?>>Pending Setup</option>
                            <option value="active"    <?php if(old('status')=='active'): echo 'selected'; endif; ?>>Active / Live</option>
                            <option value="suspended" <?php if(old('status')=='suspended'): echo 'selected'; endif; ?>>Suspended</option>
                        </select>
                        <div style="position:absolute;inset-y:0;right:12px;display:flex;align-items:center;pointer-events:none;color:#94a3b8;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="sa-form-label">Max Students <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="max_students" value="<?php echo e(old('max_students', 500)); ?>" required min="1"
                           class="sa-form-input" style="font-family:monospace;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['max_students'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div>
                    <label class="sa-form-label">Max Teachers <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="max_teachers" value="<?php echo e(old('max_teachers', 50)); ?>" required min="1"
                           class="sa-form-input" style="font-family:monospace;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['max_teachers'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

            </div>
        </div>

        
        <div class="sa-panel" style="margin-bottom:24px;" x-data="{ createAdmin: <?php echo e(old('create_admin') ? 'true' : 'false'); ?> }">
            <div class="sa-panel-header" style="cursor:pointer;" @click="createAdmin = !createAdmin">
                <span class="sa-panel-title">
                    <svg style="display:inline;width:15px;height:15px;vertical-align:-2px;margin-right:6px;color:#10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Create Initial Admin Account
                    <span style="font-size:11px;font-weight:500;color:#94a3b8;margin-left:8px;">— optional</span>
                </span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:12px;color:#94a3b8;" x-text="createAdmin ? 'Collapse' : 'Expand to set up admin'"></span>
                    <svg :class="createAdmin ? 'rotate-180' : ''" style="width:16px;height:16px;color:#94a3b8;transition:transform .2s;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <div x-show="createAdmin" x-cloak style="padding:24px; border-top:1px solid #f1f5f9;">
                <input type="hidden" name="create_admin" value="1">
                <p style="font-size:13px;color:#64748b;margin:0 0 18px;line-height:1.6;">
                    Provision an administrator account for this school. The admin can log in immediately and begin configuring the school.
                </p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <label class="sa-form-label">Admin Full Name</label>
                        <input type="text" name="admin_name" value="<?php echo e(old('admin_name')); ?>"
                               class="sa-form-input" placeholder="e.g. John Doe">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['admin_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="sa-form-label">Admin Email</label>
                        <input type="email" name="admin_email" value="<?php echo e(old('admin_email')); ?>"
                               class="sa-form-input" placeholder="admin@school.com">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['admin_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="sa-form-label">Admin Password</label>
                        <input type="password" name="admin_password"
                               class="sa-form-input" placeholder="Min. 8 characters">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['admin_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="sa-form-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="sa-form-label">Confirm Password</label>
                        <input type="password" name="admin_password_confirmation"
                               class="sa-form-input" placeholder="Repeat password">
                    </div>
                </div>
            </div>

            <div x-show="!createAdmin" style="padding:16px 24px;background:#f8fafc;border-top:1px solid #f1f5f9;">
                <p style="font-size:12.5px;color:#94a3b8;margin:0;">
                    ℹ️ No admin will be created now. You can still add users later from the school's settings page.
                </p>
            </div>
        </div>

        
        <div style="display:flex; align-items:center; justify-content:flex-end; gap:12px;">
            <a href="<?php echo e(route('superadmin.tenants.index')); ?>" class="sa-btn sa-btn-ghost">Cancel</a>
            <button type="submit" class="sa-btn sa-btn-primary" style="padding:10px 24px;">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Create School Instance
            </button>
        </div>

    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/superadmin/tenants/create.blade.php ENDPATH**/ ?>