@php
    $user = auth()->user();
    $submissionStatus = $this->submission?->status;
    $locked = $user?->role === 'teacher' && (in_array($submissionStatus, ['submitted', 'approved'], true) || $this->isPublished);
@endphp

<div class="space-y-8">
    {{-- Header Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-700 p-6 sm:p-8 shadow-2xl">
        <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-white/5"></div>
        <div class="absolute right-6 bottom-6 h-16 w-16 rounded-full bg-white/10"></div>
        
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 sm:h-16 sm:w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-xl sm:text-3xl font-black text-white">Score Entry</h1>
                    <p class="mt-1 text-xs sm:text-base text-emerald-100 truncate">Enter CA and Exam scores for students</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                @if($user?->role === 'admin')
                    <a href="{{ route('results.submissions') }}" 
                       class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-white/20 px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-bold text-white backdrop-blur-sm transition-all hover:bg-white/30 whitespace-nowrap">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10,9 9,9 8,9"/>
                        </svg>
                        <span class="sm:inline">Submissions</span>
                    </a>
                @endif
                <a href="{{ route('results.broadsheet') }}" 
                   class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-white px-3 py-2.5 sm:px-4 sm:py-3 text-xs sm:text-sm font-bold text-emerald-600 shadow-lg transition-all hover:bg-emerald-50 hover:shadow-xl whitespace-nowrap">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3h18v18H3zM21 9H3M9 21V9"/>
                    </svg>
                    <span class="sm:inline">Broadsheet</span>
                </a>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-white p-5 sm:p-8 shadow-xl ring-1 ring-gray-200">
        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-6">
            <div class="md:col-span-1 lg:col-span-2">
                <label class="block text-sm font-black text-gray-900 mb-1.5 sm:mb-2">Class</label>
                <select wire:key="class-select-dropdown" wire:model.live="classId"
                    class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2.5 sm:px-4 sm:py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400">
                    <option value="">Select class</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-1 lg:col-span-2">
                <label class="block text-sm font-black text-gray-900 mb-1.5 sm:mb-2">Subject</label>
                <select wire:key="subject-select-dropdown" wire:model.live="subjectId"
                    @disabled(!$classId) 
                    class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2.5 sm:px-4 sm:py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Select subject</option>
                    @foreach ($this->subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 md:col-span-2 lg:contents">
                <div>
                    <label class="block text-sm font-black text-gray-900 mb-1.5 sm:mb-2">Term</label>
                    <select wire:model.live="term" 
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2.5 sm:px-4 sm:py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-900 mb-1.5 sm:mb-2">Session</label>
                    <input wire:model.live.debounce.300ms="session" type="text" placeholder="2025/2026"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-3 py-2.5 sm:px-4 sm:py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/20 hover:border-gray-400" />
                </div>
            </div>
        </div>

        <div class="mt-6 sm:mt-8 flex flex-col lg:flex-row lg:items-center lg:justify-between border-t border-gray-100 pt-6 gap-6">
            <div class="flex items-center gap-1 rounded-2xl bg-gray-100 p-1.5 overflow-x-auto no-scrollbar w-full lg:w-auto">
                <button type="button" wire:click="setTab('scores')"
                    class="flex-1 lg:flex-none whitespace-nowrap rounded-xl px-4 sm:px-8 py-3 text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'scores' ? 'bg-white text-emerald-600 shadow-md' : 'text-gray-500 hover:text-gray-700' }}">
                    Academic Scores
                </button>
                <button type="button" wire:click="setTab('psychomotor')"
                    class="flex-1 lg:flex-none whitespace-nowrap rounded-xl px-4 sm:px-8 py-3 text-xs sm:text-sm font-bold transition-all {{ $activeTab === 'psychomotor' ? 'bg-white text-indigo-600 shadow-md' : 'text-gray-500 hover:text-gray-700' }}">
                    Psychomotor Traits
                </button>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                @if($activeTab === 'scores')
                    <button type="button" wire:click="save" @disabled(!$classId || !$subjectId || $locked) 
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3.5 text-sm font-bold text-white shadow-lg transition-all hover:from-emerald-600 hover:to-teal-600 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Save Scores
                    </button>

                    @if ($user?->role === 'teacher')
                        <button type="button" wire:click="submitScores" @disabled(!$classId || !$subjectId || $locked)
                            class="flex flex-1 items-center justify-center gap-2 rounded-xl border-2 border-emerald-300 bg-white px-6 py-3.5 text-sm font-bold text-emerald-700 shadow-sm transition-all hover:bg-emerald-50 hover:border-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13L4 6"/>
                            </svg>
                            Submit to Admin
                        </button>
                    @endif
                @else
                    <button type="button" wire:click="saveBulkPsychomotor" @disabled(!$classId || $locked) 
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        Save Assessments
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if (!$classId)
        <div class="rounded-3xl bg-white p-12 text-center shadow-xl ring-1 ring-gray-200">
            <div class="flex flex-col items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100">
                    <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">No class selected</h3>
                    <p class="mt-1 text-sm text-gray-500">Please select a class and session to continue</p>
                </div>
            </div>
        </div>
    @elseif ($activeTab === 'scores' && !$subjectId)
        <div class="rounded-3xl bg-white p-12 text-center shadow-xl ring-1 ring-gray-200">
            <div class="flex flex-col items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100">
                    <svg class="h-8 w-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Select a subject</h3>
                    <p class="mt-1 text-sm text-gray-500">Only allocated subjects are shown for teachers</p>
                </div>
            </div>
        </div>
    @else
        @if ($activeTab === 'scores')
            @php
                $maxMarks = $this->maxMarks();
            @endphp
            
            {{-- Scoresheet Table Card --}}
            <div class="overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-gray-200">
                {{-- Table Header --}}
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 sm:px-8 py-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg sm:text-xl font-black text-gray-900 leading-tight">Score Entry Sheet</h2>
                            <p class="text-xs sm:text-sm text-gray-600 mt-0.5">Enter scores for each assessment component</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-extrabold">
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-blue-700 border border-blue-200/60 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                <span>CA1 /{{ $maxMarks['ca1'] }}</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700 border border-emerald-200/60 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span>CA2 /{{ $maxMarks['ca2'] }}</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-amber-700 border border-amber-200/60 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span>Exam /{{ $maxMarks['exam'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Table Content --}}
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200 bg-gray-50">
                                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-gray-700">Student</th>
                                    <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-blue-700">CA1 /{{ $maxMarks['ca1'] }}</th>
                                    <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-green-700">CA2 /{{ $maxMarks['ca2'] }}</th>
                                    <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-amber-700">Exam /{{ $maxMarks['exam'] }}</th>
                                    <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-purple-700">Total</th>
                                    <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-gray-700">Grade</th>
                                    <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider text-gray-700">Psychomotor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($this->students as $student)
                                    @php
                                        $row = $scores[$student->id] ?? ['ca1' => 0, 'ca2' => 0, 'exam' => 0];
                                        $total = (int) ($row['ca1'] ?? 0) + (int) ($row['ca2'] ?? 0) + (int) ($row['exam'] ?? 0);
                                        $grade = \App\Models\Score::gradeForTotal($total, $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam']);
                                        
                                        // Check for validation errors
                                        $ca1Error = isset($validationErrors["{$student->id}.ca1"]);
                                        $ca2Error = isset($validationErrors["{$student->id}.ca2"]);
                                        $examError = isset($validationErrors["{$student->id}.exam"]);
                                        $hasError = $ca1Error || $ca2Error || $examError;

                                        $gradeConfig = match($grade) {
                                            'A' => ['bg' => 'from-emerald-400 to-green-500', 'text' => 'text-white'],
                                            'B' => ['bg' => 'from-blue-400 to-indigo-500', 'text' => 'text-white'],
                                            'C' => ['bg' => 'from-amber-400 to-orange-500', 'text' => 'text-white'],
                                            'D' => ['bg' => 'from-red-400 to-pink-500', 'text' => 'text-white'],
                                            'F' => ['bg' => 'from-gray-400 to-slate-500', 'text' => 'text-white'],
                                            default => ['bg' => 'from-gray-200 to-gray-300', 'text' => 'text-gray-700'],
                                        };
                                    @endphp
                                    <tr class="group transition-all duration-200 hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/50 {{ $hasError ? 'bg-red-50' : 'bg-white' }}" 
                                        data-student-id="{{ $student->id }}">
                                        {{-- Student Info --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-gray-400 to-gray-500 text-white shadow-sm">
                                                    <span class="text-sm font-black">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="truncate font-bold text-gray-900">{{ $student->full_name }}</div>
                                                    <div class="truncate text-xs text-gray-500">{{ $student->admission_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        {{-- CA1 Score --}}
                                        <td class="px-6 py-4 text-center">
                                            <input wire:model.blur="scores.{{ $student->id }}.ca1" 
                                                type="number" min="0" max="{{ $maxMarks['ca1'] }}" step="1"
                                                class="w-20 rounded-xl border-2 {{ $ca1Error ? 'border-red-400 bg-red-50' : 'border-blue-300 bg-blue-50' }} px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/20 hover:border-blue-400" />
                                        </td>
                                        
                                        {{-- CA2 Score --}}
                                        <td class="px-6 py-4 text-center">
                                            <input wire:model.blur="scores.{{ $student->id }}.ca2" 
                                                type="number" min="0" max="{{ $maxMarks['ca2'] }}" step="1"
                                                class="w-20 rounded-xl border-2 {{ $ca2Error ? 'border-red-400 bg-red-50' : 'border-green-300 bg-green-50' }} px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm transition-all focus:border-green-500 focus:bg-white focus:ring-4 focus:ring-green-500/20 hover:border-green-400" />
                                        </td>
                                        
                                        {{-- Exam Score --}}
                                        <td class="px-6 py-4 text-center">
                                            <input wire:model.blur="scores.{{ $student->id }}.exam" 
                                                type="number" min="0" max="{{ $maxMarks['exam'] }}" step="1"
                                                class="w-20 rounded-xl border-2 {{ $examError ? 'border-red-400 bg-red-50' : 'border-amber-300 bg-amber-50' }} px-3 py-2 text-center text-sm font-bold text-gray-900 shadow-sm transition-all focus:border-amber-500 focus:bg-white focus:ring-4 focus:ring-amber-500/20 hover:border-amber-400" />
                                        </td>
                                        
                                        {{-- Total Score --}}
                                        <td class="px-6 py-4 text-center">
                                            <div class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-purple-100 to-indigo-100 px-4 py-2 text-lg font-black text-purple-900 shadow-sm">
                                                {{ $total }}
                                            </div>
                                        </td>
                                        
                                        {{-- Grade --}}
                                        <td class="px-6 py-4 text-center">
                                            <div class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r {{ $gradeConfig['bg'] }} px-3 py-2 text-sm font-black {{ $gradeConfig['text'] }} shadow-lg">
                                                {{ $grade }}
                                            </div>
                                        </td>

                                        {{-- Psychomotor --}}
                                        <td class="px-6 py-4 text-center">
                                            <button type="button" wire:click="openPsychomotor({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Assess
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">No students found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile Card Layout --}}
                <div class="block lg:hidden">
                    <div class="divide-y divide-gray-100">
                        @forelse ($this->students as $student)
                            @php
                                $row = $scores[$student->id] ?? ['ca1' => 0, 'ca2' => 0, 'exam' => 0];
                                $total = (int) ($row['ca1'] ?? 0) + (int) ($row['ca2'] ?? 0) + (int) ($row['exam'] ?? 0);
                                $grade = \App\Models\Score::gradeForTotal($total, $maxMarks['ca1'] + $maxMarks['ca2'] + $maxMarks['exam']);
                                
                                $ca1Error = isset($validationErrors["{$student->id}.ca1"]);
                                $ca2Error = isset($validationErrors["{$student->id}.ca2"]);
                                $examError = isset($validationErrors["{$student->id}.exam"]);
                                $hasError = $ca1Error || $ca2Error || $examError;
                                
                                $gradeConfig = match($grade) {
                                    'A' => ['bg' => 'from-emerald-400 to-green-500', 'text' => 'text-white'],
                                    'B' => ['bg' => 'from-blue-400 to-indigo-500', 'text' => 'text-white'],
                                    'C' => ['bg' => 'from-amber-400 to-orange-500', 'text' => 'text-white'],
                                    'D' => ['bg' => 'from-red-400 to-pink-500', 'text' => 'text-white'],
                                    'F' => ['bg' => 'from-gray-400 to-slate-500', 'text' => 'text-white'],
                                    default => ['bg' => 'from-gray-200 to-gray-300', 'text' => 'text-gray-700'],
                                };
                            @endphp
                            <div class="p-4 transition-all duration-200 {{ $hasError ? 'bg-red-50' : 'bg-white' }}" data-student-id="{{ $student->id }}">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-gray-400 to-gray-500 text-white shadow-sm">
                                            <span class="text-xs font-black">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-bold text-gray-900">{{ $student->full_name }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $student->admission_number }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-sm font-black text-purple-900">
                                            {{ $total }}
                                        </div>
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-r {{ $gradeConfig['bg'] }} text-xs font-black {{ $gradeConfig['text'] }}">
                                            {{ $grade }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-black text-blue-700 uppercase mb-1">CA1 ({{ $maxMarks['ca1'] }})</label>
                                        <input wire:model.blur="scores.{{ $student->id }}.ca1" type="number" min="0" max="{{ $maxMarks['ca1'] }}"
                                            data-student-id="{{ $student->id }}" data-field="ca1"
                                            class="w-full rounded-xl border-2 {{ $ca1Error ? 'border-red-400 bg-red-50 text-red-950 focus:border-red-500' : 'border-blue-200 bg-blue-50 text-gray-900 focus:border-blue-500' }} px-2 py-2 text-center text-sm font-bold focus:bg-white focus:ring-0">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-green-700 uppercase mb-1">CA2 ({{ $maxMarks['ca2'] }})</label>
                                        <input wire:model.blur="scores.{{ $student->id }}.ca2" type="number" min="0" max="{{ $maxMarks['ca2'] }}"
                                            data-student-id="{{ $student->id }}" data-field="ca2"
                                            class="w-full rounded-xl border-2 {{ $ca2Error ? 'border-red-400 bg-red-50 text-red-950 focus:border-red-500' : 'border-green-200 bg-green-50 text-gray-900 focus:border-green-500' }} px-2 py-2 text-center text-sm font-bold focus:bg-white focus:ring-0">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-amber-700 uppercase mb-1">Exam ({{ $maxMarks['exam'] }})</label>
                                        <input wire:model.blur="scores.{{ $student->id }}.exam" type="number" min="0" max="{{ $maxMarks['exam'] }}"
                                            data-student-id="{{ $student->id }}" data-field="exam"
                                            class="w-full rounded-xl border-2 {{ $examError ? 'border-red-400 bg-red-50 text-red-950 focus:border-red-500' : 'border-amber-200 bg-amber-50 text-gray-900 focus:border-amber-500' }} px-2 py-2 text-center text-sm font-bold focus:bg-white focus:ring-0">
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end">
                                    <button type="button" wire:click="openPsychomotor({{ $student->id }}, '{{ addslashes($student->full_name) }}')"
                                        class="flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Assess Traits
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-500 font-medium">No students found</div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Keyboard Shortcuts Card --}}
            <div class="hidden lg:block rounded-3xl bg-gradient-to-r from-blue-50 to-indigo-50 p-6 shadow-lg ring-1 ring-blue-200 mt-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                        <svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-blue-900">⌨️ Keyboard Shortcuts</h3>
                        <p class="text-sm text-blue-700">Navigate quickly through the scoresheet</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="flex items-center gap-2">
                        <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">Enter</kbd>
                        <span class="text-sm font-medium text-blue-700">Move down</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">Tab</kbd>
                        <span class="text-sm font-medium text-blue-700">Move right</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">↑↓←→</kbd>
                        <span class="text-sm font-medium text-blue-700">Arrow keys</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <kbd class="inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-300">Shift+Tab</kbd>
                        <span class="text-sm font-medium text-blue-700">Move left</span>
                    </div>
                </div>
            </div>
        @else
            {{-- Bulk Psychomotor Entry View --}}
            <div class="space-y-6" x-data="{ 
                applyToAll(trait, value) {
                    const inputs = document.querySelectorAll(`[data-trait='${trait}']`);
                    inputs.forEach(input => {
                        const studentId = input.getAttribute('data-student-id');
                        @this.set(`bulkPsychomotorScores.${studentId}.${trait}`, value);
                    });
                }
            }">
                <div class="rounded-[2.5rem] bg-white shadow-2xl ring-1 ring-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-900 to-purple-900 px-6 sm:px-10 py-8 text-white relative">
                        <div class="absolute right-0 top-0 bottom-0 w-64 bg-white/5 skew-x-[-20deg] translate-x-32"></div>
                        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">Bulk Psychomotor Assessment</h2>
                                <p class="text-indigo-200 font-medium mt-1.5 text-sm sm:text-base">Assess affective traits and psychomotor skills for all students at once.</p>
                            </div>
                            <button wire:click="setTab('scores')" class="w-full sm:w-auto rounded-2xl bg-white/10 px-8 py-3.5 text-sm font-bold backdrop-blur-md border border-white/20 hover:bg-white/20 transition-all shadow-lg text-center">
                                Back to Scores
                            </button>
                        </div>
                    </div>

                    {{-- Desktop Table View --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-100 bg-gray-50/50">
                                    <th class="sticky left-0 z-20 bg-gray-50 px-8 py-5 text-left text-xs font-black uppercase tracking-widest text-gray-500 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">Student</th>
                                    @foreach($this->traitMap() as $slug => $trait)
                                        <th class="px-4 py-6 text-center min-w-[190px]">
                                            <div class="space-y-3">
                                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-950">{{ $trait }}</span>
                                                <select @change="applyToAll('{{ $slug }}', $event.target.value); $event.target.value = '';" 
                                                    class="w-full rounded-xl border-2 border-indigo-100 bg-white px-3 py-2 text-[10px] font-extrabold text-indigo-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer shadow-sm">
                                                    <option value="">Apply to all...</option>
                                                    <option value="Excellent">Excellent</option>
                                                    <option value="Good">Good</option>
                                                    <option value="Average">Average</option>
                                                    <option value="Poor">Poor</option>
                                                </select>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($this->students as $student)
                                    <tr class="group hover:bg-indigo-50/30 transition-colors">
                                        <td class="sticky left-0 z-10 bg-white group-hover:bg-indigo-50/30 px-8 py-5 shadow-[2px_0_5px_rgba(0,0,0,0.02)] transition-colors">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center text-xs font-black text-indigo-600 shadow-inner">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                </div>
                                                <div class="whitespace-nowrap">
                                                    <div class="text-sm font-black text-gray-900">{{ $student->full_name }}</div>
                                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $student->admission_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($this->traitMap() as $slug => $trait)
                                            <td class="px-4 py-5 text-center">
                                                @php
                                                    $scoreValue = $bulkPsychomotorScores[$student->id][$slug] ?? '';
                                                @endphp
                                                <div class="inline-flex p-1 bg-gray-100 rounded-xl gap-0.5 shadow-inner border border-gray-200/40">
                                                    <span data-trait="{{ $slug }}" data-student-id="{{ $student->id }}" class="hidden"></span>
                                                    
                                                    <button type="button" 
                                                        data-trait="{{ $slug }}" data-student-id="{{ $student->id }}"
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Excellent')"
                                                        class="px-2 py-1.5 rounded-lg text-[9px] font-black tracking-wider transition-all duration-150 uppercase
                                                        {{ $scoreValue === 'Excellent' 
                                                            ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-sm shadow-emerald-500/20 scale-[1.02]' 
                                                            : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                                                        EXC
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        data-trait="{{ $slug }}" data-student-id="{{ $student->id }}"
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Good')"
                                                        class="px-2 py-1.5 rounded-lg text-[9px] font-black tracking-wider transition-all duration-150 uppercase
                                                        {{ $scoreValue === 'Good' 
                                                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-sm shadow-blue-500/20 scale-[1.02]' 
                                                            : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                                                        GD
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        data-trait="{{ $slug }}" data-student-id="{{ $student->id }}"
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Average')"
                                                        class="px-2 py-1.5 rounded-lg text-[9px] font-black tracking-wider transition-all duration-150 uppercase
                                                        {{ $scoreValue === 'Average' 
                                                            ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-sm shadow-amber-500/20 scale-[1.02]' 
                                                            : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                                                        AVG
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        data-trait="{{ $slug }}" data-student-id="{{ $student->id }}"
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Poor')"
                                                        class="px-2 py-1.5 rounded-lg text-[9px] font-black tracking-wider transition-all duration-150 uppercase
                                                        {{ $scoreValue === 'Poor' 
                                                            ? 'bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-sm shadow-red-500/20 scale-[1.02]' 
                                                            : 'text-gray-400 hover:text-gray-700 hover:bg-white' }}">
                                                        PR
                                                    </button>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Bulk Actions Panel --}}
                    <div class="block lg:hidden bg-gradient-to-br from-indigo-50/50 to-purple-50/50 p-5 border-b border-indigo-100/50">
                        <div x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex w-full items-center justify-between font-black text-indigo-900 text-xs sm:text-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Bulk Actions (Apply to All Students)
                                </span>
                                <svg class="h-5 w-5 text-indigo-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="mt-4 space-y-4" style="display: none;">
                                <p class="text-xs text-indigo-700 font-medium">Select a trait rating to apply to all students at once:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($this->traitMap() as $slug => $trait)
                                        <div class="space-y-1">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-800">{{ $trait }}</span>
                                            <select @change="applyToAll('{{ $slug }}', $event.target.value)"
                                                class="w-full rounded-xl border-2 border-indigo-100 bg-white px-3 py-2 text-xs font-bold text-indigo-700 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                                <option value="">Apply to all...</option>
                                                <option value="Excellent">Excellent</option>
                                                <option value="Good">Good</option>
                                                <option value="Average">Average</option>
                                                <option value="Poor">Poor</option>
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mobile Card View --}}
                    <div class="block lg:hidden">
                        <div class="divide-y divide-gray-100">
                            @foreach ($this->students as $student)
                                <div class="p-6 space-y-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-sm font-black text-indigo-600">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-base font-black text-gray-900">{{ $student->full_name }}</div>
                                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $student->admission_number }}</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($this->traitMap() as $slug => $trait)
                                            <div class="space-y-1.5">
                                                <label class="text-[10px] font-black uppercase tracking-widest text-indigo-950 opacity-70">{{ $trait }}</label>
                                                @php
                                                    $scoreValue = $bulkPsychomotorScores[$student->id][$slug] ?? '';
                                                @endphp
                                                <div class="flex p-1 bg-gray-100 rounded-xl gap-1 ring-1 ring-gray-200/50 shadow-inner w-full justify-between">
                                                    <span data-trait="{{ $slug }}" data-student-id="{{ $student->id }}" class="hidden"></span>
                                                    
                                                    <button type="button" 
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Excellent')"
                                                        class="flex-1 py-2 rounded-lg text-xs font-black tracking-wider transition-all duration-150 uppercase text-center
                                                        {{ $scoreValue === 'Excellent' 
                                                            ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25' 
                                                            : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                                        EXC
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Good')"
                                                        class="flex-1 py-2 rounded-lg text-xs font-black tracking-wider transition-all duration-150 uppercase text-center
                                                        {{ $scoreValue === 'Good' 
                                                            ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md shadow-blue-500/25' 
                                                            : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                                        GD
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Average')"
                                                        class="flex-1 py-2 rounded-lg text-xs font-black tracking-wider transition-all duration-150 uppercase text-center
                                                        {{ $scoreValue === 'Average' 
                                                            ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/25' 
                                                            : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                                        AVG
                                                    </button>
                                                    
                                                    <button type="button" 
                                                        wire:click="$set('bulkPsychomotorScores.{{ $student->id }}.{{ $slug }}', 'Poor')"
                                                        class="flex-1 py-2 rounded-lg text-xs font-black tracking-wider transition-all duration-150 uppercase text-center
                                                        {{ $scoreValue === 'Poor' 
                                                            ? 'bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-md shadow-red-500/25' 
                                                            : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                                        PR
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-center py-10 px-4">
                    <button wire:click="saveBulkPsychomotor" class="w-full sm:w-auto rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-12 py-4.5 text-sm font-black text-white shadow-xl shadow-emerald-100 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                        SAVE ALL ASSESSMENTS
                    </button>
                </div>
            </div>
        @endif


    @endif

    {{-- Psychomotor Modal --}}
    @if($showPsychomotorModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closePsychomotor"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-white">
                        <h3 class="text-lg leading-6 font-bold" id="modal-title">
                            Psychomotor Assessment — {{ $selectedStudentName }}
                        </h3>
                        <p class="text-xs text-indigo-100 mt-1">Assess affective skills and traits</p>
                    </div>
                    <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                            @foreach($this->traitMap() as $slug => $trait)
                                <div class="flex flex-col gap-2 p-3.5 bg-gray-50/50 hover:bg-indigo-50/30 rounded-2xl transition-all border border-gray-200/50 hover:border-indigo-100 shadow-sm">
                                    <span class="text-xs font-black text-indigo-950/80 tracking-wide uppercase">{{ $trait }}</span>
                                    @php
                                        $modalValue = $psychomotorScores[$slug] ?? '';
                                    @endphp
                                    <div class="grid grid-cols-4 p-1 bg-gray-100 rounded-xl gap-1 ring-1 ring-gray-200/50 shadow-inner w-full">
                                        <button type="button" 
                                            wire:click="$set('psychomotorScores.{{ $slug }}', 'Excellent')"
                                            class="py-2.5 rounded-lg text-[10px] font-black tracking-wider transition-all duration-150 uppercase text-center
                                            {{ $modalValue === 'Excellent' 
                                                ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25 scale-[1.02]' 
                                                : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                            EXC
                                        </button>
                                        
                                        <button type="button" 
                                            wire:click="$set('psychomotorScores.{{ $slug }}', 'Good')"
                                            class="py-2.5 rounded-lg text-[10px] font-black tracking-wider transition-all duration-150 uppercase text-center
                                            {{ $modalValue === 'Good' 
                                                ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md shadow-blue-500/25 scale-[1.02]' 
                                                : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                            GD
                                        </button>
                                        
                                        <button type="button" 
                                            wire:click="$set('psychomotorScores.{{ $slug }}', 'Average')"
                                            class="py-2.5 rounded-lg text-[10px] font-black tracking-wider transition-all duration-150 uppercase text-center
                                            {{ $modalValue === 'Average' 
                                                ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md shadow-amber-500/25 scale-[1.02]' 
                                                : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                            AVG
                                        </button>
                                        
                                        <button type="button" 
                                            wire:click="$set('psychomotorScores.{{ $slug }}', 'Poor')"
                                            class="py-2.5 rounded-lg text-[10px] font-black tracking-wider transition-all duration-150 uppercase text-center
                                            {{ $modalValue === 'Poor' 
                                                ? 'bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-md shadow-red-500/25 scale-[1.02]' 
                                                : 'text-gray-500 hover:text-gray-900 hover:bg-white/50' }}">
                                            PR
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="savePsychomotor" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-sm font-bold text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto transition-all">
                            Save Assessment
                        </button>
                        <button type="button" wire:click="closePsychomotor" class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-gray-300 shadow-sm px-5 py-2.5 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto transition-all">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        // Inject shake animation styles once
        if (!document.getElementById('shake-animation-styles')) {
            const style = document.createElement('style');
            style.id = 'shake-animation-styles';
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
                    20%, 40%, 60%, 80% { transform: translateX(3px); }
                }
                .shake-row { animation: shake 0.6s ease-in-out; background-color: #fef2f2 !important; }
                .shake-input { animation: shake 0.6s ease-in-out; border-color: #ef4444 !important; background-color: #fef2f2 !important; }
            `;
            document.head.appendChild(style);
        }

        // shake-row event — $wire is available inside the script block
        $wire.on('shake-row', (event) => {
            const data = event[0] || event;
            const row   = document.querySelector(`tr[data-student-id="${data.studentId}"]`);
            const input = document.querySelector(`input[data-student-id="${data.studentId}"][data-field="${data.field}"]`);
            if (row && input) {
                row.classList.add('shake-row');
                input.classList.add('shake-input');
                setTimeout(() => {
                    row.classList.remove('shake-row');
                    input.classList.remove('shake-input');
                }, 600);
                input.focus();
                input.select();
            }
        });

        // Keyboard navigation — attach to the table body directly.
        // Using event delegation on document so it survives Livewire re-renders.
        function handleScoresheetKeydown(e) {
            const input = e.target;
            if (input.tagName !== 'INPUT' || input.type !== 'number') return;
            const table = input.closest('tbody');
            if (!table) return;

            const cell  = input.closest('td');
            const row   = cell.closest('tr');
            const cells = Array.from(row.querySelectorAll('td input[type="number"]'));
            const rows  = Array.from(table.querySelectorAll('tr:has(input)'));
            const ci    = cells.indexOf(input);
            const ri    = rows.indexOf(row);

            let next = null;
            let prevent = false;

            if (e.key === 'Enter') {
                prevent = true;
                if (ri < rows.length - 1) next = rows[ri + 1].querySelectorAll('td input[type="number"]')[ci];
            } else if (e.key === 'Tab' && !e.shiftKey) {
                prevent = true;
                next = ci < cells.length - 1 ? cells[ci + 1] : (ri < rows.length - 1 ? rows[ri + 1].querySelector('td input[type="number"]') : null);
            } else if (e.key === 'Tab' && e.shiftKey) {
                prevent = true;
                if (ci > 0) { next = cells[ci - 1]; }
                else if (ri > 0) { const pc = rows[ri - 1].querySelectorAll('td input[type="number"]'); next = pc[pc.length - 1]; }
            } else if (e.key === 'ArrowDown') {
                prevent = true;
                if (ri < rows.length - 1) next = rows[ri + 1].querySelectorAll('td input[type="number"]')[ci];
            } else if (e.key === 'ArrowUp') {
                prevent = true;
                if (ri > 0) next = rows[ri - 1].querySelectorAll('td input[type="number"]')[ci];
            } else if (e.key === 'ArrowRight' && input.selectionStart === input.value.length) {
                prevent = true;
                if (ci < cells.length - 1) next = cells[ci + 1];
            } else if (e.key === 'ArrowLeft' && input.selectionStart === 0) {
                prevent = true;
                if (ci > 0) next = cells[ci - 1];
            }

            if (prevent && next) { e.preventDefault(); next.focus(); next.select(); }
        }

        function handleScoresheetFocusin(e) {
            if (e.target.tagName === 'INPUT' && e.target.closest('tbody')) {
                const td = e.target.closest('td');
                td.style.boxShadow = '0 0 0 3px rgba(59,130,246,0.5)';
                td.style.position  = 'relative';
                td.style.zIndex    = '10';
            }
        }

        function handleScoresheetFocusout(e) {
            if (e.target.tagName === 'INPUT' && e.target.closest('tbody')) {
                const td = e.target.closest('td');
                td.style.boxShadow = '';
                td.style.zIndex    = '';
            }
        }

        // Attach once at document level — survives all Livewire re-renders
        if (!document.__scoresheetKeydownAttached) {
            document.addEventListener('keydown',  handleScoresheetKeydown);
            document.addEventListener('focusin',  handleScoresheetFocusin);
            document.addEventListener('focusout', handleScoresheetFocusout);
            document.__scoresheetKeydownAttached = true;
        }
    @endscript
</div>