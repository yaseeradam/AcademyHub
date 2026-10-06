@extends('layouts.app')

@section('content')
<div class="space-y-6 font-sans pb-10">

    {{-- ══════════════════════════════════════════════════════════════
         1. HERO SECTION (Executive Royal Navy Gradient with 3D Character)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden rounded-2xl sm:rounded-[1.5rem] bg-gradient-to-r from-[#17274E] to-[#1D3261] shadow-xl p-4 sm:p-7 flex flex-col md:flex-row items-center justify-between min-h-0 sm:min-h-[220px] text-white">
        
        {{-- Subtle radial dot grid --}}
        <div class="absolute inset-0 pointer-events-none opacity-40 mix-blend-screen bg-[radial-gradient(circle,#ffffff_1.5px,transparent_1.5px)]" style="background-size: 32px 32px;"></div>

        {{-- Left Content --}}
        <div class="relative z-10 py-2 sm:py-5 w-full md:w-3/5">
            <div class="flex items-center gap-2 mb-2 sm:mb-3">
                <span class="h-2 w-2 rounded-full bg-[#10b981] animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-[#34d399]">Executive Cockpit</span>
                <span class="text-xs text-blue-200/60">&bull;</span>
                <span class="text-xs font-semibold text-blue-200">Governance &amp; Oversight</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight mb-1 sm:mb-1.5">
                {{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}
            </h1>
            <p class="text-xs sm:text-sm font-medium text-blue-200 mb-3 sm:mb-4 max-w-xl">
                Real-time institutional oversight across student enrollment, academic performance, tuition recovery, and staff punctuality.
            </p>
            
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
                <div class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/10 backdrop-blur-sm px-2.5 py-1 sm:px-3 sm:py-1.5 text-[11px] font-bold text-white shadow-sm">
                    <svg class="h-3.5 w-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    {{ $session }} Session
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/10 backdrop-blur-sm px-2.5 py-1 sm:px-3 sm:py-1.5 text-[11px] font-bold text-white shadow-sm">
                    <svg class="h-3.5 w-3.5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Term {{ $term }}
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/10 backdrop-blur-sm px-2.5 py-1 sm:px-3 sm:py-1.5 text-[11px] font-bold text-white shadow-sm">
                    <svg class="h-3.5 w-3.5 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    {{ now()->format('l, F j') }}
                </div>
            </div>
        </div>

        {{-- Right Content: Mini "Institutional Pulse" Widget & 3D Admin Avatar --}}
        <div class="relative z-10 w-full md:w-2/5 flex items-center justify-end py-2 sm:py-4 md:py-0 md:self-stretch">
            {{-- 3D Avatar (Admin) --}}
            <img src="{{ asset('avatars/Admin.png') }}" class="absolute bottom-0 -left-12 h-52 md:h-[210px] lg:h-[230px] z-20 object-contain drop-shadow-2xl hidden md:block" alt="Executive Avatar">

            <div class="bg-white/10 backdrop-blur-md rounded-2xl border border-white/10 p-4 shadow-xl w-full max-w-full sm:max-w-[280px] relative z-10 mt-2 sm:mt-4 md:mt-0">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-xs font-bold text-white">Institutional Pulse</span>
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-300 bg-emerald-500/20 px-2 py-0.5 rounded-md border border-emerald-400/30">Active</span>
                </div>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <div class="flex items-center justify-between text-blue-100 mb-1">
                            <span class="text-[11px]">Tuition Collection</span>
                            <span class="font-bold text-white">{{ $collectionRate }}%</span>
                        </div>
                        <div class="w-full bg-white/15 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-400 h-full rounded-full transition-all duration-500" style="width: {{ $collectionRate }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-blue-100 mb-1">
                            <span class="text-[11px]">Faculty Punctuality</span>
                            <span class="font-bold text-white">{{ $staffPunctualityRate }}%</span>
                        </div>
                        <div class="w-full bg-white/15 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-sky-400 h-full rounded-full transition-all duration-500" style="width: {{ $staffPunctualityRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         2. KEY EXECUTIVE KPI METRICS (5 Colorful Gradient Cards with 3D Avatars)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5 sm:gap-4">

        {{-- 1. Student Enrollment (Orange to Rose with 3D Boy Avatar) --}}
        <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#f97316] to-[#f43f5e] p-4 sm:p-5 shadow-sm sm:shadow-md relative overflow-hidden flex flex-col justify-between min-h-[125px] sm:min-h-[145px] group text-white">
            <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            <div class="absolute right-8 bottom-8 h-12 w-12 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-black text-white drop-shadow-sm tracking-tight leading-none">{{ number_format($totalStudents) }}</h3>
                        <p class="text-white/90 font-bold text-xs sm:text-sm mt-1 tracking-wide">Total Students</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[9px] sm:text-[10px] font-bold text-white/95">
                    <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">{{ $maleStudents }} Boys</span>
                    <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">{{ $femaleStudents }} Girls</span>
                </div>
            </div>
            <img src="{{ asset('avatars/studentblue.png') }}" class="absolute bottom-0 -right-2 h-[80%] sm:h-[110%] object-contain origin-bottom scale-90 translate-x-2 z-0 opacity-75 sm:opacity-95 pointer-events-none" alt="Students">
        </div>

        {{-- 2. Staff & Punctuality (Purple to Violet with 3D Teacher Avatar) --}}
        <div class="rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#a855f7] to-[#7e22ce] p-4 sm:p-5 shadow-sm sm:shadow-md relative overflow-hidden flex flex-col justify-between min-h-[125px] sm:min-h-[145px] group text-white">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl sm:text-3xl font-black text-white drop-shadow-sm tracking-tight leading-none">{{ number_format($totalTeachers) }}</h3>
                        <p class="text-white/90 font-bold text-xs sm:text-sm mt-1 tracking-wide">Teaching Staff</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1 text-[9px] sm:text-[10px] font-bold text-white/95">
                    @if(($markedCount ?? 0) === 0)
                        <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">Awaiting Clock-ins</span>
                    @else
                        <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">{{ $staffPunctualityRate }}% On-Time Today</span>
                    @endif
                </div>
            </div>
            <img src="{{ asset('avatars/girl student pink.png') }}" class="absolute bottom-0 right-1 sm:right-2 h-[80%] sm:h-[110%] object-contain scale-90 translate-x-2 z-0 opacity-75 sm:opacity-95 pointer-events-none" alt="Teacher">
        </div>

        {{-- 3. Total Fees Collected (Emerald to Teal with 3D Avatar) --}}
        <a href="{{ route('billing.index', ['tab' => 'debtors']) }}" class="group block rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#10b981] to-[#047857] p-4 sm:p-5 shadow-sm sm:shadow-md relative overflow-hidden flex flex-col justify-between min-h-[125px] sm:min-h-[145px] text-white hover:scale-[1.02] transition-transform">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-white drop-shadow-sm tracking-tight leading-none">₦{{ number_format($totalCollected, 0) }}</h3>
                        <p class="text-white/90 font-bold text-xs sm:text-sm mt-1 tracking-wide">Fees Collected &rarr;</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-[9px] sm:text-[10px] font-bold text-white/95">
                    <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">{{ $collectionRate }}% of ₦{{ number_format($totalExpected, 0) }}</span>
                </div>
            </div>
            <img src="{{ asset('avatars/student yellow.png') }}" class="absolute bottom-0 right-1 sm:right-2 h-[75%] sm:h-[105%] object-contain scale-90 translate-x-2 z-0 opacity-75 sm:opacity-90 pointer-events-none" alt="Finance">
        </a>

        {{-- 4. Outstanding Debt (Rose to Crimson Warning Card) --}}
        <a href="{{ route('billing.index', ['tab' => 'debtors']) }}" class="group block rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] p-4 sm:p-5 shadow-sm sm:shadow-md relative overflow-hidden flex flex-col justify-between min-h-[125px] sm:min-h-[145px] text-white hover:scale-[1.02] transition-transform">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-white drop-shadow-sm tracking-tight leading-none">₦{{ number_format($outstandingDebt, 0) }}</h3>
                        <p class="text-white/90 font-bold text-xs sm:text-sm mt-1 tracking-wide">Uncollected Debt &rarr;</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-[9px] sm:text-[10px] font-bold text-white/95">
                    <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">Pending tuition recovery</span>
                </div>
            </div>
        </a>

        {{-- 5. Net Operating Position (Cyan to Teal Gradient Card) --}}
        <div class="col-span-2 sm:col-span-1 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#06b6d4] to-[#0e7490] p-4 sm:p-5 shadow-sm sm:shadow-md relative overflow-hidden flex flex-col justify-between min-h-[125px] sm:min-h-[145px] text-white">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 mix-blend-overlay"></div>
            
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-black text-white drop-shadow-sm tracking-tight leading-none">₦{{ number_format($netPosition, 0) }}</h3>
                        <p class="text-white/90 font-bold text-xs sm:text-sm mt-1 tracking-wide">Operating Net</p>
                    </div>
                    <div class="bg-white/20 p-2 rounded-xl backdrop-blur-[2px]">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-[9px] sm:text-[10px] font-bold text-white/95">
                    <span class="bg-white/20 px-2 py-0.5 rounded-full backdrop-blur-sm ring-1 ring-white/25">Less ₦{{ number_format($totalExpenses, 0) }} costs</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         3. ACADEMIC LEADERBOARD & INTERVENTION WATCHLIST
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- STAR PERFORMERS (TOP 5) --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-emerald-50/70 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0A6.75 6.75 0 0019.5 7.5c0-1.88-1.04-3.52-2.58-4.375M7.5 14.25a6.75 6.75 0 01-5.004-6.75C2.496 5.62 3.536 3.98 5.076 3.125M12 3a4.5 4.5 0 00-4.5 4.5c0 1.766 1.018 3.295 2.5 4.025v2.725h4v-2.725A4.502 4.502 0 0016.5 7.5 4.5 4.5 0 0012 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Star Performers (Top 5)</h3>
                        <p class="text-xs text-slate-500">Highest overall academic average across school</p>
                    </div>
                </div>
                <a href="{{ route('results.broadsheet') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition">
                    Broadsheet &rarr;
                </a>
            </div>

            <div class="p-5 divide-y divide-slate-100 flex-1">
                @forelse($starStudents as $index => $student)
                    <div class="py-3.5 flex items-center justify-between first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full font-black text-xs
                                {{ $index === 0 ? 'bg-amber-100 text-amber-900 border-2 border-amber-300' : ($index === 1 ? 'bg-slate-200 text-slate-800 border-2 border-slate-300' : ($index === 2 ? 'bg-orange-100 text-orange-900 border-2 border-orange-300' : 'bg-slate-100 text-slate-700')) }}">
                                #{{ $index + 1 }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-extrabold text-sm text-slate-900 truncate">{{ $student['name'] }}</div>
                                <div class="text-[11px] font-semibold text-slate-400">
                                    {{ $student['class_name'] }} &bull; ADM: {{ $student['adm_no'] }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-base font-black text-emerald-600">{{ $student['average'] }}%</div>
                            <div class="text-[10px] font-bold text-slate-400">{{ $student['total_subjects'] }} subjects</div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs font-medium">
                        No exam score records available yet for this term.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ACADEMIC WATCHLIST (NEEDS INTERVENTION) --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-rose-50/70 to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-black shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Academic Watchlist</h3>
                        <p class="text-xs text-slate-500">Students with low averages or failing &ge; 1 subjects</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200">
                    Intervention Advisory
                </span>
            </div>

            <div class="p-5 divide-y divide-slate-100 flex-1">
                @forelse($watchlistStudents as $index => $student)
                    <div class="py-3.5 flex items-center justify-between first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full font-black text-xs bg-rose-50 text-rose-600 border border-rose-200">
                                !
                            </div>
                            <div class="min-w-0">
                                <div class="font-extrabold text-sm text-slate-900 truncate">{{ $student['name'] }}</div>
                                <div class="text-[11px] font-semibold text-slate-400">
                                    {{ $student['class_name'] }} &bull; ADM: {{ $student['adm_no'] }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-base font-black text-rose-600">{{ $student['average'] }}%</div>
                            @if($student['failed_count'] > 0)
                                <div class="text-[10px] font-bold text-rose-500">{{ $student['failed_count'] }} failing subjects</div>
                            @else
                                <div class="text-[10px] font-semibold text-slate-400">Needs boost</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 text-xs font-semibold flex flex-col items-center justify-center gap-1.5">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>All students are in good academic standing (No failing averages)</span>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         4. CLASS RANKINGS & CURRICULUM SUBJECT INDEX
    ══════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- CLASS ACADEMIC RANKINGS --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-slate-200/80 shadow-sm p-5 sm:p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Class Academic Rankings</h3>
                    <p class="text-xs text-slate-500">Ranked by combined average student scores</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    {{ $classRankings->count() }} Classes
                </span>
            </div>

            @if($classRankings->isNotEmpty())
                <div class="space-y-3 max-h-[440px] overflow-y-auto pr-1">
                    @foreach($classRankings as $rank => $cls)
                        <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50/70 border border-slate-100 hover:border-slate-200 transition-colors">
                            <span class="font-black text-xs text-slate-400 w-5 text-center">{{ $rank + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-bold text-sm text-slate-900 truncate">{{ $cls['name'] }}</span>
                                    <span class="font-black text-xs {{ $cls['average'] >= 60 ? 'text-emerald-600' : ($cls['average'] >= 45 ? 'text-amber-600' : 'text-rose-600') }}">
                                        {{ $cls['average'] }}% Avg
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full {{ $cls['average'] >= 60 ? 'bg-emerald-500' : ($cls['average'] >= 45 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                         style="width: {{ min(100, $cls['average']) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs font-medium">
                    No class score summaries available yet.
                </div>
            @endif
        </div>

        {{-- SUBJECT PERFORMANCE INDEX --}}
        <div class="rounded-2xl sm:rounded-3xl bg-white border border-slate-200/80 shadow-sm p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Subject Performance Index</h3>
                <p class="text-xs text-slate-400 mb-4">Highlights curriculum strengths and bottlenecks</p>

                {{-- Top Subjects --}}
                <div class="mb-5">
                    <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600 mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Top Performing Subjects</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($bestSubjects as $sub)
                            <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100 flex items-center justify-between">
                                <span class="font-bold text-xs text-emerald-950 truncate">{{ $sub['name'] }}</span>
                                <span class="font-black text-xs text-emerald-600">{{ $sub['average'] }}%</span>
                            </div>
                        @empty
                            <div class="text-[11px] text-slate-400">No subject data</div>
                        @endforelse
                    </div>
                </div>

                {{-- Struggling Subjects --}}
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-rose-600 mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        <span>Struggling Subjects (Attention)</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($strugglingSubjects as $sub)
                            <div class="p-2.5 rounded-xl bg-rose-50/70 border border-rose-100 flex items-center justify-between">
                                <span class="font-bold text-xs text-rose-950 truncate">{{ $sub['name'] }}</span>
                                <span class="font-black text-xs text-rose-600">{{ $sub['average'] }}%</span>
                            </div>
                        @empty
                            <div class="text-[11px] text-slate-400">No subject data</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('results.broadsheet') }}" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors border border-slate-200">
                    View Academic Broadsheet &rarr;
                </a>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         5. STAFF BIOMETRIC RADAR & SHIFTS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl sm:rounded-3xl bg-white border border-slate-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Today's Staff Attendance Radar</h3>
                <p class="text-xs text-slate-500">Biometric clock-ins, arrival timestamps, and shift discipline</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                    Western: {{ $westernStaffCount }} Teachers (7:00–12:30)
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Islamic: {{ $islamicStaffCount }} Teachers (12:30–5:00)
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center">
                <div class="text-2xl font-black text-emerald-600">{{ $presentTeachers }}</div>
                <div class="text-xs font-bold text-emerald-800 mt-0.5">Present On-Time</div>
            </div>
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100 text-center">
                <div class="text-2xl font-black text-amber-600">{{ $lateTeachers }}</div>
                <div class="text-xs font-bold text-amber-800 mt-0.5">Arrived Late</div>
            </div>
            <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-100 text-center">
                <div class="text-2xl font-black text-rose-600">{{ $absentTeachers }}</div>
                <div class="text-xs font-bold text-rose-800 mt-0.5">Absent / Unverified</div>
            </div>
        </div>

        @if($recentMarks->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-black uppercase tracking-wider text-slate-500">
                            <th class="py-2.5 px-3">Teacher</th>
                            <th class="py-2.5 px-3">Shift</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-center">Punch In</th>
                            <th class="py-2.5 px-3 text-center">Punch Out</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($recentMarks as $m)
                            @php
                                $punchInDisplay = '—';
                                if (!empty($m->punch_in_time)) {
                                    try {
                                        $punchInDisplay = \Carbon\Carbon::parse($m->punch_in_time)->format('g:i A');
                                    } catch (\Throwable $e) {
                                        $punchInDisplay = (string) $m->punch_in_time;
                                    }
                                }
                                $punchOutDisplay = '—';
                                if (!empty($m->punch_out_time)) {
                                    try {
                                        $punchOutDisplay = \Carbon\Carbon::parse($m->punch_out_time)->format('g:i A');
                                    } catch (\Throwable $e) {
                                        $punchOutDisplay = (string) $m->punch_out_time;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $m->teacher?->name ?? 'Staff' }}</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $m->teacher?->getShiftLabel() ?? 'Western' }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $m->status === 'Present' ? 'bg-emerald-100 text-emerald-800' : ($m->status === 'Late' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $m->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-600">
                                    {{ $punchInDisplay }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-bold text-rose-500">
                                    {{ $punchOutDisplay }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400 text-xs font-medium">
                No staff attendance punches recorded yet today.
            </div>
        @endif

        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span class="text-xs font-medium text-slate-400">Timesheets track detailed payroll deductions for unexcused lateness and absence.</span>
            <a href="{{ route('attendance.staff-timesheet') }}" class="text-xs font-bold text-amber-600 hover:text-amber-700 transition">
                Open Full Staff Timesheets &rarr;
            </a>
        </div>
    </div>

</div>
@endsection
