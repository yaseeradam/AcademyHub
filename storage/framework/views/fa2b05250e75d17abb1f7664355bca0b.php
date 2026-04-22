<?php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */
    $user = auth()->user();
    $total = $classes->count();
    $totalStudents = $classes->sum('students_count');
    $totalSubjects = $classes->sum('subjects_count');
?>



<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Classes','subtitle' => 'Manage class levels and enrollment structure.','accent' => 'classes']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Classes','subtitle' => 'Manage class levels and enrollment structure.','accent' => 'classes']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                <a href="<?php echo e(route('classes.index')); ?>" class="btn-outline">Manage Sections</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a href="<?php echo e(route('subjects.index')); ?>" class="btn-outline">Manage Subjects</a>
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

    
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e($total); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Classes</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e(number_format($totalStudents)); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Students</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e($totalSubjects); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Subject Allocations</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('modal')): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20" x-transition>
            <div class="rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900"><?php echo e(session('modal')['message']); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="rounded-2xl border border-orange-200 bg-orange-50/60 p-5">
            <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 text-white shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="text-sm font-bold text-slate-800">Add New Class</div>
            </div>
            <form method="POST" action="<?php echo e(route('classes.store')); ?>" class="flex flex-col sm:flex-row items-end gap-4">
                <?php echo csrf_field(); ?>
                <div class="flex-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Class Name</label>
                    <input name="name" class="mt-2 input-compact w-full" value="<?php echo e(old('name')); ?>" placeholder="e.g., JSS 1A" required />
                </div>
                <div class="w-32">
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Level</label>
                    <input name="level" type="number" class="mt-2 input-compact w-full" value="<?php echo e(old('level', 1)); ?>" min="1" max="30" required />
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:from-sky-500 hover:to-blue-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Class
                </button>
            </form>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <div class="text-base font-bold text-slate-800">All Classes</div>
                <div class="mt-0.5 text-xs text-slate-400"><?php echo e($total); ?> class<?php echo e($total !== 1 ? 'es' : ''); ?> registered</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 text-left">Class Name</th>
                        <th class="px-5 py-3 text-center">Level</th>
                        <th class="px-5 py-3 text-center">Subjects</th>
                        <th class="px-5 py-3 text-center">Students</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                            <th class="px-5 py-3 text-right">Actions</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                        $gradients = [
                            'from-sky-400 to-blue-500',
                            'from-violet-500 to-purple-600',
                            'from-emerald-400 to-teal-500',
                            'from-orange-400 to-amber-500',
                            'from-pink-400 to-rose-500',
                        ];
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $grad = $gradients[$class->id % count($gradients)]; ?>
                        <tr class="group bg-white transition hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br <?php echo e($grad); ?> text-white text-xs font-black shadow-sm">
                                        <?php echo e(mb_substr($class->name, 0, 2)); ?>

                                    </div>
                                    <div class="font-semibold text-slate-800"><?php echo e($class->name); ?></div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Level <?php echo e($class->level); ?></span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-600"><?php echo e(number_format((int) $class->subjects_count)); ?></span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-lg bg-orange-50 px-2.5 py-1 text-xs font-bold text-orange-600"><?php echo e(number_format((int) $class->students_count)); ?></span>
                            </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="<?php echo e(route('classes.subjects', $class)); ?>" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-600 transition hover:bg-blue-100">Subjects</a>
                                        <form method="POST" action="<?php echo e(route('classes.destroy', $class)); ?>" class="inline-block" onsubmit="return confirm('Delete this class?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-100">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($user?->role === 'admin' ? 5 : 4); ?>" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-600">No classes found</div>
                                    <div class="text-xs text-slate-400">Create your first class to begin</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/classes/index.blade.php ENDPATH**/ ?>