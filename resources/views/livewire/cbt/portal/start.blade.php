<div class="fixed inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/cbt.png') }}');">
    <div class="flex h-screen w-screen items-center justify-center p-6 bg-black/20">
        <div class="w-full max-w-sm rounded-2xl bg-white/80 backdrop-blur-xl p-6 shadow-2xl">
            <div class="mb-4 text-center">
                <h2 class="text-xl font-black text-gray-900">Start Your Exam</h2>
                @if($examCode)
                    <p class="mt-1 text-xs text-gray-500">Enter your admission number to begin</p>
                @else
                    <p class="mt-1 text-xs text-gray-600">Enter your credentials</p>
                @endif
            </div>

            <form wire:submit="start" class="space-y-3" x-data="{ showPin: false }">
                <input wire:model="examCode" type="hidden" />
                <input wire:model="surname" type="hidden" />

                @if($examCode)
                    <div class="rounded-xl bg-blue-50 px-4 py-2.5 text-center border-2 border-blue-200">
                        <div class="text-xs font-semibold uppercase tracking-wider text-blue-700">Exam Code</div>
                        <div class="mt-0.5 font-mono text-sm font-black text-blue-900">{{ $examCode }}</div>
                    </div>
                @else
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Exam Code</label>
                        <input wire:model.live="examCode" class="mt-1.5 w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-2.5 font-mono text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 uppercase" placeholder="CBT-XXXXXX" />
                        @error('examCode') <div class="mt-1.5 text-xs font-semibold text-rose-700">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div>
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ $this->exam?->exam_type === 'aptitude' ? 'Full Name / Candidate Name' : 'Admission Number' }}
                    </label>
                    <input wire:model="admissionNumber" class="mt-1.5 w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20" 
                           placeholder="{{ $this->exam?->exam_type === 'aptitude' ? 'e.g. John Doe' : 'e.g. STU20240001' }}" />
                    @error('admissionNumber') <div class="mt-1.5 text-xs font-semibold text-rose-700">{{ $message }}</div> @enderror
                </div>

                {{-- PIN: hidden by default, shown only if needed --}}
                <div x-show="showPin" x-cloak>
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">PIN</label>
                    <input wire:model="pin" type="password" class="mt-1.5 w-full rounded-xl border-2 border-gray-200 bg-white px-4 py-2.5 font-mono text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20" placeholder="Enter PIN" />
                    @error('pin') <div class="mt-1.5 text-xs font-semibold text-rose-700">{{ $message }}</div> @enderror
                </div>
                @error('surname') <div class="text-xs font-semibold text-rose-700">{{ $message }}</div> @enderror

                <button type="submit" class="w-full rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-black text-white shadow-lg transition-all hover:bg-amber-600 hover:shadow-xl hover:scale-[1.02]">
                    Start Exam
                </button>

                <button type="button" @click="showPin = !showPin" class="w-full text-center text-xs text-gray-400 hover:text-gray-600">
                    <span x-text="showPin ? 'Hide PIN field' : 'This exam requires a PIN'"></span>
                </button>
            </form>
        </div>
    </div>
</div>
