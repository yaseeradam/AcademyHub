<div class="space-y-6">
    {{-- Top Header / Mode Switcher --}}
    <div class="bg-gradient-to-r from-slate-900 to-slate-950 p-5 sm:p-6 rounded-3xl border border-slate-800 shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('more-features') }}" class="p-2.5 rounded-2xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition shadow-xs shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0f7343] to-emerald-400 text-white flex items-center justify-center font-black shadow-[0_8px_20px_rgba(15,115,67,0.4)] border border-emerald-300/30 shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Parent Pickup ID Cards</h1>
                    <span class="px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-black uppercase tracking-wider">
                        Offline QR Security
                    </span>
                </div>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">
                    Official CR80 security badges for parents to pick up students. Scannable offline without internet.
                </p>
            </div>
        </div>

        {{-- Mode Switcher Buttons --}}
        <div class="bg-slate-950 p-1.5 rounded-2xl border border-slate-800 flex items-center gap-1 shadow-inner self-stretch sm:self-auto">
            <button
                type="button"
                wire:click="$set('activeTab', 'preview')"
                class="px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'preview' ? 'bg-gradient-to-r from-[#0f7343] to-emerald-600 text-white shadow-[0_4px_12px_rgba(15,115,67,0.4)]' : 'text-slate-400 hover:text-white' }}"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Batch Print Cards ({{ $this->students->count() }})</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'simulator')"
                class="px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer flex items-center gap-2 {{ $activeTab === 'simulator' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 shadow-[0_4px_12px_rgba(245,158,11,0.4)]' : 'text-slate-400 hover:text-white' }}"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Gate Scanner Simulator</span>
            </button>
        </div>
    </div>

    {{-- TAB 1: BATCH ID CARD PREVIEW & PRINT --}}
    @if($activeTab === 'preview')
        <div class="space-y-6">
            {{-- Filter Controls Bar --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs print:hidden">
                <div class="flex items-center gap-3 w-full sm:w-auto flex-1 flex-wrap">
                    <div class="relative flex-1 sm:w-72 min-w-[200px]">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search student name or adm no..."
                            class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500"
                        />
                    </div>

                    <select
                        wire:model.live="selectedClass"
                        class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 font-bold focus:outline-none cursor-pointer"
                    >
                        <option value="all">All Classes ({{ \App\Models\Student::count() }})</option>
                        @foreach($this->classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <span class="text-xs font-bold text-slate-500">
                        Showing {{ $this->students->count() }} card(s)
                    </span>

                    <button
                        type="button"
                        onclick="window.print()"
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-[#0f7343] hover:from-emerald-500 hover:to-[#0b5c34] text-white font-black text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center gap-2 border border-emerald-400/30"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Print All ID Cards (A4 PDF)</span>
                    </button>
                </div>
            </div>

            {{-- Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto print:grid-cols-2 print:gap-4 print:max-w-none print:m-0">
                @forelse($this->students as $student)
                    @php
                        $classArm = $this->getStudentClassArm($student);
                        $qrSvgDataUri = $this->generateQrCodeSvg($student);
                    @endphp
                    <div 
                        class="id-card-wrapper relative bg-white rounded-2xl overflow-hidden flex flex-col justify-between text-slate-900 group transition-all shadow-[0_8px_32px_rgba(15,115,67,0.18)] border border-emerald-200 hover:shadow-[0_12px_40px_rgba(15,115,67,0.28)] hover:-translate-y-0.5 print:border-2 print:border-slate-800 print:shadow-none print:rounded-2xl print:break-inside-avoid"
                        style="min-height: 280px;"
                    >
                        {{-- Header Emerald Banner with diagonal stripe overlay --}}
                        <div class="relative bg-gradient-to-r from-[#0a5c33] via-[#0f7343] to-[#1a9456] text-white px-4 py-3 border-b-4 border-amber-400 flex items-center justify-between gap-3 overflow-hidden">
                            <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #fff 0, #fff 1px, transparent 0, transparent 50%); background-size: 10px 10px;"></div>
                            
                            <div class="flex items-center gap-3 min-w-0 relative z-10">
                                <img src="{{ $logoUrl }}" alt="Logo" class="w-11 h-11 rounded-full object-contain bg-white p-0.5 border-2 border-amber-300 shadow-md shrink-0" />
                                <div class="min-w-0">
                                    <h4 class="text-[12px] font-black uppercase tracking-wider text-white leading-none truncate drop-shadow">
                                        {{ $schoolName }}
                                    </h4>
                                    <span class="text-[8px] font-extrabold text-amber-300 uppercase tracking-widest block mt-1">
                                        {{ $subTitle }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right shrink-0 relative z-10">
                                <span class="text-[7px] font-extrabold text-emerald-200 block uppercase tracking-wider mb-0.5">ADM NO</span>
                                <span class="text-[10px] font-mono font-black text-amber-300 bg-black/40 px-2 py-0.5 rounded-md border border-amber-400/50 block shadow-inner">
                                    {{ $student->admission_number }}
                                </span>
                            </div>
                        </div>

                        {{-- Main Card Body --}}
                        <div class="flex-1 flex items-stretch gap-0 bg-white">
                            {{-- Left accent bar --}}
                            <div class="w-1.5 bg-gradient-to-b from-[#0f7343] via-amber-400 to-[#0f7343] shrink-0"></div>

                            <div class="p-3 flex-1 flex items-center gap-3">
                                {{-- Column 1: Passport Photo --}}
                                <div class="shrink-0 flex flex-col items-center gap-1">
                                    <div class="w-[72px] h-[86px] rounded-xl overflow-hidden bg-gradient-to-br from-emerald-50 to-emerald-100 border-2 border-[#0f7343] shadow-[0_2px_8px_rgba(15,115,67,0.25)] relative">
                                        <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover" />
                                    </div>
                                    <span class="text-[6.5px] font-black uppercase tracking-wider text-slate-400">Passport</span>
                                </div>

                                {{-- Column 2: Student Details --}}
                                <div class="flex-1 min-w-0 space-y-1.5">
                                    <div>
                                        <h3 class="font-black text-[13px] text-slate-900 tracking-tight leading-tight truncate">
                                            {{ $student->full_name }}
                                        </h3>
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-300 text-[#0f7343] text-[9px] font-black tracking-wide mt-0.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                            {{ $classArm }}
                                        </div>
                                    </div>

                                    <div class="text-[9px] text-slate-600 space-y-1 pt-1.5 border-t border-dashed border-slate-200">
                                        <div class="flex items-baseline gap-1 truncate">
                                            <span class="text-slate-400 font-bold shrink-0">Gender/DoB</span>
                                            <span class="font-black text-slate-800 truncate">{{ $student->gender ?? 'N/A' }}{{ $student->dob ? ' • ' . $student->dob->format('Y-m-d') : '' }}</span>
                                        </div>
                                        <div class="flex items-baseline gap-1 truncate">
                                            <span class="text-slate-400 font-bold shrink-0">Parent</span>
                                            <span class="font-black text-slate-800 truncate">{{ $student->guardian_name ?: 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-baseline gap-1 truncate">
                                            <span class="text-slate-400 font-bold shrink-0">Phone</span>
                                            <span class="font-mono font-black text-[#0f7343] truncate">{{ $student->guardian_phone ?: 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Column 3: QR Code --}}
                                <div class="shrink-0 flex flex-col items-center gap-1">
                                    <div class="bg-white p-1 rounded-lg border-2 border-emerald-200 shadow-[0_2px_8px_rgba(15,115,67,0.15)]">
                                        <img src="{{ $qrSvgDataUri }}" alt="QR" class="w-16 h-16" />
                                    </div>
                                    <span class="text-[6.5px] font-black uppercase tracking-wider text-[#0f7343]">Scan QR</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Bottom Footer --}}
                        <div class="bg-gradient-to-r from-[#0a5c33] via-[#0f7343] to-[#1a9456] px-4 py-1.5 flex items-center justify-between">
                            <span class="text-[8px] font-black text-amber-300 uppercase tracking-wider">{{ $schoolName }}</span>
                            <span class="text-[7px] font-bold text-emerald-200 uppercase tracking-wide">Official ID Badge</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200">
                        <p class="font-bold text-slate-600">No students found matching your criteria</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- TAB 2: SOFT 3D GATE SCANNER SIMULATOR --}}
    @if($activeTab === 'simulator')
        <div class="bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-2xl">
            <div class="max-w-4xl mx-auto space-y-8">
                
                {{-- Banner --}}
                <div class="bg-gradient-to-r from-amber-500/20 via-emerald-500/20 to-teal-500/20 rounded-3xl p-6 border border-emerald-500/30 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="space-y-2 text-center sm:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-xs font-black">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Interactive Mobile Scanner Simulation</span>
                        </div>
                        <h3 class="text-2xl font-black text-white tracking-tight">Offline Gate Security Scanner</h3>
                        <p class="text-xs text-slate-300 font-medium max-w-xl">
                            Test how school security guards scan the parent pickup card at closing time. The scanner extracts student photo, class arm, and authorized parent phone numbers directly from the QR code without internet connection.
                        </p>
                    </div>

                    {{-- Quick Student Selector --}}
                    <div class="w-full sm:w-auto bg-slate-900/90 p-4 rounded-2xl border border-slate-800 shadow-inner space-y-2">
                        <label class="text-[11px] font-black uppercase text-emerald-400 block tracking-wider">
                            Select Card to Simulate Scan:
                        </label>
                        <select
                            wire:model.live="simulatedStudentId"
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-xs font-bold text-white focus:outline-none focus:border-amber-400 cursor-pointer"
                        >
                            @foreach($this->students as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->full_name }} ({{ $this->getStudentClassArm($s) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Phone Layout Mockup --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    
                    {{-- Left: Phone Mockup --}}
                    <div class="bg-slate-900 rounded-[3rem] p-5 border-4 border-slate-800 shadow-2xl relative max-w-sm mx-auto w-full space-y-4">
                        <div class="w-24 h-4 bg-slate-950 rounded-full mx-auto shadow-inner mb-2"></div>

                        <div class="bg-slate-950 rounded-[2rem] p-4 border border-slate-800 space-y-4 overflow-hidden shadow-inner">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-[#0f7343] to-emerald-400 flex items-center justify-center text-white font-black text-xs">
                                        AI
                                    </div>
                                    <span class="text-xs font-black text-white">AI Academy Pickup</span>
                                </div>
                                <span class="text-[10px] font-black text-emerald-400 bg-emerald-950 px-2 py-0.5 rounded-md border border-emerald-500/30">
                                    OFFLINE
                                </span>
                            </div>

                            {{-- Scanner Viewfinder Simulator --}}
                            <div class="relative h-44 rounded-2xl overflow-hidden bg-slate-900 border-2 border-emerald-500/40 flex flex-col items-center justify-center p-3 text-center space-y-2">
                                <div class="absolute inset-4 border-2 border-dashed border-emerald-400/60 rounded-xl animate-pulse"></div>
                                <svg class="w-8 h-8 text-emerald-400 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-[10px] font-bold text-slate-300 relative z-10">
                                    Point camera at Parent Pickup QR Code
                                </p>
                            </div>

                            {{-- Verification Result --}}
                            @if($this->simulatedStudent)
                                @php
                                    $simStudent = $this->simulatedStudent;
                                    $simArm = $this->getStudentClassArm($simStudent);
                                @endphp
                                <div class="bg-gradient-to-b from-slate-900 to-slate-950 rounded-2xl p-4 border border-emerald-500/40 shadow-xl space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/30">
                                            AUTHENTICATED
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">Just Now</span>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-14 rounded-xl overflow-hidden bg-slate-800 border border-emerald-400/40 shrink-0">
                                            <img src="{{ $simStudent->passport_photo_url }}" alt="{{ $simStudent->full_name }}" class="w-full h-full object-cover" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-black text-sm text-white truncate">
                                                {{ $simStudent->full_name }}
                                            </h4>
                                            <span class="text-[11px] font-black text-emerald-400 block">
                                                {{ $simArm }}
                                            </span>
                                            <span class="text-[10px] font-mono text-slate-400 block">
                                                Adm No: {{ $simStudent->admission_number }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="bg-slate-950/80 p-2.5 rounded-xl border border-slate-800 text-[11px] space-y-1">
                                        <p class="text-slate-300 truncate">
                                            <strong class="text-slate-400">Parent:</strong> {{ $simStudent->guardian_name ?: 'N/A' }}
                                        </p>
                                        <p class="text-slate-300 flex items-center justify-between">
                                            <span><strong>Phone:</strong> {{ $simStudent->guardian_phone ?: 'N/A' }}</span>
                                            @if($simStudent->guardian_phone)
                                                <a href="tel:{{ $simStudent->guardian_phone }}" class="text-emerald-400 font-bold underline flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    Call
                                                </a>
                                            @endif
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="approvePickup({{ $simStudent->id }})"
                                        class="w-full py-3 px-4 rounded-xl font-black text-xs flex items-center justify-center gap-2 shadow-[0_8px_20px_rgba(15,115,67,0.4)] transition-all cursor-pointer border {{ $justApproved ? 'bg-emerald-500 text-white border-emerald-300' : 'bg-gradient-to-r from-emerald-600 via-[#0f7343] to-emerald-700 hover:from-emerald-500 hover:to-[#0b5c34] text-white border-emerald-400/40' }}"
                                    >
                                        <svg class="w-4 h-4 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ $justApproved ? 'RELEASE APPROVED ✓' : 'APPROVE & RELEASE STUDENT' }}</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Scan Audit History --}}
                    <div class="space-y-5">
                        <div class="bg-slate-900/90 rounded-3xl p-6 border border-slate-800 shadow-xl space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-black text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Today's Gate Pickup Logs</span>
                                </h4>
                                <span class="text-xs font-mono font-bold bg-slate-800 px-3 py-1 rounded-full text-slate-300">
                                    {{ count($pickupLogs) }} Release(s)
                                </span>
                            </div>

                            @if(empty($pickupLogs))
                                <div class="py-8 text-center bg-slate-950/60 rounded-2xl border border-slate-800/80 space-y-2">
                                    <svg class="w-8 h-8 text-slate-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    <p class="text-xs font-bold text-slate-400">No pickup logs recorded yet today.</p>
                                    <p class="text-[11px] text-slate-500">Tap "APPROVE & RELEASE STUDENT" on the mobile mockup to test gate logging.</p>
                                </div>
                            @else
                                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                    @foreach($pickupLogs as $log)
                                        <div class="p-3 bg-slate-950 rounded-2xl border border-slate-800 flex items-center justify-between text-xs">
                                            <div>
                                                <h5 class="font-black text-white">{{ $log['name'] }}</h5>
                                                <span class="text-[10px] text-emerald-400 font-bold">{{ $log['status'] }}</span>
                                            </div>
                                            <span class="font-mono text-slate-400 font-semibold">{{ $log['time'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- Print CSS for Batch Printing CR80 Badges --}}
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .id-card-wrapper, .id-card-wrapper * {
                visibility: visible;
            }
            .id-card-wrapper {
                position: relative !important;
                display: flex !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-bottom: 12px !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</div>
