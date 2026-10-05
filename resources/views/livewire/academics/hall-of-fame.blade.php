<div class="space-y-6 max-w-7xl mx-auto pb-16">
    {{-- Header & Title Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white/80 backdrop-blur-md p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white shadow-md shadow-amber-500/25 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l2.4 7.4h7.6l-6.2 4.5 2.4 7.4-6.2-4.5-6.2 4.5 2.4-7.4-6.2-4.5h7.6z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                    Academic Hall of Fame
                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                        Top Performers
                    </span>
                </h1>
                <p class="text-xs text-slate-500 font-medium">
                    Hierarchical performance tree & rankings for <span class="font-bold text-slate-700">{{ $currentClassName }}</span>
                </p>
            </div>
        </div>

        {{-- Session, Term & View Controls --}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Session Dropdown --}}
            <div class="relative">
                <select wire:model.live="selectedSession" class="text-xs font-bold bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-2.5 py-1.5 pr-7 focus:ring-2 focus:ring-amber-500 outline-none cursor-pointer">
                    @foreach($sessions as $s)
                        <option value="{{ $s->name }}">{{ $s->name }} Session</option>
                    @endforeach
                </select>
            </div>

            {{-- Term Dropdown --}}
            <div class="relative">
                <select wire:model.live="selectedTerm" class="text-xs font-bold bg-slate-50 border border-slate-200 text-slate-700 rounded-xl px-2.5 py-1.5 pr-7 focus:ring-2 focus:ring-amber-500 outline-none cursor-pointer">
                    <option value="1">1st Term</option>
                    <option value="2">2nd Term</option>
                    <option value="3">3rd Term</option>
                </select>
            </div>

            {{-- View Mode Toggle --}}
            <div class="flex items-center rounded-xl bg-slate-100 p-0.5 border border-slate-200">
                <button type="button" wire:click="$set('viewMode', 'tree')" 
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 {{ $viewMode === 'tree' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/></svg>
                    Tree
                </button>
                <button type="button" wire:click="$set('viewMode', 'leaderboard')" 
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 {{ $viewMode === 'leaderboard' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    List
                </button>
            </div>
        </div>
    </div>

    {{-- Filter Bar: Overall vs Class Selector --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3 shadow-2xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Scope Switcher --}}
        <div class="flex items-center gap-2">
            <button type="button" wire:click="setScope('overall')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 {{ $scope === 'overall' ? 'bg-amber-500 text-white shadow-xs shadow-amber-500/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70' }}">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"/></svg>
                Overall School
            </button>
            <button type="button" wire:click="setScope('class')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 {{ $scope === 'class' ? 'bg-indigo-600 text-white shadow-xs shadow-indigo-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                By Class
            </button>
        </div>

        {{-- Class Selector (Visible in 'class' mode) --}}
        @if($scope === 'class')
            <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                <span class="text-xs font-bold text-slate-400 whitespace-nowrap">Class:</span>
                <select wire:model.live="selectedClassId" class="text-xs font-bold bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-xl px-3 py-1 outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="text-xs text-slate-500 font-medium flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                Top performers across all class levels
            </div>
        @endif
    </div>

    {{-- MAIN HIERARCHY TREE VIEW --}}
    @if(empty($rankedStudents))
        {{-- Empty State --}}
        <div class="text-center py-16 bg-white/70 backdrop-blur-sm rounded-3xl border border-slate-200/80 px-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 shadow-inner mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-base font-black text-slate-900">No Assessment Records Yet</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 leading-relaxed">
                Scores have not yet been recorded for <b>{{ $currentClassName }}</b> in <b>Term {{ $selectedTerm }}, {{ $selectedSession }}</b>.
            </p>
        </div>
    @elseif($viewMode === 'tree')
        <div class="relative w-full overflow-x-auto py-4 px-2 select-none">
            {{-- Tree Container --}}
            <div class="min-w-[620px] max-w-4xl mx-auto flex flex-col items-center">

                {{-- ========================================== --}}
                {{-- TIER 1: APEX CHAMPION (1ST PLACE - GOLD)  --}}
                {{-- ========================================== --}}
                @if($apex = $apexStudent)
                    <div class="relative flex flex-col items-center group">
                        {{-- Crown Pill Badge --}}
                        <div class="mb-1.5 flex items-center gap-1 bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 text-slate-950 font-black text-[10px] px-3 py-0.5 rounded-full shadow-sm border border-yellow-200 uppercase tracking-wider">
                            👑 Rank 1 &bull; Champion
                        </div>

                        {{-- Compact Apex Card --}}
                        <div wire:click="selectStudent({{ $apex['id'] }})"
                             class="cursor-pointer relative z-10 w-48 sm:w-52 rounded-2xl p-3 text-center transition-all duration-200 transform hover:scale-[1.03] hover:-translate-y-0.5 bg-gradient-to-b from-amber-500/10 via-white to-amber-500/5 border-2 border-amber-400 shadow-md hover:shadow-lg">
                            
                            {{-- Circular Photo with crisp gold ring --}}
                            <div class="relative mx-auto w-14 h-14 mb-2">
                                <img src="{{ $apex['photo'] }}" alt="{{ $apex['full_name'] }}" 
                                     class="w-14 h-14 rounded-full object-cover aspect-square border-2 border-amber-400 shadow-xs bg-white mx-auto">
                                <div class="absolute -bottom-0.5 -right-0.5 bg-amber-500 text-white font-black text-[10px] h-5 w-5 rounded-full flex items-center justify-center border border-white shadow-xs">
                                    1
                                </div>
                            </div>

                            {{-- Student Name & Class --}}
                            <h2 class="text-xs sm:text-sm font-black text-slate-900 tracking-tight truncate leading-snug">{{ $apex['full_name'] }}</h2>
                            <div class="text-[10px] font-bold text-amber-700 mt-0.5 truncate">
                                {{ $apex['class_name'] }}{{ $apex['arm_name'] ? ' • ' . $apex['arm_name'] : '' }}
                            </div>

                            {{-- Compact Points & Average Row --}}
                            <div class="mt-2 pt-2 border-t border-amber-100 flex items-center justify-between text-[11px] font-black">
                                <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/50">
                                    {{ $apex['average'] }}% avg
                                </span>
                                <span class="text-amber-800 bg-amber-100/70 px-2 py-0.5 rounded-md border border-amber-200/50">
                                    {{ $apex['total_points'] }} pts
                                </span>
                            </div>

                            @if(!empty($apex['highest_subject']))
                                <div class="mt-1.5 text-[9px] font-bold text-slate-500 truncate bg-amber-50/60 rounded px-1.5 py-0.5">
                                    ⭐ {{ $apex['highest_subject'] }} ({{ $apex['highest_score'] }}/100)
                                </div>
                            @endif
                        </div>

                        {{-- Downward Branch Line from Apex --}}
                        <div class="w-0.5 h-6 bg-amber-400"></div>
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- TIER 2: RUNNERS UP (2ND & 3RD PLACE)      --}}
                {{-- ========================================== --}}
                @if($silver = $silverStudent)
                    <div class="relative w-full max-w-lg flex justify-around items-start">
                        {{-- Horizontal Branch Crossbar --}}
                        <div class="absolute top-0 left-1/4 right-1/4 h-0.5 bg-slate-300"></div>

                        {{-- 2ND PLACE (SILVER) --}}
                        <div class="relative flex flex-col items-center group pt-3">
                            {{-- Top Connector Stem --}}
                            <div class="absolute top-0 w-0.5 h-3 bg-slate-300"></div>

                            {{-- Rank Badge --}}
                            <div class="mb-1 bg-slate-700 text-white font-black text-[9px] px-2.5 py-0.5 rounded-full shadow-xs uppercase tracking-wider">
                                🥈 Rank 2 &bull; Silver
                            </div>

                            {{-- Compact Silver Card --}}
                            <div wire:click="selectStudent({{ $silver['id'] }})"
                                 class="cursor-pointer relative z-10 w-40 sm:w-44 rounded-2xl p-2.5 text-center transition-all duration-200 transform hover:scale-[1.03] hover:-translate-y-0.5 bg-white border border-slate-300 shadow-xs hover:shadow-md">
                                
                                <div class="relative mx-auto w-12 h-12 mb-1.5">
                                    <img src="{{ $silver['photo'] }}" alt="{{ $silver['full_name'] }}" 
                                         class="w-12 h-12 rounded-full object-cover aspect-square border-2 border-slate-300 shadow-xs bg-white mx-auto">
                                    <div class="absolute -bottom-0.5 -right-0.5 bg-slate-600 text-white font-black text-[9px] h-4 w-4 rounded-full flex items-center justify-center border border-white">
                                        2
                                    </div>
                                </div>

                                <h3 class="text-xs font-black text-slate-900 tracking-tight truncate leading-tight">{{ $silver['full_name'] }}</h3>
                                <div class="text-[10px] font-semibold text-slate-500 truncate mt-0.5">
                                    {{ $silver['class_name'] }}{{ $silver['arm_name'] ? ' • ' . $silver['arm_name'] : '' }}
                                </div>

                                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] font-black">
                                    <span class="text-emerald-600">{{ $silver['average'] }}%</span>
                                    <span class="text-slate-600">{{ $silver['total_points'] }} pts</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3RD PLACE (BRONZE) --}}
                        @if($bronze = $bronzeStudent)
                            <div class="relative flex flex-col items-center group pt-3">
                                {{-- Top Connector Stem --}}
                                <div class="absolute top-0 w-0.5 h-3 bg-slate-300"></div>

                                {{-- Rank Badge --}}
                                <div class="mb-1 bg-amber-700 text-white font-black text-[9px] px-2.5 py-0.5 rounded-full shadow-xs uppercase tracking-wider">
                                    🥉 Rank 3 &bull; Bronze
                                </div>

                                {{-- Compact Bronze Card --}}
                                <div wire:click="selectStudent({{ $bronze['id'] }})"
                                     class="cursor-pointer relative z-10 w-40 sm:w-44 rounded-2xl p-2.5 text-center transition-all duration-200 transform hover:scale-[1.03] hover:-translate-y-0.5 bg-white border border-amber-600/40 shadow-xs hover:shadow-md">
                                    
                                    <div class="relative mx-auto w-12 h-12 mb-1.5">
                                        <img src="{{ $bronze['photo'] }}" alt="{{ $bronze['full_name'] }}" 
                                             class="w-12 h-12 rounded-full object-cover aspect-square border-2 border-amber-600/60 shadow-xs bg-white mx-auto">
                                        <div class="absolute -bottom-0.5 -right-0.5 bg-amber-700 text-white font-black text-[9px] h-4 w-4 rounded-full flex items-center justify-center border border-white">
                                            3
                                        </div>
                                    </div>

                                    <h3 class="text-xs font-black text-slate-900 tracking-tight truncate leading-tight">{{ $bronze['full_name'] }}</h3>
                                    <div class="text-[10px] font-semibold text-slate-500 truncate mt-0.5">
                                        {{ $bronze['class_name'] }}{{ $bronze['arm_name'] ? ' • ' . $bronze['arm_name'] : '' }}
                                    </div>

                                    <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] font-black">
                                        <span class="text-emerald-600">{{ $bronze['average'] }}%</span>
                                        <span class="text-slate-600">{{ $bronze['total_points'] }} pts</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ========================================== --}}
                {{-- TIER 3: HONOR ROLL (4TH TO 7TH PLACE)     --}}
                {{-- ========================================== --}}
                @if(!empty($honorRoll))
                    {{-- Central Downward Stem connecting Tier 2 to Tier 3 --}}
                    <div class="w-0.5 h-6 bg-slate-300"></div>

                    <div class="relative w-full max-w-2xl">
                        {{-- Horizontal Branch Crossbar spanning honor roll nodes --}}
                        <div class="absolute top-0 left-10 right-10 h-0.5 bg-slate-300"></div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3">
                            @foreach($honorRoll as $hr)
                                <div class="relative flex flex-col items-center">
                                    {{-- Mini Top Stem from horizontal crossbar --}}
                                    <div class="absolute -top-3 w-0.5 h-3 bg-slate-300"></div>

                                    {{-- Compact Honor Roll Card --}}
                                    <div wire:click="selectStudent({{ $hr['id'] }})"
                                         class="cursor-pointer w-full rounded-xl p-2 bg-white border border-slate-200/90 text-center shadow-2xs hover:shadow-md hover:border-indigo-300 transition-all duration-200 group">
                                        
                                        {{-- Rank Pill --}}
                                        <span class="inline-block text-[9px] font-black px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 mb-1">
                                            #{{ $hr['rank'] }}
                                        </span>

                                        {{-- Circular Photo --}}
                                        <img src="{{ $hr['photo'] }}" alt="{{ $hr['full_name'] }}" 
                                             class="w-10 h-10 rounded-full object-cover aspect-square border border-slate-200 shadow-2xs mx-auto mb-1">

                                        <h4 class="text-[11px] font-bold text-slate-900 truncate px-0.5 leading-tight">{{ $hr['full_name'] }}</h4>
                                        <div class="text-[9px] text-slate-400 truncate mt-0.5">{{ $hr['class_name'] }}</div>

                                        <div class="mt-1.5 pt-1 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold">
                                            <span class="text-emerald-600">{{ $hr['average'] }}%</span>
                                            <span class="text-slate-500">{{ $hr['total_points'] }}p</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- ========================================== --}}
        {{-- RUNNER-UPS DIRECTORY (RANKS 8+)            --}}
        {{-- ========================================== --}}
        @if(!empty($runnerUps))
            <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Honor Roll Scholars (Ranks 8 &ndash; {{ count($rankedStudents) }})
                    </h3>
                    <span class="text-[11px] font-bold text-slate-400">{{ count($runnerUps) }} students</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                    @foreach($runnerUps as $ru)
                        <div wire:click="selectStudent({{ $ru['id'] }})"
                             class="cursor-pointer flex items-center justify-between p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition-all">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-xs font-black text-slate-400 w-4 text-right">#{{ $ru['rank'] }}</span>
                                <img src="{{ $ru['photo'] }}" alt="{{ $ru['full_name'] }}" class="w-8 h-8 rounded-full object-cover aspect-square border border-slate-200">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ $ru['full_name'] }}</div>
                                    <div class="text-[10px] text-slate-400 truncate">{{ $ru['class_name'] }}</div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 pl-2">
                                <div class="text-xs font-black text-emerald-600">{{ $ru['average'] }}%</div>
                                <div class="text-[9px] font-semibold text-slate-400">{{ $ru['total_points'] }} pts</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    {{-- ========================================== --}}
    {{-- FULL LEADERBOARD TABLE VIEW                --}}
    {{-- ========================================== --}}
    @elseif($viewMode === 'leaderboard')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-3.5 border-b border-slate-100 flex items-center justify-between">
                <div class="font-extrabold text-xs text-slate-900">Ranked Academic Leaderboard</div>
                <div class="text-xs text-slate-500">Total ranked: <b>{{ count($rankedStudents) }}</b></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-500 uppercase tracking-wider font-extrabold text-[10px]">
                            <th class="py-2.5 px-3 w-12 text-center">Rank</th>
                            <th class="py-2.5 px-3">Student</th>
                            <th class="py-2.5 px-3">Class</th>
                            <th class="py-2.5 px-3 text-center">Subjects</th>
                            <th class="py-2.5 px-3 text-center">Distinctions</th>
                            <th class="py-2.5 px-3 text-right">Total Score</th>
                            <th class="py-2.5 px-3 text-right">Average %</th>
                            <th class="py-2.5 px-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rankedStudents as $st)
                            <tr class="hover:bg-slate-50/70 transition-colors cursor-pointer" wire:click="selectStudent({{ $st['id'] }})">
                                <td class="py-2.5 px-3 text-center">
                                    @if($st['rank'] === 1)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 text-white font-black text-[11px] shadow-2xs">1</span>
                                    @elseif($st['rank'] === 2)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-500 text-white font-black text-[11px] shadow-2xs">2</span>
                                    @elseif($st['rank'] === 3)
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-700 text-white font-black text-[11px] shadow-2xs">3</span>
                                    @else
                                        <span class="font-bold text-slate-400 text-xs">#{{ $st['rank'] }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $st['photo'] }}" alt="{{ $st['full_name'] }}" class="w-7 h-7 rounded-full object-cover aspect-square border border-slate-200">
                                        <div>
                                            <div class="font-bold text-slate-900 leading-snug">{{ $st['full_name'] }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $st['admission_number'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-600">
                                    {{ $st['class_name'] }}{{ $st['arm_name'] ? ' (' . $st['arm_name'] . ')' : '' }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-slate-700">
                                    {{ $st['subject_count'] }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $st['distinctions'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $st['distinctions'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right font-black text-slate-900">
                                    {{ $st['total_points'] }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-black text-emerald-600 text-sm">
                                    {{ $st['average'] }}%
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" class="btn-sm btn-outline text-[10px] py-0.5 px-2">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ============================================================== --}}
    {{-- STUDENT SCORE BREAKDOWN MODAL                                  --}}
    {{-- ============================================================== --}}
    @if($modalStudent)
        @php
            $mStudent = $modalStudent;
            $mScores = $mStudent->scores;
            $mTotal = $mScores->sum('total');
            $mAvg = $mScores->count() > 0 ? round($mTotal / $mScores->count(), 1) : 0;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs animate-fade-in"
             wire:click.self="closeModal">
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200/90 max-w-md w-full overflow-hidden animate-scale-up">
                {{-- Modal Header --}}
                <div class="bg-gradient-to-r from-amber-500 to-yellow-500 p-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ $mStudent->passport_photo_url }}" alt="{{ $mStudent->full_name }}" 
                             class="w-11 h-11 rounded-full object-cover aspect-square border-2 border-white shadow-sm bg-white">
                        <div>
                            <h3 class="text-sm font-black tracking-tight leading-snug">{{ $mStudent->full_name }}</h3>
                            <div class="text-[11px] text-amber-100 font-medium">
                                {{ $mStudent->schoolClass?->name ?? 'Class' }}{{ $mStudent->section ? ' • ' . $mStudent->section->name : '' }}
                                &bull; <span class="font-mono">{{ $mStudent->admission_number }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="p-1 rounded-full hover:bg-white/20 text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Summary Stats Bar --}}
                <div class="grid grid-cols-3 bg-amber-50/60 border-b border-amber-100 text-center py-2.5 px-3 text-xs">
                    <div>
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">Total Points</span>
                        <span class="font-black text-slate-900 text-sm">{{ $mTotal }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">Average</span>
                        <span class="font-black text-emerald-600 text-sm">{{ $mAvg }}%</span>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase font-bold text-slate-400 block">Subjects</span>
                        <span class="font-black text-indigo-600 text-sm">{{ $mScores->count() }}</span>
                    </div>
                </div>

                {{-- Subject Breakdown Table --}}
                <div class="p-3.5 max-h-64 overflow-y-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 text-[10px] font-black uppercase">
                                <th class="pb-1.5">Subject</th>
                                <th class="pb-1.5 text-center">CA</th>
                                <th class="pb-1.5 text-center">Exam</th>
                                <th class="pb-1.5 text-center">Total</th>
                                <th class="pb-1.5 text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($mScores as $sc)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-1.5 font-bold text-slate-800">{{ $sc->subject?->name ?? 'General' }}</td>
                                    <td class="py-1.5 text-center font-semibold text-slate-500">{{ ($sc->ca1 ?? 0) + ($sc->ca2 ?? 0) }}</td>
                                    <td class="py-1.5 text-center font-semibold text-slate-500">{{ $sc->exam ?? 0 }}</td>
                                    <td class="py-1.5 text-center font-black text-slate-900">{{ $sc->total }}</td>
                                    <td class="py-1.5 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black {{ in_array($sc->grade, ['A', 'A+'], true) ? 'bg-emerald-100 text-emerald-800' : (in_array($sc->grade, ['B', 'C'], true) ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $sc->grade }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal Footer --}}
                <div class="p-2.5 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button type="button" wire:click="closeModal" class="btn-sm btn-primary py-1 px-3.5 rounded-xl text-xs font-bold">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
