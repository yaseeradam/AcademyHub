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
    $tab = $this->tab ?? 'details';
    $hasTheory = $exam->questions->contains('type', 'theory');
@endphp

<div class="space-y-6" x-data="{ tab: '{{ $tab }}' }">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 to-purple-600 p-6 shadow-xl">
        <div class="relative">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $exam->title }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <x-status-badge variant="{{ $variant }}">{{ ucfirst($status) }}</x-status-badge>
                        @if ($status === 'approved' && $exam->access_code)
                            <span class="rounded-lg bg-white/20 px-3 py-1 text-xs font-black text-white backdrop-blur-sm">
                                Code: {{ $exam->access_code }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('cbt.index') }}" class="rounded-lg bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm hover:bg-white/30">Back</a>
                    
                    @if ($canEdit)
                        <button wire:click="saveDetails" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-violet-600 hover:bg-pink-50">Save</button>
                    @endif
                    
                    @if ($me?->role === 'teacher' && $canEdit)
                        <button wire:click="submitToAdmin" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Submit</button>
                    @endif
                    
                    @if ($me?->role === 'admin' && $status === 'submitted')
                        <button wire:click="approve" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Approve</button>
                        <button wire:click="startReject" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">Reject</button>
                    @endif
                    
                    @if ($me?->role === 'admin' && $status === 'approved')
                        <button wire:click="togglePublish" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-violet-600 hover:bg-pink-50">{{ $exam->published_at ? 'Pause' : 'Go Live' }}</button>
                    @endif
                </div>
            </div>
            
            <div class="mt-6 flex flex-wrap gap-2 border-t border-white/20 pt-4">
                <button @click="tab = 'details'" :class="tab === 'details' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Details</button>
                <button @click="tab = 'questions'" :class="tab === 'questions' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Questions</button>
                @if ($status === 'approved')
                    <button @click="tab = 'monitor'" :class="tab === 'monitor' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Monitor</button>
                @endif
                <button @click="tab = 'actions'" :class="tab === 'actions' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Actions</button>
            </div>
        </div>
    </div>

    @if ($status === 'rejected' && $exam->note)
        <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 text-sm text-orange-900">
            <div class="font-semibold">Admin note:</div>
            <div class="mt-1">{{ $exam->note }}</div>
        </div>
    @endif

    @if ($status === 'assigned' && $exam->request_note)
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
            <div class="font-semibold">Admin request:</div>
            <div class="mt-1">{{ $exam->request_note }}</div>
        </div>
    @endif

    <div x-show="tab === 'details'" class="rounded-2xl bg-white p-6 shadow-lg border border-gray-200">
        @if ($canEdit)
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-gray-800 mb-2">Title</label>
                    <input wire:model="title" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                    @error('title') <div class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Class</label>
                    <select wire:model.live="classId" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm">
                        <option value="" class="text-gray-500">Select Class</option>
                        @foreach ($this->classes as $class)
                            <option value="{{ $class->id }}" class="text-gray-900">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('classId') <div class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Subject</label>
                    <select wire:model.live="subjectId" @disabled(! $classId) class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm disabled:bg-gray-100 disabled:text-gray-500">
                        <option value="" class="text-gray-500">Select Subject</option>
                        @foreach ($this->subjects as $subject)
                            <option value="{{ $subject->id }}" class="text-gray-900">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subjectId') <div class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Duration (minutes)</label>
                    <input wire:model="durationMinutes" type="number" min="1" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                    @error('durationMinutes') <div class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Term</label>
                    <select wire:model.live="term" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm">
                        <option value="1" class="text-gray-900">Term 1</option>
                        <option value="2" class="text-gray-900">Term 2</option>
                        <option value="3" class="text-gray-900">Term 3</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-gray-800 mb-2">Session</label>
                    <input wire:model="session" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" placeholder="2025/2026" />
                    @error('session') <div class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</div> @enderror
                </div>
            </div>

            @if ($me?->role === 'admin')
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Advanced Settings</h3>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Start Time</label>
                            <input wire:model="startsAt" type="datetime-local" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">End Time</label>
                            <input wire:model="endsAt" type="datetime-local" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">PIN</label>
                            <input wire:model="pin" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm font-mono" placeholder="1234" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Grace Minutes</label>
                            <input wire:model="graceMinutes" type="number" min="0" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold text-gray-800 mb-2">Allowed CIDRs</label>
                            <textarea wire:model="allowedCidrs" rows="3" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" placeholder="192.168.99.0/24"></textarea>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <input type="checkbox" wire:model="showScore" class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500" />
                                <span class="text-sm font-bold text-gray-800">Show Score After Submit</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <label class="block text-sm font-bold text-gray-800 mb-2">Description</label>
                <textarea wire:model="description" rows="4" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm"></textarea>
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="grid gap-4 text-base">
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Class:</span> <span class="text-gray-900">{{ $exam->schoolClass?->name ?? '-' }}</span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Subject:</span> <span class="text-gray-900">{{ $exam->subject?->name ?? '-' }}</span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Duration:</span> <span class="text-gray-900">{{ (int) $exam->duration_minutes }} minutes</span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Term:</span> <span class="text-gray-900">{{ $exam->term }}</span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Session:</span> <span class="text-gray-900">{{ $exam->session }}</span></div>
                </div>
            </div>
        @endif
    </div>

    @if ($me?->role === 'admin' && $showRejectForm)
        <div class="rounded-lg border border-orange-200 bg-orange-50 p-4">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold">Reject Exam</div>
                <button wire:click="cancelReject" class="btn-outline">Cancel</button>
            </div>
            <textarea wire:model="reviewNote" rows="2" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm"></textarea>
            <button wire:click="confirmReject" class="mt-2 btn-primary">Confirm</button>
        </div>
    @endif

    <div x-show="tab === 'questions'" class="rounded-2xl bg-white p-6 shadow-lg border border-gray-200">
        @if ($canEdit)
            <div class="rounded-lg border-2 border-dashed border-violet-300 bg-violet-50 p-6">
                <div class="text-lg font-bold text-gray-900 mb-4">{{ $editingQuestionId ? 'Edit' : 'Add' }} Question</div>
                <textarea wire:model="questionPrompt" rows="3" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" placeholder="Enter your question here..."></textarea>
                @error('questionPrompt') <div class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</div> @enderror
                
                <div class="mt-6 grid gap-6 lg:grid-cols-[200px,1fr]">
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Question Type</label>
                        <select wire:model="questionType" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm">
                            <option value="mcq" class="text-gray-900">Multiple Choice (MCQ)</option>
                            <option value="theory" class="text-gray-900">Theory/Essay</option>
                        </select>
                    </div>

                    @if ($questionType === 'mcq')
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Answer Options</label>
                            <div class="grid gap-3 lg:grid-cols-2">
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="p-3 bg-white border-2 border-gray-200 rounded-lg">
                                        <div class="flex items-center gap-3 mb-2">
                                            <input type="radio" wire:model="correctIndex" value="{{ $i }}" id="opt{{ $i }}" class="w-4 h-4 text-green-600 border-2 border-gray-300 focus:ring-green-500" />
                                            <label for="opt{{ $i }}" class="text-sm font-bold text-gray-800">Option {{ chr(65 + $i) }} {{ $correctIndex == $i ? '(Correct Answer)' : '' }}</label>
                                        </div>
                                        <input wire:model="optionLabels.{{ $i }}" class="w-full px-3 py-2 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white" placeholder="Enter option {{ chr(65 + $i) }}" />
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="p-4 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50">
                            <div class="text-sm font-bold text-blue-800 mb-2">Theory Question</div>
                            <div class="text-sm text-blue-700">
                                Theory questions allow students to write detailed answers. These questions require manual marking by teachers.
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="mt-6 flex items-center gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Marks</label>
                        <input wire:model="questionMarks" type="number" min="1" class="w-24 px-3 py-2 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white" placeholder="10" />
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button wire:click="saveQuestion" class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-md">{{ $editingQuestionId ? 'Update Question' : 'Add Question' }}</button>
                        @if ($editingQuestionId)
                            <button wire:click="startNewQuestion" class="px-6 py-3 bg-gray-500 text-white font-bold rounded-lg hover:bg-gray-600 shadow-md">Cancel</button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 space-y-4">
            @forelse ($exam->questions as $q)
                <div class="rounded-lg border-2 border-gray-300 bg-gray-50 p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="text-base font-bold text-gray-900 mb-3">Q{{ $loop->iteration }}. {{ $q->prompt }}</div>
                            <div class="mt-3">
                                @if ($q->type === 'theory')
                                    <span class="inline-block rounded-lg bg-blue-200 px-3 py-2 text-sm font-bold text-blue-800">Theory Question</span>
                                @else
                                    <div class="grid gap-2 lg:grid-cols-2">
                                        @foreach ($q->options as $opt)
                                            <div class="rounded-lg bg-white px-3 py-2 border-2 {{ $opt->is_correct ? 'border-green-500 bg-green-50' : 'border-gray-200' }}">
                                                <span class="font-bold text-gray-800">{{ chr(65 + $loop->index) }}.</span> 
                                                <span class="text-gray-900">{{ $opt->label }}</span>
                                                @if ($opt->is_correct)
                                                    <span class="ml-2 text-green-600 font-bold">✓ Correct</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 text-sm font-bold text-gray-700">Marks: {{ (int) $q->marks }}</div>
                        </div>
                        @if ($canEdit)
                            <div class="flex gap-2 ml-4">
                                <button wire:click="editQuestion({{ $q->id }})" class="px-4 py-2 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 text-sm">Edit</button>
                                <button wire:click="deleteQuestion({{ $q->id }})" class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 text-sm">Delete</button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                    <div class="text-base font-medium text-gray-600">No questions added yet</div>
                    <div class="text-sm text-gray-500 mt-1">Click "Add Question" above to create your first question</div>
                </div>
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'monitor'" class="space-y-4">
        @if ($status === 'approved')
            @php
                $roster = collect();
                $submittedCount = 0;
                $inProgressCount = 0;
                $notStartedCount = 0;
                $terminatedCount = 0;

                if ($me?->role === 'admin') {
                    $roster = $this->roster;
                    $submittedCount = (int) $roster->where('state', 'submitted')->count();
                    $inProgressCount = (int) $roster->where('state', 'in_progress')->count();
                    $notStartedCount = (int) $roster->where('state', 'not_started')->count();
                    $terminatedCount = (int) $roster->where('state', 'terminated')->count();
                }
            @endphp
            
            <div class="grid gap-4 lg:grid-cols-4">
                <div class="rounded-lg bg-emerald-50 p-4 ring-1 ring-emerald-200">
                    <div class="text-2xl font-bold text-emerald-900">{{ $submittedCount }}</div>
                    <div class="text-xs font-semibold text-emerald-700">Submitted</div>
                </div>
                <div class="rounded-lg bg-blue-50 p-4 ring-1 ring-blue-200">
                    <div class="text-2xl font-bold text-blue-900">{{ $inProgressCount }}</div>
                    <div class="text-xs font-semibold text-blue-700">In Progress</div>
                </div>
                <div class="rounded-lg bg-orange-50 p-4 ring-1 ring-orange-200">
                    <div class="text-2xl font-bold text-orange-900">{{ $terminatedCount }}</div>
                    <div class="text-xs font-semibold text-orange-700">Terminated</div>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200">
                    <div class="text-2xl font-bold text-gray-900">{{ $notStartedCount }}</div>
                    <div class="text-xs font-semibold text-gray-700">Not Started</div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-lg">
                @if ($me?->role === 'admin')
                    <div class="space-y-2">
                        @forelse ($roster as $row)
                            @php
                                $student = $row['student'];
                                $attempt = $row['attempt'];
                                $state = (string) $row['state'];
                                $answered = (int) ($row['answered'] ?? 0);
                                $totalQuestions = (int) ($exam?->questions?->count() ?? 0);
                            @endphp
                            <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3">
                                <div class="flex items-center gap-3">
                                    @if ($student->passport_photo_url)
                                        <img src="{{ $student->passport_photo_url }}" class="h-10 w-10 rounded-lg object-cover" />
                                    @else
                                        <div class="grid h-10 w-10 place-items-center rounded-lg bg-gray-200 text-sm font-bold">{{ mb_substr($student->first_name ?? 'S', 0, 1) }}</div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-semibold">{{ $student->full_name ?? trim($student->first_name.' '.$student->last_name) }}</div>
                                        <div class="text-xs text-gray-500">{{ $student->admission_number }}</div>
                                    </div>
                                </div>
                            <div class="flex items-center gap-3">
                                @if ($state === 'submitted')
                                    <span class="rounded bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Submitted</span>
                                @elseif ($state === 'in_progress')
                                    <span class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">{{ $answered }}/{{ $totalQuestions }}</span>
                                @elseif ($state === 'terminated')
                                    <span class="rounded bg-orange-100 px-2 py-1 text-xs font-semibold text-orange-800">Terminated</span>
                                @else
                                    <span class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">Not Started</span>
                                @endif

                                    @if ($hasTheory && $attempt && ($attempt->submitted_at || $attempt->terminated_at))
                                        @php
                                            $theoryStatus = $attempt->theory_status ?? 'pending';
                                        @endphp
                                        @if ($theoryStatus === 'marked')
                                            <span class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">✓ Marked</span>
                                        @elseif ($theoryStatus === 'forwarded')
                                            <span class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">→ Forwarded</span>
                                        @else
                                            <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-800">Theory Pending</span>
                                        @endif
                                        <button wire:click="startReview({{ $attempt->id }})" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-200">
                                            {{ $theoryStatus === 'marked' ? 'View' : 'Mark' }}
                                        </button>
                                    @endif
                                    
                                    @if ($attempt)
                                        <div class="relative">
                                            <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="rounded bg-gray-200 px-3 py-1 text-xs font-semibold hover:bg-gray-300">•••</button>
                                            <div class="absolute right-0 z-10 mt-1 hidden w-40 rounded-lg bg-white shadow-xl ring-1 ring-black/5">
                                                @if ($hasTheory && ($attempt->submitted_at || $attempt->terminated_at) && ($attempt->theory_status ?? 'pending') !== 'marked')
                                                    <button wire:click="startForward({{ $attempt->id }})" class="block w-full px-3 py-2 text-left text-xs font-semibold text-violet-700 hover:bg-violet-50">Forward to Teacher</button>
                                                @endif
                                                <button wire:click="startIpOverride({{ $attempt->id }})" class="block w-full px-3 py-2 text-left text-xs font-semibold hover:bg-gray-50">Change IP</button>
                                                @if ($state === 'in_progress')
                                                    <button wire:click="forceSubmitAttempt({{ $attempt->id }})" class="block w-full px-3 py-2 text-left text-xs font-semibold text-blue-700 hover:bg-blue-50">Force Submit</button>
                                                    <button wire:click="terminateAttempt({{ $attempt->id }})" class="block w-full px-3 py-2 text-left text-xs font-semibold text-orange-700 hover:bg-orange-50">Terminate</button>
                                                @endif
                                                <button wire:click="resetAttempt({{ $attempt->id }})" class="block w-full px-3 py-2 text-left text-xs font-semibold text-rose-700 hover:bg-rose-50">Reset</button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border-2 border-dashed bg-gray-50 p-6 text-center text-sm text-gray-600">No students</div>
                        @endforelse
                    </div>

                    @if ($editingAttemptIpId)
                        <div class="mt-4 rounded-lg border border-violet-200 bg-violet-50 p-4">
                            <div class="text-sm font-semibold">Allow IP Override</div>
                            <div class="mt-2 flex gap-2">
                                <input wire:model="allowedIp" class="input flex-1 font-mono text-sm" placeholder="192.168.1.50" />
                                <button wire:click="saveIpOverride" class="btn-primary">Save</button>
                                <button wire:click="cancelIpOverride" class="btn-outline">Cancel</button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="space-y-2">
                        @forelse ($exam->attempts as $a)
                            <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3">
                                <div>
                                    <div class="text-sm font-semibold">{{ $a->student?->full_name ?? 'Student' }}</div>
                                    <div class="text-xs text-gray-500">{{ $a->student?->admission_number }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold">{{ (int) $a->score }}/{{ (int) $a->max_score }}</div>
                                    <div class="text-xs text-gray-500">{{ number_format((float) $a->percent, 1) }}%</div>
                                    @if ($hasTheory && ($a->submitted_at || $a->terminated_at))
                                        @php
                                            $theoryStatus = $a->theory_status ?? 'pending';
                                        @endphp
                                        @if ($theoryStatus === 'marked')
                                            <span class="mt-1 inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">✓ Marked</span>
                                        @elseif ($theoryStatus === 'forwarded')
                                            <span class="mt-1 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">→ Forwarded</span>
                                        @else
                                            <span class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-800">Pending</span>
                                        @endif
                                        <button wire:click="startReview({{ $a->id }})" class="mt-2 rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-200">
                                            {{ $theoryStatus === 'marked' ? 'View' : 'Mark' }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-lg border-2 border-dashed bg-gray-50 p-6 text-center text-sm text-gray-600">No attempts</div>
                        @endforelse
                    </div>
                @endif
            </div>
        @endif
    </div>

    @if ($hasTheory && $this->reviewAttempt)
        @php
            $reviewAttempt = $this->reviewAttempt;
            $theoryQuestions = $exam->questions->where('type', 'theory');
        @endphp
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Theory Review</div>
                    <div class="text-xs text-gray-500">
                        {{ $reviewAttempt->student?->full_name ?? 'Student' }} • {{ $reviewAttempt->student?->admission_number }}
                    </div>
                </div>
                <button wire:click="cancelReview" class="btn-outline">Close</button>
            </div>

            <div class="mt-4 space-y-4">
                @foreach ($theoryQuestions as $question)
                    @php
                        $answer = $reviewAttempt->answers->firstWhere('question_id', $question->id);
                        $response = trim((string) ($answer?->text_answer ?? ''));
                    @endphp
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="text-sm font-semibold text-gray-900">{{ $question->prompt }}</div>
                        <div class="mt-2 rounded-lg bg-white p-3 text-sm text-gray-700">
                            {{ $response !== '' ? $response : 'No answer submitted.' }}
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <label class="text-xs font-semibold uppercase text-gray-500">Marks ({{ (int) $question->marks }})</label>
                            <input
                                type="number"
                                min="0"
                                max="{{ (int) $question->marks }}"
                                wire:model.defer="theoryMarks.{{ $question->id }}"
                                class="input w-24 text-sm"
                            />
                            @error("theoryMarks.{$question->id}") <div class="text-xs text-rose-600">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                <button wire:click="saveTheoryMarks" class="btn-primary">Save Marks</button>
            </div>
        </div>
    @endif

    <div x-show="tab === 'actions'" class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="space-y-3">
            <a href="{{ route('cbt.exams.pdf', $exam) }}" target="_blank" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                <span class="text-xl">📄</span>
                <span>Download PDF</span>
            </a>
            @if ($status === 'approved' && $exam->access_code)
                <a href="{{ route('cbt.student', ['code' => $exam->access_code]) }}" target="_blank" class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                    <span class="text-xl">🎓</span>
                    <span>Student Portal</span>
                </a>
            @endif
            @if ($me?->role === 'admin')
                <a href="{{ route('cbt.exams.export', $exam) }}" class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700 transition hover:border-green-300 hover:bg-green-100">
                    <span class="text-xl">📊</span>
                    <span>Export CSV</span>
                </a>
                @if ($status === 'approved')
                    <button wire:click="transferToResults" onclick="return confirm('Transfer CBT scores to academic results?')" class="flex w-full items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-left text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                        <span class="text-xl">✅</span>
                        <span>Transfer to Results</span>
                    </button>
                    <button wire:click="endAllExams" onclick="return confirm('End this exam?')" class="flex w-full items-center gap-3 rounded-lg border border-orange-200 bg-orange-50 p-4 text-left text-sm font-semibold text-orange-700 transition hover:border-orange-300 hover:bg-orange-100">
                        <span class="text-xl">⏹️</span>
                        <span>End This Exam</span>
                    </button>
                @endif
                <button wire:click="$set('showDeleteModal', true)" class="flex w-full items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-left text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100">
                    <span class="text-xl">🗑️</span>
                    <span>Delete Exam</span>
                </button>
            @endif
        </div>
    </div>

    @if ($showDeleteModal ?? false)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data x-show="true" x-transition>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.away="$wire.set('showDeleteModal', false)">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Delete Exam</h3>
                        <p class="text-sm text-gray-600">This action cannot be undone</p>
                    </div>
                </div>
                
                <div class="mt-4 rounded-lg bg-red-50 p-4">
                    <p class="text-sm text-red-900">
                        Are you sure you want to delete <strong>{{ $exam->title }}</strong>? 
                        All questions, student attempts, and scores will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="deleteExam" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                        Delete Exam
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showForwardModal ?? false)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data x-show="true" x-transition>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.away="$wire.set('showForwardModal', false)">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100">
                        <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Forward to Teacher</h3>
                        <p class="text-sm text-gray-600">Assign theory marking</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="text-xs font-semibold uppercase text-gray-500">Select Teacher</label>
                    <select wire:model="forwardTeacherId" class="mt-1 select w-full">
                        <option value="">Choose teacher...</option>
                        @foreach ($this->availableTeachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('forwardTeacherId') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="cancelForward" class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="confirmForward" class="flex-1 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                        Forward
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>