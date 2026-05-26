<div class="space-y-6" x-data="{ viewNote: null }">
    {{-- Note Detail Modal --}}
    <template x-teleport="body">
        <div x-show="viewNote" x-transition.opacity
             class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             @click.self="viewNote = null" @keydown.escape.window="viewNote = null"
             style="display:none">
            <div x-show="viewNote" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
                <div class="relative px-6 py-8" style="background-color:#1a2e4a;">
                    <div class="absolute inset-0" style="background:radial-gradient(ellipse at top left,#1e3a5f 0%,transparent 60%);"></div>
                    <button @click="viewNote = null" class="absolute top-4 right-4 text-white/60 hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="relative">
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <span class="inline-flex items-center rounded-xl bg-white/10 px-2.5 py-1 text-[10px] font-bold text-blue-200 uppercase tracking-wider" x-text="viewNote?.term"></span>
                            <span class="inline-flex items-center rounded-xl bg-white/10 px-2.5 py-1 text-[10px] font-bold text-amber-300" x-text="viewNote?.subject"></span>
                            <span class="inline-flex items-center rounded-xl bg-white/10 px-2.5 py-1 text-[10px] font-bold text-slate-300" x-text="viewNote?.class"></span>
                        </div>
                        <h2 class="text-xl font-bold text-white" x-text="viewNote?.title"></h2>
                        <p class="mt-1 text-sm" style="color:#93c5fd;" x-text="viewNote?.description || 'No description provided.'"></p>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500 border border-slate-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-700 truncate" x-text="viewNote?.fileName"></p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="viewNote?.fileSize"></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>Uploaded by <strong class="text-slate-600" x-text="viewNote?.teacher"></strong></span>
                        <span x-text="viewNote?.downloads + ' downloads'"></span>
                    </div>
                    <button @click="$wire.downloadNote(viewNote.id); viewNote = null"
                            class="w-full flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-5 py-3 shadow-md transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download File
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Header Section --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative px-8 py-8">
            <div class="flex items-center gap-2 mb-3">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-sm font-semibold uppercase tracking-widest" style="color: #93c5fd;">E-Learning</span>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Class Notes Hub</h1>
            <p class="mt-2 text-base font-medium max-w-2xl leading-relaxed" style="color: #93c5fd;">
                Organize, access, and distribute digital course materials and lecture resources across subjects, classes, and terms.
            </p>
            @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                <div class="mt-5">
                    <button type="button" wire:click="$set('showCreateModal', true)"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white transition-all" style="background:rgba(255,255,255,0.12);">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Upload Class Note
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Filter & Search Panel --}}
    <div class="rounded-3xl border border-slate-200/60 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Search --}}
            <div class="relative">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Search Notes</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Type to search note title..."
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all"/>
                </div>
            </div>

            {{-- Class Filter --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Class</label>
                <select wire:model.live="selectedClass"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->level }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Subject Filter --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Subject</label>
                <select wire:model.live="selectedSubject"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Term Filter --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Term</label>
                <select wire:model.live="selectedTerm"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                    <option value="">All Terms</option>
                    <option value="First Term">First Term</option>
                    <option value="Second Term">Second Term</option>
                    <option value="Third Term">Third Term</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Grid Content --}}
    @if($notes->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center shadow-sm flex flex-col items-center justify-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 mb-4 shadow-inner">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">No class notes found</h3>
            <p class="mt-1.5 text-xs text-slate-400 font-medium max-w-sm">
                We couldn't find any class materials matching your criteria. Try adjusting the search filters or uploading new materials.
            </p>
            @if(in_array(auth()->user()->role, ['admin', 'teacher']))
                <button type="button" wire:click="$set('showCreateModal', true)"
                        class="mt-5 flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-md hover:bg-indigo-700 transition-colors">
                    Upload First Note
                </button>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($notes as $note)
                <div class="group relative rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300 flex flex-col justify-between overflow-hidden cursor-pointer"
                     @click="viewNote = { id: {{ $note->id }}, title: {{ Js::from($note->title) }}, description: {{ Js::from($note->description) }}, term: {{ Js::from($note->term_name) }}, subject: {{ Js::from($note->subject->name ?? '') }}, class: {{ Js::from($note->schoolClass->name ?? '') }}, fileName: {{ Js::from($note->file_name) }}, fileSize: {{ Js::from($note->file_size ?? '') }}, teacher: {{ Js::from($note->user->name ?? 'Teacher') }}, downloads: {{ $note->downloads }} }">
                    {{-- Interactive Glow Effect --}}
                    <div class="absolute right-0 top-0 -mr-6 -mt-6 h-20 w-20 rounded-full bg-indigo-500/5 group-hover:scale-[2] transition-transform duration-700"></div>
                    
                    <div class="relative space-y-4">
                        {{-- Badges Row --}}
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-xl bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-600 uppercase tracking-wider">
                                {{ $note->term_name }}
                            </span>
                            <span class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                                {{ $note->schoolClass->name ?? 'Class' }}
                            </span>
                            <span class="inline-flex items-center rounded-xl bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                                {{ $note->subject->name ?? 'Subject' }}
                            </span>
                        </div>

                        {{-- Title & Desc --}}
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                {{ $note->title }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-400 font-medium line-clamp-2 leading-relaxed h-8">
                                {{ $note->description ?: 'No description provided.' }}
                            </p>
                        </div>

                        {{-- File details card --}}
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-3 flex items-center gap-3">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white border border-slate-100 text-indigo-500 shadow-sm group-hover:scale-105 transition-transform">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-700 truncate" title="{{ $note->file_name }}">
                                    {{ $note->file_name }}
                                </p>
                                <div class="mt-0.5 flex items-center gap-2 text-[10px] text-slate-400 font-semibold">
                                    <span>{{ $note->file_size ?: '0 KB' }}</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                    <span>{{ $note->downloads }} {{ Str::plural('download', $note->downloads) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="relative mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-extrabold text-[10px]">
                                {{ mb_strtoupper(mb_substr($note->user->name ?? 'T', 0, 1)) }}
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 truncate" title="Uploaded by {{ $note->user->name ?? 'Teacher' }}">
                                By {{ $note->user->name ?? 'Teacher' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(auth()->user()->is_super_admin || auth()->id() === $note->user_id)
                                <button type="button" wire:click="triggerDelete({{ $note->id }})" @click.stop title="Delete note"
                                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition-colors">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif

                            <button type="button" wire:click="downloadNote({{ $note->id }})" @click.stop
                                    class="flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold px-3 py-1.5 shadow-sm transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Create Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto animate-fade-in">
            <div class="w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden transform transition-all duration-300 scale-95 opacity-100 flex flex-col">
                {{-- Modal Header --}}
                <div class="relative bg-gradient-to-r from-violet-600 to-indigo-600 px-6 py-4.5 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold">Upload Digital Class Note</h2>
                        <p class="text-[10px] text-indigo-100/90 font-medium mt-0.5">Share notes, lectures, and resources with students.</p>
                    </div>
                    <button type="button" wire:click="$set('showCreateModal', false)" class="text-white/80 hover:text-white rounded-lg p-1.5 hover:bg-white/10 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit.prevent="saveNote" class="p-6 space-y-4.5">
                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Note Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" placeholder="e.g. Introduction to Quadratic Equations"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all"/>
                        @error('title') <span class="mt-1 block text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- Row Class & Subject --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Target Class <span class="text-red-500">*</span></label>
                            <select wire:model="class_id"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <option value="">Select Class</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('class_id') <span class="mt-1 block text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Subject <span class="text-red-500">*</span></label>
                            <select wire:model="subject_id"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id') <span class="mt-1 block text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row Term --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Academic Term <span class="text-red-500">*</span></label>
                        <select wire:model="term_name"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            <option value="First Term">First Term</option>
                            <option value="Second Term">Second Term</option>
                            <option value="Third Term">Third Term</option>
                        </select>
                        @error('term_name') <span class="mt-1 block text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Description / Learning Objectives</label>
                        <textarea wire:model="description" rows="3" placeholder="Brief details about what topics or weeks are covered in this note..."
                                  class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                        @error('description') <span class="mt-1 block text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- File Upload box --}}
                    <div x-data="{ isDragging: false, progress: 0 }"
                         x-on:livewire-upload-start="progress = 0"
                         x-on:livewire-upload-finish="progress = 100"
                         x-on:livewire-upload-progress="progress = $event.detail.progress"
                         class="relative">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Upload File <span class="text-red-500">*</span> <span class="text-[10px] lowercase text-slate-400">(PDF, DOC, PDF, ZIP — Max 10MB)</span></label>
                        
                        <div :class="isDragging ? 'border-indigo-500 bg-indigo-50/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'"
                             class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-6 text-center transition-all relative overflow-hidden group cursor-pointer">
                            
                            <input type="file" wire:model="file" id="class-note-file"
                                   @dragover="isDragging = true" @dragleave="isDragging = false" @drop="isDragging = false"
                                   class="absolute inset-0 opacity-0 cursor-pointer z-10"/>

                            @if($file)
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 mb-2 shadow-inner">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700 max-w-[280px] truncate">{{ $file->getClientOriginalName() }}</p>
                                <p class="text-[10px] text-emerald-500 font-bold mt-1">Ready for upload!</p>
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 mb-2 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Drag your file here or <span class="text-indigo-600 group-hover:underline">browse</span></p>
                            @endif

                            {{-- Live Upload Progress Bar --}}
                            <div x-show="progress > 0 && progress < 100" style="display:none" class="absolute bottom-0 inset-x-0 h-1 bg-slate-200">
                                <div class="h-full bg-indigo-600 transition-all duration-150" :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>
                        @error('file') <span class="mt-1 block text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                                class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="flex items-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-5 py-2.5 shadow-md disabled:opacity-50 transition-colors">
                            <span wire:loading wire:target="saveNote" class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Upload Note</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($confirmingDeleteId)
        <div class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto animate-fade-in">
            <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl border border-slate-100 p-6 space-y-5 text-center transform transition-all duration-300 scale-95 opacity-100">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-500 shadow-inner">
                    <svg class="h-7 w-7 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-extrabold text-slate-800">Delete Class Note?</h3>
                    <p class="text-xs text-slate-400 font-medium leading-relaxed">
                        Are you sure you want to permanently delete this class note resource? This action cannot be undone and will delete the file from cloud storage.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-3">
                    <button type="button" wire:click="cancelDelete"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                        Cancel, Keep
                    </button>
                    <button type="button" wire:click="deleteNote"
                            class="rounded-xl bg-red-500 hover:bg-red-600 text-white text-xs font-bold px-5 py-2.5 shadow-md shadow-red-100 transition-colors">
                        Yes, Delete Note
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</div>
