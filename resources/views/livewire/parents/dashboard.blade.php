@php
    use App\Support\TenantSettings;
    
    $child     = $this->selectedChild;
    $stats     = $this->performanceStats;
    $att       = $this->attendance;
    $fees      = $this->fees;
    $scores    = $this->scores;
    $homework  = $this->homework;
    $recent    = $this->recentAttendance;
    $published = $this->resultsPublished;
    $maxTotal  = $stats['maxTotal'] ?? 100;
    
    $ordinal   = fn($n) => $n . match(true) { 
        $n % 100 >= 11 && $n % 100 <= 13 => 'th', 
        $n % 10 === 1 => 'st', 
        $n % 10 === 2 => 'nd', 
        $n % 10 === 3 => 'rd', 
        default => 'th' 
    };
    
    $gradeColor = fn($g) => match($g) { 
        'A' => 'bg-emerald-100 text-emerald-800 border-emerald-250',
        'B' => 'bg-blue-100 text-blue-800 border-blue-250',
        'C' => 'bg-yellow-100 text-yellow-800 border-yellow-250',
        'D' => 'bg-orange-100 text-orange-800 border-orange-250',
        default => 'bg-red-100 text-red-800 border-red-250' 
    };

    $user = auth()->user();
    $tenant = $user ? $user->tenant : (app()->bound('currentTenant') ? app('currentTenant') : null);
    $hasPaymentGateway = $tenant ? $tenant->activeMarketplaceComponents()->where('slug', 'payment-gateway')->exists() : false;
@endphp

