import re

with open('show_original.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Profile tab: Replace the 3 cards with one card containing 3 sections
profile_old_start = '<!-- Student Information Card -->'
profile_old_end = '<div class="xl:col-span-1">'

profile_old_regex = re.compile(f'{re.escape(profile_old_start)}.*?(?={re.escape(profile_old_end)})', re.DOTALL)

profile_new = """<!-- Combined Profile Details Card -->
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="text-base font-bold text-slate-800">Profile Details</div>
                        </div>
                        
                        <div class="p-6 space-y-8">
                            <!-- Student Information -->
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-50 pb-2 mb-4">Student Information</h3>
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    @foreach ([
                                        'Admission Number' => $student->admission_number,
                                        'Gender' => $student->gender,
                                        'Blood Group' => $student->blood_group ?: '—',
                                        'Date of Birth' => $student->dob?->format('F j, Y') ?: '—',
                                        'Class' => $student->schoolClass?->name ?: '—',
                                        'Section' => $student->section?->name ?: '—',
                                    ] as $label => $value)
                                        <div>
                                            <div class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</div>
                                            <div class="mt-1 text-base font-semibold text-slate-800">{{ $value }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Enrolled Subjects -->
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-50 pb-2 mb-4">Enrolled Subjects</h3>
                                @if($student->schoolClass && $student->schoolClass->subjects->count() > 0)
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        @foreach($student->schoolClass->subjects as $subject)
                                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-white text-violet-600 shadow-sm ring-1 ring-slate-200">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-bold text-slate-800">{{ $subject->name }}</div>
                                                    <div class="truncate text-xs font-medium text-slate-400">{{ $subject->code }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-sm font-medium text-slate-500">
                                        No subjects assigned to this class yet.
                                    </div>
                                @endif
                            </div>

                            <!-- Guardian Information -->
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-50 pb-2 mb-4">Guardian Information</h3>
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Name</div>
                                        <div class="mt-1 text-base font-semibold text-slate-800">{{ $student->guardian_name ?: '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Phone</div>
                                        <div class="mt-1 text-base font-semibold text-slate-800">{{ $student->guardian_phone ?: '—' }}</div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Address</div>
                                        <div class="mt-1 text-base font-semibold text-slate-800">{{ $student->guardian_address ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                """

content = profile_old_regex.sub(profile_new, content)

# 2. Attendance Top Card removal
attendance_top_card = """<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Attendance</div>
                            <div class="mt-1 text-sm text-slate-500">History for this student (latest 30 marks).</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('attendance') }}" class="btn-primary">Open Attendance</a>
                        </div>
                    </div>
                </div>"""
attendance_replacement = """<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-lg font-bold text-slate-800">Attendance</div>
                        <div class="text-sm text-slate-500">History for this student (latest 30 marks).</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('attendance') }}" class="btn-primary">Open Attendance</a>
                    </div>
                </div>"""
content = content.replace(attendance_top_card, attendance_replacement)

# 3. Results Top Card
results_top_card = """<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Results</div>
                            <div class="mt-1 text-sm text-slate-500">Scores for this student across sessions/terms.</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('results.entry') }}" class="btn-primary">Enter Scores</a>
                            <a href="{{ route('results.report-card', $student) }}" class="btn-outline">Download Report Card</a>
                        </div>
                    </div>
                </div>"""
results_replacement = """<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-lg font-bold text-slate-800">Results</div>
                        <div class="text-sm text-slate-500">Scores for this student across sessions/terms.</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('results.entry') }}" class="btn-primary">Enter Scores</a>
                        <a href="{{ route('results.report-card', $student) }}" class="btn-outline">Download Report Card</a>
                    </div>
                </div>"""
content = content.replace(results_top_card, results_replacement)

# 4. Finance Top Card
finance_top_card = """<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Finance</div>
                            <div class="mt-1 text-sm text-slate-500">Recent payments and transaction history.</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('billing.index') }}" class="btn-primary">Open Billing</a>
                        </div>
                    </div>
                </div>"""
finance_replacement = """<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="text-lg font-bold text-slate-800">Finance</div>
                        <div class="text-sm text-slate-500">Recent payments and transaction history.</div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('billing.index') }}" class="btn-primary">Open Billing</a>
                    </div>
                </div>"""
content = content.replace(finance_top_card, finance_replacement)

# 5. Analytics Top Card
analytics_top_card = """<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="text-sm font-semibold text-slate-800">Performance Analytics</div>
                    <div class="mt-1 text-sm text-slate-500">Comprehensive performance tracking and insights.</div>
                </div>"""
analytics_replacement = """<div>
                    <div class="text-lg font-bold text-slate-800">Performance Analytics</div>
                    <div class="text-sm text-slate-500">Comprehensive performance tracking and insights.</div>
                </div>"""
content = content.replace(analytics_top_card, analytics_replacement)


with open('resources/views/pages/students/show.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
