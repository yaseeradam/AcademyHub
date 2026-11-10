@extends('layouts.app')

@section('content')
    @php
        $hasPremium = true;

        $certificateTemplate = old('certificate_template', config('academyhub.certificate_template', 'modern'));
        $reportCardTemplate = old('report_card_template', config('academyhub.report_card_template', 'compact'));

        // Report card options
        $rcShowPosition = old('rc_show_position', config('academyhub.rc_show_position', true));
        $rcShowAttendance = old('rc_show_attendance', config('academyhub.rc_show_attendance', true));
        $rcShowGradingKey = old('rc_show_grading_key', config('academyhub.rc_show_grading_key', true));
        $rcShowClassAverage = old('rc_show_class_average', config('academyhub.rc_show_class_average', true));
        $rcShowWatermark = old('rc_show_watermark', config('academyhub.rc_show_watermark', true));
        $rcShowNextTermDate = old('rc_show_next_term_date', config('academyhub.rc_show_next_term_date', true));
        $rcShowTeacherRemarks = old('rc_show_teacher_remarks', config('academyhub.rc_show_teacher_remarks', true));
        $rcShowPrincipalRemarks = old('rc_show_principal_remarks', config('academyhub.rc_show_principal_remarks', true));
        $rcShowPsychomotor = old('rc_show_psychomotor', config('academyhub.rc_show_psychomotor', false));
        $rcPsychomotorStyle = old('rc_psychomotor_style', config('academyhub.rc_psychomotor_style', 'grid'));
        $rcPsychomotorScale = old('rc_psychomotor_scale', config('academyhub.rc_psychomotor_scale', '5_point'));
        $psychomotorDefaults = \App\Support\PsychomotorService::defaultProfiles();
        $savedProfiles = $savedProfiles ?? \App\Support\TenantSettings::get('rc_psychomotor_profiles', $psychomotorDefaults);
        if (is_string($savedProfiles)) {
            $savedProfiles = json_decode($savedProfiles, true) ?: $psychomotorDefaults;
        }
        $rcShowSchoolFees = old('rc_show_school_fees', config('academyhub.rc_show_school_fees', false));
        
        $rcShowClassHighestLowest = old('rc_show_class_highest_lowest', config('academyhub.rc_show_class_highest_lowest', false));
        $rcShowSubjectTeacherRemarks = old('rc_show_subject_teacher_remarks', config('academyhub.rc_show_subject_teacher_remarks', false));
        $rcShowQrCode = old('rc_show_qr_code', config('academyhub.rc_show_qr_code', true));
        $rcShowCumulativeSummary = old('rc_show_cumulative_summary', config('academyhub.rc_show_cumulative_summary', false));
        $rcShowColorBadges = old('rc_show_color_badges', config('academyhub.rc_show_color_badges', true));
        $rcShowPerformance = old('rc_show_performance', config('academyhub.rc_show_performance', false));

        $rcSchoolFeesAccountNumber = old('rc_school_fees_account_number', config('academyhub.rc_school_fees_account_number'));
        $rcSchoolFeesBankName = old('rc_school_fees_bank_name', config('academyhub.rc_school_fees_bank_name'));
        $rcSchoolFeesAccountName = old('rc_school_fees_account_name', config('academyhub.rc_school_fees_account_name'));
        $rcSchoolFeesByClass = old('rc_school_fees_by_class', config('academyhub.rc_school_fees_by_class', []));
        if (is_string($rcSchoolFeesByClass)) {
            $rcSchoolFeesByClass = json_decode($rcSchoolFeesByClass, true) ?? [];
        }
        $rcBlockDebtors = old('rc_block_debtors', config('academyhub.rc_block_debtors', true));
        $rcNextTermDateVal = old('rc_next_term_date', config('academyhub.rc_next_term_date', ''));
        $rcShowSignatures = old('rc_show_signatures', config('academyhub.rc_show_signatures', false));
        $rcPrincipalSignatureImage = config('academyhub.rc_principal_signature_image');
        $rcTeacherSignatureImage = config('academyhub.rc_teacher_signature_image');
        $rcPrincipalName = old('rc_principal_name', config('academyhub.rc_principal_name', ''));
        $rcPrincipalTitle = old('rc_principal_title', config('academyhub.rc_principal_title', 'Principal'));
    @endphp

    <div class="space-y-6"
        x-data="{
            open: false,
            src: null,
            title: '',
            selectedReportTemplate: '{{ $reportCardTemplate }}',
            showSchoolFees: {{ $rcShowSchoolFees ? 'true' : 'false' }},
            showSignatures: {{ $rcShowSignatures ? 'true' : 'false' }},
            showPsychomotor: {{ $rcShowPsychomotor ? 'true' : 'false' }},
            psychomotorTab: 'primary',
            profiles: @js($savedProfiles),
            defaults: @js($psychomotorDefaults),
            addTrait(stage, domain) {
                let name = prompt('Enter new ' + (domain === 'affective' ? 'Affective (Character)' : 'Psychomotor (Physical/Practical)') + ' trait:');
                if (name && name.trim().length > 0) {
                    if (!this.profiles[stage]) this.profiles[stage] = { affective: [], psychomotor: [] };
                    if (!this.profiles[stage][domain]) this.profiles[stage][domain] = [];
                    this.profiles[stage][domain].push(name.trim());
                }
            },
            removeTrait(stage, domain, index) {
                this.profiles[stage][domain].splice(index, 1);
            },
            resetStageToDefaults(stage) {
                if (confirm('Reset this educational stage back to standard curriculum defaults?')) {
                    this.profiles[stage] = JSON.parse(JSON.stringify(this.defaults[stage]));
                }
            }
        }">
        <x-page-header title="Report Card Settings" subtitle="Choose template and customize what appears on the report card."
            accent="settings" />

        <div class="flex gap-2">
            <a href="{{ route('settings.index') }}" class="btn-outline">← Back to Settings</a>
        </div>

        @if (session('status'))
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card-padded border border-orange-200 bg-orange-50/60">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update-templates') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- ═══════════════ TEMPLATE SELECTION ═══════════════ --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-emerald-50 to-sky-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-emerald-500 to-sky-600 text-white shadow-lg shadow-emerald-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">Report Card Templates</div>
                        <div class="text-sm font-semibold text-gray-600">Choose your preferred report card design - all
                            templates are free!</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @php
                        $reportTemplates = [
                            [
                                'key' => 'compact',
                                'title' => 'Compact',
                                'desc' => 'Clean, minimal layout focused on scores and summary.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'compact', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'elegant',
                                'title' => 'Elegant',
                                'desc' => 'Formal navy and gold theme with ornate borders and refined typography.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'elegant', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'classic',
                                'title' => 'Classic',
                                'desc' => 'Traditional black and white formal layout with maximum readability.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'classic', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'heritage',
                                'title' => 'Heritage',
                                'desc' => 'Sophisticated traditional academic layout with royal navy/gold borders, ornate dividers, and refined serif typography.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'heritage', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'nordic',
                                'title' => 'Nordic',
                                'desc' => 'Scandinavian minimalist design using clean slate borders, spacious margins, stark high-contrast typography, and light gray stats panels.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'nordic', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'signature',
                                'title' => 'Signature',
                                'desc' => 'Prestigious imperial watermark crest backdrop, elegant crimson/burgundy headings, gold divider styling, and premium script fonts.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'signature', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'riverdale',
                                'title' => 'Riverdale',
                                'desc' => 'Exact replica of the Riverdale High School layout featuring navy/gold header themes, student metadata boxes, behavior metrics, and principal remarks.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'riverdale', 'html' => 1]),
                                'free' => true,
                            ],
                            [
                                'key' => 'greenwood',
                                'title' => 'Greenwood',
                                'desc' => 'Premium left-sidebar report card layout matching Greenwood High School design with dark green/gold themes.',
                                'preview' => route('settings.templates.preview', ['type' => 'report-card', 'template' => 'greenwood', 'html' => 1]),
                                'free' => true,
                            ],
                        ];
                    @endphp


                    @foreach ($reportTemplates as $t)
                        @php
                            $isLocked = !$t['free'] && !$hasPremium;
                        @endphp
                        <label
                            :class="selectedReportTemplate === '{{ $t['key'] }}' ? 'border-emerald-500 ring-4 ring-emerald-500/20 bg-gradient-to-br from-white to-emerald-50/20 shadow-xl scale-[1.02] -translate-y-1 z-10' : 'border-gray-100 hover:border-emerald-250'"
                            class="group cursor-pointer rounded-3xl border bg-white/70 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur transition-all duration-300 ease-out hover:shadow-md {{ $isLocked ? 'opacity-60' : '' }} flex flex-col h-full">
                            <input type="radio" name="report_card_template" value="{{ $t['key'] }}" class="sr-only" x-model="selectedReportTemplate"
                                @checked($reportCardTemplate === $t['key']) @disabled($isLocked) />

                            <!-- Dynamic CSS Skeleton Preview -->
                            <div :class="selectedReportTemplate === '{{ $t['key'] }}' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-200'"
                                 class="relative w-full overflow-hidden rounded-xl border bg-slate-50 p-3 pointer-events-none select-none mb-4 flex flex-col justify-between transition-all duration-300" style="aspect-ratio: 1 / 1.414;">
                                
                                <!-- Glowing Selection Checkmark -->
                                <div x-show="selectedReportTemplate === '{{ $t['key'] }}'" 
                                     x-transition:enter="transition ease-out duration-250"
                                     x-transition:enter-start="opacity-0 scale-75"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute top-2 right-2 z-20 bg-emerald-600 text-white rounded-full p-1.5 shadow-lg flex items-center justify-center border border-emerald-400">
                                    <svg class="h-3.5 w-3.5 stroke-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                {{-- Header block --}}
                                @if($t['key'] === 'elegant')
                                    <div class="h-8 rounded-md bg-indigo-950 border-b-2 border-amber-400 flex items-center justify-between px-2 text-[6px] text-amber-400 font-serif">
                                        <span>ELEGANT ACADEMY</span> <div class="w-3.5 h-3.5 rounded-full border border-amber-400/40"></div>
                                    </div>
                                @elseif($t['key'] === 'classic')
                                    <div class="h-8 border-2 border-slate-950 flex items-center justify-between px-2 text-[6px] text-slate-950 font-mono font-bold">
                                        <span>CLASSIC SHEET</span> <div class="w-3.5 h-3.5 border border-slate-950"></div>
                                    </div>
                                @elseif($t['key'] === 'heritage')
                                    <div class="h-8 rounded-md bg-blue-900 border-2 border-yellow-500 flex items-center justify-between px-2 text-[6px] text-yellow-500 font-serif">
                                        <span>HERITAGE HALL</span> <div class="w-3.5 h-3.5 rounded-sm bg-yellow-500/30"></div>
                                    </div>
                                @elseif($t['key'] === 'nordic')
                                    <div class="h-8 rounded-md bg-slate-250 flex items-center justify-between px-2 text-[6px] text-slate-700 font-mono">
                                        <span>NORDIC GRADES</span> <div class="w-3.5 h-3.5 bg-slate-400/30"></div>
                                    </div>
                                @elseif($t['key'] === 'signature')
                                    <div class="h-8 rounded-md bg-red-950 border-b-2 border-amber-600 flex items-center justify-between px-2 text-[6px] text-amber-500 font-serif">
                                        <span>SIGNATURE PORTAL</span> <div class="w-3.5 h-3.5 rounded-full bg-amber-500/20 border border-amber-500/40"></div>
                                    </div>
                                @elseif($t['key'] === 'riverdale')
                                    <div class="h-8 rounded-md bg-sky-950 border-b-2 border-yellow-500 flex items-center justify-between px-2 text-[6px] text-yellow-500 font-serif">
                                        <span>RIVERDALE HIGH</span> <div class="w-3.5 h-3.5 rounded-sm bg-yellow-500/20 border border-yellow-500/40"></div>
                                    </div>
                                @elseif($t['key'] === 'greenwood')
                                    <div class="h-8 rounded-md bg-teal-950 border-b-2 border-yellow-500 flex items-center justify-between px-2 text-[6px] text-yellow-500 font-serif">
                                        <span>GREENWOOD HIGH</span> <div class="w-3.5 h-3.5 rounded-sm bg-yellow-500/20 border border-yellow-500/40"></div>
                                    </div>
                                @else {{-- compact --}}
                                    <div class="h-8 rounded-md bg-slate-800 flex items-center justify-between px-2 text-[6px] text-slate-100 font-bold">
                                        <span>COMPACT PORTAL</span> <div class="w-3.5 h-3.5 rounded-full bg-slate-600"></div>
                                    </div>
                                @endif

                                {{-- Student Info Mock --}}
                                <div class="space-y-1 mt-2">
                                    <div class="h-1.5 w-1/2 bg-slate-200 rounded"></div>
                                    <div class="h-1 w-1/3 bg-slate-150 rounded"></div>
                                </div>

                                {{-- Grid/Table Mock --}}
                                <div class="flex-1 mt-3 space-y-1">
                                    {{-- Table Header --}}
                                    <div class="h-3.5 bg-slate-350 rounded flex items-center justify-between px-1">
                                        <div class="h-1 w-1/4 bg-slate-450 rounded"></div>
                                        <div class="flex gap-1 w-1/3 justify-end">
                                            <div class="h-1 w-2.5 bg-slate-450 rounded"></div>
                                            <div class="h-1 w-2.5 bg-slate-450 rounded"></div>
                                            <div class="h-1 w-2.5 bg-slate-450 rounded"></div>
                                        </div>
                                    </div>
                                    {{-- Table Rows --}}
                                    @for($r = 0; $r < 4; $r++)
                                        <div class="h-3 bg-white border border-slate-100 rounded flex items-center justify-between px-1">
                                            <div class="h-1 w-1/3 bg-slate-200 rounded"></div>
                                            <div class="flex gap-1 w-1/3 justify-end">
                                                <div class="h-1 w-2 bg-slate-100 rounded"></div>
                                                <div class="h-1 w-2 bg-slate-150 rounded"></div>
                                                <div class="h-1 w-2 bg-indigo-100 rounded"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                {{-- Footer Mock --}}
                                <div class="border-t border-dashed border-slate-200 pt-2 mt-2 flex justify-between items-center">
                                    <div class="space-y-0.5 w-1/3">
                                        <div class="h-1 bg-slate-200 rounded"></div>
                                        <div class="h-0.5 bg-slate-150 rounded w-2/3"></div>
                                    </div>
                                    <div class="space-y-0.5 w-1/3 flex flex-col items-end">
                                        <div class="h-1 bg-slate-200 rounded w-full"></div>
                                        <div class="h-0.5 bg-slate-150 rounded w-2/3"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-black text-gray-900 flex items-center gap-2">
                                        {{ $t['title'] }}
                                        @if($isLocked)
                                            <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-xs font-semibold text-gray-600 line-clamp-2" title="{{ $t['desc'] }}">{{ $t['desc'] }}</div>
                                    @if($isLocked)
                                        <div class="mt-2 text-[10px] font-bold text-red-600 uppercase tracking-wider">🔒 Premium License Required</div>
                                    @endif
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full {{ $t['free'] ? 'bg-green-100 text-green-800' : 'bg-emerald-100 text-emerald-800' }} px-3 py-1 text-[10px] font-black group-hover:{{ $t['free'] ? 'bg-green-200' : 'bg-emerald-200' }}">
                                    {{ $t['free'] ? 'FREE' : 'PRO' }}
                                </span>
                            </div>

                            <div class="mt-auto pt-4 flex items-center justify-between">
                                <div class="text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 transition-colors duration-300"
                                     :class="selectedReportTemplate === '{{ $t['key'] }}' ? 'text-emerald-700' : 'text-gray-500'">
                                    <template x-if="selectedReportTemplate === '{{ $t['key'] }}'">
                                        <span class="flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Active Template
                                        </span>
                                    </template>
                                    <template x-if="selectedReportTemplate !== '{{ $t['key'] }}'">
                                        <span>{{ $isLocked ? 'Locked' : 'Click to select' }}</span>
                                    </template>
                                </div>
                                <button type="button" class="rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 border border-slate-200 transition-colors"
                                    @click.stop="open = true; src = @js($t['preview']); title = @js('Report Card · ' . $t['title'])">
                                    Full Preview
                                </button>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ═══════════════ REPORT CARD OPTIONS ═══════════════ --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-violet-50 to-indigo-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M9 11l3 3L22 4" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">Report Card Display Options</div>
                        <div class="text-sm font-semibold text-gray-600">Toggle which sections appear on the report card</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {{-- Position --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_position" value="0" />
                        <input type="checkbox" name="rc_show_position" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowPosition) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Student Position</div>
                            <div class="text-xs text-gray-500">Show class position/ranking</div>
                        </div>
                    </label>

                    {{-- Attendance --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_attendance" value="0" />
                        <input type="checkbox" name="rc_show_attendance" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowAttendance) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Attendance Record</div>
                            <div class="text-xs text-gray-500">Show times opened/present/absent</div>
                        </div>
                    </label>

                    {{-- Grading Key --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_grading_key" value="0" />
                        <input type="checkbox" name="rc_show_grading_key" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowGradingKey) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Grading Key</div>
                            <div class="text-xs text-gray-500">Show A-F grading legend</div>
                        </div>
                    </label>

                    {{-- Class Average --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_class_average" value="0" />
                        <input type="checkbox" name="rc_show_class_average" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowClassAverage) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Class Average / Highest / Lowest</div>
                            <div class="text-xs text-gray-500">Show class average, highest, and lowest scores</div>
                        </div>
                    </label>

                    {{-- Watermark --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_watermark" value="0" />
                        <input type="checkbox" name="rc_show_watermark" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowWatermark) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">School Logo Watermark</div>
                            <div class="text-xs text-gray-500">Faint logo watermark in background</div>
                        </div>
                    </label>

                    {{-- Next Term Date --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_next_term_date" value="0" />
                        <input type="checkbox" name="rc_show_next_term_date" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowNextTermDate) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Next Term Resumption Date</div>
                            <div class="text-xs text-gray-500">Show when next term begins</div>
                        </div>
                    </label>

                    {{-- Teacher Remarks --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_teacher_remarks" value="0" />
                        <input type="checkbox" name="rc_show_teacher_remarks" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowTeacherRemarks) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Teacher's Remarks</div>
                            <div class="text-xs text-gray-500">Show class teacher comment</div>
                        </div>
                    </label>

                    {{-- Principal Remarks --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_principal_remarks" value="0" />
                        <input type="checkbox" name="rc_show_principal_remarks" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowPrincipalRemarks) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Principal's Remarks</div>
                            <div class="text-xs text-gray-500">Show principal/head teacher comment</div>
                        </div>
                    </label>

                    {{-- Subject Teacher Remarks --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_subject_teacher_remarks" value="0" />
                        <input type="checkbox" name="rc_show_subject_teacher_remarks" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowSubjectTeacherRemarks) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Subject Teacher Remarks</div>
                            <div class="text-xs text-gray-500">Show per-subject teacher comments</div>
                        </div>
                    </label>

                    {{-- Class Highest & Lowest --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_class_highest_lowest" value="0" />
                        <input type="checkbox" name="rc_show_class_highest_lowest" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowClassHighestLowest) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Class Highest &amp; Lowest</div>
                            <div class="text-xs text-gray-500">Show class highest &amp; lowest subject scores</div>
                        </div>
                    </label>

                    {{-- Cumulative Summary --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_cumulative_summary" value="0" />
                        <input type="checkbox" name="rc_show_cumulative_summary" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowCumulativeSummary) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Cumulative Annual Summary</div>
                            <div class="text-xs text-gray-500">Show Term 1, 2, 3 totals &amp; annual average</div>
                        </div>
                    </label>

                    {{-- Color Grade Badges --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_color_badges" value="0" />
                        <input type="checkbox" name="rc_show_color_badges" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowColorBadges) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Color Grade Badges</div>
                            <div class="text-xs text-gray-500">Highlight A-F grades with color pills</div>
                        </div>
                    </label>

                    {{-- Security QR Code --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_qr_code" value="0" />
                        <input type="checkbox" name="rc_show_qr_code" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowQrCode) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Verification QR Badge</div>
                            <div class="text-xs text-gray-500">Show official verification badge at footer</div>
                        </div>
                    </label>

                    {{-- Subject Performance Rating --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_performance" value="0" />
                        <input type="checkbox" name="rc_show_performance" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcShowPerformance) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Subject Performance Rating</div>
                            <div class="text-xs text-gray-500">Show performance rating column (e.g. Excellent, Good, Pass)</div>
                        </div>
                    </label>

                    {{-- Block Debtors Gate --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_block_debtors" value="0" />
                        <input type="checkbox" name="rc_block_debtors" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" @checked($rcBlockDebtors) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Restrict Debtor Downloads</div>
                            <div class="text-xs text-gray-500">Block report cards for students with outstanding school fees</div>
                        </div>
                    </label>

                    {{-- Psychomotor Domain Toggle --}}
                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-violet-300 transition">
                        <input type="hidden" name="rc_show_psychomotor" value="0" />
                        <input type="checkbox" name="rc_show_psychomotor" value="1" class="h-5 w-5 rounded-lg border-gray-300 text-violet-600 focus:ring-violet-500" x-model="showPsychomotor" @checked($rcShowPsychomotor) />
                        <div>
                            <div class="text-sm font-bold text-gray-900">Psychomotor &amp; Affective Domains</div>
                            <div class="text-xs text-gray-500">Character demeanour, fine motor skills, crafts &amp; athletics</div>
                        </div>
                    </label>
                </div>

                {{-- Next Term Resumption Date Manual Override --}}
                <div class="mt-4 pt-4 border-t border-violet-100/80">
                    <div class="max-w-md">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">Next Term Resumption Date Override (Optional)</label>
                        <input type="text" name="rc_next_term_date" value="{{ $rcNextTermDateVal }}" placeholder="e.g. 15th September, 2026 (Leave blank to use academic calendar)"
                            class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-violet-500 focus:ring-2 focus:ring-violet-500" />
                        <div class="mt-1 text-[11px] text-gray-500">Overrides the calculated resumption date shown on report cards and booklets.</div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ PSYCHOMOTOR & AFFECTIVE DOMAIN CONFIGURATION ═══════════════ --}}
            <div x-show="showPsychomotor" x-transition class="rounded-3xl border border-gray-100 bg-gradient-to-br from-purple-50 to-pink-50/60 p-6 shadow-lg space-y-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg shadow-purple-500/30">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/>
                                <path d="M12 6a2 2 0 1 0 2 2 2 2 0 0 0-2-2zm-1 5h2v6h-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-black text-gray-900">Psychomotor &amp; Affective Domain Management</div>
                            <div class="text-sm font-semibold text-gray-600">Customize physical dexterity &amp; character traits per educational level</div>
                        </div>
                    </div>
                </div>

                {{-- Presentation Layout & Rating Scale Options --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm">
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1">Presentation Layout Style</label>
                        <select name="rc_psychomotor_style" class="w-full rounded-xl border-gray-300 py-2.5 pl-3 pr-10 text-sm font-semibold focus:border-purple-500 focus:ring-purple-500">
                            <option value="grid" @selected($rcPsychomotorStyle === 'grid')>Grid Checkbox Matrix (Formal 2-Column Tables)</option>
                            <option value="numeric" @selected($rcPsychomotorStyle === 'numeric')>Numeric Grade Scale (1–5 Blocks with Score)</option>
                            <option value="progress" @selected($rcPsychomotorStyle === 'progress')>Horizontal Progress Bars (Modern Colored Meters)</option>
                        </select>
                        <p class="text-[11px] text-gray-500 mt-1">Controls how traits are displayed on the printed report card.</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm">
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider mb-1">Evaluation Rating Scale</label>
                        <select name="rc_psychomotor_scale" class="w-full rounded-xl border-gray-300 py-2.5 pl-3 pr-10 text-sm font-semibold focus:border-purple-500 focus:ring-purple-500">
                            <option value="5_point" @selected($rcPsychomotorScale === '5_point')>5-Point Numeric Scale (5 = Excellent, 1 = Poor)</option>
                            <option value="letter" @selected($rcPsychomotorScale === 'letter')>Letter Scale (A = Distinction, E = Poor)</option>
                            <option value="descriptive" @selected($rcPsychomotorScale === 'descriptive')>Descriptive Words (Excellent, Good, Average, Fair, Poor)</option>
                        </select>
                        <p class="text-[11px] text-gray-500 mt-1">Scale used during teacher score entry and legend descriptions.</p>
                    </div>
                </div>

                {{-- Educational Stage Tabs --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                        <div class="flex gap-2 p-1 bg-gray-100 rounded-xl">
                            <button type="button" @click="psychomotorTab = 'early_years'"
                                :class="psychomotorTab === 'early_years' ? 'bg-white shadow text-purple-700 font-black' : 'text-gray-600 font-semibold hover:text-gray-900'"
                                class="px-4 py-2 text-xs rounded-lg transition">
                                🌱 Early Years / Nursery / KG
                            </button>
                            <button type="button" @click="psychomotorTab = 'primary'"
                                :class="psychomotorTab === 'primary' ? 'bg-white shadow text-purple-700 font-black' : 'text-gray-600 font-semibold hover:text-gray-900'"
                                class="px-4 py-2 text-xs rounded-lg transition">
                                🎒 Primary School (Basic 1–6)
                            </button>
                            <button type="button" @click="psychomotorTab = 'secondary'"
                                :class="psychomotorTab === 'secondary' ? 'bg-white shadow text-purple-700 font-black' : 'text-gray-600 font-semibold hover:text-gray-900'"
                                class="px-4 py-2 text-xs rounded-lg transition">
                                🎓 Secondary School / College
                            </button>
                        </div>

                        <button type="button" @click="resetStageToDefaults(psychomotorTab)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                            Reset Stage to Defaults
                        </button>
                    </div>

                    {{-- Hidden Form Inputs Synced to Alpine State --}}
                    <template x-for="stageKey in ['early_years', 'primary', 'secondary']" :key="'sync-' + stageKey">
                        <div>
                            <template x-for="(trait, idx) in (profiles[stageKey]?.affective || [])" :key="stageKey + '-aff-' + idx">
                                <input type="hidden" :name="'rc_psychomotor_profiles[' + stageKey + '][affective][]'" :value="trait">
                            </template>
                            <template x-for="(trait, idx) in (profiles[stageKey]?.psychomotor || [])" :key="stageKey + '-psy-' + idx">
                                <input type="hidden" :name="'rc_psychomotor_profiles[' + stageKey + '][psychomotor][]'" :value="trait">
                            </template>
                        </div>
                    </template>

                    {{-- Trait Columns --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        {{-- Column 1: Affective Domain --}}
                        <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-black text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full bg-purple-600"></span>
                                        Affective Domain (Character &amp; Social Habits)
                                    </div>
                                    <div class="text-[11px] text-gray-500">Manners, personal discipline, peer relationships</div>
                                </div>
                                <button type="button" @click="addTrait(psychomotorTab, 'affective')"
                                    class="px-2.5 py-1 text-xs font-bold bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-sm transition">
                                    + Add Trait
                                </button>
                            </div>

                            <div class="space-y-1.5">
                                <template x-for="(trait, idx) in (profiles[psychomotorTab]?.affective || [])" :key="psychomotorTab + '-aff-list-' + idx">
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-purple-100 shadow-xs text-xs">
                                        <span class="font-bold text-gray-800" x-text="trait"></span>
                                        <button type="button" @click="removeTrait(psychomotorTab, 'affective', idx)"
                                            class="text-red-500 hover:text-red-700 p-1" title="Delete trait">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Column 2: Psychomotor Domain --}}
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-black text-indigo-900 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                                        Psychomotor Domain (Physical &amp; Practical Skills)
                                    </div>
                                    <div class="text-[11px] text-gray-500">Fine/gross motor control, sports, craft &amp; apparatus dexterity</div>
                                </div>
                                <button type="button" @click="addTrait(psychomotorTab, 'psychomotor')"
                                    class="px-2.5 py-1 text-xs font-bold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm transition">
                                    + Add Trait
                                </button>
                            </div>

                            <div class="space-y-1.5">
                                <template x-for="(trait, idx) in (profiles[psychomotorTab]?.psychomotor || [])" :key="psychomotorTab + '-psy-list-' + idx">
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-indigo-100 shadow-xs text-xs">
                                        <span class="font-bold text-gray-800" x-text="trait"></span>
                                        <button type="button" @click="removeTrait(psychomotorTab, 'psychomotor', idx)"
                                            class="text-red-500 hover:text-red-700 p-1" title="Delete trait">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ SCHOOL FEES SECTION ═══════════════ --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-orange-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">School Fees for Next Term</div>
                        <div class="text-sm font-semibold text-gray-600">Show tuition fees and bank details on the report card</div>
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-amber-300 transition mb-4">
                    <input type="hidden" name="rc_show_school_fees" value="0" />
                    <input type="checkbox" name="rc_show_school_fees" value="1"
                        class="h-5 w-5 rounded-lg border-gray-300 text-amber-600 focus:ring-amber-500"
                        x-model="showSchoolFees"
                        @checked($rcShowSchoolFees) />
                    <div>
                        <div class="text-sm font-bold text-gray-900">Enable School Fees on Report Card</div>
                        <div class="text-xs text-gray-500">When enabled, each class's tuition and account details will be printed</div>
                    </div>
                </label>

                <div x-show="showSchoolFees" x-transition class="space-y-4">
                    {{-- Bank Details --}}
                    <div class="rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
                        <div class="text-sm font-bold text-gray-900 mb-3">Bank Account Details</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Bank Name</label>
                                <input name="rc_school_fees_bank_name"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    value="{{ $rcSchoolFeesBankName }}" placeholder="e.g. First Bank" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Account Number</label>
                                <input name="rc_school_fees_account_number"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    value="{{ $rcSchoolFeesAccountNumber }}" placeholder="e.g. 0123456789" />
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Account Name</label>
                                <input name="rc_school_fees_account_name"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                    value="{{ $rcSchoolFeesAccountName }}" placeholder="e.g. School Name Ltd" />
                            </div>
                        </div>
                    </div>

                    {{-- Per-Class Fees --}}
                    <div class="rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
                        <div class="text-sm font-bold text-gray-900 mb-1">Fees Per Class</div>
                        <div class="text-xs text-gray-500 mb-4">Set the tuition amount for each class (leave empty to skip that class)</div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($classes as $class)
                                <div class="flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/50 p-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-gray-800">{{ $class->name }}</div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-sm font-bold text-gray-500">{{ config('academyhub.currency_symbol', '₦') }}</span>
                                        <input name="rc_school_fees_by_class[{{ $class->id }}]"
                                            type="number" step="0.01" min="0"
                                            class="w-28 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                            value="{{ $rcSchoolFeesByClass[$class->id] ?? '' }}"
                                            placeholder="0.00" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ SIGNATURES SECTION ═══════════════ --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-rose-50 to-pink-50/60 p-6 shadow-lg">
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-lg shadow-rose-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-black text-gray-900">Signatures on Report Card</div>
                        <div class="text-sm font-semibold text-gray-600">Upload actual signature images to print on report cards</div>
                    </div>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm cursor-pointer hover:border-rose-300 transition mb-4">
                    <input type="hidden" name="rc_show_signatures" value="0" />
                    <input type="checkbox" name="rc_show_signatures" value="1"
                        class="h-5 w-5 rounded-lg border-gray-300 text-rose-600 focus:ring-rose-500"
                        x-model="showSignatures"
                        @checked($rcShowSignatures) />
                    <div>
                        <div class="text-sm font-bold text-gray-900">Enable Signature Images</div>
                        <div class="text-xs text-gray-500">When enabled, uploaded signatures will appear above the signature lines</div>
                    </div>
                </label>

                <div x-show="showSignatures" x-transition class="space-y-4">
                    {{-- Principal Details --}}
                    <div class="rounded-2xl border border-rose-200 bg-white/90 p-5 shadow-sm">
                        <div class="text-sm font-bold text-gray-900 mb-3">Principal / Head Teacher Name &amp; Title</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Principal's Full Name</label>
                                <input name="rc_principal_name" type="text"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                    value="{{ $rcPrincipalName }}" placeholder="e.g. Dr. Rebecca Carter or Hajia Fatima Usman" />
                                <div class="mt-1 text-[10px] text-gray-500">Official name to print under Principal's signature block</div>
                            </div>
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600">Designation / Title</label>
                                <input name="rc_principal_title" type="text"
                                    class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                    value="{{ $rcPrincipalTitle }}" placeholder="e.g. Principal or Head Teacher" />
                                <div class="mt-1 text-[10px] text-gray-500">Designation title (e.g. Principal, Headmistress, Director)</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- Principal Signature --}}
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-5 shadow-sm">
                            <div class="text-sm font-bold text-gray-900 mb-3">Principal / Head Teacher Signature</div>
                            @if($rcPrincipalSignatureImage)
                                <div class="mb-3 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <img src="{{ asset('uploads/' . str_replace('\\', '/', $rcPrincipalSignatureImage)) }}"
                                        alt="Principal Signature" class="h-12 object-contain" />
                                    <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                        <input type="checkbox" name="rc_principal_signature_remove" value="1" class="rounded border-gray-300 text-red-500" />
                                        Remove
                                    </label>
                                </div>
                            @endif
                            <input name="rc_principal_signature_image" type="file" accept="image/*"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-rose-500" />
                            <div class="mt-1 text-xs text-gray-500">Upload a transparent PNG for best results (max 2MB)</div>
                        </div>

                        {{-- Teacher Signature --}}
                        <div class="rounded-2xl border border-rose-200 bg-white/90 p-5 shadow-sm">
                            <div class="text-sm font-bold text-gray-900 mb-3">Class Teacher Signature</div>
                            @if($rcTeacherSignatureImage)
                                <div class="mb-3 flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                                    <img src="{{ asset('uploads/' . str_replace('\\', '/', $rcTeacherSignatureImage)) }}"
                                        alt="Teacher Signature" class="h-12 object-contain" />
                                    <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                        <input type="checkbox" name="rc_teacher_signature_remove" value="1" class="rounded border-gray-300 text-red-500" />
                                        Remove
                                    </label>
                                </div>
                            @endif
                            <input name="rc_teacher_signature_image" type="file" accept="image/*"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:ring-2 focus:ring-rose-500" />
                            <div class="mt-1 text-xs text-gray-500">Upload a transparent PNG for best results (max 2MB)</div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-slate-800 transition-all">
                Save Report Card Settings
            </button>
        </form>

        <!-- Preview Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4"
            @click.self="open = false; src = null">
            <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="text-sm font-black text-slate-900" x-text="title"></div>
                    <button type="button"
                        class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200"
                        @click="open = false; src = null">
                        Close
                    </button>
                </div>
                <div class="h-[80vh] bg-slate-50">
                    <iframe :src="src" class="h-full w-full" title="Template Preview"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection