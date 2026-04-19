<div class="space-y-6">
    
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-4 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">Daily Tracking</span>
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">Attendance</h1>
                <p class="mt-1.5 text-base font-medium" style="color:#93c5fd;"><?php echo e(now()->format('l, F j, Y')); ?></p>
            </div>
            <button wire:click="$set('showModal', true)"
                    class="inline-flex items-center gap-2 self-start sm:self-auto rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Mark Attendance
            </button>
        </div>
    </div>

    
    <?php
        $dayCounts = $dateRecords->countBy(fn($m) => $m->status);
    ?>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600">Present</span>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-900"><?php echo e($dayCounts['Present'] ?? 0); ?></div>
            <div class="mt-0.5 text-xs font-semibold text-slate-400">Students present</div>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-red-50 text-red-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">Absent</span>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-900"><?php echo e($dayCounts['Absent'] ?? 0); ?></div>
            <div class="mt-0.5 text-xs font-semibold text-slate-400">Students absent</div>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600">Late</span>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-900"><?php echo e($dayCounts['Late'] ?? 0); ?></div>
            <div class="mt-0.5 text-xs font-semibold text-slate-400">Students late</div>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between">
                <div class="grid h-10 w-10 place-items-center rounded-xl bg-violet-50 text-violet-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-600">Excused</span>
            </div>
            <div class="mt-4 text-3xl font-black text-slate-900"><?php echo e($dayCounts['Excused'] ?? 0); ?></div>
            <div class="mt-0.5 text-xs font-semibold text-slate-400">Students excused</div>
        </div>
    </div>

    
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
        <div class="flex flex-wrap items-center gap-3">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">View date</label>
            <input wire:change="$set('date', $event.target.value)" type="date" value="<?php echo e($date); ?>" class="input-compact" />
            <button wire:click="$set('date', '<?php echo e(now()->toDateString()); ?>')" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Today</button>
        </div>
    </div>

    
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div class="text-sm font-bold text-slate-800">
                <?php echo e($date ? \Carbon\Carbon::parse($date)->format('l, F j, Y') : 'Today'); ?>

            </div>
            <span class="text-xs font-semibold text-slate-400"><?php echo e($dateRecords->count()); ?> records</span>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dateRecords->count() > 0): ?>
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
                    <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Student</th>
                            <th class="px-4 py-3 text-left">Class</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dateRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $student = $mark->student; ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->passport_photo_url): ?>
                                            <img src="<?php echo e($student->passport_photo_url); ?>" class="h-9 w-9 rounded-full object-cover ring-2 ring-slate-100 flex-shrink-0"/>
                                        <?php else: ?>
                                            <div class="grid h-9 w-9 flex-shrink-0 place-items-center rounded-full bg-slate-700 text-xs font-bold text-white">
                                                <?php echo e(substr($student?->first_name ?? '?', 0, 1)); ?><?php echo e(substr($student?->last_name ?? '', 0, 1)); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900"><?php echo e($student?->full_name ?? '—'); ?></div>
                                            <div class="text-xs text-slate-400"><?php echo e($student?->admission_number); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-slate-600"><?php echo e($student?->schoolClass?->name ?? 'N/A'); ?></td>
                                <td class="px-4 py-3.5">
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
                                <td class="px-4 py-3.5 text-sm text-slate-500"><?php echo e($mark->note ?: '—'); ?></td>
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
            <div class="px-6 py-14 text-center">
                <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-sm font-bold text-slate-700">No attendance for <?php echo e($date ? \Carbon\Carbon::parse($date)->format('F j, Y') : 'today'); ?></div>
                <div class="mt-1 text-xs text-slate-400">Click "Mark Attendance" to add records</div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('showModal', false)"></div>
            <div class="absolute inset-0 sm:inset-auto sm:right-0 sm:top-0 sm:bottom-0 sm:w-[480px] bg-white flex flex-col shadow-2xl">

                
                <div class="flex-shrink-0 px-5 py-4 border-b border-slate-100" style="background-color:#1a2e4a;">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-base font-bold text-white">Mark Attendance</div>
                            <div class="text-xs mt-0.5" style="color:#93c5fd;"><?php echo e($date ? \Carbon\Carbon::parse($date)->format('l, M j') : 'Today'); ?></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classId && $sectionId): ?>
                                <span class="rounded-full bg-emerald-500 px-2.5 py-0.5 text-[11px] font-bold text-white"><?php echo e($markCounts['Present'] ?? 0); ?> P</span>
                                <span class="rounded-full bg-red-500 px-2.5 py-0.5 text-[11px] font-bold text-white"><?php echo e($markCounts['Absent'] ?? 0); ?> A</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button wire:click="$set('showModal', false)"
                                    class="grid h-8 w-8 place-items-center rounded-xl text-white transition-all"
                                    style="background:rgba(255,255,255,0.15);">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="flex-shrink-0 grid grid-cols-2 gap-3 border-b border-slate-100 bg-slate-50 px-5 py-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Class</label>
                        <select wire:model.live="classId" class="select w-full">
                            <option value="">Select class</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Section</label>
                        <select wire:model.live="sectionId" class="select w-full" <?php if(!$classId): echo 'disabled'; endif; ?>>
                            <option value="">Select section</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>

                
                <div class="flex-1 overflow-y-auto px-4 py-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classId && $sectionId): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $visibleStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $status = $marks[$student->id]['status'] ?? 'Present'; ?>
                                <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                                    <div class="flex items-center gap-3 mb-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                                            <img src="<?php echo e($student->passport_photo_url); ?>" class="h-10 w-10 flex-shrink-0 rounded-full object-cover ring-2 ring-slate-100"/>
                                        <?php else: ?>
                                            <div class="grid h-10 w-10 flex-shrink-0 place-items-center rounded-full bg-slate-700 text-sm font-bold text-white">
                                                <?php echo e(substr($student->first_name, 0, 1)); ?><?php echo e(substr($student->last_name, 0, 1)); ?>

                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-bold text-slate-900"><?php echo e($student->full_name); ?></div>
                                            <div class="text-xs text-slate-400"><?php echo e($student->admission_number); ?></div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <button wire:click="setMark(<?php echo e($student->id); ?>, 'Present')"
                                                class="rounded-xl py-2 text-xs font-bold transition-all <?php echo e($status === 'Present' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'); ?>">
                                            Present
                                        </button>
                                        <button wire:click="setMark(<?php echo e($student->id); ?>, 'Absent')"
                                                class="rounded-xl py-2 text-xs font-bold transition-all <?php echo e($status === 'Absent' ? 'bg-red-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'); ?>">
                                            Absent
                                        </button>
                                        <button wire:click="setMark(<?php echo e($student->id); ?>, 'Late')"
                                                class="rounded-xl py-2 text-xs font-bold transition-all <?php echo e($status === 'Late' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'); ?>">
                                            Late
                                        </button>
                                        <button wire:click="setMark(<?php echo e($student->id); ?>, 'Excused')"
                                                class="rounded-xl py-2 text-xs font-bold transition-all <?php echo e($status === 'Excused' ? 'bg-violet-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'); ?>">
                                            Excused
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex h-full items-center justify-center py-16 text-center">
                            <div>
                                <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-slate-600">Select class and section</div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classId && $sectionId): ?>
                    <div class="flex-shrink-0 border-t border-slate-100 bg-white px-5 py-4">
                        <button wire:click="save"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition-colors disabled:opacity-50"
                                wire:loading.attr="disabled">
                            <svg class="h-4 w-4" wire:loading.remove wire:target="save" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path d="M5 13l4 4L19 7"/>
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