<div class="space-y-4">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-6 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-8 -left-8 h-28 w-28 rounded-full bg-white/5"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl font-black text-white sm:text-2xl">Theory Review</h1>
                    <p class="mt-0.5 truncate text-xs text-violet-200 sm:text-sm">
                        {{ $exam->title }} &bull; {{ $exam->schoolClass?->name }} &bull; {{ $exam->subject?->name }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($reviewAttemptId)
                    <button wire:click="back"
                        class="flex items-center gap-1.5 rounded-xl bg-white/20 px-3 py-2 text-xs font-bold text-white backdrop-blur-sm hover:bg-white/30 sm:px-4 sm:py-2.5 sm:text-sm">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        All Students
                    </button>
                @endif
                <a href="{{ route('cbt.exams.edit', $exam) }}"
                   class="flex items-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-bold text-violet-700 shadow-lg hover:bg-violet-50 sm:px-4 sm:py-2.5 sm:text-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Exam
                </a>
            </div>
        </div>
    </div>

    {{-- ── STUDENT LIST ──────────────────────────────────────────────── --}}
    @if(!$reviewAttemptId)
        @php
            $total   = $this->attempts->count();
            $marked  = $this->attempts->where('theory_status', 'marked')->count();
            $pending = $total - $marked;
        @endphp

        {{-- Progress Bar --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <div class="mb-3 flex items-center justify-between">
                <span class="text-sm font-black text-gray-900">Marking Progress</span>
                <span class="text-sm font-bold text-gray-500">{{ $marked }} / {{ $total }}</span>
            </div>
            <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-2.5 rounded-full bg-gradient-to-r from-violet-500 to-purple-500 transition-all duration-500"
                     style="width: {{ $total > 0 ? round(($marked / $total) * 100) : 0 }}%"></div>
            </div>
            <div class="mt-3 flex gap-4 text-xs font-semibold">
                <span class="text-emerald-600">✓ {{ $marked }} marked</span>
                <span class="text-amber-600">⏳ {{ $pending }} pending</span>
            </div>
        </div>

        {{-- Student Cards --}}
        <div class="space-y-2">
            @forelse($this->attempts as $attempt)
                @php
                    $status = $attempt->theory_status ?? 'pending';
                    $isMarked = $status === 'marked';
                @endphp
                <button wire:click="openAttempt({{ $attempt->id }})"
                    class="group flex w-full items-center justify-between rounded-2xl bg-white px-4 py-3.5 text-left shadow-sm ring-1 transition-all hover:shadow-md sm:px-6 sm:py-4
                        {{ $isMarked ? 'ring-emerald-200 hover:ring-emerald-400' : 'ring-gray-200 hover:ring-violet-400' }}">
                    <div class="flex min-w-0 items-center gap-3">
                        @if($attempt->student?->passport_photo_url)
                            <img src="{{ $attempt->student->passport_photo_url }}"
                                 class="h-10 w-10 flex-shrink-0 rounded-xl object-cover ring-2 ring-gray-100" />
                        @else
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl text-white shadow-sm
                                {{ $isMarked ? 'bg-gradient-to-br from-emerald-400 to-teal-500' : 'bg-gradient-to-br from-violet-400 to-purple-500' }}">
                                <span class="text-sm font-black">{{ substr($attempt->student?->first_name ?? 'S', 0, 1) }}{{ substr($attempt->student?->last_name ?? '', 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="truncate font-bold text-gray-900">{{ $attempt->student?->full_name ?? 'Student' }}</div>
                            <div class="text-xs text-gray-500">{{ $attempt->student?->admission_number }}</div>
                        </div>
                    </div>
                    <div class="ml-3 flex flex-shrink-0 items-center gap-2">
                        @if($isMarked)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">✓ Marked</span>
                        @elseif($status === 'forwarded')
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">Forwarded</span>
                        @else
                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-800">Pending</span>
                        @endif
                        <svg class="h-4 w-4 flex-shrink-0 text-gray-400 transition-transform group-hover:translate-x-1"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </div>
                </button>
            @empty
                <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-200">
                    <div class="text-4xl mb-3">📭</div>
                    <h3 class="text-lg font-bold text-gray-900">No submitted attempts</h3>
                    <p class="mt-1 text-sm text-gray-500">Students haven't submitted yet.</p>
                </div>
            @endforelse
        </div>

    {{-- ── MARKING VIEW ──────────────────────────────────────────────── --}}
    @else
        @php $attempt = $this->currentAttempt; @endphp
        @if($attempt)
            {{-- Sticky action bar --}}
            <div class="sticky top-0 z-10 rounded-2xl bg-white px-4 py-3 shadow-md ring-1 ring-gray-200 sm:px-6 sm:py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 text-white">
                            <span class="text-xs font-black">{{ substr($attempt->student?->first_name ?? 'S', 0, 1) }}{{ substr($attempt->student?->last_name ?? '', 0, 1) }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-gray-900">{{ $attempt->student?->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $attempt->student?->admission_number }}</div>
                        </div>
                    </div>
                    <div class="flex flex-shrink-0 items-center gap-2">
                        <button wire:click="autoMarkAll" wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-2 text-xs font-bold text-white shadow-sm hover:from-amber-600 hover:to-orange-600 sm:px-5 sm:py-2.5 sm:text-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="autoMarkAll">🪄 Auto-Mark All (AI)</span>
                            <span wire:loading wire:target="autoMarkAll" class="flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                AI Marking...
                            </span>
                        </button>
                        <button wire:click="save"
                            class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-3 py-2 text-xs font-bold text-white shadow-sm hover:from-emerald-600 hover:to-teal-600 sm:px-5 sm:py-2.5 sm:text-sm">
                            Save
                        </button>
                        <button wire:click="saveAndNext"
                            class="rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-3 py-2 text-xs font-bold text-white shadow-sm hover:from-violet-700 hover:to-purple-700 sm:px-5 sm:py-2.5 sm:text-sm">
                            Save &amp; Next →
                        </button>
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            <div class="space-y-4">
                @foreach($this->theoryQuestions as $i => $question)
                    @php
                        $answer   = $attempt->answers->firstWhere('question_id', $question->id);
                        $response = trim((string) ($answer?->text_answer ?? ''));
                    @endphp
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                        {{-- Question Header --}}
                        <div class="flex flex-wrap items-start justify-between gap-2 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-slate-50 px-4 py-3 sm:px-6 sm:py-4">
                            <div class="flex items-start gap-3">
                                <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-violet-100 text-xs font-black text-violet-700">{{ $i + 1 }}</span>
                                <span class="text-sm font-bold text-gray-900 sm:text-base">{{ $question->prompt }}</span>
                            </div>
                            <span class="flex-shrink-0 text-xs font-bold text-gray-500">Max: {{ $question->marks }} marks</span>
                        </div>

                        {{-- Student Answer --}}
                        <div class="px-4 py-4 sm:px-6">
                            <div class="mb-1.5 text-xs font-bold uppercase tracking-wider text-gray-500">Student's Answer</div>
                            <div class="min-h-[60px] rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-800 ring-1 ring-gray-200">
                                {{ $response !== '' ? $response : '— No answer submitted —' }}
                            </div>
                        </div>

                        {{-- Marking Row --}}
                        <div class="flex flex-col gap-3 border-t border-gray-100 bg-violet-50/40 px-4 py-4 sm:flex-row sm:items-center sm:px-6">
                             <div class="flex flex-wrap items-center gap-2">
                                 <label class="text-sm font-black text-gray-900 whitespace-nowrap">Marks</label>
                                 <input type="number"
                                     wire:model.lazy="theoryMarks.{{ $question->id }}"
                                     min="0" max="{{ $question->marks }}"
                                     placeholder="0"
                                     class="w-20 rounded-xl border-2 border-violet-300 bg-white px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm focus:border-violet-500 focus:ring-4 focus:ring-violet-500/20" />
                                 <span class="text-sm text-gray-500 whitespace-nowrap">/ {{ $question->marks }}</span>
                                 
                                 <button type="button" wire:click="autoMarkQuestion({{ $question->id }})" wire:loading.attr="disabled"
                                     class="flex items-center gap-1 rounded-lg bg-amber-100 hover:bg-amber-200 px-2 py-1 text-xs font-bold text-amber-800 disabled:opacity-50 transition-colors">
                                     <span wire:loading.remove wire:target="autoMarkQuestion({{ $question->id }})">🪄 AI Suggest</span>
                                     <span wire:loading wire:target="autoMarkQuestion({{ $question->id }})" class="flex items-center gap-1">
                                         <svg class="animate-spin h-3 w-3 text-amber-800" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                         AI...
                                     </span>
                                 </button>
                                 
                                 @error("theoryMarks.{$question->id}")
                                     <span class="text-xs font-bold text-red-600 block sm:inline">{{ $message }}</span>
                                 @enderror
                             </div>
                            <div class="flex-1">
                                <input type="text"
                                    wire:model.lazy="theoryComments.{{ $question->id }}"
                                    placeholder="Comment (optional)"
                                    class="w-full rounded-xl border-2 border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-violet-400 focus:ring-4 focus:ring-violet-400/20" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Bottom Action Bar --}}
            <div class="flex flex-col gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm ring-1 ring-gray-200 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <button wire:click="back" class="text-sm font-bold text-gray-500 hover:text-gray-700">
                    ← Back to list
                </button>
                <div class="flex gap-2">
                    <button wire:click="save"
                        class="flex-1 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg hover:from-emerald-600 hover:to-teal-600 sm:flex-none sm:px-6">
                        Save Marks
                    </button>
                    <button wire:click="saveAndNext"
                        class="flex-1 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg hover:from-violet-700 hover:to-purple-700 sm:flex-none sm:px-6">
                        Save &amp; Next →
                    </button>
                </div>
            </div>
        @endif
    @endif

</div>
