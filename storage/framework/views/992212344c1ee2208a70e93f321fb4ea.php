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
    <div class="space-y-8">
        
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-500 via-blue-600 to-indigo-700 p-8 shadow-2xl">
            <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="absolute right-6 bottom-6 h-16 w-16 rounded-full bg-white/10"></div>
            
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                        <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white">Teaching Staff</h1>
                        <p class="mt-1 text-sky-100">Manage teachers and their subject allocations</p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->role === 'admin'): ?>
                    <a href="<?php echo e(route('teachers.create')); ?>" 
                       class="flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-sky-600 shadow-lg transition-all hover:bg-sky-50 hover:shadow-xl">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Teacher
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e($teachers->count()); ?></div>
                    <div class="mt-1 text-sm font-semibold text-emerald-100">Total Teachers</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e($teachers->where('is_active', true)->count()); ?></div>
                    <div class="mt-1 text-sm font-semibold text-blue-100">Active</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-400 to-violet-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e($teachers->where('is_class_teacher', true)->count()); ?></div>
                    <div class="mt-1 text-sm font-semibold text-purple-100">Class Teachers</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e($allocations->sum(fn($allocs) => $allocs->count())); ?></div>
                    <div class="mt-1 text-sm font-semibold text-amber-100">Total Allocations</div>
                </div>
            </div>
        </div>

        
        <div class="rounded-3xl bg-white p-6 shadow-xl ring-1 ring-gray-200">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="relative lg:col-span-2">
                    <svg class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input type="text" 
                           id="teacherSearch" 
                           placeholder="Search by name or email..." 
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-4 text-sm font-medium transition-all focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-500/20" />
                </div>
                <select id="statusFilter" 
                        class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-medium transition-all focus:border-sky-500 focus:bg-white focus:ring-2 focus:ring-sky-500/20">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="font-bold text-emerald-800"><?php echo e(session('status')); ?></p>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $rows = $allocations->get($teacher->id, collect());
                    $colors = [
                        ['bg' => 'from-amber-50 to-orange-100', 'ring' => 'ring-amber-200', 'icon' => 'from-amber-400 to-orange-500', 'badge' => 'bg-amber-100 text-amber-800', 'accent' => 'bg-amber-500'],
                        ['bg' => 'from-blue-50 to-indigo-100', 'ring' => 'ring-blue-200', 'icon' => 'from-blue-400 to-indigo-500', 'badge' => 'bg-blue-100 text-blue-800', 'accent' => 'bg-blue-500'],
                        ['bg' => 'from-purple-50 to-violet-100', 'ring' => 'ring-purple-200', 'icon' => 'from-purple-400 to-violet-500', 'badge' => 'bg-purple-100 text-purple-800', 'accent' => 'bg-purple-500'],
                        ['bg' => 'from-emerald-50 to-teal-100', 'ring' => 'ring-emerald-200', 'icon' => 'from-emerald-400 to-teal-500', 'badge' => 'bg-emerald-100 text-emerald-800', 'accent' => 'bg-emerald-500'],
                        ['bg' => 'from-pink-50 to-rose-100', 'ring' => 'ring-pink-200', 'icon' => 'from-pink-400 to-rose-500', 'badge' => 'bg-pink-100 text-pink-800', 'accent' => 'bg-pink-500'],
                    ];
                    $color = $colors[$teacher->id % count($colors)];
                ?>
                <a href="<?php echo e(route('teachers.show', $teacher)); ?>" 
                   class="group relative overflow-hidden rounded-3xl bg-gradient-to-br <?php echo e($color['bg']); ?> shadow-xl ring-1 <?php echo e($color['ring']); ?> transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02]">
                    
                    
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-6 -left-6 h-20 w-20 rounded-full <?php echo e($color['accent']); ?> opacity-10"></div>
                    
                    <div class="p-6">
                        
                        <div class="flex items-start gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($teacher->profile_photo_url): ?>
                                <img src="<?php echo e($teacher->profile_photo_url); ?>"
                                     alt="<?php echo e($teacher->name); ?>"
                                     class="h-16 w-16 rounded-2xl object-cover ring-4 ring-white shadow-lg transition-transform duration-300 group-hover:scale-110" />
                            <?php else: ?>
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br <?php echo e($color['icon']); ?> text-white shadow-lg ring-4 ring-white transition-transform duration-300 group-hover:scale-110">
                                    <span class="text-2xl font-black"><?php echo e(mb_substr($teacher->name, 0, 1)); ?></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-lg font-black text-gray-900"><?php echo e($teacher->name); ?></h3>
                                        <p class="truncate text-sm text-gray-600"><?php echo e($teacher->email); ?></p>
                                    </div>
                                    <span class="status-badge inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold <?php echo e($teacher->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'); ?>">
                                        <?php echo e($teacher->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </div>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($teacher->is_class_teacher): ?>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center rounded-lg bg-gradient-to-r <?php echo e($color['icon']); ?> px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                                            <svg class="mr-1 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 10 12 5 2 10l10 5 10-5z"/>
                                                <path d="M6 12v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-5"/>
                                            </svg>
                                            Class Teacher
                                        </span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-gray-700">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                    </svg>
                                    Subject Allocations
                                </div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/80 text-sm font-black text-gray-900">
                                    <?php echo e($rows->count()); ?>

                                </div>
                            </div>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->isEmpty()): ?>
                                <div class="rounded-2xl bg-white/60 px-4 py-3 text-center text-sm font-medium text-gray-600">
                                    No allocations yet
                                </div>
                            <?php else: ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center rounded-xl <?php echo e($color['badge']); ?> px-3 py-1.5 text-xs font-bold">
                                            <?php echo e($alloc->subject?->code ?? 'SUB'); ?> • <?php echo e($alloc->schoolClass?->name ?? 'Class'); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rows->count() > 4): ?>
                                        <span class="inline-flex items-center rounded-xl bg-white/80 px-3 py-1.5 text-xs font-bold text-gray-700">
                                            +<?php echo e($rows->count() - 4); ?> more
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        
                        <div class="mt-4 flex items-center justify-end">
                            <div class="flex items-center gap-1 text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                <span>View Details</span>
                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="sm:col-span-2 xl:col-span-3">
                    <div class="rounded-3xl bg-white p-12 text-center shadow-xl ring-1 ring-gray-200">
                        <div class="flex flex-col items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100">
                                <svg class="h-8 w-8 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">No teachers found</h3>
                                <p class="mt-1 text-sm text-gray-500">Create your first teacher account to begin allocations</p>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->role === 'admin'): ?>
                                <a href="<?php echo e(route('teachers.create')); ?>" 
                                   class="mt-2 flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-blue-500 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:from-sky-600 hover:to-blue-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Teacher
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
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
            const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
            const email = card.querySelector('p')?.textContent.toLowerCase() || '';
            const statusBadgeNode = card.querySelector('.status-badge');
            const statusBadge = statusBadgeNode ? statusBadgeNode.textContent.trim().toLowerCase() : '';
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = statusValue === 'all' || statusBadge === statusValue;

            card.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
        
        // Show/hide empty state
        const visibleCards = Array.from(teacherCards).filter(card => card.style.display !== 'none');
        const emptyState = document.querySelector('.grid > div');
        if (emptyState && visibleCards.length === 0 && teacherCards.length > 0) {
            emptyState.style.display = '';
        } else if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    searchInput?.addEventListener('input', filterTeachers);
    statusFilter?.addEventListener('change', filterTeachers);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/teachers/index.blade.php ENDPATH**/ ?>