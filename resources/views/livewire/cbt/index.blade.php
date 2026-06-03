@php
    $isAdmin = $me?->role === 'admin';
@endphp

<div class="space-y-6">
    {{-- Dynamic Deep Space Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 p-6 sm:p-8 shadow-xl border border-slate-800">
        {{-- Absolute Gradients and Blurs --}}
        <div class="absolute -right-20 -top-20 h-44 w-44 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 h-44 w-44 rounded-full bg-violet-500/10 blur-3xl"></div>
        
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-[10px] font-extrabold text-indigo-400 uppercase tracking-wider border border-indigo-500/20">
                        Administration
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Computer Based Testing (CBT)</h1>
                <p class="mt-1 text-sm text-slate-400 font-medium">Create custom online examinations, manage questions, and review real-time attempts.</p>
            </div>
            <button wire:click="{{ $creating ? 'cancelCreate' : 'startCreate' }}"
        class="relative overflow-hidden group inline-flex items-center gap-2 self-start sm:self-auto rounded-xl px-6 py-3 text-sm font-bold transition-all shadow-md active:scale-95
        {{ $creating ? 'bg-white/10 text-white hover:bg-white/20 border border-white/10' : 'bg-gradient-to-r from-amber-500 to-amber-600 text-white hover:from-amber-400 hover:to-amber-500 shadow-amber-500/10' }}">
    @if(!$creating)
        <svg class="h-4 w-4 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path d="M12 5v14M5 12h14"/>
        </svg>
    @endif
    <span>{{ $creating ? 'Close Panel' : 'New Examination' }}</span>
</button>
        </div>
    </div>

    {{-- Premium Exam Creation Form --}}
    @if ($creating)
        <div class="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden transition-all duration-300">
            <div class="bg-gradient-to-r from-slate-950 to-indigo-950 px-6 py-4 shadow-sm relative overflow-hidden">
                <div class="absolute -right-8 -top-8 h-20 w-20 rounded-full bg-indigo-500/10 blur-xl"></div>
                <div class="relative flex items-center gap-3">
                    <div class="grid h-8 w-8 place-items-center rounded-lg bg-amber-500/10 text-amber-500 border border-amber-500/20">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-black text-white uppercase tracking-wider">New Exam Config</h2>
                </div>
            </div>
            
            <div class="p-6 bg-slate-50/40">
                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Exam Title <span class="text-red-500">*</span></label>
                        <input wire:model="title"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-800 placeholder-slate-400 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                               placeholder="e.g. Mathematics First Term Quiz" autofocus />
                        @error('title') <div class="mt-1.5 text-xs font-bold text-rose-600 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                    </div>

                    {{-- Exam Type --}}
                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Exam Type <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <label class="flex flex-1 cursor-pointer items-center gap-3 rounded-xl border-2 p-3.5 transition-all {{ $examType === 'academic' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                <input type="radio" wire:model.live="examType" value="academic" class="text-indigo-600" />
                                <div>
                                    <div class="text-xs font-black text-slate-800">Academic Exam</div>
                                    <div class="text-[10px] text-slate-500">Assigned to a specific class & subject</div>
                                </div>
                            </label>
                            <label class="flex flex-1 cursor-pointer items-center gap-3 rounded-xl border-2 p-3.5 transition-all {{ $examType === 'aptitude' ? 'border-violet-500 bg-violet-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                <input type="radio" wire:model.live="examType" value="aptitude" class="text-violet-600" />
                                <div>
                                    <div class="text-xs font-black text-slate-800">Aptitude / Entrance Test</div>
                                    <div class="text-[10px] text-slate-500">Open to anyone with the access code</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if($examType === 'academic')
                    <div>
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Target Class <span class="text-red-500">*</span></label>
                        <select wire:model.live="classId"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-800 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none cursor-pointer">
                            <option value="">Select class</option>
                            @foreach ($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('classId') <div class="mt-1.5 text-xs font-bold text-rose-600 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Subject <span class="text-red-500">*</span></label>
                        <select wire:model.live="subjectId" @disabled(!$classId)
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-800 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <option value="">Select subject</option>
                            @foreach ($this->subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subjectId') <div class="mt-1.5 text-xs font-bold text-rose-600 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                    </div>
                    @else
                    <div class="lg:col-span-2 rounded-xl border border-violet-200 bg-violet-50/50 px-4 py-3 text-xs text-violet-700 font-medium">
                        🎯 No class required — anyone with the access code can take this exam.
                    </div>
                    @endif
                    <div>
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Duration (minutes)</label>
                        <input wire:model="durationMinutes" type="number" min="1"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-800 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none" />
                        @error('durationMinutes') <div class="mt-1.5 text-xs font-bold text-rose-600 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Academic Term</label>
                        <select wire:model.live="term"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-800 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none cursor-pointer">
                            <option value="1">First Term</option>
                            <option value="2">Second Term</option>
                            <option value="3">Third Term</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-1.5 block text-xs font-black text-slate-700 uppercase tracking-wider">Academic Session</label>
                        <input wire:model="session"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-800 placeholder-slate-400 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none"
                               placeholder="e.g. 2025/2026" />
                        @error('session') <div class="mt-1.5 text-xs font-bold text-rose-600 flex items-center gap-1">⚠ {{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-between border-t border-slate-200 pt-5">
                    <div>
                        @if ($isAdmin)
                            <p class="text-[10px] font-bold text-slate-500 flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Admin exams bypass approval and go live immediately.
                            </p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="cancelCreate"
                                class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-xs font-extrabold text-slate-700 hover:bg-slate-50 transition-all active:scale-95">
                            Cancel
                        </button>
    <button wire:click="createExam"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-7 py-3 text-sm font-bold text-white hover:from-indigo-500 hover:to-violet-500 shadow-md active:scale-95">
    <span>Create & Add Questions</span>
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path d="M9 5l7 7-7 7"/>
    </svg>
</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="flex items-center justify-between rounded-2xl bg-white px-5 py-4 border border-slate-100 shadow-sm">
        <span class="text-xs font-black text-slate-800 uppercase tracking-wider">{{ $exams->count() }} Examinations</span>
        <select wire:model.live="statusFilter"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/10 cursor-pointer">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="pending_approval">Pending Approval</option>
            <option value="live">Live</option>
            <option value="ended">Ended</option>
        </select>
    </div>

    {{-- Redesigned Premium Exam Cards Grid --}}
    <div class="grid gap-6 lg:grid-cols-2">
        @forelse ($exams as $exam)
            @php
                $isLive  = $exam->status === 'live';
                $isEnded = $exam->status === 'ended';
                $isPending = $exam->status === 'pending_approval';
                
                // Style attributes based on status
                $cardStyle = 'bg-white border-slate-100 hover:border-indigo-100 hover:shadow-lg';
                $accentColor = 'bg-slate-300';
                $iconColor = 'bg-slate-50 text-slate-500';
                
                if ($isLive) {
                    $cardStyle = 'bg-gradient-to-b from-emerald-50/10 to-white border-emerald-200 hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/5';
                    $accentColor = 'bg-emerald-500';
                    $iconColor = 'bg-emerald-50 text-emerald-600 border border-emerald-200/50';
                } elseif ($isPending) {
                    $cardStyle = 'bg-gradient-to-b from-violet-50/10 to-white border-violet-200 hover:border-violet-300 hover:shadow-lg hover:shadow-violet-500/5';
                    $accentColor = 'bg-violet-500';
                    $iconColor = 'bg-violet-50 text-violet-600 border border-violet-200/50';
                } elseif ($exam->status === 'draft') {
                    $cardStyle = 'bg-gradient-to-b from-amber-50/10 to-white border-amber-200 hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/5';
                    $accentColor = 'bg-amber-400';
                    $iconColor = 'bg-amber-50 text-amber-600 border border-amber-200/50';
                } else {
                    $cardStyle = 'bg-slate-50/30 border-slate-200 grayscale-[10%]';
                }
            @endphp
            <div class="group relative overflow-hidden rounded-2xl border p-5 transition-all duration-300 flex flex-col justify-between min-h-[200px] {{ $cardStyle }}">
                
                <div class="flex flex-1 flex-col pl-2">
                    {{-- Card Header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            {{-- Standard Document Icon --}}
                            <div class="mt-0.5 grid h-10 w-10 flex-shrink-0 place-items-center rounded-xl {{ $iconColor }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-black text-slate-800 tracking-tight leading-snug group-hover:text-indigo-950 transition-colors">
                                    {{ $exam->title }}
                                </h3>
                                <div class="mt-0.5 text-xs text-slate-400 font-bold flex items-center gap-1.5">
                                    <span>{{ $exam->schoolClass?->name ?? ($exam->exam_type === 'aptitude' ? 'Aptitude Test' : '-') }}</span>
                                    @if($exam->subject?->name)
                                        <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                        <span class="text-indigo-600 font-extrabold">{{ $exam->subject?->name }}</span>
                                    @endif
                                    @if($exam->exam_type === 'aptitude')
                                        <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-[9px] font-black text-violet-700">APTITUDE</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Premium Badges --}}
                        <div class="flex-shrink-0">
                            @if($isLive)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500 px-2.5 py-0.5 text-[9px] font-black text-white border border-emerald-400/20 animate-pulse">
                                    <span class="h-1 w-1 rounded-full bg-white"></span>
                                    LIVE
                                </span>
                            @elseif($isEnded)
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-0.5 text-[9px] font-black text-slate-700">
                                    ENDED
                                </span>
                            @elseif($isPending)
                                <span class="inline-flex items-center gap-1 rounded-full bg-violet-500 px-2.5 py-0.5 text-[9px] font-black text-white border border-violet-400/20">
                                    PENDING
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-0.5 text-[9px] font-black text-white border border-amber-400/20">
                                    DRAFT
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Dynamic Meta Chips --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100/50 px-2.5 py-1 text-[10px] font-bold text-indigo-700">
                            <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ (int) $exam->questions_count }} Questions
                        </span>
                        
                        <span class="inline-flex items-center gap-1 rounded-lg bg-teal-50 border border-teal-100/50 px-2.5 py-1 text-[10px] font-bold text-teal-700">
                            <svg class="h-3.5 w-3.5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ (int) $exam->attempts_count }} Attempts
                        </span>
                        
                        @if($exam->duration_minutes)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200/50 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                {{ $exam->duration_minutes }} Mins
                            </span>
                        @endif
                        
                        @if($isLive && $exam->access_code)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 border border-amber-200 px-2.5 py-1 font-mono text-[10px] font-black text-amber-800 uppercase tracking-widest shadow-sm">
                                <svg class="h-3.5 w-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                {{ $exam->access_code }}
                            </span>
                        @endif
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-[10px] text-slate-400 font-bold">by {{ $exam->creator?->name ?? 'Unknown' }}</span>

                        <div class="flex items-center gap-2">
                            @if($isLive)
                                {{-- Live: Monitor + share code --}}
                                <a href="{{ route('cbt.exams.edit', ['exam' => $exam, 'tab' => 'monitor']) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-200 transition-all active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Monitor
                                </a>
                                <a href="{{ route('cbt.exams.edit', $exam) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-3.5 py-2 text-[11px] font-bold text-white shadow-sm hover:from-emerald-400 hover:to-teal-400 transition-all active:scale-95">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                                    Manage Live
                                </a>
                            @elseif($isPending)
                                {{-- Pending: Review button --}}
                                <a href="{{ route('cbt.exams.edit', $exam) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 px-3.5 py-2 text-[11px] font-bold text-white shadow-sm hover:from-violet-400 hover:to-purple-500 transition-all active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $isAdmin ? 'Review & Approve' : 'View Submission' }}
                                </a>
                            @elseif($isEnded)
                                {{-- Ended: Results + open --}}
                                <a href="{{ route('cbt.exams.edit', ['exam' => $exam, 'tab' => 'monitor']) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-200 transition-all active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    Results
                                </a>
                                <a href="{{ route('cbt.exams.edit', $exam) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-700 px-3.5 py-2 text-[11px] font-bold text-white hover:bg-slate-600 transition-all active:scale-95">
                                    Open
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @else
                                {{-- Draft: Edit + Preview --}}
                                <a href="{{ route('cbt.exams.edit', ['exam' => $exam, 'tab' => 'questions']) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-slate-200 transition-all active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Questions
                                </a>
                                <a href="{{ route('cbt.exams.edit', $exam) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-3.5 py-2 text-[11px] font-bold text-white shadow-sm hover:from-amber-400 hover:to-orange-400 transition-all active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit Exam
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Modern Empty State --}}
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 py-16 text-center shadow-sm flex flex-col items-center justify-center">
                <div class="mb-4 grid h-14 w-14 place-items-center rounded-full bg-slate-50 border border-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">No Examinations Found</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-xs leading-normal">Click the "New Examination" button above to quickly configure and dispatch a new online exam.</p>
            </div>
        @endforelse
    </div>
</div>
