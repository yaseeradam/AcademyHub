<div class="min-h-screen bg-[#0f172a] text-[#f8fafc] flex items-center justify-center p-4 relative overflow-hidden font-['Outfit',sans-serif]">
    
    <!-- Ambient Glow Blobs -->
    <div class="absolute top-[10%] left-[15%] w-[300px] height-[300px] bg-violet-600/10 filter blur-[120px] rounded-full z-0 animate-pulse"></div>
    <div class="absolute bottom-[10%] right-[15%] w-[300px] height-[300px] bg-pink-600/10 filter blur-[120px] rounded-full z-0 animate-pulse delay-700"></div>

    <div class="w-full max-w-2xl bg-slate-900/40 backdrop-blur-xl border border-white/5 rounded-3xl p-8 shadow-2xl relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-8 border-b border-white/5 pb-6">
            <h1 class="text-2xl font-extrabold bg-gradient-to-r from-violet-400 to-pink-500 bg-clip-text text-transparent">Admission Screening Portal</h1>
            <p class="text-xs uppercase tracking-widest text-slate-400 font-bold mt-1.5">Aptitude &amp; Cognitive Examination</p>
        </div>

        @if($alreadyTaken)
            <!-- ALREADY COMPLETED SCREEN -->
            <div class="text-center py-6 flex flex-col items-center">
                <div class="h-20 w-20 rounded-full flex items-center justify-center mb-6 {{ $resultStatus === 'Passed' || $resultStatus === 'Admitted' ? 'bg-emerald-500/10 border border-emerald-500 text-emerald-500' : 'bg-rose-500/10 border border-rose-500 text-rose-500' }}">
                    @if($resultStatus === 'Passed' || $resultStatus === 'Admitted')
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        </svg>
                    @else
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @endif
                </div>

                <h2 class="text-2xl font-extrabold mb-2 {{ $resultStatus === 'Passed' || $resultStatus === 'Admitted' ? 'text-emerald-400' : 'text-rose-400' }}">
                    Screening Completed
                </h2>
                <p class="text-slate-400 text-sm max-w-md mx-auto mb-8 leading-relaxed">
                    Thank you, *{{ $applicant->full_name }}*. Your admission screening session has already been completed and graded. Please close this browser window and contact the admission office.
                </p>

                <div class="bg-slate-950/40 border border-white/5 rounded-2xl p-6 w-full text-left space-y-4">
                    <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                        <span class="text-slate-400">Applicant Name</span>
                        <span class="font-bold text-white">{{ $applicant->full_name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                        <span class="text-slate-400">Applied Class</span>
                        <span class="font-bold text-white">{{ $applicant->schoolClass?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                        <span class="text-slate-400">Exam Grade Score</span>
                        <span class="font-bold {{ $resultStatus === 'Passed' || $resultStatus === 'Admitted' ? 'text-emerald-400' : 'text-rose-400' }}">{{ number_format($scorePercent) }}%</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Status</span>
                        <span class="font-bold {{ $resultStatus === 'Passed' || $resultStatus === 'Admitted' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $resultStatus }}</span>
                    </div>
                </div>
            </div>

        @elseif($isFinished)
            <!-- TEST FINISHED SUCCESS CARD -->
            <div class="text-center py-6 flex flex-col items-center">
                <div class="h-20 w-20 rounded-full flex items-center justify-center mb-6 {{ $resultStatus === 'Passed' ? 'bg-emerald-500/10 border border-emerald-500 text-emerald-500' : 'bg-rose-500/10 border border-rose-500 text-rose-500' }} animate-bounce">
                    @if($resultStatus === 'Passed')
                        <svg class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        </svg>
                    @else
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @endif
                </div>

                <h2 class="text-2xl font-extrabold mb-2 {{ $resultStatus === 'Passed' ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $resultStatus === 'Passed' ? 'Congratulations! You Passed!' : 'Exam Attempt Graded' }}
                </h2>
                <p class="text-slate-400 text-sm max-w-md mx-auto mb-8 leading-relaxed">
                    @if($resultStatus === 'Passed')
                        Your screening exam attempt has been scored. You successfully passed the screening benchmark! Please notify your supervisor to finalize your admission.
                    @else
                        Your screening exam attempt has been scored. Unfortunately, you did not meet the passing benchmark for JSS1 admission. Please contact the admission office.
                    @endif
                </p>

                <div class="bg-slate-950/40 border border-white/5 rounded-2xl p-6 w-full text-left space-y-4">
                    <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                        <span class="text-slate-400">Applicant Name</span>
                        <span class="font-bold text-white">{{ $applicant->full_name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                        <span class="text-slate-400">Applied Class</span>
                        <span class="font-bold text-white">{{ $applicant->schoolClass?->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm border-b border-white/5 pb-3">
                        <span class="text-slate-400">Exam Grade Score</span>
                        <span class="font-bold {{ $resultStatus === 'Passed' ? 'text-emerald-400' : 'text-rose-400' }}">{{ number_format($scorePercent) }}%</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">Status</span>
                        <span class="font-bold {{ $resultStatus === 'Passed' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $resultStatus }}</span>
                    </div>
                </div>
            </div>

        @else
            <!-- RUNNING EXAM SCREEN -->
            <div class="mb-6 bg-slate-950/20 border border-white/5 rounded-2xl p-5 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h3 class="font-bold text-sm text-white">Candidate Details</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Name: *{{ $applicant->full_name }}* | Class: *{{ $applicant->schoolClass?->name }}*</p>
                </div>
                <div class="flex items-center gap-2 bg-violet-600/10 border border-violet-500/20 px-4 py-2.5 rounded-xl self-start md:self-auto">
                    <svg class="h-4 w-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-xs font-bold text-violet-400 uppercase tracking-wider">Supervised Exam</span>
                </div>
            </div>

            @if(empty($questions))
                <div class="text-center py-12">
                    <p class="text-slate-400 text-sm font-semibold italic">There are currently no exam questions configured for your applied class level. Please inform your screening officer.</p>
                </div>
            @else
                <form wire:submit.prevent="submitTest" class="space-y-6">
                    <div class="space-y-6 max-h-[450px] overflow-y-auto pr-2">
                        @foreach($questions as $index => $q)
                            <div class="bg-slate-950/15 border border-white/5 rounded-2xl p-5 hover:border-white/10 transition duration-150">
                                <div class="flex gap-3">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-violet-600/20 text-xs font-bold text-violet-400 flex-shrink-0 mt-0.5">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-white text-sm leading-relaxed mb-4">{{ $q->question_text }}</h3>
                                        
                                        <!-- Options -->
                                        <div class="grid grid-cols-1 gap-3.5">
                                            
                                            <!-- Option A -->
                                            <label class="flex items-center gap-3 rounded-xl border border-white/5 bg-slate-950/20 hover:bg-slate-950/40 px-4 py-3 cursor-pointer transition duration-150">
                                                <input type="radio" wire:model="selectedAnswers.{{ $q->id }}" value="A" class="h-4.5 w-4.5 text-violet-600 border-white/10 focus:ring-violet-500 bg-transparent">
                                                <span class="text-xs font-bold text-slate-400">A.</span>
                                                <span class="text-xs font-semibold text-slate-200">{{ $q->option_a }}</span>
                                            </label>

                                            <!-- Option B -->
                                            <label class="flex items-center gap-3 rounded-xl border border-white/5 bg-slate-950/20 hover:bg-slate-950/40 px-4 py-3 cursor-pointer transition duration-150">
                                                <input type="radio" wire:model="selectedAnswers.{{ $q->id }}" value="B" class="h-4.5 w-4.5 text-violet-600 border-white/10 focus:ring-violet-500 bg-transparent">
                                                <span class="text-xs font-bold text-slate-400">B.</span>
                                                <span class="text-xs font-semibold text-slate-200">{{ $q->option_b }}</span>
                                            </label>

                                            <!-- Option C -->
                                            <label class="flex items-center gap-3 rounded-xl border border-white/5 bg-slate-950/20 hover:bg-slate-950/40 px-4 py-3 cursor-pointer transition duration-150">
                                                <input type="radio" wire:model="selectedAnswers.{{ $q->id }}" value="C" class="h-4.5 w-4.5 text-violet-600 border-white/10 focus:ring-violet-500 bg-transparent">
                                                <span class="text-xs font-bold text-slate-400">C.</span>
                                                <span class="text-xs font-semibold text-slate-200">{{ $q->option_c }}</span>
                                            </label>

                                            <!-- Option D -->
                                            <label class="flex items-center gap-3 rounded-xl border border-white/5 bg-slate-950/20 hover:bg-slate-950/40 px-4 py-3 cursor-pointer transition duration-150">
                                                <input type="radio" wire:model="selectedAnswers.{{ $q->id }}" value="D" class="h-4.5 w-4.5 text-violet-600 border-white/10 focus:ring-violet-500 bg-transparent">
                                                <span class="text-xs font-bold text-slate-400">D.</span>
                                                <span class="text-xs font-semibold text-slate-200">{{ $q->option_d }}</span>
                                            </label>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-violet-600 to-pink-600 hover:from-violet-700 hover:to-pink-700 text-white font-bold py-4 transition duration-200 shadow-lg hover:shadow-violet-500/20">
                        Finish &amp; Submit Screening Exam
                    </button>
                </form>
            @endif
        @endif

    </div>
</div>
