<div
    class="space-y-6"
    x-data="{
        // ── Keyboard shortcuts ──
        onKeydown(e) {
            const isTyping = (el) => {
                if (!el) return false;
                const tag = (el.tagName || '').toLowerCase();
                return tag === 'input' || tag === 'textarea' || tag === 'select' || el.isContentEditable;
            };

            const key = (e.key || '').toLowerCase();

            if ((e.ctrlKey || e.metaKey) && key === 's') {
                e.preventDefault();
                this.$wire.save();
                return;
            }

            if (isTyping(e.target)) {
                if (key === 'escape') e.target.blur();
                return;
            }

            if (key === '/') {
                e.preventDefault();
                const search = document.getElementById('teacherAttendanceSearch');
                if (search) search.focus();
                return;
            }

            if (key === 'escape') {
                this.$wire.set('search', '');
                this.$wire.set('onlyExceptions', false);
                return;
            }

            if (key === 'a') this.$wire.setTool('Absent');
            if (key === 'l') this.$wire.setTool('Late');
            if (key === 'e') this.$wire.setTool('Excused');
            if (key === 'p') this.$wire.setTool('Present');
        },
    }"
    @keydown.window="onKeydown($event)"
>
    {{-- Header & Action Bar --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-2 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Staff Console
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs font-semibold text-slate-500">
                    {{ \Carbon\Carbon::parse($this->date ?: now())->format('l, F j, Y') }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                Teacher &amp; Staff Attendance
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Take, monitor, and audit daily attendance records for teaching and administrative personnel.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Mode Switcher Tabs --}}
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/80 shadow-2xs">
                <button type="button" wire:click="$set('viewMode', 'sheet')"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'sheet' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Daily Sheet
                </button>
                <button type="button" wire:click="$set('viewMode', 'summary')"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'summary' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Term Audit
                </button>
            </div>

            {{-- Monthly Timesheet Link --}}
            <a href="{{ route('attendance.staff-timesheet') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 border border-slate-200 hover:bg-slate-50 hover:text-slate-900 shadow-2xs transition-all active:scale-95">
                <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Monthly Timesheet</span>
            </a>

            {{-- Export CSV --}}
            <button type="button" wire:click="exportCsv"
                class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-slate-700 border border-slate-200 hover:bg-slate-50 hover:text-slate-900 shadow-2xs transition-all active:scale-95"
                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV</span>
            </button>

            {{-- Student Attendance --}}
            <a href="{{ route('attendance') }}"
                class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-all active:scale-95">
                <span>Student Registry &rarr;</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="rounded-2xl bg-white p-4 sm:p-5 shadow-xs border border-slate-200/90">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Date --}}
            <div class="space-y-1">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Attendance Date</label>
                <div class="relative">
                    <input wire:model.live="date" type="date"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-3 focus:ring-amber-500/15 font-semibold text-slate-800 text-sm bg-slate-50/50 transition-all" />
                </div>
            </div>

            {{-- Term --}}
            <div class="space-y-1">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Academic Term</label>
                <div class="relative">
                    <select wire:model.live="term"
                        class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-3 focus:ring-amber-500/15 font-semibold text-slate-800 text-sm bg-slate-50/50 transition-all appearance-none">
                        <option value="1">Term 1 (First Term)</option>
                        <option value="2">Term 2 (Second Term)</option>
                        <option value="3">Term 3 (Third Term)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Session --}}
            <div class="space-y-1">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Academic Session</label>
                <input wire:model.live="session" type="text" placeholder="e.g. 2026/2027"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-3 focus:ring-amber-500/15 font-semibold text-slate-800 text-sm bg-slate-50/50 transition-all" />
            </div>
        </div>
    </div>

    {{-- Modern KPI Stat Cards --}}
    @php
        $totalTeachersCount = $this->teachers->count();
        $presentCount = $this->markCounts['Present'] ?? 0;
        $absentCount = $this->markCounts['Absent'] ?? 0;
        $lateCount = $this->markCounts['Late'] ?? 0;
        $excusedCount = $this->markCounts['Excused'] ?? 0;
        $unmarkedCount = $this->markCounts['Unmarked'] ?? 0;

        $presentRate = $totalTeachersCount > 0 ? round(($presentCount / $totalTeachersCount) * 100) : 0;
        $absentRate = $totalTeachersCount > 0 ? round(($absentCount / $totalTeachersCount) * 100) : 0;
        $lateRate = $totalTeachersCount > 0 ? round(($lateCount / $totalTeachersCount) * 100) : 0;
        $excusedRate = $totalTeachersCount > 0 ? round(($excusedCount / $totalTeachersCount) * 100) : 0;
        $unmarkedRate = $totalTeachersCount > 0 ? round(($unmarkedCount / $totalTeachersCount) * 100) : 0;
    @endphp

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        {{-- Total Active Teachers --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-xs border border-slate-200 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Staff</span>
                <span class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900 tracking-tight">{{ $totalTeachersCount }}</div>
            <div class="mt-1 text-[11px] font-medium text-slate-400">Registered staff</div>
        </div>

        {{-- Present --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-xs border border-emerald-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Present</span>
                <span class="h-6 px-2 rounded-lg bg-emerald-50 text-[10px] font-black text-emerald-700 flex items-center justify-center">
                    {{ $presentRate }}%
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-700 tracking-tight">{{ $presentCount }}</div>
            <div class="mt-1 flex items-center gap-1.5">
                <div class="h-1.5 w-full bg-emerald-50 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-300" style="width: {{ $presentRate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Absent --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-xs border border-rose-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Absent</span>
                <span class="h-6 px-2 rounded-lg bg-rose-50 text-[10px] font-black text-rose-700 flex items-center justify-center">
                    {{ $absentRate }}%
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-rose-700 tracking-tight">{{ $absentCount }}</div>
            <div class="mt-1 flex items-center gap-1.5">
                <div class="h-1.5 w-full bg-rose-50 rounded-full overflow-hidden">
                    <div class="h-full bg-rose-500 rounded-full transition-all duration-300" style="width: {{ $absentRate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Late --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-xs border border-amber-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Late</span>
                <span class="h-6 px-2 rounded-lg bg-amber-50 text-[10px] font-black text-amber-700 flex items-center justify-center">
                    {{ $lateRate }}%
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-700 tracking-tight">{{ $lateCount }}</div>
            <div class="mt-1 flex items-center gap-1.5">
                <div class="h-1.5 w-full bg-amber-50 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full transition-all duration-300" style="width: {{ $lateRate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Excused --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-xs border border-purple-100 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Excused</span>
                <span class="h-6 px-2 rounded-lg bg-purple-50 text-[10px] font-black text-purple-700 flex items-center justify-center">
                    {{ $excusedRate }}%
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-purple-700 tracking-tight">{{ $excusedCount }}</div>
            <div class="mt-1 flex items-center gap-1.5">
                <div class="h-1.5 w-full bg-purple-50 rounded-full overflow-hidden">
                    <div class="h-full bg-purple-500 rounded-full transition-all duration-300" style="width: {{ $excusedRate }}%"></div>
                </div>
            </div>
        </div>

        {{-- Unmarked --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-4 shadow-xs border border-slate-200 transition-all hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Unmarked</span>
                <span class="h-6 px-2 rounded-lg bg-slate-100 text-[10px] font-black text-slate-600 flex items-center justify-center">
                    {{ $unmarkedRate }}%
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-800 tracking-tight">{{ $unmarkedCount }}</div>
            <div class="mt-1 flex items-center gap-1.5">
                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-slate-400 rounded-full transition-all duration-300" style="width: {{ $unmarkedRate }}%"></div>
                </div>
            </div>
        </div>
    </div>

    @if($viewMode === 'sheet')
        {{-- Daily Sheet Mode --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Quick Controls Sidebar (4 cols) --}}
            <div class="space-y-4 lg:col-span-4">
                <div class="rounded-2xl bg-white p-5 shadow-xs border border-slate-200/90 space-y-5 lg:sticky lg:top-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Marking Controls</h3>
                            <p class="text-xs text-slate-400">Click any teacher card to apply active tool</p>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider {{ $sheetId ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            {{ $sheetId ? 'Saved' : 'Auto-Sync' }}
                        </span>
                    </div>

                    {{-- Tool Selection Grid --}}
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-2">
                            Active Tap Tool
                        </label>
                        @php
                            $activeTool = $tool ?? 'Absent';
                        @endphp

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="setTool('Present')"
                                class="flex items-center justify-between rounded-xl px-3 py-2.5 text-xs font-bold border transition-all {{ $activeTool === 'Present' ? 'bg-emerald-600 text-white border-emerald-700 shadow-sm ring-2 ring-emerald-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $activeTool === 'Present' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                    Present
                                </span>
                                <span class="text-[10px] opacity-75 font-mono">(P)</span>
                            </button>

                            <button type="button" wire:click="setTool('Absent')"
                                class="flex items-center justify-between rounded-xl px-3 py-2.5 text-xs font-bold border transition-all {{ $activeTool === 'Absent' ? 'bg-rose-600 text-white border-rose-700 shadow-sm ring-2 ring-rose-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $activeTool === 'Absent' ? 'bg-white' : 'bg-rose-500' }}"></span>
                                    Absent
                                </span>
                                <span class="text-[10px] opacity-75 font-mono">(A)</span>
                            </button>

                            <button type="button" wire:click="setTool('Late')"
                                class="flex items-center justify-between rounded-xl px-3 py-2.5 text-xs font-bold border transition-all {{ $activeTool === 'Late' ? 'bg-amber-600 text-white border-amber-700 shadow-sm ring-2 ring-amber-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $activeTool === 'Late' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                    Late
                                </span>
                                <span class="text-[10px] opacity-75 font-mono">(L)</span>
                            </button>

                            <button type="button" wire:click="setTool('Excused')"
                                class="flex items-center justify-between rounded-xl px-3 py-2.5 text-xs font-bold border transition-all {{ $activeTool === 'Excused' ? 'bg-purple-600 text-white border-purple-700 shadow-sm ring-2 ring-purple-500/20' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full {{ $activeTool === 'Excused' ? 'bg-white' : 'bg-purple-500' }}"></span>
                                    Excused
                                </span>
                                <span class="text-[10px] opacity-75 font-mono">(E)</span>
                            </button>
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Search Staff</label>
                        <div class="relative">
                            <input id="teacherAttendanceSearch" wire:model.live.debounce.200ms="search" type="text"
                                placeholder="Search by name or email (/)..."
                                class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-3 focus:ring-amber-500/15 font-medium text-slate-800 bg-slate-50/50 transition-all" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Exceptions Checkbox --}}
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 cursor-pointer select-none transition-colors border border-slate-200/60">
                        <input type="checkbox" wire:model.live="onlyExceptions" class="h-4 w-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300" />
                        <span class="text-xs font-bold text-slate-700">Show Exceptions Only (Absent/Late/Excused)</span>
                    </label>

                    {{-- Bulk Actions --}}
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Quick Bulk Actions</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="markAll('Present')"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100/80 py-2.5 px-3 text-xs font-bold text-emerald-800 border border-emerald-200 transition-all active:scale-95"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>All Present</span>
                            </button>
                            <button type="button" wire:click="markAll('Absent')"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-rose-50 hover:bg-rose-100/80 py-2.5 px-3 text-xs font-bold text-rose-800 border border-rose-200 transition-all active:scale-95"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                <svg class="h-3.5 w-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                <span>All Absent</span>
                            </button>
                        </div>

                        <button type="button" wire:click="save"
                            class="w-full mt-1 inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 py-3 text-sm font-extrabold text-white shadow-md shadow-amber-500/20 hover:shadow-lg transition-all active:scale-95"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span wire:loading.remove wire:target="save">Commit &amp; Save Sheet</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>

                    {{-- Keyboard Shortcuts helper --}}
                    <div class="rounded-xl bg-slate-50 p-3 text-[11px] font-medium text-slate-500 space-y-1.5 border border-slate-200/60">
                        <div class="font-bold text-slate-700 flex items-center justify-between">
                            <span>Keyboard Shortcuts</span>
                            <span class="text-[9px] uppercase tracking-wider text-slate-400">Pro Tip</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[10px]">P</kbd> Present</span>
                            <span><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[10px]">A</kbd> Absent</span>
                            <span><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[10px]">L</kbd> Late</span>
                            <span><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[10px]">E</kbd> Excused</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Teachers List (8 cols) --}}
            <div class="space-y-4 lg:col-span-8">
                {{-- Shift Section Filter Bar & Teacher Counter --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="text-xs font-extrabold text-slate-900 flex items-center gap-2">
                        <span>Staff Members</span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-[11px] font-bold text-slate-600">
                            {{ $this->visibleTeachers->count() }} of {{ $totalTeachersCount }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
                            <button type="button" wire:click="$set('sectionFilter', 'all')"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                                All Shifts
                            </button>
                            <button type="button" wire:click="$set('sectionFilter', 'Western')"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'Western' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                                Western (8:00 AM)
                            </button>
                            <button type="button" wire:click="$set('sectionFilter', 'Islamic')"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'Islamic' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                                Islamic (12:30 PM)
                            </button>
                        </div>

                        <a href="{{ route('settings.attendance') }}" title="Configure Shift Parameters"
                            class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 text-xs transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Teacher Cards Stream --}}
                <div class="space-y-3">
                    @forelse ($this->visibleTeachers as $teacher)
                        @php
                            $status = $marks[$teacher->id]['status'] ?? 'Unmarked';
                            $note = $marks[$teacher->id]['note'] ?? null;

                            $statusConfig = [
                                'Present' => [
                                    'card' => 'border-emerald-200/90 bg-emerald-50/20 hover:border-emerald-300',
                                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'indicator' => 'bg-emerald-500',
                                ],
                                'Absent' => [
                                    'card' => 'border-rose-200/90 bg-rose-50/20 hover:border-rose-300',
                                    'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'indicator' => 'bg-rose-500',
                                ],
                                'Late' => [
                                    'card' => 'border-amber-200/90 bg-amber-50/20 hover:border-amber-300',
                                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'indicator' => 'bg-amber-500',
                                ],
                                'Excused' => [
                                    'card' => 'border-purple-200/90 bg-purple-50/20 hover:border-purple-300',
                                    'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'indicator' => 'bg-purple-500',
                                ],
                                'Unmarked' => [
                                    'card' => 'border-slate-200 bg-white hover:border-slate-300',
                                    'badge' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    'indicator' => 'bg-slate-300',
                                ],
                            ][$status] ?? [
                                'card' => 'border-slate-200 bg-white hover:border-slate-300',
                                'badge' => 'bg-slate-100 text-slate-600 border-slate-200',
                                'indicator' => 'bg-slate-300',
                            ];
                        @endphp

                        <div wire:key="teacher-row-{{ $teacher->id }}"
                            wire:click="applyTool({{ $teacher->id }})"
                            class="group relative rounded-2xl border p-4 shadow-2xs transition-all duration-150 cursor-pointer {{ $statusConfig['card'] }}">
                            
                            {{-- Top Row: Avatar + Info + Segmented Buttons --}}
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                {{-- Teacher Info --}}
                                <div class="flex items-center gap-3.5 min-w-0 flex-1">
                                    {{-- Avatar --}}
                                    <div class="relative flex-shrink-0">
                                        @if($teacher->profile_photo)
                                            <img src="{{ $teacher->profile_photo_url }}" alt="{{ $teacher->name }}"
                                                class="h-11 w-11 rounded-2xl object-cover ring-1 ring-slate-200 shadow-2xs" />
                                        @else
                                            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-sm font-black text-amber-700 ring-1 ring-inset ring-amber-200/80 shadow-2xs">
                                                {{ mb_substr($teacher->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-white {{ $statusConfig['indicator'] }}"></span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="truncate text-sm font-extrabold text-slate-900 group-hover:text-amber-700 transition-colors">
                                                {{ $teacher->name }}
                                            </span>

                                            {{-- Shift Badge --}}
                                            @if($teacher->getShift() === 'Islamic')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Islamic Shift
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200">
                                                    Western Shift
                                                </span>
                                            @endif

                                            @if($teacher->role !== 'teacher')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    {{ ucfirst($teacher->role) }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="truncate text-xs font-medium text-slate-400 mt-0.5">
                                            {{ $teacher->email }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Segmented Controls & Status Cycle Button --}}
                                <div class="flex items-center gap-2 flex-shrink-0" @click.stop>
                                    {{-- Segmented 1-Click P / A / L / E --}}
                                    <div class="inline-flex rounded-xl bg-slate-100/90 p-1 border border-slate-200 shadow-inner">
                                        <button type="button" wire:click="setMark({{ $teacher->id }}, 'Present')"
                                            class="inline-flex items-center justify-center rounded-lg h-7 w-7 text-xs font-black transition-all {{ $status === 'Present' ? 'bg-emerald-600 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/60' }}"
                                            title="Mark Present">
                                            P
                                        </button>
                                        <button type="button" wire:click="setMark({{ $teacher->id }}, 'Absent')"
                                            class="inline-flex items-center justify-center rounded-lg h-7 w-7 text-xs font-black transition-all {{ $status === 'Absent' ? 'bg-rose-600 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/60' }}"
                                            title="Mark Absent">
                                            A
                                        </button>
                                        <button type="button" wire:click="setMark({{ $teacher->id }}, 'Late')"
                                            class="inline-flex items-center justify-center rounded-lg h-7 w-7 text-xs font-black transition-all {{ $status === 'Late' ? 'bg-amber-600 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/60' }}"
                                            title="Mark Late">
                                            L
                                        </button>
                                        <button type="button" wire:click="setMark({{ $teacher->id }}, 'Excused')"
                                            class="inline-flex items-center justify-center rounded-lg h-7 w-7 text-xs font-black transition-all {{ $status === 'Excused' ? 'bg-purple-600 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/60' }}"
                                            title="Mark Excused">
                                            E
                                        </button>
                                    </div>

                                    {{-- Status Capsule Button --}}
                                    <button type="button" wire:click="cycleStatus({{ $teacher->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold border transition-all hover:scale-102 active:scale-95 shadow-2xs {{ $statusConfig['badge'] }}"
                                        title="Click to cycle status">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusConfig['indicator'] }}"></span>
                                        <span>{{ $status }}</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Bottom Row: Note Input / Biometric Punch Info --}}
                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center gap-2" @click.stop>
                                <div class="relative flex-1">
                                    <input type="text"
                                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/15 font-medium text-slate-700 bg-slate-50/70 transition-all placeholder:text-slate-400"
                                        placeholder="Add note for {{ $teacher->name }} (e.g. sick leave, late due to traffic)..."
                                        wire:model.live.debounce.400ms="marks.{{ $teacher->id }}.note" />
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl bg-white p-12 text-center border border-slate-200/80 shadow-xs">
                            <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="text-sm font-bold text-slate-800">No staff matching current filters</div>
                            <p class="mt-1 text-xs text-slate-400">Try clearing search terms or shift filters to view all staff members.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        {{-- Term Summary & Audit Leaderboard Mode --}}
        <div class="rounded-2xl bg-white shadow-xs border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Term Attendance Audit Log</h3>
                    <p class="text-xs text-slate-400">Staff attendance breakdown for {{ $session }} &mdash; Term {{ $term }}</p>
                </div>
                <button type="button" wire:click="exportCsv"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-slate-700 border border-slate-200 hover:bg-slate-50 shadow-2xs transition-all"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Audit CSV
                </button>
            </div>

            @if(count($this->teacherSummaries) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="px-6 py-3.5">Staff Member</th>
                                <th class="px-6 py-3.5 text-center">Sheets Recorded</th>
                                <th class="px-6 py-3.5 text-center text-emerald-600">Present</th>
                                <th class="px-6 py-3.5 text-center text-amber-600">Late</th>
                                <th class="px-6 py-3.5 text-center text-rose-600">Absent</th>
                                <th class="px-6 py-3.5 text-center text-purple-600">Excused</th>
                                <th class="px-6 py-3.5 text-right">Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold">
                            @foreach($this->teacherSummaries as $row)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($row['teacher']->profile_photo)
                                                <img src="{{ $row['teacher']->profile_photo_url }}" class="h-9 w-9 rounded-xl object-cover ring-1 ring-slate-200" />
                                            @else
                                                <div class="grid h-9 w-9 place-items-center rounded-xl bg-amber-50 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                                                    {{ mb_substr($row['teacher']->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $row['teacher']->name }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $row['teacher']->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-600">{{ $row['total_sheets'] }}</td>
                                    <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $row['present'] }}</td>
                                    <td class="px-6 py-4 text-center text-amber-600 font-bold">{{ $row['late'] }}</td>
                                    <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $row['absent'] }}</td>
                                    <td class="px-6 py-4 text-center text-purple-600 font-bold">{{ $row['excused'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2.5">
                                            <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, $row['attendance_rate']) }}%"></div>
                                            </div>
                                            <span class="font-extrabold text-slate-900">{{ $row['attendance_rate'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="text-sm font-bold text-slate-800">No attendance sheets recorded for this term yet</div>
                    <p class="mt-1 text-xs text-slate-400">Switch to Daily Sheet mode above to record attendance for today.</p>
                </div>
            @endif
        </div>
    @endif
</div>
