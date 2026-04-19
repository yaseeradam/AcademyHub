<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">My Results</h2>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Session</label>
                <select wire:model.live="selectedSession" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($session); ?>"><?php echo e($session); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Term</label>
                <select wire:model.live="selectedTerm" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($term); ?>">Term <?php echo e($term); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($scores->isEmpty()): ?>
        <div class="rounded-2xl bg-white p-12 text-center shadow-lg">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">No results available</h3>
            <p class="mt-2 text-sm text-gray-600">Results for this session and term haven't been published yet.</p>
        </div>
    <?php else: ?>
        <div class="rounded-2xl bg-white shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">CA1</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">CA2</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Exam</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Position</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $scores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900"><?php echo e($score->subject->name); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900"><?php echo e($score->ca1); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900"><?php echo e($score->ca2); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900"><?php echo e($score->exam); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm font-bold text-gray-900"><?php echo e($score->total); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                        <?php if($score->grade === 'A'): ?> bg-green-100 text-green-800
                                        <?php elseif($score->grade === 'B'): ?> bg-blue-100 text-blue-800
                                        <?php elseif($score->grade === 'C'): ?> bg-yellow-100 text-yellow-800
                                        <?php elseif($score->grade === 'D'): ?> bg-orange-100 text-orange-800
                                        <?php else: ?> bg-red-100 text-red-800
                                        <?php endif; ?>">
                                        <?php echo e($score->grade); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-sm text-gray-900">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($score->position): ?>
                                            <?php echo e($score->position); ?><?php echo e($score->position == 1 ? 'st' : ($score->position == 2 ? 'nd' : ($score->position == 3 ? 'rd' : 'th'))); ?>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">TOTAL</td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-900"><?php echo e($scores->sum('ca1')); ?></td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-900"><?php echo e($scores->sum('ca2')); ?></td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-900"><?php echo e($scores->sum('exam')); ?></td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-gray-900"><?php echo e($scores->sum('total')); ?></td>
                            <td colspan="2" class="px-6 py-4 text-center text-sm font-bold text-gray-900">
                                Average: <?php echo e(round($scores->avg('total'), 1)); ?>%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl bg-white p-6 shadow-lg">
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-600">Total Subjects</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($scores->count()); ?></p>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-lg">
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-600">Average Score</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e(round($scores->avg('total'), 1)); ?>%</p>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-lg">
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-600">Highest Score</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($scores->max('total')); ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?php echo e($scores->where('total', $scores->max('total'))->first()->subject->name ?? ''); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/results.blade.php ENDPATH**/ ?>