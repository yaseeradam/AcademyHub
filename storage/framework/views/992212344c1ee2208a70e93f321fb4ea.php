<?php
    use App\Models\SubjectAllocation;
    use App\Models\User;

    $teachers = User::query()
        ->where('role', 'teacher')
        ->orderBy('name')
        ->get();

    $allocations = SubjectAllocation::query()
        ->with(['subject', 'schoolClass'])
        ->whereIn('teacher_id', $teachers->pluck('id'))
        ->get()
        ->groupBy('teacher_id');
?>



<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <div class="rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Teachers</h1>
                    <p class="mt-1 text-sm text-gray-600">Teaching staff directory and subject coverage</p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->role === 'admin'): ?>
                    <a href="<?php echo e(route('teachers.create')); ?>" class="btn-primary">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        Add Teacher
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-6 rounded-xl border border-gray-200 bg-white p-4">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
                    <input type="text" id="teacherSearch" placeholder="Search by name or email..." class="lg:col-span-3 input" />
                    <select id="statusFilter" class="select">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $rows = $allocations->get($teacher->id, collect());
                    $colors = [
                        ['bg' => 'from-amber-50 to-orange-100/60', 'ring' => 'ring-amber-300/40', 'icon' => 'from-amber-400 to-amber-600', 'shadow' => 'shadow-amber-500/30', 'badge' => 'bg-amber-100 text-amber-700 ring-amber-200', 'accent' => 'bg-amber-500'],
                        ['bg' => 'from-blue-50 to-indigo-100/60', 'ring' => 'ring-blue-300/40', 'icon' => 'from-blue-400 to-blue-600', 'shadow' => 'shadow-blue-500/30', 'badge' => 'bg-blue-100 text-blue-700 ring-blue-200', 'accent' => 'bg-blue-500'],
                        ['bg' => 'from-purple-50 to-violet-100/60', 'ring' => 'ring-purple-300/40', 'icon' => 'from-purple-400 to-purple-600', 'shadow' => 'shadow-purple-500/30', 'badge' => 'bg-purple-100 text-purple-700 ring-purple-200', 'accent' => 'bg-purple-500'],
                        ['bg' => 'from-emerald-50 to-teal-100/60', 'ring' => 'ring-emerald-300/40', 'icon' => 'from-emerald-400 to-emerald-600', 'shadow' => 'shadow-emerald-500/30', 'badge' => 'bg-emerald-100 text-emerald-700 ring-emerald-200', 'accent' => 'bg-emerald-500'],
                        ['bg' => 'from-pink-50 to-rose-100/60', 'ring' => 'ring-pink-300/40', 'icon' => 'from-pink-400 to-pink-600', 'shadow' => 'shadow-pink-500/30', 'badge' => 'bg-pink-100 text-pink-700 ring-pink-200', 'accent' => 'bg-pink-500'],
                    ];
                    $color = $colors[$teacher->id % count($colors)];
                ?>
                <a href="<?php echo e(route('teachers.show', $teacher)); ?>" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br <?php echo e($color['bg']); ?> shadow-lg ring-1 <?php echo e($color['ring']); ?> transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 hover:scale-[1.02]">
                    <div class="h-28 bg-gradient-to-br from-white/20 to-transparent"></div>
                    <div class="absolute right-6 top-6 h-20 w-20 rounded-full bg-white/20"></div>
                    <div class="absolute left-0 bottom-0 h-32 w-32 -translate-x-8 translate-y-8 rounded-full <?php echo e($color['accent']); ?> opacity-10"></div>
                    
                    <div class="-mt-16 px-6">
                        <div class="flex items-start gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($teacher->profile_photo_url): ?>
                                <img
                                    src="<?php echo e($teacher->profile_photo_url); ?>"
                                    alt="<?php echo e($teacher->name); ?>"
                                    class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white shadow-xl transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
                                />
                            <?php else: ?>
                                <div class="grid h-24 w-24 place-items-center rounded-2xl bg-gradient-to-br <?php echo e($color['icon']); ?> text-white shadow-xl <?php echo e($color['shadow']); ?> ring-4 ring-white transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3">
                                    <span class="text-3xl font-black"><?php echo e(mb_substr($teacher->name, 0, 1)); ?></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="min-w-0 flex-1 pt-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-lg font-black text-slate-900"><?php echo e($teacher->name); ?></div>
                                        <div class="mt-1 truncate text-sm text-slate-600"><?php echo e($teacher->email); ?></div>
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => ''.e($teacher->is_active ? 'success' : 'warning').'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($teacher->is_active ? 'success' : 'warning').'']); ?>
                                        <?php echo e($teacher->is_active ? 'Active' : 'Inactive'); ?>

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
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-white/80 px-4 py-3 ring-1 ring-white/60 backdrop-blur-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider <?php echo e(str_replace('bg-', 'text-', $color['accent'])); ?>">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                    </svg>
                                    Allocations
                                </div>
                                <div class="text-2xl font-black text-slate-900"><?php echo e(number_format((int) $rows->count())); ?></div>
                            </div>
                        </div>

                        <div class="mt-4 pb-6">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->isEmpty()): ?>
                                <div class="rounded-2xl bg-white/60 px-4 py-3 text-center text-sm font-semibold text-slate-600 ring-1 ring-white/40 backdrop-blur-sm">
                                    No allocations yet
                                </div>
                            <?php else: ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center rounded-xl <?php echo e($color['badge']); ?> px-3 py-1.5 text-xs font-bold ring-1">
                                            <?php echo e($alloc->subject?->code ?? 'SUB'); ?> · <?php echo e($alloc->schoolClass?->name ?? 'Class'); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->count() > 6): ?>
                                        <span class="inline-flex items-center rounded-xl bg-white/80 px-3 py-1.5 text-xs font-bold text-slate-700 ring-1 ring-white/60">+<?php echo e($rows->count() - 6); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card-padded sm:col-span-2 xl:col-span-3">
                    <div class="text-sm font-semibold text-slate-900">No teachers found</div>
                    <div class="mt-1 text-sm text-slate-600">Create your first teacher account to begin allocations.</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->role === 'admin'): ?>
                        <div class="mt-4">
                            <a href="<?php echo e(route('teachers.create')); ?>" class="btn-primary">Add Teacher</a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('teacherSearch');
    const statusFilter = document.getElementById('statusFilter');
    const teacherCards = document.querySelectorAll('.grid > a');

    function filterTeachers() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        teacherCards.forEach(card => {
            const name = card.querySelector('.text-lg')?.textContent.toLowerCase() || '';
            const email = card.querySelector('.text-sm.text-slate-600')?.textContent.toLowerCase() || '';
            const statusBadge = card.querySelector('[class*="status-badge"]')?.textContent.toLowerCase() || '';
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || 
                (statusValue === 'active' && statusBadge.includes('active')) ||
                (statusValue === 'inactive' && statusBadge.includes('inactive'));

            card.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', filterTeachers);
    statusFilter?.addEventListener('change', filterTeachers);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/teachers/index.blade.php ENDPATH**/ ?>