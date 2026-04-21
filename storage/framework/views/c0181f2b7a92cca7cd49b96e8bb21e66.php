<?php
    $rateColor  = $rate >= 80 ? 'text-emerald-600' : ($rate >= 60 ? 'text-amber-600' : 'text-red-600');
    $rateBg     = $rate >= 80 ? 'from-emerald-500 to-teal-600' : ($rate >= 60 ? 'from-amber-500 to-orange-500' : 'from-red-500 to-rose-600');
    $rateLabel  = $rate >= 80 ? 'Excellent' : ($rate >= 60 ? 'Fair' : 'Poor');

    $monthName  = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y');
    $startDate  = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1);
    $daysInMonth   = $startDate->daysInMonth;
    $startDayOfWeek = $startDate->dayOfWeek; // 0=Sun

    // SVG ring for attendance rate (r=40, circ=251.3)
    $circ = 251.3;
    $dash = round($circ * $rate / 100, 1);

    $termLabels = [1 => 'First Term', 2 => 'Second Term', 3 => 'Third Term'];
?>

<div class="space-y-6">

    
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Attendance</h1>
        <p class="text-sm text-gray-500">
            <?php echo e($student->full_name); ?> &bull; <?php echo e($student->schoolClass?->name); ?>

            &bull; <?php echo e($termLabels[$currentTerm] ?? 'Term '.$currentTerm); ?> &bull; <?php echo e($currentSession); ?>

        </p>
    </div>

    
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

        
        <div class="col-span-2 sm:col-span-1 relative overflow-hidden rounded-2xl bg-gradient-to-br <?php echo e($rateBg); ?> p-5 text-white shadow-lg flex flex-col items-center justify-center">
            <div class="text-xs font-bold uppercase tracking-wider text-white/70 mb-3">Attendance Rate</div>
            <div class="relative">
                <svg width="100" height="100" viewBox="0 0 100 100" class="-rotate-90">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                    <circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="8"
                            stroke-linecap="round"
                            stroke-dasharray="<?php echo e($circ); ?>"
                            stroke-dashoffset="<?php echo e($circ - $dash); ?>"
                            style="transition: stroke-dashoffset 1s ease"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-2xl font-extrabold text-white"><?php echo e($rate); ?>%</span>
                </div>
            </div>
            <div class="mt-2 text-sm font-bold text-white/90"><?php echo e($rateLabel); ?></div>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>

        
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg">
            <div class="text-xs font-bold uppercase tracking-wider text-emerald-100">Present</div>
            <div class="mt-2 text-4xl font-extrabold"><?php echo e($present); ?></div>
            <div class="text-sm text-emerald-100">of <?php echo e($total); ?> days</div>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>

        
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 p-5 text-white shadow-lg">
            <div class="text-xs font-bold uppercase tracking-wider text-red-100">Absent</div>
            <div class="mt-2 text-4xl font-extrabold"><?php echo e($absent); ?></div>
            <div class="text-sm text-red-100">day<?php echo e($absent !== 1 ? 's' : ''); ?> missed</div>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>

        
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 p-5 text-white shadow-lg">
            <div class="text-xs font-bold uppercase tracking-wider text-amber-100">Late</div>
            <div class="mt-2 text-4xl font-extrabold"><?php echo e($late); ?></div>
            <div class="text-sm text-amber-100">arrival<?php echo e($late !== 1 ? 's' : ''); ?></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($streak > 0): ?>
                <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">
                    🔥 <?php echo e($streak); ?>-day streak
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        </div>
    </div>

    
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
        <div class="mb-3 flex items-center justify-between text-sm">
            <span class="font-bold text-gray-700">Term Attendance Overview</span>
            <span class="font-semibold <?php echo e($rateColor); ?>"><?php echo e($rate); ?>%</span>
        </div>
        <div class="flex h-4 w-full overflow-hidden rounded-full bg-gray-100">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($total > 0): ?>
                <div class="h-full bg-emerald-500 transition-all duration-700" style="width: <?php echo e($total > 0 ? round($present/$total*100) : 0); ?>%" title="Present"></div>
                <div class="h-full bg-amber-400 transition-all duration-700" style="width: <?php echo e($total > 0 ? round($late/$total*100) : 0); ?>%" title="Late"></div>
                <div class="h-full bg-red-400 transition-all duration-700" style="width: <?php echo e($total > 0 ? round($absent/$total*100) : 0); ?>%" title="Absent"></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold text-gray-600">
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-emerald-500"></span>Present (<?php echo e($present); ?>)</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-amber-400"></span>Late (<?php echo e($late); ?>)</span>
            <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-red-400"></span>Absent (<?php echo e($absent); ?>)</span>
        </div>
    </div>

    
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">

        
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <button wire:click="previousMonth"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="text-center">
                <div class="text-base font-bold text-gray-900"><?php echo e($monthName); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mTotal > 0): ?>
                    <div class="text-xs text-gray-500"><?php echo e($mPresent); ?> present &bull; <?php echo e($mAbsent); ?> absent &bull; <?php echo e($mLate); ?> late &bull; <?php echo e($mRate); ?>% rate</div>
                <?php else: ?>
                    <div class="text-xs text-gray-400">No records this month</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <button wire:click="nextMonth"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <div class="p-5">
            
            <div class="mb-2 grid grid-cols-7 text-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['S','M','T','W','T','F','S']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="text-xs font-bold text-gray-400 py-1"><?php echo e($d); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="grid grid-cols-7 gap-1">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < $startDayOfWeek; $i++): ?>
                    <div></div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($day = 1; $day <= $daysInMonth; $day++): ?>
                    <?php
                        $dateKey = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day)->format('Y-m-d');
                        $mark    = $monthMarks->get($dateKey);
                        $isToday = $dateKey === now()->format('Y-m-d');
                        $isWeekend = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day)->isWeekend();

                        $cellBg = match(true) {
                            $mark?->status === 'Present'  => 'bg-emerald-500 text-white',
                            $mark?->status === 'Absent'   => 'bg-red-500 text-white',
                            $mark?->status === 'Late'     => 'bg-amber-400 text-white',
                            $isWeekend                    => 'bg-gray-50 text-gray-300',
                            default                       => 'bg-gray-100 text-gray-500',
                        };
                        $icon = match($mark?->status) {
                            'Present' => '✓',
                            'Absent'  => '✗',
                            'Late'    => '~',
                            default   => '',
                        };
                    ?>
                    <div class="aspect-square p-0.5">
                        <div class="relative flex h-full flex-col items-center justify-center rounded-xl text-xs font-bold <?php echo e($cellBg); ?> <?php echo e($isToday ? 'ring-2 ring-offset-1 ring-violet-500' : ''); ?> transition">
                            <span><?php echo e($day); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
                                <span class="text-[9px] leading-none opacity-90"><?php echo e($icon); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="mt-4 flex flex-wrap justify-center gap-4 text-xs font-semibold text-gray-600">
                <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-md bg-emerald-500"></span>Present</span>
                <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-md bg-red-500"></span>Absent</span>
                <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-md bg-amber-400"></span>Late</span>
                <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-md bg-gray-100 ring-1 ring-gray-200"></span>No Record</span>
                <span class="flex items-center gap-1.5"><span class="h-3.5 w-3.5 rounded-md ring-2 ring-violet-500"></span>Today</span>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($monthMarks->isNotEmpty()): ?>
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500"><?php echo e($monthName); ?> — Daily Records</h3>
            </div>
            <div class="divide-y divide-gray-50">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $monthMarks->sortByDesc(fn($m) => $m->sheet->date); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $s = $mark->status;
                        $rowBg   = match($s) { 'Present' => 'bg-emerald-50', 'Absent' => 'bg-red-50', 'Late' => 'bg-amber-50', default => '' };
                        $iconBg  = match($s) { 'Present' => 'bg-emerald-100 text-emerald-600', 'Absent' => 'bg-red-100 text-red-600', 'Late' => 'bg-amber-100 text-amber-600', default => 'bg-gray-100 text-gray-500' };
                        $badge   = match($s) { 'Present' => 'bg-emerald-100 text-emerald-800', 'Absent' => 'bg-red-100 text-red-800', 'Late' => 'bg-amber-100 text-amber-800', default => 'bg-gray-100 text-gray-600' };
                    ?>
                    <div class="flex items-center justify-between px-6 py-3.5 <?php echo e($rowBg); ?> transition hover:brightness-95">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl <?php echo e($iconBg); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s === 'Present'): ?>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <?php elseif($s === 'Absent'): ?>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                <?php else: ?>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900"><?php echo e($mark->sheet->date->format('l, d M Y')); ?></div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mark->note): ?>
                                    <div class="text-xs text-gray-500"><?php echo e($mark->note); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($badge); ?>"><?php echo e($s); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/attendance.blade.php ENDPATH**/ ?>