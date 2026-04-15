<?php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Subject> $subjects */
    $user = auth()->user();
?>



<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-500 via-blue-500 to-indigo-600 p-8 shadow-2xl">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-white">Subjects</h1>
                    <p class="mt-2 text-cyan-100">Create subject codes used for results and allocations</p>
                </div>
                <a href="<?php echo e(route('classes.index')); ?>" class="rounded-xl bg-white/20 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/30">Classes</a>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('modal')): ?>
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20" x-transition>
                <div class="rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900"><?php echo e(session('modal')['message']); ?></div>
                        </div>
                    </div>
                </div>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
            <form method="POST" action="<?php echo e(route('subjects.store')); ?>" class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-white to-cyan-50/30 p-6 shadow-lg backdrop-blur-sm">
                <?php echo csrf_field(); ?>
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-lg">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-cyan-900">Add New Subject</div>
                        <div class="text-sm text-cyan-700">Create a subject for allocations</div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-cyan-700">Subject name</label>
                        <div class="mt-2">
                            <input name="name" class="w-full rounded-lg border border-cyan-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-cyan-500 focus:ring-cyan-500" value="<?php echo e(old('name')); ?>" placeholder="e.g., Mathematics" required />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-cyan-700">Code</label>
                        <div class="mt-2">
                            <input name="code" class="w-full rounded-lg border border-cyan-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-cyan-500 focus:ring-cyan-500" value="<?php echo e(old('code')); ?>" placeholder="e.g., MATH" required />
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg hover:from-cyan-600 hover:to-blue-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        Add Subject
                    </button>
                </div>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $colors = [
                        ['bg' => 'from-blue-50 to-indigo-100/60', 'ring' => 'ring-blue-300/40', 'icon' => 'from-blue-400 to-blue-600', 'shadow' => 'shadow-blue-500/30', 'badge' => 'bg-blue-100 text-blue-700 ring-blue-200', 'accent' => 'bg-blue-500'],
                        ['bg' => 'from-purple-50 to-violet-100/60', 'ring' => 'ring-purple-300/40', 'icon' => 'from-purple-400 to-purple-600', 'shadow' => 'shadow-purple-500/30', 'badge' => 'bg-purple-100 text-purple-700 ring-purple-200', 'accent' => 'bg-purple-500'],
                        ['bg' => 'from-emerald-50 to-green-100/60', 'ring' => 'ring-emerald-300/40', 'icon' => 'from-emerald-400 to-emerald-600', 'shadow' => 'shadow-emerald-500/30', 'badge' => 'bg-emerald-100 text-emerald-700 ring-emerald-200', 'accent' => 'bg-emerald-500'],
                        ['bg' => 'from-orange-50 to-amber-100/60', 'ring' => 'ring-orange-300/40', 'icon' => 'from-orange-400 to-orange-600', 'shadow' => 'shadow-orange-500/30', 'badge' => 'bg-orange-100 text-orange-700 ring-orange-200', 'accent' => 'bg-orange-500'],
                        ['bg' => 'from-pink-50 to-rose-100/60', 'ring' => 'ring-pink-300/40', 'icon' => 'from-pink-400 to-pink-600', 'shadow' => 'shadow-pink-500/30', 'badge' => 'bg-pink-100 text-pink-700 ring-pink-200', 'accent' => 'bg-pink-500'],
                        ['bg' => 'from-cyan-50 to-teal-100/60', 'ring' => 'ring-cyan-300/40', 'icon' => 'from-cyan-400 to-cyan-600', 'shadow' => 'shadow-cyan-500/30', 'badge' => 'bg-cyan-100 text-cyan-700 ring-cyan-200', 'accent' => 'bg-cyan-500'],
                    ];
                    $color = $colors[$subject->id % count($colors)];
                ?>

                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br <?php echo e($color['bg']); ?> shadow-lg ring-1 <?php echo e($color['ring']); ?> transition-all duration-500 hover:shadow-2xl hover:-translate-y-2">
                    <div class="absolute right-0 top-0 h-24 w-24 -translate-y-6 translate-x-6 rounded-full <?php echo e($color['accent']); ?> opacity-10"></div>
                    <div class="absolute left-0 bottom-0 h-16 w-16 -translate-x-4 translate-y-4 rounded-full <?php echo e($color['accent']); ?> opacity-5"></div>
                    
                    <div class="relative p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="icon-3d grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br <?php echo e($color['icon']); ?> text-white shadow-xl <?php echo e($color['shadow']); ?> transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                            </div>
                            <div class="inline-flex items-center gap-1.5 rounded-full <?php echo e($color['badge']); ?> px-3 py-1.5 text-xs font-black uppercase tracking-wider ring-1">
                                <?php echo e($subject->code); ?>

                            </div>
                        </div>

                        <div class="mt-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                                <form id="subject-update-<?php echo e($subject->id); ?>" method="POST" action="<?php echo e(route('subjects.update', $subject)); ?>" class="space-y-3">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <input
                                        name="name"
                                        class="w-full rounded-xl border-0 bg-white/80 px-4 py-2.5 text-base font-bold text-slate-900 ring-1 ring-white/60 backdrop-blur-sm transition-all focus:bg-white focus:ring-2 focus:ring-white"
                                        value="<?php echo e(old('name', $subject->name)); ?>"
                                        required
                                    />
                                    <div class="flex gap-2">
                                        <input
                                            name="code"
                                            class="w-24 rounded-xl border-0 bg-white/80 px-3 py-2 text-sm font-bold text-slate-900 ring-1 ring-white/60 backdrop-blur-sm transition-all focus:bg-white focus:ring-2 focus:ring-white"
                                            value="<?php echo e(old('code', $subject->code)); ?>"
                                            required
                                        />
                                        <button type="submit" class="flex-1 rounded-xl bg-white/90 px-4 py-2 text-sm font-bold text-slate-900 ring-1 ring-white/60 backdrop-blur-sm transition-all hover:bg-white hover:shadow-lg">
                                            Save
                                        </button>
                                    </div>
                                </form>
                                <form method="POST" action="<?php echo e(route('subjects.destroy', $subject)); ?>" class="mt-2">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="w-full rounded-xl bg-red-500/10 px-4 py-2 text-sm font-bold text-red-600 ring-1 ring-red-200/50 backdrop-blur-sm transition-all hover:bg-red-500/20">
                                        Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="text-xl font-black tracking-tight text-slate-900"><?php echo e($subject->name); ?></div>
                                <div class="mt-2 text-sm text-slate-600">Subject code: <?php echo e($subject->code); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full rounded-3xl bg-gradient-to-br from-slate-50 to-gray-100/60 p-12 text-center shadow-lg ring-1 ring-slate-200/50">
                    <div class="mx-auto grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-slate-400 to-slate-600 text-white shadow-xl">
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <div class="mt-6 text-xl font-black text-slate-900">No subjects yet</div>
                    <div class="mt-2 text-sm text-slate-600">Add your first subject to get started</div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/subjects/index.blade.php ENDPATH**/ ?>