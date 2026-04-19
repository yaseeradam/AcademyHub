<?php
    use App\Models\Announcement;
    use App\Models\AttendanceMark;
    use App\Models\SchoolClass;
    use App\Models\Score;
    use App\Models\Student;
    use App\Models\SubjectAllocation;
    use App\Models\AcademicTerm;

    $user = auth()->user();

    $classIds = SubjectAllocation::where('teacher_id', $user->id)->pluck('class_id')->unique()->values();
    $subjectIds = SubjectAllocation::where('teacher_id', $user->id)->pluck('subject_id')->unique()->values();

    $classes = $classIds->isEmpty()
        ? collect()
        : SchoolClass::whereIn('id', $classIds)->withCount('students')->orderBy('level')->orderBy('name')->get();

    $studentsCount = $classIds->isEmpty()
        ? 0
        : Student::whereIn('class_id', $classIds)->where('status', 'Active')->count();

    $subjectsCount = (int) $subjectIds->count();

    $pendingSubmissions = 0;

    // Attendance trend (last 7 days) for teacher's classes
    $attendanceData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $present = AttendanceMark::whereHas('sheet', fn($q) => $q->whereDate('date', $date)->whereIn('class_id', $classIds))->where('status', 'Present')->count();
        $absent  = AttendanceMark::whereHas('sheet', fn($q) => $q->whereDate('date', $date)->whereIn('class_id', $classIds))->where('status', 'Absent')->count();
        $attendanceData[] = ['label' => $date->format('D'), 'present' => $present, 'absent' => $absent];
    }

    // Top students from teacher's classes
    $topStudents = Score::whereIn('class_id', $classIds)
        ->selectRaw('student_id, AVG(total) as avg_score')
        ->with('student')
        ->groupBy('student_id')
        ->orderByDesc('avg_score')
        ->limit(5)
        ->get();

    // Subject averages for teacher's subjects
    $subjectStats = Score::whereIn('subject_id', $subjectIds)
        ->whereIn('class_id', $classIds)
        ->selectRaw('subject_id, AVG(total) as avg_score')
        ->with('subject')
        ->groupBy('subject_id')
        ->orderByDesc('avg_score')
        ->limit(5)
        ->get();

    $announcements = Announcement::whereNotNull('published_at')
        ->where(fn($q) => $q->where('audience', 'all')->orWhere('audience', 'staff')->orWhere('audience', $user->role))
        ->orderByDesc('published_at')
        ->limit(4)
        ->get();

    $currentTerm = AcademicTerm::active();
?>



