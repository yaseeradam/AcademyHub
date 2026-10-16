@php
    use App\Models\Announcement;
    use App\Models\AttendanceMark;
    use App\Models\SchoolClass;
    use App\Models\Score;
    use App\Models\Student;
    use App\Models\SubjectAllocation;
    use App\Models\AcademicTerm;

    $user = auth()->user();

    // Retrieve all subject allocations for this teacher with class and section relations
    $allocations = SubjectAllocation::with(['schoolClass', 'section'])
        ->where('teacher_id', $user->id)
        ->get();

    $classIds = $allocations->pluck('class_id')->filter()->unique()->values();
    $subjectIds = $allocations->pluck('subject_id')->filter()->unique()->values();

    // Map teacher's scope: [class_id => null (all arms) | array of section_ids]
    $teacherScope = $user->teacherClassSectionScope();
    $hasScope = !empty($teacherScope);

    // Build base query for students assigned to this teacher
    $studentsQuery = Student::where('status', 'Active');
    if ($hasScope) {
        $studentsQuery->where(function ($q) use ($teacherScope) {
            foreach ($teacherScope as $classId => $sectionIds) {
                $q->orWhere(function ($sub) use ($classId, $sectionIds) {
                    $sub->where('class_id', $classId);
                    if (is_array($sectionIds)) {
                        $sub->whereIn('section_id', $sectionIds);
                    }
                });
            }
        });
    }

    $studentsCount = (clone $studentsQuery)->count();
    $studentIds = (clone $studentsQuery)->pluck('id')->toArray();

    // Build allocated classes / subclasses (arms) breakdown
    $myClassesList = collect();
    $groupedAllocations = $allocations->groupBy('class_id');
    foreach ($groupedAllocations as $cId => $allocGroup) {
        $first = $allocGroup->first();
        $schoolClass = $first->schoolClass;
        if (! $schoolClass) continue;

        $sectionIds = $allocGroup->pluck('section_id')->unique()->values();
        // If there's an allocation with null section_id, the teacher teaches all arms
        if ($sectionIds->contains(null)) {
            $count = Student::where('class_id', $cId)->where('status', 'Active')->count();
            $myClassesList->push([
                'name' => $schoolClass->name,
                'subtitle' => 'All Arms • Level ' . $schoolClass->level,
                'count' => $count,
                'level' => $schoolClass->level,
            ]);
        } else {
            // Teacher is assigned to specific arm(s)
            $sections = \App\Models\Section::whereIn('id', $sectionIds)->get();
            foreach ($sections as $sec) {
                $count = Student::where('class_id', $cId)->where('section_id', $sec->id)->where('status', 'Active')->count();
                $myClassesList->push([
                    'name' => $schoolClass->name . ' (' . $sec->name . ')',
                    'subtitle' => 'Arm ' . $sec->name . ' • Level ' . $schoolClass->level,
                    'count' => $count,
                    'level' => $schoolClass->level,
                ]);
            }
        }
    }
    $myClassesList = $myClassesList->sortBy('level')->values();

    $subjectsCount = (int) $subjectIds->count();
    $pendingSubmissions = 0;

    // Attendance trend (last 7 days) for teacher's students/subclasses
    $attendanceData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $dateStr = $date->toDateString();

        $present = AttendanceMark::where(function ($q) use ($dateStr) {
                $q->whereHas('sheet', fn($sq) => $sq->whereDate('date', $dateStr))
                  ->orWhereDate('created_at', $dateStr);
            })
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['Present', 'Late', 'half_day', 'present', 'late'])
            ->count();

        $absent = AttendanceMark::where(function ($q) use ($dateStr) {
                $q->whereHas('sheet', fn($sq) => $sq->whereDate('date', $dateStr))
                  ->orWhereDate('created_at', $dateStr);
            })
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['Absent', 'absent'])
            ->count();

        $attendanceData[] = [
            'label'   => $date->format('D'),
            'date'    => $date->format('M j'),
            'present' => $present,
            'absent'  => $absent,
        ];
    }

    // Top students from teacher's assigned subclass/students
    $topStudents = Score::whereIn('student_id', $studentIds)
        ->selectRaw('student_id, AVG(total) as avg_score')
        ->with('student')
        ->groupBy('student_id')
        ->orderByDesc('avg_score')
        ->limit(5)
        ->get();

    // Subject averages for teacher's subjects and students
    $subjectStatsQuery = Score::query();
    if ($subjectIds->isNotEmpty()) {
        $subjectStatsQuery->whereIn('subject_id', $subjectIds);
    }
    if (!empty($studentIds)) {
        $subjectStatsQuery->whereIn('student_id', $studentIds);
    }
    $subjectStats = $subjectStatsQuery
        ->selectRaw('subject_id, AVG(total) as avg_score')
        ->with('subject')
        ->groupBy('subject_id')
        ->orderByDesc('avg_score')
        ->limit(5)
        ->get();

    // Teacher's own attendance for today
    $myTodayMark = \App\Models\TeacherAttendanceMark::where('teacher_id', $user->id)
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->first();

    $announcements = Announcement::whereNotNull('published_at')
        ->where(fn($q) => $q->where('audience', 'all')->orWhere('audience', 'staff')->orWhere('audience', $user->role))
        ->orderByDesc('published_at')
        ->limit(4)
        ->get();

    $currentTerm = AcademicTerm::active();
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex items-center gap-2 mb-2 sm:mb-3">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs sm:text-sm font-semibold uppercase tracking-widest" style="color: #93c5fd;">Teacher Portal</span>
            </div>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">Welcome, {{ $user->name }}</h2>
            <p class="mt-1.5 text-xs sm:text-base font-medium" style="color: #93c5fd;">Manage your classes, scores and attendance.</p>
            <div class="mt-4 sm:mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg px-3 py-1 sm:px-4 sm:py-1.5 text-xs sm:text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $currentTerm ? $currentTerm->name : 'No Active Term' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg px-3 py-1 sm:px-4 sm:py-1.5 text-xs sm:text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        {{ number_format($studentsCount) }} Students
                    </span>
                    <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg px-3 py-1 sm:px-4 sm:py-1.5 text-xs sm:text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ now()->format('l, F j') }}
                    </span>
                    @if($myTodayMark)
                        <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg px-3 py-1 sm:px-4 sm:py-1.5 text-xs sm:text-sm font-semibold {{ $myTodayMark->status === 'Present' ? 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-400/40' : 'bg-amber-500/20 text-amber-300 ring-1 ring-amber-400/40' }}">
                            <span class="h-2 w-2 rounded-full {{ $myTodayMark->status === 'Present' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                            My Attendance: {{ $myTodayMark->status }} ({{ $myTodayMark->note ?? $myTodayMark->punch_in_time ?? 'Recorded' }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg px-3 py-1 sm:px-4 sm:py-1.5 text-xs sm:text-sm font-semibold text-white/80" style="background:rgba(255,255,255,0.12);">
                            <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                            My Attendance: Not Scanned Today
                        </span>
                    @endif
                </div>

                <a href="{{ route('curriculum.topics') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-500 to-indigo-500 hover:from-violet-600 hover:to-indigo-600 text-white px-5 py-2.5 text-xs sm:text-sm font-bold shadow-lg shadow-black/20 transition active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Upload Scheme &amp; Topics
                </a>
            </div>
        </div>
    </div>

    {{-- Stat Cards (2-Column Mobile Grid for Instant Scanning) --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-4">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 p-4 sm:p-6 text-white shadow-md">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-2xl sm:text-4xl font-black">{{ $myClassesList->count() }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/80">Assigned Classes &amp; Arms</div>
                </div>
                <div class="grid h-9 w-9 sm:h-12 sm:w-12 place-items-center rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4.5 w-4.5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-4 sm:p-6 text-white shadow-md">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-2xl sm:text-4xl font-black">{{ number_format($studentsCount) }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/80">Active Students</div>
                </div>
                <div class="grid h-9 w-9 sm:h-12 sm:w-12 place-items-center rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4.5 w-4.5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-4 sm:p-6 text-white shadow-md">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-2xl sm:text-4xl font-black">{{ $subjectsCount }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/80">Assigned Subjects</div>
                </div>
                <div class="grid h-9 w-9 sm:h-12 sm:w-12 place-items-center rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4.5 w-4.5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-4 sm:p-6 text-white shadow-md">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-2xl sm:text-4xl font-black">{{ $pendingSubmissions }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/80">Pending Submissions</div>
                </div>
                <div class="grid h-9 w-9 sm:h-12 sm:w-12 place-items-center rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4.5 w-4.5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <div class="text-base font-bold text-slate-800">Attendance (Last 7 Days)</div>
                    <div class="text-xs font-medium text-slate-400">
                        {{ $hasScope ? 'Student attendance trend for your assigned classes' : 'School-wide student attendance trend' }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 ring-1 ring-blue-500/20">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        Present
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-600 ring-1 ring-violet-500/20">
                        <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                        Absent
                    </span>
                </div>
            </div>
            <div class="relative w-full" style="height: 220px;">
                <canvas id="teacherAttendanceChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">Top Students</div>
                <a href="{{ route('students.index') }}" class="text-xs font-semibold text-orange-500 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($topStudents as $row)
                    @php($student = $row->student)
                    <div class="flex items-center gap-3">
                        <div class="relative flex-shrink-0">
                            <img src="{{ $student?->passport_photo_url }}"
                                 class="h-10 w-10 rounded-full object-cover ring-2 ring-blue-100"
                                 onerror="this.src='/avatars/student_blue.png'" alt="">
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800">{{ $student?->full_name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $student?->admission_number ?? '' }}</div>
                        </div>
                        <div class="text-sm font-bold text-blue-500">{{ round($row->avg_score) }}%</div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-slate-400">No scores recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Subject Averages + My Classes --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 text-base font-bold text-slate-800">Subject Averages</div>
            <div class="relative w-full" style="height: 220px;">
                <canvas id="teacherSubjectChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">My Classes &amp; Arms</div>
                <a href="{{ route('classes.index') }}" class="text-xs font-semibold text-blue-500 hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($myClassesList as $classItem)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $classItem['name'] }}</div>
                            <div class="text-xs text-slate-400">{{ $classItem['subtitle'] }}</div>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            {{ number_format($classItem['count']) }} students
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-slate-400">No classes assigned yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Announcements --}}
    @if($announcements->isNotEmpty())
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
        <div class="mb-4 text-base font-bold text-slate-800">Announcements</div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($announcements as $announcement)
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-800">{{ $announcement->title }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $announcement->published_at?->format('M j, Y') }}</div>
                    <div class="mt-2 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($announcement->body, 100) }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
{{-- Load Chart.js with local asset and CDN fallback --}}
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}?v=4.4"></script>
<script>
    if (!window.Chart) {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        document.head.appendChild(s);
    }
</script>
<script>
    window.teacherDashboardData = {
        attendance: @json($attendanceData),
        subjects: @json($subjectStats->map(fn($s) => ['name' => $s->subject?->name ?? 'N/A', 'avg' => round($s->avg_score)])->values()),
    };

    function initTeacherCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initTeacherCharts, 100);
            return;
        }

        const data = window.teacherDashboardData || {};

        // 1. Attendance Line Chart
        const attCanvas = document.getElementById('teacherAttendanceChart');
        if (attCanvas) {
            const existing = Chart.getChart(attCanvas);
            if (existing) existing.destroy();

            const att = data.attendance || [];
            new Chart(attCanvas, {
                type: 'line',
                data: {
                    labels: att.map(d => d.label + ' (' + (d.date || '') + ')'),
                    datasets: [
                        {
                            label: 'Present',
                            data: att.map(d => d.present),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.12)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            borderWidth: 2.5,
                        },
                        {
                            label: 'Absent',
                            data: att.map(d => d.absent),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.08)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#8b5cf6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            borderWidth: 2.5,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false, // Handled by header badges
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.dataset.label + ': ' + context.parsed.y + ' students';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '500' }, color: '#64748b' }
                        },
                        y: {
                            grid: { color: 'rgba(226, 232, 240, 0.8)' },
                            ticks: { font: { size: 11 }, color: '#64748b', precision: 0 },
                            beginAtZero: true,
                        },
                    },
                },
            });
        }

        // 2. Subject Averages Bar Chart
        const subCanvas = document.getElementById('teacherSubjectChart');
        if (subCanvas) {
            const existingSub = Chart.getChart(subCanvas);
            if (existingSub) existingSub.destroy();

            const subjects = data.subjects || [];
            const palette = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ec4899'];
            new Chart(subCanvas, {
                type: 'bar',
                data: {
                    labels: subjects.map(s => s.name),
                    datasets: [{
                        label: 'Avg Score (%)',
                        data: subjects.map(s => s.avg),
                        backgroundColor: subjects.map((_, i) => palette[i % palette.length]),
                        borderRadius: 6,
                        borderSkipped: false,
                        barThickness: 18,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' Average: ' + context.parsed.x + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(226, 232, 240, 0.8)' },
                            ticks: { font: { size: 11 }, color: '#64748b' },
                            beginAtZero: true,
                            max: 100
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '500' }, color: '#334155' }
                        },
                    },
                },
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTeacherCharts);
    } else {
        initTeacherCharts();
    }
    document.addEventListener('livewire:navigated', initTeacherCharts);
</script>
@endpush
