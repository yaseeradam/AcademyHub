<div class="space-y-5">

    <?php
        $pendingCount   = $homework->filter(fn($h) => $h->submissions->isEmpty() && $h->due_date >= now())->count();
        $overdueCount   = $homework->filter(fn($h) => $h->submissions->isEmpty() && $h->due_date < now())->count();
        $submittedCount = $homework->filter(fn($h) => $h->submissions->isNotEmpty())->count();
    ?>

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Homework</h1>
            <p class="text-sm text-gray-500"><?php echo e($homework->count()); ?> assignment<?php echo e($homework->count() !== 1 ? 's' : ''); ?> assigned</p>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingCount): ?>
                <span class="text-amber-600 font-medium"><?php echo e($pendingCount); ?> pending</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overdueCount): ?>
                <span class="text-red-600 font-medium"><?php echo e($overdueCount); ?> overdue</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($submittedCount): ?>
                <span class="text-green-600 font-medium"><?php echo e($submittedCount); ?> submitted</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="flex gap-1 border-b border-gray-200">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['all' => 'All', 'pending' => 'Pending', 'overdue' => 'Overdue', 'submitted' => 'Submitted']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button wire:click="setFilter('<?php echo e($key); ?>')"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors
                    <?php echo e($filter === $key
                        ? 'border-gray-900 text-gray-900'
                        : 'border-transparent text-gray-500 hover:text-gray-700'); ?>">
                <?php echo e($label); ?>

            </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($homework->isEmpty()): ?>
        <div class="py-16 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-3 text-sm font-medium text-gray-500">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filter === 'pending'): ?> You're all caught up!
                <?php elseif($filter === 'overdue'): ?> No overdue assignments.
                <?php elseif($filter === 'submitted'): ?> Nothing submitted yet.
                <?php else: ?> No homework assigned yet.
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
        </div>

    <?php else: ?>
        <div class="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $homework; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $submitted = $hw->submissions->isNotEmpty();
                    $overdue   = !$submitted && $hw->due_date < now();
                ?>

                <div class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start gap-4 min-w-0">

                        
                        <div class="mt-1 h-2 w-2 flex-shrink-0 rounded-full
                            <?php echo e($submitted ? 'bg-green-500' : ($overdue ? 'bg-red-500' : 'bg-amber-400')); ?>">
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900"><?php echo e($hw->title); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($submitted): ?>
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Submitted</span>
                                <?php elseif($overdue): ?>
                                    <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700">Overdue</span>
                                <?php else: ?>
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">Pending</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-3 text-xs text-gray-400">
                                <span><?php echo e($hw->subject->name); ?></span>
                                <span>·</span>
                                <span>Due <?php echo e($hw->due_date->format('M d, Y')); ?></span>
                                <span>·</span>
                                <span><?php echo e($hw->teacher->name); ?></span>
                            </div>
                        </div>
                    </div>

                    <button wire:click="selectHomework(<?php echo e($hw->id); ?>)"
                        class="flex-shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-gray-300 hover:bg-gray-50">
                        <?php echo e($submitted ? 'View' : ($overdue ? 'Submit late' : 'Submit')); ?>

                    </button>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php $selectedHomework = $this->selectedHomework; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedHomework): ?>
        <div class="fixed inset-0 z-40 bg-black/30" wire:click="closePanel()"></div>

        <div class="fixed inset-y-0 right-0 z-50 flex w-full flex-col bg-white shadow-xl sm:max-w-lg">

            
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-400 mb-1"><?php echo e($selectedHomework->subject->name); ?></p>
                    <h2 class="text-base font-bold text-gray-900"><?php echo e($selectedHomework->title); ?></h2>
                    <div class="mt-1.5 flex flex-wrap gap-3 text-xs text-gray-400">
                        <span>Due <?php echo e($selectedHomework->due_date->format('M d, Y')); ?></span>
                        <span>·</span>
                        <span><?php echo e($selectedHomework->teacher->name); ?></span>
                    </div>
                </div>
                <button wire:click="closePanel()" class="flex-shrink-0 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            
            <div class="flex-1 overflow-y-auto">

                
                <div class="border-b border-gray-100 px-6 py-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Assignment</p>
                    <p class="text-sm leading-relaxed text-gray-700 whitespace-pre-wrap"><?php echo e($selectedHomework->content); ?></p>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedHomework->submissions->isNotEmpty()): ?>
                    <?php $sub = $selectedHomework->submissions->first(); ?>

                    <div class="px-6 py-5 space-y-5">

                        
                        <div class="flex items-center gap-3 rounded-lg bg-green-50 border border-green-100 px-4 py-3">
                            <svg class="h-4 w-4 flex-shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <div>
                                <p class="text-sm font-semibold text-green-800">Submitted</p>
                                <p class="text-xs text-green-600"><?php echo e($sub->submitted_at->format('M d, Y \a\t h:i A')); ?></p>
                            </div>
                        </div>

                        
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Your Answer</p>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                <p class="text-sm leading-relaxed text-gray-800 whitespace-pre-wrap"><?php echo e($sub->submission); ?></p>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->attachment): ?>
                            <a href="<?php echo e(asset('storage/' . $sub->attachment)); ?>" target="_blank"
                               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                View Attachment
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->grade !== null || $sub->feedback): ?>
                            <div class="rounded-lg border border-gray-200 p-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Teacher Feedback</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->grade !== null): ?>
                                    <div class="mb-3 flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-gray-900"><?php echo e((int) $sub->grade); ?></span>
                                        <span class="text-sm text-gray-400">/ 100</span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->feedback): ?>
                                    <p class="text-sm leading-relaxed text-gray-700 whitespace-pre-wrap"><?php echo e($sub->feedback); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->graded_at): ?>
                                    <p class="mt-3 text-xs text-gray-400">Graded <?php echo e($sub->graded_at->format('M d, Y')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-xs text-gray-400">Not graded yet — check back later.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                <?php else: ?>
                    <form wire:submit.prevent="submitHomework" class="px-6 py-5 space-y-4">

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Your Answer <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="submission" rows="8"
                                placeholder="Write your answer here..."
                                class="w-full resize-none rounded-lg border border-gray-300 p-3 text-sm text-gray-800 placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-200 transition"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['submission'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Attachment <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input type="file" wire:model="attachment" id="hw-attachment" class="hidden">
                            <label for="hw-attachment"
                                class="flex cursor-pointer items-center gap-3 rounded-lg border border-dashed border-gray-300 px-4 py-3 text-sm text-gray-500 transition hover:border-gray-400 hover:bg-gray-50">
                                <svg wire:loading.remove wire:target="attachment" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <svg wire:loading wire:target="attachment" class="h-4 w-4 animate-spin text-gray-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <span wire:loading.remove wire:target="attachment">Click to upload — PDF, DOC, JPG, PNG (max 10MB)</span>
                                <span wire:loading wire:target="attachment">Uploading...</span>
                            </label>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachment): ?>
                                <div class="mt-2 flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="flex-1 truncate text-xs text-gray-600"><?php echo e($attachment->getClientOriginalName()); ?></span>
                                    <button type="button" wire:click="$set('attachment', null)" class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['attachment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="button" wire:click="closePanel()"
                                class="flex-1 rounded-lg border border-gray-200 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 rounded-lg bg-gray-900 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                                <span wire:loading.remove wire:target="submitHomework">Submit Homework</span>
                                <span wire:loading wire:target="submitHomework">Submitting...</span>
                            </button>
                        </div>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/homework.blade.php ENDPATH**/ ?>