<div class="space-y-6 pb-12">
    
    {{-- Global Dashboard Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-5 sm:p-8 shadow-lg">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-5"></div>
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-black/20 blur-2xl"></div>
        
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 mb-3 backdrop-blur-sm shadow-sm">
                <div class="h-2 w-2 rounded-full bg-indigo-400 shadow-[0_0_8px_rgba(129,140,248,0.8)]"></div>
                <span class="text-[11px] sm:text-xs font-bold text-white tracking-wide uppercase">Parent Portal Control Center</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">Welcome back, {{ auth()->user()->name }}</h1>
            @if($hasPaymentGateway)
                <p class="mt-2 text-xs sm:text-sm text-indigo-150 font-semibold max-w-lg">Track your children's academic progress, attendance, homework, and fees seamlessly from one place.</p>
            @else
                <p class="mt-2 text-xs sm:text-sm text-indigo-150 font-semibold max-w-lg">Track your children's academic progress, attendance, and homework seamlessly from one place.</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 p-1">
                @foreach ($this->children as $c)
                    <button wire:click="selectChild({{ $c->id }})"
                            class="group relative w-full text-left bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_40px_rgb(0,0,0,0.1)] overflow-hidden flex flex-col min-h-[17rem] h-auto">
                        
                        {{-- Top Pattern/Color Strip --}}
                        <div class="h-28 w-full relative border-b border-slate-100 overflow-hidden bg-gradient-to-r from-slate-100 to-slate-50">
                            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#94a3b8 1px, transparent 1px); background-size: 16px 16px;"></div>
                        </div>

                        {{-- Photo --}}
                        <div class="absolute top-12 left-6">
                            @if ($c->passport_photo_url)
                                <img src="{{ $c->passport_photo_url }}" class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white shadow-lg bg-white block" />
                            @else
                                <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 text-4xl font-black text-indigo-600 ring-4 ring-white shadow-lg">
                                    {{ mb_substr($c->first_name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="px-6 pt-12 pb-6 flex-1 flex flex-col justify-between relative bg-white z-10">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors">{{ $c->full_name }}</h3>
                                <p class="text-sm font-semibold text-slate-500 mt-1">{{ $c->schoolClass?->name ?? 'Unassigned' }}</p>
                            </div>
                            
                            <div class="mt-4 flex items-center justify-between border-t border-slate-50 pt-4">
                                <span class="text-[11px] font-black text-slate-400 tracking-widest uppercase bg-slate-50 px-2.5 py-1 rounded-lg">Admin: {{ $c->admission_number }}</span>
                                <div class="h-8 w-8 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                    <svg class="h-4 w-4 text-slate-400 group-hover:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
        {{-- STATE 2: Child Data Layout (Compact, Premium Tabbed Control) --}}
        
        {{-- Very Compact Colorful Header --}}
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 sm:gap-5 bg-slate-800 rounded-3xl p-4 sm:p-6 shadow-md border-0 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9zdmc+')] opacity-10"></div>
            
            <div class="relative z-10 flex items-center gap-3 sm:gap-5 w-full lg:w-auto">
                <button wire:click="$set('selectedChildId', null)" class="shrink-0 h-10 w-10 sm:h-12 sm:w-12 flex items-center justify-center rounded-xl sm:rounded-2xl bg-white/10 text-white hover:bg-white hover:text-slate-800 backdrop-blur-md transition-all shadow-sm border border-white/5" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                
                @if ($child->passport_photo_url)
                    <img src="{{ $child->passport_photo_url }}" class="shrink-0 h-12 w-12 sm:h-16 sm:w-16 rounded-xl sm:rounded-2xl object-cover ring-2 ring-white/50 shadow-sm" />
                @else
                    <div class="shrink-0 flex h-12 w-12 sm:h-16 sm:w-16 items-center justify-center rounded-xl sm:rounded-2xl bg-white/20 backdrop-blur-md text-xl sm:text-2xl font-black text-white ring-2 ring-white/50 shadow-sm">
                        {{ mb_substr($child->first_name, 0, 1) }}
                    </div>
                @endif
                
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl sm:text-2xl font-black text-white leading-tight truncate">{{ $child->full_name }}</h2>
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-1.5 sm:mt-2">
                        <span class="rounded-lg bg-white/20 px-2.5 py-0.5 sm:py-1 text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-white backdrop-blur-md shadow-sm">{{ $child->schoolClass?->name ?? 'Unassigned' }}</span>
                        <span class="rounded-lg bg-black/20 px-2.5 py-0.5 sm:py-1 text-[10px] sm:text-[11px] font-black uppercase tracking-wider text-white backdrop-blur-md shadow-sm">Admin: {{ $child->admission_number }}</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 grid grid-cols-2 sm:flex gap-2.5 sm:gap-3 w-full lg:w-auto">
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

        {{-- Elegant Tabbed Navigation Bar --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 no-scrollbar" style="-webkit-overflow-scrolling: touch; touch-action: pan-x;">
            @php
                $tabs = [
                    'overview'  => ['name' => 'Overview & Bulletin', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
                    'results'   => ['name' => 'Academic Results', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
                    'topics'    => ['name' => 'Curriculum Topics', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>'],
                    'homework'  => ['name' => 'Homework Tracker', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'],
                    'timetable' => ['name' => 'Weekly Timetable', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                ];
                // Only include Fee Ledger tab if Payment Gateway plugin is installed
                if ($hasPaymentGateway) {
                    $tabs['bursary'] = ['name' => 'Fee Ledger', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-6 2h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'];
                }
                $tabs['teachers']  = ['name' => 'Class Teachers', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'];
            @endphp
            @foreach($tabs as $tabKey => $tabVal)
                <button type="button" wire:click="$set('activeTab', '{{ $tabKey }}')"
                        class="flex items-center gap-2 rounded-xl px-3.5 py-2.5 sm:px-4 text-xs font-bold transition-all whitespace-nowrap {{ $activeTab === $tabKey ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-500 hover:text-slate-800 bg-slate-50 hover:bg-slate-100' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                    {!! $tabVal['icon'] !!}
                    {{ $tabVal['name'] }}
                </button>
            @endforeach
        </div>

        {{-- TAB CONTENT RENDERERS --}}

        @if($activeTab === 'overview')
            {{-- Stat Metrics Row --}}
            <div class="grid grid-cols-2 lg:grid-cols-{{ $hasPaymentGateway ? 4 : 3 }} gap-3 sm:gap-4">
                {{-- Metric 1: Average --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center">
                            <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 sm:py-1 rounded-md">Average</p>
                    </div>
                    <div class="mt-3 sm:mt-4 text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ $stats['average'] }}<span class="text-xs sm:text-sm font-bold text-slate-400 ml-1">%</span></div>
                </div>
                
                {{-- Metric 2: Position --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                            <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 sm:py-1 rounded-md">Position</p>
                    </div>
                    <div class="mt-3 sm:mt-4 text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                        {{ $published && $stats['position'] ? $ordinal($stats['position']) : '--' }}
                    </div>
                </div>

                {{-- Metric 3: Attendance --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                            <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 sm:py-1 rounded-md">Attendance</p>
                    </div>
                    <div class="mt-3 sm:mt-4 text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ $att['rate'] }}<span class="text-xs sm:text-sm font-bold text-slate-400 ml-1">%</span></div>
                </div>

                @if($hasPaymentGateway)
                    {{-- Metric 4: Fees --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm flex flex-col justify-between transition-transform hover:-translate-y-1 hover:shadow-md">
                        <div class="flex items-start justify-between">
                            <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl {{ $fees['outstanding'] > 0 ? 'bg-rose-50 text-rose-500' : 'bg-emerald-50 text-emerald-500' }} flex items-center justify-center">
                                <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
                            </div>
                            <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 sm:py-1 rounded-md">Outstanding</p>
                        </div>
                        <div class="mt-3 sm:mt-4 text-xl sm:text-2xl font-black {{ $fees['outstanding'] > 0 ? 'text-rose-600' : 'text-slate-800' }} tracking-tight truncate">₦{{ number_format($fees['outstanding'], 2) }}</div>
                    </div>
                @endif
            </div>

            {{-- Overview Tab Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Left: Academic Standings Gauge and Quick Info --}}
                <div class="lg:col-span-7 space-y-6">
                    {{-- Academic Gauge Card --}}
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col sm:flex-row items-center gap-6">
                        {{-- Circular Gauge --}}
                        <div class="relative flex items-center justify-center shrink-0">
                            @php
                                $pct = min(100, max(0, $stats['average']));
                                $color = $pct >= 70 ? 'stroke-emerald-500' : ($pct >= 50 ? 'stroke-indigo-500' : 'stroke-rose-500');
                                $bg = $pct >= 70 ? 'text-emerald-50' : ($pct >= 50 ? 'text-indigo-50' : 'text-rose-50');
                            @endphp
                            <svg class="w-32 h-32 transform -rotate-90">
                                <circle cx="64" cy="64" r="54" stroke-width="10" stroke="#f1f5f9" fill="transparent" />
                                <circle cx="64" cy="64" r="54" stroke-width="10" class="{{ $color }}" fill="transparent"
                                        stroke-dasharray="339.3"
                                        stroke-dashoffset="{{ 339.3 - (339.3 * $pct / 100) }}"
                                        stroke-linecap="round" />
                            </svg>
                            <div class="absolute flex flex-col items-center justify-center">
                                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ $stats['average'] }}%</span>
                                <span class="text-[9px] uppercase tracking-wider font-extrabold text-slate-400">Average</span>
                            </div>
                        </div>
                        
                        {{-- Standing Details --}}
                        <div class="flex-1 min-w-0 text-center sm:text-left">
                            <h3 class="text-lg font-black text-slate-800 leading-tight">Academic Standing</h3>
                            <p class="text-sm text-slate-500 font-semibold mt-1">
                                @if($stats['average'] >= 70)
                                    Outstanding academic performance! Your child is showing exceptional capability.
                                @elseif($stats['average'] >= 50)
                                    Good progress. With consistent effort, your child can achieve higher results.
                                @else
                                    Additional support recommended. Please coordinate with the subject teachers.
                                @endif
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2 justify-center sm:justify-start">
                                <span class="text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg">Passed: {{ $stats['passed'] }}</span>
                                <span class="text-[10px] font-black uppercase bg-rose-50 text-rose-700 px-2.5 py-1 rounded-lg">Failed: {{ $stats['failed'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- School Announcements Bulletin --}}
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide flex items-center gap-2">
                                <svg class="h-4.5 w-4.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                School Bulletin & Notices
                            </h3>
                            <span class="text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-md">Live Updates</span>
                        </div>

                        <div class="space-y-4">
                            @forelse($this->announcements as $announcement)
                                @php
                                    $prio = $announcement->priority ?? 'Normal';
                                    $prioColor = match($prio) {
                                        'Urgent' => 'bg-rose-105 text-rose-800 border border-rose-200',
                                        'Notice' => 'bg-amber-105 text-amber-800 border border-amber-200',
                                        default => 'bg-slate-105 text-slate-700 border border-slate-200'
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 shadow-sm transition hover:bg-slate-50">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <span class="rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $prioColor }}">
                                            {{ $prio }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-bold">{{ $announcement->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <h4 class="text-sm font-black text-slate-800 leading-snug">{{ $announcement->title }}</h4>
                                    <p class="mt-2.5 text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $announcement->content }}</p>
                                </div>
                            @empty
                                <div class="py-8 text-center text-sm font-bold text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                    No announcements posted recently.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right: Recent Assignments & Attendance summary --}}
                <div class="lg:col-span-5 space-y-6">
                    {{-- Child Attendance quick Card --}}
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col justify-between">
                        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Attendance Summary</h3>
                            <span class="text-[10px] font-black uppercase tracking-wider text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-md">Rate: {{ $att['rate'] }}%</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-2xl bg-emerald-50/50 border border-emerald-100/50 p-3">
                                <div class="text-xl font-black text-emerald-600">{{ $att['present'] }}</div>
                                <div class="text-[9px] uppercase tracking-wider font-extrabold text-emerald-500 mt-1">Present</div>
                            </div>
                            <div class="rounded-2xl bg-amber-50/50 border border-amber-100/50 p-3">
                                <div class="text-xl font-black text-amber-600">{{ $att['late'] }}</div>
                                <div class="text-[9px] uppercase tracking-wider font-extrabold text-amber-500 mt-1">Late</div>
                            </div>
                            <div class="rounded-2xl bg-rose-50/50 border border-rose-100/50 p-3">
                                <div class="text-xl font-black text-rose-600">{{ $att['absent'] }}</div>
                                <div class="text-[9px] uppercase tracking-wider font-extrabold text-rose-500 mt-1">Absent</div>
                            </div>
                        </div>
                    </div>

                    {{-- Next Homework Assignment --}}
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
                        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Recent Assignment</h3>
                            <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-md">Action Required</span>
                        </div>
                        
                        @php
                            $nextHw = $homework->first();
                        @endphp
                        @if($nextHw)
                            @php
                                $sub = $nextHw->submissions->first();
                                $done = (bool) $sub;
                                $late = !$done && $nextHw->due_date->isPast();
                                $statusBadge = $done ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : ($late ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-amber-50 text-amber-700 border border-amber-100');
                            @endphp
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
                                <div class="flex items-center justify-between gap-3 mb-2.5">
                                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-500 bg-slate-150 px-2 py-0.5 rounded-full">{{ $nextHw->subject?->name }}</span>
                                    <span class="rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $statusBadge }}">
                                        {{ $done ? 'Submitted' : ($late ? 'Overdue' : 'Pending') }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-black text-slate-800 leading-snug truncate">{{ $nextHw->title }}</h4>
                                <p class="text-[10px] text-slate-400 font-bold mt-1">Due: {{ $nextHw->due_date->format('l, d M, Y') }}</p>
                                <button type="button" wire:click="$set('activeTab', 'homework')" class="w-full mt-4 btn-primary text-xs py-2 rounded-xl flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/10" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                    View Homework Tracker
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        @else
                            <div class="py-8 text-center text-sm font-bold text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                No homework assigned recently.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($activeTab === 'results')
            {{-- Academic Results Tab --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/50 px-4 sm:px-6 py-4 sm:py-5">
                    <div>
                        <h3 class="text-base font-black text-slate-800 uppercase tracking-wide">Academic Results</h3>
                        <p class="text-xs font-semibold text-slate-500 mt-1">Academic report scores for {{ $child->full_name }} (Term {{ $term }}, {{ $session }})</p>
                    </div>
                    @if ($published && $scores->isNotEmpty())
                        <a href="{{ route('results.report-card', ['student' => $child, 'term' => $term, 'session' => $session]) }}"
                           target="_blank" class="w-full sm:w-auto btn-outline text-xs px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-sm font-bold">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download Report Card PDF
                        </a>
                    @endif
                </div>

                @if (! $published)
                    <div class="py-16 text-center px-4">
                        <div class="mx-auto h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 ring-1 ring-slate-100">
                            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-bold text-slate-500">Results are currently pending school compilation & release.</span>
                    </div>
                @elseif ($scores->isEmpty())
                    <div class="py-16 text-center text-sm font-semibold text-slate-400 px-4">No subject scores recorded for this child.</div>
                @else
                    {{-- Mobile Card View (block md:hidden) --}}
                    <div class="block md:hidden p-4 space-y-3">
                        @foreach ($scores as $score)
                            <div class="rounded-2xl border border-slate-150 bg-slate-50/40 p-4 space-y-3 shadow-2xs">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="font-black text-slate-800 text-sm leading-snug">{{ $score->subject?->name }}</div>
                                    <span class="shrink-0 rounded-lg px-2.5 py-1 text-xs uppercase font-black {{ $gradeColor($score->grade) }}">
                                        {{ $score->grade ?? '-' }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-2 text-center pt-1">
                                    <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 shadow-2xs">
                                        <span class="block text-[9px] font-black uppercase tracking-wider text-slate-400">CA</span>
                                        <span class="text-sm font-bold text-slate-700">{{ ($score->ca1 ?? 0) + ($score->ca2 ?? 0) }}</span>
                                    </div>
                                    <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 shadow-2xs">
                                        <span class="block text-[9px] font-black uppercase tracking-wider text-slate-400">Exam</span>
                                        <span class="text-sm font-bold text-slate-700">{{ $score->exam ?? '-' }}</span>
                                    </div>
                                    <div class="bg-indigo-50/70 rounded-xl p-2.5 border border-indigo-100 shadow-2xs">
                                        <span class="block text-[9px] font-black uppercase tracking-wider text-indigo-500">Total</span>
                                        <span class="text-sm font-black text-indigo-700">{{ $score->total ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Desktop Table View (hidden md:block) --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Subject Title</th>
                                    <th class="px-6 py-4 text-center">Continuous Assessment</th>
                                    <th class="px-6 py-4 text-center">Terminal Exam</th>
                                    <th class="px-6 py-4 text-center">Grand Total</th>
                                    <th class="px-6 py-4 text-right">Letter Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($scores as $score)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-slate-800">{{ $score->subject?->name }}</td>
                                        <td class="px-6 py-4 text-center text-slate-500 font-semibold">{{ ($score->ca1 ?? 0) + ($score->ca2 ?? 0) }}</td>
                                        <td class="px-6 py-4 text-center text-slate-500 font-semibold">{{ $score->exam ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="inline-flex items-center justify-center rounded-lg bg-slate-100 h-8 w-12 text-sm font-black text-slate-800 border border-slate-200">
                                                {{ $score->total ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="rounded-md px-2.5 py-1 text-[11px] uppercase font-black {{ $gradeColor($score->grade) }}">
                                                {{ $score->grade ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Grading Scheme Key --}}
                @if ($published && $scores->isNotEmpty())
                    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-wrap gap-4 items-center justify-center text-xs font-bold text-slate-500">
                        <span class="text-[10px] font-black uppercase text-slate-400 mr-2">Grading Scheme:</span>
                        <div class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-emerald-500"></span> A (70-100) Excellent</div>
                        <div class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-blue-500"></span> B (60-69) Very Good</div>
                        <div class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-yellow-500"></span> C (50-59) Good</div>
                        <div class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-orange-500"></span> D (40-49) Pass</div>
                        <div class="flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-red-500"></span> F (0-39) Fail</div>
                    </div>
                @endif
            </div>
        @endif

        @if($activeTab === 'topics')
            {{-- Curriculum Topics & Scheme of Work Tab --}}
            <div class="space-y-6">
                {{-- Overview Header Card --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 sm:gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Scheme of Work</span>
                            </div>
                            <h3 class="text-lg sm:text-xl font-black text-slate-800 tracking-tight">Curriculum Syllabus & Topics</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-1">
                                Tracking topics taught and planned for {{ $child->schoolClass?->name ?? 'Class' }} — Term {{ $term == 1 ? 'One' : ($term == 2 ? 'Two' : 'Three') }} ({{ $session }})
                            </p>
                        </div>

                        {{-- Progress & Metrics Badges (Responsive Grid) --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3 w-full lg:w-auto">
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-2.5 sm:p-3 text-center">
                                <div class="text-[9px] font-black uppercase tracking-wider text-slate-400">Total</div>
                                <div class="text-base sm:text-lg font-black text-slate-800 mt-0.5">{{ $this->topicStats['total'] }}</div>
                            </div>
                            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-2.5 sm:p-3 text-center">
                                <div class="text-[9px] font-black uppercase tracking-wider text-emerald-600">Covered</div>
                                <div class="text-base sm:text-lg font-black text-emerald-700 mt-0.5">{{ $this->topicStats['completed'] }}</div>
                            </div>
                            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-2.5 sm:p-3 text-center">
                                <div class="text-[9px] font-black uppercase tracking-wider text-amber-600">In Progress</div>
                                <div class="text-base sm:text-lg font-black text-amber-700 mt-0.5">{{ $this->topicStats['inProgress'] }}</div>
                            </div>
                            <div class="bg-slate-100 border border-slate-200 rounded-2xl p-2.5 sm:p-3 text-center">
                                <div class="text-[9px] font-black uppercase tracking-wider text-slate-500">Upcoming</div>
                                <div class="text-base sm:text-lg font-black text-slate-700 mt-0.5">{{ $this->topicStats['upcoming'] }}</div>
                            </div>
                            <div class="col-span-2 sm:col-span-1 bg-indigo-50 border border-indigo-100 rounded-2xl p-2.5 sm:p-3 text-center">
                                <div class="text-[9px] font-black uppercase tracking-wider text-indigo-600">Completed</div>
                                <div class="text-base sm:text-lg font-black text-indigo-700 mt-0.5">{{ $this->topicStats['percent'] }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Linear Progress Bar --}}
                    @if($this->topicStats['total'] > 0)
                        <div class="mt-5 pt-4 sm:mt-6 sm:pt-5 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-2">
                                <span class="truncate mr-2">Progress: {{ $this->topicStats['completed'] }} of {{ $this->topicStats['total'] }} topics covered</span>
                                <span class="font-black text-indigo-600 shrink-0">{{ $this->topicStats['percent'] }}%</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all duration-500" style="width: {{ $this->topicStats['percent'] }}%"></div>
                            </div>
                        </div>
                    @endif

                    @php
                        $activeDoc = $this->curriculumDocument;
                        $classDocs = $this->classCurriculumDocuments;
                    @endphp
                    @if($activeDoc)
                        <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-indigo-50/50 p-4 rounded-2xl">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-800 truncate">Official Syllabus: {{ $activeDoc->file_name }}</h4>
                                    <p class="text-[11px] text-slate-500 font-semibold truncate">{{ $activeDoc->formatted_file_size }} • By {{ $activeDoc->uploader?->name ?? 'Subject Teacher' }}</p>
                                </div>
                            </div>
                            <a href="{{ route('curriculum.document.download', $activeDoc) }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 text-xs font-black shadow-xs transition active:scale-95 shrink-0">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Syllabus ({{ strtoupper($activeDoc->file_type) }})
                            </a>
                        </div>
                    @elseif($classDocs->isNotEmpty() && is_null($selectedTopicSubjectId))
                        <div class="mt-5 pt-4 border-t border-slate-100 space-y-2">
                            <div class="text-[11px] font-black uppercase tracking-wider text-slate-400">Available Syllabus Documents:</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($classDocs as $cDoc)
                                    <a href="{{ route('curriculum.document.download', $cDoc) }}"
                                       class="inline-flex items-center gap-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-100 px-3 py-1.5 text-xs font-bold transition">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>{{ $cDoc->subject?->name }}: {{ strtoupper($cDoc->file_type) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Subject Filter Pill Navigation --}}
                <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar" style="-webkit-overflow-scrolling: touch; touch-action: pan-x;">
                    <button type="button" wire:click="$set('selectedTopicSubjectId', null)"
                            class="rounded-xl px-4 py-2 text-xs font-black transition-all whitespace-nowrap {{ is_null($selectedTopicSubjectId) ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        All Subjects ({{ $this->childSubjects->count() }})
                    </button>
                    @foreach($this->childSubjects as $cSub)
                        <button type="button" wire:click="$set('selectedTopicSubjectId', {{ $cSub->id }})"
                                class="rounded-xl px-4 py-2 text-xs font-black transition-all whitespace-nowrap {{ (int)$selectedTopicSubjectId === (int)$cSub->id ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                            {{ $cSub->name }}
                        </button>
                    @endforeach
                </div>

                {{-- Topic Cards List --}}
                @if($this->subjectTopics->isEmpty())
                    <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 mb-4">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h4 class="text-base font-black text-slate-800">No Curriculum Topics Published Yet</h4>
                        <p class="text-xs text-slate-500 font-semibold max-w-md mx-auto mt-1">
                            @if($selectedTopicSubjectId)
                                No topics have been recorded for the selected subject in Term {{ $term == 1 ? 'One' : ($term == 2 ? 'Two' : 'Three') }}.
                            @else
                                Teachers have not uploaded the scheme of work topics for this term yet. Once posted, weekly lesson outlines and progress will appear here.
                            @endif
                        </p>
                        @if($selectedTopicSubjectId)
                            <button type="button" wire:click="$set('selectedTopicSubjectId', null)" class="mt-4 inline-flex items-center gap-1.5 text-xs font-black text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-1.5 rounded-xl transition">
                                Clear Subject Filter
                            </button>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($this->subjectTopics as $topic)
                            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:border-indigo-200 hover:shadow-md transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($topic->week_number)
                                                <span class="rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider">
                                                    Week {{ $topic->week_number }}
                                                </span>
                                            @else
                                                <span class="rounded-lg bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider">
                                                    General
                                                </span>
                                            @endif
                                            <span class="rounded-lg bg-slate-50 text-slate-700 border border-slate-200 px-2.5 py-1 text-[10px] font-bold">
                                                {{ $topic->subject?->name ?? 'Subject' }}
                                            </span>
                                        </div>

                                        {{-- Status Badge --}}
                                        @if($topic->status === 'completed')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-black text-emerald-700">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Covered
                                            </span>
                                        @elseif($topic->status === 'in_progress')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-[10px] font-black text-amber-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                In Progress
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[10px] font-black text-slate-600">
                                                <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Upcoming
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="text-base font-black text-slate-800 leading-snug">{{ $topic->title }}</h4>

                                    @if($topic->learning_objectives)
                                        <div class="mt-3.5 rounded-2xl bg-slate-50/70 border border-slate-150 p-3.5">
                                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5 flex items-center gap-1">
                                                <svg class="h-3 w-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Learning Objectives
                                            </div>
                                            <div class="text-xs text-slate-600 font-medium leading-relaxed whitespace-pre-wrap">{{ $topic->learning_objectives }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if($activeTab === 'homework')
            {{-- Homework Tracker Tab --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Homework & Assignments</h3>
                </div>
                
                <div class="p-6 flex flex-col gap-4">
                    @if ($homework->isEmpty())
                        <div class="py-16 text-center text-sm font-bold text-slate-400 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            No assignments active or graded for this term.
                        </div>
                    @else
                        @foreach ($homework as $hw)
                            @php
                                $sub  = $hw->submissions->first();
                                $done = (bool) $sub;
                                $late = !$done && $hw->due_date->isPast();
                                $badge = $done ? 'bg-emerald-100 text-emerald-700' : ($late ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700');
                            @endphp
                            
                            <div x-data="{ expanded: false }" class="rounded-2xl border border-slate-200 bg-white overflow-hidden transition shadow-sm animate-fade-in">
                                {{-- Clickable Header --}}
                                <button @click="expanded = !expanded" class="w-full flex items-start justify-between px-5 py-4 text-left hover:bg-slate-50/80 transition-colors">
                                    <div class="min-w-0 flex-1 pr-4">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ $hw->subject?->name }}</span>
                                            <span class="text-[10px] font-bold text-slate-400">Due: {{ $hw->due_date->format('M d, Y') }}</span>
                                        </div>
                                        <div class="text-base font-black text-slate-800 leading-tight">{{ $hw->title }}</div>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-3">
                                        <span class="text-[10px] font-black uppercase rounded-lg border px-2.5 py-1 {{ $badge }}">
                                            {{ $done ? '✓ Completed' : ($late ? '! Overdue' : '? Pending') }}
                                        </span>
                                        <svg class="h-4 w-4 text-slate-400 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </button>

                                {{-- Expandable Content Details --}}
                                <div x-show="expanded" x-collapse x-cloak class="border-t border-slate-100 bg-slate-50/50 p-5 space-y-5">
                                    {{-- Homework Prompt --}}
                                    <div>
                                        <h4 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2 flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Assignment Prompt
                                        </h4>
                                        <div class="text-sm font-medium text-slate-700 bg-white p-4 rounded-xl border border-slate-200 shadow-sm leading-relaxed whitespace-pre-wrap">{{ $hw->content }}</div>
                                    </div>

                                    @if($done)
                                        {{-- Student Answer --}}
                                        <div>
                                            <h4 class="text-[10px] font-black uppercase text-indigo-500 tracking-wider mb-2 flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                Submitted Answer
                                            </h4>
                                            <div class="text-sm font-medium text-slate-700 bg-indigo-50/30 p-4 rounded-xl border border-indigo-100 shadow-sm leading-relaxed whitespace-pre-wrap">{{ $sub->submission }}</div>
                                        </div>

                                        {{-- Grade --}}
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
                                                Grading pending teacher verification...
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-100 px-4 py-3 rounded-xl border border-slate-200">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            This assignment has not been submitted by the child yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endif

        @if($activeTab === 'timetable')
            {{-- Timetable Tab --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col"
                 x-data="{ activeDay: (new Date().getDay() >= 1 && new Date().getDay() <= 5) ? new Date().getDay() : 1 }">
                <div class="border-b border-slate-100 bg-slate-50/50 px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Weekly Timetable Schedule</h3>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">Periods and subject allocations for {{ $child->schoolClass?->name ?? 'Class' }}</p>
                    </div>
                    @if($child->class_id)
                        <a href="{{ route('timetable.pdf', ['class_id' => $child->class_id, 'section_id' => $child->section_id]) }}"
                           target="_blank"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 text-xs font-black transition-colors shadow-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download PDF Timetable
                        </a>
                    @endif
                </div>
                
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    @php
                        $daysMap = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
                        $timetable = $this->timetable;
                    @endphp
                    @if ($timetable->isEmpty())
                        <div class="py-16 text-center text-sm font-bold text-slate-400 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            No class timetable schedule recorded for this class.
                        </div>
                    @else
                        {{-- Mobile Day Filter Pills (Phones only) --}}
                        <div class="sm:hidden flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar" style="-webkit-overflow-scrolling: touch; touch-action: pan-x;">
                            @foreach($daysMap as $dayNum => $dayName)
                                <button type="button" @click="activeDay = {{ $dayNum }}"
                                        :class="activeDay === {{ $dayNum }} ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                        class="rounded-xl px-3.5 py-1.5 text-xs font-black transition whitespace-nowrap">
                                    {{ substr($dayName, 0, 3) }}
                                </button>
                            @endforeach
                            <button type="button" @click="activeDay = 'all'"
                                    :class="activeDay === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="rounded-xl px-3.5 py-1.5 text-xs font-black transition whitespace-nowrap">
                                All Days
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                            @foreach($daysMap as $dayNum => $dayName)
                                @php
                                    $dayEntries = $timetable->where('day_of_week', $dayNum)->sortBy('starts_at');
                                @endphp
                                <div :class="{ 'hidden': activeDay !== 'all' && activeDay !== {{ $dayNum }} }"
                                     class="sm:!flex rounded-2xl border border-slate-200 bg-slate-50/20 p-4 shadow-sm flex-col h-full">
                                    <div class="border-b border-slate-100 pb-2 mb-3">
                                        <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                            {{ $dayName }}
                                        </h4>
                                    </div>
                                    <div class="space-y-3 flex-1">
                                        @forelse($dayEntries as $entry)
                                            <div class="rounded-xl border border-slate-150 bg-white p-3 shadow-xs transition hover:border-slate-350">
                                                <div class="text-[9px] font-black uppercase text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md inline-block mb-1.5">
                                                    {{ $entry->starts_at }} - {{ $entry->ends_at }}
                                                </div>
                                                <div class="text-xs font-black text-slate-800 truncate">{{ $entry->subject?->name }}</div>
                                                @if($entry->teacher)
                                                    <div class="text-[9px] text-slate-500 font-bold mt-1 truncate flex items-center gap-1">
                                                        <svg class="h-3 w-3 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        <span class="truncate">{{ $entry->teacher->name }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-[10px] text-slate-400 font-bold py-6 text-center italic">No periods allocated.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($activeTab === 'bursary' && $hasPaymentGateway)
            {{-- Bursary & Payment History Tab â€” only visible when Payment Gateway plugin is installed --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                {{-- Left: Transaction History Log --}}
                <div class="lg:col-span-8 rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                    <div class="border-b border-slate-100 bg-slate-50/50 px-4 sm:px-6 py-4 sm:py-5">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Child Account Ledger</h3>
                        <p class="text-xs font-semibold text-slate-400 mt-1">Audit logs of all charges, invoice bills, and payments received</p>
                    </div>
                    
                    @php
                        $txs = $this->transactions;
                    @endphp
                    @if ($txs->isEmpty())
                        <div class="py-16 text-center text-sm font-semibold text-slate-400 px-4">No account ledger entries found.</div>
                    @else
                        {{-- Mobile Transaction Cards (block md:hidden) --}}
                        <div class="block md:hidden p-4 space-y-3">
                            @foreach ($txs as $tx)
                                <div class="rounded-2xl border border-slate-150 bg-slate-50/40 p-4 space-y-2.5 shadow-2xs">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-xs font-black text-slate-800 tracking-wide">{{ $tx->receipt_number ?? 'INVOICE' }}</span>
                                            <span class="block text-[10px] font-semibold text-slate-400 mt-0.5">{{ $tx->date?->format('M d, Y') }}</span>
                                        </div>
                                        <span class="text-sm font-black {{ $tx->type === 'Payment' ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $tx->type === 'Payment' ? '-' : '+' }}₦{{ number_format($tx->amount, 2) }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                                        <span class="font-bold text-slate-600 text-xs">{{ $tx->category }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $tx->type === 'Payment' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                                {{ $tx->type }}
                                            </span>
                                            @if($tx->installment_plan && $tx->installment_plan !== 'full')
                                                <span class="text-[9px] font-bold bg-violet-50 text-violet-700 border border-violet-200 px-2 py-0.5 rounded-full">
                                                    @if($tx->installment_plan === 'two_installments')
                                                        2-Part #{{ $tx->installment_number }}
                                                    @elseif($tx->installment_plan === 'monthly')
                                                        Monthly #{{ $tx->installment_number }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop Table View (hidden md:block) --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Transaction Date</th>
                                        <th class="px-6 py-4">Reference</th>
                                        <th class="px-6 py-4">Type</th>
                                        <th class="px-6 py-4">Category</th>
                                        <th class="px-6 py-4">Plan</th>
                                        <th class="px-6 py-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($txs as $tx)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 font-bold text-slate-500 text-xs">{{ $tx->date?->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 font-black text-slate-800 text-xs">{{ $tx->receipt_number ?? 'INVOICE' }}</td>
                                            <td class="px-6 py-4">
                                                <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $tx->type === 'Payment' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                                    {{ $tx->type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-slate-600 text-xs truncate max-w-[120px]">{{ $tx->category }}</td>
                                            <td class="px-6 py-4">
                                                @if($tx->installment_plan && $tx->installment_plan !== 'full')
                                                    <span class="text-[9px] font-bold bg-violet-50 text-violet-700 border border-violet-200 px-2 py-0.5 rounded-full">
                                                        @if($tx->installment_plan === 'two_installments')
                                                            2-Part #{{ $tx->installment_number }}
                                                        @elseif($tx->installment_plan === 'monthly')
                                                            Monthly #{{ $tx->installment_number }}
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full">Full</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right font-black text-sm {{ $tx->type === 'Payment' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $tx->type === 'Payment' ? '-' : '+' }}₦{{ number_format($tx->amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Right: Pay Now CTA + Bank fallback --}}
                <div class="lg:col-span-4 space-y-6">
                    {{-- Pay Now button (online gateway) --}}
                    <div class="rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50 to-indigo-50/60 p-6 shadow-sm flex flex-col">
                        <h4 class="text-sm font-black text-violet-800 uppercase tracking-wide mb-2">Pay School Fees Online</h4>
                        <p class="text-xs text-violet-700 font-semibold mb-5 leading-relaxed">
                            Pay your child's outstanding tuition using a debit or credit card, with installment plan options set by the school.
                        </p>
                        <a href="{{ route('parent.pay') }}" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 text-sm transition duration-200 shadow-md shadow-violet-500/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Go to Payment Center
                        </a>
                    </div>

                    {{-- Direct Bank Transfer fallback (always visible) --}}
                    <div class="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50/60 p-6 shadow-sm flex flex-col">
                        <h4 class="text-sm font-black text-amber-800 uppercase tracking-wide mb-3">Direct Bank Transfer</h4>
                        <p class="text-xs text-amber-700 font-semibold mb-4 leading-relaxed">
                            For convenient tuitions, you can pay directly to the school bank account details listed below. Kindly include the Admission Number as remarks:
                        </p>
                        @php
                            $settings = json_decode(file_exists(TenantSettings::settingsPath()) ? file_get_contents(TenantSettings::settingsPath()) : '{}', true) ?? [];
                        @endphp
                        <div class="bg-white border border-amber-200 rounded-2xl p-4 space-y-3 shadow-xs">
                            <div>
                                <span class="text-[9px] font-black uppercase text-amber-500 tracking-wider block">Bank Name</span>
                                <span class="text-sm font-black text-slate-800">{{ $settings['rc_school_fees_bank_name'] ?? 'UBA Bank' }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase text-amber-500 tracking-wider block">Account Number</span>
                                <span class="text-base font-black text-slate-800 tracking-wider">{{ $settings['rc_school_fees_account_number'] ?? '1023456789' }}</span>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase text-amber-500 tracking-wider block">Account Name</span>
                                <span class="text-sm font-black text-slate-800 leading-snug">{{ $settings['rc_school_fees_account_name'] ?? 'School Tuition Fund' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($activeTab === 'teachers')
            {{-- Class Teachers Directory Tab (Direct WhatsApp Connection) --}}
            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                <div class="border-b border-slate-100 bg-slate-50/70 px-4 sm:px-6 py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/></svg>
                            </span>
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">Class Teachers & Instructors</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Connect directly with your child's teachers via WhatsApp for academic questions and guidance.</p>
                    </div>
                    @php
                        $teachers = $this->classTeachers;
                    @endphp
                    @if ($teachers->isNotEmpty())
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/60 self-start sm:self-auto">
                            <span>{{ $teachers->count() }}</span>
                            <span class="text-slate-500">{{ Str::plural('Teacher', $teachers->count()) }}</span>
                        </div>
                    @endif
                </div>
                
                <div class="p-4 sm:p-6">
                    @if ($teachers->isEmpty())
                        <div class="py-16 text-center text-sm font-bold text-slate-400 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            <svg class="mx-auto h-10 w-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            No teachers or subject allocations recorded for this child's class yet.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            @foreach ($teachers as $teacher)
                                @php
                                    $parentName = auth()->user()?->name ?? 'Parent';
                                    $studentName = $child->full_name ?? ($child->first_name . ' ' . $child->last_name);
                                    $className = $child->schoolClass?->name ?? 'Class';
                                    $schoolName = \App\Support\TenantSettings::get('school_name') ?? config('app.name', 'AcademyHub');
                                    $waGreeting = "Hello {$teacher->name}, I am reaching out as the parent of {$studentName} ({$className}) at {$schoolName} regarding their academic performance.";
                                    $waUrl = $teacher->getWhatsappChatUrl($waGreeting);
                                    $cleanPhone = $teacher->clean_whatsapp_phone;
                                    $formattedPhone = $teacher->formatted_whatsapp_phone;
                                    $subjects = $teacher->assigned_subjects ?? [];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-start gap-3 sm:gap-3.5">
                                            @if ($teacher->profile_photo)
                                                <img src="{{ $teacher->profile_photo_url }}" class="h-12 w-12 rounded-2xl object-cover ring-2 ring-slate-100 shadow-sm shrink-0" alt="{{ $teacher->name }}" />
                                            @else
                                                <div class="shrink-0 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-lg font-black text-indigo-700 ring-2 ring-slate-100 shadow-sm">
                                                    {{ mb_substr($teacher->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-1">
                                                    <h4 class="text-base font-black text-slate-800 leading-tight truncate">{{ $teacher->name }}</h4>
                                                    @if($teacher->is_class_teacher)
                                                        <span class="inline-flex shrink-0 items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200/60">Class Teacher</span>
                                                    @endif
                                                </div>
                                                
                                                {{-- Assigned Subjects Pills --}}
                                                <div class="flex flex-wrap gap-1 mt-1.5">
                                                    @forelse($subjects as $subj)
                                                        <span class="inline-block text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md">
                                                            {{ $subj }}
                                                        </span>
                                                    @empty
                                                        <span class="inline-block text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">
                                                            Subject Teacher
                                                        </span>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        {{-- WhatsApp Contact Number Banner --}}
                                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                            <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/></svg>
                                                WhatsApp:
                                            </span>
                                            @if ($formattedPhone)
                                                <span class="font-black text-slate-700 tracking-wide">{{ $formattedPhone }}</span>
                                            @else
                                                <span class="font-bold text-slate-400 italic">Not Registered</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Primary Action: 1-Click WhatsApp Button --}}
                                    <div class="mt-4">
                                        @if ($waUrl)
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer"
                                               class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-black shadow-sm shadow-emerald-500/25 transition-all">
                                                <svg class="h-4 w-4 fill-current shrink-0" viewBox="0 0 24 24">
                                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                                                </svg>
                                                <span>Chat on WhatsApp</span>
                                            </a>
                                        @else
                                            <div class="w-full text-center py-2 px-3 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold border border-slate-200/60 min-h-[44px] flex items-center justify-center">
                                                Contact via School Frontdesk
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

    @endif
</div>
