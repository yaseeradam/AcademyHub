<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Settings','subtitle' => 'System configuration and offline backups.','accent' => 'settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Settings','subtitle' => 'System configuration and offline backups.','accent' => 'settings']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div class="flex flex-wrap gap-2">
        <a href="<?php echo e(route('settings.backup')); ?>"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(16, 185, 129, 0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(16, 185, 129, 0.3)'">
            <svg style="width: 1.125rem; height: 1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5">
                <path
                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                <polyline points="3.27 6.96 12 12.01 20.73 6.96" />
                <line x1="12" y1="22.08" x2="12" y2="12" />
            </svg>
            Backups
        </a>
        <a href="<?php echo e(route('settings.audit-logs')); ?>"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.3);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(245, 158, 11, 0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(245, 158, 11, 0.3)'">
            <svg style="width: 1.125rem; height: 1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <line x1="10" y1="9" x2="8" y2="9" />
            </svg>
            Audit Logs
        </a>
        <a href="<?php echo e(route('settings.results')); ?>"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(59, 130, 246, 0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(59, 130, 246, 0.3)'">
            <svg style="width: 1.125rem; height: 1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            Result Scoring
        </a>
        <a href="<?php echo e(route('settings.certificates')); ?>"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; box-shadow: 0 4px 6px -1px rgba(236, 72, 153, 0.3);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(236, 72, 153, 0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(236, 72, 153, 0.3)'">
            <svg style="width: 1.125rem; height: 1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5">
                <circle cx="12" cy="8" r="7" />
                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
            </svg>
            Certificates
        </a>
        <a href="<?php echo e(route('settings.templates')); ?>"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.3);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(14, 165, 233, 0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(14, 165, 233, 0.3)'">
            <svg style="width: 1.125rem; height: 1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5">
                <path
                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
            </svg>
            Templates
        </a>
        <a href="<?php echo e(route('settings.custom-fields')); ?>"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white; box-shadow: 0 4px 6px -1px rgba(6, 182, 212, 0.3);"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(6, 182, 212, 0.4)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(6, 182, 212, 0.3)'">
            <svg style="width: 1.125rem; height: 1.125rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Custom Fields
        </a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
        <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="card-padded border border-orange-200 bg-orange-50/60">
            <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 gap-6">
        <!-- School Information Card -->
        <div
            class="rounded-3xl border-2 border-blue-200 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 p-6 shadow-2xl">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('myacademy.school_logo')): ?>
                <div class="mb-5 flex justify-center">
                    <img src="<?php echo e(asset('uploads/' . str_replace('\\', '/', config('myacademy.school_logo')))); ?>"
                        alt="School logo"
                        class="h-24 w-24 rounded-full bg-white object-contain p-3 ring-4 ring-white shadow-2xl" />
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex items-center gap-3 mb-5">
                <div
                    class="grid h-12 w-12 place-items-center rounded-xl bg-white/20 backdrop-blur-sm text-white shadow-lg">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                </div>
                <div class="text-lg font-black text-white">School Information</div>
            </div>

            <form method="POST" action="<?php echo e(route('settings.update-school')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-white/90">School Name</label>
                        <input name="school_name"
                            class="mt-2 w-full rounded-xl border-0 bg-white/95 px-4 py-3 text-sm font-semibold text-gray-900 shadow-lg focus:ring-2 focus:ring-white"
                            value="<?php echo e(old('school_name', config('myacademy.school_name'))); ?>" required />
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-white/90">Address</label>
                        <input name="school_address"
                            class="mt-2 w-full rounded-xl border-0 bg-white/95 px-4 py-3 text-sm font-semibold text-gray-900 shadow-lg focus:ring-2 focus:ring-white"
                            value="<?php echo e(old('school_address', config('myacademy.school_address'))); ?>" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-white/90">Phone</label>
                            <input name="school_phone"
                                class="mt-2 w-full rounded-xl border-0 bg-white/95 px-4 py-3 text-sm font-semibold text-gray-900 shadow-lg focus:ring-2 focus:ring-white"
                                value="<?php echo e(old('school_phone', config('myacademy.school_phone'))); ?>" />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-white/90">Email</label>
                            <input name="school_email" type="email"
                                class="mt-2 w-full rounded-xl border-0 bg-white/95 px-4 py-3 text-sm font-semibold text-gray-900 shadow-lg focus:ring-2 focus:ring-white"
                                value="<?php echo e(old('school_email', config('myacademy.school_email'))); ?>" />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-white/90">School Logo</label>
                        <input name="school_logo" type="file" accept="image/*"
                            class="mt-2 w-full rounded-xl border-0 bg-white/95 px-4 py-3 text-sm font-semibold text-gray-900 shadow-lg focus:ring-2 focus:ring-white" />
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-600 shadow-xl hover:bg-white/90 transition-all flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Save School Information
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/settings/index.blade.php ENDPATH**/ ?>