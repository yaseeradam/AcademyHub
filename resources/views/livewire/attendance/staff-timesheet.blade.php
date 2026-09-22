<div class="space-y-6">
    {{-- Page Header --}}
    <div class="sm:flex sm:items-center sm:justify-between pb-5 border-b border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendance.teachers') }}" class="text-sm text-slate-500 hover:text-indigo-600 dark:text-slate-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Daily Staff Sheet
                </a>
                <span class="text-slate-300 dark:text-slate-600">/</span>
                <span class="text-sm font-semibold text-slate-800 dark:text-white">Monthly Timesheet</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-1">Staff Timesheet & Payroll Attendance</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Monthly biometric punch aggregation, shift compliance, and punctuality scoring for payroll & bursar.
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2">
            <a href="{{ route('biometrics.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                Hardware Gate Monitor
            </a>
            <button wire:click="exportCsv" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export for Payroll (CSV)
            </button>
        </div>
    </div>

    {{-- Filters & Month Selector --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Select Month</label>
                <input type="month" wire:model.live="selectedMonth" class="w-full text-sm font-medium border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Section / Shift</label>
                <select wire:model.live="sectionFilter" class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
                    <option value="all">All Sections (Western & Islamic)</option>
                    <option value="Western">Western Section (7:00 AM – 12:30 PM)</option>
                    <option value="Islamic">Islamic Section (12:30 PM – 5:00 PM)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Search Staff</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name..." class="w-full text-sm border-slate-300 dark:border-slate-600 rounded-lg dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Working Days This Month</label>
                <div class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-1">
                    <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-md text-sm font-semibold">
                        {{ $this->workingDaysCount }} Recorded Days
                    </span>
                </div>
            </div>
        </div>

        {{-- Deduction Rate Inputs (Collapsible or Inline) --}}
        <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex flex-wrap items-center gap-6 text-xs text-slate-600 dark:text-slate-300">
            <span class="font-semibold text-slate-700 dark:text-slate-200">Payroll Deduction Rules:</span>
            <div class="flex items-center gap-2">
                <span>Late Rate (₦/min):</span>
                <input type="number" step="5" min="0" wire:model.live.debounce.500ms="deductionPerMinute" class="w-20 text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded dark:bg-slate-700 dark:text-white">
            </div>
            <div class="flex items-center gap-2">
                <span>Unexcused Absence (₦/day):</span>
                <input type="number" step="100" min="0" wire:model.live.debounce.500ms="deductionPerAbsent" class="w-24 text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded dark:bg-slate-700 dark:text-white">
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    @php
        $totalStaff = count($timesheets);
        $avgAttendance = $totalStaff > 0 ? round(collect($timesheets)->avg('attendance_rate'), 1) : 0;
        $totalLateMins = collect($timesheets)->sum('total_late_minutes');
        $totalDeductions = collect($timesheets)->sum('total_deduction');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total Staff Evaluated</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalStaff }}</div>
            <div class="text-xs text-slate-500 mt-1">Teachers & Administrative staff</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Average Attendance</div>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $avgAttendance }}%</div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full mt-2 overflow-hidden">
                <div class="bg-indigo-600 h-full rounded-full" style="width: {{ min(100, $avgAttendance) }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total Late Minutes</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalLateMins) }} mins</div>
            <div class="text-xs text-slate-500 mt-1">Beyond shift thresholds</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total Suggested Deductions</div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">₦{{ number_format($totalDeductions, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Late minutes + unexcused absence</div>
        </div>
    </div>

    {{-- Main Timesheet Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Monthly Staff Attendance Register</h2>
            <span class="text-xs text-slate-500">Dual-shift aware: Western (late > 8:15 AM), Islamic (late > 12:45 PM)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/40 text-xs uppercase font-semibold text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3">Staff Member</th>
                        <th class="px-3 py-3">Section / Shift</th>
                        <th class="px-3 py-3 text-center">Work Days</th>
                        <th class="px-3 py-3 text-center text-emerald-600 dark:text-emerald-400">Present</th>
                        <th class="px-3 py-3 text-center text-amber-600 dark:text-amber-400">Late</th>
                        <th class="px-3 py-3 text-center text-rose-600 dark:text-rose-400">Absent</th>
                        <th class="px-3 py-3 text-center">Late (Mins)</th>
                        <th class="px-3 py-3">Attendance %</th>
                        <th class="px-3 py-3 text-right">Suggested Deduction</th>
                        <th class="px-3 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-slate-700 dark:text-slate-200">
                    @forelse($timesheets as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $row['name'] }}</div>
                                <div class="text-xs text-slate-400 flex items-center gap-2">
                                    <span>{{ $row['email'] }}</span>
                                    @if($row['k40_uid'])
                                        <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-[10px] font-mono text-slate-600 dark:text-slate-300">K40 #{{ $row['k40_uid'] }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                @if($row['shift'] === 'Islamic')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Islamic Section
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                        Western Section
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center font-medium">{{ $row['work_days'] }}</td>
                            <td class="px-3 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400">{{ $row['present_days'] }}</td>
                            <td class="px-3 py-3 text-center font-bold text-amber-600 dark:text-amber-400">{{ $row['late_days'] }}</td>
                            <td class="px-3 py-3 text-center font-bold text-rose-600 dark:text-rose-400">{{ $row['absent_days'] }}</td>
                            <td class="px-3 py-3 text-center font-mono text-xs">
                                @if($row['total_late_minutes'] > 0)
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $row['total_late_minutes'] }}m</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold {{ $row['attendance_rate'] >= 80 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                        {{ $row['attendance_rate'] }}%
                                    </span>
                                    <div class="w-16 bg-slate-100 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="{{ $row['attendance_rate'] >= 80 ? 'bg-emerald-500' : 'bg-amber-500' }} h-full rounded-full" style="width: {{ $row['attendance_rate'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-right font-mono font-semibold {{ $row['total_deduction'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                                ₦{{ number_format($row['total_deduction'], 2) }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button wire:click="openBreakdown({{ $row['id'] }})" type="button" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded dark:bg-indigo-900/30 dark:text-indigo-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Daily Log
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                No staff attendance records found for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daily Breakdown Modal --}}
    @if($showBreakdownModal && $this->selectedTeacherBreakdown)
        @php
            $modalData = $this->selectedTeacherBreakdown;
            $teacher = $modalData['teacher'];
        @endphp
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="closeBreakdown" class="fixed inset-0 bg-slate-900/60 transition-opacity" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $teacher->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $teacher->getShiftLabel() }} &bull; {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }} Breakdown</p>
                        </div>
                        <button wire:click="closeBreakdown" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 max-h-[65vh] overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-700/50 uppercase font-semibold text-slate-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-3 py-2 text-left">Date</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-center text-emerald-600 dark:text-emerald-400">Sign-In</th>
                                    <th class="px-3 py-2 text-center text-rose-600 dark:text-rose-400">Sign-Out</th>
                                    <th class="px-3 py-2 text-right">Recorded At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40 text-slate-700 dark:text-slate-300">
                                @foreach($modalData['logs'] as $log)
                                    <tr>
                                        <td class="px-3 py-2.5 font-medium">{{ $log['date'] }}</td>
                                        <td class="px-3 py-2.5">
                                            @if($log['status'] === 'Present')
                                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Present</span>
                                            @elseif($log['status'] === 'Late')
                                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Late</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">{{ $log['status'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-center font-mono text-[11px]">
                                            @if($log['punch_in'])
                                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $log['punch_in'] }}</span>
                                            @else
                                                <span class="text-slate-300 dark:text-slate-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-center font-mono text-[11px]">
                                            @if($log['punch_out'])
                                                <span class="text-rose-500 dark:text-rose-400 font-semibold">{{ $log['punch_out'] }}</span>
                                            @else
                                                <span class="text-slate-300 dark:text-slate-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-right text-slate-400">{{ $log['updated'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-3 bg-slate-50 dark:bg-slate-700/30 text-right border-t border-slate-200 dark:border-slate-700">
                        <button wire:click="closeBreakdown" class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-600">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
