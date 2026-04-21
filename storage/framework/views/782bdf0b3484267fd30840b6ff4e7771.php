<?php
    /** @var \App\Models\Student $student */

    use App\Models\AttendanceMark;
    use App\Models\Score;
    use App\Models\Transaction;

    $tab = request('tab', 'profile');
    $tabs = [
        'profile' => 'Profile Overview',
        'attendance' => 'Attendance Record',
        'results' => 'Academic Results',
        'finance' => 'Financial Details',
        'analytics' => 'Performance Analytics',
    ];

    $initials = collect(explode(' ', $student->full_name))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $studentMeta = implode(' · ', array_values(array_filter([
        $student->admission_number,
        $student->schoolClass?->name,
        $student->section?->name,
    ])));

    $scores = collect();
    $scoreGroups = collect();
    if ($tab === 'results') {
        $scores = Score::query()
            ->with('subject')
            ->where('student_id', $student->id)
            ->orderByDesc('session')
            ->orderByDesc('term')
            ->get();
        $scoreGroups = $scores->groupBy(fn ($row) => "{$row->session} · Term {$row->term}");
    }

    $studentTransactions = collect();
    $studentIncomeTotal = 0.0;
    if ($tab === 'finance') {
        $studentTransactions = Transaction::query()
            ->where('student_id', $student->id)
            ->where('is_void', false)
            ->orderByDesc('date')
            ->limit(25)
            ->get();
        $studentIncomeTotal = (float) Transaction::query()
            ->where('student_id', $student->id)
            ->where('type', 'Income')
            ->where('is_void', false)
            ->sum('amount_paid');
    }

    $attendanceMarks = collect();
    $attendanceCounts = collect();
    $lastAttendanceDate = null;
    if ($tab === 'attendance') {
        $attendanceMarks = AttendanceMark::query()
            ->with([
                'sheet' => fn ($q) => $q->with(['schoolClass', 'section', 'takenBy']),
            ])
            ->where('student_id', $student->id)
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $attendanceCounts = AttendanceMark::query()
            ->where('student_id', $student->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $lastAttendanceDate = AttendanceMark::query()
            ->where('student_id', $student->id)
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->max('attendance_sheets.date');
    }

    $performanceData = [];
    if ($tab === 'analytics') {
        $service = app(\App\Support\StudentPerformanceService::class);
        $currentTerm = \App\Models\AcademicTerm::where('is_active', true)->first();
        if (!$currentTerm) {
            $currentTerm = \App\Models\AcademicTerm::latest()->first();
        }
        if ($currentTerm) {
            $performanceData = [
                'overview' => $service->getOverview($student, $currentTerm->term_number, $currentTerm->academicSession->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1)),
                'attendance_impact' => $service->getAttendanceImpact($student, $currentTerm->term_number, $currentTerm->academicSession->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1)),
                'homework_performance' => $service->getHomeworkPerformance($student),
                'cbt_performance' => $service->getCbtPerformance($student),
            ];
        }
    }
?>



