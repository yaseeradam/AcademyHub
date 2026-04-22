<?php
    /** @var \App\Models\User $user */
    $meta = $user->email;
?>



<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'My Profile','subtitle' => $meta,'accent' => 'settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Profile','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($meta),'accent' => 'settings']); ?>
             <?php $__env->slot('leading', null, []); ?> 
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->profile_photo_url): ?>
                    <img
                        src="<?php echo e($user->profile_photo_url); ?>"
                        alt="<?php echo e($user->name); ?>"
                        class="h-32 w-32 rounded-full object-cover ring-2 ring-white shadow-sm"
                    />
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.avatar','data' => ['name' => $user->name,'size' => '128','class' => 'ring-2 ring-white shadow-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($user->name),'size' => '128','class' => 'ring-2 ring-white shadow-sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b)): ?>
<?php $attributes = $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b; ?>
<?php unset($__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b)): ?>
<?php $component = $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b; ?>
<?php unset($__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
             <?php $__env->endSlot(); ?>
             <?php $__env->slot('actions', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => ''.e($user->is_active ? 'success' : 'warning').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($user->is_active ? 'success' : 'warning').'']); ?>
                    <?php echo e($user->is_active ? 'Active' : 'Inactive'); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn-outline">
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

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="card-padded lg:col-span-2">
                <div class="text-sm font-semibold text-slate-900">Profile Photo</div>
                <div class="mt-1 text-sm text-slate-600">Upload your photo to show on your dashboard and in messages.</div>

                <form method="POST" action="<?php echo e(route('profile.photo')); ?>" enctype="multipart/form-data" class="mt-5 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Choose photo</label>
                        <input
                            type="file"
                            name="photo"
                            accept="image/*"
                            class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-brand-400 focus:ring-brand-300"
                            required
                        />
                        <div class="mt-2 text-xs text-slate-500">JPG/PNG up to 2MB.</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="btn-primary">Upload</button>
                    </div>
                </form>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->profile_photo_url): ?>
                    <form
                        method="POST"
                        action="<?php echo e(route('profile.photo.destroy')); ?>"
                        onsubmit="return confirm('Remove your profile photo?')"
                        class="mt-2"
                    >
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn-outline text-red-700 ring-red-200 hover:bg-red-50">
                            Remove Photo
                        </button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="card-padded">
                <div class="text-sm font-semibold text-slate-900">Account Details</div>
                <div class="mt-1 text-sm text-slate-600">Update your name and email.</div>

                <form method="POST" action="<?php echo e(route('profile.details')); ?>" class="mt-5 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Name</label>
                        <input
                            name="name"
                            type="text"
                            value="<?php echo e(old('name', $user->name)); ?>"
                            class="mt-2 input w-full"
                            required
                        />
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Email</label>
                        <input
                            name="email"
                            type="email"
                            value="<?php echo e(old('email', $user->email)); ?>"
                            class="mt-2 input w-full"
                            required
                        />
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm ring-1 ring-inset ring-slate-200">
                        <div class="text-slate-600">Role</div>
                        <div class="font-semibold text-slate-900"><?php echo e(ucfirst($user->role ?? 'user')); ?></div>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">Save Details</button>
                </form>

                <div class="mt-6 border-t border-slate-200/60 pt-6">
                    <div class="text-sm font-semibold text-slate-900">Change Password</div>
                    <div class="mt-1 text-sm text-slate-600">Use a strong password (min 8 characters).</div>

                    <form method="POST" action="<?php echo e(route('profile.password')); ?>" class="mt-5 space-y-4">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Current password</label>
                            <input name="current_password" type="password" class="mt-2 input w-full" required />
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">New password</label>
                            <input name="password" type="password" class="mt-2 input w-full" required />
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-slate-600">Confirm new password</label>
                            <input name="password_confirmation" type="password" class="mt-2 input w-full" required />
                        </div>
                        <button type="submit" class="btn-outline w-full justify-center">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/profile/index.blade.php ENDPATH**/ ?>