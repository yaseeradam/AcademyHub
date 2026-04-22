<?php
    $user = auth()->user();
    $submissionStatus = $this->submission?->status;
    $locked = $user?->role === 'teacher' && (in_array($submissionStatus, ['submitted', 'approved'], true) || $this->isPublished);
?>

<div class="space-y-8">
    
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-700 p-8 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white/5"></div>
        <div class="absolute right-6 bottom-6 h-16 w-16 rounded-full bg-white/10"></div>
        
        <div class="relative flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-white">Score Entry</h1>
                    <p class="mt-1 text-emerald-100">Enter CA and Exam scores for students</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                    <a href="<?php echo e(route('results.submissions')); ?>" 
                       class="flex items-center gap-2 rounded-xl bg-white/20 px-4 py-3 text-sm font-bold text-white backdrop-blur-sm transition-all hover:bg-white/30">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10,9 9,9 8,9"/>
                        </svg>
                        Submissions
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('results.broadsheet')); ?>" 
                   class="flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-emerald-600 shadow-lg transition-all hover:bg-emerald-50 hover:shadow-xl">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3zM21 9H3M9 21V9"/>
                    </svg>
                    Broadsheet
                </a>
            </div>
        </div>
    </div>

    
    <div class="rounded-3xl bg-white p-8 shadow-xl ring-1 ring-gray-200">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="block text-sm font-black text-gray-900 mb-2">Class</label>
                <select wire:key="class-dropdown-<?php echo e($classId ?: 'empty'); ?>" wire:model.live="classId"
                    class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400">
                    <option value="">Select class</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-black text-gray-900 mb-2">Subject</label>
                <select wire:key="subject-dropdown-<?php echo e($classId ?: 'empty'); ?>" wire:model.live="subjectId"
                    <?php if(!$classId): echo 'disabled'; endif; ?> 
                    class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Select subject</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-black text-gray-900 mb-2">Term</label>
                <select wire:model.live="term" 
                    class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400">
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-black text-gray-900 mb-2">Session</label>
                <input wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026"
                    class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400" />
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="button" wire:click="save" <?php if(!$classId || !$subjectId || $locked): echo 'disabled'; endif; ?> 
                class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3 text-sm font-bold text-white shadow-lg transition-all hover:from-emerald-600 hover:to-teal-600 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17,21 17,13 7,13 7,21"/>
                    <polyline points="7,3 7,8 15,8"/>
                </svg>
                Save Scores
            </button>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'teacher'): ?>
                <button type="button" wire:click="submitScores" <?php if(!$classId || !$subjectId || $locked): echo 'disabled'; endif; ?>
                    class="flex items-center gap-2 rounded-xl border-2 border-emerald-300 bg-white px-6 py-3 text-sm font-bold text-emerald-700 shadow-sm transition-all hover:bg-emerald-50 hover:border-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13L4 6"/>
                    </svg>
                    Submit to Admin
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isPublished): ?>
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800">
                    <svg class="mr-1 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Published
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'teacher' && $this->submission): ?>
                <?php
                    $status = $this->submission->status ?? 'submitted';
                    $statusConfig = match ($status) {
                        'approved' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'icon' => 'M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'M6 18L18 6M6 6l12 12'],
                        'submitted' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    };
                ?>
                <span class="inline-flex items-center rounded-full <?php echo e($statusConfig['bg']); ?> px-3 py-1.5 text-xs font-bold <?php echo e($statusConfig['text']); ?>">
                    <svg class="mr-1 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="<?php echo e($statusConfig['icon']); ?>"/>
                    </svg>
                    <?php echo e(ucfirst($status)); ?>

                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'rejected' && $this->submission->note): ?>
                    <div class="rounded-lg bg-red-50 border border-red-200 px-3 py-2">
                        <span class="text-xs font-medium text-red-700">Note: <?php echo e($this->submission->note); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$classId): ?>
        <div class="rounded-3xl bg-white p-12 text-center shadow-xl ring-1 ring-gray-200">
            <div class="flex flex-col items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100">
                    <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 10 12 5 2 10l10 5 10-5z"/>
                        <path d="M6 12v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-5"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Select a class</h3>
                    <p class="mt-1 text-sm text-gray-500">Choose a class to load students and subjects</p>
                </div>
            </div>
        </div>
    <?php elseif(!$subjectId): ?>
        <div class="rounded-3xl bg-white p-12 text-center shadow-xl ring-1 ring-gray-200">
            <div class="flex flex-col items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100">
                    <svg class="h-8 w-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Select a subject</h3>
                    <p class="mt-1 text-sm text-gray-500">Only allocated subjects are shown for teachers</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php
            $maxMarks = $this->maxMarks();
        ?>
        
        
        <div class="overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-gray-200">
            
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-gray-900">Score Entry Sheet</h2>
                        <p class="text-sm text-gray-600">Enter scores for each assessment component</p>
                    </div>
                    <div class="flex items-center gap-4 text-sm font-bold text-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-blue-400"></div>
                            <span>CA1 /<?php echo e($maxMarks['ca1']); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-green-400"></div>
                            <span>CA2 /<?php echo e($maxMarks['ca2']); ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-amber-400"></div>
                            <span>Exam /<?php echo e($maxMarks['exam']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-gray-700">Student</th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-blue-700">CA1 /<?php echo e($maxMarks['ca1']); ?></th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-green-700">CA2 /<?php echo e($maxMarks['ca2']); ?></th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-amber-700">Exam /<?php echo e($maxMarks['exam']); ?></th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-purple-700">Total</th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-gray-700">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $row = $scores[$student->id] ?? ['ca1' => 0, 'ca2' => 0, 'exam' => 0];
                                $total = (int) ($row['ca1'] ?? 0) + (int) ($row['ca2'] ?? 0) + (int) ($row['exam'] ?? 0);
                                $grade = \App\Models\Score::gradeForTotal($total, $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam']);
                                
                                // Check for validation errors
                                $ca1Error = isset($validationErrors["{$student->id}.ca1"]);
                                $ca2Error = isset($validationErrors["{$student->id}.ca2"]);
                                $examError = isset($validationErrors["{$student->id}.exam"]);
                                $hasError = $ca1Error || $ca2Error || $examError;
                            ?>
                            <tr class="group transition-all duration-200 hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/50 <?php echo e($hasError ? 'bg-red-50' : 'bg-white'); ?>" 
                                data-student-id="<?php echo e($student->id); ?>">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gray-400 to-gray-500 text-white shadow-sm">
                                            <span class="text-sm font-black"><?php echo e(substr($student->first_name, 0, 1)); ?><?php echo e(substr($student->last_name, 0, 1)); ?></span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900"><?php echo e($student->full_name); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo e($student->admission_number); ?></div>
                                        </div>
                                    </div>
                                </td>
                                
                                
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center">
                                        <input wire:model.lazy="scores.<?php echo e($student->id); ?>.ca1" 
                                            type="number" min="0" max="<?php echo e($maxMarks['ca1']); ?>" step="1"
                                            data-student-id="<?php echo e($student->id); ?>" data-field="ca1"
                                            class="w-20 rounded-xl border-2 <?php echo e($ca1Error ? 'border-red-400 bg-red-50' : 'border-blue-300 bg-blue-50'); ?> px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/20 hover:border-blue-400" />
                                    </div>
                                </td>
                                
                                
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center">
                                        <input wire:model.lazy="scores.<?php echo e($student->id); ?>.ca2" 
                                            type="number" min="0" max="<?php echo e($maxMarks['ca2']); ?>" step="1"
                                            data-student-id="<?php echo e($student->id); ?>" data-field="ca2"
                                            class="w-20 rounded-xl border-2 <?php echo e($ca2Error ? 'border-red-400 bg-red-50' : 'border-green-300 bg-green-50'); ?> px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm transition-all focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/20 hover:border-green-400" />
                                    </div>
                                </td>
                                
                                
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center">
                                        <input wire:model.lazy="scores.<?php echo e($student->id); ?>.exam" 
                                            type="number" min="0" max="<?php echo e($maxMarks['exam']); ?>" step="1"
                                            data-student-id="<?php echo e($student->id); ?>" data-field="exam"
                                            class="w-20 rounded-xl border-2 <?php echo e($examError ? 'border-red-400 bg-red-50' : 'border-amber-300 bg-amber-50'); ?> px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm transition-all focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/20 hover:border-amber-400" />
                                    </div>
                                </td>
                                
                                
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-purple-100 to-indigo-100 px-4 py-2 text-lg font-black text-purple-900 shadow-sm">
                                        <?php echo e($total); ?>

                                    </div>
                                </td>
                                
                                
                                <td class="px-6 py-4 text-center">
                                    <?php
                                        $gradeConfig = match($grade) {
                                            'A' => ['bg' => 'from-emerald-400 to-green-500', 'text' => 'text-white'],
                                            'B' => ['bg' => 'from-blue-400 to-indigo-500', 'text' => 'text-white'],
                                            'C' => ['bg' => 'from-amber-400 to-orange-500', 'text' => 'text-white'],
                                            'D' => ['bg' => 'from-red-400 to-pink-500', 'text' => 'text-white'],
                                            'F' => ['bg' => 'from-gray-400 to-slate-500', 'text' => 'text-white'],
                                            default => ['bg' => 'from-gray-200 to-gray-300', 'text' => 'text-gray-700'],
                                        };
                                    ?>
                                    <div class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r <?php echo e($gradeConfig['bg']); ?> px-3 py-2 text-sm font-black <?php echo e($gradeConfig['text']); ?> shadow-lg">
                                        <?php echo e($grade); ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100">
                                            <svg class="h-8 w-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">No students found</h3>
                                            <p class="mt-1 text-sm text-gray-500">No students are enrolled in this class</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="rounded-3xl bg-gradient-to-r from-blue-50 to-indigo-50 p-6 shadow-lg ring-1 ring-blue-200">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                    <svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-blue-900">⌨️ Keyboard Shortcuts</h3>
                    <p class="text-sm text-blue-700">Navigate quickly through the scoresheet</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="flex items-center gap-2">
                    <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">Enter</kbd>
                    <span class="text-sm font-medium text-blue-700">Move down</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">Tab</kbd>
                    <span class="text-sm font-medium text-blue-700">Move right</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">↑↓←→</kbd>
                    <span class="text-sm font-medium text-blue-700">Arrow keys</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">Shift+Tab</kbd>
                    <span class="text-sm font-medium text-blue-700">Move left</span>
                </div>
            </div>
        </div>

            <script>
                // Add shake animation styles
                const shakeStyles = `
                    <style id="shake-animation-styles">
                        @keyframes shake {
                            0%, 100% { transform: translateX(0); }
                            10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
                            20%, 40%, 60%, 80% { transform: translateX(3px); }
                        }
                        
                        .shake-row {
                            animation: shake 0.6s ease-in-out;
                            background-color: #fef2f2 !important;
                        }
                        
                        .error-row {
                            background-color: #fef2f2;
                        }
                        
                        .error-row:hover {
                            background-color: #fecaca !important;
                        }
                        
                        .shake-input {
                            animation: shake 0.6s ease-in-out;
                            border-color: #ef4444 !important;
                            background-color: #fef2f2 !important;
                        }
                    </style>
                `;
                
                // Add styles to head if not already present
                if (!document.getElementById('shake-animation-styles')) {
                    document.head.insertAdjacentHTML('beforeend', shakeStyles);
                }
                
                // Listen for shake-row events from Livewire
                document.addEventListener('livewire:init', () => {
                    Livewire.on('shake-row', (event) => {
                        const data = event[0] || event;
                        const studentId = data.studentId;
                        const field = data.field;
                        
                        // Find the row and input field
                        const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
                        const input = document.querySelector(`input[data-student-id="${studentId}"][data-field="${field}"]`);
                        
                        if (row && input) {
                            // Add shake classes
                            row.classList.add('shake-row');
                            input.classList.add('shake-input');
                            
                            // Remove shake classes after animation completes
                            setTimeout(() => {
                                row.classList.remove('shake-row');
                                input.classList.remove('shake-input');
                            }, 600);
                            
                            // Focus the input field to draw attention
                            input.focus();
                            input.select();
                        }
                    });
                });
                
                document.addEventListener('DOMContentLoaded', function () {
                    const table = document.querySelector('tbody');
                    if (!table) return;

                    table.addEventListener('keydown', function (e) {
                        const input = e.target;
                        if (input.tagName !== 'INPUT' || input.type !== 'number') return;

                        const cell = input.closest('td');
                        const row = cell.closest('tr');
                        const cells = Array.from(row.querySelectorAll('td input[type="number"]'));
                        const rows = Array.from(table.querySelectorAll('tr:has(input)'));
                        const currentCellIndex = cells.indexOf(input);
                        const currentRowIndex = rows.indexOf(row);

                        let nextInput = null;
                        let shouldPrevent = false;

                        if (e.key === 'Enter') {
                            shouldPrevent = true;
                            if (currentRowIndex < rows.length - 1) {
                                const nextRow = rows[currentRowIndex + 1];
                                const nextRowInputs = nextRow.querySelectorAll('td input[type="number"]');
                                nextInput = nextRowInputs[currentCellIndex];
                            }
                        } else if (e.key === 'Tab' && !e.shiftKey) {
                            shouldPrevent = true;
                            if (currentCellIndex < cells.length - 1) {
                                nextInput = cells[currentCellIndex + 1];
                            } else if (currentRowIndex < rows.length - 1) {
                                nextInput = rows[currentRowIndex + 1].querySelector('td input[type="number"]');
                            }
                        } else if (e.key === 'Tab' && e.shiftKey) {
                            shouldPrevent = true;
                            if (currentCellIndex > 0) {
                                nextInput = cells[currentCellIndex - 1];
                            } else if (currentRowIndex > 0) {
                                const prevRow = rows[currentRowIndex - 1];
                                const prevCells = prevRow.querySelectorAll('td input[type="number"]');
                                nextInput = prevCells[prevCells.length - 1];
                            }
                        } else if (e.key === 'ArrowDown') {
                            shouldPrevent = true;
                            if (currentRowIndex < rows.length - 1) {
                                const nextRow = rows[currentRowIndex + 1];
                                const nextRowInputs = nextRow.querySelectorAll('td input[type="number"]');
                                nextInput = nextRowInputs[currentCellIndex];
                            }
                        } else if (e.key === 'ArrowUp') {
                            shouldPrevent = true;
                            if (currentRowIndex > 0) {
                                const prevRow = rows[currentRowIndex - 1];
                                const prevRowInputs = prevRow.querySelectorAll('td input[type="number"]');
                                nextInput = prevRowInputs[currentCellIndex];
                            }
                        } else if (e.key === 'ArrowRight') {
                            if (input.selectionStart === input.value.length) {
                                shouldPrevent = true;
                                if (currentCellIndex < cells.length - 1) {
                                    nextInput = cells[currentCellIndex + 1];
                                }
                            }
                        } else if (e.key === 'ArrowLeft') {
                            if (input.selectionStart === 0) {
                                shouldPrevent = true;
                                if (currentCellIndex > 0) {
                                    nextInput = cells[currentCellIndex - 1];
                                }
                            }
                        }

                        if (shouldPrevent && nextInput) {
                            e.preventDefault();
                            nextInput.focus();
                            nextInput.select();
                        }
                    });

                    table.addEventListener('focusin', function (e) {
                        if (e.target.tagName === 'INPUT') {
                            e.target.closest('td').style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.5)';
                            e.target.closest('td').style.position = 'relative';
                            e.target.closest('td').style.zIndex = '10';
                        }
                    });

                    table.addEventListener('focusout', function (e) {
                        if (e.target.tagName === 'INPUT') {
                            e.target.closest('td').style.boxShadow = '';
                            e.target.closest('td').style.zIndex = '';
                        }
                    });
                });
            </script>


    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/results/entry.blade.php ENDPATH**/ ?>