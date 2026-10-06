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
    {{-- ══════════════════════════════════════════════════════════════
         1. EXECUTIVE HEADER & ACTIONS
    ══════════════════════════════════════════════════════════════ --}}
    <x-page-header 
        :title="$hasTarget ? $targetTitle : 'Institutional Timetable'" 
        :subtitle="$hasTarget ? ($totalPeriods . ' scheduled periods · Monday to ' . (count($days) === 6 ? 'Saturday' : 'Friday')) : 'Manage weekly teaching periods, room allocations, and scheduled recess periods'" 
        accent="classes">
        <x-slot name="actions">
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                {{-- Scope Switcher: Class vs Staff --}}
                @if(auth()->user()?->role !== 'parent')
                    <div class="inline-flex p-1 bg-white rounded-xl border border-slate-200 shadow-xs">
                        <button type="button" 
                                wire:click="$set('scheduleScope', 'class')" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ !$isStaffScope ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Class Schedule</span>
                        </button>
                        <button type="button" 
                                wire:click="$set('scheduleScope', 'staff')" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $isStaffScope ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Staff Timetable</span>
                        </button>
                    </div>
                @endif

                {{-- Add Period Button --}}
                @if($isAdmin && !$isStaffScope && $hasTarget)
                    <button type="button" 
                            wire:click="clearForm" 
                            x-data 
                            x-on:click="$dispatch('open-modal', 'timetable-form')" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl text-white bg-sky-600 hover:bg-sky-700 shadow-sm transition-all cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Period</span>
                    </button>
                @endif

                @if($classId && !$isStaffScope && auth()->user()?->role !== 'parent')
                    <a href="{{ route('timetable.pdf', ['class_id' => $classId]) }}" target="_blank" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold rounded-xl text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-300 transition-all shadow-xs" title="Download Official Certificate Poster PDF">
                        <svg class="h-3.5 w-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Download PDF</span>
                    </a>
                @endif

                @if($hasTarget)
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 transition-all shadow-xs print:hidden cursor-pointer" title="Print Landscape Timetable Poster">
                        <svg class="h-3.5 w-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Print</span>
                    </button>
                @endif
            </div>
        </x-slot>
    </x-page-header>

    {{-- ══════════════════════════════════════════════════════════════
         2. CONTROLS TOOLBAR & VIEW SWITCHER
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white p-3 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div class="flex flex-wrap items-center gap-3">
            @if(!$isStaffScope)
                {{-- Class Selector --}}
                <div class="min-w-[190px]">
                    <select wire:model.live="classId" class="select text-xs sm:text-sm font-bold w-full bg-slate-50 border-slate-200">
                        <option value="">Select Class ({{ collect($this->classes)->count() }})</option>
                        @foreach($this->classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                {{-- Teacher Selector --}}
                <div class="min-w-[210px]">
                    <select wire:model.live="teacherFilterId" class="select text-xs sm:text-sm font-bold w-full bg-slate-50 border-slate-200">
                        <option value="">Select Teaching Staff ({{ collect($this->teachers)->count() }})</option>
                        @foreach($this->teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($hasTarget)
                {{-- View Toggle [Modern Grid (Default) | Daily Agenda | Conventional Poster Preview] --}}
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200">
                    <button type="button" wire:click="$set('viewMode', 'grid')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'grid' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Modern Grid</span>
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'daily')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'daily' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Agenda</span>
                    </button>
                    <button type="button" wire:click="$set('viewMode', 'conventional')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ $viewMode === 'conventional' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Poster Preview</span>
                    </button>
                </div>

                @if($isAdmin && !$isStaffScope)
                    <button type="button" wire:click="loadConventionalTemplate" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300/80 transition shadow-2xs"
                            title="Ensure conventional break 9:30 - 9:40 is set for Mon-Fri">
                        <svg class="h-3.5 w-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Break Preset</span>
                    </button>
                @endif
            @endif
        </div>

        @if($hasTarget)
            <div class="flex items-center gap-2.5 text-xs">
                @if($isAdmin && !$isStaffScope)
                    <span class="hidden md:inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">
                        <svg class="h-3 w-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Click slot to edit
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ $totalPeriods }} Scheduled Periods
                </span>
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
            @if($viewMode === 'conventional')
                {{-- ══════════════════════════════════════════════════════════════
                     CONVENTIONAL TIMETABLE POSTER
                ══════════════════════════════════════════════════════════════ --}}
                <div class="space-y-4">
                    {{-- POSTER CONTAINER (Target for print) --}}
                    <div id="conventional-poster-canvas" class="bg-white rounded-3xl p-3 sm:p-6 shadow-xl border border-slate-200/80 overflow-x-auto">
                        {{-- Outer Gold Border (matches poster) --}}
                        <div class="poster-outer-frame border-[3.5px] border-[#C59B27] bg-[#FAF8F5] p-2.5 sm:p-4 rounded-2xl sm:rounded-3xl shadow-xs" style="min-width: 980px;">
                            {{-- Inner Navy Border (matches poster) --}}
                            <div class="poster-inner-frame border-[2px] border-[#0C1E40] bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl">

                                {{-- ══════ HEADER ROW (Exact 3-column replica) ══════ --}}
                                <div class="flex items-center justify-between gap-4 pb-3 mb-3">
                                    {{-- Left: Official Circular Emblem Badge --}}
                                    <div class="w-52 flex items-center justify-start shrink-0">
                                        <div class="h-24 w-24 rounded-full border-[2.5px] border-[#C59B27] p-1 bg-white shadow-sm flex items-center justify-center">
                                            @if(!empty($this->schoolInfo['logo']))
                                                <div class="h-full w-full rounded-full border border-amber-200/80 p-1 flex items-center justify-center bg-white overflow-hidden">
                                                    <img src="{{ asset('uploads/'.str_replace('\\','/',$this->schoolInfo['logo'])) }}" alt="Logo" class="h-full w-full object-contain" />
                                                </div>
                                            @else
                                                <div class="h-full w-full rounded-full bg-[#0C1E40] border border-[#C59B27]/70 flex flex-col items-center justify-center p-1.5 text-center text-white relative overflow-hidden">
                                                    <svg class="h-7 w-7 text-amber-400 my-0.5" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.45 11.55L12 16.22l8.55-4.67V17c0 3.31-4.7 6-8.55 6S3.45 20.31 3.45 17v-5.45z"/>
                                                    </svg>
                                                    <div class="text-[6.5px] font-black uppercase tracking-wider text-amber-200/90 leading-tight">ACADEMY</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Center: School Name + Motto + Big Bold Title --}}
                                    <div class="flex-1 text-center px-2">
                                        <h1 class="text-2xl sm:text-3xl font-black text-[#0C1E40] tracking-tight uppercase font-serif leading-tight">
                                            {{ $this->schoolInfo['name'] }}
                                        </h1>

                                        {{-- Motto with horizontal rules & gold diamond --}}
                                        <div class="flex items-center justify-center gap-3 my-1.5">
                                            <div class="h-[1.5px] w-16 sm:w-28 bg-[#C59B27]"></div>
                                            <div class="text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] text-[#0C1E40]">
                                                {{ $this->schoolInfo['motto'] }}
                                            </div>
                                            <div class="h-[1.5px] w-16 sm:w-28 bg-[#C59B27]"></div>
                                        </div>

                                        {{-- Big Title: CONVENTIONAL TIMETABLE with Gold Diamond Separator --}}
                                        <div class="flex items-center justify-center gap-3 mt-2">
                                            <div class="h-2 w-2 rotate-45 bg-[#C59B27]"></div>
                                            <h2 class="text-base sm:text-lg font-black tracking-[0.25em] text-[#0C1E40] uppercase">
                                                CONVENTIONAL TIMETABLE
                                            </h2>
                                            <div class="h-2 w-2 rotate-45 bg-[#C59B27]"></div>
                                        </div>
                                    </div>

                                    {{-- Right: Meta Card (CLASS, TERM, SESSION, CLASS TEACHER) --}}
                                    <div class="w-72 shrink-0">
                                        <div class="border-[1.5px] border-[#0C1E40] rounded-xl p-3 bg-white text-xs space-y-1.5 shadow-2xs">
                                            <div class="flex items-baseline justify-between border-b border-[#0C1E40]/25 pb-1">
                                                <span class="font-black text-[#0C1E40] text-[10px] tracking-wider uppercase">CLASS:</span>
                                                <span class="font-black text-[#0C1E40] text-xs uppercase">{{ $this->selectedClass?->name ?? '________' }}</span>
                                            </div>
                                            <div class="flex items-baseline justify-between border-b border-[#0C1E40]/25 pb-1">
                                                <span class="font-black text-[#0C1E40] text-[10px] tracking-wider uppercase">TERM:</span>
                                                <span class="font-extrabold text-slate-800 text-[11px] uppercase">{{ $this->schoolInfo['term'] }}</span>
                                            </div>
                                            <div class="flex items-baseline justify-between border-b border-[#0C1E40]/25 pb-1">
                                                <span class="font-black text-[#0C1E40] text-[10px] tracking-wider uppercase">SESSION:</span>
                                                <span class="font-extrabold text-slate-800 text-[11px]">{{ $this->schoolInfo['session'] }}</span>
                                            </div>
                                            <div class="flex items-start justify-between pt-0.5">
                                                <span class="font-black text-[#0C1E40] text-[10px] tracking-wider uppercase shrink-0 pt-0.5">CLASS TEACHER:</span>
                                                <span class="font-bold text-[#8C6D15] text-[11px] text-right pl-2 leading-tight">{{ $this->selectedClassTeacher ?? '_______________' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ══════ TIMETABLE GRID TABLE (Exact Layout & Proportions) ══════ --}}
                                <div class="mt-2">
                                    <table class="w-full border-collapse border-[2px] border-[#0C1E40] text-center table-fixed">
                                        <thead>
                                            {{-- Top Navy Bar --}}
                                            <tr class="bg-[#0C1E40] text-white">
                                                <th rowspan="2" class="border-[2px] border-[#0C1E40] w-[11%] px-2 py-2.5 text-xs font-black uppercase tracking-wider text-amber-300 bg-[#0C1E40]">
                                                    DAYS
                                                </th>
                                                <th colspan="7" class="border-[2px] border-[#0C1E40] py-2.5 text-xs font-black uppercase tracking-[0.25em] text-white bg-[#0C1E40]">
                                                    PERIODS &amp; TIME
                                                </th>
                                            </tr>
                                            {{-- Periods & Time Row --}}
                                            <tr class="bg-[#0C1E40] text-white text-[11px]">
                                                {{-- Period 1 --}}
                                                <th class="border-[2px] border-[#0C1E40] bg-slate-50 text-[#0C1E40] px-1.5 py-2 font-bold w-[12.7%]">
                                                    <div class="h-5 w-5 mx-auto rounded-full bg-[#0C1E40] text-amber-300 font-black text-xs flex items-center justify-center">1</div>
                                                    <div class="text-[10px] text-slate-700 font-bold whitespace-nowrap mt-1">8:00am &ndash; 8:30am</div>
                                                </th>
                                                {{-- Period 2 --}}
                                                <th class="border-[2px] border-[#0C1E40] bg-slate-50 text-[#0C1E40] px-1.5 py-2 font-bold w-[12.7%]">
                                                    <div class="h-5 w-5 mx-auto rounded-full bg-[#0C1E40] text-amber-300 font-black text-xs flex items-center justify-center">2</div>
                                                    <div class="text-[10px] text-slate-700 font-bold whitespace-nowrap mt-1">8:30am &ndash; 9:00am</div>
                                                </th>
                                                {{-- Period 3 --}}
                                                <th class="border-[2px] border-[#0C1E40] bg-slate-50 text-[#0C1E40] px-1.5 py-2 font-bold w-[12.7%]">
                                                    <div class="h-5 w-5 mx-auto rounded-full bg-[#0C1E40] text-amber-300 font-black text-xs flex items-center justify-center">3</div>
                                                    <div class="text-[10px] text-slate-700 font-bold whitespace-nowrap mt-1">9:00am &ndash; 9:30am</div>
                                                </th>

                                                {{-- Recess Break Column Header --}}
                                                <th class="border-[2px] border-[#0C1E40] px-1 py-2 font-bold w-[6.5%] bg-amber-50/90 text-amber-950">
                                                    <div class="font-black text-[10.5px] text-amber-950 tracking-wider uppercase">BREAK</div>
                                                    <div class="text-[8.5px] text-amber-800 font-bold mt-1 whitespace-nowrap">9:30&ndash;9:40</div>
                                                </th>

                                                {{-- Period 4 --}}
                                                <th class="border-[2px] border-[#0C1E40] bg-slate-50 text-[#0C1E40] px-1.5 py-2 font-bold w-[14.8%]">
                                                    <div class="h-5 w-5 mx-auto rounded-full bg-[#0C1E40] text-amber-300 font-black text-xs flex items-center justify-center">4</div>
                                                    <div class="text-[10px] text-slate-700 font-bold whitespace-nowrap mt-1">9:40am &ndash; 10:10am</div>
                                                </th>
                                                {{-- Period 5 --}}
                                                <th class="border-[2px] border-[#0C1E40] bg-slate-50 text-[#0C1E40] px-1.5 py-2 font-bold w-[14.8%]">
                                                    <div class="h-5 w-5 mx-auto rounded-full bg-[#0C1E40] text-amber-300 font-black text-xs flex items-center justify-center">5</div>
                                                    <div class="text-[10px] text-slate-700 font-bold whitespace-nowrap mt-1">10:10am &ndash; 10:40am</div>
                                                </th>
                                                {{-- Period 6 --}}
                                                <th class="border-[2px] border-[#0C1E40] bg-slate-50 text-[#0C1E40] px-1.5 py-2 font-bold w-[14.8%]">
                                                    <div class="h-5 w-5 mx-auto rounded-full bg-[#0C1E40] text-amber-300 font-black text-xs flex items-center justify-center">6</div>
                                                    <div class="text-[10px] text-slate-700 font-bold whitespace-nowrap mt-1">10:40am &ndash; 1:10pm</div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-xs">
                                            @php
                                                $weekdays = [
                                                    1 => 'MONDAY',
                                                    2 => 'TUESDAY',
                                                    3 => 'WEDNESDAY',
                                                    4 => 'THURSDAY',
                                                    5 => 'FRIDAY',
                                                ];
                                            @endphp

                                            @foreach($weekdays as $dayNumber => $dayName)
                                                <tr class="h-16">
                                                    {{-- Day Column --}}
                                                    <td class="border-[2px] border-[#0C1E40] px-2 py-2 font-black text-[#0C1E40] bg-slate-50/70 text-center tracking-wider text-[11px] uppercase">
                                                        {{ $dayName }}
                                                    </td>

                                                    {{-- Period 1 (idx 0) --}}
                                                    @php $slot0 = $conventionalMap[$dayNumber][0] ?? null; @endphp
                                                    <td class="border-[2px] border-[#0C1E40] p-1.5 align-middle relative group bg-white {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-50/60' : '' }}"
                                                        @if($isAdmin && !$isStaffScope)
                                                            @if($slot0)
                                                                wire:click="edit({{ $slot0->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @else
                                                                wire:click="selectSlot({{ $dayNumber }}, '08:00', '08:30')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif
                                                        @endif>
                                                        @if($slot0 && !$slot0->is_break)
                                                            <div class="p-0.5 flex flex-col items-center justify-center">
                                                                <div class="font-black text-[#0C1E40] text-xs leading-snug tracking-tight">
                                                                    {{ $slot0->subject?->name ?? 'Untitled' }}
                                                                </div>
                                                                @if($slot0->teacher?->name)
                                                                    <div class="mt-1 text-[9.5px] font-bold text-slate-600 bg-slate-100 rounded px-1.5 py-0.5 truncate max-w-full">
                                                                        {{ $slot0->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($slot0->room)
                                                                    <div class="text-[8.5px] text-slate-400 font-semibold mt-0.5">Rm {{ $slot0->room }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 font-serif italic text-base group-hover:text-sky-600 select-none transition-colors">&mdash;</span>
                                                        @endif
                                                    </td>

                                                    {{-- Period 2 (idx 1) --}}
                                                    @php $slot1 = $conventionalMap[$dayNumber][1] ?? null; @endphp
                                                    <td class="border-[2px] border-[#0C1E40] p-1.5 align-middle relative group bg-white {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-50/60' : '' }}"
                                                        @if($isAdmin && !$isStaffScope)
                                                            @if($slot1)
                                                                wire:click="edit({{ $slot1->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @else
                                                                wire:click="selectSlot({{ $dayNumber }}, '08:30', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif
                                                        @endif>
                                                        @if($slot1 && !$slot1->is_break)
                                                            <div class="p-0.5 flex flex-col items-center justify-center">
                                                                <div class="font-black text-[#0C1E40] text-xs leading-snug tracking-tight">
                                                                    {{ $slot1->subject?->name ?? 'Untitled' }}
                                                                </div>
                                                                @if($slot1->teacher?->name)
                                                                    <div class="mt-1 text-[9.5px] font-bold text-slate-600 bg-slate-100 rounded px-1.5 py-0.5 truncate max-w-full">
                                                                        {{ $slot1->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($slot1->room)
                                                                    <div class="text-[8.5px] text-slate-400 font-semibold mt-0.5">Rm {{ $slot1->room }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 font-serif italic text-base group-hover:text-sky-600 select-none transition-colors">&mdash;</span>
                                                        @endif
                                                    </td>

                                                    {{-- Period 3 (idx 2) --}}
                                                    @php $slot2 = $conventionalMap[$dayNumber][2] ?? null; @endphp
                                                    <td class="border-[2px] border-[#0C1E40] p-1.5 align-middle relative group bg-white {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-50/60' : '' }}"
                                                        @if($isAdmin && !$isStaffScope)
                                                            @if($slot2)
                                                                wire:click="edit({{ $slot2->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @else
                                                                wire:click="selectSlot({{ $dayNumber }}, '09:00', '09:30')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif
                                                        @endif>
                                                        @if($slot2 && !$slot2->is_break)
                                                            <div class="p-0.5 flex flex-col items-center justify-center">
                                                                <div class="font-black text-[#0C1E40] text-xs leading-snug tracking-tight">
                                                                    {{ $slot2->subject?->name ?? 'Untitled' }}
                                                                </div>
                                                                @if($slot2->teacher?->name)
                                                                    <div class="mt-1 text-[9.5px] font-bold text-slate-600 bg-slate-100 rounded px-1.5 py-0.5 truncate max-w-full">
                                                                        {{ $slot2->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($slot2->room)
                                                                    <div class="text-[8.5px] text-slate-400 font-semibold mt-0.5">Rm {{ $slot2->room }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 font-serif italic text-base group-hover:text-sky-600 select-none transition-colors">&mdash;</span>
                                                        @endif
                                                    </td>

                                                    {{-- Break Column with Rowspan=5 (Only output in Monday row) --}}
                                                    @if($dayNumber === 1)
                                                        @php $breakEntry = $conventionalMap[1][3] ?? null; @endphp
                                                        <td rowspan="5" class="border-[2px] border-[#0C1E40] bg-amber-50/70 p-1 text-center align-middle relative group select-none {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-100/60' : '' }}"
                                                            @if($isAdmin && !$isStaffScope && $breakEntry)
                                                                wire:click="edit({{ $breakEntry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @elseif($isAdmin && !$isStaffScope)
                                                                wire:click="selectSlot(1, '09:30', '09:40')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif>
                                                            <div class="flex flex-col items-center justify-center h-full py-4 space-y-2">
                                                                <span class="text-xs font-black tracking-[0.25em] text-amber-950 uppercase [writing-mode:vertical-rl] rotate-180 transform">
                                                                    BREAK
                                                                </span>
                                                                <span class="text-[9.5px] font-extrabold text-amber-800 tracking-wider [writing-mode:vertical-rl] rotate-180 transform mt-3 whitespace-nowrap">
                                                                    9:30am &ndash; 9:40am
                                                                </span>
                                                            </div>
                                                        </td>
                                                    @endif

                                                    {{-- Period 4 (idx 4) --}}
                                                    @php $slot4 = $conventionalMap[$dayNumber][4] ?? null; @endphp
                                                    <td class="border-[2px] border-[#0C1E40] p-1.5 align-middle relative group bg-white {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-50/60' : '' }}"
                                                        @if($isAdmin && !$isStaffScope)
                                                            @if($slot4)
                                                                wire:click="edit({{ $slot4->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @else
                                                                wire:click="selectSlot({{ $dayNumber }}, '09:40', '10:10')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif
                                                        @endif>
                                                        @if($slot4 && !$slot4->is_break)
                                                            <div class="p-0.5 flex flex-col items-center justify-center">
                                                                <div class="font-black text-[#0C1E40] text-xs leading-snug tracking-tight">
                                                                    {{ $slot4->subject?->name ?? 'Untitled' }}
                                                                </div>
                                                                @if($slot4->teacher?->name)
                                                                    <div class="mt-1 text-[9.5px] font-bold text-slate-600 bg-slate-100 rounded px-1.5 py-0.5 truncate max-w-full">
                                                                        {{ $slot4->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($slot4->room)
                                                                    <div class="text-[8.5px] text-slate-400 font-semibold mt-0.5">Rm {{ $slot4->room }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 font-serif italic text-base group-hover:text-sky-600 select-none transition-colors">&mdash;</span>
                                                        @endif
                                                    </td>

                                                    {{-- Period 5 (idx 5) --}}
                                                    @php $slot5 = $conventionalMap[$dayNumber][5] ?? null; @endphp
                                                    <td class="border-[2px] border-[#0C1E40] p-1.5 align-middle relative group bg-white {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-50/60' : '' }}"
                                                        @if($isAdmin && !$isStaffScope)
                                                            @if($slot5)
                                                                wire:click="edit({{ $slot5->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @else
                                                                wire:click="selectSlot({{ $dayNumber }}, '10:10', '10:40')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif
                                                        @endif>
                                                        @if($slot5 && !$slot5->is_break)
                                                            <div class="p-0.5 flex flex-col items-center justify-center">
                                                                <div class="font-black text-[#0C1E40] text-xs leading-snug tracking-tight">
                                                                    {{ $slot5->subject?->name ?? 'Untitled' }}
                                                                </div>
                                                                @if($slot5->teacher?->name)
                                                                    <div class="mt-1 text-[9.5px] font-bold text-slate-600 bg-slate-100 rounded px-1.5 py-0.5 truncate max-w-full">
                                                                        {{ $slot5->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($slot5->room)
                                                                    <div class="text-[8.5px] text-slate-400 font-semibold mt-0.5">Rm {{ $slot5->room }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 font-serif italic text-base group-hover:text-sky-600 select-none transition-colors">&mdash;</span>
                                                        @endif
                                                    </td>

                                                    {{-- Period 6 (idx 6) --}}
                                                    @php $slot6 = $conventionalMap[$dayNumber][6] ?? null; @endphp
                                                    <td class="border-[2px] border-[#0C1E40] p-1.5 align-middle relative group bg-white {{ $isAdmin && !$isStaffScope ? 'cursor-pointer hover:bg-amber-50/60' : '' }}"
                                                        @if($isAdmin && !$isStaffScope)
                                                            @if($slot6)
                                                                wire:click="edit({{ $slot6->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @else
                                                                wire:click="selectSlot({{ $dayNumber }}, '10:40', '13:10')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')"
                                                            @endif
                                                        @endif>
                                                        @if($slot6 && !$slot6->is_break)
                                                            <div class="p-0.5 flex flex-col items-center justify-center">
                                                                <div class="font-black text-[#0C1E40] text-xs leading-snug tracking-tight">
                                                                    {{ $slot6->subject?->name ?? 'Untitled' }}
                                                                </div>
                                                                @if($slot6->teacher?->name)
                                                                    <div class="mt-1 text-[9.5px] font-bold text-slate-600 bg-slate-100 rounded px-1.5 py-0.5 truncate max-w-full">
                                                                        {{ $slot6->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($slot6->room)
                                                                    <div class="text-[8.5px] text-slate-400 font-semibold mt-0.5">Rm {{ $slot6->room }}</div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-slate-300 font-serif italic text-base group-hover:text-sky-600 select-none transition-colors">&mdash;</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- ══════ NOTES ROW (Exact icons & text match) ══════ --}}
                                <div class="mt-4 pt-2.5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs text-[#0C1E40]">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-[#0C1E40] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="font-black">NOTE: Period 1 &ndash; 5 are 30 minutes each</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-[#0C1E40] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="font-black">Period 6 is 2 hours 30 minutes (10:40am &ndash; 1:10pm)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-[#0C1E40] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="font-black">School closes by 1:10pm</span>
                                    </div>
                                </div>

                                {{-- ══════ SIGNATURE LINES (Exact underline match) ══════ --}}
                                <div class="grid grid-cols-2 gap-12 mt-8 pt-4 px-6 sm:px-16">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs font-black uppercase text-[#0C1E40] tracking-wider whitespace-nowrap">CLASS TEACHER'S SIGNATURE:</span>
                                        <div class="border-b-[1.5px] border-[#0C1E40] flex-1"></div>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xs font-black uppercase text-[#0C1E40] tracking-wider whitespace-nowrap">PRINCIPAL'S SIGNATURE:</span>
                                        <div class="border-b-[1.5px] border-[#0C1E40] flex-1"></div>
                                    </div>
                                </div>

                                {{-- ══════ BOTTOM CURVE FLOURISHES & MOTTO BANNER (100% Poster Replica) ══════ --}}
                                <div class="mt-8 pt-2 flex items-center justify-center">
                                    <div class="flex items-center justify-center gap-4 w-full max-w-2xl px-4">
                                        {{-- Left Golden Curve Flourish SVG (Exact match to poster) --}}
                                        <div class="flex-1 flex items-center justify-end">
                                            <svg class="w-full max-w-[140px] h-5 text-[#C59B27]" viewBox="0 0 140 20" fill="none">
                                                <path d="M0 10 C30 10, 50 3, 90 3 C115 3, 130 12, 140 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M35 15 C65 15, 85 8, 120 8 C132 8, 138 12, 140 12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" opacity="0.7"/>
                                                <circle cx="138" cy="10" r="2.5" fill="currentColor"/>
                                            </svg>
                                        </div>

                                        {{-- Centered Motto Ribbon / Text --}}
                                        <div class="px-3 py-0.5 text-center whitespace-nowrap">
                                            <span class="text-xs sm:text-sm font-black tracking-[0.25em] text-[#0C1E40] uppercase font-serif">
                                                {{ $this->schoolInfo['motto'] }}
                                            </span>
                                        </div>

                                        {{-- Right Golden Curve Flourish SVG (Mirrored) --}}
                                        <div class="flex-1 flex items-center justify-start">
                                            <svg class="w-full max-w-[140px] h-5 text-[#C59B27] scale-x-[-1]" viewBox="0 0 140 20" fill="none">
                                                <path d="M0 10 C30 10, 50 3, 90 3 C115 3, 130 12, 140 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M35 15 C65 15, 85 8, 120 8 C132 8, 138 12, 140 12" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" opacity="0.7"/>
                                                <circle cx="138" cy="10" r="2.5" fill="currentColor"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dedicated Print Style Sheet: Isolate the poster and force landscape page format --}}
                <style>
                    @media print {
                        @page {
                            size: A4 landscape;
                            margin: 4mm 6mm;
                        }
                        html, body {
                            background: #ffffff !important;
                            color: #000000 !important;
                            margin: 0 !important;
                            padding: 0 !important;
                            width: 100% !important;
                            height: auto !important;
                            min-height: 0 !important;
                            overflow: visible !important;
                        }
                        /* Explicitly kill all top navigation, sidebars, modals, floating buttons, offline/broadcast bars */
                        header, 
                        aside, 
                        #mobileSidebar, 
                        nav, 
                        .bottom-nav, 
                        footer, 
                        #biometric-popup-root,
                        .x-page-header, 
                        .print\:hidden,
                        [wire\:offline],
                        #subscription-expired-modal {
                            display: none !important;
                            visibility: hidden !important;
                            height: 0 !important;
                            width: 0 !important;
                            overflow: hidden !important;
                        }
                        /* Reset App Shell Flex/Overflow */
                        #app {
                            display: block !important;
                            position: static !important;
                            height: auto !important;
                            min-height: auto !important;
                            overflow: visible !important;
                            padding: 0 !important;
                            margin: 0 !important;
                        }
                        #app > div.flex.flex-1 {
                            display: block !important;
                            height: auto !important;
                            overflow: visible !important;
                            padding: 0 !important;
                            margin: 0 !important;
                        }
                        main {
                            display: block !important;
                            position: static !important;
                            height: auto !important;
                            overflow: visible !important;
                            padding: 0 !important;
                            margin: 0 !important;
                        }
                        /* Poster container fills landscape page cleanly */
                        #conventional-poster-canvas {
                            display: block !important;
                            position: relative !important;
                            width: 100% !important;
                            max-width: 100% !important;
                            margin: 0 auto !important;
                            padding: 0 !important;
                            box-shadow: none !important;
                            border: none !important;
                            background: #ffffff !important;
                            overflow: visible !important;
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                        }
                        .poster-outer-frame {
                            min-width: 0 !important;
                            width: 100% !important;
                            box-shadow: none !important;
                            padding: 2.5mm !important;
                            border-width: 3.5px !important;
                            border-color: #C59B27 !important;
                            border-style: solid !important;
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                        }
                        .poster-inner-frame {
                            padding: 3mm 4mm !important;
                            border-width: 2px !important;
                            border-color: #0C1E40 !important;
                            border-style: solid !important;
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                        }
                        /* Ensure table borders and background colors print accurately */
                        * {
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                    }
                </style>
            @elseif($viewMode === 'grid')
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
                                                                <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="group relative w-full text-left rounded-xl border border-slate-200/90 border-l-[4px] {{ $cardBorder }} p-2.5 transition hover:shadow-xs hover:border-slate-300 cursor-pointer">
                                                                    <div class="flex items-start justify-between gap-1">
                                                                        <div class="text-xs font-bold text-slate-800 truncate leading-snug">{{ $entry->subject?->name ?? 'Untitled' }}</div>
                                                                        <span class="opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 group-hover:text-sky-600 shrink-0">
                                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                                        </span>
                                                                    </div>
                                                                    @if($isStaffScope)
                                                                        <div class="text-[11px] font-semibold text-sky-700 truncate mt-1">
                                                                            {{ $entry->schoolClass?->name ?? 'Class' }}{{ $entry->section ? ' · ' . $entry->section->name : '' }}
                                                                        </div>
                                                                    @else
                                                                        @if($entry->teacher?->name)
                                                                            <div class="text-[11px] text-slate-600 truncate mt-1 flex items-center gap-1">
                                                                                <svg class="h-3 w-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                                                <span>{{ $entry->teacher->name }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                    @if($entry->room)
                                                                        <div class="text-[10px] text-slate-500 font-medium truncate mt-1 inline-flex items-center gap-1 bg-white/70 px-1.5 py-0.5 rounded border border-slate-200/60">
                                                                            <svg class="h-2.5 w-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                                            <span>Room {{ $entry->room }}</span>
                                                                        </div>
                                                                    @endif
                                                                </button>
                                                            @else
                                                                <div class="w-full text-left rounded-xl border border-slate-200/90 border-l-[4px] {{ $cardBorder }} p-2.5">
                                                                    <div class="text-xs font-bold text-slate-800 truncate leading-snug">{{ $entry->subject?->name ?? 'Untitled' }}</div>
                                                                    @if($isStaffScope)
                                                                        <div class="text-[11px] font-semibold text-sky-700 truncate mt-1">
                                                                            {{ $entry->schoolClass?->name ?? 'Class' }}{{ $entry->section ? ' · ' . $entry->section->name : '' }}
                                                                        </div>
                                                                    @else
                                                                        @if($entry->teacher?->name)
                                                                            <div class="text-[11px] text-slate-600 truncate mt-1 flex items-center gap-1">
                                                                                <svg class="h-3 w-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                                                <span>{{ $entry->teacher->name }}</span>
                                                                            </div>
                                                                        @endif
                                                                    @endif
                                                                    @if($entry->room)
                                                                        <div class="text-[10px] text-slate-500 font-medium truncate mt-1 inline-flex items-center gap-1 bg-white/70 px-1.5 py-0.5 rounded border border-slate-200/60">
                                                                            <svg class="h-2.5 w-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                                            <span>Room {{ $entry->room }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if($isAdmin && !$isStaffScope)
                                                            <button type="button" wire:click="selectSlot({{ $d['day'] }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="group w-full h-full min-h-[58px] rounded-xl border-2 border-dashed border-slate-200 hover:border-sky-400 hover:bg-sky-50/40 p-2 text-slate-300 hover:text-sky-600 transition-all flex flex-col items-center justify-center gap-1 cursor-pointer" title="Click to add period">
                                                                <svg class="h-4 w-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                                <span class="text-[9.5px] font-bold tracking-tight opacity-0 group-hover:opacity-100 transition-opacity">Add</span>
                                                            </button>
                                                        @else
                                                            <div class="w-full h-full min-h-[58px] flex items-center justify-center text-xs text-slate-200 font-light">
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
        <div x-data="{ open: false }" 
             x-on:open-modal.window="if ($event.detail === 'timetable-form') open = true" 
             x-on:close-modal.window="if ($event.detail === 'timetable-form' || !$event.detail) open = false" 
             x-on:close.window="open = false" 
             x-show="open" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;" 
             role="dialog" 
             aria-modal="true">
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

                        {{-- Quick Period Presets --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[11px] font-bold text-slate-700">Quick Period Presets</label>
                                <span class="text-[10px] text-slate-400">Auto-fill timetable times</span>
                            </div>
                            <div class="grid grid-cols-7 gap-1">
                                <button type="button" wire:click="setPeriodPreset('1')" class="py-1 px-1 text-center rounded-lg border border-slate-200 hover:border-sky-500 hover:bg-sky-50 text-[10px] font-bold text-slate-700 transition" title="Period 1: 8:00 – 8:30">P1</button>
                                <button type="button" wire:click="setPeriodPreset('2')" class="py-1 px-1 text-center rounded-lg border border-slate-200 hover:border-sky-500 hover:bg-sky-50 text-[10px] font-bold text-slate-700 transition" title="Period 2: 8:30 – 9:00">P2</button>
                                <button type="button" wire:click="setPeriodPreset('3')" class="py-1 px-1 text-center rounded-lg border border-slate-200 hover:border-sky-500 hover:bg-sky-50 text-[10px] font-bold text-slate-700 transition" title="Period 3: 9:00 – 9:30">P3</button>
                                <button type="button" wire:click="setPeriodPreset('break')" class="py-1 px-1 text-center rounded-lg border border-amber-300 hover:border-amber-500 bg-amber-50/70 hover:bg-amber-100 text-[10px] font-bold text-amber-900 transition" title="Break: 9:30 – 9:40">Break</button>
                                <button type="button" wire:click="setPeriodPreset('4')" class="py-1 px-1 text-center rounded-lg border border-slate-200 hover:border-sky-500 hover:bg-sky-50 text-[10px] font-bold text-slate-700 transition" title="Period 4: 9:40 – 10:10">P4</button>
                                <button type="button" wire:click="setPeriodPreset('5')" class="py-1 px-1 text-center rounded-lg border border-slate-200 hover:border-sky-500 hover:bg-sky-50 text-[10px] font-bold text-slate-700 transition" title="Period 5: 10:10 – 10:40">P5</button>
                                <button type="button" wire:click="setPeriodPreset('6')" class="py-1 px-1 text-center rounded-lg border border-slate-200 hover:border-sky-500 hover:bg-sky-50 text-[10px] font-bold text-slate-700 transition" title="Period 6: 10:40 – 13:10">P6</button>
                            </div>
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
                                <button type="button" wire:click="delete({{ $editingId }})" wire:loading.attr="disabled" onclick="return confirm('Delete this timetable entry?')" class="rounded-xl px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50 transition cursor-pointer">
                                    Delete
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" x-on:click="open = false" class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="button" wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 px-4 py-2 text-xs font-bold text-white transition shadow-xs disabled:opacity-50 cursor-pointer">
                                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update Entry' : 'Save Entry' }}</span>
                                <span wire:loading wire:target="save" class="inline-flex items-center gap-1">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Saving...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