<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 px-6 py-6 mb-2">
        <div class="flex flex-col gap-6 md:flex-row md:items-center justify-between">
            
            <div class="flex items-center gap-5">
                <div class="shrink-0 relative">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                        <img src="<?php echo e($student->passport_photo_url); ?>" alt="<?php echo e($student->full_name); ?>" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-slate-50 shadow-sm" />
                    <?php else: ?>
                        <div class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-2xl font-bold text-white shadow-sm ring-4 ring-slate-50">
                            <?php echo e($initials); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight"><?php echo e($student->full_name); ?></h2>
                        <?php $statusColor = match($student->status) { 'Active' => 'bg-emerald-50 text-emerald-600 ring-emerald-200', 'Graduated' => 'bg-emerald-50 text-emerald-600 ring-emerald-200', default => 'bg-amber-50 text-amber-600 ring-amber-200' }; ?>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 <?php echo e($statusColor); ?>"><?php echo e($student->status); ?></span>
                    </div>
                    <p class="mt-1 text-sm font-medium text-slate-500"><?php echo e($studentMeta); ?></p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="<?php echo e(route('students.admission-form', $student)); ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 hover:bg-orange-100 transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            Admission Form
                        </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->role === 'admin'): ?>
                            <a href="<?php echo e(route('students.edit', $student)); ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Profile
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="flex md:flex-col items-center md:items-end justify-between h-full gap-4">
                <a href="<?php echo e(route('students.index')); ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition ring-1 ring-slate-200 shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </a>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->role === 'admin'): ?>
                    <form method="POST" action="<?php echo e(route('students.destroy', $student)); ?>" class="mt-1" onsubmit="return confirm('Delete this student?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline underline-offset-2 transition-colors">Delete Student</button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>


    
    <div class="rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-slate-100 mb-6">
        <div class="flex gap-1 overflow-x-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('students.show', ['student' => $student, 'tab' => $key])); ?>"
                    class="<?php echo e($tab === $key ? 'bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-md shadow-orange-200' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'); ?> flex-1 min-w-[100px] rounded-xl py-2.5 text-center text-sm font-bold transition whitespace-nowrap">
                    <?php echo e($label); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm ring-1 ring-red-100">
            <div class="text-sm font-bold text-red-800 mb-2">Please fix the following issues:</div>
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'profile'): ?>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            <div class="lg:col-span-2 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="text-lg font-bold text-slate-800">Student Particulars</div>
                </div>
                
                <div class="space-y-10">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Personal Information</h4>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div><div class="text-xs font-semibold text-slate-500">Admission No</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->admission_number); ?></div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Gender</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->gender); ?></div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Date of Birth</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->dob?->format('F j, Y') ?: '—'); ?></div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Blood Group</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->blood_group ?: '—'); ?></div></div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Academic Placement</h4>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div><div class="text-xs font-semibold text-slate-500">Class</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->schoolClass?->name ?: '—'); ?></div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Section</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->section?->name ?: '—'); ?></div></div>
                            <div class="sm:col-span-2">
                                <div class="text-xs font-semibold text-slate-500 mb-2">Enrolled Subjects</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->schoolClass && $student->schoolClass->subjects->count() > 0): ?>
                                    <div class="flex flex-wrap gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $student->schoolClass->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"><?php echo e($subject->name); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm text-slate-400">No subjects currently assigned.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Guardian Contact</h4>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div><div class="text-xs font-semibold text-slate-500">Guardian Name</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->guardian_name ?: '—'); ?></div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Phone Number</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->guardian_phone ?: '—'); ?></div></div>
                            <div class="sm:col-span-2"><div class="text-xs font-semibold text-slate-500">Residential Address</div><div class="mt-1 text-sm font-bold text-slate-800"><?php echo e($student->guardian_address ?: '—'); ?></div></div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="space-y-6">
                
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 p-6 text-white shadow-lg">
                    <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                    <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-3xl font-black"><?php echo e($student->schoolClass?->name ?: 'N/A'); ?></div>
                            <div class="mt-1 text-sm font-semibold text-white/80">Current Class</div>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/></svg>
                        </div>
                    </div>
                </div>
                
                
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-base font-bold text-slate-800">Recent Activity</div>
                    </div>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                            ['title' => 'Student record viewed', 'time' => now()->format('M j, Y g:i A'), 'color' => 'bg-emerald-400'],
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 h-2 w-2 rounded-full <?php echo e($item['color']); ?> shrink-0"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold text-slate-800"><?php echo e($item['title']); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo e($item['time']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif($tab === 'attendance'): ?>
        
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-green-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e(number_format((int) ($attendanceCounts['Present'] ?? 0))); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Present</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-400 to-rose-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e(number_format((int) ($attendanceCounts['Absent'] ?? 0))); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Absent</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e(number_format((int) ($attendanceCounts['Late'] ?? 0))); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Late</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-xl font-bold mt-2 truncate"><?php echo e($lastAttendanceDate ? \Illuminate\Support\Carbon::parse($lastAttendanceDate)->format('M j, Y') : 'N/A'); ?></div>
                    <div class="mt-2 text-sm font-semibold text-white/80">Last Attendance</div>
                </div>
            </div>
        </div>

        
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 mt-6">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Attendance Log</div>
                    <div class="mt-0.5 text-sm text-slate-500">History of the latest 30 marks for this student</div>
                </div>
                <a href="<?php echo e(route('attendance')); ?>" class="text-sm font-semibold text-orange-500 hover:text-orange-600 transition">View Full Register &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50 border-b border-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left">Date</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Class / Section</th>
                            <th class="px-6 py-4 text-left">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attendanceMarks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="bg-white hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700"><?php echo e($mark->sheet?->date?->format('M j, Y') ?: '—'); ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                        $variant = match ($mark->status) {
                                            'Present' => 'bg-emerald-100 text-emerald-700',
                                            'Absent' => 'bg-red-100 text-red-700',
                                            'Late' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    ?>
                                    <span class="rounded-full <?php echo e($variant); ?> px-3 py-1 text-xs font-bold"><?php echo e($mark->status); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo e($mark->sheet?->schoolClass?->name ?: '—'); ?> / <?php echo e($mark->sheet?->section?->name ?: '—'); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?php echo e($mark->note ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">No attendance marks have been recorded yet.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif($tab === 'results'): ?>
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Academic Records</div>
                    <div class="mt-0.5 text-sm text-slate-500">Historical performance data across sessions</div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?php echo e(route('results.entry')); ?>" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">Enter Scores</a>
                    <a href="<?php echo e(route('results.report-card', $student)); ?>" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 transition">Generate Report Card</a>
                </div>
            </div>
            
            <div class="p-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($scores->isEmpty()): ?>
                    <div class="py-12 text-center text-sm text-slate-400">No academic results available for this student.</div>
                <?php else: ?>
                    <div class="space-y-8">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $scoreGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupTitle => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4"><?php echo e($groupTitle); ?></h4>
                                <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
                                    <table class="min-w-full text-left">
                                        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                            <tr>
                                                <th class="px-5 py-3">Subject</th>
                                                <th class="px-5 py-3 text-right">CA 1</th>
                                                <th class="px-5 py-3 text-right">CA 2</th>
                                                <th class="px-5 py-3 text-right">Exam</th>
                                                <th class="px-5 py-3 text-right text-slate-800">Total Score</th>
                                                <th class="px-5 py-3 text-right">Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rows->sortBy(fn($r) => $r->subject?->name); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="bg-white hover:bg-slate-50 transition">
                                                    <td class="px-5 py-4">
                                                        <div class="text-sm font-semibold text-slate-800"><?php echo e($row->subject?->name ?? '—'); ?></div>
                                                        <div class="text-xs text-slate-400"><?php echo e($row->subject?->code ?? ''); ?></div>
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-slate-500 text-right"><?php echo e($row->ca1); ?></td>
                                                    <td class="px-5 py-4 text-sm text-slate-500 text-right"><?php echo e($row->ca2); ?></td>
                                                    <td class="px-5 py-4 text-sm text-slate-500 text-right"><?php echo e($row->exam); ?></td>
                                                    <td class="px-5 py-4 text-sm font-black text-slate-800 text-right"><?php echo e($row->total); ?></td>
                                                    <td class="px-5 py-4 text-right">
                                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600"><?php echo e($row->grade ?: '—'); ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

    <?php elseif($tab === 'finance'): ?>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-green-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black"><?php echo e(config('myacademy.currency_symbol')); ?><?php echo e(number_format($studentIncomeTotal, 2)); ?></div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Processed Income</div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 flex flex-col justify-center">
                <div class="text-3xl font-black text-slate-800"><?php echo e($studentTransactions->count()); ?></div>
                <div class="mt-1 text-sm font-bold text-slate-400 uppercase tracking-widest">Transactions</div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 flex flex-col justify-center">
                <div class="text-3xl font-black text-slate-800"><?php echo e($studentTransactions->whereNotNull('receipt_number')->count()); ?></div>
                <div class="mt-1 text-sm font-bold text-slate-400 uppercase tracking-widest">Issued Receipts</div>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 mt-6">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Payment History</div>
                    <div class="mt-0.5 text-sm text-slate-500">Log of recent transactions and payments</div>
                </div>
                <a href="<?php echo e(route('billing.index')); ?>" class="text-sm font-semibold text-emerald-500 hover:text-emerald-600 transition">Go to Billing &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-50 border-b border-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left">Date</th>
                            <th class="px-6 py-4 text-left">Transaction Type</th>
                            <th class="px-6 py-4 text-left">Details</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-right">Receipt #</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $studentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="bg-white hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700"><?php echo e($t->date?->format('M j, Y')); ?></td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($t->type === 'Income' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'); ?>"><?php echo e($t->type); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="font-bold text-slate-800"><?php echo e($t->category); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo e($t->session); ?><?php echo e($t->term ? ' · Term '.$t->term : ''); ?></div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-slate-800"><?php echo e(config('myacademy.currency_symbol')); ?><?php echo e(number_format((float)$t->amount_paid, 2)); ?></td>
                                <td class="px-6 py-4 text-right text-sm text-slate-500 font-mono"><?php echo e($t->receipt_number ?: '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400">No transactions recorded yet.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif($tab === 'analytics'): ?>
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-8 text-center text-slate-500">
            <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="text-lg font-bold text-slate-800 mb-1">Analytics Dashboard</div>
            <p>Comprehensive system analytics implementation typically resides here. This area adapts to the current active reporting modules.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/students/show.blade.php ENDPATH**/ ?>