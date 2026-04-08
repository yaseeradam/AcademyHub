<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">My Attendance</h2>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-600">Total Days</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-600">Present</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['present'] }}</p>
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-600">Absent</p>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $stats['absent'] }}</p>
            </div>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-600">Attendance Rate</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['rate'] }}%</p>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <button wire:click="previousMonth" class="p-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <h3 class="text-lg font-bold text-gray-900">
                {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
            </h3>
            <button wire:click="nextMonth" class="p-2 rounded-lg hover:bg-gray-100 transition">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-7 gap-2">
            <!-- Day headers -->
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-center text-xs font-bold text-gray-600 py-2">{{ $day }}</div>
            @endforeach

            <!-- Calendar days -->
            @php
                $startDate = \Carbon\Carbon::create($selectedYear, $selectedMonth, 1);
                $endDate = $startDate->copy()->endOfMonth();
                $startDayOfWeek = $startDate->dayOfWeek;
                $daysInMonth = $startDate->daysInMonth;
            @endphp

            <!-- Empty cells before month starts -->
            @for($i = 0; $i < $startDayOfWeek; $i++)
                <div class="aspect-square"></div>
            @endfor

            <!-- Days of the month -->
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $currentDate = \Carbon\Carbon::create($selectedYear, $selectedMonth, $day);
                    $dateKey = $currentDate->format('Y-m-d');
                    $mark = $attendanceRecords->get($dateKey);
                    $isToday = $currentDate->isToday();
                @endphp
                <div class="aspect-square p-1">
                    <div class="h-full rounded-lg flex flex-col items-center justify-center text-sm
                        @if($isToday) ring-2 ring-blue-500 @endif
                        @if($mark)
                            @if($mark->status === 'Present') bg-green-100 text-green-800
                            @elseif($mark->status === 'Absent') bg-red-100 text-red-800
                            @elseif($mark->status === 'Late') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif
                        @else
                            bg-gray-50 text-gray-400
                        @endif">
                        <span class="font-semibold">{{ $day }}</span>
                        @if($mark)
                            <span class="text-xs mt-1">
                                @if($mark->status === 'Present') ✓
                                @elseif($mark->status === 'Absent') ✗
                                @elseif($mark->status === 'Late') ⏰
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            @endfor
        </div>

        <!-- Legend -->
        <div class="mt-6 flex flex-wrap gap-4 justify-center text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-100 border border-green-200"></div>
                <span class="text-gray-600">Present</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-100 border border-red-200"></div>
                <span class="text-gray-600">Absent</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-yellow-100 border border-yellow-200"></div>
                <span class="text-gray-600">Late</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-50 border border-gray-200"></div>
                <span class="text-gray-600">No Record</span>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    @if($attendanceRecords->isNotEmpty())
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900 mb-4">This Month's Records</h3>
            <div class="space-y-2">
                @foreach($attendanceRecords->sortByDesc(fn($mark) => $mark->sheet->date) as $mark)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center
                                @if($mark->status === 'Present') bg-green-100 text-green-600
                                @elseif($mark->status === 'Absent') bg-red-100 text-red-600
                                @elseif($mark->status === 'Late') bg-yellow-100 text-yellow-600
                                @endif">
                                @if($mark->status === 'Present')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @elseif($mark->status === 'Absent')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $mark->sheet->date->format('l, F d, Y') }}</p>
                                @if($mark->note)
                                    <p class="text-xs text-gray-600">{{ $mark->note }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="text-sm font-semibold
                            @if($mark->status === 'Present') text-green-600
                            @elseif($mark->status === 'Absent') text-red-600
                            @elseif($mark->status === 'Late') text-yellow-600
                            @endif">
                            {{ $mark->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
