<div class="p-6 space-y-6 font-sans bg-[#f5f6fa]">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white border border-slate-200/50 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse shadow-[0_0_8px_rgba(99,102,241,0.5)]"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Analytics Suite</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Student Performance Analytics</h1>
            <p class="text-xs font-semibold text-slate-500 mt-0.5">Analyze academic trends, strengths, weaknesses, and classroom engagement</p>
        </div>
        
        {{-- Quick Stats Summary Badges --}}
        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Term: {{ $selectedTerm == 1 ? '1st' : ($selectedTerm == 2 ? '2nd' : '3rd') }} Term</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Student Selection Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200/50 rounded-3xl shadow-sm p-6 sticky top-6 space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Select Student
                    </h2>
                    <span class="text-[10px] font-black uppercase bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                        {{ count($this->students) }} Listed
                    </span>
                </div>
                
                {{-- Filters --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Class Filter</label>
                        <div class="relative">
                            <select wire:model.live="selectedClass" class="w-full text-xs font-bold text-slate-700 bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 shadow-sm transition-all focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none appearance-none">
                                <option value="">All Classes</option>
                                @foreach($this->classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Search Name / Admission</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchTerm" 
                                   placeholder="Type to search..."
                                   class="w-full text-xs font-bold text-slate-700 placeholder-slate-400 bg-slate-50/50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 shadow-sm transition-all focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Academic Term</label>
                        <div class="relative">
                            <select wire:model.live="selectedTerm" class="w-full text-xs font-bold text-slate-700 bg-slate-50/50 border border-slate-200 rounded-xl px-3.5 py-2.5 shadow-sm transition-all focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none appearance-none">
                                @foreach($this->availableTerms as $term)
                                    <option value="{{ $term['value'] }}">{{ $term['label'] }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Student List --}}
                <div class="pt-3 border-t border-slate-100">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-2">Student Directory</label>
                    <div class="space-y-2 max-h-[340px] overflow-y-auto pr-1 sidebar-scroll">
                        @forelse($this->students as $student)
                            <button wire:click="selectStudent({{ $student->id }})" 
                                    class="relative overflow-hidden w-full text-left p-3.5 rounded-2xl border transition-all duration-300 flex flex-col gap-0.5 group {{ $selectedStudent === $student->id ? 'bg-indigo-50/50 border-indigo-200 shadow-sm ring-1 ring-indigo-500/10' : 'bg-slate-50/40 border-slate-100 hover:bg-slate-50 hover:border-slate-300 hover:shadow-sm' }}">
                                @if($selectedStudent === $student->id)
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 rounded-r-md"></div>
                                @endif
                                <p class="text-xs font-black transition-colors {{ $selectedStudent === $student->id ? 'text-indigo-900' : 'text-slate-800 group-hover:text-slate-900' }}">{{ $student->full_name }}</p>
                                <div class="flex justify-between items-center w-full mt-1">
                                    <span class="text-[10px] font-bold text-slate-500">{{ $student->admission_number }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider {{ $selectedStudent === $student->id ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200/60 text-slate-600 group-hover:bg-slate-200' }}">
                                        {{ $student->schoolClass?->name }}
                                    </span>
                                </div>
                            </button>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <p class="text-xs font-bold text-slate-400">No active students match your filters.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance Display Panel --}}
        <div class="lg:col-span-2">
            @if($this->selectedStudentModel)
                @php
                    $perfData = $this->performanceData;
                    $hasScores = isset($perfData['overview']) && $perfData['overview']['total_subjects'] > 0;
                @endphp

                @if($hasScores)
                <div class="space-y-6">
                    {{-- Student Profile Hero Header --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#17274E] to-[#1D3261] shadow-xl p-6 text-white">
                        {{-- Background details --}}
                        <div class="absolute inset-0 pointer-events-none opacity-20 mix-blend-screen bg-[radial-gradient(circle,#ffffff_1.5px,transparent_1.5px)]" style="background-size: 24px 24px;"></div>
                        <div class="absolute right-0 top-0 bottom-0 w-32 opacity-10 pointer-events-none">
                            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                <circle cx="80" cy="50" r="45" stroke="white" stroke-width="0.5"/>
                                <circle cx="80" cy="50" r="30" stroke="white" stroke-width="0.5"/>
                            </svg>
                        </div>

                        <div class="relative z-10 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 ring-2 ring-white/10 text-xl font-black text-white shadow-md">
                                    {{ mb_strtoupper(mb_substr($this->selectedStudentModel->first_name, 0, 1)) }}{{ mb_strtoupper(mb_substr($this->selectedStudentModel->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="inline-flex items-center gap-1 bg-emerald-500/20 backdrop-blur-sm text-[9px] font-black uppercase tracking-widest text-[#34d399] px-2.5 py-1 rounded-full mb-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Selected Student
                                    </span>
                                    <h2 class="text-2xl font-black tracking-tight leading-none text-white">{{ $this->selectedStudentModel->full_name }}</h2>
                                    <p class="text-xs font-semibold text-blue-200 mt-1.5 flex items-center gap-2">
                                        <span>ID: {{ $this->selectedStudentModel->admission_number }}</span>
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-300/40"></span>
                                        <span>Class: {{ $this->selectedStudentModel->schoolClass?->name }}</span>
                                    </p>
                                </div>
                            </div>
                            <button wire:click="clearSelection" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white hover:bg-white/20 transition-all border border-white/10 hover:scale-105 shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Performance Metrics Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {{-- Term Average --}}
                        <div class="bg-white border border-slate-200/50 rounded-2xl p-5 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[110px]">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Term Average</span>
                                <div class="bg-indigo-50 p-2 rounded-xl text-indigo-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h3 class="text-2xl font-black text-slate-900 leading-none">{{ $perfData['overview']['average_score'] }}</h3>
                                <p class="text-[11px] font-bold text-indigo-600 mt-1">{{ $perfData['overview']['percentage'] }}% average score</p>
                            </div>
                        </div>

                        {{-- Grade --}}
                        <div class="bg-white border border-slate-200/50 rounded-2xl p-5 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[110px]">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Class Grade</span>
                                <div class="bg-purple-50 p-2 rounded-xl text-purple-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h3 class="text-2xl font-black text-slate-900 leading-none">{{ $perfData['overview']['grade'] }}</h3>
                                <p class="text-[11px] font-bold text-purple-600 mt-1">Current Rating</p>
                            </div>
                        </div>

                        {{-- Passed --}}
                        <div class="bg-white border border-slate-200/50 rounded-2xl p-5 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[110px]">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Passed Subjects</span>
                                <div class="bg-emerald-50 p-2 rounded-xl text-emerald-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h3 class="text-2xl font-black text-slate-900 leading-none">{{ $perfData['overview']['subjects_passed'] }}</h3>
                                <p class="text-[11px] font-bold text-emerald-600 mt-1">of {{ $perfData['overview']['total_subjects'] }} subjects</p>
                            </div>
                        </div>

                        {{-- Attendance Rate --}}
                        <div class="bg-white border border-slate-200/50 rounded-2xl p-5 shadow-sm relative overflow-hidden flex flex-col justify-between min-h-[110px]">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Attendance</span>
                                <div class="bg-orange-50 p-2 rounded-xl text-orange-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h3 class="text-2xl font-black text-slate-900 leading-none">{{ $perfData['attendance_impact']['attendance_rate'] }}%</h3>
                                <p class="text-[11px] font-bold text-orange-600 mt-1">{{ $perfData['attendance_impact']['present_days'] }}/{{ $perfData['attendance_impact']['total_days'] }} active days</p>
                            </div>
                        </div>
                    </div>

                    {{-- Strengths & Weaknesses --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Strengths Card --}}
                        <div class="bg-white border border-slate-200/50 rounded-3xl p-6 shadow-sm space-y-4">
                            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                                <div class="h-6 w-6 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                Academic Strengths
                            </h3>
                            @if($perfData['strengths_weaknesses']['strengths']->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($perfData['strengths_weaknesses']['strengths'] as $strength)
                                        <div class="relative overflow-hidden bg-slate-50/50 hover:bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center justify-between transition-all hover:shadow-sm">
                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                                            <div class="pl-1">
                                                <p class="text-xs font-black text-slate-800">{{ $strength['subject'] }}</p>
                                                <p class="text-[10px] font-bold text-slate-500 mt-0.5">Grade {{ $strength['grade'] }} • CA & Exam Avg</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-base font-black text-emerald-600">{{ $strength['score'] }}</span>
                                                <p class="text-[9px] font-bold text-slate-400">{{ $strength['percentage'] }}%</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <p class="text-xs font-bold text-slate-400">No strengths logged (scores above 70%) for this term.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Needs Attention Card --}}
                        <div class="bg-white border border-slate-200/50 rounded-3xl p-6 shadow-sm space-y-4">
                            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                                <div class="h-6 w-6 rounded-lg bg-rose-50 flex items-center justify-center text-rose-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                </div>
                                Needs Attention
                            </h3>
                            @if($perfData['strengths_weaknesses']['weaknesses']->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($perfData['strengths_weaknesses']['weaknesses'] as $weakness)
                                        <div class="relative overflow-hidden bg-slate-50/50 hover:bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center justify-between transition-all hover:shadow-sm">
                                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-rose-500"></div>
                                            <div class="pl-1">
                                                <p class="text-xs font-black text-slate-800">{{ $weakness['subject'] }}</p>
                                                <p class="text-[10px] font-bold text-slate-500 mt-0.5">Grade {{ $weakness['grade'] }} • CA & Exam Avg</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-base font-black text-rose-600">{{ $weakness['score'] }}</span>
                                                <p class="text-[9px] font-bold text-slate-400">{{ $weakness['percentage'] }}%</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <p class="text-xs font-bold text-slate-400">Excellent! No weak areas (scores below 60%) identified.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Subject Performance Table --}}
                    <div class="bg-white border border-slate-200/50 rounded-3xl overflow-hidden shadow-sm">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-sm font-extrabold text-slate-900">Subject Breakdown</h3>
                            <span class="text-[10px] font-black uppercase bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-full">CA & Exam Details</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50/70">
                                    <tr>
                                        <th class="px-6 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Subject</th>
                                        <th class="px-6 py-3.5 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">CA 1</th>
                                        <th class="px-6 py-3.5 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">CA 2</th>
                                        <th class="px-6 py-3.5 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Exam</th>
                                        <th class="px-6 py-3.5 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Total</th>
                                        <th class="px-6 py-3.5 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Grade</th>
                                        <th class="px-6 py-3.5 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">%</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($perfData['subject_performance'] as $subject)
                                        <tr class="hover:bg-slate-50/40 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-slate-800">{{ $subject['subject'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center text-slate-600 font-bold">{{ $subject['ca1'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center text-slate-600 font-bold">{{ $subject['ca2'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center text-slate-600 font-bold">{{ $subject['exam'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center font-extrabold text-slate-900">{{ $subject['total'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @php
                                                    $gColor = match($subject['grade']) {
                                                        'A'     => 'emerald',
                                                        'B'     => 'teal',
                                                        'C'     => 'indigo',
                                                        'D'     => 'amber',
                                                        'E'     => 'orange',
                                                        'F'     => 'rose',
                                                        default => 'slate'
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-{{ $gColor }}-50 text-{{ $gColor }}-700 border border-{{ $gColor }}-100 shadow-sm">
                                                    {{ $subject['grade'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-center text-slate-700 font-black">{{ $subject['percentage'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Progress Analysis (Improvement Areas) --}}
                    <div class="bg-white border border-slate-200/50 rounded-3xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                <div class="h-6 w-6 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-500">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                Academic Progress Analysis
                            </h3>
                            <span class="text-[10px] font-black uppercase bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">Term-over-Term Trend</span>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-3.5">
                            @foreach($perfData['improvement_areas'] as $area)
                                <div class="relative overflow-hidden border rounded-2xl p-4 transition-all duration-300 hover:shadow-sm {{ $area['needs_attention'] ? 'border-rose-100 bg-rose-50/20' : 'border-slate-100 bg-slate-50/30' }}">
                                    @if($area['needs_attention'])
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-rose-500"></div>
                                    @endif
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-black text-slate-800">{{ $area['subject'] }}</p>
                                            <div class="flex items-center gap-3.5 mt-1.5 text-[10px] font-bold text-slate-500">
                                                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Previous: {{ $area['previous_score'] }}</span>
                                                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span> Current: {{ $area['current_score'] }}</span>
                                                <span class="inline-flex items-center gap-0.5 {{ $area['change'] > 0 ? 'text-emerald-600' : ($area['change'] < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                                                    {{ $area['change'] > 0 ? '+' : '' }}{{ $area['change'] }} pts
                                                    @if($area['change'] > 0)
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                                    @elseif($area['change'] < 0)
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            @php
                                                $tColor = match($area['trend']) {
                                                    'Improving' => 'emerald',
                                                    'Declining' => 'rose',
                                                    default     => 'slate'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase bg-{{ $tColor }}-50 text-{{ $tColor }}-700 border border-{{ $tColor }}-100 shadow-sm">
                                                {{ $area['trend'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Attendance Impact Analysis --}}
                    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1E293B] to-[#0F172A] border border-slate-800 p-6 text-white shadow-lg">
                        {{-- Stars/Dots pattern --}}
                        <div class="absolute inset-0 pointer-events-none opacity-10 mix-blend-screen bg-[radial-gradient(circle,#ffffff_1.5px,transparent_1.5px)]" style="background-size: 16px 16px;"></div>
                        
                        <div class="relative z-10 flex flex-col md:flex-row gap-6 justify-between items-start md:items-center">
                            <div class="max-w-md">
                                <h3 class="text-sm font-black uppercase tracking-wider text-indigo-400 mb-1">Attendance Correlation</h3>
                                <h4 class="text-base font-extrabold text-white">Engagement Impact Analysis</h4>
                                <p class="text-xs font-semibold text-slate-300 mt-2 leading-relaxed">
                                    {{ $perfData['attendance_impact']['correlation'] }}
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-4 gap-3 bg-white/5 border border-white/10 rounded-2xl p-4 w-full md:w-auto shrink-0 shadow-inner">
                                <div class="text-center px-2">
                                    <p class="text-lg font-black text-indigo-400 leading-none">{{ $perfData['attendance_impact']['present_days'] }}</p>
                                    <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 mt-1">Present</p>
                                </div>
                                <div class="text-center px-2 border-l border-white/10">
                                    <p class="text-lg font-black text-rose-400 leading-none">{{ $perfData['attendance_impact']['absent_days'] }}</p>
                                    <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 mt-1">Absent</p>
                                </div>
                                <div class="text-center px-2 border-l border-white/10">
                                    <p class="text-lg font-black text-amber-400 leading-none">{{ $perfData['attendance_impact']['late_days'] }}</p>
                                    <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 mt-1">Late</p>
                                </div>
                                <div class="text-center px-2 border-l border-white/10">
                                    <p class="text-lg font-black text-[#34d399] leading-none">{{ $perfData['attendance_impact']['attendance_rate'] }}%</p>
                                    <p class="text-[9px] font-black uppercase tracking-wider text-slate-400 mt-1">Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                    {{-- No Performance Data --}}
                    <div class="bg-amber-50/50 border border-amber-100 rounded-3xl p-12 text-center shadow-sm">
                        <div class="h-14 w-14 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4 border border-amber-100 shadow-inner">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 mb-2">No Performance Records Available</h3>
                        <p class="text-xs font-semibold text-slate-600 max-w-md mx-auto mb-6">{{ $this->selectedStudentModel->full_name }} doesn't have any kontinuierliche assessments or exam scores recorded for the selected academic term.</p>
                        
                        <a href="{{ route('results.entry') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-xs font-extrabold shadow-md hover:shadow-lg transition-all hover:scale-[1.02]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Go to Score Entry
                        </a>
                    </div>
                @endif
            @else
                {{-- Empty Selection State --}}
                <div class="bg-white border border-slate-200/50 rounded-3xl p-16 text-center shadow-sm flex flex-col items-center justify-center">
                    <div class="h-16 w-16 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center mb-5 border border-slate-100 shadow-inner">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-1.5">No Student Selected</h3>
                    <p class="text-xs font-semibold text-slate-500 max-w-xs leading-relaxed">Choose a student from the directory sidebar to generate a comprehensive diagnostic performance dashboard.</p>
                </div>
            @endif
        </div>
    </div>
</div>
