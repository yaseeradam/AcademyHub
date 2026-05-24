<div class="space-y-6 font-sans" x-data="{ 
    selected: @entangle('selectedSubjects'),
    selectedList: [],
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
    }
}">
    
    {{-- Header --}}
    <x-page-header title="{{ $class->name }} - Subjects" subtitle="Configure and allocate default curriculum subjects for students in this class." accent="violet">
        <x-slot:actions>
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Curriculum Checklist</h3>
                <p class="text-xs text-slate-400 mt-0.5">Check the subjects that apply to all students enrolled in {{ $class->name }}.</p>
            </div>
            <div class="inline-flex items-center rounded-xl bg-violet-50 border border-violet-100 px-3.5 py-1.5 text-xs font-black text-violet-700 shadow-sm shadow-violet-50/50">
                <span class="font-extrabold mr-1" x-text="selectedList.length"></span> subjects active
            </div>
        </div>
 
        {{-- Checklist Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($allSubjects as $subject)
                <label wire:key="subject-{{ $subject->id }}"
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
            <button 
                wire:click="save" 
                class="rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-violet-100 transition-all hover:from-violet-700 hover:to-purple-700 hover:shadow-lg active:scale-[0.98] w-full sm:w-auto text-center"
            >
                Save Subject Allocation
            </button>
        </div>
    </div>

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
