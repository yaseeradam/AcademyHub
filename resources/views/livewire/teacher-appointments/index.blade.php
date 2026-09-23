<div class="space-y-6">
    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('more-features') }}" class="p-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Teacher Appointment Letter Generator</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-700 border border-blue-500/20 text-xs font-black uppercase tracking-wider">
                        Official Staff Appointment
                    </span>
                </div>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">
                    Generate, configure, and print official provisional employment offers for teachers with custom salary, name, staff ID, and dates.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button
                type="button"
                wire:click="resetToDefaultTemplate"
                class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer"
            >
                Reset to Default
            </button>
            <button
                type="button"
                onclick="printAppointmentLetter()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#1B3A6B] to-[#254d8c] hover:from-[#152e55] hover:to-[#1B3A6B] text-white font-black text-xs shadow-md transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Print Official Letter (A4)</span>
            </button>
        </div>
    </div>

    {{-- Main Studio: Left Configurator & Right Live A4 Preview --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- LEFT COLUMN: Configurator Panel (4 Cols on LG) --}}
        <div class="lg:col-span-4 space-y-5 print:hidden">
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-xs space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#1B3A6B] flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Appointment Parameters</h3>
                        <p class="text-[11px] text-slate-500 font-semibold">Live updates reflected on letter</p>
                    </div>
                </div>

                {{-- Quick Load from Existing Staff --}}
                @if($this->teachers->isNotEmpty())
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider">
                            Load Registered Teacher (Optional)
                        </label>
                        <select
                            wire:model.live="selectedTeacherId"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#1B3A6B] cursor-pointer"
                        >
                            <option value="">-- Choose registered staff or enter custom --</option>
                            @foreach($this->teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Teacher Name --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider">
                        Teacher Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model.live="teacherName"
                        placeholder="e.g. Abdulmalik Muhammad"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-[#1B3A6B]"
                    />
                </div>

                {{-- Staff ID / Adm No --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider">
                        Staff ID / Adm No <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model.live="staffId"
                        placeholder="e.g. AIA/26/N005"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-[#1B3A6B]"
                    />
                </div>

                {{-- Salary Amount & Words --}}
                <div class="space-y-3 p-4 bg-emerald-50/50 rounded-2xl border border-emerald-200/60">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black uppercase text-emerald-800 tracking-wider">
                            Monthly Salary (₦ Figures) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-black text-xs text-emerald-700">₦</span>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="salaryAmount"
                                placeholder="50,000"
                                class="w-full pl-8 pr-3.5 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs font-black text-slate-900 focus:outline-none focus:border-emerald-600"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black uppercase text-emerald-800 tracking-wider">
                            Salary in Words
                        </label>
                        <input
                            type="text"
                            wire:model.live="salaryWords"
                            placeholder="Fifty Thousand Naira"
                            class="w-full px-3.5 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-600"
                        />
                        <span class="text-[10px] text-emerald-700 font-semibold block">Auto-converts from figures, editable anytime.</span>
                    </div>
                </div>

                {{-- Appointment Date --}}
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider">
                        Date / Commencement Date <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model.live="appointmentDate"
                        placeholder="e.g. 1st September, 2026"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-[#1B3A6B]"
                    />
                </div>

                {{-- Role / Position --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider">
                            Position / Role
                        </label>
                        <input
                            type="text"
                            wire:model.live="position"
                            placeholder="Teacher"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-[#1B3A6B]"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black uppercase text-slate-500 tracking-wider">
                            Type
                        </label>
                        <select
                            wire:model.live="employmentType"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-[#1B3A6B] cursor-pointer"
                        >
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                        </select>
                    </div>
                </div>

                {{-- Additional Terms Collapsible --}}
                <div x-data="{ open: false }" class="pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between text-xs font-bold text-slate-600 hover:text-slate-900 py-1"
                    >
                        <span>Terms & Conditions Details</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="mt-3 space-y-3">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500">Probation Period</label>
                            <input type="text" wire:model.live="probationPeriod" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500">Notice Period</label>
                            <input type="text" wire:model.live="noticePeriod" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500">Signatory Name</label>
                            <input type="text" wire:model.live="signatoryName" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500">Signatory Title</label>
                            <input type="text" wire:model.live="signatoryTitle" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Official Document Preview (8 Cols on LG) --}}
        <div class="lg:col-span-8 flex justify-center overflow-x-auto p-2 sm:p-4 bg-slate-200/60 rounded-3xl border border-slate-300/60">
            <div
                id="appointmentLetterPrintArea"
                class="bg-white shadow-2xl rounded-sm w-full max-w-[210mm] min-h-[297mm] p-[12mm_16mm] relative flex flex-col justify-between text-slate-800"
                style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"
            >
                {{-- Watermark Logo in Center --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.06] z-0 overflow-hidden">
                    <img src="{{ $logoUrl }}" alt="Watermark" class="w-[450px] h-[450px] object-contain" />
                </div>

                {{-- Letter Content --}}
                <div class="relative z-10 flex flex-col justify-between flex-1 h-full">
                    <div>
                        {{-- Header Banner matching uploaded PDF --}}
                        <div class="flex items-center gap-5 mb-2.5">
                            <div class="w-[125px] h-[125px] rounded-full overflow-hidden shrink-0">
                                <img src="{{ $logoUrl }}" alt="Logo" class="w-full h-full object-contain" />
                            </div>
                            <div class="flex-1">
                                <h1 class="text-[32px] sm:text-[36px] font-black text-[#1B3A6B] uppercase leading-[1.12] tracking-tight">
                                    {!! nl2br(e($schoolName)) !!}
                                </h1>
                                <div class="inline-block bg-[#D4851F] text-white px-5 py-1 text-xs font-bold italic rounded-sm mt-2 shadow-xs">
                                    Motto: {{ $schoolMotto }}
                                </div>
                            </div>
                        </div>

                        {{-- Contact Details with SVG Icons --}}
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

                        {{-- Dual Divider Bars --}}
                        <div class="mt-2.5 mb-3">
                            <div class="h-1 bg-[#1B3A6B] rounded-xs"></div>
                            <div class="h-1 bg-[#D4851F] rounded-xs mt-0.5"></div>
                        </div>

                        {{-- Recipient & Date Row --}}
                        <div class="flex justify-between items-start text-xs leading-relaxed mb-3">
                            <div>
                                <span class="font-bold text-slate-700 block">To:</span>
                                <span class="font-black text-sm text-slate-900 block">{{ $teacherName }}</span>
                                <span class="font-bold text-slate-700 block">Staff ID: <strong class="font-mono text-slate-900">{{ $staffId }}</strong></span>
                                <span class="font-bold text-slate-800 block mt-1">Dear Sir/Ma,</span>
                            </div>
                            <div class="text-right">
                                <span class="font-black text-xs text-slate-900 border-b border-slate-300 pb-0.5">{{ $appointmentDate }}</span>
                            </div>
                        </div>

                        {{-- Document Title --}}
                        <div class="text-center my-3">
                            <h2 class="text-sm font-black text-slate-950 uppercase tracking-wide underline underline-offset-4">
                                OFFER OF PROVISIONAL APPOINTMENT
                            </h2>
                        </div>

                        {{-- Body Clauses matching exact text in uploaded document --}}
                        <div class="text-[12px] leading-relaxed text-justify text-slate-800 space-y-2.5">
                            <p>
                                With reference to your application for employment and subsequent interaction and interview, we are pleased to offer you a <strong>{{ $employmentType }}</strong> position as <strong>{{ $position }}</strong> in our ever-progressive school.
                            </p>
                            <p>
                                Your employment is based on our confidence in your competence, dedication, and commitment to effective teaching, moral upbringing of learners, and the overall progress of the school.
                            </p>
                            <p class="font-semibold text-slate-900">
                                The terms and conditions of your appointment are as follows:
                            </p>

                            <div class="space-y-1.5 pl-1">
                                <p>
                                    <strong>Commencement Date:</strong> Your appointment takes effect from {{ $appointmentDate }}.
                                </p>
                                <p>
                                    <strong>Probationary Period:</strong> You will serve a {{ $probationPeriod }} probationary period starting from your date of resumption. Within this period, there will be monthly performance appraisals over defined agreed tasks.
                                </p>
                                <p>
                                    <strong>Confirmation:</strong> Confirmation of this offer is subject to the satisfactory completion of your {{ $probationPeriod }} probationary period.
                                </p>

                                <div>
                                    <strong class="block mb-1">Core Responsibilities:</strong>
                                    <ul class="list-disc pl-5 space-y-0.5">
                                        <li>Planning and delivering lessons effectively.</li>
                                        <li>Maintaining a safe, nurturing, and productive learning environment.</li>
                                        <li>Supporting the holistic development and moral upbringing of the learners.</li>
                                        <li>Assessing and recording learners' progress and providing feedback.</li>
                                        <li>Participating in school activities as directed by the administration.</li>
                                    </ul>
                                </div>

                                <p>
                                    <strong>Salary:</strong> Your role shall be indemnified with a monthly salary of ({{ $salaryWords }}) (₦{{ $salaryAmount }}).
                                </p>
                                <p>
                                    <strong>Holiday & Leave:</strong> You will be entitled to a {{ $holidayLeave }}
                                </p>
                                <p>
                                    <strong>Maternity Leave:</strong> {{ $maternityLeave }}
                                </p>
                                <p>
                                    <strong>Termination:</strong> Either you or the school can end this appointment by giving {{ $noticePeriod }} written notice. If notice is not given, {{ $noticePeriod }} salary will be paid.
                                </p>
                            </div>

                            <p class="pt-1">
                                Kindly indicate your acceptance of this offer by endorsing and returning the attached copy of this letter. Please also submit one recent passport photograph for your file along with the signed last page of the <strong>CODE OF CONDUCT POLICY FOR EMPLOYEES</strong>.
                            </p>
                            <p class="font-semibold text-slate-900">
                                Do accept our warm congratulations.
                            </p>
                        </div>
                    </div>

                    {{-- Sign-off & Bottom Accent Bar --}}
                    <div>
                        <div class="mt-4 text-xs">
                            <p class="font-semibold text-slate-700 mb-6">Yours Faithfully,</p>
                            <p class="font-black text-sm text-slate-950">{{ $signatoryName }}</p>
                            <p class="font-semibold text-slate-600 text-[11px]">{{ $signatoryTitle }}</p>
                        </div>

                        {{-- Bottom Accent Stripe --}}
                        <div class="mt-6 flex items-center gap-1.5 justify-end">
                            <div class="w-16 h-1 bg-[#1B3A6B] rounded-xs"></div>
                            <div class="w-8 h-1 bg-[#D4851F] rounded-xs"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Standalone Printing Script --}}
    <script>
        function printAppointmentLetter() {
            const letterContent = document.getElementById('appointmentLetterPrintArea');
            if (!letterContent) return;

            const printWindow = window.open('', '_blank', 'width=900,height=1100');
            if (!printWindow) {
                alert('Please allow popups to print official appointment letters.');
                return;
            }

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Appointment Letter - {{ $this->teacherName }}</title>
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
                            padding: 12mm 16mm;
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
    </script>
</div>
