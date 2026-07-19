@php
    /** @var \App\Models\Student $student */

    use App\Models\AttendanceMark;
    use App\Models\Score;
    use App\Models\Transaction;

    $user = auth()->user();
    $tenant = $user ? $user->tenant : (app()->bound('currentTenant') ? app('currentTenant') : null);
    $hasPaymentGateway = $tenant ? $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists() : false;

    $tab = request('tab', 'profile');
    $tabs = [
        'profile' => 'Profile Overview',
        'attendance' => 'Attendance Record',
        'results' => 'Academic Results',
    ];
    if ($hasPaymentGateway) {
        $tabs['finance'] = 'Financial Details';
    }
    $tabs['analytics'] = 'Performance Analytics';

    if ($tab === 'finance' && !$hasPaymentGateway) {
        $tab = 'profile';
    }

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
        $currentTerm = \App\Models\AcademicTerm::where('is_active', true)->first();
        if (!$currentTerm) {
            $currentTerm = \App\Models\AcademicTerm::latest()->first();
        }

        $session = $currentTerm?->academicSession?->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);
        $term = $currentTerm?->term_number ?? 1;

        $attendanceMarks = AttendanceMark::query()
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->select('attendance_marks.*')
            ->with([
                'sheet' => fn ($q) => $q->with(['schoolClass', 'section', 'takenBy']),
            ])
            ->where('attendance_marks.student_id', $student->id)
            ->where('attendance_sheets.term', $term)
            ->where('attendance_sheets.session', $session)
            ->orderByDesc('attendance_sheets.date')
            ->orderByDesc('attendance_marks.id')
            ->limit(30)
            ->get();

        $attendanceCounts = AttendanceMark::query()
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->where('attendance_marks.student_id', $student->id)
            ->where('attendance_sheets.term', $term)
            ->where('attendance_sheets.session', $session)
            ->selectRaw('attendance_marks.status, COUNT(*) as total')
            ->groupBy('attendance_marks.status')
            ->pluck('total', 'status');

        $lastAttendanceDate = AttendanceMark::query()
            ->where('student_id', $student->id)
            ->join('attendance_sheets', 'attendance_sheets.id', '=', 'attendance_marks.sheet_id')
            ->where('attendance_sheets.term', $term)
            ->where('attendance_sheets.session', $session)
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
            $sessionName = $currentTerm->academicSession->name ?? now()->format('Y') . '/' . (now()->format('Y') + 1);
            $performanceData = $service->getPerformanceAnalysis($student, $currentTerm->term_number, $sessionName);
        }
    }
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    @if (session('parent_credentials'))
        <div class="rounded-2xl border-2 border-indigo-200 bg-indigo-50/60 p-6 shadow-sm animate-fadeIn mb-4">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 shadow-inner shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-indigo-900">Parent Account Created Successfully</h3>
                    <p class="mt-1 text-sm text-indigo-700 font-medium">We registered a new parent account and linked it to this student. Please share these credentials with the parent:</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 max-w-lg bg-white/80 border border-indigo-100 rounded-xl p-3.5 text-sm">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block">Username / Email</span>
                            <span class="font-bold text-slate-800">{{ session('parent_credentials')['email'] }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block">Password</span>
                            <span class="font-bold text-slate-800">{{ session('parent_credentials')['password'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Profile Header Card --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 px-6 py-6 mb-2">
        <div class="flex flex-col gap-6 md:flex-row md:items-center justify-between">
            {{-- Left: Avatar & Info --}}
            <div class="flex items-center gap-5">
                <div class="shrink-0 relative">
                    @if ($student->passport_photo_url)
                        <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-slate-50 shadow-sm" />
                    @else
                        <div class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-2xl font-bold text-white shadow-sm ring-4 ring-slate-50">
                            {{ $initials }}
                        </div>
                    @endif
                </div>
                
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $student->full_name }}</h2>
                        @php $statusColor = match($student->status) { 'Active' => 'bg-emerald-50 text-emerald-600 ring-emerald-200', 'Graduated' => 'bg-emerald-50 text-emerald-600 ring-emerald-200', default => 'bg-amber-50 text-amber-600 ring-amber-200' }; @endphp
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $statusColor }}">{{ $student->status }}</span>
                    </div>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $studentMeta }}</p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('students.admission-form', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 hover:bg-orange-100 transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            Admission Form
                        </a>
                        @if (auth()->user()?->role === 'admin')
                            <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Profile
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Actions & Status --}}
            <div class="flex md:flex-col items-center md:items-end justify-between h-full gap-4">
                <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition ring-1 ring-slate-200 shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </a>
                
                @if (auth()->user()?->role === 'admin')
                    <form method="POST" action="{{ route('students.destroy', $student) }}" class="mt-1" onsubmit="return confirm('Delete this student?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline underline-offset-2 transition-colors">Delete Student</button>
                    </form>
                @endif
            </div>
        </div>
    </div>


    {{-- Tab Navigation --}}
    <div class="rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-slate-100 mb-6">
        <div class="flex gap-1 overflow-x-auto">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('students.show', ['student' => $student, 'tab' => $key]) }}"
                    class="{{ $tab === $key ? 'bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-md shadow-orange-200' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }} flex-1 min-w-[100px] rounded-xl py-2.5 text-center text-sm font-bold transition whitespace-nowrap">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm ring-1 ring-red-100">
            <div class="text-sm font-bold text-red-800 mb-2">Please fix the following issues:</div>
            <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main Content Area --}}
    @if ($tab === 'profile')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Unified Details Card --}}
            <div class="lg:col-span-2 rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-100">
                <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="text-lg font-bold text-slate-800">Student Particulars</div>
                </div>
                
                <div class="space-y-10">
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Personal Information</h4>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div><div class="text-xs font-semibold text-slate-500">Admission No</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->admission_number }}</div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Gender</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->gender }}</div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Date of Birth</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->dob?->format('F j, Y') ?: '—' }}</div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Blood Group</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->blood_group ?: '—' }}</div></div>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Academic Placement</h4>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div><div class="text-xs font-semibold text-slate-500">Class</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->schoolClass?->name ?: '—' }}</div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Section</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->section?->name ?: '—' }}</div></div>
                            <div class="sm:col-span-2">
                                <div class="text-xs font-semibold text-slate-500 mb-2">Enrolled Subjects</div>
                                @if($student->schoolClass && $student->schoolClass->subjects->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($student->schoolClass->subjects as $subject)
                                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $subject->name }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-sm text-slate-400">No subjects currently assigned.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Guardian Contact</h4>
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div><div class="text-xs font-semibold text-slate-500">Guardian Name</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->guardian_name ?: '—' }}</div></div>
                            <div><div class="text-xs font-semibold text-slate-500">Phone Number</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->guardian_phone ?: '—' }}</div></div>
                            <div class="sm:col-span-2"><div class="text-xs font-semibold text-slate-500">Residential Address</div><div class="mt-1 text-sm font-bold text-slate-800">{{ $student->guardian_address ?: '—' }}</div></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Stats matching Dashboard --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 p-6 text-white shadow-lg">
                    <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                    <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <div class="text-3xl font-black">{{ $student->schoolClass?->name ?: 'N/A' }}</div>
                            <div class="mt-1 text-sm font-semibold text-white/80">Current Class</div>
                        </div>
                        <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"/></svg>
                        </div>
                    </div>
                </div>
                
                {{-- Activity Feed --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-base font-bold text-slate-800">Recent Activity</div>
                    </div>
                    <div class="space-y-4">
                        @foreach ([
                            ['title' => 'Student record viewed', 'time' => now()->format('M j, Y g:i A'), 'color' => 'bg-emerald-400'],
                        ] as $item)
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 h-2 w-2 rounded-full {{ $item['color'] }} shrink-0"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-semibold text-slate-800">{{ $item['title'] }}</div>
                                    <div class="text-xs text-slate-400">{{ $item['time'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    @elseif ($tab === 'attendance')
        {{-- Attendance Stat Cards matching dashboard --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-green-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black">{{ number_format((int) ($attendanceCounts['Present'] ?? 0)) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Present</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-400 to-rose-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black">{{ number_format((int) ($attendanceCounts['Absent'] ?? 0)) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Absent</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black">{{ number_format((int) ($attendanceCounts['Late'] ?? 0)) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Late</div>
                </div>
            </div>
            
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-xl font-bold mt-2 truncate">{{ $lastAttendanceDate ? \Illuminate\Support\Carbon::parse($lastAttendanceDate)->format('M j, Y') : 'N/A' }}</div>
                    <div class="mt-2 text-sm font-semibold text-white/80">Last Attendance</div>
                </div>
            </div>
        </div>

        {{-- Table mimicking index.blade.php style --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 mt-6">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Attendance Log</div>
                    <div class="mt-0.5 text-sm text-slate-500">History of the latest 30 marks for this student</div>
                </div>
                <a href="{{ route('attendance') }}" class="text-sm font-semibold text-orange-500 hover:text-orange-600 transition">View Full Register &rarr;</a>
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
                        @forelse ($attendanceMarks as $mark)
                            <tr class="bg-white hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $mark->sheet?->date?->format('M j, Y') ?: '—' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $variant = match ($mark->status) {
                                            'Present' => 'bg-emerald-100 text-emerald-700',
                                            'Absent' => 'bg-red-100 text-red-700',
                                            'Late' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <span class="rounded-full {{ $variant }} px-3 py-1 text-xs font-bold">{{ $mark->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $mark->sheet?->schoolClass?->name ?: '—' }} / {{ $mark->sheet?->section?->name ?: '—' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $mark->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400">No attendance marks have been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($tab === 'results')
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Academic Records</div>
                    <div class="mt-0.5 text-sm text-slate-500">Historical performance data across sessions</div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('results.entry') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">Enter Scores</a>
                    <a href="{{ route('results.report-card', $student) }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700 transition">Generate Report Card</a>
                </div>
            </div>
            
            <div class="p-6">
                @if ($scores->isEmpty())
                    <div class="py-12 text-center text-sm text-slate-400">No academic results available for this student.</div>
                @else
                    <div class="space-y-8">
                        @foreach ($scoreGroups as $groupTitle => $rows)
                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">{{ $groupTitle }}</h4>
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
                                            @foreach ($rows->sortBy(fn($r) => $r->subject?->name) as $row)
                                                <tr class="bg-white hover:bg-slate-50 transition">
                                                    <td class="px-5 py-4">
                                                        <div class="text-sm font-semibold text-slate-800">{{ $row->subject?->name ?? '—' }}</div>
                                                        <div class="text-xs text-slate-400">{{ $row->subject?->code ?? '' }}</div>
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-slate-500 text-right">{{ $row->ca1 }}</td>
                                                    <td class="px-5 py-4 text-sm text-slate-500 text-right">{{ $row->ca2 }}</td>
                                                    <td class="px-5 py-4 text-sm text-slate-500 text-right">{{ $row->exam }}</td>
                                                    <td class="px-5 py-4 text-sm font-black text-slate-800 text-right">{{ $row->total }}</td>
                                                    <td class="px-5 py-4 text-right">
                                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $row->grade ?: '—' }}</span>
                                                    </td>
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
        </div>

    @elseif ($tab === 'finance')
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-green-500 p-6 text-white shadow-lg">
                <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="text-3xl font-black">{{ config('academyhub.currency_symbol') }}{{ number_format($studentIncomeTotal, 2) }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Processed Income</div>
                </div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 flex flex-col justify-center">
                <div class="text-3xl font-black text-slate-800">{{ $studentTransactions->count() }}</div>
                <div class="mt-1 text-sm font-bold text-slate-400 uppercase tracking-widest">Transactions</div>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 flex flex-col justify-center">
                <div class="text-3xl font-black text-slate-800">{{ $studentTransactions->whereNotNull('receipt_number')->count() }}</div>
                <div class="mt-1 text-sm font-bold text-slate-400 uppercase tracking-widest">Issued Receipts</div>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 mt-6">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Payment History</div>
                    <div class="mt-0.5 text-sm text-slate-500">Log of recent transactions and payments</div>
                </div>
                <a href="{{ route('billing.index') }}" class="text-sm font-semibold text-emerald-500 hover:text-emerald-600 transition">Go to Billing &rarr;</a>
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
                        @forelse ($studentTransactions as $t)
                            <tr class="bg-white hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ $t->date?->format('M j, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $t->type === 'Income' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $t->type }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="font-bold text-slate-800">{{ $t->category }}</div>
                                    <div class="text-xs text-slate-400">{{ $t->session }}{{ $t->term ? ' · Term '.$t->term : '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-black text-slate-800">{{ config('academyhub.currency_symbol') }}{{ number_format((float)$t->amount_paid, 2) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-slate-500 font-mono">{{ $t->receipt_number ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400">No transactions recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($tab === 'analytics')
        <div class="space-y-6">
            {{-- Performance Analytics Title --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-lg font-bold text-slate-800">Performance Analytics</div>
                    <div class="mt-0.5 text-sm text-slate-500">Comprehensive performance tracking and academic insights for the current active term.</div>
                </div>
            </div>

            @if(!empty($performanceData))
                {{-- Academic Performance Card --}}
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-blue-50/50 to-indigo-50/30">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="text-base font-bold text-slate-800">Academic Performance</div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(isset($performanceData['overview']) && $performanceData['overview']['total_subjects'] > 0)
                            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                                <div class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-blue-100/30 p-5 hover:shadow-md transition">
                                    <div class="text-xs font-bold uppercase tracking-wider text-blue-600">Average Score</div>
                                    <div class="mt-2 text-2xl font-black text-slate-800">{{ $performanceData['overview']['average_score'] }}</div>
                                </div>
                                <div class="rounded-xl border border-purple-100 bg-gradient-to-br from-purple-50 to-purple-100/30 p-5 hover:shadow-md transition">
                                    <div class="text-xs font-bold uppercase tracking-wider text-purple-600">Current Grade</div>
                                    <div class="mt-2 text-2xl font-black text-slate-800">{{ $performanceData['overview']['grade'] }}</div>
                                </div>
                                <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-emerald-100/30 p-5 hover:shadow-md transition">
                                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Subjects Passed</div>
                                    <div class="mt-2 text-2xl font-black text-slate-800">{{ $performanceData['overview']['subjects_passed'] }} / {{ $performanceData['overview']['total_subjects'] }}</div>
                                </div>
                                <div class="rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-indigo-100/30 p-5 hover:shadow-md transition">
                                    <div class="text-xs font-bold uppercase tracking-wider text-indigo-600">Highest Score</div>
                                    <div class="mt-2 text-2xl font-black text-slate-800">{{ $performanceData['overview']['highest_score'] }}</div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <div class="mt-3 text-sm font-bold text-slate-700">No scores recorded yet</div>
                                <div class="mt-1 text-xs text-slate-500">Academic performance scores will appear here once grades are entered for the active term.</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Performance Trend and Strengths Grid --}}
                @if(isset($performanceData['progress_trend']) && $performanceData['progress_trend']->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {{-- Term-by-Term Progress Bar Chart --}}
                        <div class="lg:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                            <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-violet-50/50 to-purple-50/30">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                                    </div>
                                    <div class="text-base font-bold text-slate-800">Academic Progress Trend</div>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="rounded-xl bg-slate-50/50 p-5 ring-1 ring-slate-100">
                                    <div class="flex items-end justify-around h-48 gap-4 pt-4">
                                        @foreach($performanceData['progress_trend'] as $trend)
                                            <div class="flex flex-col items-center gap-1.5 flex-1">
                                                <span class="text-xs font-bold text-slate-700">{{ $trend['average'] }}</span>
                                                <div class="w-full max-w-[40px] rounded-t-lg bg-gradient-to-t from-violet-500 to-indigo-400 shadow-sm transition-all duration-500 hover:opacity-90"
                                                     style="height: {{ max(6, $trend['percentage'] * 1.5) }}px;"></div>
                                                <span class="text-xs font-bold text-slate-500">{{ $trend['term'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Strengths vs Weaknesses Mini List --}}
                        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                            <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-emerald-50/50 to-rose-50/30">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                    </div>
                                    <div class="text-base font-bold text-slate-800">Academic Standing</div>
                                </div>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-2">Strengths</div>
                                    @if(isset($performanceData['strengths_weaknesses']['strengths']) && $performanceData['strengths_weaknesses']['strengths']->isNotEmpty())
                                        <div class="space-y-1.5">
                                            @foreach($performanceData['strengths_weaknesses']['strengths'] as $s)
                                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 bg-emerald-50 px-2.5 py-1.5 rounded-lg">
                                                    <span>{{ $s['subject'] }}</span>
                                                    <span class="text-emerald-700 font-bold">{{ $s['score'] }} ({{ $s['grade'] }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1.5 rounded-lg">No strengths identified yet.</div>
                                    @endif
                                </div>

                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-rose-600 mb-2">Needs Attention</div>
                                    @if(isset($performanceData['strengths_weaknesses']['weaknesses']) && $performanceData['strengths_weaknesses']['weaknesses']->isNotEmpty())
                                        <div class="space-y-1.5">
                                            @foreach($performanceData['strengths_weaknesses']['weaknesses'] as $w)
                                                <div class="flex items-center justify-between text-xs font-semibold text-slate-700 bg-rose-50 px-2.5 py-1.5 rounded-lg">
                                                    <span>{{ $w['subject'] }}</span>
                                                    <span class="text-rose-700 font-bold">{{ $w['score'] }} ({{ $w['grade'] }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1.5 rounded-lg">No weak areas identified.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Subject Performance Progress Chart --}}
                @if(isset($performanceData['subject_performance']) && $performanceData['subject_performance']->isNotEmpty())
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                        <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-blue-50/50 to-indigo-50/30">
                            <div class="flex items-center gap-2.5">
                                <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">Subject-wise Academic Standing</div>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($performanceData['subject_performance'] as $sp)
                                @php
                                    $barBg = match(true) {
                                        $sp['percentage'] >= 70 => 'bg-emerald-500',
                                        $sp['percentage'] >= 50 => 'bg-blue-500',
                                        default => 'bg-amber-500',
                                    };
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-sm font-bold text-slate-700">
                                        <span>{{ $sp['subject'] }}</span>
                                        <span>{{ $sp['total'] }} ({{ $sp['grade'] }} • {{ $sp['percentage'] }}%)</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden shadow-inner">
                                        <div class="h-full rounded-full {{ $barBg }} transition-all duration-500" style="width: {{ $sp['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Attendance Overview Card --}}
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                    <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-emerald-50/50 to-teal-50/30">
                        <div class="flex items-center gap-2.5">
                            <div class="h-8 w-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <div class="text-base font-bold text-slate-800">Attendance Overview</div>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(isset($performanceData['attendance_impact']) && $performanceData['attendance_impact']['total_days'] > 0)
                            <div class="grid grid-cols-2 gap-2.5 lg:grid-cols-5">
                                <div class="rounded-lg border border-emerald-100 bg-gradient-to-br from-emerald-50 to-emerald-100/30 p-3 hover:shadow-sm transition">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Attendance Rate</div>
                                    <div class="mt-1 text-lg font-extrabold text-slate-800">{{ $performanceData['attendance_impact']['attendance_rate'] }}%</div>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-white p-3 hover:shadow-sm transition">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Days Present</div>
                                    <div class="mt-1 text-lg font-extrabold text-emerald-600">{{ $performanceData['attendance_impact']['present_days'] }}</div>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-white p-3 hover:shadow-sm transition">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Days Absent</div>
                                    <div class="mt-1 text-lg font-extrabold text-red-500">{{ $performanceData['attendance_impact']['absent_days'] }}</div>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-white p-3 hover:shadow-sm transition">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Days Late</div>
                                    <div class="mt-1 text-lg font-extrabold text-amber-500">{{ $performanceData['attendance_impact']['late_days'] }}</div>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-white p-3 hover:shadow-sm transition">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Term Days</div>
                                    <div class="mt-1 text-lg font-extrabold text-slate-800">{{ $performanceData['attendance_impact']['total_days'] }}</div>
                                </div>
                            </div>
                            <div class="mt-5 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50/50 p-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <svg class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div class="text-sm font-semibold text-blue-900 leading-relaxed">{{ $performanceData['attendance_impact']['correlation'] }}</div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <div class="mt-3 text-sm font-bold text-slate-700">No attendance records yet</div>
                                <div class="mt-1 text-xs text-slate-500">Attendance analysis will populate once student roll-calls are marked.</div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Homework & CBT Grid --}}
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- Homework Performance Card --}}
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                        <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-purple-50/50 to-pink-50/30">
                            <div class="flex items-center gap-2.5">
                                <div class="h-8 w-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">Homework Performance</div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if(isset($performanceData['homework_performance']) && $performanceData['homework_performance']['total_assignments'] > 0)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                                        <span class="text-sm font-semibold text-slate-500">Total Assignments</span>
                                        <span class="text-base font-black text-slate-800">{{ $performanceData['homework_performance']['total_assignments'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                                        <span class="text-sm font-semibold text-slate-500">Submitted Tasks</span>
                                        <span class="text-base font-black text-emerald-600">{{ $performanceData['homework_performance']['submitted'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                                        <span class="text-sm font-semibold text-slate-500">Tasks On Time</span>
                                        <span class="text-base font-black text-blue-600">{{ $performanceData['homework_performance']['on_time'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-purple-150 bg-gradient-to-br from-purple-50 to-purple-100/30 px-4 py-3">
                                        <span class="text-sm font-bold text-purple-700">Average Grade</span>
                                        <span class="text-base font-black text-purple-950">{{ $performanceData['homework_performance']['average_grade'] }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <div class="mt-3 text-sm font-bold text-slate-700">No homework assigned yet</div>
                                    <div class="mt-1 text-xs text-slate-500">Homework stats will activate when assignments are submitted.</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- CBT Exam Performance Card --}}
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                        <div class="border-b border-slate-100 px-6 py-4 bg-gradient-to-r from-orange-50/50 to-amber-50/30">
                            <div class="flex items-center gap-2.5">
                                <div class="h-8 w-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">CBT Exam Performance</div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if(isset($performanceData['cbt_performance']) && $performanceData['cbt_performance']['total_exams'] > 0)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-3">
                                        <span class="text-sm font-semibold text-slate-500">Total CBT Exams</span>
                                        <span class="text-base font-black text-slate-800">{{ $performanceData['cbt_performance']['total_exams'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                                        <span class="text-sm font-semibold text-slate-500">Average CBT Score</span>
                                        <span class="text-base font-black text-blue-600">{{ $performanceData['cbt_performance']['average_percent'] }}%</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
                                        <span class="text-sm font-semibold text-slate-500">Exams Passed</span>
                                        <span class="text-base font-black text-emerald-600">{{ $performanceData['cbt_performance']['exams_passed'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-orange-150 bg-gradient-to-br from-orange-50 to-orange-100/30 px-4 py-3">
                                        <span class="text-sm font-bold text-orange-700">Highest CBT Score</span>
                                        <span class="text-base font-black text-orange-950">{{ $performanceData['cbt_performance']['highest_score'] }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <div class="mt-3 text-sm font-bold text-slate-700">No CBT exams completed</div>
                                    <div class="mt-1 text-xs text-slate-500">CBT performance metrics will activate once exam attempts are saved.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                {{-- No Academic Term Fallback --}}
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 p-12 text-center">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-amber-50 text-amber-600 shadow-md">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="mt-5 text-lg font-black text-slate-800">No Academic Term Active</div>
                    <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">Please set up and activate an academic term in settings to populate live student performance analytics.</p>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
