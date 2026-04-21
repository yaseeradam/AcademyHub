<div class="space-y-6">
    <!-- Header -->
    <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-600 via-orange-600 to-orange-700 p-8 shadow-2xl transition-all duration-500 hover:shadow-amber-500/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="absolute right-0 top-0 h-96 w-96 -translate-y-32 translate-x-32 rounded-full bg-white/10"></div>
        <div class="absolute left-0 bottom-0 h-64 w-64 -translate-x-24 translate-y-24 rounded-full bg-black/10"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 backdrop-blur-md mb-4">
                        <div class="h-2 w-2 animate-pulse rounded-full bg-green-400"></div>
                        <span class="text-sm font-bold text-white">Parent Portal</span>
                    </div>
                    <h1 class="text-3xl font-black text-white sm:text-4xl">Welcome back, <?php echo e(auth()->user()->name); ?></h1>
                    <p class="mt-2 text-base text-amber-50">Track your children's progress and stay updated.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-white/20 px-5 py-3 text-sm font-bold text-white backdrop-blur-md">
                        <svg class="inline h-5 w-5 mr-1 -mt-0.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                        <?php echo e($this->children->count()); ?> <?php echo e(Str::plural('Child', $this->children->count())); ?> linked
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Children Selection -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->children->count() > 1): ?>
        <div class="space-y-3">
            <div class="text-sm font-semibold text-slate-900">Select Child</div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button 
                        wire:click="selectChild(<?php echo e($child->id); ?>)"
                        class="flex items-center gap-3 p-4 rounded-2xl border transition-all duration-300 <?php echo e($selectedChildId === $child->id ? 'border-amber-500 bg-amber-50/50 shadow-md ring-1 ring-amber-500' : 'border-gray-200 bg-white hover:border-amber-300 hover:shadow-sm'); ?>"
                    >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($child->passport_photo_url): ?>
                            <img src="<?php echo e($child->passport_photo_url); ?>" class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow-sm" />
                        <?php else: ?>
                            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold shadow-inner">
                                <?php echo e(substr($child->first_name, 0, 1)); ?>

                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="text-left">
                            <div class="text-sm font-bold text-slate-900"><?php echo e($child->full_name); ?></div>
                            <div class="text-xs text-slate-500"><?php echo e($child->schoolClass?->name ?? 'Unassigned'); ?></div>
                        </div>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedChild): ?>
        <!-- Selected Child Info -->
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
            <div class="flex items-center gap-4 mb-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedChild->passport_photo_url): ?>
                    <img src="<?php echo e($this->selectedChild->passport_photo_url); ?>" class="h-16 w-16 rounded-full object-cover shadow-md ring-2 ring-white" />
                <?php else: ?>
                    <div class="h-16 w-16 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-2xl shadow-md">
                        <?php echo e(substr($this->selectedChild->first_name, 0, 1)); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-slate-900"><?php echo e($this->selectedChild->full_name); ?></h2>
                    <p class="text-sm font-medium text-slate-600"><?php echo e($this->selectedChild->admission_number); ?> • <?php echo e($this->selectedChild->schoolClass?->name ?? 'Unassigned'); ?> <?php echo e($this->selectedChild->section?->name ?? ''); ?></p>
                </div>
            </div>

            <!-- Session/Term Filters -->
            <div class="flex flex-wrap gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                    <input wire:model.live="session" type="text" placeholder="2025/2026" class="mt-1 input-compact min-w-32" />
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                    <select wire:model.live="term" class="mt-1 select min-w-20">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>
        </div>        <!-- Performance Overview -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Academic Performance -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 shadow-sm ring-1 ring-blue-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-500/5"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-blue-600">Average Score</div>
                        <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900"><?php echo e($this->childPerformanceStats['average']); ?>%</div>
                        <div class="mt-1.5 text-xs text-slate-600">Grade: <?php echo e($this->childPerformanceStats['grade']); ?></div>
                    </div>
                    <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Attendance -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-50 to-green-100/50 p-6 shadow-sm ring-1 ring-green-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-green-500/5"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-green-600">Attendance</div>
                        <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900"><?php echo e($this->childAttendance['percentage']); ?>%</div>
                        <div class="mt-1.5 text-xs text-slate-600"><?php echo e($this->childAttendance['present']); ?>/<?php echo e($this->childAttendance['total']); ?> days</div>
                    </div>
                    <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg shadow-green-500/30">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Fees Status -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 shadow-sm ring-1 ring-amber-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-500/5"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-amber-600">Outstanding Fees</div>
                        <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">₦<?php echo e(number_format($this->childFees['outstanding'])); ?></div>
                        <div class="mt-1.5 text-xs text-slate-600">Paid: ₦<?php echo e(number_format($this->childFees['paid'])); ?></div>
                    </div>
                    <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-600 text-white shadow-lg shadow-amber-500/30">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Subjects Count -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100/50 p-6 shadow-sm ring-1 ring-purple-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-purple-500/5"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-purple-600">Subjects Taken</div>
                        <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900"><?php echo e($this->childPerformanceStats['subjects']); ?></div>
                        <div class="mt-1.5 text-xs text-slate-600">Total: <?php echo e($this->childPerformanceStats['total']); ?> marks</div>
                    </div>
                    <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow-lg shadow-purple-500/30">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scores -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->childScores->isNotEmpty()): ?>
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="absolute -left-12 -top-12 h-32 w-32 rounded-full bg-slate-500/5"></div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold tracking-tight text-slate-900">Recent Scores</h3>
                    <a href="<?php echo e(route('results.report-card', ['student' => $this->selectedChild, 'term' => $term, 'session' => $session])); ?>" 
                       target="_blank" 
                       class="btn-outline">
                        Download Report Card
                    </a>
                </div>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginal163c8ba6efb795223894d5ffef5034f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal163c8ba6efb795223894d5ffef5034f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Subject</th>
                                <th class="px-5 py-3 text-right">CA</th>
                                <th class="px-5 py-3 text-right">Exam</th>
                                <th class="px-5 py-3 text-right">Total</th>
                                <th class="px-5 py-3 text-right">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->childScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="bg-white hover:bg-slate-50 cursor-default">
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-900"><?php echo e($score->subject->name); ?></td>
                                    <td class="px-5 py-4 text-sm text-right text-slate-700"><?php echo e(($score->ca1 ?? 0) + ($score->ca2 ?? 0)); ?></td>
                                    <td class="px-5 py-4 text-sm text-right text-slate-700"><?php echo e($score->exam ?? '-'); ?></td>
                                    <td class="px-5 py-4 text-sm text-right font-bold text-slate-900"><?php echo e($score->total ?? '-'); ?></td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            <?php echo e(($score->total ?? 0) >= 70 ? 'bg-green-100 text-green-700' : 
                                               (($score->total ?? 0) >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')); ?>">
                                            <?php echo e($this->getGrade($score->total ?? 0)); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal163c8ba6efb795223894d5ffef5034f5)): ?>
