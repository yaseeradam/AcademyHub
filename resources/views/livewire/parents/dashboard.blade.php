@php
    use App\Support\TenantSettings;
    
    $child     = $this->selectedChild;
    $stats     = $this->performanceStats;
    $att       = $this->attendance;
    $fees      = $this->fees;
    $scores    = $this->scores;
    $homework  = $this->homework;
    $recent    = $this->recentAttendance;
    $todayAtt  = $this->todayAttendance;
    $calData   = $this->attendanceCalendarData;
    $attHist   = $this->attendanceHistory;
    $absenceRequests = $this->childAbsenceRequests;
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
    
    @if (! $child)
        @php
            $hour = (int) now()->format('H');
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            $parentFirstName = explode(' ', trim(auth()->user()->name))[0] ?? auth()->user()->name;
            $childrenCards = $this->childrenCardsData;
            $summary = $this->familySummary;
            $updates = $this->recentFamilyUpdates;
        @endphp

        {{-- SCREEN 1: Top Family Dashboard Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span>{{ $greeting }}, {{ $parentFirstName }}</span>
                    <span class="inline-block transform hover:rotate-12 transition-transform">👋</span>
                </h1>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                    Welcome to your family dashboard. Here's an overview of your children and school activities.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <div class="inline-flex items-center gap-2 rounded-2xl bg-white border border-slate-200/90 px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                    <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-slate-400 font-semibold">Academic Session:</span>
                    <select wire:model.live="session" class="bg-transparent text-xs font-black text-slate-900 border-0 p-0 pr-4 focus:ring-0 cursor-pointer">
                        @foreach($this->availableSessions as $sName)
                            <option value="{{ $sName }}" {{ $session === $sName ? 'selected' : '' }}>{{ $sName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SCREEN 1: Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column: My Children Section (8 Cols) --}}
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight leading-none">My Children</h2>
                            <p class="text-xs text-slate-400 font-medium mt-1">Manage and view the progress of your children</p>
                        </div>
                    </div>

                    {{-- + Add Child CTA Button --}}
                    <button type="button" wire:click="openAddChildModal"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs active:scale-95 transition"
                            style="background-color: #4f46e5; color: #ffffff;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Child</span>
                    </button>
                </div>

                @if (empty($childrenCards))
                    <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-white py-16 text-center shadow-xs">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-black text-slate-700">No children linked</h3>
                        <p class="mt-1 text-xs text-slate-500">Click "+ Add Child" above or contact administration to link your student wards.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        @foreach ($childrenCards as $c)
                            <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs hover:shadow-md hover:border-slate-300 transition duration-200 flex flex-col justify-between group">
                                {{-- Top: Avatar & Information --}}
                                <div>
                                    <div class="flex items-start gap-3.5">
                                        @if ($c['photo_url'])
                                            <img src="{{ $c['photo_url'] }}" alt="{{ $c['name'] }}" class="h-14 w-14 rounded-2xl object-cover ring-2 ring-slate-100 shadow-2xs bg-slate-50 shrink-0" style="width: 56px; height: 56px; max-width: 56px; max-height: 56px; object-fit: cover;" />
                                        @else
                                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center font-black text-xl shadow-2xs ring-2 ring-indigo-50 shrink-0" style="width: 56px; height: 56px;">
                                                {{ mb_substr($c['first_name'], 0, 1) }}
                                            </div>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <h3 class="text-base font-black text-slate-800 truncate group-hover:text-indigo-600 transition-colors">
                                                    {{ $c['name'] }}
                                                </h3>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0" style="background-color: #def7ec; color: #03543f;">
                                                    {{ $c['status'] }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] font-semibold text-slate-400 mt-1 truncate">
                                                ADM: <span class="text-slate-600 font-bold">{{ $c['admission_number'] }}</span> &nbsp;|&nbsp; {{ $c['class_name'] }}{{ $c['section_name'] ? ' (' . $c['section_name'] . ')' : '' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- 3 Horizontal Stat Badges (Attendance Circular Ring, Academic Avg Checkmark, Fee Coin) --}}
                                    <div class="grid grid-cols-3 gap-2 py-3.5 my-3.5 border-y border-slate-100">
                                        {{-- 1. Attendance Ring --}}
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-10 h-10 flex items-center justify-center shrink-0">
                                                <svg class="w-10 h-10 -rotate-90" viewBox="0 0 36 36">
                                                    <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none"
                                                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                    <path class="{{ $c['attendance_rate'] >= 85 ? 'text-emerald-500' : ($c['attendance_rate'] >= 70 ? 'text-amber-500' : 'text-rose-500') }}"
                                                          stroke-dasharray="{{ $c['attendance_rate'] }}, 100"
                                                          stroke-width="3" stroke-linecap="round" stroke="currentColor" fill="none"
                                                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                </svg>
                                                <span class="absolute text-[9px] font-black text-slate-800">{{ $c['attendance_rate'] }}%</span>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-slate-800 leading-none">{{ $c['attendance_rate'] }}%</div>
                                                <div class="text-[10px] font-semibold text-slate-400 mt-0.5">Attendance</div>
                                                <div class="text-[9px] font-medium text-slate-400 leading-none">(This term)</div>
                                            </div>
                                        </div>

                                        {{-- 2. Academic Average Checkmark --}}
                                        <div class="flex items-center gap-2 border-l border-slate-100 pl-2">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-slate-800 leading-none">{{ $c['academic_average'] > 0 ? $c['academic_average'] . '%' : '--' }}</div>
                                                <div class="text-[10px] font-semibold text-slate-400 mt-0.5">Academic Average</div>
                                            </div>
                                        </div>

                                        {{-- 3. Fee Balance Coin --}}
                                        <div class="flex items-center gap-2 border-l border-slate-100 pl-2">
                                            <div class="w-8 h-8 rounded-full {{ $c['fee_balance'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-black text-slate-800 leading-none">₦{{ number_format($c['fee_balance']) }}</div>
                                                <div class="text-[10px] font-semibold text-slate-400 mt-0.5">Fee Balance</div>
                                                @if($c['fee_balance'] <= 0)
                                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">Clear</span>
                                                @else
                                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">Pay Now</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Bottom: View Dashboard CTA & More Options --}}
                                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-100">
                                    <button type="button" wire:click="selectChild({{ $c['id'] }})"
                                            class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition duration-200 shadow-2xs active:scale-[0.98]"
                                            style="background-color: #4f46e5; color: #ffffff;">
                                        <span>View Dashboard</span>
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </button>
                                    <div class="relative" x-data="{ open: false }">
                                        <button type="button" @click="open = !open"
                                                class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                            <span>&bull;&bull;&bull; More</span>
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-cloak
                                             class="absolute right-0 bottom-full mb-2 w-48 rounded-2xl bg-white border border-slate-200 shadow-xl py-1 z-30 text-xs font-semibold text-slate-700">
                                            <button type="button" wire:click="selectChild({{ $c['id'] }}); $set('activeTab', 'results')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2">
                                                <span>View Results</span>
                                            </button>
                                            <button type="button" wire:click="selectChild({{ $c['id'] }}); $set('activeTab', 'attendance')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2">
                                                <span>Attendance History</span>
                                            </button>
                                            <button type="button" wire:click="selectChild({{ $c['id'] }}); $set('activeTab', 'homework')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2">
                                                <span>Homework Tasks</span>
                                            </button>
                                            <button type="button" wire:click="selectChild({{ $c['id'] }}); $set('activeTab', 'bursary')" class="w-full text-left px-3.5 py-2 hover:bg-slate-50 flex items-center gap-2">
                                                <span>Fee Ledger</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right Column: Family Summary (4 Cols) --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Family Summary Card --}}
                <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs">
                    <div class="flex items-start gap-2.5 pb-4 border-b border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-800 leading-none">Family Summary</h3>
                            <p class="text-xs text-slate-400 font-medium mt-1">Quick overview of your children</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4">
                        {{-- 1. Overall Attendance --}}
                        <div class="rounded-2xl bg-sky-50 p-3.5 flex flex-col justify-between border border-sky-200/80 shadow-2xs" style="background-color: #f0f9ff;">
                            <div class="w-8 h-8 rounded-xl bg-white text-sky-600 flex items-center justify-center shadow-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-sky-600 leading-none">{{ $summary['overall_attendance'] }}%</div>
                                <div class="text-[11px] font-semibold text-slate-500 mt-1">Overall Attendance</div>
                            </div>
                        </div>

                        {{-- 2. Average Performance --}}
                        <div class="rounded-2xl bg-indigo-50 p-3.5 flex flex-col justify-between border border-indigo-200/80 shadow-2xs" style="background-color: #f5f3ff;">
                            <div class="w-8 h-8 rounded-xl bg-white text-indigo-600 flex items-center justify-center shadow-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-indigo-600 leading-none">{{ $summary['average_performance'] > 0 ? $summary['average_performance'] . '%' : '--' }}</div>
                                <div class="text-[11px] font-semibold text-slate-500 mt-1">Average Performance</div>
                            </div>
                        </div>

                        {{-- 3. Total Outstanding Fees --}}
                        <div class="rounded-2xl bg-emerald-50 p-3.5 flex flex-col justify-between border border-emerald-200/80 shadow-2xs" style="background-color: #ecfdf5;">
                            <div class="w-8 h-8 rounded-xl bg-white text-emerald-600 flex items-center justify-center shadow-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                            </div>
                            <div class="mt-3">
                                <div class="text-xl sm:text-2xl font-black text-emerald-600 leading-none truncate">₦{{ number_format($summary['total_outstanding_fees']) }}</div>
                                <div class="text-[11px] font-semibold text-slate-500 mt-1 truncate">Total Outstanding Fees</div>
                            </div>
                        </div>

                        {{-- 4. Pending Homework --}}
                        <div class="rounded-2xl bg-purple-50 p-3.5 flex flex-col justify-between border border-purple-200/80 shadow-2xs" style="background-color: #faf5ff;">
                            <div class="w-8 h-8 rounded-xl bg-white text-purple-600 flex items-center justify-center shadow-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="mt-3">
                                <div class="text-2xl font-black text-purple-600 leading-none">{{ $summary['total_pending_homework'] }}</div>
                                <div class="text-[11px] font-semibold text-slate-500 mt-1">Pending Homework</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SCREEN 1 Bottom: Recent Updates Horizontal Section --}}
        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-xs mt-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-slate-800 leading-none">Recent Updates</h3>
                        <p class="text-xs text-slate-400 font-medium mt-1">Important notices and activities</p>
                    </div>
                </div>
                <a href="#updates" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">
                    <span>View All</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 mt-4">
                @forelse ($updates as $up)
                    @php
                        $cardStyle = match($up['type']) {
                            'fee'      => 'border-rose-100 bg-gradient-to-br from-rose-50/90 via-rose-50/50 to-white hover:border-rose-200',
                            'homework' => 'border-sky-100 bg-gradient-to-br from-sky-50/90 via-sky-50/50 to-white hover:border-sky-200',
                            default    => 'border-emerald-100 bg-gradient-to-br from-emerald-50/90 via-emerald-50/50 to-white hover:border-emerald-200',
                        };
                        $iconBox = match($up['type']) {
                            'fee'      => 'bg-white text-rose-600',
                            'homework' => 'bg-white text-sky-600',
                            default    => 'bg-white text-emerald-600',
                        };
                    @endphp
                    <div class="rounded-2xl border {{ $cardStyle }} p-3.5 flex items-start gap-3 transition shadow-2xs">
                        <div class="w-8 h-8 rounded-xl {{ $iconBox }} flex items-center justify-center shrink-0 shadow-xs">
                            @if($up['type'] === 'fee')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                            @elseif($up['type'] === 'homework')
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-bold block leading-none {{ $up['type'] === 'fee' ? 'text-rose-600' : ($up['type'] === 'homework' ? 'text-sky-600' : 'text-emerald-600') }}">
                                {{ $up['title'] }}
                            </span>
                            <p class="text-[11px] font-medium text-slate-600 mt-1 line-clamp-1 leading-snug">
                                {{ $up['message'] }}
                            </p>
                            <span class="text-[10px] text-slate-400 font-semibold block mt-1">{{ $up['time'] }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-3 py-6 text-center text-xs font-semibold text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                        No pending fee reminders or new school notices.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Add Child Modal --}}
        @if($showAddChildModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl border border-slate-100" @click.away="$wire.closeAddChildModal()">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-indigo-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-800 leading-none">Add Child</h3>
                                <p class="text-xs text-slate-400 font-medium mt-1">Link your child to your parent portal</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeAddChildModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-50 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit="submitAddChild" class="mt-4 space-y-4">
                        @if($addChildErrorMessage)
                            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-700">
                                {{ $addChildErrorMessage }}
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Student Admission Number</label>
                            <input type="text" wire:model="addChildAdmissionNumber" placeholder="e.g. ADM/2026/001"
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required />
                            <span class="text-[10px] text-slate-400 mt-1 block">Enter the official student admission number issued by the school.</span>
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-2.5 border-t border-slate-100">
                            <button type="button" wire:click="closeAddChildModal" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs" style="background-color: #4f46e5; color: #ffffff;">
                                Link Child
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    @else
        {{-- SCREEN 2: Child Detail Dashboard --}}

        {{-- Sibling Switcher Bar & Return Control --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <button type="button" wire:click="$set('selectedChildId', null)" 
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:text-indigo-600 hover:bg-slate-50 transition shadow-2xs group">
                <svg class="h-4 w-4 text-slate-400 group-hover:text-indigo-600 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span>Family Dashboard</span>
            </button>

            @if($this->children->count() > 1)
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 mr-1 hidden sm:inline">Switch Child:</span>
                    @foreach($this->children as $sib)
                        <button type="button" wire:click="selectChild({{ $sib->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap shadow-2xs {{ $sib->id === $child->id ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 shadow-indigo-600/20' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
                            @if($sib->id === $child->id)
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            @else
                                <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                            @endif
                            <span>{{ $sib->first_name }}</span>
                            <span class="text-[10px] {{ $sib->id === $child->id ? 'text-indigo-200' : 'text-slate-400' }}">({{ $sib->schoolClass?->name ?? 'Class' }})</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Child Identity Banner & Motivational Card Matching Mockup --}}
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
            {{-- Left Profile Details --}}
            <div class="flex items-start gap-4 min-w-0">
                @if ($child->passport_photo_url)
                    <img src="{{ $child->passport_photo_url }}" alt="{{ $child->full_name }}" class="shrink-0 h-14 w-14 rounded-2xl object-cover ring-2 ring-slate-100 shadow-xs bg-slate-50" style="width: 56px; height: 56px; max-width: 56px; max-height: 56px; object-fit: cover;" />
                @else
                    <div class="shrink-0 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-xl font-black text-white ring-2 ring-indigo-50 shadow-xs" style="width: 56px; height: 56px;">
                        {{ mb_substr($child->first_name, 0, 1) }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight truncate">{{ $child->full_name }}</h2>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded-full" style="background-color: #def7ec; color: #03543f;">
                            + Active
                        </span>
                    </div>

                    <p class="text-xs font-semibold text-slate-500 mt-1">
                        ADM: {{ $child->admission_number }} &nbsp;|&nbsp; {{ $child->schoolClass?->name ?? 'Unassigned' }}
                    </p>

                    <div class="flex items-center gap-2 mt-2.5">
                        <div class="inline-flex items-center gap-2 rounded-xl bg-slate-50 border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Academic Session:</span>
                            <select wire:model.live="session" class="bg-transparent text-xs font-black text-slate-900 border-0 p-0 pr-2 focus:ring-0 cursor-pointer">
                                @foreach($this->availableSessions as $sName)
                                    <option value="{{ $sName }}" {{ $session === $sName ? 'selected' : '' }}>{{ $sName }}</option>
                                @endforeach
                            </select>
                            <span class="text-slate-300">|</span>
                            <select wire:model.live="term" class="bg-transparent text-xs font-bold text-slate-800 border-0 p-0 focus:ring-0 cursor-pointer rounded">
                                <option value="1">Term 1 ▾</option>
                                <option value="2">Term 2 ▾</option>
                                <option value="3">Term 3 ▾</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Open Book & Growing Seedling Illustration Banner Matching Mockup --}}
            <div class="hidden sm:flex items-center gap-4 bg-gradient-to-r from-blue-50/70 via-indigo-50/50 to-white rounded-2xl p-4 border border-blue-100/60 shadow-2xs shrink-0">
                <div class="text-right">
                    <span class="block text-sm sm:text-base font-serif italic text-blue-700 tracking-wide font-bold">
                        Small steps
                    </span>
                    <span class="block text-sm sm:text-base font-serif italic text-blue-800 tracking-wide font-black -mt-1">
                        for a brighter future
                    </span>
                </div>
                <div class="shrink-0 relative w-16 h-16 flex items-center justify-center">
                    <svg viewBox="0 0 100 100" class="w-16 h-16 drop-shadow-sm" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 68 L15 54 L15 42 L50 56 Z" fill="#60A5FA" />
                        <path d="M50 68 L85 54 L85 42 L50 56 Z" fill="#3B82F6" />
                        <path d="M50 56 L15 42 L50 30 L85 42 Z" fill="#93C5FD" opacity="0.5" />
                        <path d="M50 56 L18 43 C32 40 42 45 50 56 Z" fill="#FFFFFF" />
                        <path d="M50 56 L82 43 C68 40 58 45 50 56 Z" fill="#F0F9FF" />
                        <path d="M50 68 L50 56" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" />
                        <path d="M50 54 Q49 38 50 26" stroke="#10B981" stroke-width="3" stroke-linecap="round" />
                        <path d="M50 40 C42 36 38 28 42 22 C48 24 50 32 50 40 Z" fill="#34D399" />
                        <path d="M50 34 C58 30 63 22 60 16 C54 18 51 26 50 34 Z" fill="#10B981" />
                        <circle cx="50" cy="24" r="2.5" fill="#059669" />
                        <path d="M30 20 L31.5 24 L35.5 25.5 L31.5 27 L30 31 L28.5 27 L24.5 25.5 L28.5 24 Z" fill="#FBBF24" />
                        <path d="M72 18 L73 21 L76 22 L73 23 L72 26 L71 23 L68 22 L71 21 Z" fill="#F59E0B" />
                        <circle cx="68" cy="38" r="1.5" fill="#FCD34D" />
                        <circle cx="34" cy="42" r="1.5" fill="#FCD34D" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Tab Navigation Bar Matching Mockup --}}
        @php
            $tabs = [
                'overview'   => ['name' => 'Overview', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
                'attendance' => ['name' => 'Attendance', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>'],
                'results'    => ['name' => 'Results', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
                'homework'   => ['name' => 'Homework', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'],
                'timetable'  => ['name' => 'Timetable', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
                'topics'     => ['name' => 'Curriculum', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>'],
                'bursary'    => ['name' => 'Fee Ledger', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>'],
                'teachers'   => ['name' => 'Teachers', 'icon' => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
            ];
        @endphp

        <div class="border-b border-slate-200">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 px-0.5">
                @foreach($tabs as $tabKey => $tabVal)
                    <button type="button" wire:click="$set('activeTab', '{{ $tabKey }}')"
                            class="relative inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold transition-all duration-200 whitespace-nowrap shrink-0 border-b-2
                                   {{ $activeTab === $tabKey
                                        ? 'border-indigo-600 text-indigo-600 font-black'
                                        : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        <span>{!! $tabVal['icon'] !!}</span>
                        <span>{{ $tabVal['name'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- TAB CONTENT RENDERERS --}}

        @if($activeTab === 'overview')
            <div class="space-y-6">
                {{-- 4 Hero KPI Cards Matching Mockup --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    {{-- 1. Academic Average --}}
                    <div class="rounded-3xl border border-emerald-200 bg-white bg-gradient-to-br from-emerald-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-emerald-300 transition flex flex-col justify-between"
                         style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(236,253,245,0.85) 0%, #ffffff 100%);">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-2xl sm:text-3xl font-black text-emerald-600 leading-tight">
                                    {{ $stats['average'] > 0 ? $stats['average'] . '%' : '--' }}
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500 block truncate">Academic Average</span>
                                <span class="text-[10px] font-black {{ ($stats['trend']['direction'] ?? '') === 'down' ? 'text-rose-600' : 'text-emerald-700' }} mt-0.5 block truncate">
                                    @if(!empty($stats['trend']))
                                        {{ $stats['trend']['label'] }}
                                    @elseif($published && $stats['average'] > 0)
                                        Term {{ $term }} Published
                                    @else
                                        Term {{ $term }} in progress
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Class Position --}}
                    <div class="rounded-3xl border border-purple-200 bg-white bg-gradient-to-br from-purple-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-purple-300 transition flex flex-col justify-between"
                         style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(250,245,255,0.85) 0%, #ffffff 100%);">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-2xl sm:text-3xl font-black text-purple-600 leading-tight">
                                    @if($published && !empty($stats['position']))
                                        {{ $ordinal($stats['position']) }}
                                    @else
                                        <span class="text-slate-400 text-xl font-bold">--</span>
                                    @endif
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500 block truncate">Class Position</span>
                                <span class="text-[10px] font-semibold text-purple-400 mt-0.5 block truncate">
                                    @if($published && !empty($stats['position']) && !empty($stats['classSize']))
                                        (out of {{ $stats['classSize'] }})
                                    @elseif(! $published)
                                        Pending release
                                    @else
                                        Active session
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Attendance Rate --}}
                    <div class="rounded-3xl border border-sky-200 bg-white bg-gradient-to-br from-sky-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-sky-300 transition flex flex-col justify-between"
                         style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(240,249,255,0.85) 0%, #ffffff 100%);">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-2xl sm:text-3xl font-black text-sky-600 leading-tight">
                                    {{ $att['rate'] }}%
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500 block truncate">Attendance</span>
                                <span class="text-[10px] font-semibold text-sky-400 mt-0.5 block truncate">
                                    (This term)
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Fee Balance --}}
                    <div class="rounded-3xl border border-amber-200 bg-white bg-gradient-to-br from-amber-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-amber-300 transition flex flex-col justify-between"
                         style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(254,243,199,0.85) 0%, #ffffff 100%);">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-2xl sm:text-3xl font-black text-amber-600 leading-tight truncate">
                                    ₦{{ number_format($fees['outstanding'], 2) }}
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500 block truncate">Outstanding Fees</span>
                                @if($fees['outstanding'] <= 0)
                                    <span class="text-[10px] font-bold text-emerald-600 mt-0.5 block truncate">All Cleared</span>
                                @else
                                    <span class="text-[10px] font-bold text-rose-600 mt-0.5 block truncate">Payment Due</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Two-Column Overview: Left Column = Academic Performance (5 Cols), Right Column = 2x2 Grid (7 Cols) --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {{-- Left Column: Academic Performance Bar Chart (lg:col-span-5) --}}
                    <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                </div>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Academic Performance</h3>
                            </div>
                            <select wire:model.live="term" class="text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-xl px-2.5 py-1 focus:ring-0 cursor-pointer shadow-2xs">
                                <option value="1">Term 1 ▾</option>
                                <option value="2">Term 2 ▾</option>
                                <option value="3">Term 3 ▾</option>
                            </select>
                        </div>

                        @php
                            $chartItems = $this->subjectPerformanceChart;
                        @endphp

                        @if(empty($chartItems))
                            <div class="py-16 text-center text-xs font-bold text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200 my-4">
                                No assessment records found for Term {{ $term }}.
                            </div>
                        @else
                            {{-- Vertical Bar Chart Area with Y-axis 100, 80, 60, 40, 20, 0 --}}
                            <div class="pt-6 pb-2">
                                <div class="relative h-64 flex items-end justify-between gap-2 sm:gap-3 pl-8 pr-2">
                                    {{-- Y-Axis Grid Lines & Labels --}}
                                    <div class="absolute inset-0 pl-8 pointer-events-none flex flex-col justify-between text-[10px] font-bold text-slate-300">
                                        <div class="border-b border-dashed border-slate-100 w-full flex items-center justify-start"><span class="absolute left-0 -translate-y-1/2">100</span></div>
                                        <div class="border-b border-dashed border-slate-100 w-full flex items-center justify-start"><span class="absolute left-0 -translate-y-1/2">80</span></div>
                                        <div class="border-b border-dashed border-slate-100 w-full flex items-center justify-start"><span class="absolute left-0 -translate-y-1/2">60</span></div>
                                        <div class="border-b border-dashed border-slate-100 w-full flex items-center justify-start"><span class="absolute left-0 -translate-y-1/2">40</span></div>
                                        <div class="border-b border-dashed border-slate-100 w-full flex items-center justify-start"><span class="absolute left-0 -translate-y-1/2">20</span></div>
                                        <div class="border-b border-slate-200 w-full flex items-center justify-start"><span class="absolute left-0 -translate-y-1/2">0</span></div>
                                    </div>

                                    {{-- Bars --}}
                                    @foreach($chartItems as $bar)
                                        <div class="relative z-10 flex-1 flex flex-col items-center h-full justify-end group">
                                            {{-- Value floating above the bar --}}
                                            <span class="text-[10px] sm:text-[11px] font-black text-slate-700 mb-1.5 opacity-90 group-hover:scale-110 transition-transform">
                                                {{ $bar['score'] }}
                                            </span>
                                            {{-- The Bar --}}
                                            <div class="w-full max-w-[34px] sm:max-w-[40px] {{ $bar['bar_class'] }} rounded-t-xl transition-all duration-300 group-hover:brightness-95 shadow-2xs"
                                                 style="height: {{ max(8, $bar['score']) }}%;"
                                                 title="{{ $bar['name'] }}: {{ $bar['score'] }}%">
                                            </div>
                                            {{-- Label beneath the bar --}}
                                            <span class="text-[10px] sm:text-[11px] font-bold text-slate-600 mt-2 truncate max-w-[50px] sm:max-w-[65px] text-center" title="{{ $bar['name'] }}">
                                                {{ $bar['short'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Right Section: 2x2 Grid Matching Mockup (lg:col-span-7) --}}
                    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                        {{-- 1. Recent Results Card --}}
                        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-purple-50 text-indigo-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Recent Results</h3>
                                </div>
                                <button type="button" wire:click="$set('activeTab', 'results')" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    View All
                                </button>
                            </div>

                            @php
                                $overviewScores = $this->overviewRecentResults;
                            @endphp

                            <div class="mt-2 divide-y divide-slate-100 text-xs">
                                <div class="grid grid-cols-12 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    <span class="col-span-7">Subject</span>
                                    <span class="col-span-3 text-center">Score</span>
                                    <span class="col-span-2 text-right">Grade</span>
                                </div>
                                @forelse($overviewScores as $sc)
                                    <div class="grid grid-cols-12 py-2 items-center">
                                        <span class="col-span-7 font-semibold text-slate-700 truncate">{{ $sc->subject?->name }}</span>
                                        <span class="col-span-3 text-center font-bold text-slate-800">{{ round($sc->total) }}%</span>
                                        <span class="col-span-2 text-right font-black {{ in_array($sc->grade, ['A', 'A+']) ? 'text-emerald-600' : (in_array($sc->grade, ['B', 'C']) ? 'text-amber-500' : 'text-rose-500') }}">{{ $sc->grade }}</span>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-xs text-slate-400 font-semibold">No results recorded yet</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- 2. Attendance History Card --}}
                        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Attendance History</h3>
                                </div>
                                <button type="button" wire:click="$set('activeTab', 'attendance')" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    View All
                                </button>
                            </div>

                            <div class="flex items-center justify-center gap-2 my-2">
                                <button type="button" wire:click="prevAttendanceMonth" class="text-slate-400 hover:text-slate-600 transition text-sm font-bold px-1">&lsaquo;</button>
                                <span class="text-xs font-bold text-slate-700">{{ $calData['month'] }} ▾</span>
                                <button type="button" wire:click="nextAttendanceMonth" class="text-slate-400 hover:text-slate-600 transition text-sm font-bold px-1">&rsaquo;</button>
                            </div>

                            <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-semibold text-slate-400 mb-1">
                                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center">
                                @foreach($calData['weeks'] as $week)
                                    @foreach($week as $day)
                                        <div class="h-6 flex items-center justify-center">
                                            @if($day['day'])
                                                @if($day['status'] === 'present')
                                                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center justify-center">{{ $day['day'] }}</span>
                                                @elseif($day['status'] === 'absent')
                                                    <span class="w-5 h-5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px] flex items-center justify-center">{{ $day['day'] }}</span>
                                                @elseif(in_array($day['status'], ['late', 'excused', 'holiday']))
                                                    <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] flex items-center justify-center">{{ $day['day'] }}</span>
                                                @else
                                                    <span class="text-[10px] font-semibold text-slate-500">{{ $day['day'] }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>

                            <div class="flex items-center justify-center gap-3 text-[9px] font-bold text-slate-500 pt-3 mt-2 border-t border-slate-100">
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Present</span>
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-rose-500"></span> Absent</span>
                                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Holiday</span>
                            </div>
                        </div>

                        {{-- 3. Upcoming Homework Card --}}
                        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Upcoming Homework</h3>
                                </div>
                                <button type="button" wire:click="$set('activeTab', 'homework')" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    View All
                                </button>
                            </div>

                            <div class="mt-2 space-y-2.5">
                                @forelse($homework->take(3) as $hw)
                                    <div class="flex items-start gap-2.5 text-xs">
                                        <div class="flex items-center gap-1 text-[10px] font-semibold text-slate-400 shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>{{ $hw->due_date ? $hw->due_date->format('d M Y') : 'Upcoming' }}</span>
                                        </div>
                                        <div class="border-l border-slate-200 pl-2 min-w-0 flex-1">
                                            <span class="font-bold text-slate-800 block truncate leading-none">{{ $hw->subject?->name }}</span>
                                            <span class="text-[11px] text-slate-500 font-medium block truncate mt-0.5">{{ $hw->title }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-xs text-slate-400 font-semibold">No pending homework</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- 4. Announcements Card --}}
                        <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs flex flex-col justify-between">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                    </div>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-800">Announcements</h3>
                                </div>
                                <button type="button" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 transition">
                                    View All
                                </button>
                            </div>

                            <div class="mt-3">
                                @forelse($this->announcements->take(1) as $ann)
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-purple-50 text-indigo-600 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-slate-800 leading-snug">{{ $ann->title }}</h4>
                                            <span class="text-[11px] text-slate-400 font-semibold block mt-0.5">{{ $ann->created_at ? $ann->created_at->format('d M Y') : 'Recent' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-4 text-center text-xs text-slate-400 font-semibold">
                                        No recent announcements posted.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($activeTab === 'attendance')
            {{-- ATTENDANCE TRACKER TAB --}}
            <div class="space-y-6">

                {{-- Header with Title & Quick Actions --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-sm">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-sky-50 text-sky-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Attendance Tracker
                            </span>
                            <span class="text-xs font-bold text-slate-400">Term {{ $term }} &bull; {{ $session }}</span>
                        </div>
                        <h2 class="text-lg sm:text-xl font-black text-slate-800 tracking-tight mt-1.5">
                            Daily Attendance & Punctuality Records
                        </h2>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">
                            Live time-in and time-out updates, K40 biometric logs, and official roll call history for {{ $child->full_name }}.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" wire:click="openAbsenceModal"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 active:scale-[0.98] transition"
                                style="background-color: #4f46e5; color: #ffffff;">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Submit Absence Request</span>
                        </button>
                    </div>
                </div>

                {{-- Real-Time Today's Arrival Status Card --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm overflow-hidden relative">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="flex items-start sm:items-center gap-4">
                            <div class="relative shrink-0">
                                @if(in_array($todayAtt['raw_status'] ?? '', ['Present', 'Late']) || in_array($todayAtt['status'] ?? '', ['Present', 'Late']))
                                    <span class="absolute -inset-1 rounded-3xl bg-emerald-400 opacity-25 animate-ping"></span>
                                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                        <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                @elseif(($todayAtt['raw_status'] ?? '') === 'Excused' || ($todayAtt['status'] ?? '') === 'Excused')
                                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-purple-500/25">
                                        <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                @elseif(($todayAtt['raw_status'] ?? '') === 'Absent' || ($todayAtt['status'] ?? '') === 'Absent')
                                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white flex items-center justify-center shadow-lg shadow-rose-500/25">
                                        <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                @else
                                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                        <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">Today's Status</span>
                                    <span class="text-xs font-bold text-slate-300">&bull;</span>
                                    <span class="text-xs font-bold text-slate-500">{{ now()->format('l, F j, Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2.5 mt-1">
                                    <h3 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">
                                        {{ $todayAtt['status'] }}
                                    </h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider {{ $todayAtt['badge_class'] }}">
                                        {{ $todayAtt['status'] }}
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm font-semibold text-slate-600 mt-1">
                                    {{ $todayAtt['message'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Timestamps & Source Indicators --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 border-t lg:border-t-0 lg:border-l border-slate-100 pt-4 lg:pt-0 lg:pl-6 shrink-0">
                            {{-- Clock In --}}
                            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Clock In</span>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    <span class="text-sm font-black text-slate-800">{{ $todayAtt['time_in'] ?? '--:--' }}</span>
                                </div>
                            </div>

                            {{-- Clock Out --}}
                            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Clock Out</span>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span class="text-sm font-black text-slate-800">{{ $todayAtt['time_out'] ?? '--:--' }}</span>
                                </div>
                            </div>

                            {{-- Punch Source --}}
                            <div class="col-span-2 sm:col-span-1 bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Recorded Via</span>
                                <div class="mt-1 flex items-center gap-1.5">
                                    @if($todayAtt['is_biometric'])
                                        <svg class="h-4 w-4 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 7.07"/></svg>
                                        <span class="text-xs font-black text-sky-700">K40 Biometric</span>
                                    @elseif($todayAtt['source'])
                                        <svg class="h-4 w-4 text-indigo-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span class="text-xs font-bold text-slate-700 truncate" title="{{ $todayAtt['source'] }}">{{ $todayAtt['source'] }}</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5-Metric Summary Statistics --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    {{-- Present --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Present</span>
                            <div class="h-7 w-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ $att['present'] }}</div>
                            <span class="text-[10px] font-bold text-slate-400">Total days on campus</span>
                        </div>
                    </div>

                    {{-- Late --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">Late</span>
                            <div class="h-7 w-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ $att['late'] }}</div>
                            <span class="text-[10px] font-bold text-slate-400">Late arrivals</span>
                        </div>
                    </div>

                    {{-- Excused --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">Excused</span>
                            <div class="h-7 w-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ $att['excused'] }}</div>
                            <span class="text-[10px] font-bold text-slate-400">Official leave / sick notes</span>
                        </div>
                    </div>

                    {{-- Absent --}}
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">Absent</span>
                            <div class="h-7 w-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">{{ $att['absent'] }}</div>
                            <span class="text-[10px] font-bold text-slate-400">Unexcused absences</span>
                        </div>
                    </div>

                    {{-- Overall Attendance Rate --}}
                    <div class="col-span-2 sm:col-span-1 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md">Rate</span>
                            <div class="h-7 w-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-black text-xs">
                                %
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">
                                {{ $att['rate'] }}<span class="text-xs font-bold text-slate-400 ml-0.5">%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $att['rate'] >= 85 ? 'bg-emerald-500' : ($att['rate'] >= 70 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $att['rate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Interactive Monthly Attendance Heatmap & Calendar --}}
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                    {{-- Calendar Header & Navigation Controls --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/50 px-4 sm:px-6 py-4">
                        <div>
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-wide flex items-center gap-2">
                                <svg class="h-4.5 w-4.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Monthly Attendance Calendar
                            </h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Heatmap visualization of daily school attendance</p>
                        </div>

                        {{-- Month Selector Buttons --}}
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                            <button type="button" wire:click="prevAttendanceMonth"
                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 flex items-center justify-center transition active:scale-95 shadow-2xs"
                                    title="Previous Month">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span class="text-sm font-black text-slate-800 px-3 py-1 bg-white border border-slate-200 rounded-xl shadow-2xs min-w-[130px] text-center">
                                {{ $calData['month'] }}
                            </span>
                            <button type="button" wire:click="nextAttendanceMonth"
                                    class="h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 flex items-center justify-center transition active:scale-95 shadow-2xs"
                                    title="Next Month">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                            @if(! $calData['is_current'])
                                <button type="button" wire:click="setAttendanceMonth('{{ now()->format('Y-m') }}')"
                                        class="px-2.5 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition">
                                    Current
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Calendar Grid --}}
                    <div class="p-3 sm:p-6 overflow-x-auto">
                        <div class="min-w-[640px]">
                            {{-- Day of Week Headers (Sun - Sat) --}}
                            <div class="grid grid-cols-7 gap-2 mb-2 text-center text-[11px] font-black uppercase text-slate-400 tracking-wider">
                                <div class="text-slate-300">Sun</div>
                                <div>Mon</div>
                                <div>Tue</div>
                                <div>Wed</div>
                                <div>Thu</div>
                                <div>Fri</div>
                                <div class="text-slate-300">Sat</div>
                            </div>

                            {{-- Calendar Week Rows --}}
                            <div class="space-y-2">
                                @foreach($calData['weeks'] as $week)
                                    <div class="grid grid-cols-7 gap-2">
                                        @foreach($week as $day)
                                            @if(! $day['day'])
                                                {{-- Empty padding slot --}}
                                                <div class="h-20 sm:h-24 rounded-2xl bg-slate-50/40 border border-transparent"></div>
                                            @else
                                                @php
                                                    $st = $day['status'];
                                                    $cellBg = match($st) {
                                                        'present' => 'bg-emerald-50/80 border-emerald-200 text-emerald-950',
                                                        'late'    => 'bg-amber-50/80 border-amber-200 text-amber-950',
                                                        'excused' => 'bg-purple-50/80 border-purple-200 text-purple-950',
                                                        'absent'  => 'bg-rose-50/80 border-rose-200 text-rose-950',
                                                        'weekend' => 'bg-slate-50/40 border-slate-100 text-slate-300',
                                                        'future'  => 'bg-white border-slate-100 text-slate-300',
                                                        default   => 'bg-slate-50/60 border-slate-200/80 text-slate-400'
                                                    };
                                                    $statusPill = match($st) {
                                                        'present' => 'bg-emerald-500 text-white',
                                                        'late'    => 'bg-amber-500 text-white',
                                                        'excused' => 'bg-purple-500 text-white',
                                                        'absent'  => 'bg-rose-500 text-white',
                                                        default   => null
                                                    };
                                                @endphp
                                                <div class="h-20 sm:h-24 rounded-2xl border p-2 flex flex-col justify-between transition-all {{ $cellBg }} {{ $day['is_today'] ? 'ring-2 ring-indigo-500 ring-offset-1 font-black shadow-sm' : '' }}">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs sm:text-sm font-black {{ $day['is_today'] ? 'text-indigo-600' : '' }}">
                                                            {{ $day['day'] }}
                                                        </span>
                                                        @if($day['is_today'])
                                                            <span class="text-[8px] font-black uppercase tracking-wider bg-indigo-600 text-white px-1.5 py-0.2 rounded-md">Today</span>
                                                        @elseif($statusPill)
                                                            <span class="h-2 w-2 rounded-full {{ $statusPill }}"></span>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        @if($st === 'present')
                                                            <span class="block text-[9px] sm:text-[10px] font-black uppercase text-emerald-700 truncate">
                                                                Present
                                                            </span>
                                                            @if($day['time_in'])
                                                                <span class="block text-[9px] font-semibold text-emerald-600 truncate">{{ $day['time_in'] }}</span>
                                                            @endif
                                                        @elseif($st === 'late')
                                                            <span class="block text-[9px] sm:text-[10px] font-black uppercase text-amber-700 truncate">
                                                                Late
                                                            </span>
                                                            @if($day['time_in'])
                                                                <span class="block text-[9px] font-semibold text-amber-600 truncate">{{ $day['time_in'] }}</span>
                                                            @endif
                                                        @elseif($st === 'excused')
                                                            <span class="block text-[9px] sm:text-[10px] font-black uppercase text-purple-700 truncate" title="{{ $day['note'] }}">
                                                                Excused
                                                            </span>
                                                        @elseif($st === 'absent')
                                                            <span class="block text-[9px] sm:text-[10px] font-black uppercase text-rose-700 truncate">
                                                                Absent
                                                            </span>
                                                        @elseif($day['is_weekend'])
                                                            <span class="block text-[9px] font-medium text-slate-300">Off-day</span>
                                                        @elseif(! $day['is_future'])
                                                            <span class="block text-[9px] font-medium text-slate-400 italic">No entry</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Calendar Color Legend --}}
                    <div class="border-t border-slate-100 bg-slate-50/60 px-4 sm:px-6 py-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Legend</span>
                        <div class="flex flex-wrap items-center gap-3 sm:gap-5 text-xs font-bold">
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span>Present</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                <span>Late</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                                <span>Excused Leave</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-600">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                <span>Absent</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-400">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                <span>Weekend / Holiday</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Parent Absence & Sick Leave Requests Section --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm flex flex-col">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-wide flex items-center gap-2">
                                <svg class="h-4.5 w-4.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Absence & Sick Leave Notes
                            </h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Submitted doctor, illness, and family emergency notices for {{ $child->full_name }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('parents.excuse-requests', ['child' => $child->id]) }}" wire:navigate
                               class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                                <span>Manage All</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <button type="button" wire:click="openAbsenceModal"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span>New Leave Note</span>
                            </button>
                        </div>
                    </div>

                    @if($absenceRequests->isEmpty())
                        <div class="py-8 text-center text-sm font-semibold text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            No absence or sick leave requests filed this session.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($absenceRequests as $req)
                                @php
                                    $stBadge = match($req->status) {
                                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default    => 'bg-amber-50 text-amber-700 border-amber-200'
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 flex flex-col justify-between transition hover:bg-slate-50">
                                    <div>
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $stBadge }}">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-bold">Filed {{ $req->created_at->diffForHumans() }}</span>
                                        </div>
                                        <h4 class="text-sm font-black text-slate-800">{{ $req->reason }}</h4>
                                        <p class="text-xs text-slate-500 font-bold mt-1">
                                            {{ $req->start_date->format('M d, Y') }} &mdash; {{ $req->end_date->format('M d, Y') }}
                                        </p>
                                        @if($req->notes)
                                            <p class="text-xs text-slate-600 mt-2 bg-white p-2.5 rounded-xl border border-slate-100 leading-relaxed">{{ $req->notes }}</p>
                                        @endif
                                    </div>

                                    @if($req->status === 'pending')
                                        <div class="mt-3 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                                            <span class="text-slate-400 font-semibold italic">Awaiting school teacher review</span>
                                            <button type="button" wire:click="deleteAbsenceRequest({{ $req->id }})"
                                                    wire:confirm="Cancel this absence request?"
                                                    class="text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Cancel</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Date-by-Date Attendance Log Table --}}
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/50 px-4 sm:px-6 py-4 sm:py-5">
                        <div>
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-wide">Detailed Attendance Log</h3>
                            <p class="text-xs font-semibold text-slate-500 mt-1">Full chronological terminal attendance record for Term {{ $term }}, {{ $session }}</p>
                        </div>
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-2xs">
                            {{ $attHist->count() }} Records Logged
                        </span>
                    </div>

                    @if($attHist->isEmpty())
                        <div class="py-16 text-center text-sm font-semibold text-slate-400 px-4">
                            No attendance sheets or biometric scans recorded for this child in Term {{ $term }}.
                        </div>
                    @else
                        {{-- Mobile Cards View (block md:hidden) --}}
                        <div class="block md:hidden p-4 space-y-3">
                            @foreach($attHist as $mark)
                                @php
                                    $stClass = match($mark->status) {
                                        'Present' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Late'    => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Excused' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'Absent'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default   => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                    $isBio = str_contains(strtolower($mark->note ?? ''), 'k40') || str_contains(strtolower($mark->note ?? ''), 'biometric');
                                    $timeIn = $mark->arrived_at ? \Carbon\Carbon::parse($mark->arrived_at)->format('h:i A') : null;
                                    $timeOut = $mark->departed_at ? \Carbon\Carbon::parse($mark->departed_at)->format('h:i A') : null;
                                @endphp
                                <div class="rounded-2xl border border-slate-150 bg-slate-50/40 p-4 space-y-3 shadow-2xs">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="font-black text-slate-800 text-sm">
                                                {{ \Carbon\Carbon::parse($mark->sheet->date)->format('M d, Y') }}
                                            </div>
                                            <div class="text-[11px] font-bold text-slate-400">
                                                {{ \Carbon\Carbon::parse($mark->sheet->date)->format('l') }}
                                            </div>
                                        </div>
                                        <span class="rounded-lg px-2.5 py-1 text-xs uppercase font-black border {{ $stClass }}">
                                            {{ $mark->status }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-center pt-1">
                                        <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 shadow-2xs">
                                            <span class="block text-[9px] font-black uppercase tracking-wider text-slate-400">Arrived</span>
                                            <span class="text-xs font-black text-slate-700">{{ $timeIn ?? '--' }}</span>
                                        </div>
                                        <div class="bg-white rounded-xl p-2.5 border border-slate-200/70 shadow-2xs">
                                            <span class="block text-[9px] font-black uppercase tracking-wider text-slate-400">Departed</span>
                                            <span class="text-xs font-black text-slate-700">{{ $timeOut ?? '--' }}</span>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                        <span class="font-semibold flex items-center gap-1">
                                            @if($isBio)
                                                <span class="text-sky-600 font-bold">K40 Terminal</span>
                                            @else
                                                <span>{{ $mark->sheet?->takenBy?->name ?? 'Roll Call' }}</span>
                                            @endif
                                        </span>
                                        @if($mark->note)
                                            <span class="text-slate-400 font-medium truncate max-w-[150px]" title="{{ $mark->note }}">{{ $mark->note }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Desktop Table View (hidden md:block) --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                                    <tr>
                                        <th class="px-6 py-4">Date</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-center">Arrival</th>
                                        <th class="px-6 py-4 text-center">Departure</th>
                                        <th class="px-6 py-4">Recorded By / Device</th>
                                        <th class="px-6 py-4">Notes & Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($attHist as $mark)
                                        @php
                                            $stClass = match($mark->status) {
                                                'Present' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'Late'    => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'Excused' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                'Absent'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default   => 'bg-slate-100 text-slate-700 border-slate-200'
                                            };
                                            $isBio = str_contains(strtolower($mark->note ?? ''), 'k40') || str_contains(strtolower($mark->note ?? ''), 'biometric');
                                            $timeIn = $mark->arrived_at ? \Carbon\Carbon::parse($mark->arrived_at)->format('h:i A') : null;
                                            $timeOut = $mark->departed_at ? \Carbon\Carbon::parse($mark->departed_at)->format('h:i A') : null;
                                        @endphp
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-black text-slate-800">{{ \Carbon\Carbon::parse($mark->sheet->date)->format('M d, Y') }}</div>
                                                <div class="text-[11px] font-bold text-slate-400">{{ \Carbon\Carbon::parse($mark->sheet->date)->format('l') }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider border {{ $stClass }}">
                                                    {{ $mark->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center font-bold text-slate-700 whitespace-nowrap">
                                                {{ $timeIn ?? '--' }}
                                            </td>
                                            <td class="px-6 py-4 text-center font-bold text-slate-700 whitespace-nowrap">
                                                {{ $timeOut ?? '--' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($isBio)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-black bg-sky-50 text-sky-700 border border-sky-200">
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004.07 7.07"/></svg>
                                                        K40 Biometrics
                                                    </span>
                                                @else
                                                    <span class="text-xs font-semibold text-slate-600">
                                                        {{ $mark->sheet?->takenBy?->name ?? 'Class Roll Call' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-xs text-slate-500">
                                                {{ $mark->note ?: '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
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
                    <div class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/60">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Active Schedule
                    </div>
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
                                                    <div class="text-[9px] text-slate-500 font-bold mt-1 truncate flex items-center gap-1.5">
                                                        <img src="{{ $entry->teacher->profile_photo_url }}"
                                                             onerror="this.onerror=null; this.src='{{ asset('avatars/teachers.png') }}';"
                                                             class="h-3.5 w-3.5 rounded-full object-cover ring-1 ring-slate-200 shrink-0 bg-slate-50"
                                                             alt="{{ $entry->teacher->name }}" />
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

        @if($activeTab === 'bursary')
            {{-- Bursary & Payment History Tab / Fee Ledger --}}
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
                    @if($hasPaymentGateway)
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
                    @endif

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
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wide">
                                {{ $child->schoolClass?->name ?? 'Class' }}@if($child->section) ({{ $child->section->name }})@endif Teachers & Instructors
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 font-medium">
                            Teachers assigned to {{ $child->first_name }} in {{ $child->schoolClass?->name ?? 'Class' }}@if($child->section) ({{ $child->section->name }})@endif. Connect directly via WhatsApp for academic questions and guidance.
                        </p>
                    </div>
                    @php
                        $teachers = $this->classTeachers;
                    @endphp
                    @if ($teachers->isNotEmpty())
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200/60 self-start sm:self-auto">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $teachers->count() }}</span>
                            <span class="text-slate-500">{{ Str::plural('Teacher', $teachers->count()) }}</span>
                            @if($child->section)
                                <span class="text-slate-400 font-normal">| {{ $child->section->name }} Arm</span>
                            @endif
                        </div>
                    @endif
                </div>
                
                <div class="p-4 sm:p-6">
                    @if ($teachers->isEmpty())
                        <div class="py-16 text-center text-sm font-bold text-slate-400 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            <svg class="mx-auto h-10 w-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            No teachers or subject allocations recorded for {{ $child->schoolClass?->name ?? 'this child\'s class' }}@if($child->section) ({{ $child->section->name }})@endif yet.
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            @foreach ($teachers as $teacher)
                                @php
                                    $parentName = auth()->user()?->name ?? 'Parent';
                                    $studentName = $child->full_name ?? ($child->first_name . ' ' . $child->last_name);
                                    $subclassName = ($child->schoolClass?->name ?? 'Class') . ($child->section ? ' (' . $child->section->name . ')' : '');
                                    $schoolName = \App\Support\TenantSettings::get('school_name') ?? config('app.name', 'AcademyHub');
                                    $waGreeting = "Hello {$teacher->name}, I am reaching out as the parent of {$studentName} ({$subclassName}) at {$schoolName} regarding their academic performance.";
                                    $waUrl = $teacher->getWhatsappChatUrl($waGreeting);
                                    $cleanPhone = $teacher->clean_whatsapp_phone;
                                    $formattedPhone = $teacher->formatted_whatsapp_phone;
                                    $subjects = $teacher->assigned_subjects ?? [];
                                @endphp
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-start gap-3 sm:gap-3.5">
                                            <div class="relative shrink-0">
                                                <img src="{{ $teacher->profile_photo_url }}"
                                                     onerror="this.onerror=null; this.src='{{ asset('avatars/teachers.png') }}';"
                                                     class="h-12 w-12 rounded-2xl object-cover ring-2 ring-slate-100 shadow-sm shrink-0 bg-slate-50"
                                                     alt="{{ $teacher->name }}" />
                                            </div>
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

        {{-- Absence / Sick Leave Request Modal --}}
        @if($showAbsenceModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                    {{-- Backdrop --}}
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeAbsenceModal"></div>

                    {{-- Modal Card --}}
                    <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg border border-slate-200 p-6 sm:p-8 z-10">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">Notice of Absence</span>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight mt-1">Submit Absence Request</h3>
                                <p class="text-xs font-semibold text-slate-500">For {{ $child->full_name }} ({{ $child->schoolClass?->name ?? 'Student' }})</p>
                            </div>
                            <button type="button" wire:click="closeAbsenceModal" class="h-8 w-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form wire:submit="submitAbsenceRequest" class="mt-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">Start Date <span class="text-rose-500">*</span></label>
                                    <input type="date" wire:model="absenceStartDate"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500" required />
                                    @error('absenceStartDate') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">End Date <span class="text-rose-500">*</span></label>
                                    <input type="date" wire:model="absenceEndDate"
                                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500" required />
                                    @error('absenceEndDate') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">Reason Category <span class="text-rose-500">*</span></label>
                                <select wire:model="absenceReason"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500">
                                    <option value="Illness / Medical">Illness / Medical (Fever, flu, hospitalization)</option>
                                    <option value="Doctor Appointment">Doctor Appointment / Checkup</option>
                                    <option value="Family Emergency">Family Emergency / Urgent Personal Matter</option>
                                    <option value="Religious Observance">Religious Observance</option>
                                    <option value="Travel / Vacation">Travel / Pre-Planned Vacation</option>
                                    <option value="Other">Other / Miscellaneous</option>
                                </select>
                                @error('absenceReason') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-1">Explanation & Notes (Optional)</label>
                                <textarea wire:model="absenceNotes" rows="3"
                                          placeholder="Provide any additional details or notes for the class teacher and administration..."
                                          class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-1 focus:ring-indigo-500 placeholder-slate-400"></textarea>
                                @error('absenceNotes') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                                <button type="button" wire:click="closeAbsenceModal"
                                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black shadow-md shadow-indigo-600/20 active:scale-[0.98] transition"
                                        style="background-color: #4f46e5; color: #ffffff;"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                    <svg wire:loading wire:target="submitAbsenceRequest" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Submit Request</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    @endif
</div>
