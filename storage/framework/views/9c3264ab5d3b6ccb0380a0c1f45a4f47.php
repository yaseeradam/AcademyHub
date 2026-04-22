<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Result Scoring','subtitle' => 'Configure maximum marks for CA and Exam.','accent' => 'settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Result Scoring','subtitle' => 'Configure maximum marks for CA and Exam.','accent' => 'settings']); ?>
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

        <div class="flex gap-2">
            <a href="<?php echo e(route('settings.index')); ?>" class="btn-outline">← Back to Settings</a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
                <?php echo e(session('status')); ?>

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

        <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-purple-50 to-fuchsia-50/60 p-6 shadow-lg max-w-2xl">
            <div class="flex items-center gap-3 mb-5">
                <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white shadow-lg shadow-purple-500/30">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                </div>
                <div class="text-lg font-black text-gray-900">Maximum Marks Configuration</div>
            </div>

            <form method="POST" action="<?php echo e(route('settings.update-results')); ?>">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div class="text-sm font-semibold text-gray-600">
                        Set the maximum marks for CA and Exam (used for validation and grading).
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">CA1 Max</label>
                            <input
                                name="results_ca1_max"
                                type="number"
                                min="0"
                                max="200"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-purple-500"
                                value="<?php echo e(old('results_ca1_max', config('myacademy.results_ca1_max', 20))); ?>"
                                required
                            />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">CA2 Max</label>
                            <input
                                name="results_ca2_max"
                                type="number"
                                min="0"
                                max="200"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-purple-500"
                                value="<?php echo e(old('results_ca2_max', config('myacademy.results_ca2_max', 20))); ?>"
                                required
                            />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Exam Max</label>
                            <input
                                name="results_exam_max"
                                type="number"
                                min="0"
                                max="200"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-purple-500"
                                value="<?php echo e(old('results_exam_max', config('myacademy.results_exam_max', 60))); ?>"
                                required
                            />
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-purple-700 transition-all">
                        Save Scoring Marks
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/settings/results.blade.php ENDPATH**/ ?>