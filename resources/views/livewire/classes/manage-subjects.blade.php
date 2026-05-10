<div class="space-y-6">
    <x-page-header title="{{ $class->name }} - Subjects" subtitle="Assign default subjects for all students in this class." accent="violet">
        <x-slot:actions>
            <a href="{{ route('classes.index') }}" class="btn-outline">Back to Classes</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card-padded">
        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Select Subjects</h3>
                <p class="text-xs text-gray-500">All students in {{ $class->name }} will automatically get these subjects.</p>
            </div>
            <div class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                <span class="font-black" x-text="$wire.selectedSubjects.length"></span> selected
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($allSubjects as $subject)
                <label wire:key="subject-{{ $subject->id }}"
                       :class="$wire.selectedSubjects.includes('{{ $subject->id }}') ? 'ring-2 ring-violet-500 border-transparent bg-white shadow-sm' : 'border-gray-200 bg-gray-50/50 hover:border-violet-300 hover:bg-white'"
                       class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-all">
                    <input type="checkbox" wire:model="selectedSubjects" value="{{ $subject->id }}" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500 transition-colors">
                    <div class="flex-1 leading-tight">
                        <div class="text-sm font-bold text-gray-900">{{ $subject->name }}</div>
                        <div class="mt-0.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $subject->code }}</div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="mt-8 flex items-center justify-between rounded-xl bg-violet-50/80 p-4 ring-1 ring-violet-100">
            <div class="text-sm font-medium text-violet-800">
                <span class="font-black text-violet-900" x-text="$wire.selectedSubjects.length"></span> subject(s) selected for this class
            </div>
            <button wire:click="save" class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-violet-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                Save Changes
            </button>
        </div>
    </div>

    <div class="rounded-2xl bg-sky-50/50 border border-sky-100 p-5 mt-6">
        <h3 class="text-sm font-bold text-sky-800 flex items-center gap-2">
            <svg class="h-4 w-4 text-sky-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            How it Works
        </h3>
        <ul class="mt-3 space-y-2 text-xs text-sky-700/80">
            <li class="flex items-start gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400"></span>
                <span>All current students in <strong>{{ $class->name }}</strong> will be automatically enrolled in these subjects.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400"></span>
                <span>Any new student added to this class will immediately inherit these subjects.</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-sky-400"></span>
                <span>You can still manually add or remove unique subjects for individual students via their personal profile page.</span>
            </li>
        </ul>
    </div>
</div>
