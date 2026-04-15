<?php
    $user = auth()->user();
    $submissionStatus = $this->submission?->status;
    $locked = $user?->role === 'teacher' && (in_array($submissionStatus, ['submitted', 'approved'], true) || $this->isPublished);
?>

<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Score Entry','subtitle' => 'Enter CA and Exam scores for students','accent' => 'results']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Score Entry','subtitle' => 'Enter CA and Exam scores for students','accent' => 'results']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'admin'): ?>
                <a href="<?php echo e(route('results.submissions')); ?>" class="btn-primary">Score Submissions</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <a href="<?php echo e(route('results.broadsheet')); ?>" class="btn-outline">Broadsheet</a>
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

    <div class="card-padded">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                <select wire:key="class-dropdown-<?php echo e($classId ?: 'empty'); ?>" wire:model.live="classId"
                    class="mt-2 select">
                    <option value="">Select class</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Subject</label>
                <select wire:key="subject-dropdown-<?php echo e($classId ?: 'empty'); ?>" wire:model.live="subjectId"
                    <?php if(!$classId): echo 'disabled'; endif; ?> class="mt-2 select">
                    <option value="">Select subject</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                <select wire:model.live="term" class="mt-2 select">
                    <option value="1">Term 1</option>
                    <option value="2">Term 2</option>
                    <option value="3">Term 3</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                <input wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026"
                    class="mt-2 input-compact" />
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="button" wire:click="save" <?php if(!$classId || !$subjectId || $locked): echo 'disabled'; endif; ?> class="btn-primary">
                Save Scores
            </button>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'teacher'): ?>
                <button type="button" wire:click="submitScores" <?php if(!$classId || !$subjectId || $locked): echo 'disabled'; endif; ?>
                    class="btn-outline">
                    Submit to Admin
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isPublished): ?>
                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success']); ?>Published <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->role === 'teacher' && $this->submission): ?>
                <?php
                    $status = $this->submission->status ?? 'submitted';
                    $variant = match ($status) {
                        'approved' => 'success',
                        'rejected' => 'warning',
                        'submitted' => 'info',
                        default => 'neutral',
                    };
                ?>
                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => ''.e($variant).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($variant).'']); ?><?php echo e(ucfirst($status)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'rejected' && $this->submission->note): ?>
                    <span class="text-xs text-orange-700">Note: <?php echo e($this->submission->note); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$classId): ?>
        <div class="card-padded text-center">
            <div class="text-lg font-semibold text-gray-900">Select a class</div>
            <div class="mt-2 text-sm text-gray-600">Choose a class to load students and subjects.</div>
        </div>
    <?php elseif(!$subjectId): ?>
        <div class="card-padded text-center">
            <div class="text-lg font-semibold text-gray-900">Select a subject</div>
            <div class="mt-2 text-sm text-gray-600">Only allocated subjects are shown for teachers.</div>
        </div>
    <?php else: ?>
            <?php
                $maxMarks = $this->maxMarks();
            ?>
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
                <thead class="bg-white text-xs font-bold uppercase tracking-wider text-gray-900">
                    <tr>
                        <th class="px-5 py-4 border-b-2 border-gray-300">Student</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">CA1 /<?php echo e($maxMarks['ca1']); ?></th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">CA2 /<?php echo e($maxMarks['ca2']); ?></th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">Exam /<?php echo e($maxMarks['exam']); ?></th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">Total</th>
                        <th class="px-5 py-4 text-right border-b-2 border-gray-300">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
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
                        <tr class="bg-white hover:bg-indigo-50 transition-colors <?php echo e($hasError ? 'error-row' : ''); ?>" 
                            data-student-id="<?php echo e($student->id); ?>">
                            <td class="px-5 py-4 bg-gray-100">
                                <div class="text-sm font-semibold text-gray-900"><?php echo e($student->full_name); ?></div>
                                <div class="mt-1 text-xs text-gray-500"><?php echo e($student->admission_number); ?></div>
                            </td>
                            <td class="px-5 py-4 text-right <?php echo e($ca1Error ? 'bg-red-100' : 'bg-blue-100'); ?>">
                                <input wire:model.lazy="scores.<?php echo e($student->id); ?>.ca1" 
                                    type="number" min="0" max="<?php echo e($maxMarks['ca1']); ?>" step="1"
                                    data-student-id="<?php echo e($student->id); ?>" data-field="ca1"
                                    class="w-20 rounded-lg border-2 <?php echo e($ca1Error ? 'border-red-500 bg-red-50' : 'border-blue-300 bg-white'); ?> px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500" />
                            </td>
                            <td class="px-5 py-4 text-right <?php echo e($ca2Error ? 'bg-red-100' : 'bg-green-100'); ?>">
                                <input wire:model.lazy="scores.<?php echo e($student->id); ?>.ca2" 
                                    type="number" min="0" max="<?php echo e($maxMarks['ca2']); ?>" step="1"
                                    data-student-id="<?php echo e($student->id); ?>" data-field="ca2"
                                    class="w-20 rounded-lg border-2 <?php echo e($ca2Error ? 'border-red-500 bg-red-50' : 'border-green-300 bg-white'); ?> px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500" />
                            </td>
                            <td class="px-5 py-4 text-right <?php echo e($examError ? 'bg-red-100' : 'bg-amber-100'); ?>">
                                <input wire:model.lazy="scores.<?php echo e($student->id); ?>.exam" 
                                    type="number" min="0" max="<?php echo e($maxMarks['exam']); ?>" step="1"
                                    data-student-id="<?php echo e($student->id); ?>" data-field="exam"
                                    class="w-20 rounded-lg border-2 <?php echo e($examError ? 'border-red-500 bg-red-50' : 'border-amber-300 bg-white'); ?> px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500" />
                            </td>
                            <td class="px-5 py-4 text-right bg-purple-100">
                                <span
                                    class="inline-flex items-center justify-center rounded-lg bg-purple-100 px-3 py-2 text-sm font-bold text-purple-900"><?php echo e($total); ?></span>
                            </td>
                            <td class="px-5 py-4 text-right bg-gray-50">
                                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => ''.e(in_array($grade, ['A', 'B'], true) ? 'success' : 'neutral').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e(in_array($grade, ['A', 'B'], true) ? 'success' : 'neutral').'']); ?>
                                    <?php echo e($grade); ?>

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
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No students in this class.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

            <div class="mt-4 rounded-lg bg-blue-50 p-4">
                <div class="text-xs font-semibold text-blue-900 mb-2">⌨️ Keyboard Shortcuts:</div>
                <div class="grid grid-cols-2 gap-2 text-xs text-blue-700">
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">Enter</kbd> Move down</div>
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">Tab</kbd> Move right</div>
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">↑↓←→</kbd> Arrow keys</div>
                    <div><kbd class="px-2 py-1 bg-white rounded shadow-sm">Shift+Tab</kbd> Move left</div>
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