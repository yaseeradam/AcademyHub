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
                <div class="relative overflow-hidden rounded-xl border <?php echo e($exam['status'] === 'in_progress' ? 'border-amber-400 bg-amber-50 shadow-md ring-2 ring-amber-400/20' : 'border-gray-200 bg-white shadow-sm'); ?> p-6 transition-all hover:shadow-md">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam['status'] === 'in_progress'): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-amber-400 px-3 py-1 text-xs font-bold text-amber-900 shadow">IN PROGRESS</div>
                    <?php elseif($exam['status'] === 'completed'): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">COMPLETED</div>
                    <?php elseif($exam['ends_at'] && \Carbon\Carbon::parse($exam['ends_at'])->diffInHours(now()) <= 24): ?>
                        <div class="absolute right-0 top-0 rounded-bl-lg bg-red-100 px-3 py-1 text-xs font-bold text-red-800">ENDING SOON</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="mb-4">
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam['status'] === 'completed' && $exam['needs_review']): ?>
                            <div class="mt-2 text-sm font-semibold text-amber-600">Pending Review</div>
                        <?php elseif($exam['status'] === 'completed' && $exam['score'] !== null): ?>
                            <div class="mt-2 text-sm font-semibold">
                                Score: <span class="text-emerald-700"><?php echo e($exam['score']); ?></span> / <?php echo e($exam['max_score']); ?>

                            </div>
                        <?php elseif($exam['status'] === 'completed' && ($exam['score_pending'] ?? false)): ?>
                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Results pending release
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mt-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam['status'] === 'completed'): ?>
                            <button disabled class="w-full rounded-lg bg-gray-100 py-2.5 text-sm font-semibold text-gray-400">Exam completed</button>
                        <?php else: ?>
                            <a href="<?php echo e(route('cbt.student', ['code' => $exam['access_code']])); ?>" class="block w-full text-center rounded-lg <?php echo e($exam['status'] === 'in_progress' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-gray-900 hover:bg-gray-800'); ?> py-2.5 text-sm font-semibold text-white transition">
                                <?php echo e($exam['status'] === 'in_progress' ? 'Resume Exam' : 'Start Exam'); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/exams.blade.php ENDPATH**/ ?>