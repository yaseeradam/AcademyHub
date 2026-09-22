<div class="space-y-6 pb-12 font-sans">
    
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-10"></div>
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-violet-600/20 blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-indigo-600/20 blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 mb-3 backdrop-blur-md">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Academic Curriculum</span>
                </div>
                <h1 class="text-3xl font-black text-white sm:text-4xl tracking-tight">Scheme of Work &amp; Topics</h1>
                <p class="mt-2 text-sm text-slate-300 font-medium max-w-xl">
                    Define and track the syllabus topics covered in each subject so parents and students have full visibility into the academic curriculum.
                </p>
            </div>

            @if(auth()->user()?->role !== 'proprietor')
                <div class="shrink-0">
                    <button wire:click="openCreateModal" 
                            class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-violet-500/30 transition-all hover:from-violet-700 hover:to-indigo-700 hover:shadow-xl active:scale-95">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add New Topic
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Filter Selectors Toolbar --}}
    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Class Picker --}}
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">Class / Level</label>
                <select wire:model.live="classId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20">
                    @forelse($this->classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @empty
                        <option value="">No classes allocated</option>
                    @endforelse
                </select>
            </div>

            {{-- Subject Picker --}}
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">Subject</label>
                <select wire:model.live="subjectId" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20">
                    @forelse($this->subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code ?? 'N/A' }})</option>
                    @empty
                        <option value="">No subjects found for this class</option>
                    @endforelse
                </select>
            </div>

            {{-- Term Picker --}}
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">Academic Term</label>
                <select wire:model.live="term" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20">
                    <option value="1">First Term (Term 1)</option>
                    <option value="2">Second Term (Term 2)</option>
                    <option value="3">Third Term (Term 3)</option>
                </select>
            </div>

            {{-- Academic Session --}}
            <div>
                <label class="block text-[11px] font-black uppercase tracking-wider text-slate-500 mb-1.5">Session</label>
                <input wire:model.live.debounce.400ms="session" type="text" placeholder="e.g. 2024/2025" 
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20" />
            </div>
        </div>
    </div>

    @if($this->classId && $this->subjectId)
        @php
            $stats = $this->stats;
        @endphp

        {{-- Stat Cards & Progress --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Topics</div>
                <div class="mt-2 text-2xl font-black text-slate-800">{{ $stats['total'] }}</div>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-xs">
                <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Covered / Done</div>
                <div class="mt-2 text-2xl font-black text-emerald-700">{{ $stats['completed'] }}</div>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-5 shadow-xs">
                <div class="text-[10px] font-black uppercase tracking-wider text-amber-600">In Progress</div>
                <div class="mt-2 text-2xl font-black text-amber-700">{{ $stats['inProgress'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-xs">
                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500">Upcoming</div>
                <div class="mt-2 text-2xl font-black text-slate-700">{{ $stats['upcoming'] }}</div>
            </div>
            <div class="col-span-2 lg:col-span-1 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-indigo-600">
                    <span>Syllabus Covered</span>
                    <span>{{ $stats['percent'] }}%</span>
                </div>
                <div class="w-full bg-indigo-100 h-2.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $stats['percent'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Row --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            {{-- Status Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                @php
                    $filters = [
                        'all'         => 'All Topics',
                        'completed'   => 'Completed',
                        'in_progress' => 'In Progress',
                        'upcoming'    => 'Upcoming',
                    ];
                @endphp
                @foreach($filters as $key => $label)
                    <button type="button" wire:click="$set('statusFilter', '{{ $key }}')" 
                            class="rounded-xl px-3.5 py-1.5 text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === $key ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Search Bar --}}
            <div class="relative sm:w-72">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search topics or objectives..." 
                       class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" />
                <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- Topics List --}}
        <div class="space-y-3">
            @forelse($this->topics as $topic)
                @php
                    $isCompleted = $topic->status === 'completed';
                    $isInProgress = $topic->status === 'in_progress';
                    $borderClass = $isCompleted ? 'border-emerald-200 bg-white' : ($isInProgress ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200 bg-white');
                @endphp
                <div class="rounded-2xl border {{ $borderClass }} p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex items-start gap-4 flex-1">
                            {{-- Week Badge --}}
                            <div class="shrink-0 flex flex-col items-center justify-center rounded-xl bg-slate-100 px-3 py-2 border border-slate-200 min-w-[4rem]">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">WEEK</span>
                                <span class="text-lg font-black text-slate-800">{{ $topic->week_number ? sprintf('%02d', $topic->week_number) : '--' }}</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    {{-- Status Trigger Button --}}
                                    @if(auth()->user()?->role !== 'proprietor')
                                        <button type="button" wire:click="toggleStatus({{ $topic->id }})" title="Click to cycle status"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider transition-transform active:scale-95 {{ $isCompleted ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($isInProgress ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                            @if($isCompleted)
                                                <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                Completed
                                            @elseif($isInProgress)
                                                <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                In Progress
                                            @else
                                                <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                                                Upcoming
                                            @endif
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $isCompleted ? 'bg-emerald-100 text-emerald-800' : ($isInProgress ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                            {{ ucfirst(str_replace('_', ' ', $topic->status)) }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-black text-slate-800 leading-snug">{{ $topic->title }}</h3>

                                @if($topic->learning_objectives)
                                    <p class="mt-2 text-xs text-slate-600 font-medium leading-relaxed whitespace-pre-line bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        {{ $topic->learning_objectives }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        @if(auth()->user()?->role !== 'proprietor')
                            <div class="shrink-0 flex items-center gap-2 self-end sm:self-center">
                                <button type="button" wire:click="editTopic({{ $topic->id }})" 
                                        class="h-8 w-8 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center transition" title="Edit Topic">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" wire:confirm="Are you sure you want to remove this topic from the curriculum?" wire:click="deleteTopic({{ $topic->id }})" 
                                        class="h-8 w-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center transition" title="Delete Topic">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white p-12 text-center shadow-xs">
                    <div class="mx-auto h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mb-3">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No topics added yet for Term {{ $term }}</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto font-medium">
                        Start structuring the scheme of work for this subject. Once added, parents and students will be able to see the scheduled topics and follow along with classroom learning.
                    </p>
                    @if(auth()->user()?->role !== 'proprietor')
                        <button type="button" wire:click="openCreateModal" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add First Topic
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
    @else
        <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white p-12 text-center text-sm font-semibold text-slate-400">
            Please choose an active class and subject above to manage its curriculum topics.
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs animate-fade-in" x-data>
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl space-y-5 animate-scale-up" @click.outside="$wire.set('showModal', false)">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">{{ $editingId ? 'Edit Curriculum Topic' : 'Add Curriculum Topic' }}</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $this->selectedClass?->name }} • {{ $this->selectedSubject?->name }} (Term {{ $term }})</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="h-8 w-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                        ✕
                    </button>
                </div>

                <form wire:submit="saveTopic" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Week Number *</label>
                            <input wire:model="weekNumber" type="number" min="1" max="52" required
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:bg-white" />
                            @error('weekNumber') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Status *</label>
                            <select wire:model="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:bg-white">
                                <option value="upcoming">Upcoming</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed / Covered</option>
                            </select>
                            @error('status') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Topic Title *</label>
                        <input wire:model="title" type="text" placeholder="e.g., Photosynthesis and Cellular Respiration" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:border-indigo-500 focus:bg-white" />
                        @error('title') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Learning Objectives / Description</label>
                        <textarea wire:model="learningObjectives" rows="3" placeholder="Outline specific learning outcomes, key concepts, or homework references..."
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:bg-white"></textarea>
                        @error('learningObjectives') <span class="text-[11px] text-rose-500 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-xl px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/20 transition active:scale-95"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            {{ $editingId ? 'Update Topic' : 'Add to Curriculum' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
