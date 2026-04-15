@php
    use App\Models\AcademicTerm;
    use App\Models\AttendanceMark;
    use App\Models\CbtExam;
    use App\Models\SchoolClass;
    use App\Models\Score;
    use App\Models\Student;
    use App\Models\User;

    $user = auth()->user();
    $currentTerm = AcademicTerm::active();

    $currentWeekLabel = 'Week 1';
    if ($currentTerm && $currentTerm->starts_on) {
        $termStart = $currentTerm->starts_on->startOfWeek();
        $thisWeek  = now()->startOfWeek();
        $weekNum   = max(1, (int) $termStart->diffInWeeks($thisWeek) + 1);
        $currentWeekLabel = 'Week ' . $weekNum;
    }

    $studentsTotal  = Student::query()->count();
    $studentsBoys   = Student::query()->where('gender', 'Male')->count();
    $studentsGirls  = Student::query()->where('gender', 'Female')->count();
    $teachersTotal  = User::query()->where('role', 'teacher')->count();
    $privateTeachers = User::query()->where('role', 'teacher')->where('is_active', false)->count();

    $attendanceToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->count();
    $presentToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->where('status', 'Present')
        ->count();
    $attendanceRate = $attendanceToday > 0 ? round(($presentToday / $attendanceToday) * 100, 1) : 0;

    // Attendance trend for last 6 weeks (grouped by week)
    $attendanceData = \Illuminate\Support\Facades\Cache::remember('dashboard_attendance_trend', \DateInterval::createFromDateString('15 minutes'), function () {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $present = \App\Models\AttendanceMark::query()
                ->whereHas('sheet', fn($q) => $q->whereDate('date', $date))
                ->where('status', 'Present')->count();
            $absent = \App\Models\AttendanceMark::query()
                ->whereHas('sheet', fn($q) => $q->whereDate('date', $date))
                ->where('status', 'Absent')->count();
            $data[] = ['label' => $date->format('D'), 'present' => $present, 'absent' => $absent];
        }
        return $data;
    });

    $totalScores = \App\Models\Score::query()->count();
    $passScores  = \App\Models\Score::query()->where('total', '>=', 50)->count();
    $failScores  = $totalScores - $passScores;

    // Top students by average score
    $topStudents = \App\Models\Score::query()
        ->selectRaw('student_id, AVG(total) as avg_score')
        ->with('student')
        ->groupBy('student_id')
        ->orderByDesc('avg_score')
        ->limit(5)
        ->get();

    // Subject averages for bar chart
    $subjectStats = \App\Models\Score::query()
        ->selectRaw('subject_id, AVG(total) as avg_score')
        ->with('subject')
        ->groupBy('subject_id')
        ->orderByDesc('avg_score')
        ->limit(5)
        ->get();
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>

        <div class="relative flex items-end justify-between">
            {{-- Left: content --}}
            <div class="px-8 py-8">
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color: #93c5fd;">Live System</span>
                </div>
                <h2 class="text-5xl font-bold text-white tracking-tight">
                    {{ config('myacademy.school_name', config('app.name', 'MyAcademy')) }}
                </h2>
                <p class="mt-2 text-lg font-medium" style="color: #93c5fd;">School Management System</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @php($currentTerm = \App\Models\AcademicTerm::active())
                    <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $currentTerm ? $currentTerm->name : 'No Active Term' }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        {{ number_format($studentsTotal) }} Students
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-sm font-semibold text-white" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {{ now()->format('l, F j') }}
                    </span>
                </div>
            </div>

            {{-- Right: avatar --}}
            <div class="hidden lg:block flex-shrink-0 self-end">
                <img src="{{ asset('uploads/admin avatar.png') }}" alt="Admin Avatar"
                     class="h-72 w-auto object-contain object-bottom" style="display:block;">
            </div>
        </div>
    </div>


    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

        {{-- Students --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ number_format($studentsTotal) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Students</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Teachers --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ number_format($teachersTotal) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Teachers</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Private Teachers / Classes --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-400 to-teal-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ number_format(\App\Models\SchoolClass::count()) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Classes</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">

        {{-- Management Value Line Chart --}}
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">Attendance Today</div>
                <div class="flex gap-2">
                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-600">Present</span>
                    <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-500">Absent</span>
                </div>
            </div>
            <canvas id="managementValueChart" height="180"></canvas>
        </div>

        {{-- Gender Donut Chart --}}
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 flex flex-col justify-center">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">Students</div>
                <div class="text-xs text-slate-400">{{ $studentsTotal }} total</div>
            </div>
            <div class="flex justify-center">
                <div class="relative h-36 w-36">
                    <canvas id="genderDonutChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-2xl font-black text-slate-800">{{ $studentsTotal > 0 ? round(($studentsGirls / $studentsTotal) * 100) : 0 }}%</div>
                        <div class="text-xs text-slate-500">Female</div>
                    </div>
                </div>
            </div>
            <div class="mt-3 flex justify-center gap-4 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span>
                    <span class="text-slate-600">Male {{ $studentsTotal > 0 ? round(($studentsBoys / $studentsTotal) * 100) : 0 }}%</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span>
                    <span class="text-slate-600">Female {{ $studentsTotal > 0 ? round(($studentsGirls / $studentsTotal) * 100) : 0 }}%</span>
                </div>
            </div>
            <div class="mt-1 text-center text-xs text-slate-400">Out of {{ $studentsTotal }}</div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 items-start">

        {{-- Subject Task Bar Chart --}}
        <div class="lg:col-span-2 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-4 flex items-center justify-between">
                <div class="text-base font-bold text-slate-800">Subject Task</div>
                <div class="flex gap-2">
                    @foreach(['Board 1','Board 2','Board 3','Board 4','Board 5'] as $i => $b)
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                            style="background:{{ ['#fb923c','#a78bfa','#34d399','#60a5fa','#f472b6'][$i] }}22;color:{{ ['#ea580c','#7c3aed','#059669','#2563eb','#db2777'][$i] }}">
                            {{ $b }}
                        </span>
                    @endforeach
                </div>
            </div>
            <canvas id="subjectTaskChart" height="200"></canvas>
        </div>

        {{-- Top Students --}}
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
                            @if($student?->passport_photo)
                                <img src="{{ asset('uploads/passports/' . $student->passport_photo) }}"
                                     class="h-10 w-10 rounded-full object-cover ring-2 ring-orange-100" alt="">
                            @else
                                <div class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-orange-400 to-amber-500 text-sm font-bold text-white ring-2 ring-orange-100">
                                    {{ mb_substr($student?->first_name ?? 'S', 0, 1) }}
                                </div>
                            @endif
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-slate-800">{{ $student?->full_name ?? '—' }}</div>
                            <div class="text-xs text-slate-400">{{ $student?->admission_number ?? '' }}</div>
                        </div>
                        <div class="text-sm font-bold text-orange-500">{{ round($row->avg_score) }}%</div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-slate-400">No student scores yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    window.dashboardData = {
        attendance: @json($attendanceData),
        examPass: {{ $passScores }},
        examFail: {{ $failScores }},
        boys: {{ $studentsBoys }},
        girls: {{ $studentsGirls }},
        subjects: @json($subjectStats->map(fn($s) => ['name' => $s->subject?->name ?? 'N/A', 'avg' => round($s->avg_score)])->values()),
    };
</script>
@vite('resources/js/pages/dashboard.js')
@endpush
