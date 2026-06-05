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
        <x-page-header :title="$student->full_name" :subtitle="$studentMeta" accent="students">
            <x-slot:leading>
                @if ($student->passport_photo_url)
                    <img
                        src="{{ $student->passport_photo_url }}"
                        alt="{{ $student->full_name }}"
                        class="h-32 w-32 rounded-full object-cover ring-2 ring-white shadow-sm"
                    />
                @else
                    <div class="grid h-32 w-32 place-items-center rounded-full bg-brand-50 text-3xl font-bold text-brand-700 ring-1 ring-inset ring-brand-100">
                        {{ $initials }}
                    </div>
                @endif
            </x-slot:leading>
            <x-slot:actions>
                <x-status-badge variant="{{ $statusVariant }}">{{ $student->status }}</x-status-badge>
                <a href="{{ route('students.admission-form', $student) }}" class="btn-primary">
                    <svg class="h-5 w-5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    Admission Form
                </a>
                @if (auth()->user()?->role === 'admin')
                    <a href="{{ route('students.edit', $student) }}" class="btn-outline">Edit</a>
                    <form
                        method="POST"
                        action="{{ route('students.destroy', $student) }}"
                        class="inline"
                        onsubmit="return confirm('Delete this student? This action cannot be undone.')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-warning">Delete</button>
                    </form>
                @endif
                <a href="{{ route('students.index') }}" class="btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </x-slot:actions>
        </x-page-header>

        @if ($errors->any())
            <div class="card-padded border border-orange-200 bg-orange-50/60">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="px-6">
                <div class="flex flex-wrap gap-6 border-b border-gray-200/70">
                    @foreach ($tabs as $key => $label)
                        <a
                            href="{{ route('students.show', ['student' => $student, 'tab' => $key]) }}"
                            class="{{ $tab === $key ? 'border-b-2 border-brand-500 text-brand-700' : 'border-b-2 border-transparent text-slate-600 hover:text-slate-900' }} -mb-px py-4 text-sm font-semibold"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($tab === 'attendance')
            <div class="space-y-4">
                <div class="card-padded">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Attendance</div>
                            <div class="mt-1 text-sm text-gray-600">History for this student (latest 30 marks).</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('attendance') }}" class="btn-primary">Open Attendance</a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card label="Present" :value="number_format((int) ($attendanceCounts['Present'] ?? 0))" iconBg="bg-green-50" iconColor="text-green-600" />
                    <x-stat-card label="Absent" :value="number_format((int) ($attendanceCounts['Absent'] ?? 0))" iconBg="bg-orange-50" iconColor="text-orange-600" />
                    <x-stat-card label="Late" :value="number_format((int) ($attendanceCounts['Late'] ?? 0))" iconBg="bg-brand-50" iconColor="text-brand-700" />
                    <x-stat-card label="Last Marked" :value="$lastAttendanceDate ? \Illuminate\Support\Carbon::parse($lastAttendanceDate)->format('M j, Y') : '-'" iconBg="bg-slate-50" iconColor="text-slate-700" />
                </div>

                <x-table>
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Class</th>
                            <th class="px-5 py-3">Note</th>
                            <th class="px-5 py-3">Taken By</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($attendanceMarks as $mark)
                            @php
                                $variant = match ($mark->status) {
                                    'Present' => 'success',
                                    'Absent' => 'warning',
                                    'Late' => 'info',
                                    default => 'neutral',
                                };
                            @endphp
                            <tr class="bg-white hover:bg-gray-50">
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $mark->sheet?->date?->format('M j, Y') ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <x-status-badge variant="{{ $variant }}">{{ $mark->status }}</x-status-badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">
                                    {{ $mark->sheet?->schoolClass?->name ?: '-' }} / {{ $mark->sheet?->section?->name ?: '-' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $mark->note ?: '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $mark->sheet?->takenBy?->name ?: '-' }}</td>
                                <td class="px-5 py-4 text-right">
                                    @if ($mark->sheet_id)
                                        <a
                                            href="{{ route('attendance', ['sheet' => $mark->sheet_id]) }}"
                                            class="inline-flex items-center justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-brand-700 ring-1 ring-inset ring-brand-100 hover:bg-brand-50"
                                        >
                                            Open Sheet
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No attendance marks yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </div>
        @elseif ($tab === 'results')
            <div class="space-y-4">
                <div class="card-padded">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Results</div>
                            <div class="mt-1 text-sm text-gray-600">Scores for this student across sessions/terms.</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('results.entry') }}" class="btn-primary">Enter Scores</a>
                            <a href="{{ route('results.report-card', $student) }}" class="btn-outline">Download Report Card</a>
                        </div>
                    </div>
                </div>

                @if ($scores->isEmpty())
                    <div class="card-padded text-center">
                        <div class="text-sm font-semibold text-gray-900">No scores yet</div>
                        <div class="mt-2 text-sm text-gray-600">Use Score Entry to add results for this student.</div>
                    </div>
                @else
                    @foreach ($scoreGroups as $groupTitle => $rows)
                        <div class="card-padded">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-semibold text-gray-900">{{ $groupTitle }}</div>
                                <x-status-badge variant="info">{{ $rows->count() }} subjects</x-status-badge>
                            </div>
                            <div class="mt-4">
                                <x-table>
                                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        <tr>
                                            <th class="px-5 py-3">Subject</th>
                                            <th class="px-5 py-3 text-right">CA1</th>
                                            <th class="px-5 py-3 text-right">CA2</th>
                                            <th class="px-5 py-3 text-right">Exam</th>
                                            <th class="px-5 py-3 text-right">Total</th>
                                            <th class="px-5 py-3 text-right">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($rows->sortBy(fn ($r) => $r->subject?->name) as $row)
                                            <tr class="bg-white hover:bg-gray-50">
                                                <td class="px-5 py-4">
                                                    <div class="text-sm font-semibold text-gray-900">{{ $row->subject?->name ?? '-' }}</div>
                                                    <div class="mt-1 text-xs text-gray-500">{{ $row->subject?->code ?? '' }}</div>
                                                </td>
                                                <td class="px-5 py-4 text-right text-sm font-medium text-gray-700">{{ $row->ca1 }}</td>
                                                <td class="px-5 py-4 text-right text-sm font-medium text-gray-700">{{ $row->ca2 }}</td>
                                                <td class="px-5 py-4 text-right text-sm font-medium text-gray-700">{{ $row->exam }}</td>
                                                <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ $row->total }}</td>
                                                <td class="px-5 py-4 text-right">
                                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">
                                                        {{ $row->grade ?: '-' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </x-table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @elseif ($tab === 'finance')
            <div class="space-y-4">
                <div class="card-padded">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Finance</div>
                            <div class="mt-1 text-sm text-gray-600">Recent payments and transaction history.</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('billing.index') }}" class="btn-primary">Open Billing</a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-stat-card label="Total Paid" :value="config('academyhub.currency_symbol').' '.number_format($studentIncomeTotal, 2)" iconBg="bg-green-50" iconColor="text-green-600" />
                    <x-stat-card label="Transactions" :value="number_format((int) $studentTransactions->count())" />
                    <x-stat-card label="Receipts" :value="number_format((int) $studentTransactions->whereNotNull('receipt_number')->count())" iconBg="bg-green-50" iconColor="text-green-600" />
                </div>

                <x-table>
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($studentTransactions as $t)
                            <tr class="bg-white hover:bg-gray-50">
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $t->date?->format('M j, Y') }}</td>
                                <td class="px-5 py-4">
                                    <x-status-badge variant="{{ $t->type === 'Income' ? 'success' : 'warning' }}">{{ $t->type }}</x-status-badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">
                                    <div class="font-medium text-gray-900">{{ $t->category }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        @if ($t->session)
                                            {{ $t->session }}
                                        @endif
                                        @if ($t->term)
                                            · Term {{ $t->term }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">{{ config('academyhub.currency_symbol') }}{{ number_format((float) $t->amount_paid, 2) }}</td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-700">
                                    {{ $t->receipt_number ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No transactions for this student yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-table>
            </div>
        @elseif ($tab === 'analytics')
            <div class="space-y-4">
                <div class="card-padded">
                    <div class="text-sm font-semibold text-gray-900">Performance Analytics</div>
                    <div class="mt-1 text-sm text-gray-600">Comprehensive performance tracking and insights.</div>
                </div>

                @if(!empty($performanceData))
                    {{-- Academic Performance --}}
                    <div class="card">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div class="text-sm font-semibold text-gray-900">Academic Performance</div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if(isset($performanceData['overview']) && $performanceData['overview']['total_subjects'] > 0)
                                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                                    <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-blue-50 to-blue-100/50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-blue-600">Average Score</div>
                                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $performanceData['overview']['average_score'] }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-purple-50 to-purple-100/50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-purple-600">Current Grade</div>
                                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $performanceData['overview']['grade'] }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-green-50 to-green-100/50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-green-600">Subjects Passed</div>
                                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $performanceData['overview']['subjects_passed'] }}/{{ $performanceData['overview']['total_subjects'] }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Highest Score</div>
                                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $performanceData['overview']['highest_score'] }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <div class="mt-3 text-sm font-medium text-gray-900">No scores recorded yet</div>
                                    <div class="mt-1 text-xs text-gray-500">Scores will appear here once entered for the current term</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Attendance --}}
                    <div class="card">
                        <div class="border-b border-gray-100 px-6 py-4">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <div class="text-sm font-semibold text-gray-900">Attendance Overview</div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if(isset($performanceData['attendance_impact']) && $performanceData['attendance_impact']['total_days'] > 0)
                                <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                                    <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-green-50 to-green-100/50 p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-green-600">Attendance Rate</div>
                                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $performanceData['attendance_impact']['attendance_rate'] }}%</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Present</div>
                                        <div class="mt-2 text-2xl font-bold text-green-600">{{ $performanceData['attendance_impact']['present_days'] }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Absent</div>
                                        <div class="mt-2 text-2xl font-bold text-red-600">{{ $performanceData['attendance_impact']['absent_days'] }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Late</div>
                                        <div class="mt-2 text-2xl font-bold text-yellow-600">{{ $performanceData['attendance_impact']['late_days'] }}</div>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Days</div>
                                        <div class="mt-2 text-2xl font-bold text-gray-900">{{ $performanceData['attendance_impact']['total_days'] }}</div>
                                    </div>
                                </div>
                                <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <div class="text-sm font-medium text-blue-900">{{ $performanceData['attendance_impact']['correlation'] }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <div class="mt-3 text-sm font-medium text-gray-900">No attendance records yet</div>
                                    <div class="mt-1 text-xs text-gray-500">Attendance data will appear here once marked</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        {{-- Homework Performance --}}
                        <div class="card">
                            <div class="border-b border-gray-100 px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <div class="text-sm font-semibold text-gray-900">Homework Performance</div>
                                </div>
                            </div>
                            <div class="p-6">
                                @if(isset($performanceData['homework_performance']) && $performanceData['homework_performance']['total_assignments'] > 0)
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                                            <span class="text-sm text-gray-600">Total Assignments</span>
                                            <span class="text-lg font-bold text-gray-900">{{ $performanceData['homework_performance']['total_assignments'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                                            <span class="text-sm text-gray-600">Submitted</span>
                                            <span class="text-lg font-bold text-green-600">{{ $performanceData['homework_performance']['submitted'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                                            <span class="text-sm text-gray-600">On Time</span>
                                            <span class="text-lg font-bold text-blue-600">{{ $performanceData['homework_performance']['on_time'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gradient-to-br from-purple-50 to-purple-100/50 p-3">
                                            <span class="text-sm font-semibold text-purple-700">Average Grade</span>
                                            <span class="text-lg font-bold text-purple-900">{{ $performanceData['homework_performance']['average_grade'] }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <div class="mt-3 text-sm font-medium text-gray-900">No homework yet</div>
                                        <div class="mt-1 text-xs text-gray-500">Data will appear once submitted</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- CBT Performance --}}
                        <div class="card">
                            <div class="border-b border-gray-100 px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <div class="text-sm font-semibold text-gray-900">CBT Exam Performance</div>
                                </div>
                            </div>
                            <div class="p-6">
                                @if(isset($performanceData['cbt_performance']) && $performanceData['cbt_performance']['total_exams'] > 0)
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                                            <span class="text-sm text-gray-600">Total Exams</span>
                                            <span class="text-lg font-bold text-gray-900">{{ $performanceData['cbt_performance']['total_exams'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                                            <span class="text-sm text-gray-600">Average Score</span>
                                            <span class="text-lg font-bold text-blue-600">{{ $performanceData['cbt_performance']['average_percent'] }}%</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                                            <span class="text-sm text-gray-600">Exams Passed</span>
                                            <span class="text-lg font-bold text-green-600">{{ $performanceData['cbt_performance']['exams_passed'] }}</span>
                                        </div>
                                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gradient-to-br from-orange-50 to-orange-100/50 p-3">
                                            <span class="text-sm font-semibold text-orange-700">Highest Score</span>
                                            <span class="text-lg font-bold text-orange-900">{{ $performanceData['cbt_performance']['highest_score'] }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <div class="mt-3 text-sm font-medium text-gray-900">No CBT exams yet</div>
                                        <div class="mt-1 text-xs text-gray-500">Results will appear once completed</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-padded text-center">
                        <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-yellow-50 text-yellow-600">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="mt-4 text-sm font-semibold text-gray-900">No Academic Term Available</div>
                        <div class="mt-2 text-sm text-gray-600">Please set up an academic term to view performance analytics</div>
                    </div>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <!-- Student Information Card -->
                    <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-blue-50 to-indigo-50/60 p-6 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <div class="text-lg font-black text-gray-900">Student Information</div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ([
                                'Admission Number' => $student->admission_number,
                                'Gender' => $student->gender,
                                'Blood Group' => $student->blood_group ?: '—',
                                'Date of Birth' => $student->dob?->format('Y-m-d') ?: '—',
                                'Class' => $student->schoolClass?->name ?: '—',
                                'Section' => $student->section?->name ?: '—',
                            ] as $label => $value)
                                <div class="rounded-2xl border border-white/60 bg-white/70 backdrop-blur-sm p-5 hover:shadow-md transition-all">
                                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $label }}</div>
                                    <div class="mt-2 text-base font-bold text-gray-900">{{ $value }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Subject Card -->
                    <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-purple-50 to-pink-50/60 p-6 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white shadow-lg shadow-purple-500/30">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" />
                                </svg>
                            </div>
                            <div class="text-lg font-black text-gray-900">Enrolled Subjects</div>
                        </div>
                        @if($student->schoolClass && $student->schoolClass->subjects->count() > 0)
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach($student->schoolClass->subjects as $subject)
                                    <div class="rounded-2xl border border-white/60 bg-white/70 backdrop-blur-sm p-4 hover:shadow-md transition-all">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-10 w-10 place-items-center rounded-lg bg-gradient-to-br from-purple-100 to-pink-100 text-purple-600">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $subject->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $subject->code }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-white/60 bg-white/70 backdrop-blur-sm p-5 text-center">
                                <div class="text-sm font-semibold text-gray-500">No subjects assigned</div>
                            </div>
                        @endif
                    </div>

                    <!-- Guardian Information Card -->
                    <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-orange-50/60 p-6 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <div class="text-lg font-black text-gray-900">Guardian Information</div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="rounded-2xl border border-white/60 bg-white/70 backdrop-blur-sm p-5 hover:shadow-md transition-all">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Name</div>
                                <div class="mt-2 text-base font-bold text-gray-900">{{ $student->guardian_name ?: '—' }}</div>
                            </div>
                            <div class="rounded-2xl border border-white/60 bg-white/70 backdrop-blur-sm p-5 hover:shadow-md transition-all">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Phone</div>
                                <div class="mt-2 text-base font-bold text-gray-900">{{ $student->guardian_phone ?: '—' }}</div>
                            </div>
                            <div class="rounded-2xl border border-white/60 bg-white/70 backdrop-blur-sm p-5 md:col-span-2 hover:shadow-md transition-all">
                                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Address</div>
                                <div class="mt-2 text-base font-bold text-gray-900">{{ $student->guardian_address ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-slate-50 to-gray-50/60 p-6 shadow-lg sticky top-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-slate-600 to-gray-700 text-white shadow-lg shadow-slate-500/30">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 8v4l3 3" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                            </div>
                            <div class="text-lg font-black text-gray-900">Recent Activities</div>
                        </div>
                        <div class="space-y-4">
                            @foreach ([
                                ['title' => 'Student record viewed', 'time' => now()->format('M j, Y g:i A'), 'variant' => 'info'],
                            ] as $item)
                                <div class="relative pl-5">
                                    <div class="absolute left-0 top-1.5 h-full w-px bg-gradient-to-b from-blue-200 to-transparent"></div>
                                    <div class="absolute left-[-4px] top-1.5 h-2.5 w-2.5 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/50"></div>
                                    <div class="rounded-xl border border-white/60 bg-white/70 backdrop-blur-sm p-4 hover:shadow-md transition-all">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm font-bold text-gray-800">{{ $item['title'] }}</div>
                                            <x-status-badge variant="{{ $item['variant'] }}">{{ strtoupper(mb_substr($item['variant'], 0, 1)) }}</x-status-badge>
                                        </div>
                                        <div class="mt-2 text-xs font-semibold text-gray-500">{{ $item['time'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
