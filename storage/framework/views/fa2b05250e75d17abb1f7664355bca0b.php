<?php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */
    $user = auth()->user();
?>



<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-500 via-violet-500 to-indigo-600 p-8 shadow-2xl">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
            <div class="relative flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-white">Classes</h1>
                    <p class="mt-2 text-purple-100">Manage class levels and enrollment structure</p>
                </div>
                <a href="<?php echo e(route('subjects.index')); ?>" class="rounded-xl bg-white/20 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/30">Subjects</a>
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
            <form method="POST" action="<?php echo e(route('classes.store')); ?>" class="rounded-2xl border border-purple-100 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-lg backdrop-blur-sm">
                <?php echo csrf_field(); ?>
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-purple-900">Add New Class</div>
                        <div class="text-sm text-purple-700">Create a new class level</div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-purple-700">Class name</label>
                        <div class="mt-2">
                            <input name="name" class="w-full rounded-lg border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-purple-500 focus:ring-purple-500" value="<?php echo e(old('name')); ?>" placeholder="e.g., JSS 1A" required />
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-purple-700">Level</label>
                        <div class="mt-2">
                            <input name="level" type="number" class="w-full rounded-lg border border-purple-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-purple-500 focus:ring-purple-500" value="<?php echo e(old('level', 1)); ?>" min="1" max="30" required />
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg hover:from-purple-600 hover:to-indigo-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        Add Class
                    </button>
                </div>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $palette = [
                        ['from' => 'from-amber-400', 'via' => 'via-orange-500', 'to' => 'to-orange-600', 'bg' => 'from-amber-50 to-orange-100/60', 'ring' => 'ring-amber-300/40', 'iconBg' => 'from-amber-400 to-orange-600', 'iconText' => 'text-amber-700', 'accent' => 'bg-amber-500', 'shadow' => 'shadow-amber-500/30', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200'],
                        ['from' => 'from-emerald-400', 'via' => 'via-teal-500', 'to' => 'to-cyan-500', 'bg' => 'from-emerald-50 to-teal-100/60', 'ring' => 'ring-emerald-300/40', 'iconBg' => 'from-emerald-400 to-emerald-600', 'iconText' => 'text-emerald-700', 'accent' => 'bg-emerald-500', 'shadow' => 'shadow-emerald-500/30', 'badge' => 'bg-emerald-100 text-emerald-700 ring-emerald-200'],
                        ['from' => 'from-orange-400', 'via' => 'via-amber-500', 'to' => 'to-yellow-500', 'bg' => 'from-orange-50 to-amber-100/60', 'ring' => 'ring-orange-300/40', 'iconBg' => 'from-orange-400 to-amber-600', 'iconText' => 'text-orange-700', 'accent' => 'bg-orange-500', 'shadow' => 'shadow-orange-500/30', 'badge' => 'bg-orange-100 text-orange-700 ring-orange-200'],
                        ['from' => 'from-amber-400', 'via' => 'via-orange-500', 'to' => 'to-rose-500', 'bg' => 'from-amber-50 to-orange-100/60', 'ring' => 'ring-amber-300/40', 'iconBg' => 'from-amber-400 to-amber-600', 'iconText' => 'text-amber-700', 'accent' => 'bg-amber-500', 'shadow' => 'shadow-amber-500/30', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200'],
                        ['from' => 'from-yellow-400', 'via' => 'via-amber-500', 'to' => 'to-orange-500', 'bg' => 'from-yellow-50 to-amber-100/60', 'ring' => 'ring-yellow-300/40', 'iconBg' => 'from-yellow-400 to-amber-600', 'iconText' => 'text-yellow-700', 'accent' => 'bg-yellow-500', 'shadow' => 'shadow-yellow-500/30', 'badge' => 'bg-yellow-100 text-yellow-700 ring-yellow-200'],
                    ];
                    $scheme = $palette[$class->id % count($palette)];
                ?>

                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br <?php echo e($scheme['bg']); ?> shadow-lg ring-1 <?php echo e($scheme['ring']); ?> transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 hover:scale-[1.02]">
                    <div class="absolute inset-0 bg-gradient-to-br <?php echo e($scheme['from']); ?> <?php echo e($scheme['via']); ?> <?php echo e($scheme['to']); ?> opacity-0 transition-opacity duration-500 group-hover:opacity-10"></div>
                    <div class="h-2 bg-gradient-to-r <?php echo e($scheme['from']); ?> <?php echo e($scheme['via']); ?> <?php echo e($scheme['to']); ?>"></div>
                    <div class="absolute right-0 top-0 h-32 w-32 -translate-y-8 translate-x-8 rounded-full <?php echo e($scheme['accent']); ?> opacity-10"></div>
                    <div class="absolute left-0 bottom-0 h-24 w-24 -translate-x-6 translate-y-6 rounded-full <?php echo e($scheme['accent']); ?> opacity-5"></div>
                    
                    <div class="relative p-7">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="inline-flex items-center gap-2 rounded-full <?php echo e($scheme['badge']); ?> px-3 py-1.5 text-xs font-bold uppercase tracking-wider ring-1 backdrop-blur-sm">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Level <?php echo e($class->level); ?>

                                </div>
                                <div class="mt-3 truncate text-2xl font-black tracking-tight text-slate-900"><?php echo e($class->name); ?></div>
                                <div class="mt-1.5 flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                    Academic year overview
                                </div>
                            </div>
                            <div class="icon-3d grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br <?php echo e($scheme['iconBg']); ?> text-white shadow-xl <?php echo e($scheme['shadow']); ?> transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                                <a href="<?php echo e(route('classes.subjects', $class)); ?>" class="group/stat relative overflow-hidden rounded-2xl bg-white/80 px-5 py-4 ring-1 ring-white/60 backdrop-blur-sm transition-all duration-300 hover:bg-white hover:shadow-lg">
                                    <div class="absolute inset-0 bg-gradient-to-br <?php echo e($scheme['from']); ?> <?php echo e($scheme['to']); ?> opacity-0 transition-opacity duration-300 group-hover/stat:opacity-5"></div>
                                    <div class="relative">
                                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider <?php echo e($scheme['iconText']); ?>">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                            </svg>
                                            Subjects
                                        </div>
                                        <div class="mt-2 text-3xl font-black tracking-tight text-slate-900"><?php echo e(number_format((int) $class->defaultSubjects->count())); ?></div>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="group/stat relative overflow-hidden rounded-2xl bg-white/80 px-5 py-4 ring-1 ring-white/60 backdrop-blur-sm">
                                    <div class="relative">
                                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider <?php echo e($scheme['iconText']); ?>">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                            </svg>
                                            Subjects
                                        </div>
                                        <div class="mt-2 text-3xl font-black tracking-tight text-slate-900"><?php echo e(number_format((int) $class->defaultSubjects->count())); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="group/stat relative overflow-hidden rounded-2xl bg-white/80 px-5 py-4 ring-1 ring-white/60 backdrop-blur-sm transition-all duration-300 hover:bg-white hover:shadow-lg">
                                <div class="absolute inset-0 bg-gradient-to-br <?php echo e($scheme['from']); ?> <?php echo e($scheme['to']); ?> opacity-0 transition-opacity duration-300 group-hover/stat:opacity-5"></div>
                                <div class="relative">
                                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider <?php echo e($scheme['iconText']); ?>">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                            <circle cx="9" cy="7" r="4"/>
                                        </svg>
                                        Students
                                    </div>
                                    <div class="mt-2 text-3xl font-black tracking-tight text-slate-900"><?php echo e(number_format((int) $class->students_count)); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card-padded sm:col-span-2 xl:col-span-3 text-center">
                    <div class="text-sm font-semibold text-slate-900">No classes yet</div>
                    <div class="mt-2 text-sm text-slate-600">Create your first class to begin admissions and allocations.</div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/classes/index.blade.php ENDPATH**/ ?>