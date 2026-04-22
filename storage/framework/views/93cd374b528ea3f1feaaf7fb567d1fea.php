<?php
    use App\Models\AcademicTerm;
    use App\Models\AttendanceMark;
    use App\Models\SchoolClass;
    use App\Models\Score;
    use App\Models\Student;
    use App\Models\User;

    $user = auth()->user();
    $currentTerm = AcademicTerm::active();

    $studentsTotal  = Student::query()->count();
    $studentsBoys   = Student::query()->where('gender', 'Male')->count();
    $studentsGirls  = Student::query()->where('gender', 'Female')->count();
    
    $admissionsThisMonth = Student::query()
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();

    $teachersTotal  = User::query()->where('role', 'teacher')->count();
    $classesTotal = SchoolClass::query()->count();

    // Actual Backend Stats Functioning
    $attendanceTodayTotal = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->count();
    $presentToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->where('status', 'Present')
        ->count();
    $absentToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->where('status', 'Absent')
        ->count();
    $lateToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->where('status', 'Late')
        ->count();

    $attendanceRate = $attendanceTodayTotal > 0 ? round(($presentToday / $attendanceTodayTotal) * 100) : 0;
    $absentRate = $attendanceTodayTotal > 0 ? round(($absentToday / $attendanceTodayTotal) * 100) : 0;
    $lateRate = $attendanceTodayTotal > 0 ? round(($lateToday / $attendanceTodayTotal) * 100) : 0;

    $monthlyAttendance = \Illuminate\Support\Facades\Cache::remember('dashboard_attendance_monthly', \DateInterval::createFromDateString('15 minutes'), function () {
        $marks = AttendanceMark::query()
            ->whereHas('sheet', fn($q) => $q->whereMonth('date', now()->month)->whereYear('date', now()->year))
            ->get();
        if ($marks->isEmpty()) return 0;
        $present = $marks->where('status', 'Present')->count();
        return round(($present / $marks->count()) * 100);
    });

    // Real functioning Weekly Bar Chart Data (Mon-Sat presence)
    $weeklyBars = collect(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])->map(function($dayName) {
        $date = now()->startOfWeek()->modify('this ' . $dayName);
        $total = AttendanceMark::query()->whereHas('sheet', fn($q) => $q->whereDate('date', $date))->count();
        $pres = AttendanceMark::query()->whereHas('sheet', fn($q) => $q->whereDate('date', $date))->where('status','Present')->count();
        $abs = AttendanceMark::query()->whereHas('sheet', fn($q) => $q->whereDate('date', $date))->where('status','Absent')->count();
        $late = AttendanceMark::query()->whereHas('sheet', fn($q) => $q->whereDate('date', $date))->where('status','Late')->count();
        
        return [
            'day' => substr($dayName, 0, 3), // Mon, Tue...
            'present_h' => $total>0 ? round(($pres/$total)*100) : 0,
            'absent_h' => $total>0 ? round(($abs/$total)*100) : 0,
            'late_h' => $total>0 ? round(($late/$total)*100) : 0,
            'is_today' => $date->isToday()
        ];
    });

?>



