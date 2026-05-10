@php
    $isAdmin = $me?->role === 'admin';
@endphp

<div class="space-y-6">
    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-4 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">Examination</span>
                </div>
                <h1 class="text-4xl font-bold text-white tracking-tight">CBT Exams</h1>
                <p class="mt-1.5 text-base font-medium" style="color:#93c5fd;">Create an exam, add questions, and it's live instantly.</p>
            </div>
            <button wire:click="{{ $creating ? 'cancelCreate' : 'startCreate' }}"
                    class="inline-flex items-center gap-2 self-start sm:self-auto rounded-xl px-5 py-2.5 text-sm font-bold transition-colors
                    {{ $creating ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
                @if(!$creating)
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                @endif
                {{ $creating ? 'Close' : 'New Exam' }}
            </button>
        </div>
    </div>

    @if ($creating)
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center gap-3 border-b border-slate-200 px-6 py-4">
                <div class="grid h-8 w-8 place-items-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-sm font-bold text-slate-900">New Exam</div>
            </div>
            <div class="p-6">
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Title <span class="text-red-500">*</span></label>
                        <input wire:model="title"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm placeholder-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                               placeholder="e.g. Mathematics Quiz 1" autofocus />
                        @error('title') <div class="mt-1 text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Class <span class="text-red-500">*</span></label>
                        <select wire:model.live="classId"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            <option value="">Select class</option>
                            @foreach ($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('classId') <div class="mt-1 text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Subject <span class="text-red-500">*</span></label>
                        <select wire:model.live="subjectId" @disabled(!$classId)
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100 disabled:opacity-50">
                            <option value="">Select subject</option>
                            @foreach ($this->subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                        @error('subjectId') <div class="mt-1 text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Duration (minutes)</label>
                        <input wire:model="durationMinutes" type="number" min="1"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100" />
                        @error('durationMinutes') <div class="mt-1 text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Term</label>
                        <select wire:model.live="term"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                            <option value="1">Term 1</option>
                            <option value="2">Term 2</option>
                            <option value="3">Term 3</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Session</label>
                        <input wire:model="session"
                               class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm placeholder-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                               placeholder="2025/2026" />
                        @error('session') <div class="mt-1 text-xs text-red-500">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-between border-t border-slate-200 pt-5">
                    <div>
                        @if ($isAdmin)
                            <p class="text-xs text-slate-500">Admin exams go live immediately with an access code.</p>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="cancelCreate"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="createExam"
                                class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2 text-sm font-bold text-white hover:bg-amber-600 transition-colors">
                            Create & Add Questions
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
    <div class="flex items-center justify-between rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
        <span class="text-sm font-bold text-slate-800">{{ $exams->count() }} Exams</span>
        <select wire:model.live="statusFilter"
                class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
            <option value="">All Status</option>
            <option value="draft">Draft</option>
            <option value="live">Live</option>
            <option value="ended">Ended</option>
        </select>
    </div>

    {{-- Exam cards --}}
    <div class="grid gap-4 lg:grid-cols-2">
        @forelse ($exams as $exam)
            @php
                $isLive  = $exam->status === 'live';
                $isEnded = $exam->status === 'ended';
            @endphp
            <div class="flex overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 hover:shadow-md transition-shadow">

                {{-- Left accent bar --}}
                <div class="w-1.5 flex-shrink-0 {{ $isLive ? 'bg-emerald-500' : ($isEnded ? 'bg-slate-300' : 'bg-amber-400') }}"></div>

                <div class="flex flex-1 flex-col p-5">
                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="mt-0.5 grid h-10 w-10 flex-shrink-0 place-items-center rounded-xl
                                {{ $isLive ? 'bg-emerald-50 text-emerald-600' : ($isEnded ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-600') }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-bold text-slate-900">{{ $exam->title }}</div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    {{ $exam->schoolClass?->name }}
                                    @if($exam->subject?->name)
                                        <span class="mx-1 text-slate-300">&middot;</span>{{ $exam->subject->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($isLive)
                            <span class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Live
                            </span>
                        @elseif($isEnded)
                            <span class="flex-shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Ended</span>
                        @else
                            <span class="flex-shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Draft</span>
                        @endif
                    </div>

                    {{-- Meta chips --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ (int) $exam->questions_count }} questions
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ (int) $exam->attempts_count }} attempts
                        </span>
                        @if($exam->duration_minutes)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                {{ $exam->duration_minutes }} min
                            </span>
                        @endif
                        @if($isLive && $exam->access_code)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2.5 py-1 font-mono text-xs font-bold text-amber-800">
                                <svg class="h-3.5 w-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                {{ $exam->access_code }}
                            </span>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-xs text-slate-400">by {{ $exam->creator?->name ?? 'Unknown' }}</span>
                        <a href="{{ route('cbt.exams.edit', $exam) }}"
                           class="rounded-xl px-4 py-1.5 text-xs font-bold transition-colors
                           {{ $isLive ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
                            {{ $exam->status === 'draft' ? 'Edit Exam' : 'Open Exam' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 rounded-2xl bg-white py-14 text-center shadow-sm ring-1 ring-slate-100">
                <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="text-sm font-bold text-slate-700">No exams yet</div>
                <div class="mt-1 text-xs text-slate-400">Click "New Exam" to get started</div>
            </div>
        @endforelse
    </div>
</div>
