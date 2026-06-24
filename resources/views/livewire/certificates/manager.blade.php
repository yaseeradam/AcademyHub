<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('more-features') }}"
                class="inline-flex items-center px-4 py-2 bg-white/80 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Certificate Generator</h2>
                <p class="text-slate-500 text-sm mt-0.5">Design and issue official certificates to students</p>
            </div>
        </div>
    </div>

    <!-- 3-Step Guided Workflow Dashboard Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Steps 1 & 2 -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Step 1: Choose Roster (Class & Students) -->
            <div class="rounded-2xl border border-slate-200/80 bg-white/90 backdrop-blur-xl shadow-md p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500 text-white font-bold text-sm shadow-sm">1</span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Select Class & Student</h3>
                        <p class="text-xs text-slate-500">Pick a class to load the student list</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Class</label>
                        <select wire:model.live="classId"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            <option value="">-- Choose Class --</option>
                            @foreach ($this->classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Search Student (Optional)</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input wire:model.live.debounce.250ms="search" placeholder="Type student's name or ID..."
                                class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                        </div>
                    </div>
                </div>

                <!-- Student Roster List -->
                <div class="border border-slate-100 rounded-xl overflow-hidden bg-slate-50/30">
                    <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-600">Student Roster</span>
                        <span class="text-[10px] text-slate-500 font-bold tabular-nums">{{ $classId ? $this->filteredStudents->count() : 0 }} active</span>
                    </div>
                    <div class="max-h-60 overflow-y-auto divide-y divide-slate-100">
                        @if(!$classId)
                            <div class="p-8 text-center text-slate-400 text-xs">
                                <svg class="h-8 w-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Please select a class above
                            </div>
                        @else
                            @forelse ($this->filteredStudents as $student)
                                <div class="flex items-center justify-between px-4 py-2.5 bg-white hover:bg-slate-50/50 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-8 w-8 rounded-full bg-amber-50 border border-amber-100 flex items-center justify-center text-xs font-bold text-amber-700 shadow-sm">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-700 leading-snug truncate">{{ $student->full_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">{{ $student->admission_number ?: 'No ID' }}</p>
                                        </div>
                                    </div>
                                    <button wire:click="issueForStudent({{ $student->id }})"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200/60 rounded-lg hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all shadow-sm shrink-0">
                                        <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Download
                                    </button>
                                </div>
                            @empty
                                <div class="p-8 text-center text-slate-400 text-xs">No matching students found</div>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 2: Certificate Information -->
            <div class="rounded-2xl border border-slate-200/80 bg-white/90 backdrop-blur-xl shadow-md p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500 text-white font-bold text-sm shadow-sm">2</span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Certificate Information</h3>
                        <p class="text-xs text-slate-500">Customize the certificate's text</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Certificate Type</label>
                            <select wire:model.live="type"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                                <option value="Achievement">Academic Merit</option>
                                <option value="Attendance">Perfect Attendance</option>
                                <option value="Excellence">Sports Excellence</option>
                                <option value="Graduation">Graduation</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Issue Date</label>
                            <input wire:model.live="issueDate" type="date"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Title on Certificate</label>
                        <input wire:model.live.debounce.500ms="title" placeholder="Certificate of Achievement"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:border-transparent" />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Reason / Description</label>
                        <textarea wire:model.live.debounce.500ms="description" placeholder="e.g. Outstanding performance and academic excellence in all subjects." rows="3"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Live Design Preview & Step 3 Selection -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Live Design Preview Card -->
            <div class="rounded-2xl border border-slate-200/80 bg-white shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <svg class="h-4.5 w-4.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Live Design Preview
                    </h3>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1 rounded-full shadow-sm">{{ $this->templateLabel }} Design</span>
                </div>
                <div class="p-6 bg-slate-50/20 flex items-center justify-center">
                    <div class="w-full overflow-x-auto">

                    {{-- Modern --}}
                    @if($template === 'modern')
                        <div
                            class="aspect-[1.414/1] w-full max-w-2xl mx-auto bg-white border-8 border-slate-100 shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300">
                            <div class="absolute inset-2 border border-slate-100 pointer-events-none"></div>
                            <div class="mb-4">
                                <svg class="h-10 w-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path
                                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                                <h3 class="text-lg font-bold text-slate-400 uppercase tracking-widest">
                                    {{ config('app.name') }}
                                </h3>
                            </div>
                            <h1 class="text-4xl font-serif italic text-slate-800 mb-4">
                                {{ $title ?: 'Certificate of Achievement' }}
                            </h1>
                            <p class="text-sm text-slate-500 mb-4 uppercase tracking-tighter">This is to certify that</p>
                            <div class="mb-6"><span
                                    class="text-3xl font-serif font-bold italic text-slate-700 border-b-2 border-slate-200 px-8 py-1">Student
                                    Name</span></div>
                            <div class="space-y-1">
                                <p class="text-sm font-medium text-slate-600 uppercase">
                                    {{ $type === 'Achievement' ? 'Outstanding Academic Merit' : ($type === 'Attendance' ? 'Perfect Attendance Record' : ($type === 'Excellence' ? 'Excellence in Athleticism' : 'Academic Excellence')) }}
                                </p>
                                <p class="text-xs text-slate-400 max-w-xs mx-auto italic">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                            </div>
                            <div
                                class="absolute bottom-8 left-8 right-8 flex justify-between items-end border-t border-slate-50 pt-2">
                                <div class="w-32 border-t border-slate-200">
                                    <p class="text-[10px] text-slate-400 mt-1">Principal</p>
                                </div>
                                <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center"><svg
                                        class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg></div>
                                <div class="w-32 border-t border-slate-200">
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Classic --}}
                    @if($template === 'classic')
                        <div
                            class="aspect-[1.414/1] w-full max-w-2xl mx-auto bg-white shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden border-4 border-blue-500 transition-all duration-300">
                            <div class="absolute inset-2 border-2 border-blue-400 pointer-events-none"></div>
                            <h3 class="text-base font-bold text-slate-900 uppercase tracking-widest mb-1">
                                {{ config('app.name') }}
                            </h3>
                            <div class="w-3/4 border-t-2 border-blue-500 my-3"></div>
                            <h1 class="text-3xl font-bold text-slate-900 mb-1">{{ $title ?: 'Certificate' }}</h1>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-600 mb-3">{{ $type }}</p>
                            <p class="text-xs uppercase tracking-wider text-slate-500 mb-2">Presented to</p>
                            <span class="text-2xl font-bold text-blue-600 mb-2">Student Name</span>
                            <p class="text-xs text-slate-500 italic max-w-xs">
                                {{ $description ?: 'Outstanding performance' }}
                            </p>
                            <div class="absolute bottom-6 left-8 right-8 flex justify-between">
                                <div class="w-28 border-t border-slate-300">
                                    <p class="text-[9px] text-slate-500 mt-1 font-bold uppercase">Signature</p>
                                </div>
                                <div class="w-28 border-t border-slate-300">
                                    <p class="text-[9px] text-slate-500 mt-1 font-bold uppercase">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Elegant --}}
                    @if($template === 'elegant')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(135deg, #fffef5, #fef9e7); border: 6px solid #d4af37;">
                            <div class="absolute inset-3 border-2 border-blue-900 pointer-events-none"></div>
                            <div class="absolute top-5 left-5 w-12 h-12 border-t-4 border-l-4 border-yellow-600"></div>
                            <div class="absolute top-5 right-5 w-12 h-12 border-t-4 border-r-4 border-yellow-600"></div>
                            <div class="absolute bottom-5 left-5 w-12 h-12 border-b-4 border-l-4 border-yellow-600"></div>
                            <div class="absolute bottom-5 right-5 w-12 h-12 border-b-4 border-r-4 border-yellow-600"></div>
                            <h3 class="text-sm font-bold text-blue-900 uppercase tracking-[0.3em]">{{ config('app.name') }}
                            </h3>
                            <div class="w-48 border-t border-yellow-600 my-3 relative">
                                <span
                                    class="absolute -top-2 left-1/2 -translate-x-1/2 bg-yellow-50 px-2 text-yellow-600 text-[10px]">◆</span>
                            </div>
                            <h1 class="text-3xl font-serif text-blue-900 mb-1">{{ $title ?: 'Certificate of Achievement' }}
                            </h1>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-600 mb-3">{{ $type }}</p>
                            <p class="text-xs italic text-slate-500 mb-2">This Certificate is Proudly Presented To</p>
                            <span
                                class="text-2xl font-serif italic text-blue-900 border-b-2 border-yellow-600 px-6 py-1">Student
                                Name</span>
                            <p class="text-xs text-slate-500 italic mt-3 max-w-xs">
                                {{ $description ?: 'Outstanding performance' }}
                            </p>
                            <div class="absolute bottom-8 left-10 right-10 flex justify-between items-end">
                                <div class="w-28 border-t border-blue-900">
                                    <p class="text-[9px] text-blue-900 mt-1 font-bold uppercase tracking-wider">Signature
                                    </p>
                                </div>
                                <div
                                    class="w-14 h-14 rounded-full border-2 border-yellow-600 bg-yellow-50 flex items-center justify-center text-[8px] font-bold text-blue-900 uppercase">
                                    SEAL</div>
                                <div class="w-28 border-t border-blue-900">
                                    <p class="text-[9px] text-blue-900 mt-1 font-bold uppercase tracking-wider">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Vibrant --}}
                    @if($template === 'vibrant')
                        <div
                            class="aspect-[1.414/1] w-full max-w-2xl mx-auto bg-amber-50 shadow-lg rounded-2xl p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300">
                            <div class="absolute top-0 left-0 right-0 h-28 rounded-t-2xl"
                                style="background: linear-gradient(135deg, #6c5ce7, #a855f7, #ec4899, #f59e0b);"></div>
                            <div class="absolute bottom-0 left-0 right-0 h-20 rounded-b-2xl"
                                style="background: linear-gradient(135deg, #f59e0b, #06b6d4, #6c5ce7);"></div>
                            <div class="absolute top-32 left-16 w-2 h-2 bg-yellow-400 rounded-full opacity-60"></div>
                            <div class="absolute top-36 right-24 w-1.5 h-1.5 bg-purple-400 rounded-full opacity-50"></div>
                            <div class="relative z-10">
                                <h3 class="text-sm font-extrabold text-white uppercase tracking-wider drop-shadow mb-6">
                                    {{ config('app.name') }}
                                </h3>
                            </div>
                            <div class="relative z-10 mt-2">
                                <h1 class="text-2xl font-extrabold text-slate-900 mb-0">Certificate</h1>
                                <span class="text-lg italic text-purple-500 font-semibold">of</span>
                                <p class="text-xl font-extrabold text-purple-600 uppercase tracking-wider">
                                    {{ $type ?: 'Achievement' }}
                                </p>
                            </div>
                            <p class="relative z-10 text-xs uppercase tracking-wider text-slate-500 mt-3 font-bold">This
                                Certificate is Presented To</p>
                            <span class="relative z-10 text-2xl font-serif font-bold italic text-slate-900 mt-1">Student
                                Name</span>
                            <div class="relative z-10 w-52 h-0.5 mx-auto mt-1"
                                style="background: linear-gradient(90deg, transparent, #a855f7, transparent);"></div>
                            <p class="relative z-10 text-xs text-slate-500 italic mt-2 max-w-xs">
                                {{ $description ?: 'Outstanding performance' }}
                            </p>
                            <div class="relative z-10 mt-3 w-14 h-14 rounded-full flex items-center justify-center text-[7px] font-extrabold text-white uppercase mx-auto"
                                style="background: linear-gradient(135deg, #fbbf24, #f59e0b); border: 2px solid #d97706;">
                                EXCELLENCE<br>AWARD</div>
                            <div class="absolute bottom-24 left-10 right-10 flex justify-between z-10">
                                <div class="w-24 border-t border-slate-300">
                                    <p class="text-[9px] text-slate-500 mt-1 font-bold">Principal</p>
                                </div>
                                <div class="w-24 border-t border-slate-300">
                                    <p class="text-[9px] text-slate-500 mt-1 font-bold">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Dated' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Minimal --}}
                    @if($template === 'minimal')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto bg-white shadow-lg rounded p-6 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="border: 1px solid #e2e8f0;">
                            <div class="absolute top-0 left-6 right-6 h-1 bg-sky-500"></div>
                            <div class="absolute inset-5 border border-slate-100 pointer-events-none"></div>
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.4em]">
                                {{ config('app.name') }}
                            </h3>
                            <div class="w-14 border-t border-sky-500 my-5"></div>
                            <h1 class="text-4xl font-light text-slate-900 tracking-tight">{{ $title ?: 'Certificate' }}</h1>
                            <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-sky-500 mt-2">{{ $type }}</p>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mt-6 font-semibold">Presented to
                            </p>
                            <span class="text-2xl font-bold text-slate-900 mt-2 tracking-wide">Student Name</span>
                            <div class="w-44 border-t border-slate-200 mt-2"></div>
                            <p class="text-xs text-slate-400 mt-4 max-w-xs">{{ $description ?: 'Outstanding performance' }}
                            </p>
                            <div class="absolute bottom-8 left-12 right-12 flex justify-between">
                                <div class="w-28 border-t border-slate-200">
                                    <p class="text-[8px] text-slate-400 mt-1 font-bold uppercase tracking-widest">Signature
                                    </p>
                                </div>
                                <div class="w-28 border-t border-slate-200">
                                    <p class="text-[8px] text-slate-400 mt-1 font-bold uppercase tracking-widest">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Royal --}}
                    @if($template === 'royal')
                        <div
                            class="aspect-[1.414/1] w-full max-w-2xl mx-auto bg-purple-50 shadow-lg rounded p-0 flex relative overflow-hidden transition-all duration-300">
                            <div class="w-1/4 h-full"
                                style="background: linear-gradient(180deg, #581c87, #7c3aed, #a855f7, #d946ef);"></div>
                            <div class="absolute top-6 left-[28%] right-6 bottom-6 border-2 border-yellow-600"></div>
                            <div class="absolute top-8 left-[29%] right-8 bottom-8 border border-purple-400"></div>
                            <div class="absolute top-8 left-4 w-[22%] text-center">
                                <p class="text-[10px] font-bold text-white/90 uppercase tracking-wider mt-4 leading-snug">
                                    {{ config('app.name') }}
                                </p>
                            </div>
                            <div class="flex-1 flex flex-col items-center justify-center text-center px-6 relative z-10">
                                <h1 class="text-3xl font-bold text-slate-900 uppercase tracking-widest">Certificate</h1>
                                <span class="text-base italic text-purple-500 font-medium">of</span>
                                <p class="text-lg font-bold text-purple-700 uppercase tracking-[0.2em]">
                                    {{ $type ?: 'Achievement' }}
                                </p>
                                <p class="text-xs text-slate-500 mt-4 font-semibold uppercase tracking-wider">This is
                                    Proudly Presented To</p>
                                <span class="text-xl font-serif font-bold italic text-slate-900 mt-2">Student Name</span>
                                <div class="w-44 border-t-2 border-yellow-600 mt-1 relative">
                                    <span
                                        class="absolute -top-1.5 left-1/2 -translate-x-1/2 bg-purple-50 px-1 text-yellow-600 text-[8px]">◇</span>
                                </div>
                                <p class="text-xs text-slate-500 italic mt-2 max-w-[16rem]">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-12 h-12 rounded-full flex items-center justify-center text-[7px] font-extrabold uppercase mx-auto border-2 border-amber-700"
                                    style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #7c2d12;">
                                    EXCEL-<br>LENCE</div>
                            </div>
                            <div class="absolute bottom-6 left-[30%] right-10 flex justify-between">
                                <div class="w-24 border-t border-purple-300">
                                    <p class="text-[8px] text-purple-700 mt-1 font-bold uppercase">Signature</p>
                                </div>
                                <div class="w-24 border-t border-purple-300">
                                    <p class="text-[8px] text-purple-700 mt-1 font-bold uppercase">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Prestige --}}
                    @if($template === 'prestige')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(160deg, #0a1628, #0f2241, #162d50, #0a1628);">
                            {{-- Triple border layers --}}
                            <div class="absolute inset-1" style="border: 3px solid #c9a84c;"></div>
                            <div class="absolute inset-2" style="border: 1px solid rgba(201,168,76,0.4);"></div>
                            <div class="absolute inset-3" style="border: 2px solid #c9a84c;"></div>
                            {{-- Gold corner ornaments --}}
                            <div class="absolute top-4 left-4 w-10 h-10"
                                style="border-top: 4px solid #e8d48b; border-left: 4px solid #e8d48b;"></div>
                            <div class="absolute top-4 right-4 w-10 h-10"
                                style="border-top: 4px solid #e8d48b; border-right: 4px solid #e8d48b;"></div>
                            <div class="absolute bottom-4 left-4 w-10 h-10"
                                style="border-bottom: 4px solid #e8d48b; border-left: 4px solid #e8d48b;"></div>
                            <div class="absolute bottom-4 right-4 w-10 h-10"
                                style="border-bottom: 4px solid #e8d48b; border-right: 4px solid #e8d48b;"></div>
                            <div class="relative z-10">
                                <h3 class="text-sm font-bold uppercase tracking-[0.3em]" style="color: #c9a84c;">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-48 h-px mx-auto my-3"
                                    style="background: linear-gradient(90deg, transparent, #c9a84c, transparent);"></div>
                                <h1 class="text-3xl font-bold uppercase tracking-wider" style="color: #f5f0e1;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #c9a84c;">
                                    {{ $type }}
                                </p>
                                <p class="text-xs italic mt-5 uppercase tracking-wider" style="color: #8899aa;">This
                                    Certificate is Proudly Awarded To</p>
                                <span class="text-2xl font-serif italic mt-2 inline-block" style="color: #f5f0e1;">Student
                                    Name</span>
                                <div class="w-44 mx-auto mt-1 relative" style="border-top: 2px solid #c9a84c;">
                                    <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 px-2 text-[8px]"
                                        style="background: #0f2241; color: #c9a84c;">★</span>
                                </div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #8899aa;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase"
                                    style="background: linear-gradient(135deg, #c9a84c, #e8d48b, #c9a84c); border: 3px solid #a08530; color: #0f2241;">
                                    HONOR<br>AWARD</div>
                            </div>
                            <div class="absolute bottom-8 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #c9a84c;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #c9a84c;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #c9a84c;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #c9a84c;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Botanical --}}
                    @if($template === 'botanical')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(170deg, #f7faf4, #eef5e8, #f0f7ec, #fafcf8);">
                            {{-- Top and bottom green bars --}}
                            <div class="absolute top-0 left-0 right-0 h-2"
                                style="background: linear-gradient(90deg, #4a7c59, #6b9e7a, #8bb89a, #6b9e7a, #4a7c59);">
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 h-2"
                                style="background: linear-gradient(90deg, #4a7c59, #6b9e7a, #8bb89a, #6b9e7a, #4a7c59);">
                            </div>
                            {{-- Frame --}}
                            <div class="absolute inset-2" style="border: 2px solid #4a7c59;"></div>
                            <div class="absolute inset-3" style="border: 1px solid #a8c8a0;"></div>
                            {{-- Leaf corners --}}
                            <div class="absolute top-4 left-4 w-8 h-8"
                                style="border-radius: 0 50% 0 50%; background: rgba(74,124,89,0.12); border: 1px solid rgba(74,124,89,0.25);">
                            </div>
                            <div class="absolute top-4 right-4 w-8 h-8"
                                style="border-radius: 50% 0 50% 0; background: rgba(74,124,89,0.12); border: 1px solid rgba(74,124,89,0.25);">
                            </div>
                            <div class="absolute bottom-4 left-4 w-8 h-8"
                                style="border-radius: 50% 0 50% 0; background: rgba(74,124,89,0.12); border: 1px solid rgba(74,124,89,0.25);">
                            </div>
                            <div class="absolute bottom-4 right-4 w-8 h-8"
                                style="border-radius: 0 50% 0 50%; background: rgba(74,124,89,0.12); border: 1px solid rgba(74,124,89,0.25);">
                            </div>
                            {{-- Side vine accents --}}
                            <div class="absolute top-1/2 left-4 -translate-y-1/2 w-0.5 h-32"
                                style="background: linear-gradient(180deg, transparent, #a8c8a0, #4a7c59, #a8c8a0, transparent);">
                            </div>
                            <div class="absolute top-1/2 right-4 -translate-y-1/2 w-0.5 h-32"
                                style="background: linear-gradient(180deg, transparent, #a8c8a0, #4a7c59, #a8c8a0, transparent);">
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-sm font-bold uppercase tracking-[0.3em]" style="color: #4a7c59;">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-36 mx-auto my-3 relative" style="border-top: 1px solid #4a7c59;">
                                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 text-sm"
                                        style="background: #f0f7ec; color: #4a7c59;">❧</span>
                                </div>
                                <h1 class="text-3xl font-serif" style="color: #2d3b2d;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #6b9e7a;">
                                    {{ $type }}
                                </p>
                                <p class="text-xs italic mt-5 uppercase tracking-wider" style="color: #7a8e7a;">This
                                    Certificate is Gratefully Presented To</p>
                                <span class="text-2xl font-serif italic mt-2 inline-block" style="color: #2d3b2d;">Student
                                    Name</span>
                                <div class="w-44 h-0.5 mx-auto mt-1"
                                    style="background: linear-gradient(90deg, transparent, #4a7c59, transparent);"></div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #7a8e7a;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase"
                                    style="background: linear-gradient(135deg, #4a7c59, #6b9e7a); border: 3px solid #3a6248; color: #f5f0e1;">
                                    MERIT<br>AWARD</div>
                            </div>
                            <div class="absolute bottom-8 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #4a7c59;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #4a7c59;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #4a7c59;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #4a7c59;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Aurora --}}
                    @if($template === 'aurora')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: #fafbff;">
                            {{-- Top aurora gradient --}}
                            <div class="absolute top-0 left-0 right-0 h-24 rounded-t"
                                style="background: linear-gradient(135deg, #0d9488, #2dd4bf, #7c3aed, #a855f7, #ec4899); opacity: 0.9;">
                            </div>
                            {{-- Bottom aurora gradient --}}
                            <div class="absolute bottom-0 left-0 right-0 h-16 rounded-b"
                                style="background: linear-gradient(135deg, #a855f7, #7c3aed, #2dd4bf, #0d9488); opacity: 0.85;">
                            </div>
                            {{-- Frost panel --}}
                            <div class="absolute inset-x-4 top-12 bottom-3"
                                style="background: rgba(255,255,255,0.92); border: 1px solid rgba(13,148,136,0.2); border-radius: 4px;">
                            </div>
                            {{-- Accent line --}}
                            <div class="absolute top-24 left-8 right-8 h-0.5"
                                style="background: linear-gradient(90deg, #0d9488, #7c3aed, #ec4899, #7c3aed, #0d9488);">
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-xs font-bold uppercase tracking-[0.3em] text-white drop-shadow mb-5">
                                    {{ config('app.name') }}
                                </h3>
                                <h1 class="text-3xl font-serif mt-4" style="color: #1a1a2e;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <div class="w-36 h-0.5 mx-auto mt-2"
                                    style="background: linear-gradient(90deg, #0d9488, #7c3aed, #ec4899); border-radius: 2px;">
                                </div>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-2" style="color: #7c3aed;">
                                    {{ $type }}
                                </p>
                                <p class="text-xs italic mt-4 uppercase tracking-wider text-slate-500">This Certificate is
                                    Presented With Distinction To</p>
                                <span class="text-2xl font-serif italic mt-2 inline-block" style="color: #0d9488;">Student
                                    Name</span>
                                <div class="w-44 h-0.5 mx-auto mt-1"
                                    style="background: linear-gradient(90deg, transparent, #0d9488, #7c3aed, #0d9488, transparent);">
                                </div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto text-slate-500">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold text-white uppercase"
                                    style="background: linear-gradient(135deg, #0d9488, #2dd4bf); border: 3px solid #0f766e;">
                                    HONOR<br>ROLL</div>
                            </div>
                            <div class="absolute bottom-20 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #0d9488;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #0d9488;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #0d9488;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #0d9488;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Heritage --}}
                    @if($template === 'heritage')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(150deg, #fdf6ec, #faf0de, #f5e8d0, #fdf6ec);">
                            {{-- 4-layer ornate border --}}
                            <div class="absolute"
                                style="top:2px; left:2px; right:2px; bottom:2px; border: 5px solid #8b1a1a;"></div>
                            <div class="absolute"
                                style="top:10px; left:10px; right:10px; bottom:10px; border: 1px solid #d4af37;"></div>
                            <div class="absolute"
                                style="top:12px; left:12px; right:12px; bottom:12px; border: 1px solid #d4af37;"></div>
                            <div class="absolute"
                                style="top:16px; left:16px; right:16px; bottom:16px; border: 3px solid #8b1a1a;"></div>
                            {{-- Gold corners --}}
                            <div class="absolute top-5 left-5 w-10 h-10"
                                style="border-top: 5px solid #d4af37; border-left: 5px solid #d4af37;"></div>
                            <div class="absolute top-5 right-5 w-10 h-10"
                                style="border-top: 5px solid #d4af37; border-right: 5px solid #d4af37;"></div>
                            <div class="absolute bottom-5 left-5 w-10 h-10"
                                style="border-bottom: 5px solid #d4af37; border-left: 5px solid #d4af37;"></div>
                            <div class="absolute bottom-5 right-5 w-10 h-10"
                                style="border-bottom: 5px solid #d4af37; border-right: 5px solid #d4af37;"></div>
                            {{-- Inner maroon corners --}}
                            <div class="absolute top-6 left-6 w-5 h-5"
                                style="border-top: 2px solid #8b1a1a; border-left: 2px solid #8b1a1a;"></div>
                            <div class="absolute top-6 right-6 w-5 h-5"
                                style="border-top: 2px solid #8b1a1a; border-right: 2px solid #8b1a1a;"></div>
                            <div class="absolute bottom-6 left-6 w-5 h-5"
                                style="border-bottom: 2px solid #8b1a1a; border-left: 2px solid #8b1a1a;"></div>
                            <div class="absolute bottom-6 right-6 w-5 h-5"
                                style="border-bottom: 2px solid #8b1a1a; border-right: 2px solid #8b1a1a;"></div>
                            {{-- Side accent bars --}}
                            <div class="absolute top-1/2 left-1 -translate-y-1/2 w-0.5 h-24"
                                style="background: linear-gradient(180deg, transparent, #d4af37, #8b1a1a, #d4af37, transparent);">
                            </div>
                            <div class="absolute top-1/2 right-1 -translate-y-1/2 w-0.5 h-24"
                                style="background: linear-gradient(180deg, transparent, #d4af37, #8b1a1a, #d4af37, transparent);">
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-sm font-bold uppercase tracking-[0.3em]" style="color: #8b1a1a;">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-40 mx-auto my-3 relative" style="border-top: 1px solid #8b1a1a;">
                                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 text-[10px]"
                                        style="background: #f5e8d0; color: #d4af37;">✦</span>
                                </div>
                                <h1 class="text-3xl font-serif" style="color: #8b1a1a;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #d4af37;">
                                    {{ $type }}
                                </p>
                                <p class="text-xs italic mt-4 uppercase tracking-wider" style="color: #8b6b4a;">This
                                    Certificate is Honourably Bestowed Upon</p>
                                <span class="text-2xl font-serif italic mt-2 inline-block" style="color: #3c1f1f;">Student
                                    Name</span>
                                <div class="w-44 mx-auto mt-1 relative"
                                    style="border-top: 2px solid transparent; background-image: linear-gradient(90deg, transparent, #8b1a1a, #d4af37, #8b1a1a, transparent); background-size: 100% 2px; background-repeat: no-repeat; background-position: top;">
                                    <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 px-1 text-[8px]"
                                        style="background: #f5e8d0; color: #d4af37;">◆</span>
                                </div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #8b6b4a;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase"
                                    style="background: linear-gradient(135deg, #d4af37, #e8d48b, #d4af37); border: 4px solid #8b1a1a; color: #8b1a1a;">
                                    DISTINC-<br>TION</div>
                            </div>
                            <div class="absolute bottom-8 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #8b1a1a;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #8b1a1a;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #8b1a1a;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #8b1a1a;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Obsidian --}}
                    @if($template === 'obsidian')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(145deg, #1a1a1a, #2d2d2d, #1f1f1f, #111111);">
                            {{-- Thin silver border --}}
                            <div class="absolute inset-2" style="border: 1px solid rgba(192,192,192,0.3);"></div>
                            <div class="absolute inset-3" style="border: 2px solid rgba(192,192,192,0.15);"></div>
                            {{-- Top silver bar --}}
                            <div class="absolute top-0 left-16 right-16 h-1"
                                style="background: linear-gradient(90deg, transparent, #c0c0c0, #e8e8e8, #c0c0c0, transparent);">
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-[10px] font-bold uppercase tracking-[0.4em]" style="color: #999;">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-20 h-px mx-auto my-3"
                                    style="background: linear-gradient(90deg, transparent, #888, transparent);"></div>
                                <h1 class="text-3xl font-light uppercase tracking-[0.3em]" style="color: #ffffff;">
                                    {{ $title ?: 'Certificate' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #c0c0c0;">
                                    {{ $type }}
                                </p>
                                <p class="text-[10px] mt-5 uppercase tracking-wider" style="color: #777;">Awarded to</p>
                                <span class="text-2xl font-light mt-2 inline-block tracking-wide"
                                    style="color: #ffffff;">Student Name</span>
                                <div class="w-44 h-px mx-auto mt-2"
                                    style="background: linear-gradient(90deg, transparent, #c0c0c0, transparent);"></div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #999;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase"
                                    style="background: linear-gradient(135deg, #888, #ccc, #888); border: 2px solid #666; color: #222;">
                                    PLATI-<br>NUM</div>
                            </div>
                            <div class="absolute bottom-8 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid rgba(192,192,192,0.3);">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #999;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid rgba(192,192,192,0.3);">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #999;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Sahara --}}
                    @if($template === 'sahara')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(155deg, #faf3e6, #f0e0c4, #e8d5b0, #f5eadb);">
                            {{-- Terracotta bands --}}
                            <div class="absolute top-0 left-0 right-0 h-10"
                                style="background: linear-gradient(90deg, #c2703e, #d4874e, #c2703e, #b85c30, #c2703e);">
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 h-7"
                                style="background: linear-gradient(90deg, #c2703e, #d4874e, #c2703e, #b85c30, #c2703e);">
                            </div>
                            {{-- Copper frame --}}
                            <div class="absolute"
                                style="top: 14px; left: 6px; right: 6px; bottom: 10px; border: 2px solid #b87333;"></div>
                            <div class="absolute"
                                style="top: 18px; left: 10px; right: 10px; bottom: 14px; border: 1px solid rgba(184,115,51,0.4);">
                            </div>
                            {{-- Diamond corners --}}
                            <div class="absolute w-3 h-3"
                                style="top: 12px; left: 4px; background: #c2703e; transform: rotate(45deg);"></div>
                            <div class="absolute w-3 h-3"
                                style="top: 12px; right: 4px; background: #c2703e; transform: rotate(45deg);"></div>
                            <div class="absolute w-3 h-3"
                                style="bottom: 8px; left: 4px; background: #c2703e; transform: rotate(45deg);"></div>
                            <div class="absolute w-3 h-3"
                                style="bottom: 8px; right: 4px; background: #c2703e; transform: rotate(45deg);"></div>
                            <div class="relative z-10">
                                <h3 class="text-sm font-bold uppercase tracking-[0.3em]" style="color: #5c3a1e;">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-40 mx-auto my-3 relative" style="border-top: 1px solid #b87333;">
                                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 text-sm"
                                        style="background: #f0e0c4; color: #c2703e;">❖</span>
                                </div>
                                <h1 class="text-3xl font-serif" style="color: #5c3a1e;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #c2703e;">
                                    {{ $type }}
                                </p>
                                <p class="text-xs italic mt-5 uppercase tracking-wider" style="color: #8b6b4a;">This Honor
                                    is Bestowed Upon</p>
                                <span class="text-2xl font-serif italic mt-2 inline-block" style="color: #5c3a1e;">Student
                                    Name</span>
                                <div class="w-44 h-0.5 mx-auto mt-1"
                                    style="background: linear-gradient(90deg, transparent, #c2703e, #d4874e, #c2703e, transparent);">
                                </div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #8b6b4a;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase"
                                    style="background: linear-gradient(135deg, #b87333, #d4874e, #b87333); border: 3px solid #8b5a2b; color: #fff;">
                                    HONOR<br>SEAL</div>
                            </div>
                            <div class="absolute bottom-10 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #b87333;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #b87333;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #b87333;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #b87333;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Oceanic --}}
                    @if($template === 'oceanic')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: linear-gradient(165deg, #e8f4f8, #d4eef5, #c0e6f0, #e0f0f5);">
                            {{-- Top wave --}}
                            <div class="absolute top-0 left-0 right-0 h-16"
                                style="background: linear-gradient(180deg, #0a4d68, #0e6b8a, #1090b0, transparent);"></div>
                            <div class="absolute top-12 left-0 right-0 h-6"
                                style="background: linear-gradient(180deg, rgba(16,144,176,0.4), transparent);"></div>
                            {{-- Bottom wave --}}
                            <div class="absolute bottom-0 left-0 right-0 h-12"
                                style="background: linear-gradient(0deg, #0a4d68, #0e6b8a, #1090b0, transparent);"></div>
                            {{-- Side stripes --}}
                            <div class="absolute top-1/2 left-0 -translate-y-1/2 w-1 h-40"
                                style="background: linear-gradient(180deg, transparent, #0e6b8a, #56c5d0, #0e6b8a, transparent); border-radius: 0 3px 3px 0;">
                            </div>
                            <div class="absolute top-1/2 right-0 -translate-y-1/2 w-1 h-40"
                                style="background: linear-gradient(180deg, transparent, #0e6b8a, #56c5d0, #0e6b8a, transparent); border-radius: 3px 0 0 3px;">
                            </div>
                            {{-- Frame --}}
                            <div class="absolute"
                                style="top: 20px; left: 6px; right: 6px; bottom: 16px; border: 2px solid rgba(10,77,104,0.3); border-radius: 3px;">
                            </div>
                            {{-- Bubble dots --}}
                            <div class="absolute w-1.5 h-1.5 rounded-full"
                                style="top: 22px; left: 10px; background: rgba(86,197,208,0.4);"></div>
                            <div class="absolute w-1 h-1 rounded-full"
                                style="top: 25px; right: 14px; background: rgba(86,197,208,0.3);"></div>
                            <div class="absolute w-1.5 h-1.5 rounded-full"
                                style="bottom: 22px; left: 16px; background: rgba(86,197,208,0.35);"></div>
                            <div class="relative z-10">
                                <h3 class="text-xs font-bold uppercase tracking-[0.3em] text-white drop-shadow mb-2">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-36 mx-auto my-3 relative" style="border-top: 1px solid #0e6b8a;">
                                    <span class="absolute -top-2 left-1/2 -translate-x-1/2 px-2 text-xs"
                                        style="background: #d4eef5; color: #0e6b8a;">◈</span>
                                </div>
                                <h1 class="text-3xl font-serif" style="color: #0a2540;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #1090b0;">
                                    {{ $type }}
                                </p>
                                <p class="text-xs italic mt-4 uppercase tracking-wider" style="color: #5a8a9a;">Proudly
                                    Presented To</p>
                                <span class="text-2xl font-serif italic mt-2 inline-block" style="color: #0a2540;">Student
                                    Name</span>
                                <div class="w-44 h-0.5 mx-auto mt-1"
                                    style="background: linear-gradient(90deg, transparent, #56c5d0, #0e6b8a, #56c5d0, transparent);">
                                </div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #5a8a9a;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase text-white"
                                    style="background: linear-gradient(135deg, #0e6b8a, #1090b0, #56c5d0); border: 3px solid #0a4d68;">
                                    NAUTI-<br>CAL</div>
                            </div>
                            <div class="absolute bottom-14 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #0e6b8a;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #0e6b8a;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #0e6b8a;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #0e6b8a;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Crimson --}}
                    @if($template === 'crimson')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded flex relative overflow-hidden transition-all duration-300"
                            style="background: #fafafa;">
                            {{-- Bold left red panel --}}
                            <div class="absolute top-0 left-0 w-20 h-full"
                                style="background: linear-gradient(180deg, #8b0000, #c41e1e, #e63946, #c41e1e, #8b0000);">
                            </div>
                            {{-- Horizontal stripe pattern on panel --}}
                            <div class="absolute top-0 left-0 w-20 h-full"
                                style="background: repeating-linear-gradient(0deg, transparent, transparent 12px, rgba(0,0,0,0.06) 12px, rgba(0,0,0,0.06) 13px);">
                            </div>
                            {{-- Top/bottom black bars --}}
                            <div class="absolute top-0 left-20 right-0 h-1.5" style="background: #1a0000;"></div>
                            <div class="absolute bottom-0 left-20 right-0 h-1.5" style="background: #1a0000;"></div>
                            {{-- Red accent line --}}
                            <div class="absolute top-1.5 left-20 right-0 h-0.5"
                                style="background: linear-gradient(90deg, #e63946, #c41e1e, transparent);"></div>
                            {{-- School name in left panel --}}
                            <div class="absolute top-4 left-2 w-16 text-center z-10">
                                <p class="text-[7px] font-bold uppercase text-white/90 tracking-wider leading-tight">
                                    {{ config('app.name') }}
                                </p>
                            </div>
                            {{-- Content area --}}
                            <div
                                class="relative z-10 flex-1 ml-20 p-6 flex flex-col items-center justify-center text-center">
                                <div class="text-4xl font-extrabold uppercase tracking-wider" style="color: #1a0000;">
                                    Certificate</div>
                                <div class="text-base font-semibold italic" style="color: #e63946;">of</div>
                                <div class="text-xl font-extrabold uppercase tracking-wider" style="color: #c41e1e;">
                                    {{ $type ?: 'Achievement' }}
                                </div>
                                <div class="w-16 h-0.5 mx-auto my-3" style="background: #e63946;"></div>
                                <p class="text-[10px] uppercase tracking-wider" style="color: #888;">This is Proudly
                                    Presented To</p>
                                <span class="text-2xl font-extrabold italic mt-2 inline-block"
                                    style="color: #1a0000;">Student Name</span>
                                <div class="w-44 mx-auto mt-1" style="border-top: 3px solid #e63946;"></div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #555;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-extrabold uppercase text-white"
                                    style="background: linear-gradient(135deg, #8b0000, #c41e1e, #e63946); border: 3px solid #5a0000;">
                                    SUPREME<br>AWARD</div>
                            </div>
                            <div class="absolute bottom-6 left-28 right-8 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid #c41e1e;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #c41e1e;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid #c41e1e;">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #c41e1e;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Ivory --}}
                    @if($template === 'ivory')
                        <div class="aspect-[1.414/1] w-full max-w-2xl mx-auto shadow-lg rounded p-4 flex flex-col items-center justify-center text-center relative overflow-hidden transition-all duration-300"
                            style="background: #fffef9;">
                            {{-- Rose-gold radial glows --}}
                            <div class="absolute -top-12 -right-12 w-56 h-56 rounded-full"
                                style="background: radial-gradient(circle, rgba(183,110,121,0.12), transparent 70%);"></div>
                            <div class="absolute -bottom-12 -left-12 w-56 h-56 rounded-full"
                                style="background: radial-gradient(circle, rgba(183,110,121,0.10), transparent 70%);"></div>
                            {{-- Thin rose-gold borders --}}
                            <div class="absolute inset-3" style="border: 1px solid rgba(183,110,121,0.25);"></div>
                            <div class="absolute inset-4" style="border: 1px solid rgba(183,110,121,0.12);"></div>
                            {{-- Rose-gold dot corners --}}
                            <div class="absolute top-2 left-2 w-2 h-2 rounded-full"
                                style="background: linear-gradient(135deg, #b76e79, #d4a0a7);"></div>
                            <div class="absolute top-2 right-2 w-2 h-2 rounded-full"
                                style="background: linear-gradient(135deg, #b76e79, #d4a0a7);"></div>
                            <div class="absolute bottom-2 left-2 w-2 h-2 rounded-full"
                                style="background: linear-gradient(135deg, #b76e79, #d4a0a7);"></div>
                            <div class="absolute bottom-2 right-2 w-2 h-2 rounded-full"
                                style="background: linear-gradient(135deg, #b76e79, #d4a0a7);"></div>
                            {{-- Top/bottom center line --}}
                            <div class="absolute top-3 left-1/2 -translate-x-1/2 w-24 h-px"
                                style="background: linear-gradient(90deg, transparent, #b76e79, transparent);"></div>
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 w-24 h-px"
                                style="background: linear-gradient(90deg, transparent, #b76e79, transparent);"></div>
                            <div class="relative z-10">
                                <h3 class="text-[10px] font-bold uppercase tracking-[0.35em]" style="color: #b76e79;">
                                    {{ config('app.name') }}
                                </h3>
                                <div class="w-32 mx-auto my-3 relative"
                                    style="border-top: 1px solid rgba(183,110,121,0.5);">
                                    <span class="absolute -top-1.5 left-1/2 -translate-x-1/2 px-2 text-[9px]"
                                        style="background: #fffef9; color: #b76e79;">⬥</span>
                                </div>
                                <h1 class="text-3xl font-normal" style="color: #3a2f2f;">
                                    {{ $title ?: 'Certificate of Achievement' }}
                                </h1>
                                <p class="text-xs font-bold uppercase tracking-[0.3em] mt-1" style="color: #b76e79;">
                                    {{ $type }}
                                </p>
                                <p class="text-[10px] mt-5 uppercase tracking-wider" style="color: #c0a0a5;">Gracefully
                                    Presented To</p>
                                <span class="text-2xl font-normal italic mt-2 inline-block" style="color: #3a2f2f;">Student
                                    Name</span>
                                <div class="w-44 h-px mx-auto mt-1"
                                    style="background: linear-gradient(90deg, transparent, #b76e79, transparent);"></div>
                                <p class="text-xs italic mt-3 max-w-xs mx-auto" style="color: #c0a0a5;">
                                    {{ $description ?: 'Outstanding performance' }}
                                </p>
                                <div class="mt-3 w-14 h-14 rounded-full mx-auto flex items-center justify-center text-[7px] font-bold uppercase text-white"
                                    style="background: linear-gradient(135deg, #b76e79, #d4a0a7, #e8c4c9, #d4a0a7, #b76e79); border: 2px solid #a05a65;">
                                    GRACE<br>AWARD</div>
                            </div>
                            <div class="absolute bottom-8 left-10 right-10 flex justify-between z-10">
                                <div class="w-24" style="border-top: 1px solid rgba(183,110,121,0.35);">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #b76e79;">Signature</p>
                                </div>
                                <div class="w-24" style="border-top: 1px solid rgba(183,110,121,0.35);">
                                    <p class="text-[8px] mt-1 font-bold uppercase" style="color: #b76e79;">
                                        {{ $issueDate ? \Carbon\Carbon::parse($issueDate)->format('M d, Y') : 'Date' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Step 3: Select Design & Export -->
        <div class="rounded-2xl border border-slate-200/80 bg-white/90 backdrop-blur-xl shadow-md p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500 text-white font-bold text-sm shadow-sm">3</span>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Select Template Design</h3>
                    <p class="text-xs text-slate-500">Choose a layout style. The preview will update instantly</p>
                </div>
            </div>

            <!-- Gorgeous Visual Grid of Templates -->
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                @foreach($availableTemplates as $i => $tpl)
                    <button wire:click="setTemplate({{ $i }})" type="button"
                        class="group relative rounded-xl border-2 p-2.5 text-center transition-all duration-200 hover:shadow-md {{ $previewIndex === $i ? 'border-amber-500 bg-amber-50 shadow-md ring-2 ring-amber-200' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                        
                        <div class="w-full aspect-[1.414/1] rounded-lg mb-2 flex items-center justify-center overflow-hidden {{ $previewIndex === $i ? 'ring-1 ring-amber-300' : '' }}"
                            @php
                                $bgStyles = [
                                    'modern' => 'background: linear-gradient(135deg, #f8fafc, #e2e8f0);',
                                    'classic' => 'background: #fff; border: 3px solid #3b82f6;',
                                    'elegant' => 'background: linear-gradient(135deg, #fffef5, #fef9e7); border: 3px solid #d4af37;',
                                    'vibrant' => 'background: linear-gradient(135deg, #6c5ce7, #a855f7, #ec4899);',
                                    'minimal' => 'background: #fff; border-top: 3px solid #0ea5e9;',
                                    'royal' => 'background: linear-gradient(180deg, #581c87, #7c3aed, #a855f7);',
                                    'prestige' => 'background: linear-gradient(160deg, #0a1628, #0f2241, #162d50);',
                                    'botanical' => 'background: linear-gradient(170deg, #f7faf4, #eef5e8);',
                                    'aurora' => 'background: linear-gradient(135deg, #0d9488, #7c3aed, #ec4899);',
                                    'heritage' => 'background: linear-gradient(150deg, #fdf6ec, #f5e8d0); border: 3px solid #8b1a1a;',
                                    'obsidian' => 'background: linear-gradient(145deg, #1a1a1a, #2d2d2d);',
                                    'sahara' => 'background: linear-gradient(155deg, #faf3e6, #e8d5b0);',
                                    'oceanic' => 'background: linear-gradient(165deg, #e8f4f8, #c0e6f0);',
                                    'crimson' => 'background: linear-gradient(145deg, #1a0a0a, #2d1515);',
                                    'ivory' => 'background: #fffef9; border: 2px solid rgba(183,110,121,0.3);',
                                ];
                            @endphp
                            style="{{ $bgStyles[$tpl['key']] ?? 'background: #f1f5f9;' }}">
                            <svg class="h-5 w-5 {{ in_array($tpl['key'], ['vibrant', 'royal', 'prestige', 'aurora', 'obsidian', 'crimson']) ? 'text-white/60' : 'text-slate-300' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[11px] font-bold block truncate {{ $previewIndex === $i ? 'text-amber-800' : 'text-slate-600 group-hover:text-slate-900' }}">
                            {{ $tpl['label'] }}
                        </span>
                        @if($previewIndex === $i)
                            <div class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center shadow-sm">
                                <svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>

            <!-- Bulk Actions Area -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-left">
                    <h4 class="text-sm font-bold text-slate-800">Bulk Actions</h4>
                    <p class="text-xs text-slate-500 font-medium">Download certificates for all matching students in a ZIP</p>
                </div>
                <button wire:click="bulkGenerate" wire:loading.attr="disabled" @disabled(!$classId || $this->filteredStudents->isEmpty())
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-amber-500 to-yellow-600 hover:from-amber-600 hover:to-yellow-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:shadow-none">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span wire:loading.remove wire:target="bulkGenerate">
                        Bulk Download ZIP ({{ $classId ? $this->filteredStudents->count() : 0 }})
                    </span>
                    <span wire:loading wire:target="bulkGenerate">Generating ZIP...</span>
                </button>
            </div>
        </div>
    </div> <!-- Close Right Column -->
</div> <!-- Close Main Grid -->

<!-- Centered Modal Loader Overlay -->
<div wire:loading wire:target="bulkGenerate, issueForStudent" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 shadow-2xl flex flex-col items-center max-w-sm mx-4 text-center border border-slate-100">
        <div class="relative w-16 h-16 mb-4">
            <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
            <div class="absolute inset-0 rounded-full border-4 border-amber-500 border-t-transparent animate-spin"></div>
        </div>
        <h4 class="text-lg font-bold text-slate-800 font-sans">Generating Certificate(s)</h4>
        <p class="text-xs text-slate-500 mt-1 font-medium">Please wait while we compile the PDF. This might take a few seconds...</p>
    </div>
</div>
</div> <!-- Close Root Div -->

@once
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-url', (event) => {
                    const data = event[0] || event;
                    const url = data?.url || data;
                    if (url) {
                        window.open(url, '_blank');
                    }
                });

                Livewire.on('alert', (event) => {
                    const data = event[0] || event;
                    if (data?.message) {
                        alert(data.message);
                    }
                });
            });
        </script>
    @endpush
@endonce