<?php $attributes = $__attributesOriginal163c8ba6efb795223894d5ffef5034f5; ?>
<?php unset($__attributesOriginal163c8ba6efb795223894d5ffef5034f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal163c8ba6efb795223894d5ffef5034f5)): ?>
<?php $component = $__componentOriginal163c8ba6efb795223894d5ffef5034f5; ?>
<?php unset($__componentOriginal163c8ba6efb795223894d5ffef5034f5); ?>
<?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">No scores available</h3>
                <p class="mt-2 text-gray-500">Scores for <?php echo e($this->selectedChild->full_name); ?> will appear here once teachers enter them.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php elseif($this->children->isEmpty()): ?>
        <!-- No Children Linked -->
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <h3 class="mt-4 text-xl font-semibold text-gray-900">No children linked</h3>
            <p class="mt-2 text-gray-500">Please contact the school administrator to link your children to this parent account.</p>
            <div class="mt-6">
                <a href="<?php echo e(route('profile')); ?>" class="btn-primary">
                    Update Profile
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Select a Child -->
        <div class="rounded-2xl bg-white p-8 text-center shadow-lg">
            <svg class="mx-auto h-16 w-16 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 011 1v2a1 1 0 01-1 1h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V8H3a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
            </svg>
            <h3 class="mt-4 text-xl font-semibold text-gray-900">Select a child</h3>
            <p class="mt-2 text-gray-500">Choose one of your children above to view their academic progress and information.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/parents/dashboard.blade.php ENDPATH**/ ?>