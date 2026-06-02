@php
    $child     = $this->selectedChild;
    $stats     = $this->performanceStats;
    $att       = $this->attendance;
    $fees      = $this->fees;
    $scores    = $this->scores;
    $homework  = $this->homework;
    $recent    = $this->recentAttendance;
    $published = $this->resultsPublished;
    $maxTotal  = $stats['maxTotal'] ?? 100;
    $ordinal   = fn($n) => $n . match(true) { $n%100>=11&&$n%100<=13=>'th', $n%10===1=>'st', $n%10===2=>'nd', $n%10===3=>'rd', default=>'th' };
    $gradeColor = fn($g) => match($g) { 'A'=>'bg-emerald-100 text-emerald-800','B'=>'bg-blue-100 text-blue-800','C'=>'bg-yellow-100 text-yellow-800','D'=>'bg-orange-100 text-orange-800',default=>'bg-red-100 text-red-800' };

    $user = auth()->user();
    $tenant = $user ? $user->tenant : (app()->bound('currentTenant') ? app('currentTenant') : null);
    $hasPaymentGateway = $tenant ? $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists() : false;
@endphp

<div class="space-y-6 pb-12">
    
    {{-- Global Dashboard Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-8 shadow-lg">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-5"></div>
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-black/20 blur-2xl"></div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 mb-3 backdrop-blur-sm shadow-sm">
                <div class="h-2 w-2 rounded-full bg-green-400 shadow-[0_0_8px_rgba(74,222,128,0.8)]"></div>
                <span class="text-xs font-bold text-white tracking-wide uppercase">Parent Dashboard</span>
            </div>
            <h1 class="text-3xl font-black text-white sm:text-4xl tracking-tight">Welcome back, {{ auth()->user()->name }}</h1>
            @if($hasPaymentGateway)
                <p class="mt-2 text-sm text-brand-100 font-semibold max-w-lg">Track your children's academic progress, attendance, homework, and fees seamlessly from one place.</p>
            @else
                <p class="mt-2 text-sm text-brand-100 font-semibold max-w-lg">Track your children's academic progress, attendance, and homework seamlessly from one place.</p>
            @endif
        </div>
    </div>

    @if (! $child)
        {{-- STATE 1: Select Child View (3D Rectangular Cards) --}}
        @if ($this->children->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-white py-16 text-center shadow-sm">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 ring-1 ring-slate-100">
                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <h3 class="mt-4 text-lg font-bold text-slate-700">No children linked</h3>
                <p class="mt-1 text-sm text-slate-500">Contact the school administrator to link your children.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 p-1">
                @foreach ($this->children as $c)
                    <button wire:click="selectChild({{ $c->id }})"
                            class="group relative w-full text-left bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgb(0,0,0,0.1)] overflow-hidden flex flex-col h-72">
                        
                        {{-- Top Pattern/Color Strip --}}
                        <div class="h-28 w-full relative border-b border-slate-100 overflow-hidden bg-gradient-to-r from-slate-100 to-slate-50">
                            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#94a3b8 1px, transparent 1px); background-size: 16px 16px;"></div>
                        </div>

                        {{-- Photo --}}
                        <div class="absolute top-12 left-6">
                            @if ($c->passport_photo_url)
                                <img src="{{ $c->passport_photo_url }}" class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white shadow-lg bg-white block" />
                            @else
                                <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-100 to-indigo-100 text-4xl font-black text-brand-600 ring-4 ring-white shadow-lg">
                                    {{ mb_substr($c->first_name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="px-6 pt-12 pb-6 flex-1 flex flex-col justify-between relative bg-white z-10">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800 leading-tight group-hover:text-brand-600 transition-colors">{{ $c->full_name }}</h3>
                                <p class="text-sm font-semibold text-slate-500 mt-1">{{ $c->schoolClass?->name ?? 'Unassigned' }}</p>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-between border-t border-slate-50 pt-4">
                                <span class="text-[11px] font-black text-slate-400 tracking-widest uppercase bg-slate-50 px-2.5 py-1 rounded-lg">Admin: {{ $c->admission_number }}</span>
                                <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-brand-50 transition-colors">
                                    <svg class="h-4 w-4 text-slate-400 group-hover:text-brand-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

    @else
        {{-- STATE 2: Child Data Layout (Compact, No massive scrolling) --}}
        
        {{-- Very Compact Colorful Header --}}
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 bg-slate-800 rounded-3xl p-6 shadow-md border-0 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-10"></div>
            
            <div class="relative z-10 flex items-center gap-5 w-full lg:w-auto">
                <button wire:click="$set('selectedChildId', null)" class="shrink-0 h-12 w-12 flex items-center justify-center rounded-2xl bg-white/10 text-white hover:bg-white hover:text-slate-800 backdrop-blur-md transition-all shadow-sm border border-white/5">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                
                @if ($child->passport_photo_url)
                    <img src="{{ $child->passport_photo_url }}" class="shrink-0 h-16 w-16 rounded-2xl object-cover ring-2 ring-white/50 shadow-sm" />
                @else
                    <div class="shrink-0 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-md text-2xl font-black text-white ring-2 ring-white/50 shadow-sm">
                        {{ mb_substr($child->first_name, 0, 1) }}
                    </div>
                @endif
                
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-black text-white leading-tight truncate">{{ $child->full_name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="rounded-lg bg-white/20 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-white backdrop-blur-md shadow-sm">{{ $child->schoolClass?->name ?? 'Unassigned' }}</span>
                        <span class="rounded-lg bg-black/20 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-white backdrop-blur-md shadow-sm">Admin: {{ $child->admission_number }}</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex gap-3 w-full lg:w-auto">
                <div class="flex items-center gap-2 rounded-xl bg-slate-900/50 backdrop-blur-md px-3 py-2 border border-white/10 shadow-sm flex-1 lg:flex-none">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/50">Term</span>
                    <select wire:model.live="term" class="bg-transparent text-sm font-black text-white border-0 p-0 focus:ring-0 cursor-pointer w-full focus:bg-slate-800 transition-colors rounded">
                        <option class="text-slate-800" value="1">One</option>
                        <option class="text-slate-800" value="2">Two</option>
                        <option class="text-slate-800" value="3">Three</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 rounded-xl bg-slate-900/50 backdrop-blur-md px-3 py-2 border border-white/10 shadow-sm flex-1 lg:flex-none">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/50">Session</span>
                    <input wire:model.live="session" type="text" class="w-24 bg-transparent text-sm font-black text-white border-0 p-0 focus:ring-0 text-center placeholder-white/20" placeholder="2024/2025" />
                </div>
            </div>
        </div>

        {{-- Stat Metrics Row --}}
        <div class="grid grid-cols-2 lg:grid-cols-{{ $hasPaymentGateway ? 4 : 3 }} gap-4">
            {{-- Metric 1: Average --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-1 rounded-md">Average</p>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-800 tracking-tight">{{ $stats['average'] }}<span class="text-sm font-bold text-slate-400 ml-1">%</span></div>
            </div>
            
            {{-- Metric 2: Position --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-1 rounded-md">Position</p>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-800 tracking-tight">
                    {{ $published && $stats['position'] ? $ordinal($stats['position']) : '--' }}
                </div>
            </div>

            {{-- Metric 3: Attendance --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="h-10 w-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-1 rounded-md">Attendance</p>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-800 tracking-tight">{{ $att['rate'] }}<span class="text-sm font-bold text-slate-400 ml-1">%</span></div>
            </div>

            @if($hasPaymentGateway)
                {{-- Metric 4: Fees --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="h-10 w-10 rounded-xl {{ $fees['outstanding'] > 0 ? 'bg-rose-50 text-rose-500' : 'bg-emerald-50 text-emerald-500' }} flex items-center justify-center">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-1 rounded-md">Outstanding</p>
                    </div>
                    <div class="mt-4 text-2xl font-black {{ $fees['outstanding'] > 0 ? 'text-rose-600' : 'text-slate-800' }} tracking-tight truncate">₦{{ number_format($fees['outstanding']) }}</div>
                </div>
            @endif
        </div>

        {{-- Grid Main Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- Left Column: Results --}}
            <div class="lg:col-span-6 xl:col-span-7 rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col h-full overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Term Results</h3>
                    @if ($published && $scores->isNotEmpty())
                        <a href="{{ route('results.report-card', ['student' => $child, 'term' => $term, 'session' => $session]) }}"
                           target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download PDF
                        </a>
                    @endif
                </div>

                <div class="flex-1 overflow-auto p-0">
                    @if (! $published)
                        <div class="py-16 text-center">
                            <div class="mx-auto h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 ring-1 ring-slate-100">
                                <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-500">Results pending publication by school admin</span>
                        </div>
                    @elseif ($scores->isEmpty())
                        <div class="py-16 text-center text-sm font-semibold text-slate-400">No scores recorded for this term.</div>
                    @else
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-white border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Subject</th>
                                    <th class="px-6 py-4 text-center">CA</th>
                                    <th class="px-6 py-4 text-center">Exam</th>
                                    <th class="px-6 py-4 text-center">Total</th>
                                    <th class="px-6 py-4 text-right">Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($scores as $score)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-3.5 font-bold text-slate-800">{{ $score->subject?->name }}</td>
                                        <td class="px-6 py-3.5 text-center text-slate-500 font-semibold">{{ ($score->ca1 ?? 0) + ($score->ca2 ?? 0) }}</td>
                                        <td class="px-6 py-3.5 text-center text-slate-500 font-semibold">{{ $score->exam ?? '-' }}</td>
                                        <td class="px-6 py-3.5 text-center">
                                            <div class="inline-flex items-center justify-center rounded-lg bg-slate-100 h-7 w-11 text-sm font-black text-slate-800 border border-slate-200">
                                                {{ $score->total ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-3.5 text-right">
                                            <span class="rounded-md px-2.5 py-1 text-[11px] uppercase font-black {{ $gradeColor($score->grade) }}">
                                                {{ $score->grade ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Right Column: Stacked Attendance & Homework (Clickable!) --}}
            <div class="lg:col-span-6 xl:col-span-5 flex flex-col gap-6">

                {{-- Homework Block with Expandable Accordions --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Homework & Assignments</h3>
                        @if($homework->isNotEmpty())
                            <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">{{ $homework->count() }} due</span>
                        @endif
                    </div>
                    
                    <div class="p-4 flex flex-col gap-3">
                        @if ($homework->isEmpty())
                            <div class="py-8 text-center text-sm font-bold text-slate-400">No active assignments</div>
                        @else
                            @foreach ($homework as $hw)
                                @php
                                    $sub  = $hw->submissions->first();
                                    $done = (bool) $sub;
                                    $late = !$done && $hw->due_date->isPast();
                                    $badge = $done ? 'bg-emerald-100 text-emerald-700' : ($late ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700');
                                @endphp
                                
                                <div x-data="{ expanded: false }" class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition-all shadow-sm">
                                    {{-- Clickable Header --}}
                                    <button @click="expanded = !expanded" class="w-full flex items-start justify-between px-5 py-4 text-left hover:bg-slate-50 transition-colors">
                                        <div class="min-w-0 flex-1 pr-4">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ $hw->subject?->name }}</span>
                                                <span class="text-[10px] font-bold text-slate-400">Due: {{ $hw->due_date->format('d M') }}</span>
                                            </div>
                                            <div class="truncate text-sm font-bold text-slate-800 leading-tight">{{ $hw->title }}</div>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-3">
                                            <div class="h-7 w-7 rounded-full flex items-center justify-center {{ $badge }}">
                                                @if($done) <span class="text-xs font-black">✓</span>
                                                @elseif($late) <span class="text-xs font-black">!</span>
                                                @else <span class="text-xs font-black">?</span>
                                                @endif
                                            </div>
                                            <svg class="h-4 w-4 text-slate-400 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </div>
                                    </button>

                                    {{-- Expandable Content Details --}}
                                    <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/50 p-5 space-y-5">
                                        
                                        {{-- The Question --}}
                                        <div>
                                            <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2 flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Homework Question
                                            </h4>
                                            <div class="text-sm font-medium text-slate-700 bg-white p-4 rounded-xl border border-slate-200 shadow-sm leading-relaxed whitespace-pre-wrap">{{ $hw->content }}</div>
                                        </div>

                                        @if($done)
                                            {{-- The Child's Answer --}}
                                            <div>
                                                <h4 class="text-[10px] font-black uppercase text-brand-500 tracking-wider mb-2 flex items-center gap-1.5">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                    Child's Answer
                                                </h4>
                                                <div class="text-sm font-medium text-slate-700 bg-brand-50/50 p-4 rounded-xl border border-brand-100 shadow-sm leading-relaxed whitespace-pre-wrap">{{ $sub->submission }}</div>
                                            </div>

                                            {{-- Teacher Grading --}}
                                            @if($sub->graded_at)
                                                <div class="flex flex-col sm:flex-row items-stretch gap-4 pt-1">
                                                    <div class="shrink-0 bg-white border border-emerald-100 rounded-xl p-3 flex flex-col items-center justify-center min-w-[5rem] shadow-sm">
                                                        <h4 class="text-[10px] font-black uppercase text-emerald-500 tracking-wider mb-1">Score</h4>
                                                        <div class="text-2xl font-black text-emerald-600">{{ $sub->grade }}</div>
                                                    </div>
                                                    
                                                    @if($sub->feedback)
                                                    <div class="flex-1 bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 shadow-sm">
                                                        <h4 class="text-[10px] font-black uppercase text-emerald-600 tracking-wider mb-2">Teacher Feedback</h4>
                                                        <div class="text-sm font-medium text-emerald-800 leading-relaxed whitespace-pre-wrap">{{ $sub->feedback }}</div>
                                                    </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2 text-xs font-bold text-amber-600 bg-amber-50 px-4 py-3 rounded-xl border border-amber-200">
                                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    Awaiting teacher grading...
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-4 py-3 rounded-xl border border-slate-200">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                                No answer has been submitted by the child yet.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Attendance Summary (Stacked nicely below Homework) --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Attendance Days</h3>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-center">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 py-4 text-center">
                                <div class="text-2xl font-black text-emerald-600">{{ $att['present'] }}</div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-emerald-500 mt-1">Present</div>
                            </div>
                            <div class="rounded-2xl border border-amber-100 bg-amber-50 py-4 text-center">
                                <div class="text-2xl font-black text-amber-600">{{ $att['late'] }}</div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-amber-500 mt-1">Late</div>
                            </div>
                            <div class="rounded-2xl border border-rose-100 bg-rose-50 py-4 text-center">
                                <div class="text-2xl font-black text-rose-600">{{ $att['absent'] }}</div>
                                <div class="text-[10px] uppercase tracking-wider font-bold text-rose-500 mt-1">Absent</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    @endif
</div>
