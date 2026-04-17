<div class="space-y-6">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-500 via-violet-500 to-indigo-600 p-8 shadow-2xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-white"><?php echo e($class->name); ?> - Subjects</h1>
                <p class="mt-2 text-purple-100">Assign default subjects for all students in this class</p>
            </div>
            <a href="<?php echo e(route('classes.index')); ?>" class="rounded-xl bg-white/20 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/30">Back to Classes</a>
        </div>
    </div>

    <div class="rounded-2xl border border-purple-100 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-lg backdrop-blur-sm">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-purple-900">Select Subjects</div>
                <div class="text-sm text-purple-700">All students in <?php echo e($class->name); ?> will automatically get these subjects</div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $allSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-purple-200 bg-white p-4 shadow-sm transition-all hover:border-purple-300 hover:shadow-md <?php echo e(in_array($subject->id, $selectedSubjects) ? 'ring-2 ring-purple-500' : ''); ?>">
                    <input type="checkbox" wire:model="selectedSubjects" value="<?php echo e($subject->id); ?>" class="h-5 w-5 rounded border-purple-300 text-purple-600 focus:ring-purple-500">
                    <div class="flex-1">
                        <div class="text-sm font-bold text-gray-900"><?php echo e($subject->name); ?></div>
                        <div class="text-xs text-gray-600"><?php echo e($subject->code); ?></div>
                    </div>
                </label>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="mt-6 flex items-center justify-between rounded-xl bg-purple-50 p-4">
            <div class="text-sm text-purple-700">
                <span class="font-bold"><?php echo e(count($selectedSubjects)); ?></span> subject(s) selected
            </div>
            <button wire:click="save" class="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg hover:from-purple-600 hover:to-indigo-700">
                Save Changes
            </button>
        </div>
    </div>

    <div class="rounded-2xl border border-purple-100 bg-gradient-to-br from-white to-purple-50/30 p-6 shadow-lg backdrop-blur-sm">
        <div class="flex items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-sm font-bold text-purple-900">How It Works</div>
                <div class="text-sm text-purple-700">Automatic subject assignment</div>
            </div>
        </div>

        <div class="mt-4 space-y-3 text-sm text-gray-700">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 h-2 w-2 rounded-full bg-purple-500"></div>
                <div>All students in <strong><?php echo e($class->name); ?></strong> will automatically get the selected subjects</div>
            </div>
            <div class="flex items-start gap-3">
                <div class="mt-0.5 h-2 w-2 rounded-full bg-purple-500"></div>
                <div>New students added to this class will inherit these subjects</div>
            </div>
            <div class="flex items-start gap-3">
                <div class="mt-0.5 h-2 w-2 rounded-full bg-purple-500"></div>
                <div>You can still add/remove subjects for individual students from their profile</div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/classes/manage-subjects.blade.php ENDPATH**/ ?>