<div class="space-y-6 font-sans" x-data="{ 
    selected: @entangle('selectedSubjects'),
    selectedList: [],
    search: '',
    init() {
        this.selectedList = (this.selected ? Array.from(this.selected) : []).map(String);
        this.$watch('selected', value => {
            this.selectedList = (value ? Array.from(value) : []).map(String);
        });
    },
    toggleSubject(id) {
        const idStr = String(id);
        if (this.selectedList.includes(idStr)) {
            this.selectedList = this.selectedList.filter(i => String(i) !== idStr);
        } else {
            this.selectedList = [...this.selectedList, idStr];
        }
        this.selected = this.selectedList;
    },
    selectAll(allIds) {
        this.selectedList = allIds.map(String);
        this.selected = this.selectedList;
    },
    deselectAll() {
        this.selectedList = [];
        this.selected = [];
    },
    matchesSearch(name, code) {
        if (!this.search || this.search.trim() === '') return true;
        const q = this.search.toLowerCase();
        return name.toLowerCase().includes(q) || code.toLowerCase().includes(q);
    }
}">
    
    {{-- Header --}}
    <x-page-header title="{{ $class->name }} - Subjects" subtitle="Configure and allocate default curriculum subjects for students in this class." accent="violet">
        <x-slot:actions>
            @if(count($otherClasses) > 0)
                <button type="button" wire:click="openCopyModal" class="inline-flex items-center gap-2 rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-700 font-bold px-4 py-2.5 text-xs sm:text-sm border border-violet-200 transition-all shadow-sm">
                    <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Copy Curriculum to Other Classes
                </button>
            @endif
            <a href="{{ route('classes.index') }}" class="btn-outline transition-all hover:bg-slate-100 hover:shadow-sm">
                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Classes
            </a>
        </x-slot:actions>
    </x-page-header>
 
    {{-- Main Container Card --}}
    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        {{-- Toolbar: Search, Select All, Counter --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Curriculum Checklist</h3>
                <p class="text-xs text-slate-400 mt-0.5">Check the subjects that apply to all students enrolled in {{ $class->name }}.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Live Search Filter --}}
                <div class="relative min-w-[200px] flex-1 sm:flex-none">
                    <input 
                        type="text" 
                        x-model="search" 
                        placeholder="Search subjects..." 
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-8 pr-3 py-2 text-xs font-semibold text-slate-700 placeholder-slate-400 focus:border-violet-500 focus:bg-white focus:ring-2 focus:ring-violet-500/20"
                    />
                    <svg class="pointer-events-none absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <path d="M21 21l-4.35-4.35" stroke-width="2"/>
                    </svg>
                </div>

                {{-- Bulk Selection Toggles --}}
                <button 
                    type="button" 
                    @click="selectAll([{{ $allSubjects->pluck('id')->map(fn($id) => "'$id'")->join(',') }}])"
                    class="rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 hover:bg-violet-100 transition-colors"
                >
                    Select All
                </button>
                <button 
                    type="button" 
                    @click="deselectAll()"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                >
                    Deselect All
                </button>

                <div class="inline-flex items-center rounded-xl bg-violet-50 border border-violet-100 px-3.5 py-1.5 text-xs font-black text-violet-700 shadow-sm shadow-violet-50/50">
                    <span class="font-extrabold mr-1" x-text="selectedList.length"></span> / {{ $allSubjects->count() }} active
                </div>
            </div>
        </div>
 
        {{-- Checklist Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($allSubjects as $subject)
                <label wire:key="subject-{{ $subject->id }}"
                       x-show="matchesSearch('{{ addslashes($subject->name) }}', '{{ addslashes($subject->code) }}')"
                       :class="selectedList.includes('{{ $subject->id }}') ? 'border-violet-600 bg-violet-50/20 shadow-md shadow-violet-100/50 text-violet-900 ring-4 ring-violet-600/5' : 'border-slate-200 bg-slate-50/50 text-slate-800 hover:border-slate-300 hover:bg-white hover:shadow-sm'"
                       class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition-all duration-200 select-none">
                    <input 
                        type="checkbox" 
                        :checked="selectedList.includes('{{ $subject->id }}')"
                        @change="toggleSubject('{{ $subject->id }}')"
                        class="mt-0.5 h-4.5 w-4.5 rounded border-slate-300 text-violet-600 focus:ring-violet-500/20 transition-all cursor-pointer"
                    >
                    <div class="flex-1 leading-snug">
                        <div class="text-sm font-black tracking-tight transition-colors">{{ $subject->name }}</div>
                        <div class="mt-1 text-[10px] font-black uppercase tracking-wider text-slate-500/90">{{ $subject->code }}</div>
                    </div>
                </label>
            @endforeach
        </div>
 
        {{-- Action Bar --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-3xl bg-gradient-to-r from-violet-500/5 to-purple-500/5 border border-violet-100 p-5 shadow-inner">
            <div class="text-sm font-semibold text-violet-800 text-center sm:text-left">
                <span class="font-black text-violet-900" x-text="selectedList.length"></span> subject(s) selected for student enrollment inheritance.
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <button 
                    wire:click="save" 
                    class="rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-violet-100 transition-all hover:from-violet-700 hover:to-purple-700 hover:shadow-lg active:scale-[0.98] w-full sm:w-auto text-center"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    Save Subject Allocation
                </button>
            </div>
        </div>
    </div>

    {{-- Copy Curriculum to Other Classes Modal --}}
    @if($showCopyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in" x-data="{
            allOtherIds: [{{ $otherClasses->pluck('id')->map(fn($id) => "'$id'")->join(',') }}],
            allSelected: false,
            toggleAll() {
                if (this.allSelected) {
                    @this.set('targetClassIds', []);
                    this.allSelected = false;
                } else {
                    @this.set('targetClassIds', this.allOtherIds.map(Number));
                    this.allSelected = true;
                }
            }
        }">
            <div class="relative w-full max-w-lg rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden" @click.outside="$wire.closeCopyModal()">
                {{-- Modal Header --}}
                <div class="bg-gradient-to-br from-violet-600 to-purple-700 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black">Copy Curriculum</h3>
                                <p class="text-xs text-violet-200">Replicate {{ $class->name }} subjects to other classes</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeCopyModal" class="rounded-lg p-1.5 text-white/80 hover:bg-white/20 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Select Destination Classes</span>
                        <button type="button" @click="toggleAll()" class="text-xs font-bold text-violet-600 hover:text-violet-800">
                            <span x-text="allSelected ? 'Deselect All' : 'Select All'"></span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach($otherClasses as $target)
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                                <input type="checkbox" wire:model="targetClassIds" value="{{ $target->id }}" class="h-4.5 w-4.5 rounded border-slate-300 text-violet-600 focus:ring-violet-500/20">
                                <div class="flex-1">
                                    <div class="text-sm font-bold text-slate-900">{{ $target->name }}</div>
                                    <div class="text-xs text-slate-400">Level {{ $target->level }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    @error('targetClassIds')
                        <p class="text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 p-5 bg-slate-50 border-t border-slate-100">
                    <button type="button" wire:click="closeCopyModal" class="rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-200/60 transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="copyToClasses" class="rounded-xl bg-violet-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-violet-200 hover:bg-violet-700 transition-all active:scale-95" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        Copy Subjects ({{ count($selectedSubjects) }} Subjects)
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- System Alert / Guide --}}
    <div class="rounded-3xl bg-gradient-to-br from-sky-50 to-blue-50/30 border border-sky-100 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-sky-850 flex items-center gap-2">
            <svg class="h-5 w-5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.063.854l-.512.773a1.125 1.125 0 00-.194.462l-.039.291m0 0h-.011m0 0h.011M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Automated Enrollment & Curriculum Policies
        </h3>
        <ul class="mt-4 space-y-3 text-xs text-sky-800/90 font-medium">
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400"></span>
                <span><strong>Instant Registration Enrollment</strong>: All active students in <strong>{{ $class->name }}</strong> will be automatically linked to these allocated subjects instantly.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400"></span>
                <span><strong>Admission Inheritance</strong>: Any new student admitted or promoted to this class level in the future will automatically inherit this curriculum checklist.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400"></span>
                <span><strong>Individual Overrides</strong>: Admins can still manually assign or isolate unique subject combinations for specific students via their student bio-data profile.</span>
            </li>
        </ul>
    </div>
</div>
