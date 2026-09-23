<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('more-features') }}" class="p-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Admission Letters Hub</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 text-xs font-black uppercase tracking-wider">
                        Official A4 Letterhead
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    Generate and print official admission letters with school seal, watermark, resumption date, and director signature.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button
                type="button"
                onclick="printBulkSelected()"
                @if(empty($selectedStudentIds)) disabled @endif
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#1B3A6B] to-[#254d8c] hover:from-[#152e55] hover:to-[#1B3A6B] text-white font-bold text-xs shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Bulk Print Letters ({{ count($selectedStudentIds) }})</span>
            </button>
        </div>
    </div>

    {{-- Configuration Accordion / Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs" x-data="{ expanded: false }">
        <div class="flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800">Letterhead & Admission Parameters</h3>
                    <p class="text-xs text-slate-500 font-medium">Session: <strong class="text-slate-700">{{ $academicSession }}</strong> • Resumption: <strong class="text-slate-700">{{ $resumptionDate }}</strong> • Signatory: <strong class="text-slate-700">{{ $signatoryName }}</strong></p>
                </div>
            </div>
            <button type="button" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                <span x-text="expanded ? 'Hide Settings' : 'Edit Parameters'">Edit Parameters</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <div x-show="expanded" x-collapse class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Academic Session</label>
                <input type="text" wire:model.live="academicSession" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#1B3A6B]" />
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Resumption Date</label>
                <input type="text" wire:model.live="resumptionDate" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#1B3A6B]" />
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Issue / Admission Date</label>
                <input type="text" wire:model.live="admissionDate" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#1B3A6B]" />
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Signatory & Title</label>
                <input type="text" wire:model.live="signatoryName" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#1B3A6B]" placeholder="Name" />
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-3 w-full sm:w-auto flex-1 flex-wrap">
            {{-- Search --}}
            <div class="relative flex-1 sm:w-64 min-w-[200px]">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search name or adm no..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#1B3A6B]"
                />
            </div>

            {{-- Class Filter --}}
            <select wire:model.live="classId" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none cursor-pointer">
                <option value="">All Classes</option>
                @foreach($this->classes as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>

            {{-- Section Filter --}}
            @if($classId && $this->sections->isNotEmpty())
                <select wire:model.live="sectionId" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none cursor-pointer">
                    <option value="">All Sections / Arms</option>
                    @foreach($this->sections as $sec)
                        <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="text-xs font-semibold text-slate-500 shrink-0">
            Showing {{ $this->students->total() }} student(s)
        </div>
    </div>

    {{-- Students Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 w-10">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="rounded border-slate-300 text-[#1B3A6B] focus:ring-[#1B3A6B] cursor-pointer"
                            />
                        </th>
                        <th class="py-3 px-4">Student</th>
                        <th class="py-3 px-4">Admission No</th>
                        <th class="py-3 px-4">Class & Arm</th>
                        <th class="py-3 px-4">Guardian Contact</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($this->students as $student)
                        @php
                            $classArm = $this->getStudentClassArm($student);
                            $isSelected = in_array($student->id, $selectedStudentIds);
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isSelected ? 'bg-blue-50/30' : '' }}">
                            <td class="py-3 px-4">
                                <input
                                    type="checkbox"
                                    value="{{ $student->id }}"
                                    wire:model.live="selectedStudentIds"
                                    class="rounded border-slate-300 text-[#1B3A6B] focus:ring-[#1B3A6B] cursor-pointer"
                                />
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                                        <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}" class="w-full h-full object-cover" />
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-900 text-sm">{{ $student->full_name }}</div>
                                        <div class="text-[11px] text-slate-400 capitalize">{{ $student->gender ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $student->admission_number }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 font-bold border border-emerald-200/60 text-[11px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $classArm }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-800 truncate max-w-[180px]">{{ $student->guardian_name ?: 'N/A' }}</div>
                                <div class="text-[11px] font-mono text-slate-400">{{ $student->guardian_phone ?: '—' }}</div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button
                                    type="button"
                                    wire:click="openPreview({{ $student->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-[#1B3A6B] font-bold text-xs hover:bg-[#1B3A6B] hover:text-white transition shadow-2xs border border-blue-200/50 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>Preview & Print</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-bold text-slate-600">No student records found</p>
                                <p class="text-xs text-slate-400 mt-0.5">Try adjusting your search query or class filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->students->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $this->students->links() }}
            </div>
        @endif
    </div>

    {{-- Single Student Preview Modal --}}
    @if($showPreviewModal && $this->previewStudent)
        @php
            $modalStudent = $this->previewStudent;
            $modalClassArm = $this->getStudentClassArm($modalStudent);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-3 overflow-y-auto animate-fade-in" x-data>
            <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[94vh]">
                
                {{-- Modal Header --}}
                <div class="p-4 sm:p-5 bg-slate-900 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#1B3A6B] to-[#254d8c] flex items-center justify-center text-white font-black text-sm border border-blue-400/30">
                            AIA
                        </div>
                        <div>
                            <h2 class="text-lg font-black tracking-tight">Official Admission Letter Preview</h2>
                            <p class="text-xs text-slate-400 font-semibold">{{ $modalStudent->full_name }} • {{ $modalStudent->admission_number }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            onclick="printCurrentModal()"
                            class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-[#0f7343] hover:from-emerald-500 hover:to-[#0b5c34] text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Print Letter (A4)</span>
                        </button>
                        <button
                            type="button"
                            wire:click="closePreview"
                            class="p-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Letter Preview Container (Scalable A4 Container matching aiacademy) --}}
                <div class="flex-1 overflow-y-auto p-4 sm:p-8 bg-slate-100 flex justify-center">
                    <div id="admissionLetterPrintContainer" class="bg-white shadow-xl rounded-sm w-full max-w-[210mm] min-h-[297mm] p-[12mm_15mm] relative flex flex-col justify-between text-slate-800" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        
                        {{-- Watermark --}}
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.06] z-0 overflow-hidden">
                            <img src="{{ $logoUrl }}" alt="Watermark" class="w-[440px] h-[440px] object-contain" />
                        </div>

                        {{-- Main Letter Content --}}
                        <div class="relative z-10 flex flex-col justify-between flex-1 h-full">
                            
                            {{-- Header --}}
                            <div>
                                <div class="flex items-center gap-5 mb-2.5">
                                    <div class="w-[125px] h-[125px] rounded-full overflow-hidden shrink-0">
                                        <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-contain" />
                                    </div>
                                    <div class="flex-1">
                                        <h1 class="text-[32px] sm:text-[35px] font-black text-[#1B3A6B] uppercase leading-[1.12] tracking-tight">
                                            {!! nl2br(e($schoolName)) !!}
                                        </h1>
                                        <div class="inline-block bg-[#D4851F] text-white px-5 py-1 text-xs font-bold italic rounded-sm mt-2 shadow-xs">
                                            Motto: {{ $schoolMotto }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact Row with SVGs --}}
                                <div class="text-[12px] text-slate-700 space-y-1 mt-2 font-medium">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-[#1B3A6B] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <span>{{ $schoolAddress }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-[#1B3A6B] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <span>{{ $schoolPhone }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-[#1B3A6B] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                        <span>{{ $schoolEmail }}</span>
                                    </div>
                                </div>

                                {{-- Dual Divider --}}
                                <div class="mt-2.5 mb-3">
                                    <div class="h-1 bg-[#1B3A6B] rounded-xs"></div>
                                    <div class="h-1 bg-[#D4851F] rounded-xs mt-0.5"></div>
                                </div>

                                {{-- Meta Info Row with Passport Box --}}
                                <div class="flex justify-between items-start mb-2.5">
                                    <div class="space-y-1 text-xs">
                                        <div><span class="text-slate-600 font-semibold">Student Name: </span><span class="font-black text-slate-900 border-b border-slate-400 pb-0.5 px-1 uppercase">{{ $modalStudent->full_name }}</span></div>
                                        <div><span class="text-slate-600 font-semibold">Admission Number: </span><span class="font-black text-slate-900 border-b border-slate-400 pb-0.5 px-1 uppercase">{{ $modalStudent->admission_number }}</span></div>
                                        <div><span class="text-slate-600 font-semibold">Class: </span><span class="font-black text-slate-900 border-b border-slate-400 pb-0.5 px-1 uppercase">{{ $modalClassArm }}</span></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-slate-700 mb-2">Date: <span class="font-semibold border-b border-slate-400 pb-0.5 px-1">{{ $admissionDate }}</span></div>
                                        <div class="w-24 h-28 border-2 border-slate-800 p-0.5 ml-auto flex items-center justify-center bg-slate-50">
                                            @if($modalStudent->passport_photo)
                                                <img src="{{ $modalStudent->passport_photo_url }}" alt="Passport" class="w-full h-full object-cover" />
                                            @else
                                                <div class="text-[9px] text-slate-400 text-center font-bold uppercase leading-tight">Passport<br>Photograph</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Subject --}}
                                <div class="text-center font-black text-base underline uppercase tracking-wide my-3 text-slate-900">
                                    SUBJECT: ADMISSION LETTER
                                </div>

                                {{-- Letter Body --}}
                                <div class="text-[12.5px] leading-relaxed text-justify text-slate-800 space-y-2">
                                    <p class="font-bold text-slate-900">Dear Parent/Guardian,</p>
                                    <p>We are pleased to inform you that your child has been offered admission into <strong>{{ $schoolName }}</strong> into <strong>{{ strtoupper($modalClassArm) }}</strong> for the <strong>{{ $academicSession }}</strong> Academic Session.</p>
                                    <p>The admission is offered based on the assessment and admission requirements of the school. We are delighted to welcome your child into our learning community and look forward to supporting his/her academic, moral, and personal development.</p>
                                    <p>Please complete the registration process and settle the applicable school fees and other required charges on or before the stated deadline. Admission is subject to compliance with the school's rules, regulations, and code of conduct.</p>
                                    <p>We kindly request that the parent/guardian report to the school for final registration and submission of the required documents.</p>
                                    <p>We congratulate you and your child on this opportunity and look forward to a successful and rewarding academic journey together.</p>
                                </div>

                                {{-- Summary Table --}}
                                <table class="w-full border-collapse text-xs my-3 border border-slate-400">
                                    <tbody>
                                        <tr>
                                            <td class="p-2 border border-slate-400 font-bold bg-slate-100 text-slate-700 w-2/5">Student Name</td>
                                            <td class="p-2 border border-slate-400 font-black text-slate-900 uppercase">{{ $modalStudent->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="p-2 border border-slate-400 font-bold bg-slate-100 text-slate-700">Class / Level / Arm</td>
                                            <td class="p-2 border border-slate-400 font-black text-slate-900 uppercase">{{ $modalClassArm }}</td>
                                        </tr>
                                        <tr>
                                            <td class="p-2 border border-slate-400 font-bold bg-slate-100 text-slate-700">Academic Session</td>
                                            <td class="p-2 border border-slate-400 font-black text-slate-900 uppercase">{{ $academicSession }}</td>
                                        </tr>
                                        <tr>
                                            <td class="p-2 border border-slate-400 font-bold bg-slate-100 text-slate-700">Resumption Date</td>
                                            <td class="p-2 border border-slate-400 font-black text-slate-900 uppercase">{{ $resumptionDate }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Sign-off --}}
                            <div class="mt-4 text-xs">
                                <p class="font-semibold text-slate-700 mb-7">Yours faithfully,</p>
                                <div class="w-48 border-b-2 border-slate-800 mb-1"></div>
                                <p class="font-black text-sm text-slate-900">{{ $signatoryName }}</p>
                                <p class="font-semibold text-slate-600 text-[11px]">{{ $signatoryTitle }}</p>
                                <p class="font-bold text-slate-800 text-[11px]">{{ $schoolName }}</p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- Standalone Printing Script matching aiacademy printBulkAdmissionLetters exactly --}}
    <script>
        function printCurrentModal() {
            const letterContent = document.getElementById('admissionLetterPrintContainer');
            if (!letterContent) return;

            const printWindow = window.open('', '_blank', 'width=900,height=1100');
            if (!printWindow) {
                alert('Please allow popups to print official admission letters.');
                return;
            }

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Admission Letter - {{ $this->previewStudent?->admission_number ?? '' }}</title>
                    <style>
                        @page { size: A4 portrait; margin: 0; }
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            color: #1a1a1a;
                            background: white;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .page {
                            width: 100%;
                            max-width: 210mm;
                            min-height: 297mm;
                            margin: 0 auto;
                            padding: 12mm 15mm;
                            position: relative;
                            display: flex;
                            flex-direction: column;
                            justify-content: space-between;
                        }
                    </style>
                </head>
                <body>
                    <div class="page">
                        ${letterContent.innerHTML}
                    </div>
                    <script>
                        window.onload = function() {
                            setTimeout(function() { window.print(); }, 400);
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

        function printBulkSelected() {
            @this.call('allFilteredStudentIds').then(ids => {
                const selected = @this.get('selectedStudentIds');
                const targetIds = selected.length > 0 ? selected : ids;
                if (!targetIds || targetIds.length === 0) {
                    alert('No students selected to print admission letters.');
                    return;
                }
                const url = '/print/admission-letters?ids=' + targetIds.join(',') + 
                    '&session=' + encodeURIComponent(@this.get('academicSession')) +
                    '&resumption=' + encodeURIComponent(@this.get('resumptionDate')) +
                    '&date=' + encodeURIComponent(@this.get('admissionDate')) +
                    '&signatory=' + encodeURIComponent(@this.get('signatoryName')) +
                    '&title=' + encodeURIComponent(@this.get('signatoryTitle'));
                window.open(url, '_blank', 'width=950,height=1100');
            });
        }
    </script>
</div>
