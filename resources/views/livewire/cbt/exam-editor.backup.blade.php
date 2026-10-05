@php
    $status = (string) ($exam->status ?? 'draft');
    $variant = match ($status) {
        'approved' => 'success',
        'submitted' => 'info',
        'assigned' => 'info',
        'rejected' => 'warning',
        default => 'neutral',
    };
    $canEdit = (bool) $this->canEdit;
@endphp

<div class="space-y-6">
    <div class="relative overflow-hidden rounded-2xl bg-violet-600 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $exam->title }}</h1>
                    <p class="mt-2 text-base text-pink-50">Build questions, submit for approval, and share exam code</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-status-badge variant="{{ $variant }}">{{ ucfirst($status) }}</x-status-badge>
                        @if ($status === 'approved' && $exam->access_code)
                            <span class="inline-flex items-center rounded-lg bg-white/20 px-3 py-2 text-xs font-black text-white backdrop-blur-sm">
                                Code: <span class="ml-2 font-mono">{{ $exam->access_code }}</span>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('cbt.index') }}" class="rounded-xl bg-white/20 px-5 py-2.5 text-sm font-semibold text-white shadow-lg backdrop-blur-sm transition-all hover:bg-white/30 hover:shadow-xl">Back</a>
                    
                    @if ($canEdit)
                        <button type="button" wire:click="saveDetails" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-rose-600 shadow-lg transition-all hover:bg-pink-50 hover:shadow-xl" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Save Details</button>
                    @endif
                    
                    @if ($me?->role === 'teacher' && $canEdit)
                        <button type="button" wire:click="submitToAdmin" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-rose-600 shadow-lg transition-all hover:bg-pink-50 hover:shadow-xl" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Submit to Admin</button>
                    @endif
                    
                    @if ($me?->role === 'admin' && $status === 'submitted')
                        <button type="button" wire:click="approve" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all hover:bg-emerald-600 hover:shadow-xl" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Approve</button>
                        <button type="button" wire:click="startReject" class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg transition-all hover:bg-orange-600 hover:shadow-xl" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Reject</button>
                    @endif
                    
                    @if ($me?->role === 'admin' && $status === 'approved')
                        <button type="button" wire:click="togglePublish" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-rose-600 shadow-lg transition-all hover:bg-pink-50 hover:shadow-xl" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">{{ $exam->published_at ? 'Pause' : 'Go Live' }}</button>
                    @endif
                    
                    <!-- Actions Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="rounded-xl bg-white/20 px-5 py-2.5 text-sm font-semibold text-white shadow-lg backdrop-blur-sm transition-all hover:bg-white/30 hover:shadow-xl">
                            Actions ▼
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 z-50 mt-2 w-56 rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5" role="dialog" aria-modal="true">
                            <a href="{{ route('cbt.exams.pdf', $exam) }}" target="_blank" @click="open = false" class="block px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 first:rounded-t-xl">
                                📄 Download PDF
                            </a>
                            @if ($status === 'approved' && $exam->access_code)
                                <a href="{{ route('cbt.student', ['code' => $exam->access_code]) }}" target="_blank" @click="open = false" class="block px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    🎓 Student Portal
                                </a>
                            @endif
                            @if ($me?->role === 'admin')
                                <a href="{{ route('cbt.exams.export', $exam) }}" @click="open = false" class="block px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                    📊 Export CSV
                                </a>
                                @if ($status === 'approved')
                                    <button type="button" wire:click="transferToResults" @click="open = false" class="block w-full px-4 py-3 text-left text-sm font-semibold text-emerald-700 hover:bg-emerald-50" onclick="if (!confirm('Transfer CBT scores to academic results? This will update the results table for {{ $exam->session }} Term {{ $exam->term }}.')) { event.stopImmediatePropagation(); }">
                                        ✅ Transfer to Results
                                    </button>
                                    <button type="button" wire:click="endAllExams" @click="open = false" class="block w-full px-4 py-3 text-left text-sm font-semibold text-orange-700 hover:bg-orange-50" onclick="if (!confirm('End this exam? This will submit all active attempts for {{ $exam->title }} and calculate final scores.')) { event.stopImmediatePropagation(); }">
                                        ⏹️ End This Exam
                                    </button>
                                @endif
                                <button type="button" wire:click="deleteExam" @click="open = false" class="block w-full px-4 py-3 text-left text-sm font-semibold text-red-700 hover:bg-red-50 last:rounded-b-xl" onclick="if (!confirm('Delete this exam? This action cannot be undone.')) { event.stopImmediatePropagation(); }" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait" aria-label="Close">
                                    🗑️ Delete Exam
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($status === 'rejected' && $exam->note)
        <div class="rounded-2xl border border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 p-6 text-sm text-orange-900 shadow-lg">
            <div class="font-semibold">Admin note</div>
            <div class="mt-1">{{ $exam->note }}</div>
        </div>
    @endif

    @if ($status === 'assigned' && $exam->request_note)
        <div class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-purple-50 p-6 text-sm text-indigo-900 shadow-lg">
            <div class="font-semibold">Admin request</div>
            <div class="mt-1">{{ $exam->request_note }}</div>
            <div class="mt-2 text-xs text-indigo-800">
                Requested by: <span class="font-semibold">{{ $exam->requester?->name ?? 'Admin' }}</span>
            </div>
        </div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900">Exam Details</div>
                <div class="mt-1 text-sm text-gray-600">Class, subject, and timing settings.</div>
            </div>
        </div>

        @if ($canEdit)
            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Title</label>
                    <input wire:model="title" class="mt-2 input w-full" />
                    @error('title') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Duration (minutes)</label>
                    <input wire:model="durationMinutes" type="number" min="1" max="300" class="mt-2 input w-full" />
                    @error('durationMinutes') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                    <select wire:model.live="classId" class="mt-2 select w-full">
                        <option value="">Select class</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('classId') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Subject</label>
                    <select wire:model.live="subjectId" @disabled(! $classId) class="mt-2 select w-full">
                        <option value="">Select subject</option>
                        @foreach ($this->subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subjectId') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Term</label>
                    <select wire:model.live="term" class="mt-2 select w-full">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                    @error('term') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Session</label>
                <input wire:model="session" class="mt-2 input w-full" placeholder="2025/2026" />
                @error('session') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
            </div>

            @if ($me?->role === 'admin')
                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Start Time (optional)</label>
                        <input wire:model="startsAt" type="datetime-local" class="mt-2 input w-full" />
                        @error('startsAt') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">End Time (optional)</label>
                        <input wire:model="endsAt" type="datetime-local" class="mt-2 input w-full" />
                        @error('endsAt') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-600">
                    Students can only start within this window. Leave blank to allow anytime.
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Exam PIN (optional)</label>
                        <input wire:model="pin" class="mt-2 input w-full font-mono" placeholder="e.g. 1234" />
                        @error('pin') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Grace Minutes</label>
                        <input wire:model="graceMinutes" type="number" min="0" max="120" class="mt-2 input w-full" />
                        @error('graceMinutes') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                        <div class="mt-1 text-[11px] text-gray-600">Allows late starts after End Time.</div>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Allowed CIDRs (optional)</label>
                        <textarea wire:model="allowedCidrs" rows="2" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="e.g. 192.168.99.0/24, 10.0.0.0/8"></textarea>
                        @error('allowedCidrs') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                        <div class="mt-1 text-[11px] text-gray-600">If set, only devices on these networks can start the exam.</div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="showScore" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Show Score After Submit</span>
                        </label>
                        <div class="mt-1 text-[11px] text-gray-600">Students will see their score immediately after submitting.</div>
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Description (optional)</label>
                <textarea wire:model="description" rows="3" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                @error('description') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
            </div>
        @else
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-100">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Title</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">{{ $exam->title }}</div>
                    <div class="mt-2 text-xs text-gray-600">{{ $exam->description ?: '-' }}</div>
                </div>
                <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-100">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class / Subject</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">{{ $exam->schoolClass?->name ?? '-' }}</div>
                    <div class="mt-1 text-xs text-gray-600">{{ $exam->subject?->name ?? '-' }}</div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-600">
                        <div><span class="font-semibold">Term:</span> {{ $exam->term ?: '-' }}</div>
                        <div><span class="font-semibold">Session:</span> {{ $exam->session ?: '-' }}</div>
                        <div><span class="font-semibold">Duration:</span> {{ (int) $exam->duration_minutes }} mins</div>
                        <div><span class="font-semibold">By:</span> {{ $exam->creator?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($me?->role === 'admin' && $showRejectForm)
        <div class="rounded-2xl border border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 p-6 shadow-lg">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Reject Exam</div>
                    <div class="mt-1 text-sm text-gray-600">Explain what needs to be corrected.</div>
                </div>
                <button type="button" wire:click="cancelReject" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
            </div>
            <div class="mt-4">
                <label class="text-xs font-semibold uppercase tracking-wider text-orange-700">Rejection note</label>
                <textarea wire:model="reviewNote" rows="3" class="mt-2 w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-orange-400 focus:ring-orange-300"></textarea>
                @error('reviewNote') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
            </div>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" wire:click="confirmReject" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Confirm Reject</button>
            </div>
        </div>
    @endif

    @if (in_array($me?->role, ['teacher'], true) || $canEdit)
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Questions</div>
                    <div class="mt-1 text-sm text-gray-600">Multiple-choice (4 options). One correct answer.</div>
                </div>

                @if ($canEdit)
                    <button type="button" wire:click="startNewQuestion" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">New Question</button>
                @endif
            </div>

            <div class="mt-4 space-y-3">
                @forelse ($exam->questions as $q)
                    @php
                        $correct = $q->options->firstWhere('is_correct', true);
                    @endphp
                    <div class="rounded-2xl border border-gray-100 bg-white p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900">
                                    Q{{ $loop->iteration }}. {{ $q->prompt }}
                                </div>
                                <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    @foreach ($q->options as $opt)
                                        <div class="rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-800 ring-1 ring-inset ring-gray-100">
                                            <span class="font-semibold">{{ chr(65 + $loop->index) }}.</span>
                                            {{ $opt->label }}
                                            @if ($opt->is_correct)
                                                <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-800">Correct</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Marks: <span class="font-semibold text-gray-700">{{ (int) $q->marks }}</span>
                                    &middot;
                                    Correct: <span class="font-mono">{{ $correct ? (chr(65 + $q->options->search($correct))) : '-' }}</span>
                                </div>
                            </div>

                            @if ($canEdit)
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="editQuestion({{ $q->id }})" class="btn-outline">Edit</button>
                                    <button type="button" wire:click="deleteQuestion({{ $q->id }})" class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700">
                                        Delete
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-600">
                        No questions yet.
                    </div>
                @endforelse
            </div>

            @if ($canEdit)
                <div class="mt-6 rounded-2xl border border-pink-200 bg-gradient-to-br from-pink-50 to-fuchsia-50 p-6 shadow-lg">
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $editingQuestionId ? 'Edit Question' : 'Add Question' }}
                    </div>

                    <div class="mt-4">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Question</label>
                        <textarea wire:model="questionPrompt" rows="3" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Type the question..."></textarea>
                        @error('questionPrompt') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Marks</label>
                            <input wire:model="questionMarks" type="number" min="1" max="100" class="mt-2 input w-full" />
                            @error('questionMarks') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                        </div>
                        <div class="lg:col-span-2">
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Correct Option</label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @for ($i = 0; $i < 4; $i++)
                                    <label class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-200">
                                        <input type="radio" wire:model="correctIndex" value="{{ $i }}" />
                                        {{ chr(65 + $i) }}
                                    </label>
                                @endfor
                            </div>
                            @error('correctIndex') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @for ($i = 0; $i < 4; $i++)
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Option {{ chr(65 + $i) }}</label>
                                <input wire:model="optionLabels.{{ $i }}" class="mt-2 input w-full" />
                                @error("optionLabels.$i") <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                            </div>
                        @endfor
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="saveQuestion" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Save Question</button>
                        @if ($editingQuestionId)
                            <button type="button" wire:click="startNewQuestion" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel Edit</button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($status === 'approved')
        @php
            $roster = collect();
            $submittedCount = 0;
            $inProgressCount = 0;
            $notStartedCount = 0;
            $terminatedCount = 0;
            $totalQuestions = 0;

            if ($me?->role === 'admin') {
                $roster = $this->roster;
                $submittedCount = (int) $roster->where('state', 'submitted')->count();
                $inProgressCount = (int) $roster->where('state', 'in_progress')->count();
                $notStartedCount = (int) $roster->where('state', 'not_started')->count();
                $terminatedCount = (int) $roster->where('state', 'terminated')->count();
                $totalQuestions = (int) ($exam?->questions?->count() ?? 0);
            }
        @endphp
        <div class="rounded-2xl bg-white p-8 shadow-lg lg:p-10">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Exam Monitor</div>
                    <div class="mt-1 text-sm text-gray-600">Track who started, who submitted, and manage attempts.</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge variant="{{ $exam->published_at ? 'success' : 'warning' }}">
                        {{ $exam->published_at ? 'Live' : 'Paused' }}
                    </x-status-badge>
                    <x-status-badge variant="neutral">{{ $exam->attempts->count() }} attempt(s)</x-status-badge>
                </div>
            </div>

            <div class="mt-4">
                @if ($me?->role === 'admin')
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <x-status-badge variant="neutral">{{ (int) $roster->count() }} student(s)</x-status-badge>
                        <x-status-badge variant="success">{{ $submittedCount }} submitted</x-status-badge>
                        <x-status-badge variant="info">{{ $inProgressCount }} in progress</x-status-badge>
                        <x-status-badge variant="warning">{{ $terminatedCount }} terminated</x-status-badge>
                        <x-status-badge variant="neutral">{{ $notStartedCount }} not started</x-status-badge>
                    </div>

                    <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-100">
                        <x-table>
                            <thead class="bg-gradient-to-r from-rose-500 to-fuchsia-600 text-xs font-semibold uppercase tracking-wider text-white">
                                <tr>
                                    <th class="px-3 py-2">Student</th>
                                    <th class="px-3 py-2">State</th>
                                    <th class="px-3 py-2 text-center">Progress</th>
                                    <th class="px-3 py-2">Started</th>
                                    <th class="px-3 py-2">Submitted</th>
                                    <th class="px-3 py-2">IP</th>
                                    <th class="px-3 py-2 text-right">Action</th>
                                </tr>
                            </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($roster as $row)
                                @php
                                    $student = $row['student'];
                                    $attempt = $row['attempt'];
                                    $state = (string) $row['state'];
                                    $answered = (int) ($row['answered'] ?? 0);
                                    $rem = (int) ($row['remaining'] ?? 0);
                                    $stateVariant = match ($state) {
                                        'submitted' => 'success',
                                        'in_progress' => 'info',
                                        'terminated' => 'warning',
                                        default => 'neutral',
                                    };
                                @endphp
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2">
                                            @if ($student->passport_photo_url)
                                                <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name ?? 'Student' }}" class="h-8 w-8 rounded-lg object-cover ring-1 ring-inset ring-slate-200" />
                                            @else
                                                <div class="grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200">
                                                    <span class="text-xs font-black">{{ mb_substr($student->first_name ?? 'S', 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="truncate text-xs font-semibold text-gray-900">{{ $student->full_name ?? trim($student->first_name.' '.$student->last_name) }}</div>
                                                <div class="text-[10px] font-mono text-gray-500">{{ $student->admission_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <x-status-badge variant="{{ $stateVariant }}">{{ str_replace('_', ' ', ucfirst($state)) }}</x-status-badge>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($attempt)
                                            <div class="text-xs font-semibold text-gray-900">{{ $answered }}/{{ $totalQuestions }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        {{ $attempt?->started_at ? $attempt->started_at->format('g:i A') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700">
                                        {{ $attempt?->submitted_at ? $attempt->submitted_at->format('g:i A') : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-[10px] text-gray-700">
                                        <div class="font-mono">{{ $attempt?->ip_address ?: '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        @if ($attempt)
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" type="button" class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200">
                                                    Actions ▼
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 z-10 mt-1 w-44 rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5" role="dialog" aria-modal="true">
                                                    <button type="button" wire:click="startIpOverride({{ $attempt->id }})" @click="open = false" class="block w-full px-4 py-2.5 text-left text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                                        Change IP
                                                    </button>
                                                    @if ($state === 'in_progress')
                                                        <button type="button" wire:click="forceSubmitAttempt({{ $attempt->id }})" @click="open = false" class="block w-full px-4 py-2.5 text-left text-xs font-semibold text-blue-700 hover:bg-blue-50" onclick="if (!confirm('Force submit?')) { event.stopImmediatePropagation(); }">
                                                            Force Submit
                                                        </button>
                                                        <button type="button" wire:click="terminateAttempt({{ $attempt->id }})" @click="open = false" class="block w-full px-4 py-2.5 text-left text-xs font-semibold text-orange-700 hover:bg-orange-50" onclick="if (!confirm('Terminate?')) { event.stopImmediatePropagation(); }">
                                                            Terminate
                                                        </button>
                                                    @endif
                                                    <button type="button" wire:click="resetAttempt({{ $attempt->id }})" @click="open = false" class="block w-full px-4 py-2.5 text-left text-xs font-semibold text-rose-700 hover:bg-rose-50" onclick="if (!confirm('Reset?')) { event.stopImmediatePropagation(); }">
                                                        Reset Attempt
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-xs text-gray-600">
                                        No students found for this class.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </x-table>
                    </div>

                    @if ($editingAttemptIpId)
                        <div class="mt-4 rounded-2xl border border-pink-200 bg-gradient-to-br from-pink-50 to-fuchsia-50 p-6 shadow-lg">
                            <div class="text-sm font-semibold text-gray-900">Allow IP override</div>
                            <div class="mt-1 text-sm text-gray-600">Enter the student's current IP to allow access from that device. Leave blank to remove override.</div>

                            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                                <input wire:model="allowedIp" class="input w-full font-mono" placeholder="e.g. 192.168.1.50" />
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="saveIpOverride" class="btn-primary" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Save</button>
                                    <button type="button" wire:click="cancelIpOverride" class="btn-outline" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">Cancel</button>
                                </div>
                            </div>
                            @error('allowedIp') <div class="mt-1 text-xs font-semibold text-orange-700">{{ $message }}</div> @enderror
                        </div>
                    @endif
                @else
                    <div class="overflow-hidden rounded-2xl border border-gray-100">
                        <x-table>
                            <thead class="bg-gradient-to-r from-rose-500 to-fuchsia-600 text-xs font-semibold uppercase tracking-wider text-white">
                                <tr>
                                    <th class="px-5 py-3">Student</th>
                                    <th class="px-5 py-3 text-center">Score</th>
                                    <th class="px-5 py-3 text-center">Percent</th>
                                    <th class="px-5 py-3">Submitted</th>
                                </tr>
                            </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($exam->attempts as $a)
                                <tr class="bg-white hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $a->student?->full_name ?? trim(($a->student?->first_name ?? '').' '.($a->student?->last_name ?? '')) ?: 'Student' }}
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 font-mono">{{ $a->student?->admission_number ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-center text-sm font-semibold text-gray-900">
                                        {{ (int) $a->score }} / {{ (int) $a->max_score }}
                                    </td>
                                    <td class="px-5 py-4 text-center text-sm font-semibold text-gray-900">
                                        {{ number_format((float) $a->percent, 2) }}%
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        {{ $a->submitted_at ? $a->submitted_at->format('M j, Y g:i A') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-600">
                                        No attempts yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </x-table>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
