<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Attendance Management</h2>
            <p class="text-slate-600 text-sm mt-1">Mark and track student attendance</p>
        </div>
        <button
            wire:click="$set('showModal', true)"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl font-bold shadow-lg"
        >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Mark Student Attendance
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card-padded relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="h-20 w-20 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center mb-2">
                    <div class="p-2 rounded-lg bg-emerald-50 mr-2">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-600">Present Today</h3>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo e($this->markCounts['Present'] ?? 0); ?></div>
                <p class="text-xs text-emerald-600 font-semibold">Students present</p>
            </div>
        </div>

        <div class="card-padded relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="h-20 w-20 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center mb-2">
                    <div class="p-2 rounded-lg bg-red-50 mr-2">
                        <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-600">Absent Today</h3>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo e($this->markCounts['Absent'] ?? 0); ?></div>
                <p class="text-xs text-red-600 font-semibold">Students absent</p>
            </div>
        </div>

        <div class="card-padded relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="h-20 w-20 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center mb-2">
                    <div class="p-2 rounded-lg bg-amber-50 mr-2">
                        <svg class="h-4 w-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-600">Late Today</h3>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo e($this->markCounts['Late'] ?? 0); ?></div>
                <p class="text-xs text-amber-600 font-semibold">Students late</p>
            </div>
        </div>

        <div class="card-padded relative overflow-hidden">
            <div class="absolute top-0 right-0 p-3 opacity-10">
                <svg class="h-20 w-20 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="relative">
                <div class="flex items-center mb-2">
                    <div class="p-2 rounded-lg bg-purple-50 mr-2">
                        <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-600">Excused Today</h3>
                </div>
                <div class="text-3xl font-bold text-slate-900 mb-1"><?php echo e($this->markCounts['Excused'] ?? 0); ?></div>
                <p class="text-xs text-purple-600 font-semibold">Students excused</p>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card-padded">
        <div class="flex items-center gap-4 flex-wrap">
            <div class="flex items-center text-slate-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <label class="text-sm font-bold">View Attendance for:</label>
            </div>
            <input wire:model.live="date" type="date" class="input-compact max-w-xs" />
            <button wire:click="$set('date', '<?php echo e(now()->toDateString()); ?>')" class="btn-outline text-sm">Reset</button>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card-padded">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="font-bold text-slate-900">Attendance Details - <?php echo e($date ? \Carbon\Carbon::parse($date)->format('l, F d, Y') : 'Today'); ?></h3>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->dateRecords->count() > 0): ?>
            <div class="overflow-x-auto">
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
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->dateRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $student = $mark->student; ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->passport_photo_url): ?>
                                            <img src="<?php echo e($student->passport_photo_url); ?>" alt="<?php echo e($student->full_name); ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
                                        <?php else: ?>
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                <?php echo e(substr($student?->first_name ?? '?', 0, 1)); ?><?php echo e(substr($student?->last_name ?? '', 0, 1)); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div>
                                            <div class="font-semibold text-slate-900"><?php echo e($student?->full_name ?? '—'); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo e($student?->admission_number); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-slate-700 text-sm"><?php echo e($student?->schoolClass?->name ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => ''.e($mark->status === 'Present' ? 'success' : ($mark->status === 'Absent' ? 'danger' : ($mark->status === 'Late' ? 'warning' : 'default'))).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($mark->status === 'Present' ? 'success' : ($mark->status === 'Absent' ? 'danger' : ($mark->status === 'Late' ? 'warning' : 'default'))).'']); ?>
                                        <?php echo e($mark->status); ?>

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
                                </td>
                                <td class="text-sm text-slate-600"><?php echo e($mark->note ?: '-'); ?></td>
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
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="h-12 w-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-slate-900 font-semibold">No attendance marked for <?php echo e($date ? \Carbon\Carbon::parse($date)->format('F d, Y') : 'today'); ?></p>
                <p class="text-sm text-slate-500 mt-1">Click "Mark Attendance" to add records</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Mobile Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('showModal', false)"></div>
            <div class="absolute inset-0 sm:inset-auto sm:right-0 sm:top-0 sm:bottom-0 sm:w-[500px] bg-white flex flex-col shadow-2xl">
                <!-- Modal Header -->
                <div class="flex-shrink-0 px-4 py-3 border-b border-slate-200 bg-gradient-to-r from-amber-50 to-orange-50">
                    <div class="flex items-center gap-3">
                        <button wire:click="$set('showModal', false)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white shadow-sm hover:shadow-md">
                            <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <div class="flex-1">
                            <h2 class="text-base font-bold text-slate-900">Student Attendance</h2>
                            <p class="text-xs text-slate-600"><?php echo e($date ? \Carbon\Carbon::parse($date)->format('l, M d') : 'Today'); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classId && $sectionId): ?>
                            <div class="flex gap-1.5">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700">
                                    <?php echo e($this->markCounts['Present'] ?? 0); ?>

                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700">
                                    <?php echo e($this->markCounts['Absent'] ?? 0); ?>

                                </span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="flex-1 overflow-y-auto p-3 space-y-3">
                    <!-- Selectors -->
                    <div class="flex gap-2">
                        <select wire:model.live="classId" class="flex-1 select">
                            <option value="">Select class</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <select wire:model.live="sectionId" class="flex-1 select" <?php if(!$classId): echo 'disabled'; endif; ?>>
                            <option value="">Select section</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <!-- Student List -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classId && $sectionId): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->visibleStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $status = $marks[$student->id]['status'] ?? 'Present';
                                ?>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <div class="flex items-center gap-3 mb-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                                            <img src="<?php echo e($student->passport_photo_url); ?>" alt="<?php echo e($student->full_name); ?>" class="w-10 h-10 rounded-full object-cover flex-shrink-0" />
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                                <?php echo e(substr($student->first_name, 0, 1)); ?><?php echo e(substr($student->last_name, 0, 1)); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-slate-900 text-sm truncate"><?php echo e($student->full_name); ?></p>
                                            <p class="text-[11px] text-slate-500"><?php echo e($student->admission_number); ?></p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-4 gap-1.5">
                                        <button
                                            wire:click="setMark(<?php echo e($student->id); ?>, 'Present')"
                                            class="py-2 rounded-lg text-xs font-bold transition-all <?php echo e($status === 'Present' ? 'bg-emerald-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'); ?>"
                                        >
                                            Present
                                        </button>
                                        <button
                                            wire:click="setMark(<?php echo e($student->id); ?>, 'Absent')"
                                            class="py-2 rounded-lg text-xs font-bold transition-all <?php echo e($status === 'Absent' ? 'bg-red-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'); ?>"
                                        >
                                            Absent
                                        </button>
                                        <button
                                            wire:click="setMark(<?php echo e($student->id); ?>, 'Late')"
                                            class="py-2 rounded-lg text-xs font-bold transition-all <?php echo e($status === 'Late' ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'); ?>"
                                        >
                                            Late
                                        </button>
                                        <button
                                            wire:click="setMark(<?php echo e($student->id); ?>, 'Excused')"
                                            class="py-2 rounded-lg text-xs font-bold transition-all <?php echo e($status === 'Excused' ? 'bg-purple-500 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'); ?>"
                                        >
                                            Excused
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="h-12 w-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="text-slate-600 text-sm font-semibold">Select class and section</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Modal Footer -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classId && $sectionId): ?>
                    <div class="flex-shrink-0 p-3 border-t border-slate-200 bg-slate-50">
                        <button
                            wire:click="save"
                            class="w-full h-12 text-base font-bold bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl shadow-lg"
                        >
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span wire:loading.remove wire:target="save">Save Attendance</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/attendance/index.blade.php ENDPATH**/ ?>