<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative px-8 py-8">
            <div class="flex items-center gap-2 mb-3">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-sm font-semibold uppercase tracking-widest" style="color: #93c5fd;">Teacher Portal</span>
            </div>
            <h2 class="text-4xl font-bold text-white tracking-tight">Welcome, <?php echo e($user->name); ?></h2>
            <p class="mt-2 text-lg font-medium" style="color: #93c5fd;">Manage your classes, scores and attendance.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <?php echo e($currentTerm ? $currentTerm->name : 'No Active Term'); ?>

                </span>
                <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <?php echo e(number_format($studentsCount)); ?> Students
                </span>
                <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php echo e(now()->format('l, F j')); ?>

                </span>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e($classes->count()); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Assigned Classes</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e(number_format($studentsCount)); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Active Students</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e($subjectsCount); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Assigned Subjects</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black"><?php echo e($pendingSubmissions); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Pending Submissions</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">Attendance (Last 7 Days)</div>
                <div class="flex gap-2">
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-600">Present</span>
                    <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-500">Absent</span>
                </div>
            </div>
            <canvas id="teacherAttendanceChart" height="180"></canvas>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">Top Students</div>
                <a href="<?php echo e(route('students.index')); ?>" class="text-xs font-semibold text-orange-500 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php ($student = $row->student); ?>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-shrink-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student?->passport_photo): ?>
                                <img src="<?php echo e(asset('uploads/passports/' . $student->passport_photo)); ?>"
                                     class="h-10 w-10 rounded-full object-cover ring-2 ring-blue-100" alt="">
                            <?php else: ?>
                                <div class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-blue-400 to-violet-500 text-sm font-bold text-white ring-2 ring-blue-100">
                                    <?php echo e(mb_substr($student?->first_name ?? 'S', 0, 1)); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800"><?php echo e($student?->full_name ?? '—'); ?></div>
                            <div class="text-xs text-slate-400"><?php echo e($student?->admission_number ?? ''); ?></div>
                        </div>
                        <div class="text-sm font-bold text-blue-500"><?php echo e(round($row->avg_score)); ?>%</div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-6 text-center text-sm text-slate-400">No scores recorded yet.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 text-base font-bold text-slate-800">Subject Averages</div>
            <canvas id="teacherSubjectChart" height="200"></canvas>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">My Classes</div>
                <a href="<?php echo e(route('classes.index')); ?>" class="text-xs font-semibold text-blue-500 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-800"><?php echo e($class->name); ?></div>
                            <div class="text-xs text-slate-400">Level <?php echo e($class->level); ?></div>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            <?php echo e($class->students_count); ?> students
                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-6 text-center text-sm text-slate-400">No classes assigned yet.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($announcements->isNotEmpty()): ?>
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-4 text-base font-bold text-slate-800">Announcements</div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-800"><?php echo e($announcement->title); ?></div>
                    <div class="mt-1 text-xs text-slate-400"><?php echo e($announcement->published_at?->format('M j, Y')); ?></div>
                    <div class="mt-2 text-sm text-slate-600"><?php echo e(\Illuminate\Support\Str::limit($announcement->body, 100)); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/pages/dashboard.js'); ?>
<script>
    window.teacherDashboardData = {
        attendance: <?php echo json_encode($attendanceData, 15, 512) ?>,
        subjects: <?php echo json_encode($subjectStats->map(fn($s) => ['name' => $s->subject?->name ?? 'N/A', 'avg' => round($s->avg_score)])->values(), 512) ?>,
    };
</script>
<script>
(function () {
    const data = window.teacherDashboardData || {};

    // Attendance line chart
    const attCanvas = document.getElementById('teacherAttendanceChart');
    if (attCanvas) {
        const att = data.attendance || [];
        new Chart(attCanvas, {
            type: 'line',
            data: {
                labels: att.map(d => d.label),
                datasets: [
                    {
                        label: 'Present',
                        data: att.map(d => d.present),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.12)',
                        tension: 0.45, fill: true, pointRadius: 3,
                        pointBackgroundColor: '#3b82f6', borderWidth: 2.5,
                    },
                    {
                        label: 'Absent',
                        data: att.map(d => d.absent),
                        borderColor: '#a78bfa',
                        backgroundColor: 'rgba(167,139,250,0.10)',
                        tension: 0.45, fill: true, pointRadius: 3,
                        pointBackgroundColor: '#a78bfa', borderWidth: 2.5,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { grid: { color: 'rgba(226,232,240,0.8)' }, ticks: { font: { size: 11 } }, beginAtZero: true },
                },
            },
        });
    }

    // Subject averages bar chart
    const subCanvas = document.getElementById('teacherSubjectChart');
    if (subCanvas) {
        const subjects = data.subjects || [];
        const palette = ['#3b82f6', '#a78bfa', '#34d399', '#f97316', '#f472b6'];
        new Chart(subCanvas, {
            type: 'bar',
            data: {
                labels: subjects.map(s => s.name),
                datasets: [{
                    label: 'Avg Score',
                    data: subjects.map(s => s.avg),
                    backgroundColor: subjects.map((_, i) => palette[i % palette.length]),
                    borderRadius: 6, borderSkipped: false, barThickness: 18,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: 'rgba(226,232,240,0.8)' }, ticks: { font: { size: 11 } }, beginAtZero: true, max: 100 },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } },
                },
            },
        });
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/dashboard-teacher.blade.php ENDPATH**/ ?>