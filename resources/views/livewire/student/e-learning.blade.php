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
            <h1 class="text-3xl font-bold text-white tracking-tight">My Learning Materials</h1>
            <p class="mt-2 text-base font-medium max-w-2xl leading-relaxed" style="color: #93c5fd;">
                Access worksheets, lecture notes, and digital resources uploaded by your teachers for <strong class="text-white">{{ $student->schoolClass->name ?? 'your class' }}</strong>.
            </p>
        </div>
    </div>

    {{-- Filter & Search Panel --}}
    <div class="rounded-3xl border border-slate-200/60 bg-white p-5 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Search --}}
            <div class="relative">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Search Notes</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title or topic..."
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-10 pr-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all"/>
                </div>
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
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Academic Term</label>
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
            <h3 class="text-base font-bold text-slate-800">No learning materials shared yet</h3>
            <p class="mt-1.5 text-xs text-slate-400 font-medium max-w-sm">
                Your teachers haven't uploaded any documents or course files for your class yet. Check back soon!
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($notes as $note)
                <div class="group relative rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300 flex flex-col justify-between overflow-hidden cursor-pointer"
                     @click="viewNote = { id: {{ $note->id }}, title: {{ Js::from($note->title) }}, description: {{ Js::from($note->description) }}, term: {{ Js::from($note->term_name) }}, subject: {{ Js::from($note->subject->name ?? '') }}, fileName: {{ Js::from($note->file_name) }}, fileSize: {{ Js::from($note->file_size ?? '') }}, teacher: {{ Js::from($note->user->name ?? 'Teacher') }}, downloads: {{ $note->downloads }} }">
                    {{-- Decorative interactive glow --}}
                    <div class="absolute right-0 top-0 -mr-6 -mt-6 h-20 w-20 rounded-full bg-indigo-500/5 group-hover:scale-[2] transition-transform duration-700"></div>

                    <div class="relative space-y-4">
                        {{-- Badges row --}}
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-xl bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-600 uppercase tracking-wider">
                                {{ $note->term_name }}
                            </span>
                            <span class="inline-flex items-center rounded-xl bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700">
                                {{ $note->subject->name ?? 'Subject' }}
                            </span>
                        </div>

                        {{-- Title & description --}}
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-1">
                                {{ $note->title }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-400 font-medium line-clamp-2 leading-relaxed h-8">
                                {{ $note->description ?: 'Read note description or instructions.' }}
                            </p>
                        </div>

                        {{-- File details --}}
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
                                <p class="mt-0.5 text-[10px] text-slate-400 font-semibold">
                                    {{ $note->file_size ?: '0 KB' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Card footer with download action --}}
                    <div class="relative mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-extrabold text-[10px]">
                                {{ mb_strtoupper(mb_substr($note->user->name ?? 'T', 0, 1)) }}
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 truncate">
                                By {{ $note->user->name ?? 'Teacher' }}
                            </span>
                        </div>

                        <button type="button" wire:click="downloadNote({{ $note->id }})" @click.stop
                                class="flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold px-4.5 py-2 shadow-sm transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
