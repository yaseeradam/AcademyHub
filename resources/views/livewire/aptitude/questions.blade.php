<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('aptitude.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition duration-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manage Questions</h1>
                <p class="mt-1 text-sm text-slate-500">Add and review multiple choice admission screening tests for each school class.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Panel: Create MCQ Question -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm h-fit">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Add Question</h2>
                <form wire:submit.prevent="addQuestion" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Class Exam</label>
                        <select wire:model.live="selectedClass" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedClass') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Question Text</label>
                        <textarea wire:model="question_text" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200" placeholder="e.g. What is the value of 5 + 7?"></textarea>
                        @error('question_text') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Option A</label>
                            <input type="text" wire:model="option_a" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            @error('option_a') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Option B</label>
                            <input type="text" wire:model="option_b" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            @error('option_b') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Option C</label>
                            <input type="text" wire:model="option_c" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            @error('option_c') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Option D</label>
                            <input type="text" wire:model="option_d" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            @error('option_d') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Correct Answer</label>
                            <select wire:model="correct_option" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                            </select>
                            @error('correct_option') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Points</label>
                            <input type="number" wire:model="points" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:bg-white focus:border-violet-500 focus:ring-1 focus:ring-violet-500 outline-none transition duration-200">
                            @error('points') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 transition duration-200 shadow-md hover:shadow-lg">
                        Add Question
                    </button>
                </form>
            </div>

            <!-- Right Panel: Questions List -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-slate-900">Current Exam Questions ({{ count($questions) }})</h2>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-wider">
                        {{ $classes->firstWhere('id', $selectedClass)?->name }}
                    </span>
                </div>

                @if($questions->isEmpty())
                    <div class="text-center py-16">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                        </svg>
                        <h3 class="mt-4 text-sm font-semibold text-slate-900">No questions yet</h3>
                        <p class="mt-1 text-sm text-slate-500">Create multiple choice screening questions for this class on the left.</p>
                    </div>
                @else
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                        @foreach($questions as $index => $q)
                            <div class="border border-slate-200/60 rounded-2xl p-5 hover:border-slate-300 transition duration-150 relative">
                                
                                <!-- Delete Button -->
                                <button wire:click="deleteQuestion({{ $q->id }})" class="absolute top-4 right-4 text-slate-300 hover:text-rose-500 transition duration-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>

                                <div class="flex items-start gap-3.5 pr-8">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-violet-50 text-xs font-bold text-violet-600 flex-shrink-0 mt-0.5">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-slate-900 text-sm leading-relaxed mb-3">{{ $q->question_text }}</h3>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-slate-600 font-semibold mb-4">
                                            <div class="flex items-center gap-2 {{ $q->correct_option === 'A' ? 'text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-lg' : '' }}">
                                                <span class="text-slate-400 font-bold">A:</span> {{ $q->option_a }}
                                            </div>
                                            <div class="flex items-center gap-2 {{ $q->correct_option === 'B' ? 'text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-lg' : '' }}">
                                                <span class="text-slate-400 font-bold">B:</span> {{ $q->option_b }}
                                            </div>
                                            <div class="flex items-center gap-2 {{ $q->correct_option === 'C' ? 'text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-lg' : '' }}">
                                                <span class="text-slate-400 font-bold">C:</span> {{ $q->option_c }}
                                            </div>
                                            <div class="flex items-center gap-2 {{ $q->correct_option === 'D' ? 'text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-lg' : '' }}">
                                                <span class="text-slate-400 font-bold">D:</span> {{ $q->option_d }}
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">
                                                Answer: Option {{ $q->correct_option }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-md">
                                                Points: {{ $q->points }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>
