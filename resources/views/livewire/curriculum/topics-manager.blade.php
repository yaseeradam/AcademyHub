<div class="space-y-6 pb-12 font-sans">
    
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-5 sm:p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-10"></div>
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-violet-600/20 blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-indigo-600/20 blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5 sm:gap-6">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 mb-2.5 sm:mb-3 backdrop-blur-md">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Academic Curriculum</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">Scheme of Work &amp; Topics</h1>
                <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-slate-300 font-medium max-w-xl">
                    Define and track the syllabus topics covered in each subject so parents and students have full visibility into the academic curriculum.
                </p>
            </div>

            @if(auth()->user()?->role !== 'proprietor')
                <div class="shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full sm:w-auto">
                    <button wire:click="openImportModal"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-3 text-sm font-bold text-white transition-all shadow-sm active:scale-95">
                        <svg class="h-5 w-5 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Bulk Import CSV
                    </button>
                    <button wire:click="openCreateModal" 
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-500/30 transition-all hover:from-violet-700 hover:to-indigo-700 hover:shadow-xl active:scale-95">
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
    <div class="rounded-3xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
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
        {{-- Term Syllabus Document Card (Upload & Download Hub) --}}
        @php
            $doc = $this->curriculumDocument;
        @endphp
        <div class="rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-50/50 via-white to-purple-50/30 p-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-md">Official Syllabus Document</span>
                            @if($doc)
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">✓ Uploaded</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 bg-amber-100 px-2 py-0.5 rounded-md">Not Yet Uploaded</span>
                            @endif
                        </div>
                        <h3 class="text-base font-black text-slate-800">
                            {{ $this->selectedSubject?->name }} — Term {{ $term == 1 ? 'One' : ($term == 2 ? 'Two' : 'Three') }} Curriculum File
                        </h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                            @if($doc)
                                {{ $doc->file_name }} ({{ $doc->formatted_file_size }}) • Uploaded {{ $doc->created_at?->diffForHumans() }} by {{ $doc->uploader?->name ?? 'Staff' }}
                            @else
                                Upload the master syllabus PDF or Word document so parents and teachers can download the official scheme anytime.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Action Controls --}}
                <div class="flex flex-wrap items-center gap-3">
                    @if($doc)
                        <a href="{{ route('curriculum.document.download', $doc) }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 text-xs font-black shadow-sm transition active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Document ({{ strtoupper($doc->file_type) }})
                        </a>
                        @if(auth()->user()?->role !== 'proprietor')
                            <button wire:click="deleteCurriculumDocument({{ $doc->id }})" wire:confirm="Are you sure you want to remove this syllabus document?"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 px-3.5 py-2.5 text-xs font-black transition active:scale-95">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        @endif
                    @endif

                    @if(auth()->user()?->role !== 'proprietor')
                        <div x-data="{ uploading: false, progress: 0 }" 
                             x-on:livewire-upload-start="uploading = true" 
                             x-on:livewire-upload-finish="uploading = false" 
                             x-on:livewire-upload-error="uploading = false" 
                             x-on:livewire-upload-progress="progress = $event.detail.progress"
                             class="flex items-center gap-2">
                            <label class="cursor-pointer inline-flex items-center gap-2 rounded-xl {{ $doc ? 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm' }} px-4 py-2.5 text-xs font-black transition active:scale-95">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span>{{ $doc ? 'Replace Document' : 'Upload Syllabus (PDF/DOCX)' }}</span>
                                <input type="file" wire:model="curriculumDocFile" class="hidden" accept=".pdf,.doc,.docx" />
                            </label>

                            <div x-show="uploading" class="flex items-center gap-2 text-xs font-bold text-indigo-600">
                                <div class="h-4 w-4 animate-spin rounded-full border-2 border-indigo-600 border-t-transparent"></div>
                                <span x-text="progress + '%'"></span>
                            </div>

                            @if($curriculumDocFile)
                                <button type="button" wire:click="uploadCurriculumDocument" wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2.5 text-xs font-black shadow-sm transition active:scale-95">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Confirm Save
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @error('curriculumDocFile')
                <div class="mt-3 text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-200">
                    {{ $message }}
                </div>
            @enderror
        </div>

        @php
            $stats = $this->stats;
        @endphp

        {{-- Stat Cards & Progress --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-5 shadow-xs">
                <div class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-400">Total Topics</div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-slate-800">{{ $stats['total'] }}</div>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-3.5 sm:p-5 shadow-xs">
                <div class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-emerald-600">Covered / Done</div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-emerald-700">{{ $stats['completed'] }}</div>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-3.5 sm:p-5 shadow-xs">
                <div class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-amber-600">In Progress</div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-amber-700">{{ $stats['inProgress'] }}</div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 sm:p-5 shadow-xs">
                <div class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-500">Upcoming</div>
                <div class="mt-1.5 sm:mt-2 text-xl sm:text-2xl font-black text-slate-700">{{ $stats['upcoming'] }}</div>
            </div>
            <div class="col-span-2 sm:col-span-1 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-3.5 sm:p-5 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-indigo-600">
                    <span>Syllabus Covered</span>
                    <span class="font-black">{{ $stats['percent'] }}%</span>
                </div>
                <div class="w-full bg-indigo-100 h-2.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $stats['percent'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Row --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4">
            {{-- Status Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar" style="-webkit-overflow-scrolling: touch; touch-action: pan-x;">
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
            <div class="relative w-full sm:w-72">
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
                <div class="rounded-2xl border {{ $borderClass }} p-4 sm:p-5 shadow-xs transition hover:shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
                        <div class="flex items-start gap-3 sm:gap-4 flex-1">
                            {{-- Week Badge --}}
                            <div class="shrink-0 flex flex-col items-center justify-center rounded-xl bg-slate-100 px-2.5 py-1.5 sm:px-3 sm:py-2 border border-slate-200 min-w-[3.5rem] sm:min-w-[4rem]">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">WEEK</span>
                                <span class="text-base sm:text-lg font-black text-slate-800">{{ $topic->week_number ? sprintf('%02d', $topic->week_number) : '--' }}</span>
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

                                <h3 class="text-sm sm:text-base font-black text-slate-800 leading-snug">{{ $topic->title }}</h3>

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
                                        class="h-9 w-9 sm:h-8 sm:w-8 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center transition" title="Edit Topic">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" wire:confirm="Are you sure you want to remove this topic from the curriculum?" wire:click="deleteTopic({{ $topic->id }})" 
                                        class="h-9 w-9 sm:h-8 sm:w-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center transition" title="Delete Topic">
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

    {{-- Bulk Import CSV / Text Modal --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs animate-fade-in" x-data>
            <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl space-y-5 animate-scale-up" @click.outside="$wire.set('showImportModal', false)">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Bulk Import Scheme Topics</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-0.5">{{ $this->selectedClass?->name }} • {{ $this->selectedSubject?->name }} (Term {{ $term }})</p>
                    </div>
                    <button type="button" wire:click="$set('showImportModal', false)" class="h-8 w-8 rounded-full bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center">
                        ✕
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Download sample template notice --}}
                    <div class="rounded-2xl bg-indigo-50/60 border border-indigo-100 p-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-black text-indigo-900">Download CSV Sample Template</div>
                            <div class="text-[11px] text-indigo-700/80 font-medium mt-0.5">Use our pre-formatted spreadsheet to fill out your weekly topics.</div>
                        </div>
                        <button type="button" wire:click="downloadCsvTemplate"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 text-xs font-black shadow-xs transition active:scale-95">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Sample CSV
                        </button>
                    </div>

                    {{-- Option A: Upload CSV File --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Option 1: Upload CSV / Excel Spreadsheet</label>
                        <input type="file" wire:model="csvFile" accept=".csv,.txt"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer rounded-2xl border border-slate-200 p-2 bg-slate-50" />
                        @error('csvFile') <span class="text-[11px] text-rose-500 font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="relative flex py-1 items-center">
                        <div class="flex-grow border-t border-slate-200"></div>
                        <span class="flex-shrink mx-4 text-[10px] font-black uppercase text-slate-400">OR</span>
                        <div class="flex-grow border-t border-slate-200"></div>
                    </div>

                    {{-- Option B: Quick Paste Text --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Option 2: Quick Paste Topics List</label>
                        <textarea wire:model="rawTextTopics" rows="5" placeholder="Paste your weekly outline here. Examples:&#10;Week 1: Whole Numbers - Place value and operations&#10;Week 2: Fractions - Proper, improper and mixed fractions&#10;Week 3: Decimals - Adding and subtracting decimals"
                                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:bg-white"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1 font-semibold">Formats accepted: <code class="bg-slate-100 px-1 py-0.5 rounded">Week 1: Topic - Objectives</code> or <code class="bg-slate-100 px-1 py-0.5 rounded">1. Topic Title</code></p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="$set('showImportModal', false)" class="rounded-xl px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-100 transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="importCsvTopics" class="rounded-xl bg-indigo-600 hover:bg-indigo-700 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/20 transition active:scale-95"
                                wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                            Import &amp; Generate Scheme
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
