<div class="space-y-6" wire:poll.5s>
    {{-- Hero Banner --}}
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
                    <span class="text-[10px] sm:text-xs font-bold uppercase tracking-widest text-blue-400">Real-Time Registry</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">Daily Attendance</h1>
                <p class="text-xs sm:text-sm font-medium text-slate-400">Track and manage student presence with absolute efficiency.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <button wire:click="exportCsv" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-blue-500/20 hover:bg-blue-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-blue-300 border border-blue-500/30 transition-all active:scale-95">
                    <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Export CSV / Excel
                </button>

                @if(auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'k40-biometrics')->exists())
                    <button type="button" 
                            onclick="fetch('/attendance/test-popup', {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'), 'Accept':'application/json'}}).then(r=>r.json()).then(d=>{ if(window.biometricShow && d.scan) window.biometricShow(d.scan); })"
                            class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-purple-500/20 hover:bg-purple-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-purple-300 border border-purple-500/30 transition-all active:scale-95 shadow-sm">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a1.5 1.5 0 011.5 1.5v4.5m-3-1.5v1.5" />
                        </svg>
                        Test Biometric Modal
                    </button>
                @endif

                @if(auth()->user()?->role === 'admin')
                    <a href="{{ route('attendance.teachers') }}" class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-amber-300 border border-amber-500/30 transition-all active:scale-95">
                        Staff Attendance &rarr;
                    </a>
                @endif

                @if($classId && $sectionId && $students->count() > 0)
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

    {{-- Filter Bar --}}
    <div class="rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-6 shadow-sm sm:shadow-md border border-slate-100">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
            {{-- Class --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">School Class</label>
                <div class="relative">
                    <select wire:model.live="classId" id="class-select" wire:key="class-select-{{ count($classes) }}" aria-label="School Class" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" wire:key="class-opt-{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Section --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Section</label>
                <div class="relative">
                    <select wire:model.live="sectionId" id="section-select" wire:key="section-select-{{ $classId }}" aria-label="Section" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none" @disabled(!$classId)>
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" wire:key="section-opt-{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
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
                <input wire:model.live="session" type="text" aria-label="Academic Session" placeholder="e.g. 2026/2027" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
            </div>
        </div>
    </div>

    {{-- Today's Live Biometric Scans Feed --}}
    @if(auth()->user()?->tenant?->activeMarketplaceComponents()->where('slug', 'k40-biometrics')->exists())
        <div class="rounded-2xl sm:rounded-3xl bg-white p-4 sm:p-6 shadow-sm sm:shadow-md border border-slate-100">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            Live Biometric Scans
                            @if($selectedClass && $biometricFeedScope === 'class')
                                &mdash; <span class="text-blue-600">{{ $selectedClass->name }}</span>
                            @else
                                &mdash; <span class="text-slate-600">All Classes</span>
                            @endif
                        </h3>
                        <p class="text-xs text-slate-400">Students and staff who verified their fingerprint today via the K40 terminal</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Scope Switcher --}}
                    @if($classId)
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                            <button wire:click="setBiometricScope('class')" type="button"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $biometricFeedScope === 'class' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                {{ $selectedClass?->name ?? 'This Class' }}
                            </button>
                            <button wire:click="setBiometricScope('all')" type="button"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $biometricFeedScope === 'all' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                All School
                            </button>
                        </div>
                    @endif

                    <span class="text-xs font-extrabold bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-200/60">
                        {{ $dateRecords->count() }} Scanned Today
                    </span>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1.5 rounded-xl">
                        Hours: 7:00 AM - 5:00 PM
                    </span>
                </div>
            </div>

            @if($dateRecords && $dateRecords->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($dateRecords as $record)
                        <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-blue-50/40 hover:border-blue-200 transition-all">
                            <img src="{{ $record->student?->passport_photo_url }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-white shadow-sm flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-slate-900 truncate">{{ $record->student?->full_name }}</div>
                                <div class="text-[11px] font-semibold text-slate-500 truncate">
                                    {{ $record->student?->schoolClass?->name ?? 'Class' }} &bull; Sec {{ $record->student?->section?->name ?? 'A' }} &bull; ADM: {{ $record->student?->admission_number }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md {{ $record->status === 'Present' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $record->status }}
                                    </span>
                                    <span class="text-[10px] font-medium text-slate-500">
                                        {{ str_replace('Biometric scan at ', '', $record->note ?? '') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <svg class="h-8 w-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a1.5 1.5 0 011.5 1.5v4.5m-3-1.5v1.5"/></svg>
                    <p class="text-xs font-semibold">No biometric punches recorded for {{ $selectedClass?->name ?? 'this selection' }} today yet.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Students scanning their finger on the K40 will appear here and update their card status automatically.</p>
                </div>
            @endif
        </div>
    @else
        {{-- Marketplace Promo banner for K40 Biometrics --}}
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 to-purple-950 p-6 text-white border border-purple-900/40 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-purple-500/20 px-2.5 py-0.5 text-[10px] font-black text-purple-300 border border-purple-500/30">
                        Optional Addon
                    </span>
                    <h4 class="font-extrabold text-sm text-white">Upgrade to Biometric Hardware Attendance</h4>
                </div>
                <p class="text-xs text-purple-200">Connect physical ZKTeco K40 fingerprint devices directly to AcademyHub for real-time gate popups, WhatsApp arrival alerts, and dual-shift tracking.</p>
            </div>
            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('marketplace') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 hover:bg-purple-500 px-4 py-2.5 text-xs font-black text-white whitespace-nowrap shadow-md shadow-purple-500/25 transition-all">
                    Browse in Marketplace &rarr;
                </a>
            @endif
        </div>
    @endif

    {{-- Live Sheet UI --}}
    @if($classId && $sectionId)
        {{-- Real-Time Stats Bar --}}
        <div class="grid grid-cols-2 gap-2 sm:gap-2.5 sm:grid-cols-3 lg:grid-cols-6">
            {{-- Total Active Students --}}
            <div class="col-span-2 sm:col-span-1 relative overflow-hidden rounded-xl bg-slate-900 p-3 sm:p-3.5 shadow-sm border border-slate-800 text-white active:scale-[0.98] transition-transform">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Students</span>
                    <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[9px] font-bold text-slate-300">Total</span>
                </div>
                <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold">{{ $students->count() }}</div>
                <div class="text-[10px] font-medium text-slate-400">Enrolled in section</div>
            </div>

            {{-- Present Counter --}}
            <div class="relative overflow-hidden rounded-xl bg-white p-3 sm:p-3.5 shadow-sm border border-slate-100 transition-all hover:shadow-md active:scale-[0.98]">
                <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Present</span>
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-600">P</span>
                </div>
                <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $markCounts['Present'] ?? 0 }}</div>
                <div class="text-[10px] font-semibold text-emerald-500 flex items-center gap-1">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Present'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
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
                <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $markCounts['Absent'] ?? 0 }}</div>
                <div class="text-[10px] font-semibold text-red-500 flex items-center gap-1">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Absent'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
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
                <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $markCounts['Late'] ?? 0 }}</div>
                <div class="text-[10px] font-semibold text-amber-500 flex items-center gap-1">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Late'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
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
                <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $markCounts['Excused'] ?? 0 }}</div>
                <div class="text-[10px] font-semibold text-purple-500 flex items-center gap-1">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Excused'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
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
                <div class="mt-1 sm:mt-1.5 text-lg sm:text-xl font-extrabold text-slate-850">{{ $markCounts['Unmarked'] ?? 0 }}</div>
                <div class="text-[10px] font-semibold text-slate-400 flex items-center gap-1">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Unmarked'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
                    <span class="text-slate-400 font-medium">pending</span>
                </div>
            </div>
        </div>

        {{-- Interactive Marking Sheet Card --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white shadow-sm sm:shadow-md border border-slate-100 overflow-hidden">
            {{-- Header Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 border-b border-slate-100 px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50/50">
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-initial sm:max-w-xs w-full">
                        <input wire:model.live="search" type="text" aria-label="Search by name or ID" placeholder="Search by name or ID..."
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 font-medium text-slate-700 bg-white" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input wire:model.live="onlyExceptions" type="checkbox" aria-label="Exceptions Only" class="h-4 w-4 rounded text-blue-600 focus:ring-blue-500/20 border-slate-300" />
                        <span class="text-xs font-bold text-slate-650">Exceptions Only</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-400">Marking Date:</span>
                    <span class="text-xs font-extrabold text-blue-600 bg-blue-50 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg">
                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                    </span>
                </div>
            </div>

            {{-- Student Grid --}}
            @if($visibleStudents->count() > 0)
                <div class="p-3 sm:p-6">
                    <div class="grid grid-cols-2 gap-2.5 sm:gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach($visibleStudents as $student)
                            @php
                                $markData = $marks[$student->id] ?? [];
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
                                $arrivedAt = $markData['arrived_at'] ?? null;
                                $punchDisplay = null;
                                if ($arrivedAt) {
                                    try {
                                        $punchDisplay = \Carbon\Carbon::parse($arrivedAt)->timezone(config('app.timezone', 'Africa/Lagos'))->format('g:i A');
                                    } catch (\Throwable $e) {
                                        $punchDisplay = $arrivedAt;
                                    }
                                }
                            @endphp
                            <div wire:key="student-card-{{ $student->id }}" class="relative flex flex-col rounded-xl sm:rounded-2xl border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} overflow-hidden shadow-xs sm:shadow-sm hover:shadow-md transition-all duration-200 ring-1 sm:ring-2 {{ $statusConfig['ring'] }} active:scale-[0.99]">
                                {{-- Status colour bar --}}
                                <div class="h-1 sm:h-1.5 w-full {{ $statusConfig['bar'] }}"></div>

                                {{-- Photo + Name --}}
                                <div class="flex flex-col items-center px-2 sm:px-3 pt-3 sm:pt-4 pb-2 sm:pb-3 gap-1.5 sm:gap-2">
                                    @if($student->passport_photo_url)
                                        <img src="{{ $student->passport_photo_url }}" class="h-14 w-14 sm:h-16 sm:w-16 rounded-xl sm:rounded-2xl object-cover ring-2 ring-white shadow-xs sm:shadow" />
                                    @else
                                        <div class="grid h-14 w-14 sm:h-16 sm:w-16 place-items-center rounded-xl sm:rounded-2xl bg-slate-800 text-base sm:text-lg font-extrabold text-white ring-2 ring-white shadow-xs sm:shadow">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="text-center min-w-0 w-full">
                                        <div class="text-[11px] sm:text-xs font-extrabold text-slate-900 truncate leading-tight">{{ $student->full_name }}</div>
                                        <div class="text-[9px] sm:text-[10px] font-semibold text-slate-400 mt-0.5 truncate">{{ $student->admission_number }}</div>
                                    </div>

                                    {{-- Active status badge --}}
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        <span class="text-[8px] sm:text-[9px] font-black px-2 sm:px-2.5 py-0.5 rounded-full {{ $statusConfig['badge'] }} uppercase tracking-wide">
                                            {{ $status }}
                                        </span>
                                        @if($punchDisplay)
                                            <span class="inline-flex items-center gap-0.5 sm:gap-1 text-[8px] sm:text-[9px] font-bold text-emerald-800 bg-emerald-100/90 border border-emerald-200/80 px-1.5 sm:px-2 py-0.5 rounded-full shadow-xs" title="Biometric K40 Punch Time">
                                                <svg class="h-2 w-2 sm:h-2.5 sm:w-2.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a1.5 1.5 0 011.5 1.5v4.5m-3-1.5v1.5" />
                                                </svg>
                                                {{ $punchDisplay }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- 2×2 Status Buttons (Instant Save on Click) --}}
                                <div class="grid grid-cols-2 gap-1 px-2 sm:px-3 pb-2 sm:pb-3">
                                    <button wire:click="setMark({{ $student->id }}, 'Present')" aria-label="Mark Present" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Present' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-white/80 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Present' ? 'bg-white' : 'bg-emerald-500' }}"></span> Present
                                    </button>
                                    <button wire:click="setMark({{ $student->id }}, 'Absent')" aria-label="Mark Absent" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Absent' ? 'bg-red-500 text-white shadow-sm' : 'bg-white/80 text-slate-500 hover:bg-red-50 hover:text-red-700 border border-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Absent' ? 'bg-white' : 'bg-red-500' }}"></span> Absent
                                    </button>
                                    <button wire:click="setMark({{ $student->id }}, 'Late')" aria-label="Mark Late" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Late' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white/80 text-slate-500 hover:bg-amber-50 hover:text-amber-700 border border-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Late' ? 'bg-white' : 'bg-amber-500' }}"></span> Late
                                    </button>
                                    <button wire:click="setMark({{ $student->id }}, 'Excused')" aria-label="Mark Excused" wire:loading.attr="disabled"
                                            class="flex items-center justify-center gap-1 rounded-lg sm:rounded-xl py-1.5 sm:py-2 text-[9px] sm:text-[10px] font-extrabold transition-all duration-150 active:scale-95 {{ $status === 'Excused' ? 'bg-purple-500 text-white shadow-sm' : 'bg-white/80 text-slate-500 hover:bg-purple-50 hover:text-purple-700 border border-slate-200' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Excused' ? 'bg-white' : 'bg-purple-500' }}"></span> Excused
                                    </button>
                                </div>

                                {{-- Note Input --}}
                                <div class="px-2 sm:px-3 pb-2.5 sm:pb-3">
                                    <input wire:model.blur="marks.{{ $student->id }}.note"
                                           wire:change="updateNote({{ $student->id }}, $event.target.value)"
                                           type="text"
                                           aria-label="Note for {{ $student->first_name }}"
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
                        <span><strong>Real-Time Auto-Save:</strong> Teacher clicks and K40 biometric punches are instantly synchronized to the database.</span>
                    </div>

                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl sm:rounded-2xl bg-amber-500 hover:bg-amber-600 px-5 sm:px-7 py-2.5 sm:py-3.5 text-xs sm:text-sm font-extrabold text-white shadow-lg shadow-amber-500/25 hover:shadow-xl hover:shadow-amber-500/35 transition-all duration-200 active:scale-95 group">
                        <svg class="h-4 w-4 text-white group-hover:rotate-12 transition-transform" wire:loading.remove wire:target="save" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span wire:loading.remove wire:target="save">Finalize Register &rarr;</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving Sheets...
                        </span>
                    </button>
                </div>
            @else
                <div class="px-6 py-16 text-center bg-white">
                    <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-slate-700">No active students found</div>
                    <p class="mt-1.5 text-xs text-slate-400 max-w-sm mx-auto">There are no active students matching the search criteria or enrolled in the selected class section.</p>
                </div>
            @endif
        </div>
    @else
        {{-- Selector Empty State --}}
        <div class="rounded-3xl bg-white border border-slate-100 shadow-md p-16 text-center">
            <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-2xl bg-blue-50/50 text-blue-500 ring-4 ring-blue-500/5">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M8.25 21h8.25c.621 0 1.125-.504 1.125-1.125V4.125c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v15.75c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Select Class & Section to Begin</h3>
            <p class="mt-1.5 text-xs text-slate-400 max-w-xs mx-auto">Please choose a school class and section from the control filter card above to load the student registry and start tracking daily attendance.</p>
        </div>
    @endif
</div>
