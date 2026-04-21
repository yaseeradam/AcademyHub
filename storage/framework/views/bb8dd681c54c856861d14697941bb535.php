<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Exams</h1>
            <p class="text-sm text-gray-500">View and take your assigned exams.</p>
        </div>
        <div x-data="{ code: '' }" class="flex items-center gap-2">
            <input x-model="code" type="text" placeholder="Access Code (e.g. CBT-123)" class="rounded-lg border px-4 py-2 text-sm font-mono uppercase w-56" @keydown.enter="$wire.enterExamCode(code)" />
            <button @click="$wire.enterExamCode(code)" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Join</button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($exams)): ?>
        <div class="rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-500">
            No exams currently available.
        </div>
    <?php else: ?>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isMarked = $exam['can_view_marks'];
                    $isPending = $exam['score_pending'] ?? false;
                    $isTheoryPending = $exam['status'] === 'completed' && $exam['has_theory'] && $exam['theory_status'] !== 'marked';
                ?>
                <div class="relative overflow-hidden rounded-xl border
                    <?php echo e($exam['status'] === 'in_progress' ? 'border-amber-400 bg-amber-50 shadow-md ring-2 ring-amber-400/20' : ($isMarked ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 bg-white')); ?>

                    p-6 transition-all hover:shadow-md">

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam['status'] === 'in_progress'): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-amber-400 px-3 py-1 text-xs font-bold text-amber-900 shadow">IN PROGRESS</div>
                    <?php elseif($isMarked): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-emerald-500 px-3 py-1 text-xs font-bold text-white shadow">MARKED ✓</div>
                    <?php elseif($exam['status'] === 'completed'): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-gray-200 px-3 py-1 text-xs font-bold text-gray-700">COMPLETED</div>
                    <?php elseif($exam['ends_at'] && \Carbon\Carbon::parse($exam['ends_at'])->diffInHours(now()) <= 24): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-red-100 px-3 py-1 text-xs font-bold text-red-800">ENDING SOON</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="mb-4 mt-2">
                        <div class="text-xs font-semibold text-violet-600 uppercase tracking-wider"><?php echo e($exam['subject']); ?></div>
                        <h3 class="mt-1 text-lg font-bold text-gray-900 line-clamp-2 leading-tight"><?php echo e($exam['title']); ?></h3>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span><?php echo e($exam['duration']); ?> minutes</span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam['ends_at']): ?>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Due: <?php echo e(\Carbon\Carbon::parse($exam['ends_at'])->format('M d, Y h:i A')); ?></span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMarked): ?>
                            <?php
                                $pct = (int) $exam['percent'];
                                $scoreColor = $pct >= 70 ? 'text-emerald-700' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600');
                            ?>
                            <div class="mt-3 flex items-center justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-emerald-200">
                                <span class="text-xs font-semibold text-gray-500">Score</span>
                                <span class="text-base font-extrabold <?php echo e($scoreColor); ?>">
                                    <?php echo e($exam['score']); ?>/<?php echo e($exam['max_score']); ?>

                                    <span class="text-xs font-semibold">(<?php echo e($pct); ?>%)</span>
                                </span>
                            </div>
                        <?php elseif($isTheoryPending): ?>
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Awaiting theory marking
                            </div>
                        <?php elseif($isPending): ?>
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Results pending release
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mt-5 flex flex-col gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam['status'] === 'completed'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isMarked && $exam['attempt_id']): ?>
                                <button
                                    wire:click="viewResult(<?php echo e($exam['attempt_id']); ?>)"
                                    class="w-full rounded-lg <?php echo e($viewingAttempt && $viewingAttempt->id === $exam['attempt_id'] ? 'bg-violet-600 text-white' : 'bg-violet-100 text-violet-700 hover:bg-violet-200'); ?> py-2.5 text-sm font-semibold transition">
                                    <?php echo e($viewingAttempt && $viewingAttempt->id === $exam['attempt_id'] ? 'Hide Marks' : 'View Marks'); ?>

                                </button>
                            <?php else: ?>
                                <button disabled class="w-full rounded-lg bg-gray-100 py-2.5 text-sm font-semibold text-gray-400">Exam completed</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo e(route('cbt.student', ['code' => $exam['access_code']])); ?>"
                               class="block w-full text-center rounded-lg <?php echo e($exam['status'] === 'in_progress' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-gray-900 hover:bg-gray-800'); ?> py-2.5 text-sm font-semibold text-white transition">
                                <?php echo e($exam['status'] === 'in_progress' ? 'Resume Exam' : 'Start Exam'); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewingAttempt): ?>
        <?php
            $vExam      = $viewingAttempt->exam;
            $vAnswers   = $viewingAttempt->answers->keyBy('question_id');
            $vQuestions = $vExam->questions->sortBy('position')->values();
            $totalMarks = $vQuestions->sum('marks');
            $earnedMarks = (int) $viewingAttempt->score;
            $pct = (int) $viewingAttempt->percent;
            $grade = $pct >= 70 ? 'Excellent' : ($pct >= 50 ? 'Good' : 'Needs Work');
            $gradeColor = $pct >= 70 ? 'text-emerald-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-600');
        ?>
        <div class="rounded-2xl border border-violet-200 bg-white shadow-lg overflow-hidden">
            
            <div class="bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-violet-200">Result Breakdown</div>
                        <h2 class="mt-1 text-xl font-bold text-white"><?php echo e($vExam->title); ?></h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <div class="text-3xl font-extrabold text-white"><?php echo e($earnedMarks); ?><span class="text-lg font-semibold text-violet-200">/<?php echo e($totalMarks); ?></span></div>
                            <div class="text-xs font-semibold text-violet-200"><?php echo e($pct); ?>% &bull; <span class="<?php echo e($gradeColor); ?> text-white"><?php echo e($grade); ?></span></div>
                        </div>
                        <button wire:click="viewResult(<?php echo e($viewingAttempt->id); ?>)" class="rounded-lg bg-white/20 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/30">Close ✕</button>
                    </div>
                </div>
            </div>

            
            <div class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $vQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $answer     = $vAnswers->get($q->id);
                        $qType      = $q->type ?? 'mcq';
                        $awarded    = $qType === 'theory' ? (int) ($answer?->awarded_marks ?? 0) : ($answer?->is_correct ? (int) $q->marks : 0);
                        $maxMark    = (int) $q->marks;
                        $isCorrect  = $qType === 'mcq' ? (bool) ($answer?->is_correct) : null;
                        $rowBg      = $qType === 'mcq'
                            ? ($isCorrect ? 'bg-emerald-50' : 'bg-red-50')
                            : ($awarded >= $maxMark ? 'bg-emerald-50' : ($awarded > 0 ? 'bg-amber-50' : 'bg-red-50'));
                    ?>
                    <div class="px-6 py-4 <?php echo e($rowBg); ?>">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-gray-500">Q<?php echo e($idx + 1); ?></span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase <?php echo e($qType === 'theory' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'); ?>"><?php echo e($qType); ?></span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900"><?php echo e($q->prompt); ?></p>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qType === 'mcq'): ?>
                                    <?php
                                        $selectedOption = $answer?->option_id
                                            ? $q->options->firstWhere('id', $answer->option_id)
                                            : null;
                                        $correctOption = $q->options->firstWhere('is_correct', true);
                                    ?>
                                    <div class="mt-2 space-y-1">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $q->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $isSelected = $answer?->option_id === $opt->id;
                                                $isRight    = (bool) $opt->is_correct;
                                            ?>
                                            <div class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs
                                                <?php echo e($isRight ? 'bg-emerald-100 font-semibold text-emerald-800' : ($isSelected && !$isRight ? 'bg-red-100 font-semibold text-red-800' : 'text-gray-600')); ?>">
                                                <span class="font-bold"><?php echo e(chr(65 + $loop->index)); ?>.</span>
                                                <span><?php echo e($opt->label); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSelected && $isRight): ?> <span class="ml-auto">✓ Your answer</span>
                                                <?php elseif($isSelected && !$isRight): ?> <span class="ml-auto">✗ Your answer</span>
                                                <?php elseif($isRight): ?> <span class="ml-auto">✓ Correct</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    
                                    <div class="mt-2 rounded-lg bg-white px-3 py-2 text-xs text-gray-700 ring-1 ring-gray-200">
                                        <?php echo e(trim((string) ($answer?->text_answer ?? '')) ?: 'No answer submitted.'); ?>

                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($answer?->teacher_comment): ?>
                                        <div class="mt-1 text-xs text-violet-700 font-semibold">Teacher: <?php echo e($answer->teacher_comment); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            
                            <div class="flex-shrink-0 text-center min-w-[52px]">
                                <div class="rounded-xl px-3 py-2 text-center
                                    <?php echo e($awarded === $maxMark ? 'bg-emerald-100 text-emerald-800' : ($awarded > 0 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')); ?>">
                                    <div class="text-lg font-extrabold"><?php echo e($awarded); ?></div>
                                    <div class="text-[10px] font-semibold">/<?php echo e($maxMark); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-t border-gray-200">
                <span class="text-sm font-bold text-gray-700">Total Score</span>
                <span class="text-xl font-extrabold <?php echo e($gradeColor); ?>"><?php echo e($earnedMarks); ?> / <?php echo e($totalMarks); ?> (<?php echo e($pct); ?>%)</span>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/exams.blade.php ENDPATH**/ ?>