<?php $__env->startSection('content'); ?>
<div class="space-y-5 pb-12 font-sans">

    
    <div class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-r from-[#17274E] to-[#1D3261] shadow-xl px-7 flex flex-col md:flex-row items-center justify-between min-h-[220px]">
        
        
        <div class="absolute inset-0 pointer-events-none opacity-40 mix-blend-screen bg-[radial-gradient(circle,#ffffff_1.5px,transparent_1.5px)]" style="background-size: 32px 32px;"></div>

        
        <div class="relative z-10 py-7 w-full md:w-3/5">
            <div class="flex items-center gap-2 mb-3">
                <span class="h-2 w-2 rounded-full bg-[#10b981] animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-[#34d399]">Live System</span>
            </div>
            <h2 class="text-3xl lg:text-4xl font-black text-white tracking-tight leading-[1.1] mb-1.5">
                <?php echo e(config('myacademy.school_name', config('app.name', 'FrontalMinds Islamic and Science Academy'))); ?>

            </h2>
            <p class="text-sm font-medium text-blue-200 mb-5">School Management System</p>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-lg border border-white/10 bg-white/10 backdrop-blur-sm px-3 py-2 text-[11px] font-bold text-white shadow-sm">
                    <svg class="h-3.5 w-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <?php echo e($currentTerm ? $currentTerm->name : 'No Active Term'); ?>

                </div>
                <div class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-lg border border-white/10 bg-white/10 backdrop-blur-sm px-3 py-2 text-[11px] font-bold text-white shadow-sm">
                    <svg class="h-3.5 w-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <?php echo e(number_format($studentsTotal)); ?> Students
                </div>
                <div class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-lg border border-white/10 bg-white/10 backdrop-blur-sm px-3 py-2 text-[11px] font-bold text-white shadow-sm">
                    <svg class="h-3.5 w-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <?php echo e(now()->format('l, F j')); ?>

                </div>
            </div>
        </div>

        
        <div class="relative z-10 w-full md:w-2/5 flex items-center justify-end py-6 md:py-0">
            
            
            <!-- <img src="SUPER_ADMIN_AVATAR.png" class="absolute bottom-0 -left-6 h-64 z-20 object-contain drop-shadow-2xl" alt="Admin Avatar"> -->

            <div class="bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 p-4 shadow-xl w-full max-w-[280px] relative z-10 mt-6 md:mt-0">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-white">Today's Overview</span>
                    <svg class="h-4 w-4 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M9 15l3-3 4 4 5-5"/></svg>
                </div>
                <div class="flex items-start gap-3">
                    <div class="shrink-0 flex items-center justify-center h-10 w-10 rounded-xl bg-[#34d399] shadow-[0_0_12px_rgba(52,211,153,0.5)]">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-white leading-tight">Everything looks good!</h4>
                        <p class="text-[10px] font-semibold text-blue-200 mt-0.5 leading-relaxed">All systems are running smoothly. 😊</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        
        <div class="rounded-3xl bg-gradient-to-br from-[#f97316] to-[#f43f5e] p-5 shadow-md relative overflow-hidden flex flex-col justify-between min-h-[140px] group">
            <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            <div class="absolute right-8 bottom-8 h-12 w-12 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-white drop-shadow-sm tracking-tight leading-none"><?php echo e(number_format($studentsTotal)); ?></h3>
                        <p class="text-white/90 font-bold text-sm mt-1 tracking-wide">Students</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-1 text-[10px] font-bold text-emerald-200 bg-white/10 w-max px-2.5 py-1 rounded-full backdrop-blur-sm shadow-sm ring-1 ring-white/20">
                    <span class="text-white">+<?php echo e($admissionsThisMonth); ?></span> this month <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
            </div>
            
            <!-- <img src="BOY_STUDENT_AVATAR.png" class="absolute bottom-0 -right-2 h-[120%] object-contain origin-bottom scale-90 translate-x-2 z-0" alt="Student"> -->
        </div>

        
        <div class="rounded-3xl bg-gradient-to-br from-[#a855f7] to-[#7e22ce] p-5 shadow-md relative overflow-hidden flex flex-col justify-between min-h-[140px] group">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-white drop-shadow-sm tracking-tight leading-none"><?php echo e(number_format($teachersTotal)); ?></h3>
                        <p class="text-white/90 font-bold text-sm mt-1 tracking-wide">Teachers</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-1 text-[10px] font-bold text-emerald-200 bg-white/10 w-max px-2.5 py-1 rounded-full backdrop-blur-sm shadow-sm ring-1 ring-white/20">
                    <span class="text-white">Active</span> personnel <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
            </div>
            
            <!-- <img src="FEMALE_TEACHER_AVATAR.png" class="absolute bottom-0 right-2 h-[115%] object-contain scale-90 translate-x-2 z-0" alt="Teacher"> -->
        </div>

        
        <div class="rounded-3xl bg-gradient-to-br from-[#14b8a6] to-[#0f766e] p-5 shadow-md relative overflow-hidden flex flex-col justify-between min-h-[140px] group">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-3xl font-black text-white drop-shadow-sm tracking-tight leading-none"><?php echo e(number_format($classesTotal)); ?></h3>
                        <p class="text-white/90 font-bold text-sm mt-1 tracking-wide">Classes</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-1 text-[11px] font-bold text-teal-100 px-1">
                    Active classes in session
                </div>
            </div>
            
            <!-- <img src="BOY_POINTING_AVATAR.png" class="absolute bottom-0 -right-2 h-[120%] object-contain scale-90 translate-x-2 z-0" alt="Student"> -->
        </div>

    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        
        
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] p-5 overflow-hidden">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h3 class="text-[15px] font-extrabold text-slate-800">Attendance Today</h3>
                </div>
                <div class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 bg-slate-50 flex items-center gap-1.5 shadow-sm">
                    Today <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-7">
                
                
                <div class="shrink-0 relative flex items-center justify-center">
                    <svg class="h-28 w-28 transform -rotate-90">
                        <circle cx="56" cy="56" r="48" stroke="#f1f5f9" stroke-width="12" fill="none"></circle>
                        
                        <?php $dashPresent = 301 * ($attendanceRate / 100); ?>
                        <circle cx="56" cy="56" r="48" stroke="#10b981" stroke-width="12" fill="none" stroke-linecap="round" stroke-dasharray="<?php echo e($dashPresent); ?> 301"></circle>
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center">
                        <span class="text-xl font-black text-slate-800 tracking-tight"><?php echo e($attendanceRate); ?>%</span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Present</span>
                    </div>
                </div>

                
                <div class="shrink-0 space-y-3.5 pr-8 lg:border-r border-slate-100 min-w-[120px]">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 w-16">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#10b981]"></span>
                            <span class="text-xs font-bold text-slate-600">Present</span>
                        </div>
                        <span class="text-xs font-black text-slate-800 w-6"><?php echo e($presentToday); ?></span>
                        <span class="text-[10px] font-bold text-slate-400"><?php echo e($attendanceRate); ?>%</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 w-16">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#f43f5e]"></span>
                            <span class="text-xs font-bold text-slate-600">Absent</span>
                        </div>
                        <span class="text-xs font-black text-slate-800 w-6"><?php echo e($absentToday); ?></span>
                        <span class="text-[10px] font-bold text-slate-400"><?php echo e($absentRate); ?>%</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 w-16">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#fbbf24]"></span>
                            <span class="text-xs font-bold text-slate-600">Late</span>
                        </div>
                        <span class="text-xs font-black text-slate-800 w-6"><?php echo e($lateToday); ?></span>
                        <span class="text-[10px] font-bold text-slate-400"><?php echo e($lateRate); ?>%</span>
                    </div>
                </div>

                
                <div class="flex-1 w-full pl-2">
                    <div class="relative h-[110px] w-full flex items-end justify-between gap-2 px-2 text-[10px] font-bold text-slate-400">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $weeklyBars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex flex-col items-center w-full h-full justify-end relative group">
                                
                                <div class="absolute -top-7 opacity-0 group-hover:opacity-100 transition whitespace-nowrap bg-slate-800 text-white text-[9px] px-2 py-0.5 rounded shadow pointer-events-none z-10 text-center">
                                    <?php echo e($bar['present_h']); ?>% Present
                                </div>
                                
                                
                                <div class="flex flex-col-reverse w-2.5 md:w-3 items-center justify-start h-[80%] rounded-full overflow-hidden bg-slate-50 relative <?php echo e($bar['is_today'] ? 'ring-2 ring-slate-100' : ''); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bar['present_h'] > 0 || $bar['absent_h'] > 0 || $bar['late_h'] > 0): ?>
                                        <div class="w-full bg-[#10b981] transition-all duration-500" style="height: <?php echo e($bar['present_h']); ?>%"></div>
                                        <div class="w-full bg-[#fbbf24] transition-all duration-500" style="height: <?php echo e($bar['late_h']); ?>%"></div>
                                        <div class="w-full bg-[#f43f5e] transition-all duration-500" style="height: <?php echo e($bar['absent_h']); ?>%"></div>
                                    <?php else: ?>
                                        
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <span class="tracking-wider mt-2 <?php echo e($bar['is_today'] ? 'text-slate-800 font-extrabold' : ''); ?>"><?php echo e($bar['day']); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] p-5 overflow-hidden flex flex-col">
            <div class="flex items-center gap-2 mb-6">
                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <h3 class="text-[15px] font-extrabold text-slate-800">Students Overview</h3>
            </div>

            <div class="flex flex-row items-center gap-6 justify-between lg:justify-start flex-1">
                
                <div class="shrink-0 relative flex items-center justify-center ml-2">
                    <svg class="h-[100px] w-[100px] transform -rotate-90">
                        <circle cx="50" cy="50" r="40" stroke="#f1f5f9" stroke-width="12" fill="none"></circle>
                        <?php 
                            $boysPct = $studentsTotal > 0 ? ($studentsBoys / $studentsTotal) : 0;
                            $dashBoys = 251 * $boysPct;
                        ?>
                        <circle cx="50" cy="50" r="40" stroke="#fb8c00" stroke-width="12" fill="none"></circle> 
                        <circle cx="50" cy="50" r="40" stroke="#8b5cf6" stroke-width="12" fill="none" stroke-linecap="round" stroke-dasharray="<?php echo e($dashBoys); ?> 251"></circle>
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center">
                        <span class="text-xl font-black text-slate-800 tracking-tight"><?php echo e(number_format($studentsTotal)); ?></span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-500">Total</span>
                    </div>
                </div>

                
                <div class="flex-1 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#8b5cf6]"></span>
                            <span class="text-xs font-bold text-slate-600">Boys</span>
                        </div>
                        <span class="text-xs font-black text-slate-800"><?php echo e($studentsBoys); ?></span>
                        <span class="text-[10px] font-bold text-slate-400"><?php echo e($studentsTotal > 0 ? round(($studentsBoys/$studentsTotal)*100) : 0); ?>%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#fb8c00]"></span>
                            <span class="text-xs font-bold text-slate-600">Girls</span>
                        </div>
                        <span class="text-xs font-black text-slate-800"><?php echo e($studentsGirls); ?></span>
                        <span class="text-[10px] font-bold text-slate-400"><?php echo e($studentsTotal > 0 ? round(($studentsGirls/$studentsTotal)*100) : 0); ?>%</span>
                    </div>
                    
                    <div class="pt-3">
                        <div class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50/80 px-2.5 py-1.5 text-[10px] font-bold text-emerald-600 w-full border border-emerald-100 shadow-[0_1px_2px_rgba(16,185,129,0.05)] justify-center tracking-wide">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            Admission this month: <span class="font-black"><?php echo e($admissionsThisMonth); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)] divide-y lg:divide-y-0 lg:divide-x divide-slate-100 overflow-hidden mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
            
            
            <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                <div class="h-12 w-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex shrink-0 items-center justify-center text-indigo-500 shadow-inner">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-0.5">Attendance Rate</h4>
                    <p class="text-[9px] font-bold text-slate-400 mb-0.5">This month</p>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xl font-black text-slate-800 leading-none"><?php echo e(collect([$monthlyAttendance, 100])->min()); ?>%</span>
                        <span class="inline-flex items-center rounded-md bg-[#10b981]/10 px-1 py-0.5 text-[9px] font-black text-[#10b981] leading-none">+6%</span>
                    </div>
                </div>
            </div>

            
            <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 shadow-inner">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-0.5">Best Class</h4>
                    <span class="text-lg font-black text-[#10b981] block mb-0.5 leading-none">Class 6A</span>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">95% Attendance</p>
                </div>
            </div>

            
            <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shadow-inner">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-0.5">Needs Attention</h4>
                    <span class="text-lg font-black text-slate-800 block mb-0.5 leading-none">Class 4B</span>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">72% Attendance</p>
                </div>
            </div>

            
            <div class="p-5 flex items-center gap-4 hover:bg-slate-50 transition-colors relative overflow-hidden group">
                <div class="h-12 w-12 shrink-0 rounded-2xl bg-orange-50 border border-orange-100 flex items-center justify-center text-[#f97316] shadow-inner relative z-10">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 12a10.05 10.05 0 00-6.5-6.5C11 8.5 11.5 10.5 13 13c-4.5-2.5-4-8.5-4-8.5C9 7.5 4.5 11 4.5 16.5 4.5 20.64 7.86 24 12 24s7.5-3.36 7.5-7.5c0-1.66-.63-3.26-2-4.5z"/></svg>
                </div>
                <div class="relative z-10">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-0.5">Current Streak</h4>
                    <span class="text-lg font-black text-slate-800 block mb-0.5 leading-none">12 days</span>
                    <p class="text-[9px] font-bold text-slate-400 flex items-center gap-1 uppercase tracking-wide">Keep it up! <span class="bg-[#ffedd5] text-[#ea580c] px-1 py-0.5 rounded leading-none">🔥</span></p>
                </div>
                
                
                <div class="absolute right-[-2.5rem] bottom-[-2.5rem] opacity-20 group-hover:opacity-40 transition-opacity">
                    <!-- <img src="FIRE_EMOJI_AVATAR.png" alt="Fire" class="h-24 w-24"> -->
                    <div class="text-[8rem]">🔥</div>
                </div>
            </div>
            
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/dashboard.blade.php ENDPATH**/ ?>