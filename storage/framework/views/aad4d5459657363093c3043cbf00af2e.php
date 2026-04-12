<div class="space-y-6">
    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Welcome Back, <?php echo e($student->first_name); ?>!</h1>
                    <p class="mt-2 text-base text-green-50"><?php echo e($student->schoolClass->name ?? 'N/A'); ?><?php echo e($student->section ? ' - ' . $student->section->name : ''); ?></p>
                    <div class="mt-2 flex items-center gap-4 text-sm text-green-100">
                        <span><?php echo e($student->admission_number); ?></span>
                        <span>•</span>
                        <span>Session <?php echo e(config('myacademy.current_session')); ?> — Term <?php echo e(config('myacademy.current_term')); ?></span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                        <img src="<?php echo e($student->passport_photo_url); ?>" alt="<?php echo e($student->full_name); ?>" class="h-20 w-20 rounded-full border-4 border-white shadow-lg object-cover">
                    <?php else: ?>
                        <div class="h-20 w-20 rounded-full border-4 border-white bg-white/20 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            <?php echo e(substr($student->first_name, 0, 1)); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <!-- Attendance -->
        <div class="rounded-2xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Attendance Rate</p>
                    <p class="text-3xl font-bold"><?php echo e($stats['attendance_rate']); ?>%</p>
                    <p class="text-sm text-green-100"><?php echo e($stats['present_days']); ?>/<?php echo e($stats['total_days']); ?> days</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Score -->
        <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Average Score</p>
                    <p class="text-3xl font-bold"><?php echo e($stats['average_score']); ?>%</p>
                    <p class="text-sm text-blue-100"><?php echo e($stats['total_subjects']); ?> subjects</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Class Position -->
        <div class="rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-500 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100">Class Position</p>
                    <p class="text-3xl font-bold">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['position']): ?>
                            #<?php echo e($stats['position']); ?>

                        <?php else: ?>
                            N/A
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                    <p class="text-sm text-yellow-100">of <?php echo e($stats['total_students']); ?> students</p>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Homework -->
        <div class="rounded-2xl bg-gradient-to-br <?php echo e($stats['overdue_homework'] > 0 ? 'from-red-500 to-red-600' : 'from-purple-500 to-purple-600'); ?> p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="<?php echo e($stats['overdue_homework'] > 0 ? 'text-red-100' : 'text-purple-100'); ?>">Pending Homework</p>
                    <p class="text-3xl font-bold"><?php echo e($stats['pending_homework']); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['overdue_homework'] > 0): ?>
                        <p class="text-sm text-red-100"><?php echo e($stats['overdue_homework']); ?> overdue</p>
                    <?php else: ?>
                        <p class="text-sm text-purple-100">All up to date</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="rounded-full bg-white/20 p-3">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Distribution -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($stats['grades']) > 0): ?>
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Grade Distribution</h3>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['A', 'B', 'C', 'D', 'E', 'F']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="text-center p-4 rounded-xl <?php echo e(isset($stats['grades'][$grade]) ? 'bg-green-50 border-2 border-green-200' : 'bg-gray-50 border-2 border-transparent'); ?>">
                    <div class="text-2xl font-bold <?php echo e(isset($stats['grades'][$grade]) ? 'text-green-600' : 'text-gray-300'); ?>"><?php echo e($grade); ?></div>
                    <div class="text-sm text-gray-500 mt-1"><?php echo e($stats['grades'][$grade] ?? 0); ?> <?php echo e(Str::plural('subject', $stats['grades'][$grade] ?? 0)); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="<?php echo e(route('student.homework')); ?>" class="block rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition group">
            <div class="flex items-center gap-4">
                <div class="rounded-full bg-purple-100 p-4 group-hover:bg-purple-200 transition">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-900">View Homework</h4>
                    <p class="text-sm text-gray-500">Check and submit assignments</p>
                </div>
            </div>
        </a>

        <a href="<?php echo e(route('student.results')); ?>" class="block rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition group">
            <div class="flex items-center gap-4">
                <div class="rounded-full bg-blue-100 p-4 group-hover:bg-blue-200 transition">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-900">View Results</h4>
                    <p class="text-sm text-gray-500">Check your academic performance</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/dashboard.blade.php ENDPATH**/ ?>