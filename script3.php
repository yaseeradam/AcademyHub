<?php

$content = <<<'HTML'
@php
    /** @var \App\Models\Student $student */

    use App\Models\AttendanceMark;
    use App\Models\Score;
    use App\Models\Transaction;

    $tab = request('tab', 'profile');
    $tabs = [
        'profile' => 'Profile',
        'attendance' => 'Attendance',
        'results' => 'Results',
        'finance' => 'Finance',
        'analytics' => 'Analytics',
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

    $statusVariant = match ($student->status) {
        'Active' => 'success',
        'Graduated' => 'info',
        default => 'warning',
    };

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
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">

    {{-- Profile Header Card (Dashboard Style) --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-6 flex flex-col md:flex-row gap-6 md:items-center justify-between">
        <div class="flex items-center gap-5">
            @if ($student->passport_photo_url)
                <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}" class="h-20 w-20 rounded-full object-cover ring-4 ring-slate-50 shadow-sm" />
            @else
                <div class="grid h-20 w-20 shrink-0 place-items-center rounded-full bg-gradient-to-br from-orange-400 to-amber-500 text-2xl font-bold text-white shadow-sm">
                    {{ $initials }}
                </div>
            @endif
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800">{{ $student->full_name }}</h1>
                    @php 
                        $statusStyle = match($student->status) { 
                            'Active' => 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200', 
                            'Graduated' => 'bg-sky-50 text-sky-600 ring-1 ring-sky-200', 
                            default => 'bg-amber-50 text-amber-600 ring-1 ring-amber-200' 
                        }; 
                    @endphp
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusStyle }}">{{ $student->status }}</span>
                </div>
                <div class="mt-1 text-sm font-medium text-slate-500">{{ $studentMeta }}</div>
                
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('students.admission-form', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 hover:bg-orange-100 transition">
                        Admission Form
                    </a>
                    @if (auth()->user()?->role === 'admin')
                        <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                            Edit Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex md:flex-col items-end justify-between self-start h-full gap-2">
            <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to List
            </a>
            @if (auth()->user()?->role === 'admin')
                <form method="POST" action="{{ route('students.destroy', $student) }}" class="inline mt-2" onsubmit="return confirm('Delete this student? This action cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 underline underline-offset-2">Delete Student</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Error Banner --}}
    @if ($errors->any())
        <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
            <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
        <div class="flex overflow-x-auto border-b border-slate-100">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('students.show', ['student' => $student, 'tab' => $key]) }}"
                    class="{{ $tab === $key ? 'border-b-2 border-orange-500 text-orange-600 bg-orange-50/30' : 'border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50' }} flex-1 px-4 py-4 text-center text-sm font-bold transition whitespace-nowrap">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        
        <div class="p-6 bg-slate-50/50 min-h-[400px]">
            @if ($tab === 'attendance')
                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100 border-l-4 border-emerald-500">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Present</div>
                        <div class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format((int) ($attendanceCounts['Present'] ?? 0)) }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100 border-l-4 border-red-500">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Absent</div>
                        <div class="mt-1 text-2xl font-bold text-red-600">{{ number_format((int) ($attendanceCounts['Absent'] ?? 0)) }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100 border-l-4 border-amber-500">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Late</div>
                        <div class="mt-1 text-2xl font-bold text-amber-600">{{ number_format((int) ($attendanceCounts['Late'] ?? 0)) }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100 border-l-4 border-blue-500">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Last Marked</div>
                        <div class="mt-1 text-lg font-bold text-slate-800">{{ $lastAttendanceDate ? \Illuminate\Support\Carbon::parse($lastAttendanceDate)->format('M j, Y') : '—' }}</div>
                    </div>
                </div>

                {{-- Table Card --}}
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 p-5">
                        <div class="text-base font-bold text-slate-800">Attendance History</div>
                        <a href="{{ route('attendance') }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 transition hover:bg-orange-100">Open Register</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Status</th>
                                    <th class="px-5 py-3">Note</th>
                                    <th class="px-5 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($attendanceMarks as $mark)
                                    @php
                                        $variant = match ($mark->status) { 'Present' => 'bg-emerald-50 text-emerald-600', 'Absent' => 'bg-red-50 text-red-600', 'Late' => 'bg-amber-50 text-amber-600', default => 'bg-slate-50 text-slate-600' };
                                    @endphp
                                    <tr class="bg-white hover:bg-slate-50/50">
                                        <td class="px-5 py-3 text-sm font-medium text-slate-700">{{ $mark->sheet?->date?->format('M j, Y') ?: '—' }}</td>
                                        <td class="px-5 py-3">
                                            <span class="rounded-full {{ $variant }} px-2.5 py-0.5 text-xs font-semibold">{{ $mark->status }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-sm text-slate-500">{{ $mark->note ?: '—' }}</td>
                                        <td class="px-5 py-3 text-right">
                                            @if ($mark->sheet_id)
                                                <a href="{{ route('attendance', ['sheet' => $mark->sheet_id]) }}" class="text-xs font-bold text-orange-600 hover:underline">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">No attendance records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif ($tab === 'results')
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 p-5">
                        <div class="text-base font-bold text-slate-800">Academic Results</div>
                        <div class="flex gap-2">
                            <a href="{{ route('results.entry') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 transition hover:bg-orange-100">Enter Scores</a>
                            <a href="{{ route('results.report-card', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50">Report Card</a>
                        </div>
                    </div>
                    @if ($scores->isEmpty())
                        <div class="py-16 text-center text-sm text-slate-400">No academic results recorded yet.</div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($scoreGroups as $groupTitle => $rows)
                                <div class="px-5 py-4 bg-slate-50/50">
                                    <div class="mb-3 font-bold text-sm text-slate-700">{{ $groupTitle }}</div>
                                    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
                                        <table class="min-w-full text-left bg-white">
                                            <thead class="bg-white text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                                <tr>
                                                    <th class="px-4 py-2 text-left">Subject</th>
                                                    <th class="px-4 py-2 text-right">CA1</th>
                                                    <th class="px-4 py-2 text-right">CA2</th>
                                                    <th class="px-4 py-2 text-right">Exam</th>
                                                    <th class="px-4 py-2 text-right">Total</th>
                                                    <th class="px-4 py-2 text-right">Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach ($rows->sortBy(fn($r) => $r->subject?->name) as $row)
                                                    <tr class="hover:bg-slate-50/30">
                                                        <td class="px-4 py-2 text-sm font-semibold text-slate-700">{{ $row->subject?->name ?? '—' }}</td>
                                                        <td class="px-4 py-2 text-sm text-slate-500 text-right">{{ $row->ca1 }}</td>
                                                        <td class="px-4 py-2 text-sm text-slate-500 text-right">{{ $row->ca2 }}</td>
                                                        <td class="px-4 py-2 text-sm text-slate-500 text-right">{{ $row->exam }}</td>
                                                        <td class="px-4 py-2 text-sm font-bold text-slate-800 text-right">{{ $row->total }}</td>
                                                        <td class="px-4 py-2 text-sm font-bold text-orange-600 text-right">{{ $row->grade ?: '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            @elseif ($tab === 'finance')
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 mb-6">
                    <div class="rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 p-4 text-white shadow-sm ring-1 ring-slate-100">
                        <div class="text-xs font-bold uppercase tracking-wider text-white/80">Total Paid</div>
                        <div class="mt-1 text-2xl font-black">{{ config('myacademy.currency_symbol') }}{{ number_format($studentIncomeTotal, 2) }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Transactions</div>
                        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $studentTransactions->count() }}</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Receipts</div>
                        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $studentTransactions->whereNotNull('receipt_number')->count() }}</div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                    <div class="flex items-center justify-between border-b border-slate-100 p-5">
                        <div class="text-base font-bold text-slate-800">Transaction History</div>
                        <a href="{{ route('billing.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 transition hover:bg-orange-100">Open Billing</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Type</th>
                                    <th class="px-5 py-3">Category</th>
                                    <th class="px-5 py-3 text-right">Amount</th>
                                    <th class="px-5 py-3 text-right">Receipt #</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($studentTransactions as $t)
                                    <tr class="bg-white hover:bg-slate-50/50">
                                        <td class="px-5 py-3 text-sm font-medium text-slate-700">{{ $t->date?->format('M j, Y') }}</td>
                                        <td class="px-5 py-3">
                                            @php $v = $t->type === 'Income' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'; @endphp
                                            <span class="rounded-full {{ $v }} px-2.5 py-0.5 text-xs font-semibold">{{ $t->type }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-sm text-slate-600">
                                            {{ $t->category }} <span class="text-xs text-slate-400 block">{{ $t->session }}{{ $t->term ? ' · Term '.$t->term : '' }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-sm font-bold text-slate-800 text-right">{{ config('myacademy.currency_symbol') }}{{ number_format((float)$t->amount_paid, 2) }}</td>
                                        <td class="px-5 py-3 text-sm text-slate-500 text-right">{{ $t->receipt_number ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">No transactions recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif ($tab === 'analytics')
                @if(empty($performanceData))
                    <div class="py-16 text-center">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-slate-100 text-slate-400 mb-4">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="text-sm font-semibold text-slate-600">No Analysis Available</div>
                        <div class="text-xs text-slate-400 mt-1">Setup an active academic term to view performance analytics.</div>
                    </div>
                @else
                    <div class="space-y-6">
                        {{-- Academic --}}
                        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                            <div class="border-b border-slate-100 px-5 py-4 text-sm font-bold text-slate-800">Academic Overview</div>
                            <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="bg-blue-50/50 p-4 rounded-xl text-center">
                                    <div class="text-xs font-semibold text-blue-600 uppercase tracking-widest mb-1">Average</div>
                                    <div class="text-2xl font-black text-slate-800">{{ $performanceData['overview']['average_score'] ?? '—' }}</div>
                                </div>
                                <div class="bg-emerald-50/50 p-4 rounded-xl text-center">
                                    <div class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mb-1">Passed</div>
                                    <div class="text-2xl font-black text-slate-800">{{ $performanceData['overview']['subjects_passed'] ?? 0 }}/{{ $performanceData['overview']['total_subjects'] ?? 0 }}</div>
                                </div>
                                <div class="bg-purple-50/50 p-4 rounded-xl text-center">
                                    <div class="text-xs font-semibold text-purple-600 uppercase tracking-widest mb-1">Grade</div>
                                    <div class="text-2xl font-black text-slate-800">{{ $performanceData['overview']['grade'] ?? '—' }}</div>
                                </div>
                                <div class="bg-orange-50/50 p-4 rounded-xl text-center">
                                    <div class="text-xs font-semibold text-orange-600 uppercase tracking-widest mb-1">Highest</div>
                                    <div class="text-2xl font-black text-slate-800">{{ $performanceData['overview']['highest_score'] ?? '—' }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Attendance & CBT --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-5">
                                <div class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Attendance Impact</div>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500">Attendance Rate</span>
                                        <span class="font-bold text-slate-800">{{ $performanceData['attendance_impact']['attendance_rate'] ?? 0 }}%</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500">Correlation Info</span>
                                        <span class="font-medium text-blue-600 text-right">{{ $performanceData['attendance_impact']['correlation'] ?? 'Not enough data' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-5">
                                <div class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">CBT Performance</div>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500">Average CBT Score</span>
                                        <span class="font-bold text-slate-800">{{ $performanceData['cbt_performance']['average_percent'] ?? 0 }}%</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500">Exams Passed</span>
                                        <span class="font-medium text-emerald-600 text-right">{{ $performanceData['cbt_performance']['exams_passed'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            
            @else
                {{-- Profile Content (Default) --}}
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    {{-- Quick Details (Col 1 & Col 2) --}}
                    <div class="xl:col-span-2 space-y-6">
                        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-6">
                            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Personal Details</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><div class="text-xs text-slate-400 font-semibold uppercase">Admission No</div><div class="font-medium text-sm text-slate-800 mt-0.5">{{ $student->admission_number }}</div></div>
                                <div><div class="text-xs text-slate-400 font-semibold uppercase">Gender</div><div class="font-medium text-sm text-slate-800 mt-0.5">{{ $student->gender }}</div></div>
                                <div><div class="text-xs text-slate-400 font-semibold uppercase">Blood Group</div><div class="font-medium text-sm text-slate-800 mt-0.5">{{ $student->blood_group ?: '—' }}</div></div>
                                <div><div class="text-xs text-slate-400 font-semibold uppercase">Date of Birth</div><div class="font-medium text-sm text-slate-800 mt-0.5">{{ $student->dob?->format('M j, Y') ?: '—' }}</div></div>
                                <div><div class="text-xs text-slate-400 font-semibold uppercase">Class</div><div class="font-medium text-sm text-slate-800 mt-0.5">{{ $student->schoolClass?->name ?: '—' }}</div></div>
                                <div><div class="text-xs text-slate-400 font-semibold uppercase">Section</div><div class="font-medium text-sm text-slate-800 mt-0.5">{{ $student->section?->name ?: '—' }}</div></div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-6">
                            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Enrolled Subjects</h3>
                            @if($student->schoolClass && $student->schoolClass->subjects->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($student->schoolClass->subjects as $subject)
                                        <span class="inline-flex items-center rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">{{ $subject->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-sm text-slate-400 italic">No subjects assigned yet.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Sidebar Info (Col 3) --}}
                    <div class="xl:col-span-1 space-y-6">
                        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-6">
                            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Guardian Info</h3>
                            <div class="text-sm font-bold text-slate-800">{{ $student->guardian_name ?: '—' }}</div>
                            <div class="mt-1 text-xs text-slate-500 font-semibold">{{ $student->guardian_phone ?: 'No phone provided' }}</div>
                            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 leading-relaxed">{{ $student->guardian_address ?: 'No address provided' }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    </div>
@endsection
HTML;
file_put_contents('resources/views/pages/students/show.blade.php', $content);
?>
