<div
    class="space-y-6"
    x-data="{
        // â”€â”€ Keyboard shortcuts â”€â”€
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
    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-3xl shadow-2xl" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-amber-500/10 via-transparent to-transparent"></div>
        <div class="absolute right-0 top-0 bottom-0 w-64 opacity-5 pointer-events-none">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <path d="M100 20C55.8 20 20 55.8 20 100C20 144.2 55.8 180 100 180C144.2 180 180 144.2 180 100C180 55.8 144.2 20 100 20Z" stroke="white" stroke-width="2"/>
                <path d="M100 40C66.9 40 40 66.9 40 100C40 133.1 66.9 160 100 160C133.1 160 160 133.1 160 100C160 66.9 133.1 40 100 40Z" stroke="white" stroke-width="1.5"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-6 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-300">Admin Staff Console</span>
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">Staff &amp; Teacher Attendance</h1>
                <p class="text-sm font-medium text-slate-300">Take, manage, and audit daily attendance records for teaching staff.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex rounded-xl bg-white/10 p-1 border border-white/10 backdrop-blur-md">
                    <button type="button" wire:click="$set('viewMode', 'sheet')"
                        class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'sheet' ? 'bg-amber-500 text-white shadow-md' : 'text-slate-300 hover:text-white' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Daily Sheet
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'summary')"
                        class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'summary' ? 'bg-amber-500 text-white shadow-md' : 'text-slate-300 hover:text-white' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Term Summary
                    </button>
                    <a href="{{ route('attendance.staff-timesheet') }}"
                        class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition-all text-amber-300 hover:text-white hover:bg-white/10 flex items-center gap-1">
                        Monthly Timesheet &rarr;
                    </a>
                </div>

                <button type="button" wire:click="exportCsv"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2.5 text-xs font-bold text-white border border-white/15 transition-all active:scale-95 shadow-sm" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>

                <a href="{{ route('attendance') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2.5 text-xs font-bold text-white border border-white/15 transition-all">
                    Student Registry &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="rounded-3xl bg-white p-6 shadow-md border border-slate-100">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            {{-- Date --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Attendance Date</label>
                <input wire:model.live="date" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
            </div>

            {{-- Term --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Term</label>
                <div class="relative">
                    <select wire:model.live="term" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Session --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Academic Session</label>
                <input wire:model.live="session" type="text" placeholder="e.g. 2026/2027" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
            </div>
        </div>
    </div>

    {{-- Real-Time KPI Stats Bar --}}
    @php
        $totalTeachersCount = $this->teachers->count();
    @endphp
    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-5">
        {{-- Total Active Teachers --}}
        <div class="relative overflow-hidden rounded-xl bg-slate-900 p-3.5 shadow-sm border border-slate-800 text-white">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Teachers</span>
                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[9px] font-bold text-slate-300">Staff</span>
            </div>
            <div class="mt-1.5 text-2xl font-extrabold">{{ $totalTeachersCount }}</div>
            <div class="text-[10px] font-medium text-slate-400">Registered teaching staff</div>
        </div>

        {{-- Present Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Present</span>
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-600">P</span>
            </div>
            <div class="mt-1.5 text-2xl font-extrabold text-slate-850">{{ $this->markCounts['Present'] ?? 0 }}</div>
            <div class="text-[10px] font-semibold text-emerald-500 flex items-center gap-1">
                <span>{{ $totalTeachersCount > 0 ? round((($this->markCounts['Present'] ?? 0) / $totalTeachersCount) * 100) : 0 }}%</span>
                <span class="text-slate-400 font-medium">presence rate</span>
            </div>
        </div>

        {{-- Absent Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Absent</span>
                <span class="rounded-full bg-red-50 px-2 py-0.5 text-[9px] font-bold text-red-600">A</span>
            </div>
            <div class="mt-1.5 text-2xl font-extrabold text-slate-850">{{ $this->markCounts['Absent'] ?? 0 }}</div>
            <div class="text-[10px] font-semibold text-red-500">
                <span>{{ $totalTeachersCount > 0 ? round((($this->markCounts['Absent'] ?? 0) / $totalTeachersCount) * 100) : 0 }}%</span>
                <span class="text-slate-400 font-medium">absence rate</span>
            </div>
        </div>

        {{-- Late Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Late</span>
                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600">L</span>
            </div>
            <div class="mt-1.5 text-2xl font-extrabold text-slate-850">{{ $this->markCounts['Late'] ?? 0 }}</div>
            <div class="text-[10px] font-semibold text-amber-500">
                <span>{{ $totalTeachersCount > 0 ? round((($this->markCounts['Late'] ?? 0) / $totalTeachersCount) * 100) : 0 }}%</span>
                <span class="text-slate-400 font-medium">tardiness rate</span>
            </div>
        </div>

        {{-- Excused Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
            <div class="absolute top-0 left-0 right-0 h-1 bg-purple-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Excused</span>
                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[9px] font-bold text-purple-600">E</span>
            </div>
            <div class="mt-1.5 text-2xl font-extrabold text-slate-850">{{ $this->markCounts['Excused'] ?? 0 }}</div>
            <div class="text-[10px] font-semibold text-purple-500">
                <span>{{ $totalTeachersCount > 0 ? round((($this->markCounts['Excused'] ?? 0) / $totalTeachersCount) * 100) : 0 }}%</span>
                <span class="text-slate-400 font-medium">excused rate</span>
            </div>
        </div>
    </div>

    @if($viewMode === 'sheet')
        {{-- Interactive Daily Sheet Mode --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Quick Controls Sidebar --}}
            <div class="space-y-4 lg:col-span-4">
                <div class="rounded-3xl bg-white p-6 shadow-md border border-slate-100 space-y-4 lg:sticky lg:top-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Marking Controls</h3>
                            <p class="text-xs text-slate-400">Select status tool &amp; click teacher</p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $sheetId ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' }}">
                            {{ $sheetId ? 'Sheet Saved' : 'New Draft' }}
                        </span>
                    </div>

                    {{-- Tool Selection Grid --}}
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Active Tap Tool</label>
                        @php
                            $activeTool = $tool ?? 'Absent';
                            $toolStyle = fn (string $name, string $activeClass) => $activeTool === $name ? $activeClass : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100';
                        @endphp

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="setTool('Present')"
                                class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-bold border transition-all {{ $toolStyle('Present', 'bg-emerald-500 text-white border-emerald-600 shadow-md scale-[1.02]') }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                <span>Present</span>
                                <span class="text-[10px] opacity-80">(P)</span>
                            </button>
                            <button type="button" wire:click="setTool('Absent')"
                                class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-bold border transition-all {{ $toolStyle('Absent', 'bg-red-500 text-white border-red-600 shadow-md scale-[1.02]') }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                <span>Absent</span>
                                <span class="text-[10px] opacity-80">(A)</span>
                            </button>
                            <button type="button" wire:click="setTool('Late')"
                                class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-bold border transition-all {{ $toolStyle('Late', 'bg-amber-500 text-white border-amber-600 shadow-md scale-[1.02]') }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                <span>Late</span>
                                <span class="text-[10px] opacity-80">(L)</span>
                            </button>
                            <button type="button" wire:click="setTool('Excused')"
                                class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-bold border transition-all {{ $toolStyle('Excused', 'bg-purple-500 text-white border-purple-600 shadow-md scale-[1.02]') }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                <span>Excused</span>
                                <span class="text-[10px] opacity-80">(E)</span>
                            </button>
                        </div>
                    </div>

                    {{-- Search Input --}}
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Search Teachers</label>
                        <div class="relative">
                            <input id="teacherAttendanceSearch" wire:model.live.debounce.200ms="search" type="text" placeholder="Search by name or email (/)..."
                                class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 font-medium text-slate-700 bg-slate-50/50" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" wire:model.live="onlyExceptions" class="h-4 w-4 rounded text-amber-600 focus:ring-amber-500/20 border-slate-300" />
                        <span class="text-xs font-bold text-slate-700">Show Exceptions Only</span>
                    </label>

                    {{-- Quick Bulk Actions --}}
                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="markAll('Present')" class="flex-1 rounded-xl bg-slate-100 hover:bg-slate-200 py-2 text-xs font-bold text-slate-700 transition-all border border-slate-200" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                Mark All Present
                            </button>
                            <button type="button" wire:click="markAll('Absent')" class="flex-1 rounded-xl bg-slate-100 hover:bg-slate-200 py-2 text-xs font-bold text-slate-700 transition-all border border-slate-200" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                Mark All Absent
                            </button>
                        </div>

                        <button type="button" wire:click="save" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 hover:bg-amber-600 py-3 text-sm font-extrabold text-white shadow-lg shadow-amber-500/25 hover:shadow-xl transition-all active:scale-95" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span wire:loading.remove wire:target="save">Commit &amp; Save Attendance</span>
                            <span wire:loading wire:target="save">Saving Sheet...</span>
                        </button>
                    </div>

                    {{-- Shortcuts Info --}}
                    <div class="rounded-xl bg-slate-50 p-3 text-[11px] font-medium text-slate-500 space-y-1">
                        <div class="font-bold text-slate-700">Keyboard Shortcuts</div>
                        <div><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border">P</kbd> Present &nbsp; <kbd class="font-mono bg-white px-1.5 py-0.5 rounded border">A</kbd> Absent</div>
                        <div><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border">L</kbd> Late &nbsp; <kbd class="font-mono bg-white px-1.5 py-0.5 rounded border">E</kbd> Excused</div>
                        <div><kbd class="font-mono bg-white px-1.5 py-0.5 rounded border">/</kbd> Search &nbsp; <kbd class="font-mono bg-white px-1.5 py-0.5 rounded border">Ctrl+S</kbd> Save</div>
                    </div>
                </div>
            </div>

            {{-- Main Teachers List --}}
            <div class="space-y-4 lg:col-span-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                    <div class="text-sm font-extrabold text-slate-900">
                        Teaching Staff <span class="text-slate-400 font-semibold">({{ $this->visibleTeachers->count() }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
                            <button type="button" wire:click="$set('sectionFilter', 'all')" class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                                All
                            </button>
                            <button type="button" wire:click="$set('sectionFilter', 'Western')" class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'Western' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                                Western ({{ \App\Support\AttendanceShiftConfig::formatTime(\App\Support\AttendanceShiftConfig::getStartTime('Western')) }}–{{ \App\Support\AttendanceShiftConfig::formatTime(\App\Support\AttendanceShiftConfig::getEndTime('Western')) }})
                            </button>
                            <button type="button" wire:click="$set('sectionFilter', 'Islamic')" class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'Islamic' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900' }}">
                                Islamic ({{ \App\Support\AttendanceShiftConfig::formatTime(\App\Support\AttendanceShiftConfig::getStartTime('Islamic')) }}–{{ \App\Support\AttendanceShiftConfig::formatTime(\App\Support\AttendanceShiftConfig::getEndTime('Islamic')) }})
                            </button>
                        </div>
                        <a href="{{ route('settings.attendance') }}" title="Configure Shift Parameters" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 text-xs transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </a>
                        <div class="text-xs font-semibold text-slate-500 hidden sm:block">
                            Tool: <span class="font-bold text-amber-600">{{ $tool }}</span>
                        </div>
                    </div>
                </div>

                @forelse ($this->visibleTeachers as $teacher)
                    @php
                        $status = $marks[$teacher->id]['status'] ?? 'Present';
                        $note = $marks[$teacher->id]['note'] ?? null;

                        $rowBorder = [
                            'Present' => 'border-slate-100 bg-white hover:border-slate-300',
                            'Absent'  => 'border-red-200 bg-red-50/30 hover:border-red-300',
                            'Late'    => 'border-amber-200 bg-amber-50/30 hover:border-amber-300',
                            'Excused' => 'border-purple-200 bg-purple-50/30 hover:border-purple-300',
                        ][$status] ?? 'border-slate-100 bg-white';
                    @endphp

                    <div wire:key="teacher-row-{{ $teacher->id }}"
                        wire:click="applyTool({{ $teacher->id }})"
                        class="cursor-pointer rounded-2xl border p-4 shadow-sm transition-all duration-150 {{ $rowBorder }}">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            {{-- Teacher Info --}}
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-500/10 text-sm font-extrabold text-amber-600 ring-1 ring-inset ring-amber-500/20 flex-shrink-0">
                                    {{ mb_substr($teacher->name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="truncate text-sm font-bold text-slate-900">{{ $teacher->name }}</span>
                                        @if($teacher->getShift() === 'Islamic')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Islamic Section
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-50 text-sky-700 border border-sky-200">
                                                Western Section
                                            </span>
                                        @endif
                                        @if($note)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200 shadow-xs" title="{{ $note }}">
                                                <svg class="h-3 w-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $note }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="truncate text-xs font-medium text-slate-400">{{ $teacher->email }}</div>
                                </div>
                            </div>

                            {{-- 1-Click Segmented Controls --}}
                            <div class="flex items-center gap-2 flex-shrink-0" @click.stop>
                                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/60 shadow-inner">
                                    <button type="button" wire:click="setMark({{ $teacher->id }}, 'Present')"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $status === 'Present' ? 'bg-emerald-500 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-800' }}">
                                        P
                                    </button>
                                    <button type="button" wire:click="setMark({{ $teacher->id }}, 'Absent')"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $status === 'Absent' ? 'bg-red-500 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-800' }}">
                                        A
                                    </button>
                                    <button type="button" wire:click="setMark({{ $teacher->id }}, 'Late')"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $status === 'Late' ? 'bg-amber-500 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-800' }}">
                                        L
                                    </button>
                                    <button type="button" wire:click="setMark({{ $teacher->id }}, 'Excused')"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all {{ $status === 'Excused' ? 'bg-purple-500 text-white shadow-sm scale-105' : 'text-slate-500 hover:text-slate-800' }}">
                                        E
                                    </button>
                                </div>

                                <button type="button" wire:click="cycleStatus({{ $teacher->id }})"
                                    class="rounded-xl px-3 py-1.5 text-xs font-bold border transition-all {{ [
                                        'Present' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Absent'  => 'bg-red-50 text-red-700 border-red-200',
                                        'Late'    => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Excused' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    ][$status] }}">
                                    {{ $status }}
                                </button>
                            </div>
                        </div>

                        {{-- Internal Note Input --}}
                        <div class="mt-3" @click.stop>
                            <input type="text"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10 font-medium text-slate-700 bg-slate-50/50"
                                placeholder="Add custom note for {{ $teacher->name }} (e.g. Approved leave, sick note, delay reason)..."
                                wire:model.live.debounce.400ms="marks.{{ $teacher->id }}.note" />
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl bg-white p-12 text-center border border-slate-100 shadow-sm">
                        <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="text-sm font-bold text-slate-700">No teachers matching search criteria</div>
                        <p class="mt-1 text-xs text-slate-400">Try clearing filters or search term to see all staff members.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        {{-- Term Summary & Audit Leaderboard Mode --}}
        <div class="rounded-3xl bg-white shadow-md border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Term Attendance Audit Log</h3>
                    <p class="text-xs text-slate-400">Staff attendance breakdown for {{ $session }} â€” Term {{ $term }}</p>
                </div>
                <button type="button" wire:click="exportCsv" class="btn-outline text-xs" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Export Audit CSV
                </button>
            </div>

            @if(count($this->teacherSummaries) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="px-6 py-3.5">Teacher Profile</th>
                                <th class="px-6 py-3.5 text-center">Sheets Recorded</th>
                                <th class="px-6 py-3.5 text-center text-emerald-600">Present</th>
                                <th class="px-6 py-3.5 text-center text-amber-600">Late</th>
                                <th class="px-6 py-3.5 text-center text-red-600">Absent</th>
                                <th class="px-6 py-3.5 text-center text-purple-600">Excused</th>
                                <th class="px-6 py-3.5 text-right">Attendance Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-semibold">
                            @foreach($this->teacherSummaries as $row)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $row['teacher']->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $row['teacher']->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-600">{{ $row['total_sheets'] }}</td>
                                    <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $row['present'] }}</td>
                                    <td class="px-6 py-4 text-center text-amber-600 font-bold">{{ $row['late'] }}</td>
                                    <td class="px-6 py-4 text-center text-red-600 font-bold">{{ $row['absent'] }}</td>
                                    <td class="px-6 py-4 text-center text-purple-600 font-bold">{{ $row['excused'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, $row['attendance_rate']) }}%"></div>
                                            </div>
                                            <span class="font-extrabold text-slate-800">{{ $row['attendance_rate'] }}%</span>
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
                    <div class="text-sm font-bold text-slate-700">No attendance sheets recorded for this term yet</div>
                    <p class="mt-1 text-xs text-slate-400">Switch to Daily Sheet mode above to record attendance for today.</p>
                </div>
            @endif
        </div>
    @endif
</div>
