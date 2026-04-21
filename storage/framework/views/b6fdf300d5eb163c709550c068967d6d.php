<?php $__env->startSection('header_title', 'School Instances'); ?>
<?php $__env->startSection('header_subtitle', 'Manage all registered school tenants'); ?>

<?php $__env->startSection('header_actions'); ?>
    <a href="<?php echo e(route('superadmin.tenants.create')); ?>" class="sa-btn sa-btn-primary">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add New School
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="sa-stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
    <div class="sa-stat-card orange" style="padding:14px 18px;">
        <div class="sa-stat-icon" style="width:40px;height:40px;border-radius:10px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-value" style="font-size:22px;"><?php echo e($tenants->total()); ?></div>
        </div>
    </div>
    <div class="sa-stat-card emerald" style="padding:14px 18px;">
        <div class="sa-stat-icon" style="width:40px;height:40px;border-radius:10px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Active</div>
            <div class="sa-stat-value" style="font-size:22px;"><?php echo e($tenants->getCollection()->where('status','active')->count()); ?></div>
        </div>
    </div>
    <div class="sa-stat-card rose" style="padding:14px 18px;">
        <div class="sa-stat-icon" style="width:40px;height:40px;border-radius:10px;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div class="sa-stat-info">
            <div class="sa-stat-label">Suspended</div>
            <div class="sa-stat-value" style="font-size:22px;"><?php echo e($tenants->getCollection()->where('status','suspended')->count()); ?></div>
        </div>
    </div>
</div>


<div class="sa-panel">
    <div class="sa-panel-header">
        <span class="sa-panel-title">All Schools (<?php echo e($tenants->total()); ?>)</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>School Name</th>
                    <th>Domain / Slug</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Student / Teacher Cap</th>
                    <th>Created</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div style="font-weight:700;color:#1e293b;font-size:14px;"><?php echo e($tenant->name); ?></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->contact_email): ?>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;"><?php echo e($tenant->contact_email); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenant->domain): ?>
                            <a href="https://<?php echo e($tenant->domain); ?>" target="_blank" 
                               style="color:#4f46e5;font-weight:600;font-size:13px;text-decoration:none;">
                                <?php echo e($tenant->domain); ?>

                                <svg style="display:inline;width:10px;height:10px;vertical-align:1px;margin-left:3px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span style="font-family:monospace;font-size:12px;background:#f1f5f9;padding:2px 8px;border-radius:6px;color:#475569;"><?php echo e($tenant->slug); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td>
                        <span class="sa-badge <?php echo e($tenant->plan); ?>"><?php echo e(ucfirst($tenant->plan)); ?></span>
                    </td>
                    <td>
                        <span class="sa-badge <?php echo e($tenant->status); ?>">
                            <span class="sa-badge-dot"></span>
                            <?php echo e(ucfirst($tenant->status)); ?>

                        </span>
                    </td>
                    <td>
                        <div style="font-size:12.5px;">
                            <span style="font-weight:700;color:#1e293b;"><?php echo e(number_format($tenant->max_students)); ?></span>
                            <span style="color:#94a3b8;"> students</span>
                        </div>
                        <div style="font-size:12.5px;">
                            <span style="font-weight:700;color:#1e293b;"><?php echo e(number_format($tenant->max_teachers)); ?></span>
                            <span style="color:#94a3b8;"> teachers</span>
                        </div>
                    </td>
                    <td style="color:#94a3b8;font-size:12.5px;white-space:nowrap;">
                        <?php echo e($tenant->created_at->format('M j, Y')); ?>

                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                            <a href="<?php echo e(route('superadmin.tenants.edit', $tenant)); ?>"
                               class="sa-btn sa-btn-ghost sa-btn-icon" title="Edit">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form action="<?php echo e(route('superadmin.tenants.destroy', $tenant)); ?>" method="POST" 
                                  onsubmit="return confirm('Permanently delete <?php echo e(addslashes($tenant->name)); ?>? This cannot be undone.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="sa-btn sa-btn-danger sa-btn-icon" title="Delete">
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:56px 24px;">
                        <svg style="width:44px;height:44px;color:#cbd5e1;margin:0 auto 14px;display:block;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <div style="font-size:15px;font-weight:700;color:#475569;margin-bottom:8px;">No schools found</div>
                        <div style="font-size:13px;color:#94a3b8;margin-bottom:18px;">Get started by creating your first school instance.</div>
                        <a href="<?php echo e(route('superadmin.tenants.create')); ?>" class="sa-btn sa-btn-primary">Create New School</a>
                    </td>
                </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tenants->hasPages()): ?>
        <div style="padding:16px 22px;border-top:1px solid #f1f5f9;">
            <?php echo e($tenants->links()); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/superadmin/tenants/index.blade.php ENDPATH**/ ?>