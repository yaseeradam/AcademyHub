<div class="space-y-8" x-data="{ 
    selectedId: @entangle('selectedHomeworkId'),
    isMobile: window.innerWidth < 1024,
    init() {
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 1024;
        });
    }
}">

    @php
        $pendingCount   = $homework->filter(fn($h) => $h->submissions->isEmpty() && $h->due_date >= now())->count();
        $overdueCount   = $homework->filter(fn($h) => $h->submissions->isEmpty() && $h->due_date < now())->count();
        $submittedCount = $homework->filter(fn($h) => $h->submissions->isNotEmpty())->count();
        $selectedHomework = $this->selectedHomework;
    @endphp

    {{-- Page title and Stats --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-8 bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-950 rounded-[2.5rem] p-6 sm:p-10 text-white shadow-2xl shadow-indigo-900/30 relative overflow-hidden">
        <!-- Decorative bg -->
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5"></div>

        <div class="relative z-10 max-w-2xl">
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight mb-4 leading-tight">My Homework</h1>
            <p class="text-indigo-200 font-medium text-base sm:text-xl">Manage your assignments and track your academic progress with ease.</p>
        </div>
        
        <div class="grid grid-cols-3 sm:flex items-center gap-3 sm:gap-6 relative z-10 w-full xl:w-auto">
            <div class="bg-white/10 backdrop-blur-xl rounded-[2rem] px-4 py-4 sm:px-8 sm:py-6 border border-white/20 text-center shadow-lg flex-1">
                <span class="block text-2xl sm:text-4xl font-black text-amber-300 leading-none">{{ $pendingCount }}</span>
                <span class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-indigo-200 mt-2">Pending</span>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-[2rem] px-4 py-4 sm:px-8 sm:py-6 border border-white/20 text-center shadow-lg flex-1">
                <span class="block text-2xl sm:text-4xl font-black text-rose-300 leading-none">{{ $overdueCount }}</span>
                <span class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-indigo-200 mt-2">Overdue</span>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-[2rem] px-4 py-4 sm:px-8 sm:py-6 border border-white/20 text-center shadow-lg flex-1">
                <span class="block text-2xl sm:text-4xl font-black text-emerald-300 leading-none">{{ $submittedCount }}</span>
                <span class="block text-[10px] sm:text-xs font-black uppercase tracking-widest text-indigo-200 mt-2">Done</span>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        
        {{-- Left Pane: List & Filters --}}
        <div class="w-full lg:w-[380px] xl:w-[420px] flex-shrink-0 space-y-6" :class="selectedId && isMobile ? 'hidden' : 'block'">
            {{-- Filter tabs --}}
            <div class="flex p-1.5 bg-gray-100 rounded-2xl overflow-x-auto no-scrollbar">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'overdue' => 'Overdue', 'submitted' => 'Done'] as $key => $label)
                    <button wire:click="setFilter('{{ $key }}')"
                        class="flex-1 whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-300
                            {{ $filter === $key
                                ? 'bg-white text-indigo-600 shadow-md scale-105'
                                : 'text-gray-500 hover:text-gray-900' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search assignments..."
                    class="w-full rounded-2xl border-2 border-gray-100 bg-white py-3 pl-11 pr-4 text-sm font-bold text-gray-900 placeholder-gray-400 shadow-sm transition-all focus:border-indigo-600 focus:ring-4 focus:ring-indigo-500/10 hover:border-gray-200">
            </div>

            {{-- Assignment List --}}
            <div class="space-y-4">
                @forelse($homework as $hw)
                    @php
                        $submitted = $hw->submissions->isNotEmpty();
                        $overdue   = !$submitted && $hw->due_date < now();
                        $isSelected = $selectedId == $hw->id;
                    @endphp

                    <div wire:click="selectHomework({{ $hw->id }})"
                        class="group relative flex flex-col bg-white rounded-[2rem] border-2 transition-all duration-300 cursor-pointer p-6
                            {{ $isSelected 
                                ? 'border-indigo-600 shadow-xl shadow-indigo-100 ring-4 ring-indigo-50' 
                                : 'border-transparent shadow-sm hover:shadow-md hover:border-gray-200' }}">
                        
                        <div class="flex justify-between items-start mb-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider
                                {{ $submitted ? 'bg-emerald-50 text-emerald-700' : 
                                   ($overdue ? 'bg-rose-50 text-rose-700' : 
                                   'bg-amber-50 text-amber-700') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $submitted ? 'bg-emerald-500' : ($overdue ? 'bg-rose-500' : 'bg-amber-500') }} animate-pulse"></span>
                                {{ $submitted ? 'Submitted' : ($overdue ? 'Overdue' : 'Pending') }}
                            </span>
                            
                            <span class="text-xs font-bold text-gray-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $hw->due_date->diffForHumans() }}
                            </span>
                        </div>

                        <h3 class="text-lg font-black text-gray-900 mb-1 group-hover:text-indigo-600 transition-colors leading-tight">{{ $hw->title }}</h3>
                        <p class="text-sm text-gray-500 font-bold mb-4">{{ $hw->subject->name }}</p>

                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-[10px] font-black text-white shadow-sm">
                                    {{ substr($hw->teacher->name, 0, 1) }}
                                </div>
                                <span class="text-xs font-bold text-gray-600">{{ $hw->teacher->name }}</span>
                            </div>
                            
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all group-hover:bg-indigo-600 group-hover:text-white">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-200">
                        <p class="text-sm font-bold text-gray-400">No assignments found</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Right Pane: Detail View --}}
        <div class="flex-1 w-full" :class="selectedId ? 'block' : (isMobile ? 'hidden' : 'block')">
            @if($selectedHomework)
                <div class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden sticky top-8">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 p-8 text-white">
                        <div class="flex items-start justify-between mb-6">
                            <div class="space-y-2">
                                <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1.5 text-xs font-black uppercase tracking-widest backdrop-blur-md">
                                    {{ $selectedHomework->subject->name }}
                                </div>
                                <h2 class="text-3xl font-black leading-tight">{{ $selectedHomework->title }}</h2>
                            </div>
                            
                            <button wire:click="closePanel()" class="lg:hidden p-2 rounded-2xl bg-white/10 hover:bg-white/20 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-6 text-sm font-bold text-indigo-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase opacity-60">Due Date</p>
                                    <p>{{ $selectedHomework->due_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md border border-white/20">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] uppercase opacity-60">Teacher</p>
                                    <p>{{ $selectedHomework->teacher->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-8 space-y-8">
                        {{-- Instructions --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2.5">
                                <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                                Assignment Instructions
                            </h3>
                            <div class="prose prose-indigo max-w-none bg-gray-50 rounded-3xl p-6 border border-gray-100 text-gray-700 leading-relaxed">
                                <p class="whitespace-pre-wrap">{{ $selectedHomework->content }}</p>
                            </div>
                        </div>

                        {{-- Submission Section --}}
                        @if($selectedHomework->submissions->isNotEmpty())
                            @php $sub = $selectedHomework->submissions->first(); @endphp
                            
                            <div class="space-y-6">
                                <div class="flex items-center gap-4 rounded-3xl bg-emerald-50 border-2 border-emerald-100 p-6">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center shadow-inner">
                                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black text-emerald-900">Successfully Submitted</h4>
                                        <p class="text-sm font-bold text-emerald-600 opacity-80">{{ $sub->submitted_at->format('M d, Y \a\t h:i A') }}</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <h3 class="text-lg font-black text-gray-900">Your Response</h3>
                                    <div class="rounded-3xl border-2 border-gray-100 bg-white p-6 shadow-sm">
                                        <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $sub->submission }}</p>
                                    </div>
                                </div>

                                @if($sub->attachment)
                                    <a href="{{ asset('storage/' . $sub->attachment) }}" target="_blank"
                                       class="group flex items-center justify-between rounded-3xl border-2 border-gray-100 bg-white p-6 transition-all hover:border-indigo-600 hover:shadow-xl hover:shadow-indigo-50">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-gray-900">Supporting Attachment</p>
                                                <p class="text-xs font-bold text-gray-400">Click to open submitted file</p>
                                            </div>
                                        </div>
                                        <svg class="w-6 h-6 text-gray-300 group-hover:text-indigo-600 transform transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7-7 7" /></svg>
                                    </a>
                                @endif

                                {{-- Feedback Card --}}
                                @if($sub->grade !== null || $sub->feedback)
                                    <div class="relative overflow-hidden rounded-[2.5rem] bg-indigo-900 p-6 sm:p-10 text-white shadow-2xl shadow-indigo-900/40">
                                        <div class="absolute top-0 right-0 p-8 opacity-10 text-white pointer-events-none">
                                            <svg class="w-32 h-32 sm:w-48 sm:h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </div>
                                        
                                        <h3 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] text-indigo-300 mb-6 sm:mb-8 relative z-10">Academic Feedback</h3>
                                        
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-6 sm:gap-10 relative z-10">
                                            @if($sub->grade !== null)
                                                <div class="flex flex-col items-center justify-center w-24 h-24 sm:w-32 sm:h-32 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 shadow-xl flex-shrink-0">
                                                    <span class="text-3xl sm:text-5xl font-black text-amber-300 leading-none">{{ (int) $sub->grade }}</span>
                                                    <span class="text-[10px] sm:text-xs font-black text-indigo-200 mt-2">SCORE</span>
                                                </div>
                                            @endif
                                            
                                            @if($sub->feedback)
                                                <div class="flex-1 space-y-3">
                                                    <p class="text-indigo-50 font-bold italic text-base sm:text-xl leading-relaxed">"{{ $sub->feedback }}"</p>
                                                    @if($sub->graded_at)
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-1 h-4 bg-indigo-500 rounded-full"></div>
                                                            <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Graded on {{ $sub->graded_at->format('M d, Y') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @else
                            {{-- Submission Form --}}
                            <form wire:submit.prevent="submitHomework" class="space-y-6">
                                <div class="space-y-4">
                                    <label class="text-lg font-black text-gray-900 flex items-center gap-2">
                                        <span class="w-1.5 h-6 bg-purple-600 rounded-full"></span>
                                        Your Answer
                                    </label>
                                    <textarea wire:model="submission" rows="8"
                                        placeholder="Type your complete response here..."
                                        class="w-full rounded-[2rem] border-2 border-gray-100 bg-gray-50 p-6 text-gray-800 placeholder-gray-400 shadow-inner focus:border-indigo-600 focus:bg-white focus:ring-0 transition-all"></textarea>
                                    @error('submission')
                                        <p class="text-sm font-black text-rose-600 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></p>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="space-y-4">
                                    <label class="text-lg font-black text-gray-900">Upload Attachment <span class="text-gray-400 text-sm">(Optional)</span></label>
                                    <input type="file" wire:model="attachment" id="hw-attachment" class="hidden">
                                    
                                    @if(!$attachment)
                                        <label for="hw-attachment"
                                            class="flex cursor-pointer flex-col items-center justify-center rounded-[2rem] border-4 border-dashed border-gray-100 bg-gray-50 p-10 text-center transition-all hover:border-indigo-600 hover:bg-indigo-50 group">
                                            <div class="w-16 h-16 rounded-2xl bg-white shadow-md flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                                <svg wire:loading.remove wire:target="attachment" class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                <svg wire:loading wire:target="attachment" class="h-8 w-8 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            </div>
                                            <span class="text-lg font-black text-gray-900" wire:loading.remove wire:target="attachment">Drop your file here</span>
                                            <span class="text-sm font-bold text-gray-400 mt-1" wire:loading.remove wire:target="attachment">PDF, DOC, Images (Max 10MB)</span>
                                            <span class="text-lg font-black text-indigo-600" wire:loading wire:target="attachment">Uploading your work...</span>
                                        </label>
                                    @else
                                        <div class="flex items-center gap-4 rounded-3xl border-2 border-emerald-500 bg-emerald-50 p-6 shadow-xl shadow-emerald-100">
                                            <div class="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <div class="flex-1 overflow-hidden">
                                                <p class="text-sm font-black text-emerald-900 truncate">{{ $attachment->getClientOriginalName() }}</p>
                                                <p class="text-xs font-bold text-emerald-600 opacity-70">Ready to submit</p>
                                            </div>
                                            <button type="button" wire:click="$set('attachment', null)" class="text-emerald-500 hover:text-rose-600 transition-colors p-2 hover:bg-rose-50 rounded-xl">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-6 flex gap-4">
                                    <button type="submit"
                                        class="flex-1 rounded-[2rem] bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 py-5 text-lg font-black text-white shadow-2xl shadow-indigo-200 hover:shadow-indigo-400 hover:scale-[1.02] active:scale-[0.98] transition-all relative overflow-hidden group">
                                        <div class="absolute inset-0 w-full h-full bg-white/20 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out"></div>
                                        <span wire:loading.remove wire:target="submitHomework" class="relative z-10 flex items-center justify-center gap-3">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                                            SUBMIT ASSIGNMENT
                                        </span>
                                        <span wire:loading wire:target="submitHomework" class="relative z-10 flex items-center justify-center gap-3">
                                            <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            SUBMITTING...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                {{-- Empty Detail State --}}
                <div class="h-full min-h-[500px] flex flex-col items-center justify-center bg-gray-50/50 rounded-[3rem] border-4 border-dashed border-gray-100 p-12 text-center">
                    <div class="w-32 h-32 bg-white rounded-[2.5rem] shadow-xl border border-gray-50 flex items-center justify-center mb-8 rotate-3">
                        <svg class="w-16 h-16 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-2">No Assignment Selected</h3>
                    <p class="text-gray-500 font-bold max-w-xs">Select an assignment from the list on the left to view details and submit your work.</p>
                </div>
            @endif
        </div>
    </div>
</div>
