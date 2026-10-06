<div class="space-y-6 font-sans">
    @php
        $isStaffScope = $scheduleScope === 'staff';
        $targetTitle = $isStaffScope ? ($this->selectedTeacher?->name ?? 'Select Staff Member') : ($this->selectedClass?->name ?? 'Select Class');
        $hasTarget = $isStaffScope ? (bool) $this->teacherFilterId : (bool) $this->classId;
        $totalPeriods = $entries->where('is_break', false)->count();
    @endphp

    {{-- ══════════════════════════════════════════════════════════════
         1. EXECUTIVE HEADER & CONTROLS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-[#17274E] to-[#1D3261] shadow-xl p-5 sm:p-7 text-white">
        {{-- Subtle radial dot grid --}}
        <div class="absolute inset-0 pointer-events-none opacity-30 mix-blend-screen bg-[radial-gradient(circle,#ffffff_1.5px,transparent_1.5px)]" style="background-size: 32px 32px;"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-300">Master Schedule System</span>
                    <span class="text-xs text-blue-200">&bull;</span>
                    <span class="text-xs font-semibold text-blue-200">{{ $isStaffScope ? 'Staff & Faculty Schedule' : 'Class Schedule' }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    {{ $hasTarget ? $targetTitle : 'Institutional Timetable' }}
                </h1>
                <p class="text-xs sm:text-sm text-blue-100 max-w-xl">
                    @if($hasTarget)
                        Managing weekly teaching periods, room allocations, and scheduled recess periods.
                    @else
                        Choose between class-wide or individual staff timetable scopes below.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Scope Switcher: Class vs Staff --}}
                @if(auth()->user()?->role !== 'parent')
                    <div class="inline-flex p-1 bg-white/10 rounded-xl border border-white/15 backdrop-blur-sm">
                        <button type="button" 
                                wire:click="$set('scheduleScope', 'class')" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ !$isStaffScope ? 'bg-white text-[#17274E] shadow-sm' : 'text-blue-100 hover:text-white' }}">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Class Schedule
                        </button>
                        <button type="button" 
                                wire:click="$set('scheduleScope', 'staff')" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $isStaffScope ? 'bg-white text-[#17274E] shadow-sm' : 'text-blue-100 hover:text-white' }}">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Staff Timetable
                        </button>
                    </div>
                @endif

                @if($classId && !$isStaffScope && auth()->user()?->role !== 'parent')
                    <a href="{{ route('timetable.pdf', ['class_id' => $classId]) }}" target="_blank" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl text-white bg-white/10 hover:bg-white/20 border border-white/15 backdrop-blur-sm transition-all shadow-sm">
                        <svg class="h-3.5 w-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        PDF Export
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         2. CONTROLS TOOLBAR & METRICS BAR
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            @if(!$isStaffScope)
                {{-- Class Selector --}}
                <div class="min-w-[200px]">
                    <select wire:model.live="classId" class="select text-xs sm:text-sm font-semibold w-full">
                        <option value="">Select Class ({{ collect($this->classes)->count() }})</option>
                        @foreach($this->classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                {{-- Teacher Selector --}}
                <div class="min-w-[220px]">
                    <select wire:model.live="teacherFilterId" class="select text-xs sm:text-sm font-semibold w-full">
                        <option value="">Select Teaching Staff ({{ collect($this->teachers)->count() }})</option>
                        @foreach($this->teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($hasTarget)
                {{-- View Toggle [Grid | Daily] --}}
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600">
                    <button type="button" wire:click="$set('viewMode', 'grid')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Weekly Grid
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'daily')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'daily' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Daily Agenda
                    </button>
                </div>
            @endif
        </div>

        @if($hasTarget)
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-200 dark:border-emerald-800">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $totalPeriods }} Scheduled Periods
                </span>
                <span class="text-slate-400">&bull;</span>
                <span class="text-slate-500 dark:text-slate-400 font-medium">Monday &ndash; {{ count($days) === 6 ? 'Saturday' : 'Friday' }}</span>
            </div>
        @endif
    </div>

    @if(!$hasTarget)
        {{-- Empty State: No Target Selected --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 mb-3">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">No {{ $isStaffScope ? 'Staff Member' : 'Class' }} Selected</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Please select a {{ $isStaffScope ? 'teaching staff member' : 'class' }} from the selector above to display their timetable schedule.</p>
        </div>
    @else
        @if($entries->isEmpty())
            {{-- Empty State: Target has no entries --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 mb-3">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">No Schedule Entries</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">This {{ $isStaffScope ? 'staff member' : 'class' }} does not have any scheduled periods or breaks yet.</p>
                @if($isAdmin && !$isStaffScope)
                    <div class="mt-4">
                        <button type="button" wire:click="selectSlot(1, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            + Add First Period
                        </button>
                    </div>
                @endif
            </div>
        @else
            @if($viewMode === 'grid')
                {{-- Clean & Modern Grid View --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse" style="min-width: 720px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider border-r border-slate-200 w-32">
                                        Time
                                    </th>
                                    @foreach($days as $d)
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-slate-700 uppercase tracking-wider border-r border-slate-200 last:border-r-0">
                                            {{ $d['label'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($timeSlots as $slotIndex => $slot)
                                    @php
                                        $allBreak = true;
                                        $breakText = null;
                                        $breakEntry = null;
                                        foreach ($days as $d) {
                                            $entry = $slotMap[$d['day']][$slot['key']] ?? null;
                                            if (!$entry || !$entry->is_break) {
                                                $allBreak = false;
                                                break;
                                            }
                                            if ($breakText === null) {
                                                $breakText = trim($entry->break_text ?? 'BREAK');
                                                $breakEntry = $entry;
                                            }
                                        }
                                    @endphp

                                    @if($allBreak && $breakText)
                                        {{-- Unified Break Row across all days --}}
                                        <tr class="bg-amber-50/50">
                                            <td class="px-4 py-3 text-center text-xs font-medium text-amber-900/80 border-r border-slate-200 bg-amber-50/70">
                                                {{ $slot['start'] }} – {{ $slot['end'] }}
                                            </td>
                                            <td colspan="{{ count($days) }}" class="px-4 py-2.5 text-center bg-amber-50/50">
                                                <div class="flex items-center justify-center gap-2">
                                                    <span class="inline-flex items-center gap-1.5 text-amber-800 font-bold text-xs uppercase tracking-wider">
                                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        {{ $breakText }}
                                                    </span>
                                                    @if($isAdmin && !$isStaffScope)
                                                        <button type="button" wire:click="edit({{ $breakEntry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-[11px] font-medium text-amber-700 hover:text-amber-900 underline ml-2">
                                                            Edit
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="hover:bg-slate-50/40 transition-colors">
                                            <td class="px-4 py-3 text-center text-xs font-medium text-slate-600 bg-slate-50/50 border-r border-slate-200 whitespace-nowrap">
                                                {{ $slot['start'] }} – {{ $slot['end'] }}
                                            </td>

                                            @foreach($days as $d)
                                                @php
                                                    $entry = $slotMap[$d['day']][$slot['key']] ?? null;
                                                @endphp

                                                <td class="p-1.5 border-r border-slate-200/80 last:border-r-0 align-top">
                                                    @if($entry)
                                                        @if($entry->is_break)
                                                            <div class="rounded-lg border border-amber-200 bg-amber-50/60 p-2 text-center">
                                                                <span class="inline-flex items-center justify-center gap-1 text-[11px] font-bold text-amber-800 uppercase tracking-wide">
                                                                    <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    {{ trim($entry->break_text ?? 'BREAK') }}
                                                                </span>
                                                                @if($isAdmin && !$isStaffScope)
                                                                    <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="block mx-auto mt-0.5 text-[10px] text-amber-700 hover:underline">
                                                                        Edit
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @else
                                                            @php
                                                                $c = $entry->color ?? 'slate';
                                                                $cardBorder = match($c) {
                                                                    'blue'    => 'border-l-blue-500 bg-blue-50/30',
                                                                    'indigo'  => 'border-l-indigo-500 bg-indigo-50/30',
                                                                    'violet'  => 'border-l-violet-500 bg-violet-50/30',
                                                                    'purple'  => 'border-l-purple-500 bg-purple-50/30',
                                                                    'pink'    => 'border-l-pink-500 bg-pink-50/30',
                                                                    'red'     => 'border-l-red-500 bg-red-50/30',
                                                                    'rose'    => 'border-l-rose-500 bg-rose-50/30',
                                                                    'orange'  => 'border-l-orange-500 bg-orange-50/30',
                                                                    'amber'   => 'border-l-amber-500 bg-amber-50/30',
                                                                    'yellow'  => 'border-l-yellow-400 bg-yellow-50/30',
                                                                    'green'   => 'border-l-green-500 bg-green-50/30',
                                                                    'emerald' => 'border-l-emerald-500 bg-emerald-50/30',
                                                                    'teal'    => 'border-l-teal-500 bg-teal-50/30',
                                                                    'cyan'    => 'border-l-cyan-500 bg-cyan-50/30',
                                                                    'sky'     => 'border-l-sky-500 bg-sky-50/30',
                                                                    default   => 'border-l-slate-400 bg-slate-50/50',
                                                                };
                                                            @endphp

                                                            @if($isAdmin && !$isStaffScope)
                                                                <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full text-left rounded-lg border border-slate-200 border-l-4 {{ $cardBorder }} p-2.5 transition hover:shadow-xs hover:border-slate-300">
                                                                    <div class="text-xs font-bold text-slate-800 truncate">{{ $entry->subject?->name ?? 'Untitled' }}</div>
                                                                    @if($isStaffScope)
                                                                        <div class="text-[11px] font-semibold text-blue-700 truncate mt-0.5">
                                                                            {{ $entry->schoolClass?->name ?? 'Class' }}{{ $entry->section ? ' · ' . $entry->section->name : '' }}
                                                                        </div>
                                                                    @else
                                                                        @if($entry->teacher?->name)
                                                                            <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ $entry->teacher->name }}</div>
                                                                        @endif
                                                                    @endif
                                                                    @if($entry->room)
                                                                        <div class="text-[10px] text-slate-400 truncate mt-0.5">Room {{ $entry->room }}</div>
                                                                    @endif
                                                                </button>
                                                            @else
                                                                <div class="w-full text-left rounded-lg border border-slate-200 border-l-4 {{ $cardBorder }} p-2.5">
                                                                    <div class="text-xs font-bold text-slate-800 truncate">{{ $entry->subject?->name ?? 'Untitled' }}</div>
                                                                    @if($isStaffScope)
                                                                        <div class="text-[11px] font-semibold text-blue-700 truncate mt-0.5">
                                                                            {{ $entry->schoolClass?->name ?? 'Class' }}{{ $entry->section ? ' · ' . $entry->section->name : '' }}
                                                                        </div>
                                                                    @else
                                                                        @if($entry->teacher?->name)
                                                                            <div class="text-[11px] text-slate-500 truncate mt-0.5">{{ $entry->teacher->name }}</div>
                                                                        @endif
                                                                    @endif
                                                                    @if($entry->room)
                                                                        <div class="text-[10px] text-slate-400 truncate mt-0.5">Room {{ $entry->room }}</div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if($isAdmin && !$isStaffScope)
                                                            <button type="button" wire:click="selectSlot({{ $d['day'] }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full h-full min-h-[52px] rounded-lg border border-dashed border-slate-200 hover:border-blue-400 hover:bg-blue-50/30 text-[11px] font-medium text-slate-300 hover:text-blue-600 transition flex items-center justify-center">
                                                                +
                                                            </button>
                                                        @else
                                                            <div class="w-full h-full min-h-[52px] flex items-center justify-center text-xs text-slate-200 font-light">
                                                                —
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- Clean & Modern Daily View --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm p-4 sm:p-6 space-y-5">
                    {{-- Day Tabs --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 border-b border-slate-100">
                        @foreach($days as $d)
                            <button type="button" wire:click="$set('activeDayTab', {{ $d['day'] }})" class="px-3.5 py-2 text-xs font-semibold rounded-lg transition whitespace-nowrap {{ $activeDayTab === $d['day'] ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                                {{ $d['label'] }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Day Entries --}}
                    @php
                        $dayEntries = $entries->where('day_of_week', $activeDayTab)->sortBy('starts_at');
                    @endphp

                    @if($dayEntries->isEmpty())
                        <div class="text-center py-10 text-slate-400">
                            <p class="text-xs sm:text-sm font-medium">No periods scheduled for {{ $this->dayLabel($activeDayTab) }}.</p>
                            @if($isAdmin && !$isStaffScope)
                                <button type="button" wire:click="selectSlot({{ $activeDayTab }}, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                    + Add Period
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="space-y-2.5">
                            @if($isAdmin && !$isStaffScope)
                                <div class="flex justify-end mb-2">
                                    <button type="button" wire:click="selectSlot({{ $activeDayTab }}, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                        + Add Period
                                    </button>
                                </div>
                            @endif

                            @foreach($dayEntries as $entry)
                                @if($entry->is_break)
                                    <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-bold text-amber-900 uppercase tracking-wide">{{ $entry->break_text ?? 'BREAK' }}</div>
                                                <div class="text-[11px] text-amber-700">{{ substr($entry->starts_at, 0, 5) }} – {{ substr($entry->ends_at, 0, 5) }}</div>
                                            </div>
                                        </div>
                                        @if($isAdmin && !$isStaffScope)
                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-semibold text-amber-800 hover:underline">
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    @php
                                        $c = $entry->color ?? 'slate';
                                        $cardBorder = match($c) {
                                            'blue'    => 'border-l-blue-500 bg-blue-50/20',
                                            'indigo'  => 'border-l-indigo-500 bg-indigo-50/20',
                                            'violet'  => 'border-l-violet-500 bg-violet-50/20',
                                            'purple'  => 'border-l-purple-500 bg-purple-50/20',
                                            'pink'    => 'border-l-pink-500 bg-pink-50/20',
                                            'red'     => 'border-l-red-500 bg-red-50/20',
                                            'rose'    => 'border-l-rose-500 bg-rose-50/20',
                                            'orange'  => 'border-l-orange-500 bg-orange-50/20',
                                            'amber'   => 'border-l-amber-500 bg-amber-50/20',
                                            'yellow'  => 'border-l-yellow-400 bg-yellow-50/20',
                                            'green'   => 'border-l-green-500 bg-green-50/20',
                                            'emerald' => 'border-l-emerald-500 bg-emerald-50/20',
                                            'teal'    => 'border-l-teal-500 bg-teal-50/20',
                                            'cyan'    => 'border-l-cyan-500 bg-cyan-50/20',
                                            'sky'     => 'border-l-sky-500 bg-sky-50/20',
                                            default   => 'border-l-slate-400 bg-slate-50/40',
                                        };
                                    @endphp
                                    <div class="flex items-center justify-between rounded-xl border border-slate-200 border-l-4 {{ $cardBorder }} px-4 py-3">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-6">
                                            <div class="w-28 text-xs font-semibold text-slate-500">
                                                {{ substr($entry->starts_at, 0, 5) }} – {{ substr($entry->ends_at, 0, 5) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-900">{{ $entry->subject?->name ?? 'Untitled' }}</div>
                                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-0.5">
                                                    @if($isStaffScope)
                                                        <span class="font-semibold text-blue-700">{{ $entry->schoolClass?->name ?? 'Class' }}{{ $entry->section ? ' (' . $entry->section->name . ')' : '' }}</span>
                                                    @else
                                                        @if($entry->teacher?->name)
                                                            <span>{{ $entry->teacher->name }}</span>
                                                        @endif
                                                    @endif
                                                    @if($entry->room)
                                                        <span class="text-slate-400">Room {{ $entry->room }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if($isAdmin && !$isStaffScope)
                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-semibold text-blue-600 hover:underline">
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        @endif
    @endif

    {{-- Admin Add/Edit Modal (Super Simple & Clean) --}}
    @if($isAdmin)
        <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'timetable-form') open = true" x-on:close.window="open = false" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div x-on:click="open = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>
                
                <div x-on:click.stop class="relative w-full max-w-md rounded-2xl bg-white p-5 sm:p-6 shadow-xl border border-slate-200/80">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">
                                @if($editingId)
                                    {{ $isBreak ? 'Edit Break' : 'Edit Period' }}
                                @else
                                    Add Timetable Entry
                                @endif
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $this->selectedClass?->name ?? 'Configure schedule' }}</p>
                        </div>
                        <button type="button" x-on:click="open = false" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="mt-4 space-y-4">
                        {{-- Type Selector: Subject vs Break --}}
                        <div class="grid grid-cols-2 p-1 bg-slate-100/80 rounded-xl text-xs font-semibold">
                            <button type="button" wire:click="$set('isBreak', false)" class="py-2 rounded-lg transition-all {{ !$isBreak ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                Class Subject
                            </button>
                            <button type="button" wire:click="$set('isBreak', true)" class="py-2 rounded-lg transition-all {{ $isBreak ? 'bg-white text-amber-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                Break / Recess
                            </button>
                        </div>

                        {{-- Day & Time Inputs --}}
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">Day</label>
                                <select wire:model.live="entryDay" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500">
                                    <option value="1">Mon</option>
                                    <option value="2">Tue</option>
                                    <option value="3">Wed</option>
                                    <option value="4">Thu</option>
                                    <option value="5">Fri</option>
                                    <option value="6">Sat</option>
                                </select>
                                @error('entryDay') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">Start Time</label>
                                <input wire:model="startsAt" type="time" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500">
                                @error('startsAt') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">End Time</label>
                                <input wire:model="endsAt" type="time" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500">
                                @error('endsAt') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if($isBreak)
                            {{-- Break Inputs --}}
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">Break Title</label>
                                <input wire:model="breakText" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500" placeholder="e.g. BREAK, Short Break, Lunch, Zuhr">
                                @error('breakText') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @foreach(['BREAK', 'Short Break', 'Lunch', 'Zuhr Prayer'] as $preset)
                                        <button type="button" wire:click="$set('breakText', '{{ $preset }}')" class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 hover:bg-slate-200 transition">
                                            + {{ $preset }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- Subject Input --}}
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600">Subject <span class="text-red-500">*</span></label>
                                <select wire:model.live="subjectId" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500">
                                    <option value="">Choose a subject...</option>
                                    @foreach($this->subjects as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                @error('subjectId') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                            </div>

                            {{-- Teacher & Room --}}
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="text-[11px] font-semibold text-slate-600">Teacher <span class="text-slate-400 font-normal">(Optional)</span></label>
                                    <select wire:model.live="teacherId" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500">
                                        <option value="">Any / None</option>
                                        @foreach($this->teachers as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('teacherId') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="text-[11px] font-semibold text-slate-600">Room <span class="text-slate-400 font-normal">(Optional)</span></label>
                                    <input wire:model="room" class="mt-1 w-full rounded-lg border border-slate-200 bg-slate-50/50 px-2.5 py-2 text-xs font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-1 focus:ring-blue-500" placeholder="e.g. Lab 1">
                                    @error('room') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Color Accent --}}
                            <div>
                                <label class="text-[11px] font-semibold text-slate-600 block mb-1.5">Color Accent</label>
                                <div class="flex items-center gap-2">
                                    @php
                                        $curatedColors = [
                                            'blue'    => 'bg-blue-500',
                                            'indigo'  => 'bg-indigo-500',
                                            'emerald' => 'bg-emerald-500',
                                            'amber'   => 'bg-amber-500',
                                            'purple'  => 'bg-purple-500',
                                            'rose'    => 'bg-rose-500',
                                            'slate'   => 'bg-slate-500',
                                        ];
                                    @endphp
                                    @foreach($curatedColors as $colorKey => $colorClass)
                                        <button type="button" wire:click="$set('color', '{{ $colorKey }}')" 
                                                class="h-6 w-6 rounded-full {{ $colorClass }} transition focus:outline-none {{ $color === $colorKey ? 'ring-2 ring-offset-2 ring-slate-800 scale-110' : 'opacity-70 hover:opacity-100' }}"
                                                title="{{ ucfirst($colorKey) }}"></button>
                                    @endforeach
                                </div>
                                @error('color') <div class="mt-1 text-[10px] text-red-600">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        @if(!$editingId)
                            <div class="pt-0.5">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" wire:model="applyToAllDays" class="h-3.5 w-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                    <span class="text-xs text-slate-600">Repeat this for all weekdays (Mon–Fri)</span>
                                </label>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3.5">
                        <div>
                            @if($editingId)
                                <button type="button" wire:click="delete({{ $editingId }})" x-on:click="open = false" onclick="return confirm('Delete this entry?')" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                                    Delete
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" x-on:click="open = false" class="rounded-lg border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                            <button type="button" wire:click="save" x-on:click="open = false" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition shadow-xs">
                                {{ $editingId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
