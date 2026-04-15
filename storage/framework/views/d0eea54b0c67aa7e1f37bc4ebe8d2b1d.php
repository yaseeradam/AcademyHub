<?php
    /** @var \App\Models\User $teacher */
?>



<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Edit Teacher','subtitle' => 'Update teacher details and access status.','accent' => 'teachers']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Teacher','subtitle' => 'Update teacher details and access status.','accent' => 'teachers']); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <a href="<?php echo e(route('teachers.show', $teacher)); ?>" class="btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
             <?php $__env->endSlot(); ?>
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

        <form method="POST" action="<?php echo e(route('teachers.update', $teacher)); ?>" class="card-padded">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-slate-900">Full name</label>
                    <div class="mt-2">
                        <input
                            name="name"
                            class="input"
                            value="<?php echo e(old('name', $teacher->name)); ?>"
                            placeholder="e.g., Mrs. Anita Okoye"
                            required
                            autocomplete="name"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">Email</label>
                    <div class="mt-2">
                        <input
                            name="email"
                            type="email"
                            class="input"
                            value="<?php echo e(old('email', $teacher->email)); ?>"
                            placeholder="e.g., anita@school.edu"
                            required
                            autocomplete="email"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">New password</label>
                    <div class="mt-2">
                        <input
                            name="password"
                            type="password"
                            class="input"
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="mt-2 text-xs text-slate-500">Leave blank to keep the current password.</div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">Confirm new password</label>
                    <div class="mt-2">
                        <input
                            name="password_confirmation"
                            type="password"
                            class="input"
                            autocomplete="new-password"
                        />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 border-t border-gray-200/70 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input
                        type="checkbox"
                        class="checkbox-custom"
                        name="is_active"
                        value="1"
                        <?php if(old('is_active', $teacher->is_active)): echo 'checked'; endif; ?>
                    />
                    Active (can log in)
                </label>

                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('teachers.show', $teacher)); ?>" class="btn-ghost">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/teachers/edit.blade.php ENDPATH**/ ?>