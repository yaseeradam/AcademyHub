<div class="space-y-6">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 via-indigo-500 to-blue-600 p-6 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-20"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">My Homework</h1>
                <p class="mt-1 text-sm text-purple-100">View and submit your assignments</p>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $pendingCount = $homework->filter(fn($h) => $h->submissions->isEmpty() && $h->due_date >= now())->count();
                    $overdueCount = $homework->filter(fn($h) => $h->submissions->isEmpty() && $h->due_date < now())->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="rounded-xl bg-yellow-400/20 px-3 py-1.5 text-sm font-bold text-yellow-100 backdrop-blur-sm border border-yellow-300/30">
                        {{ $pendingCount }} Pending
                    </span>
                @endif
                @if($overdueCount > 0)
                    <span class="rounded-xl bg-red-400/20 px-3 py-1.5 text-sm font-bold text-red-100 backdrop-blur-sm border border-red-300/30">
                        {{ $overdueCount }} Overdue
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-1 rounded-2xl bg-white p-1.5 shadow-sm border border-gray-100">
        @foreach(['pending' => 'Pending', 'overdue' => 'Overdue', 'submitted' => 'Submitted', 'all' => 'All'] as $key => $label)
            <button wire:click="setFilter('{{ $key }}')"
                class="flex-1 rounded-xl px-3 py-2 text-sm font-semibold transition-all {{ $filter === $key ? 'bg-gradient-to-r from-purple-500 to-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Homework List --}}
    @if($homework->isEmpty())
        <div class="rounded-2xl bg-white p-12 text-center shadow-sm border border-gray-100">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-50">
                <svg class="h-8 w-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">No homework found</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if($filter === 'pending') You're all caught up! Great job!
                @elseif($filter === 'overdue') No overdue assignments.
                @elseif($filter === 'submitted') No submitted assignments yet.
                @else No homework has been assigned yet.
                @endif
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($homework as $hw)
                @php
                    $submitted = $hw->submissions->isNotEmpty();
                    $overdue = !$submitted && $hw->due_date < now();
                    $pending = !$submitted && !$overdue;
                @endphp
                <div class="group rounded-2xl bg-white border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
                    {{-- Color accent bar --}}
                    <div class="h-1 w-full {{ $submitted ? 'bg-gradient-to-r from-green-400 to-emerald-500' : ($overdue ? 'bg-gradient-to-r from-red-400 to-rose-500' : 'bg-gradient-to-r from-purple-400 to-indigo-500') }}"></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                {{-- Title + badge --}}
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h3 class="text-base font-bold text-gray-900 truncate">{{ $hw->title }}</h3>
                                    @if($submitted)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Submitted
                                        </span>
                                    @elseif($overdue)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                            Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                            Pending
                                        </span>
                                    @endif
                                </div>

                                {{-- Meta --}}
                                <div class="flex flex-wrap gap-3 text-xs text-gray-500 mb-3">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        {{ $hw->subject->name }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Due {{ $hw->due_date->format('M d, Y') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $hw->teacher->name }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">{{ $hw->content }}</p>
                            </div>

                            {{-- Action button --}}
                            <div class="flex-shrink-0">
                                @if($submitted)
                                    <button wire:click="selectHomework({{ $hw->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-green-50 border border-green-200 px-4 py-2 text-sm font-bold text-green-700 hover:bg-green-100 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </button>
                                @elseif($overdue)
                                    <button wire:click="selectHomework({{ $hw->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-red-50 border border-red-200 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-100 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        Submit Late
                                    </button>
                                @else
                                    <button wire:click="selectHomework({{ $hw->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:from-purple-600 hover:to-indigo-700 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        Submit
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Slide-in Panel --}}
    @if($selectedHomework)
        {{-- Overlay --}}
        <div class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm" wire:click="$set('selectedHomework', null)"></div>

        {{-- Panel --}}
        <div class="fixed inset-y-0 right-0 z-50 flex w-full flex-col bg-white shadow-2xl sm:max-w-xl">

            {{-- Panel Header --}}
            <div class="flex-shrink-0 bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-purple-200 mb-1">{{ $selectedHomework->subject->name }}</p>
                        <h2 class="text-lg font-bold text-white leading-snug">{{ $selectedHomework->title }}</h2>
                        <div class="mt-2 flex flex-wrap gap-3 text-xs text-purple-200">
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Due {{ $selectedHomework->due_date->format('M d, Y') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $selectedHomework->teacher->name }}
                            </span>
                        </div>
                    </div>
                    <button wire:click="$set('selectedHomework', null)" class="flex-shrink-0 rounded-xl bg-white/10 p-2 text-white hover:bg-white/20 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Panel Body --}}
            <div class="flex-1 overflow-y-auto">

                {{-- Assignment content --}}
                <div class="px-6 py-5 border-b border-gray-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Assignment</p>
                    <div class="rounded-xl bg-gray-50 border border-gray-200 p-4">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $selectedHomework->content }}</p>
                    </div>
                </div>

                @if($selectedHomework->submissions->isNotEmpty())
                    {{-- Already submitted --}}
                    <div class="px-6 py-5">
                        <div class="mb-4 flex items-center gap-3 rounded-xl bg-green-50 border border-green-200 p-4">
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-green-500">
                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-green-800">Submitted!</p>
                                <p class="text-xs text-green-600">{{ $selectedHomework->submissions->first()->submitted_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                        </div>

                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Your Answer</p>
                        <div class="rounded-xl bg-white border-2 border-green-200 p-4">
                            <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $selectedHomework->submissions->first()->submission }}</p>
                        </div>

                        @if($selectedHomework->submissions->first()->attachment)
                            <a href="{{ asset('storage/' . $selectedHomework->submissions->first()->attachment) }}"
                               target="_blank"
                               class="mt-4 inline-flex items-center gap-2 rounded-xl bg-green-50 border border-green-200 px-4 py-2.5 text-sm font-semibold text-green-700 hover:bg-green-100 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                View Attachment
                            </a>
                        @endif

                        @php $sub = $selectedHomework->submissions->first(); @endphp
                        @if($sub->grade !== null || $sub->feedback)
                            <div class="mt-5 rounded-xl border-2 border-indigo-200 bg-indigo-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-400 mb-3">Teacher Feedback</p>
                                @if($sub->grade !== null)
                                    <div class="mb-3 flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-white font-black text-lg">
                                            {{ (int) $sub->grade }}
                                        </div>
                                        <div>
                                            <p class="text-xs text-indigo-500">Your Score</p>
                                            <p class="text-sm font-bold text-indigo-800">{{ $sub->grade }}/100</p>
                                        </div>
                                    </div>
                                @endif
                                @if($sub->feedback)
                                    <p class="text-sm text-indigo-900 leading-relaxed whitespace-pre-wrap">{{ $sub->feedback }}</p>
                                @endif
                                @if($sub->graded_at)
                                    <p class="mt-2 text-xs text-indigo-400">Graded {{ $sub->graded_at->format('M d, Y') }}</p>
                                @endif
                            </div>
                        @else
                            <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                                <p class="text-xs text-gray-400">Not graded yet — check back later.</p>
                            </div>
                        @endif
                    </div>

                @else
                    {{-- Submission form --}}
                    <form wire:submit.prevent="submitHomework" class="px-6 py-5 space-y-5">

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Your Answer <span class="text-red-500">*</span></label>
                            <textarea wire:model="submission"
                                rows="8"
                                placeholder="Write your answer here. Be clear and thorough..."
                                class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 p-4 text-sm text-gray-800 placeholder-gray-400 focus:border-purple-400 focus:bg-white focus:ring-2 focus:ring-purple-100 transition resize-none"></textarea>
                            @error('submission')
                                <p class="mt-1.5 flex items-center gap-1 text-xs text-red-600">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Attachment <span class="text-gray-400 font-normal normal-case">(optional)</span></label>
                            <input type="file" wire:model="attachment" id="hw-attachment" class="hidden">
                            <label for="hw-attachment"
                                class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-4 py-4 hover:border-purple-300 hover:bg-purple-50 transition group">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white border border-gray-200 group-hover:border-purple-200 transition">
                                    <svg class="h-5 w-5 text-gray-400 group-hover:text-purple-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 group-hover:text-purple-700 transition">Click to upload</p>
                                    <p class="text-xs text-gray-400">PDF, DOC, JPG, PNG — max 10MB</p>
                                </div>
                            </label>

                            @if($attachment)
                                <div class="mt-2 flex items-center gap-2 rounded-xl bg-purple-50 border border-purple-200 px-3 py-2.5">
                                    <svg class="h-4 w-4 flex-shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="flex-1 truncate text-xs font-semibold text-purple-800">{{ $attachment->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="$set('attachment', null)" class="text-purple-400 hover:text-purple-700 transition">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endif

                            @error('attachment')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit button (sticky at bottom of form) --}}
                        <div class="flex gap-3 pt-2">
                            <button type="button" wire:click="$set('selectedHomework', null)"
                                class="flex-1 rounded-xl border-2 border-gray-200 bg-white py-3 text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                class="flex-1 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 py-3 text-sm font-bold text-white shadow-md hover:from-purple-600 hover:to-indigo-700 transition">
                                <span wire:loading.remove wire:target="submitHomework" class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    Submit Homework
                                </span>
                                <span wire:loading wire:target="submitHomework" class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Submitting...
                                </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
