@php
    use App\Models\AcademicTerm;
    use App\Models\AttendanceMark;
    use App\Models\CbtExam;
    use App\Models\SchoolClass;
    use App\Models\Score;
    use App\Models\Student;
    use App\Models\Transaction;
    use App\Models\User;
    use Illuminate\Support\Facades\DB;

    $todayLabel = now()->format('l, F j, Y');
    $user = auth()->user();
    $schoolName = config('myacademy.school_name', config('app.name', 'MyAcademy'));
    $currentTerm = AcademicTerm::active();

    $studentsTotal = Student::query()->count();
    $studentsBoys = Student::query()->where('gender', 'Male')->count();
    $studentsGirls = Student::query()->where('gender', 'Female')->count();
    $teachersTotal = User::query()->where('role', 'teacher')->count();
    $classesTotal = SchoolClass::query()->count();

    // Today's attendance
    $attendanceToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->count();
    $presentToday = AttendanceMark::query()
        ->whereHas('sheet', fn($q) => $q->whereDate('date', today()))
        ->where('status', 'Present')
        ->count();
    $attendanceRate = $attendanceToday > 0 ? round(($presentToday / $attendanceToday) * 100, 1) : 0;

    // Active CBT exams
    $activeExams = CbtExam::query()
        ->where('status', 'published')
        ->count();

    $feesCollectedToday = (float) Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereDate('date', today())
        ->sum('amount_paid');

    $feesCollectedThisWeek = (float) Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
        ->sum('amount_paid');

    $feesCollectedThisMonth = (float) Transaction::query()
        ->where('type', 'Income')
        ->where('is_void', false)
        ->whereMonth('date', now()->month)
        ->whereYear('date', now()->year)
        ->sum('amount_paid');

    $financeData = \Illuminate\Support\Facades\Cache::remember('dashboard_finance_all_time', \DateInterval::createFromDateString('15 minutes'), function () {
        $estimatedTuitionDueAllTime = (float) DB::table('students')
            ->leftJoin('fee_structures', function ($join) {
                $join->on('fee_structures.class_id', '=', 'students.class_id')
                    ->where('fee_structures.category', '=', 'Tuition')
                    ->whereNull('fee_structures.term')
                    ->whereNull('fee_structures.session');
            })
            ->sum('fee_structures.amount_due');

        $incomeAllTime = (float) Transaction::query()
            ->where('type', 'Income')
            ->where('is_void', false)
            ->sum('amount_paid');

        $outstandingPaymentsEstimate = max(0.0, $estimatedTuitionDueAllTime - $incomeAllTime);

        $dueByStudent = DB::table('students')
            ->leftJoin('fee_structures', function ($join) {
                $join->on('fee_structures.class_id', '=', 'students.class_id')
                    ->where('fee_structures.category', '=', 'Tuition')
                    ->whereNull('fee_structures.term')
                    ->whereNull('fee_structures.session');
            })
            ->select('students.id', DB::raw('COALESCE(SUM(fee_structures.amount_due), 0) as due'))
            ->groupBy('students.id');

        $paidByStudent = DB::table('transactions')
            ->where('type', 'Income')
            ->where('is_void', false)
            ->whereNotNull('student_id')
            ->select('student_id', DB::raw('SUM(amount_paid) as paid'))
            ->groupBy('student_id');

        $overdueInvoices = DB::query()
            ->fromSub($dueByStudent, 'd')
            ->leftJoinSub($paidByStudent, 'p', 'p.student_id', '=', 'd.id')
            ->whereRaw('d.due > COALESCE(p.paid, 0)')
            ->count();

        return [
            'estimatedTuitionDueAllTime' => $estimatedTuitionDueAllTime,
            'incomeAllTime' => $incomeAllTime,
            'outstandingPaymentsEstimate' => $outstandingPaymentsEstimate,
            'overdueInvoices' => $overdueInvoices,
        ];
    });

    $estimatedTuitionDueAllTime = $financeData['estimatedTuitionDueAllTime'];
    $incomeAllTime = $financeData['incomeAllTime'];
    $outstandingPaymentsEstimate = $financeData['outstandingPaymentsEstimate'];
    $overdueInvoices = $financeData['overdueInvoices'];

    $latestScores = Score::query()
        ->with(['student', 'subject', 'schoolClass'])
        ->latest('updated_at')
        ->limit(6)
        ->get();

    // Attendance data for last 7 days
    $attendanceData = \Illuminate\Support\Facades\Cache::remember('dashboard_attendance_trend', \DateInterval::createFromDateString('15 minutes'), function () {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $present = \App\Models\AttendanceMark::query()
                ->whereHas('sheet', fn($q) => $q->whereDate('date', $date))
                ->where('status', 'Present')
                ->count();
            $absent = \App\Models\AttendanceMark::query()
                ->whereHas('sheet', fn($q) => $q->whereDate('date', $date))
                ->where('status', 'Absent')
                ->count();
            $data[] = [
                'label' => $date->format('D'),
                'present' => $present,
                'absent' => $absent,
            ];
        }
        return $data;
    });

    // Exam performance data (caching removed)
    $totalScores = \App\Models\Score::query()->count();
    $passScores = \App\Models\Score::query()->where('total', '>=', 50)->count();
    $examStats = [
        'totalScores' => $totalScores,
        'passScores' => $passScores,
        'failScores' => $totalScores - $passScores,
    ];

    $totalScores = $examStats['totalScores'];
    $passScores = $examStats['passScores'];
    $failScores = $examStats['failScores'];
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="space-y-4">
            <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-600 via-orange-600 to-orange-700 shadow-2xl transition-all duration-500 hover:shadow-amber-500/50">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48cGF0aCBkPSJNMzYgMzRjMC0yLjIxLTEuNzktNC00LTRzLTQgMS43OS00IDQgMS43OSA0IDQgNCA0LTEuNzkgNC00em0wLTEwYzAtMi4yMS0xLjc5LTQtNC00cy00IDEuNzktNCA0IDEuNzkgNCA0IDQgNC0xLjc5IDQtNHptMC0xMGMwLTIuMjEtMS43OS00LTQtNHMtNCAxLjc5LTQgNCAxLjc5IDQgNCA0IDQtMS43OSA0LTR6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                <div class="absolute right-0 top-0 h-96 w-96 -translate-y-32 translate-x-32 rounded-full bg-white/10"></div>
                <div class="absolute left-0 bottom-0 h-64 w-64 -translate-x-24 translate-y-24 rounded-full bg-black/10"></div>
                
                <div class="relative h-48 w-full sm:h-56">
                    <div class="absolute inset-0 flex flex-col justify-end p-8">
                        <div class="max-w-3xl">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-2 backdrop-blur-md">
                                <div class="h-2 w-2 animate-pulse rounded-full bg-green-400"></div>
                                <span class="text-sm font-bold text-white">Live System</span>
                            </div>
                            
                            <div class="mt-4 text-4xl font-black tracking-tight text-white sm:text-5xl">
                                {{ $schoolName }}
                            </div>
                            
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-md">
                                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="text-sm font-bold text-white">{{ $currentTerm ? $currentTerm->name : 'No Active Term' }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-md">
                                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <span class="text-sm font-bold text-white">{{ config('myacademy.current_week', 'Week 1') }}</span>
                                </div>
                                <div class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-md">
                                    <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                    </svg>
                                    <span class="text-sm font-bold text-white">{{ number_format((int) $studentsTotal) }} Students</span>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('attendance') }}" class="group/btn inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 font-bold text-amber-600 shadow-lg transition-shadow duration-200 hover:shadow-xl">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 11l3 3L22 4"/>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                    </svg>
                                    Take Attendance
                                </a>
                                <a href="{{ route('results.entry') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-5 py-3 font-bold text-white backdrop-blur-md transition-all hover:bg-white/30">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                    Enter Results
                                </a>
                                <a href="{{ route('billing.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-5 py-3 font-bold text-white backdrop-blur-md transition-all hover:bg-white/30">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                    </svg>
                                    Record Payment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-900">Today at a Glance</div>
                <div class="text-xs text-slate-500">Signed in as {{ $user?->name ?? 'Admin' }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 shadow-sm ring-1 ring-blue-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-blue-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-blue-600">Total Students</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($studentsTotal) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">{{ $studentsBoys }}M / {{ $studentsGirls }}F</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg shadow-blue-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100/50 p-6 shadow-sm ring-1 ring-purple-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-purple-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-purple-600">Teachers</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($teachersTotal) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">teaching staff</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow-lg shadow-purple-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-6 shadow-sm ring-1 ring-indigo-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-indigo-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-indigo-600">Classes</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($classesTotal) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">active classes</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white shadow-lg shadow-indigo-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-50 to-green-100/50 p-6 shadow-sm ring-1 ring-green-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-green-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-green-600">Attendance</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ $attendanceRate }}%</div>
                            <div class="mt-1.5 text-xs text-slate-600">{{ $presentToday }}/{{ $attendanceToday }} today</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg shadow-green-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 shadow-sm ring-1 ring-amber-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-amber-600">Active Exams</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($activeExams) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">ongoing CBT</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-lg shadow-amber-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section class="space-y-3">
            <div class="text-sm font-semibold text-slate-900">Academics</div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 lg:col-span-2">
                    <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-blue-500/5"></div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Attendance</div>
                            <div class="mt-1 text-sm text-slate-600">7-day present vs absent trend.</div>
                        </div>
                        <x-status-badge variant="info">This week</x-status-badge>
                    </div>
                    <div class="mt-4">
                        <canvas id="attendanceTrendChart" height="220"></canvas>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-green-500/5"></div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Exam Status</div>
                            <div class="mt-1 text-sm text-slate-600">Performance snapshot.</div>
                        </div>
                        <x-status-badge variant="success">Ongoing</x-status-badge>
                    </div>
                    <div class="mt-4">
                        <canvas id="examPerformanceChart" height="220"></canvas>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 lg:col-span-3">
                    <div class="absolute -right-16 top-0 h-40 w-40 rounded-full bg-purple-500/5"></div>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Latest Score Entry</div>
                            <div class="mt-1 text-sm text-slate-600">Most recent updates across classes.</div>
                        </div>
                        <a href="{{ route('results.entry') }}" class="btn-outline">Open Entry</a>
                    </div>

                    <div class="mt-4">
                        <x-table>
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-5 py-3">Student</th>
                                    <th class="px-5 py-3">Class</th>
                                    <th class="px-5 py-3">Subject</th>
                                    <th class="px-5 py-3 text-right">Total</th>
                                    <th class="px-5 py-3 text-right">Updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($latestScores as $row)
                                    <tr class="bg-white hover:bg-slate-50">
                                        <td class="px-5 py-4">
                                            <div class="text-sm font-semibold text-slate-900">{{ $row->student?->full_name ?? '—' }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $row->student?->admission_number ?? '' }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-slate-700">{{ $row->schoolClass?->name ?? '—' }}</td>
                                        <td class="px-5 py-4 text-sm text-slate-700">
                                            <div class="font-medium text-slate-900">{{ $row->subject?->name ?? '—' }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $row->subject?->code ?? '' }}</div>
                                        </td>
                                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-900">{{ $row->total }}</td>
                                        <td class="px-5 py-4 text-right text-sm text-slate-600">{{ $row->updated_at?->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                            No score records yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-table>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="text-sm font-semibold text-slate-900">Finance</div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 shadow-sm ring-1 ring-emerald-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-emerald-600">Collected Today</div>
                            <div class="mt-2.5 text-2xl font-bold tracking-tight text-slate-900">{{ config('myacademy.currency_symbol') }}{{ number_format($feesCollectedToday, 2) }}</div>
                            <div class="mt-1.5 text-xs text-slate-600">Week: {{ config('myacademy.currency_symbol') }}{{ number_format($feesCollectedThisWeek, 2) }}</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-50 to-cyan-100/50 p-6 shadow-sm ring-1 ring-cyan-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-cyan-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-cyan-600">Outstanding</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ config('myacademy.currency_symbol') }}{{ number_format($outstandingPaymentsEstimate, 2) }}</div>
                            <div class="mt-1.5 text-sm text-slate-600">from fee structures</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 text-white shadow-lg shadow-cyan-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-50 to-red-100/50 p-6 shadow-sm ring-1 ring-red-200/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-red-500/5"></div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-red-600">Overdue</div>
                            <div class="mt-2.5 text-3xl font-bold tracking-tight text-slate-900">{{ number_format((int) $overdueInvoices) }}</div>
                            <div class="mt-1.5 text-sm text-slate-600">with balance due</div>
                        </div>
                        <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-red-400 to-red-600 text-white shadow-lg shadow-red-500/30">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        window.dashboardData = {
            attendance: @json($attendanceData),
            examPass: {{ $passScores }},
            examFail: {{ $failScores }}
        };
    </script>
    @vite('resources/js/pages/dashboard.js')
@endpush
