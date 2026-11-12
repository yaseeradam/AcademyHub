<div
    class="space-y-6"
    wire:poll.10s
    x-data="{
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
    {{-- Hero Banner (Matching Students Attendance Page) --}}
    <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl shadow-xl sm:shadow-2xl" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-blue-500/10 via-transparent to-transparent"></div>
        <div class="absolute right-0 top-0 bottom-0 w-64 opacity-5 pointer-events-none">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="1"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="1"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="1"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-4 sm:gap-6 px-4 py-5 sm:px-8 sm:py-8 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 sm:h-2.5 sm:w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-blue-400">Real-Time Staff Registry</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">Staff Attendance</h1>
                <p class="text-xs sm:text-sm font-medium text-slate-400">Track and manage teacher and administrative personnel presence with absolute efficiency.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                {{-- Mode Switcher Tabs --}}
                <div class="inline-flex rounded-xl bg-slate-800/80 p-1 border border-slate-700/80 shadow-2xs">
                    <button type="button" wire:click="$set('viewMode', 'sheet')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'sheet' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:text-white' }}"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Daily Sheet
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'summary')"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'summary' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-300 hover:text-white' }}"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Term Audit
                    </button>
                </div>

                {{-- Monthly Timesheet Link --}}
                <a href="{{ route('attendance.staff-timesheet') }}"
                    class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-slate-700/40 hover:bg-slate-700/60 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-slate-200 border border-slate-600/40 transition-all active:scale-95 shadow-sm">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Monthly Timesheet</span>
                </a>

                {{-- Export CSV --}}
                <button wire:click="exportCsv" type="button"
                    class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-blue-500/20 hover:bg-blue-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-blue-300 border border-blue-500/30 transition-all active:scale-95">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Export CSV / Excel
                </button>

                {{-- Staff Attendance PDF Report Dropdown / Modal Button --}}
                <button wire:click="openReportModal" type="button"
                    class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-amber-300 border border-amber-500/30 transition-all active:scale-95 shadow-sm">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Attendance Report (PDF) &rarr;</span>
                </button>

                {{-- Biometrics Sync & Gate Monitor (if marketplace component active) --}}
                @if(auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'k40-biometrics')->exists())
                    <button wire:click="syncBiometrics" wire:loading.attr="disabled" type="button" 
                            class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-indigo-500/20 hover:bg-indigo-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-indigo-300 border border-indigo-500/30 transition-all active:scale-95 shadow-sm disabled:opacity-50">
                        <svg wire:loading.remove wire:target="syncBiometrics" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <svg wire:loading wire:target="syncBiometrics" class="h-3.5 w-3.5 sm:h-4 sm:w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="syncBiometrics">Sync Biometrics</span>
                        <span wire:loading wire:target="syncBiometrics">Syncing...</span>
                    </button>

                    <a href="{{ route('biometrics.index') }}"
                       class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-purple-500/20 hover:bg-purple-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-purple-300 border border-purple-500/30 transition-all active:scale-95 shadow-sm">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a1.5 1.5 0 011.5 1.5v4.5m-3-1.5v1.5" />
                        </svg>
                        Gate Monitor
                    </a>
                @endif

                {{-- Student Attendance Link --}}
                <a href="{{ route('attendance') }}"
                    class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-slate-800 hover:bg-slate-700 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-white border border-slate-700 transition-all active:scale-95">
                    <span>Student Registry &rarr;</span>
                </a>

                @if($viewMode === 'sheet' && $teachers->count() > 0)
                    <button wire:click="markAll('Present')" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-emerald-500/10 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all active:scale-95 shadow-sm">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark All Present
                    </button>
                    <button wire:click="markAll('Absent')" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-red-500/10 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-all active:scale-95 shadow-sm">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark All Absent
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter Bar (Matching Students Attendance Card Design) --}}
    <div class="rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-6 shadow-sm sm:shadow-md border border-slate-100">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
            {{-- Shift Filter --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Staff Shift / Department</label>
                <div class="relative">
                    <select wire:model.live="sectionFilter" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                        <option value="all">All Shifts (Western &amp; Islamic)</option>
                        <option value="Western">Western Shift (Morning 8:00 AM)</option>
                        <option value="Islamic">Islamic Shift (Afternoon 12:30 PM)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Date --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tracking Date</label>
                <input wire:model.live="date" type="date" aria-label="Tracking Date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
            </div>

            {{-- Term --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Term</label>
                <div class="relative">
                    <select wire:model.live="term" aria-label="Term" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                        @foreach(($availableTerms ?? []) as $num => $termTitle)
                            <option value="{{ $num }}">{{ $termTitle }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Session --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Academic Session</label>
                @if(!empty($academicSessions) && $academicSessions->isNotEmpty())
                    <div class="relative">
                        <select wire:model.live="session" aria-label="Academic Session" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                            @foreach($academicSessions as $sess)
                                <option value="{{ $sess->name }}">{{ $sess->name }}{{ $sess->is_active ? ' (Current)' : '' }}</option>
                            @endforeach
                            @if(!$academicSessions->contains('name', $session) && !empty($session))
                                <option value="{{ $session }}">{{ $session }}</option>
                            @endif
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                @else
                    <input wire:model.live="session" type="text" aria-label="Academic Session" placeholder="e.g. 2026/2027" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
                @endif
            </div>
        </div>
    </div>

    {{-- Today's Live Biometric Scans Feed (Collapsible) --}}
    @if(auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'k40-biometrics')->exists())
        <div x-data="{ isExpanded: @entangle('biometricFeedExpanded') }" class="rounded-2xl sm:rounded-3xl bg-white shadow-xs sm:shadow-sm border border-slate-200/90 overflow-hidden transition-all">
            {{-- Header / Summary Banner Bar (Always visible, compact) --}}
            <div class="p-3.5 sm:p-4.5 flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-slate-50 via-white to-slate-50 border-b border-slate-100">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="flex h-3 w-3 relative flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 truncate">
                                Live Biometric Gate Scans
                            </h3>
                            <span class="text-[10px] sm:text-[11px] font-black bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full border border-emerald-300/80">
                                {{ $dateRecords->count() }} Scanned
                            </span>
                            @if($sectionFilter !== 'all')
                                <span class="hidden sm:inline-flex text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60">
                                    {{ $sectionFilter }} Shift
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">
                            @if($dateRecords->count() > 0)
                                Latest: <strong class="text-slate-800">{{ $dateRecords->first()->teacher?->name }}</strong> &bull; {{ $dateRecords->count() }} verified punch{{ $dateRecords->count() > 1 ? 'es' : '' }} on {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                            @else
                                No staff biometric gate punches recorded yet for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Action controls: Scope Switcher + Collapse/Expand Toggle --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Scope Switcher --}}
                    <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
                        <button wire:click="setBiometricScope('all')" type="button"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $biometricFeedScope === 'all' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                            All Staff
                        </button>
                        <button wire:click="setBiometricScope('Western')" type="button"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $biometricFeedScope === 'Western' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                            Western
                        </button>
                        <button wire:click="setBiometricScope('Islamic')" type="button"
                                class="px-2.5 py-1 rounded-lg transition-all {{ $biometricFeedScope === 'Islamic' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                            Islamic
                        </button>
                    </div>

                    {{-- Collapse / Expand Toggle Button --}}
                    <button @click="isExpanded = !isExpanded" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black transition-all cursor-pointer border"
                            :class="isExpanded ? 'bg-slate-200 text-slate-800 border-slate-300 hover:bg-slate-300' : 'bg-emerald-600 text-white border-emerald-700 hover:bg-emerald-500 shadow-xs active:scale-95'">
                        <svg :class="isExpanded ? 'rotate-180' : ''" class="h-3.5 w-3.5 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span x-text="isExpanded ? 'Collapse' : 'Show Scans ({{ $dateRecords->count() }})'"></span>
                    </button>
                </div>
            </div>

            {{-- Collapsible Content: Scanned Cards Grid --}}
            <div x-show="isExpanded" x-cloak class="p-4 sm:p-6 bg-white transition-all">
                @if($dateRecords && $dateRecords->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($dateRecords as $record)
                            @php
                                $inTime = null;
                                if (!empty($record->punch_in_time)) {
                                    try {
                                        $inTime = \Carbon\Carbon::parse($record->punch_in_time)->format('g:i A');
                                    } catch (\Throwable $e) {
                                        $inTime = $record->punch_in_time;
                                    }
                                } elseif (!empty($record->note) && preg_match('/\b(\d{1,2}:\d{2}\s*(?:AM|PM))\b/i', $record->note, $tm)) {
                                    $inTime = strtoupper($tm[1]);
                                }

                                $outTime = null;
                                if (!empty($record->punch_out_time)) {
                                    try {
                                        $outTime = \Carbon\Carbon::parse($record->punch_out_time)->format('g:i A');
                                    } catch (\Throwable $e) {
                                        $outTime = $record->punch_out_time;
                                    }
                                }
                            @endphp
                            <div class="flex items-center gap-3 p-3 rounded-2xl border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-200 transition-all shadow-2xs">
                                @if($record->teacher?->profile_photo)
                                    <img src="{{ $record->teacher->profile_photo_url }}" class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200" />
                                @else
                                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-xs font-black text-amber-700 ring-1 ring-amber-200">
                                        {{ mb_substr($record->teacher?->name ?? 'T', 0, 1) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-extrabold text-slate-900 truncate">{{ $record->teacher?->name }}</div>
                                    <div class="text-[10px] text-slate-400 truncate">{{ $record->teacher?->getShiftLabel() ?? 'Staff' }}</div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                            In: {{ $inTime ?: '-' }}
                                        </span>
                                        @if($outTime)
                                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-800">
                                                Out: {{ $outTime }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-xs text-slate-400">
                        No staff gate punches recorded yet for this date.
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Real-Time KPI Stats Bar (Identical to Students Attendance Page) --}}
    @php
        $totalTeachersCount = $teachers->count();
        $presentCount  = $markCounts['Present']  ?? 0;
        $absentCount   = $markCounts['Absent']   ?? 0;
        $lateCount     = $markCounts['Late']     ?? 0;
        $excusedCount  = $markCounts['Excused']  ?? 0;
        $unmarkedCount = $markCounts['Unmarked'] ?? 0;

        $presentRate  = $totalTeachersCount > 0 ? round(($presentCount / $totalTeachersCount) * 100) : 0;
        $absentRate   = $totalTeachersCount > 0 ? round(($absentCount / $totalTeachersCount) * 100) : 0;
        $lateRate     = $totalTeachersCount > 0 ? round(($lateCount / $totalTeachersCount) * 100) : 0;
        $excusedRate  = $totalTeachersCount > 0 ? round(($excusedCount / $totalTeachersCount) * 100) : 0;
        $unmarkedRate = $totalTeachersCount > 0 ? round(($unmarkedCount / $totalTeachersCount) * 100) : 0;
    @endphp

    <div class="grid grid-cols-2 gap-2 sm:gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
        {{-- Total Active Staff --}}
        <div class="col-span-2 sm:col-span-1 relative overflow-hidden rounded-xl bg-slate-900 p-3 sm:p-3.5 shadow-sm border border-slate-800 text-white active:scale-[0.98] transition-transform">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Staff</span>
                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[9px] font-bold text-slate-300">Total</span>
            </div>
            <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold">{{ $totalTeachersCount }}</div>
            <div class="text-[10px] font-medium text-slate-400">Registered staff</div>
        </div>

        {{-- Present Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3 sm:p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md active:scale-[0.98]">
            <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Present</span>
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-600">P</span>
            </div>
            <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $presentCount }}</div>
            <div class="text-[10px] font-semibold text-emerald-500 flex items-center gap-1">
                <span>{{ $presentRate }}%</span>
                <span class="text-slate-400 font-medium">rate</span>
            </div>
        </div>

        {{-- Absent Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3 sm:p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md active:scale-[0.98]">
            <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Absent</span>
                <span class="rounded-full bg-red-50 px-2 py-0.5 text-[9px] font-bold text-red-600">A</span>
            </div>
            <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $absentCount }}</div>
            <div class="text-[10px] font-semibold text-red-500 flex items-center gap-1">
                <span>{{ $absentRate }}%</span>
                <span class="text-slate-400 font-medium">rate</span>
            </div>
        </div>

        {{-- Late Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3 sm:p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md active:scale-[0.98]">
            <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Late</span>
                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-bold text-amber-600">L</span>
            </div>
            <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $lateCount }}</div>
            <div class="text-[10px] font-semibold text-amber-500 flex items-center gap-1">
                <span>{{ $lateRate }}%</span>
                <span class="text-slate-400 font-medium">rate</span>
            </div>
        </div>

        {{-- Excused Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3 sm:p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md active:scale-[0.98]">
            <div class="absolute top-0 left-0 right-0 h-1 bg-purple-500"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Excused</span>
                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-[9px] font-bold text-purple-600">E</span>
            </div>
            <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $excusedCount }}</div>
            <div class="text-[10px] font-semibold text-purple-500 flex items-center gap-1">
                <span>{{ $excusedRate }}%</span>
                <span class="text-slate-400 font-medium">rate</span>
            </div>
        </div>

        {{-- Unmarked Counter --}}
        <div class="relative overflow-hidden rounded-xl bg-white p-3 sm:p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md active:scale-[0.98]">
            <div class="absolute top-0 left-0 right-0 h-1 bg-slate-400"></div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unmarked</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-bold text-slate-500">?</span>
            </div>
            <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $unmarkedCount }}</div>
            <div class="text-[10px] font-semibold text-slate-400 flex items-center gap-1">
                <span>{{ $unmarkedRate }}%</span>
                <span class="text-slate-400 font-medium">pending</span>
            </div>
        </div>
    </div>

    @if($viewMode === 'sheet')
        {{-- Interactive Marking Sheet Card (Matching Students Attendance Card Design) --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white shadow-sm sm:shadow-md border border-slate-100 overflow-hidden">
            {{-- Header Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 border-b border-slate-100 px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50/50">
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-initial sm:max-w-xs w-full">
                        <input id="teacherAttendanceSearch" wire:model.live.debounce.200ms="search" type="text"
                               aria-label="Search staff (/)" placeholder="Search by name or email (/)..."
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 font-medium text-slate-700 bg-white" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input wire:model.live="onlyExceptions" type="checkbox" aria-label="Exceptions Only" class="h-4 w-4 rounded text-blue-600 focus:ring-blue-500/20 border-slate-300" />
                        <span class="text-xs font-bold text-slate-650">Exceptions Only</span>
                    </label>

                    {{-- Shift Quick Switcher Pills --}}
                    <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200 text-xs font-bold">
                        <button type="button" wire:click="$set('sectionFilter', 'all')"
                            class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                            All Shifts
                        </button>
                        <button type="button" wire:click="$set('sectionFilter', 'Western')"
                            class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'Western' ? 'bg-sky-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                            Western
                        </button>
                        <button type="button" wire:click="$set('sectionFilter', 'Islamic')"
                            class="px-2.5 py-1 rounded-lg transition-all {{ $sectionFilter === 'Islamic' ? 'bg-emerald-600 text-white shadow-2xs' : 'text-slate-500 hover:text-slate-900' }}">
                            Islamic
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="markAll('Present')" wire:confirm="Are you sure you want to mark all staff members as Present?"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors shadow-2xs">
                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Mark All Present</span>
                    </button>
                    <button type="button" wire:click="markUnmarkedAsAbsent" wire:confirm="Are you sure you want to mark all unmarked staff as Absent?"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold rounded-xl bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors shadow-2xs">
                        <svg class="h-3.5 w-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Mark Unmarked as Absent</span>
                    </button>
                    <div class="flex items-center gap-1.5 pl-1 border-l border-slate-200">
                        <span class="text-[11px] sm:text-xs font-bold text-slate-400">Date:</span>
                        <span class="text-xs font-extrabold text-blue-600 bg-blue-50 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg">
                            {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Register Status Banner --}}
            @if($sheetId)
                @php
                    $isToday = \Carbon\Carbon::parse($date)->isToday();
                @endphp
                <div class="mx-4 sm:mx-6 mt-3 mb-0 flex flex-wrap items-center gap-2 px-3 py-2 rounded-xl {{ $isToday ? 'bg-blue-50 border border-blue-200' : 'bg-emerald-50 border border-emerald-200' }}">
                    <span class="flex items-center gap-1 text-[10px] font-black {{ $isToday ? 'text-blue-700' : 'text-emerald-700' }}">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $isToday ? "Today's Staff Register Loaded" : "Existing Staff Register Found — " . \Carbon\Carbon::parse($date)->format('M d, Y') }}
                    </span>
                    <span class="ml-auto flex flex-wrap items-center gap-1.5 text-[9px] font-bold">
                        <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">✓ {{ $presentCount }} Present</span>
                        @if($absentCount > 0)<span class="bg-red-100 text-red-800 px-2 py-0.5 rounded-full">✗ {{ $absentCount }} Absent</span>@endif
                        @if($lateCount > 0)<span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">⏱ {{ $lateCount }} Late</span>@endif
                        @if($excusedCount > 0)<span class="bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">✔ {{ $excusedCount }} Excused</span>@endif
                        @if($unmarkedCount > 0)
                            <button type="button" wire:click="markUnmarkedAsAbsent" wire:confirm="Mark all {{ $unmarkedCount }} unmarked staff member(s) as Absent?" class="inline-flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white px-2.5 py-0.5 rounded-full transition-colors cursor-pointer shadow-2xs font-extrabold" title="Click to auto-mark unmarked staff as Absent">
                                <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Mark {{ $unmarkedCount }} Unmarked as Absent</span>
                            </button>
                        @endif
                    </span>
                </div>
            @elseif($date && !\Carbon\Carbon::parse($date)->isToday())
                <div class="mx-4 sm:mx-6 mt-3 mb-0 flex flex-wrap items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-amber-50 border border-amber-200">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="text-xs font-bold text-amber-800">
                            No staff register was recorded on <strong>{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</strong>.
                        </span>
                    </div>
                    @if(!empty($latestSheet) && \Carbon\Carbon::parse($latestSheet->date)->toDateString() !== \Carbon\Carbon::parse($date)->toDateString())
                        <button type="button" wire:click="$set('date', '{{ \Carbon\Carbon::parse($latestSheet->date)->toDateString() }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-900 font-extrabold text-xs transition-colors shadow-2xs cursor-pointer">
                            <span>Open recorded register from {{ \Carbon\Carbon::parse($latestSheet->date)->format('M d, Y') }}@if(isset($latestSheet->present_count) && $latestSheet->present_count > 0) ({{ $latestSheet->present_count }} Present)@endif</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                    @endif
                </div>
            @endif

            {{-- Staff Cards Grid (Matching Student Cards Grid) --}}
            @if($visibleTeachers->count() > 0)
                <div class="p-3 sm:p-6">
                    <div class="grid grid-cols-2 gap-2.5 sm:gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach($visibleTeachers as $teacher)
                            @php
                                $markData = $marks[$teacher->id] ?? [];
                                $status = (string) ($markData['status'] ?? 'Unmarked');
                                if (!in_array($status, ['Present', 'Absent', 'Late', 'Excused', 'Unmarked'], true)) {
                                    $status = 'Unmarked';
                                }
                                $statusConfig = [
                                    'Present'  => ['bar' => 'bg-emerald-500', 'ring' => 'ring-emerald-400/40', 'border' => 'border-emerald-200', 'bg' => 'bg-emerald-50/60', 'badge' => 'bg-emerald-100 text-emerald-700'],
                                    'Absent'   => ['bar' => 'bg-red-500',     'ring' => 'ring-red-400/40',     'border' => 'border-red-200',     'bg' => 'bg-red-50/60',     'badge' => 'bg-red-100 text-red-700'],
                                    'Late'     => ['bar' => 'bg-amber-500',   'ring' => 'ring-amber-400/40',   'border' => 'border-amber-200',   'bg' => 'bg-amber-50/60',   'badge' => 'bg-amber-100 text-amber-700'],
                                    'Excused'  => ['bar' => 'bg-purple-500',  'ring' => 'ring-purple-400/40',  'border' => 'border-purple-200',  'bg' => 'bg-purple-50/60',  'badge' => 'bg-purple-100 text-purple-700'],
                                    'Unmarked' => ['bar' => 'bg-slate-300',   'ring' => 'ring-slate-300/40',   'border' => 'border-slate-200',   'bg' => 'bg-slate-50/60',   'badge' => 'bg-slate-100 text-slate-500'],
                                ][$status];

                                $isBiometric = !empty($markData['is_biometric']);
                                $punchDisplay = $markData['punch_display'] ?? null;
                            @endphp

                            <div wire:key="teacher-card-{{ $teacher->id }}"
                                 class="relative flex flex-col rounded-xl sm:rounded-2xl border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} overflow-hidden shadow-xs sm:shadow-sm hover:shadow-md transition-all duration-200 ring-1 sm:ring-2 {{ $statusConfig['ring'] }} active:scale-[0.99]">
                                
                                {{-- Status colour bar --}}
                                <div class="h-1 sm:h-1.5 w-full {{ $statusConfig['bar'] }}"></div>

                                @if($isBiometric)
                                    <div class="absolute top-2 right-2 flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-full px-1.5 py-0.5 text-[8px] font-black shadow-2xs z-10" title="Biometric Punch Recorded">
                                        <svg class="h-2.5 w-2.5 text-emerald-600 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a1.5 1.5 0 011.5 1.5v4.5m-3-1.5v1.5" />
                                        </svg>
                                        <span class="hidden sm:inline">Biometric</span>
                                    </div>
                                @endif

                                {{-- Photo + Name + Shift --}}
                                <div class="flex flex-col items-center px-2 sm:px-3 pt-3 sm:pt-4 pb-2 sm:pb-3 gap-1.5 sm:gap-2">
                                    @if($teacher->profile_photo)
                                        <img src="{{ $teacher->profile_photo_url }}" class="h-14 w-14 sm:h-16 sm:w-16 rounded-xl sm:rounded-2xl object-cover ring-2 ring-white shadow-xs sm:shadow" />
                                    @else
                                        <div class="grid h-14 w-14 sm:h-16 sm:w-16 place-items-center rounded-xl sm:rounded-2xl bg-slate-800 text-base sm:text-lg font-extrabold text-white ring-2 ring-white shadow-xs sm:shadow">
                                            {{ mb_substr($teacher->name, 0, 1) }}
                                        </div>
                                    @endif

                                    <div class="text-center min-w-0 w-full">
                                        <div class="text-[11px] sm:text-xs font-extrabold text-slate-900 truncate leading-tight">{{ $teacher->name }}</div>
                                        <div class="text-[9px] sm:text-[10px] font-semibold text-slate-400 mt-0.5 truncate">{{ $teacher->email }}</div>

                                        {{-- Shift Toggle Badge --}}
                                        <div class="mt-1 flex items-center justify-center">
                                            <button type="button" wire:click="toggleTeacherShift({{ $teacher->id }})"
                                                    title="Click to toggle Shift (Western / Islamic)"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-extrabold transition-all cursor-pointer hover:scale-105 active:scale-95 {{ $teacher->getShift() === 'Islamic' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-sky-100 text-sky-800 border border-sky-300' }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $teacher->getShift() === 'Islamic' ? 'bg-emerald-500' : 'bg-sky-500' }}"></span>
                                                <span>{{ $teacher->getShift() }}</span>
                                                <svg class="h-2 w-2 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Active status badge + Biometric Punch Time --}}
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        <span class="text-[8px] sm:text-[9px] font-black px-2 sm:px-2.5 py-0.5 rounded-full {{ $statusConfig['badge'] }} uppercase tracking-wide">
                                            {{ $status }}
                                        </span>
                                        @if($punchDisplay)
                                            <span class="inline-flex items-center gap-0.5 sm:gap-1 text-[8px] sm:text-[9px] font-bold text-emerald-800 bg-emerald-100/90 border border-emerald-200/80 px-1.5 sm:px-2 py-0.5 rounded-full shadow-xs" title="Biometric Punch Time">
                                                <svg class="h-2 w-2 sm:h-2.5 sm:w-2.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $punchDisplay }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- 2×2 Status Buttons (Instant Save on Click) --}}
                                <div class="grid grid-cols-2 gap-1 px-2 sm:px-3 pb-2 sm:pb-3">
                                    <button wire:click="setMark({{ $teacher->id }}, 'Present')" aria-label="Mark Present" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Present' ? 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-400 ring-offset-1' : 'bg-white/80 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200' }}">
                                        @if($status === 'Present')
                                            <svg class="h-2.5 w-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        @endif
                                        Present
                                    </button>
                                    <button wire:click="setMark({{ $teacher->id }}, 'Absent')" aria-label="Mark Absent" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Absent' ? 'bg-red-600 text-white shadow-md ring-2 ring-red-400 ring-offset-1' : 'bg-white/80 text-slate-500 hover:bg-red-50 hover:text-red-700 border border-slate-200' }}">
                                        @if($status === 'Absent')
                                            <svg class="h-2.5 w-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        @endif
                                        Absent
                                    </button>
                                    <button wire:click="setMark({{ $teacher->id }}, 'Late')" aria-label="Mark Late" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Late' ? 'bg-amber-500 text-white shadow-md ring-2 ring-amber-400 ring-offset-1' : 'bg-white/80 text-slate-500 hover:bg-amber-50 hover:text-amber-700 border border-slate-200' }}">
                                        @if($status === 'Late')
                                            <svg class="h-2.5 w-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        @endif
                                        Late
                                    </button>
                                    <button wire:click="setMark({{ $teacher->id }}, 'Excused')" aria-label="Mark Excused" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Excused' ? 'bg-purple-600 text-white shadow-md ring-2 ring-purple-400 ring-offset-1' : 'bg-white/80 text-slate-500 hover:bg-purple-50 hover:text-purple-700 border border-slate-200' }}">
                                        @if($status === 'Excused')
                                            <svg class="h-2.5 w-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>
                                        @endif
                                        Excused
                                    </button>
                                </div>

                                {{-- Note Input --}}
                                <div class="px-2 sm:px-3 pb-2.5 sm:pb-3">
                                    <input wire:model.blur="marks.{{ $teacher->id }}.note"
                                           wire:change="updateNote({{ $teacher->id }}, $event.target.value)"
                                           type="text"
                                           aria-label="Note for {{ $teacher->name }}"
                                           placeholder="Note..."
                                           class="w-full px-2.5 sm:px-3 py-1.5 sm:py-2 text-[9px] sm:text-[10px] rounded-lg sm:rounded-xl border border-slate-200 focus:border-blue-400 focus:ring-2 focus:ring-blue-400/10 font-semibold text-slate-600 bg-white/80 placeholder-slate-300" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Bottom Sheet Controls --}}
                <div class="border-t border-slate-100 bg-slate-50 px-4 sm:px-8 py-3.5 sm:py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-[11px] sm:text-xs font-semibold text-emerald-600 flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Real-Time Auto-Save:</strong> Staff marks and K40 biometric punches are instantly synchronized to the database.</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                        @if(($markCounts['Unmarked'] ?? 0) > 0)
                            <button type="button"
                                    wire:click="markUnmarkedAsAbsent"
                                    wire:confirm="Are you sure you want to mark all {{ $markCounts['Unmarked'] }} unmarked staff member(s) as Absent?"
                                    wire:loading.attr="disabled"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl sm:rounded-2xl bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-4 py-2.5 sm:py-3.5 text-xs sm:text-sm font-bold transition-all duration-200 active:scale-95 cursor-pointer">
                                <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Mark {{ $markCounts['Unmarked'] }} Unmarked as Absent</span>
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="text-sm font-bold text-slate-800">No staff matching current filters</div>
                    <p class="mt-1 text-xs text-slate-400">Try clearing search terms or shift filters to view all staff members.</p>
                </div>
            @endif
        </div>
    @else
        {{-- Term Summary & Audit Leaderboard Mode --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white shadow-sm sm:shadow-md border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Term Attendance Audit Log</h3>
                    <p class="text-xs text-slate-400">Staff attendance breakdown for {{ $session }} &mdash; Term {{ $term }}</p>
                </div>
                <button type="button" wire:click="exportCsv"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-slate-700 border border-slate-200 hover:bg-slate-50 shadow-2xs transition-all"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Audit CSV</span>
                </button>
            </div>

            @if(count($teacherSummaries) > 0)
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
                            @foreach($teacherSummaries as $row)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($row['teacher']->profile_photo)
                                                <img src="{{ $row['teacher']->profile_photo_url }}" class="h-9 w-9 rounded-xl object-cover ring-1 ring-slate-200" />
                                            @else
                                                <div class="grid h-9 w-9 place-items-center rounded-xl bg-slate-800 text-xs font-bold text-white ring-1 ring-slate-200">
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
                                                <div class="bg-blue-600 h-full rounded-full" style="width: {{ min(100, $row['attendance_rate']) }}%"></div>
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

    {{-- Attendance Report PDF Modal (Week or Month Selection) --}}
    @if($showReportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            x-data
            @keydown.escape.window="$wire.closeReportModal()">
            <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl border border-slate-200 transition-all">
                {{-- Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Download Staff Attendance Report</h3>
                            <p class="text-xs text-slate-400">Export high-resolution landscape A4 PDF</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeReportModal"
                        class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="mt-5 space-y-4">
                    {{-- Period Type: Week vs Month --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Report Duration</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200">
                            <button type="button" wire:click="$set('reportPeriodType', 'week')"
                                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-black transition-all {{ $reportPeriodType === 'week' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Weekly (Mon – Fri)</span>
                            </button>
                            <button type="button" wire:click="$set('reportPeriodType', 'month')"
                                class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-black transition-all {{ $reportPeriodType === 'month' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span>Whole Month</span>
                            </button>
                        </div>
                    </div>

                    @if($reportPeriodType === 'week')
                        {{-- Week Selector --}}
                        <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 space-y-3">
                            <label class="block text-xs font-bold text-slate-700">Choose Week Period</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" wire:click="$set('selectedWeek', 'current')"
                                    class="py-2 px-2.5 rounded-xl text-xs font-bold border transition-all text-center {{ $selectedWeek === 'current' ? 'bg-amber-500 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    This Week
                                </button>
                                <button type="button" wire:click="$set('selectedWeek', 'previous')"
                                    class="py-2 px-2.5 rounded-xl text-xs font-bold border transition-all text-center {{ $selectedWeek === 'previous' ? 'bg-amber-500 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    Last Week
                                </button>
                                <button type="button" wire:click="$set('selectedWeek', 'custom')"
                                    class="py-2 px-2.5 rounded-xl text-xs font-bold border transition-all text-center {{ $selectedWeek === 'custom' ? 'bg-amber-500 text-white border-amber-600 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    Specific Date
                                </button>
                            </div>

                            @if($selectedWeek === 'custom')
                                <div class="pt-2">
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Pick Any Date in Target Week</label>
                                    <input type="date" wire:model="customWeekDate"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" />
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Month Selector --}}
                        <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 space-y-2">
                            <label class="block text-xs font-bold text-slate-700">Choose Month &amp; Year</label>
                            <input type="month" wire:model="selectedMonthYear"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20" />
                            <p class="text-[11px] text-slate-400">All Monday to Friday school days in this month will be compiled on the A4 landscape sheet.</p>
                        </div>
                    @endif

                    {{-- Shift Scope --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Shift Filter</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" wire:click="$set('reportShiftFilter', 'all')"
                                class="py-2 px-3 rounded-xl text-xs font-bold border transition-all text-center {{ $reportShiftFilter === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                All Shifts
                            </button>
                            <button type="button" wire:click="$set('reportShiftFilter', 'Western')"
                                class="py-2 px-3 rounded-xl text-xs font-bold border transition-all text-center {{ $reportShiftFilter === 'Western' ? 'bg-sky-600 text-white border-sky-700 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                Western Shift
                            </button>
                            <button type="button" wire:click="$set('reportShiftFilter', 'Islamic')"
                                class="py-2 px-3 rounded-xl text-xs font-bold border transition-all text-center {{ $reportShiftFilter === 'Islamic' ? 'bg-emerald-600 text-white border-emerald-700 shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                Islamic Shift
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" wire:click="closeReportModal"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-extrabold text-slate-600 hover:bg-slate-50 transition active:scale-95">
                        Cancel
                    </button>
                    <button type="button" wire:click="downloadWeeklyPdf"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-xs font-black text-white shadow-md shadow-amber-500/20 hover:shadow-lg transition-all active:scale-95"
                        wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-wait">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span wire:loading.remove wire:target="downloadWeeklyPdf">Generate &amp; Download PDF</span>
                        <span wire:loading wire:target="downloadWeeklyPdf">Generating PDF...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
