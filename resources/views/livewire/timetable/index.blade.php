<div class="space-y-6">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .font-display {
            font-family: 'Fredoka', 'Nunito', ui-rounded, system-ui, -apple-system, sans-serif !important;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            header, nav, aside, .sidebar, [role="navigation"], .no-print, button, .admin-controls {
                display: none !important;
            }
            #timetable-poster-wrapper {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                page-break-inside: avoid;
            }
            @page {
                size: A4 landscape;
                margin: 6mm 4mm;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl shadow-md bg-slate-900 no-print">
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 sm:p-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-400"></span>
                    <span class="text-xs font-semibold uppercase tracking-widest text-blue-300">Weekly Schedule</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">School Timetable</h2>
                <p class="mt-1 text-xs sm:text-sm text-slate-400">Manage and view the academic timetable and slots</p>
            </div>
            <a href="{{ auth()->user()?->role === 'parent' ? route('parents.dashboard') : route('more-features') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-bold text-white transition bg-white/10 hover:bg-white/20 self-start sm:self-auto min-h-[44px]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- Class Selector --}}
    <div class="rounded-2xl bg-white p-4 sm:p-6 shadow-sm border border-slate-100 no-print">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-950">Select Class</h3>
                <p class="text-xs sm:text-sm text-slate-500">Choose a class to load its weekly timetable</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <select wire:model.live="classId" class="w-full sm:w-auto rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 min-h-[44px]">
                    <option value="">Select Class ({{ collect($this->classes)->count() }} available)</option>
                    @foreach($this->classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @if($classId)
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:shadow-md transition-all min-h-[44px]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print Poster
                        </button>
                        <a href="{{ route('timetable.pdf', ['class_id' => $classId]) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-blue-700 transition-colors min-h-[44px]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download PDF
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($classId)
        <div wire:key="timetable-container-{{ $classId }}" class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            
            {{-- View Mode Switcher Header --}}
            <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/80 p-4 sm:px-6 sm:py-4 sm:flex-row sm:items-center sm:justify-between no-print">
                <div class="flex items-center bg-slate-200/60 p-1 rounded-lg self-start">
                    <button wire:click="$set('viewMode', 'grid')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 text-xs font-bold rounded-md transition-all {{ $viewMode === 'grid' ? 'bg-white shadow text-slate-800' : 'text-slate-600 hover:text-slate-900' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Grid View
                    </button>
                    <button wire:click="$set('viewMode', 'daily')" class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 text-xs font-bold rounded-md transition-all {{ $viewMode === 'daily' ? 'bg-white shadow text-slate-800' : 'text-slate-600 hover:text-slate-900' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Daily List
                    </button>
                </div>
                
                @if($viewMode === 'daily')
                    <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 no-scrollbar touch-pan-x">
                        @foreach($days as $d)
                            <button wire:click="$set('activeDayTab', {{ $d['day'] }})" class="px-3.5 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap min-h-[38px] {{ $activeDayTab === $d['day'] ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-200/50 text-slate-700 hover:bg-slate-200' }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                {{ $d['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($viewMode === 'daily')
                {{-- Simplified Daily List View --}}
                @php
                    $dayEntries = $entries->where('day_of_week', $activeDayTab)->sortBy('starts_at');
                @endphp
                <div class="p-4 sm:p-6 space-y-4">
                    @if($dayEntries->isEmpty())
                        <div class="text-center py-12 sm:py-16 text-slate-400">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="font-medium text-sm">No classes or breaks scheduled for {{ $this->dayLabel($activeDayTab) }}.</p>
                            @if($isAdmin)
                                <div class="mt-4">
                                    <button type="button" wire:click="selectSlot({{ $activeDayTab }}, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-blue-700 min-h-[44px]" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                        + Add Slot
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        @if($isAdmin)
                            <div class="flex justify-end mb-2">
                                <button type="button" wire:click="selectSlot({{ $activeDayTab }}, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow hover:bg-blue-700 min-h-[44px]" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                    + Add Slot
                                </button>
                            </div>
                        @endif
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            @foreach($dayEntries as $entry)
                                @if($entry->is_break)
                                    <div class="md:col-span-2 flex items-center justify-between p-3.5 sm:p-4 rounded-xl border border-amber-200 bg-amber-50 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-800 flex-shrink-0">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3"/></svg>
                                            </span>
                                            <div>
                                                <div class="text-xs font-black text-amber-800 uppercase tracking-wider">{{ $entry->break_text ?? 'BREAK' }}</div>
                                                <div class="text-xs text-amber-700 font-bold mt-0.5">{{ substr($entry->starts_at, 0, 5) }} – {{ substr($entry->ends_at, 0, 5) }}</div>
                                            </div>
                                        </div>
                                        @if($isAdmin)
                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-bold text-amber-700 hover:text-amber-900 hover:underline p-1">
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    @php
                                        $c = $entry->color ?? 'slate';
                                        $borderColor = match($c) {
                                            'blue'    => 'border-blue-500',
                                            'indigo'  => 'border-indigo-500',
                                            'violet'  => 'border-violet-500',
                                            'purple'  => 'border-purple-500',
                                            'pink'    => 'border-pink-500',
                                            'red'     => 'border-red-500',
                                            'orange'  => 'border-orange-500',
                                            'amber'   => 'border-amber-500',
                                            'yellow'  => 'border-yellow-400',
                                            'green'   => 'border-green-500',
                                            'emerald' => 'border-emerald-500',
                                            'teal'    => 'border-teal-500',
                                            'cyan'    => 'border-cyan-500',
                                            'sky'     => 'border-sky-500',
                                            default   => 'border-slate-400',
                                        };
                                        $bgColor = match($c) {
                                            'blue'    => 'bg-blue-50/30',
                                            'indigo'  => 'bg-indigo-50/30',
                                            'violet'  => 'bg-violet-50/30',
                                            'purple'  => 'bg-purple-50/30',
                                            'pink'    => 'bg-pink-50/30',
                                            'red'     => 'bg-red-50/30',
                                            'orange'  => 'bg-orange-50/30',
                                            'amber'   => 'bg-amber-50/30',
                                            'yellow'  => 'bg-yellow-50/30',
                                            'green'   => 'bg-green-50/30',
                                            'emerald' => 'bg-emerald-50/30',
                                            'teal'    => 'bg-teal-50/30',
                                            'cyan'    => 'bg-cyan-50/30',
                                            'sky'     => 'bg-sky-50/30',
                                            default   => 'bg-slate-50/50',
                                        };
                                    @endphp
                                    <div class="flex flex-col justify-between p-4 sm:p-5 rounded-xl border-l-4 {{ $borderColor }} {{ $bgColor }} border border-slate-200/60 shadow-sm relative">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ substr($entry->starts_at, 0, 5) }} – {{ substr($entry->ends_at, 0, 5) }}</span>
                                                @if($entry->room)
                                                    <span class="inline-flex items-center gap-1 rounded bg-slate-200/70 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                                        <svg class="h-3 w-3 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        {{ $entry->room }}
                                                    </span>
                                                @endif
                                            </div>
                                            <h4 class="mt-2 text-base sm:text-lg font-black text-slate-900">{{ $entry->subject?->name }}</h4>
                                            @if($entry->teacher?->name)
                                                <div class="mt-2 flex items-center gap-2 text-xs sm:text-sm text-slate-600 font-medium">
                                                    <svg class="h-4 w-4 opacity-60 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    <span class="truncate">{{ $entry->teacher->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        @if($isAdmin)
                                            <div class="mt-3 sm:mt-4 flex justify-end">
                                                <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline p-1">
                                                    Edit Slot
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                {{-- ══════════════════════════════════════════════════════════════ --}}
                {{-- Playful Illustrated School Timetable Poster View              --}}
                {{-- ══════════════════════════════════════════════════════════════ --}}
                <div id="timetable-poster-wrapper" class="w-full bg-[#f8fbff] flex flex-col font-display selection:bg-amber-200">
                    
                    {{-- Poster Header (School Crest, Mascot Students, Bubbly Class Badge & Ribbon, Sun) --}}
                    @include('partials.timetable.header', [
                        'schoolName' => config('academyhub.school_name'),
                        'schoolTagline' => config('academyhub.tagline'),
                        'className' => $this->classes->firstWhere('id', $classId)?->name ?? 'Class',
                        'logoBase64' => null,
                        'logoUrl' => config('academyhub.school_logo') ? asset('storage/' . config('academyhub.school_logo')) : null,
                    ])

                    {{-- Timetable Table Grid --}}
                    <div class="w-full overflow-x-auto p-3 sm:p-6">
                        <table class="w-full border-separate border-spacing-2 sm:border-spacing-3" style="min-width: 860px;">
                            {{-- Table Header Row: Days & Time Slots --}}
                            <thead>
                                <tr>
                                    {{-- First Column: "Days" Capsule --}}
                                    <th class="p-1 sm:p-1.5 text-center" style="width: 140px; min-width: 140px;">
                                        <div class="rounded-2xl bg-[#163c55] text-white py-2.5 sm:py-3 px-3 flex items-center justify-center gap-2 shadow-sm border border-[#0ea5e9]/30">
                                            <span class="text-base sm:text-lg">📅</span>
                                            <span class="text-xs sm:text-sm font-black uppercase tracking-wider">Days</span>
                                        </div>
                                    </th>

                                    {{-- Time Slot Columns --}}
                                    @foreach($timeSlots as $slot)
                                        <th class="p-1 sm:p-1.5 text-center" style="min-width: 110px;">
                                            <div class="rounded-2xl bg-[#163c55] text-white py-2.5 sm:py-3 px-3 flex items-center justify-center gap-1.5 shadow-sm border border-[#0ea5e9]/30">
                                                <span class="text-sm opacity-90">🕒</span>
                                                <span class="text-xs sm:text-sm font-black whitespace-nowrap tracking-wide">{{ $slot['start'] }}-{{ $slot['end'] }}</span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            {{-- Table Body: Days as Rows --}}
                            <tbody>
                                @php
                                    $rendered = [];
                                @endphp
                                @foreach($days as $dayIndex => $d)
                                    @php
                                        $dayNum = $d['day'];
                                        $dayPillStyle = match($dayNum) {
                                            1 => 'bg-[#0284c7] text-white shadow-sky-600/30',   // Monday (Vivid Sky Blue)
                                            2 => 'bg-[#16a34a] text-white shadow-emerald-600/30', // Tuesday (Vivid Green)
                                            3 => 'bg-[#f59e0b] text-white shadow-amber-600/30',  // Wednesday (Warm Orange/Amber)
                                            4 => 'bg-[#7c3aed] text-white shadow-purple-600/30', // Thursday (Royal Purple)
                                            5 => 'bg-[#e11d48] text-white shadow-rose-600/30',   // Friday (Vivid Pink/Rose)
                                            6 => 'bg-[#0d9488] text-white shadow-teal-600/30',   // Saturday (Teal)
                                            default => 'bg-[#163c55] text-white',
                                        };
                                        $rowPastelTheme = match($dayNum) {
                                            1 => ['bg' => 'bg-[#e0f2fe]', 'text' => 'text-[#034f75]', 'border' => 'border-[#bae6fd]'],
                                            2 => ['bg' => 'bg-[#dcfce7]', 'text' => 'text-[#14532d]', 'border' => 'border-[#bbf7d0]'],
                                            3 => ['bg' => 'bg-[#fef3c7]', 'text' => 'text-[#78350f]', 'border' => 'border-[#fde68a]'],
                                            4 => ['bg' => 'bg-[#f3e8ff]', 'text' => 'text-[#4c1d95]', 'border' => 'border-[#ddd6fe]'],
                                            5 => ['bg' => 'bg-[#ffe4e6]', 'text' => 'text-[#881337]', 'border' => 'border-[#fecdd3]'],
                                            6 => ['bg' => 'bg-[#ccfbf1]', 'text' => 'text-[#115e59]', 'border' => 'border-[#99f6e4]'],
                                            default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-900', 'border' => 'border-slate-200'],
                                        };
                                    @endphp
                                    <tr>
                                        {{-- Day Label Capsule --}}
                                        <td class="p-1 sm:p-1.5 align-middle text-center" style="width: 140px; min-width: 140px;">
                                            <div class="rounded-2xl py-3.5 px-3 sm:px-4 font-black text-xs sm:text-sm tracking-wide shadow-md flex items-center justify-center {{ $dayPillStyle }}">
                                                {{ $d['label'] }}
                                            </div>
                                        </td>

                                        {{-- Subject / Break Cells --}}
                                        @foreach($timeSlots as $slot)
                                            @php
                                                // Skip if already rendered in a multi-row break span
                                                if (isset($rendered[$dayNum][$slot['key']])) {
                                                    continue;
                                                }

                                                $entry = $slotMap[$dayNum][$slot['key']] ?? null;
                                            @endphp

                                            @if($entry && $entry->is_break)
                                                @php
                                                    $targetText = trim($entry->break_text ?? 'BREAK');
                                                    $rowspan = 1;
                                                    $currentIdx = array_search($dayNum, array_column($days, 'day'));
                                                    for ($k = $currentIdx + 1; $k < count($days); $k++) {
                                                        $nextDayNum = $days[$k]['day'];
                                                        $nextEntry = $slotMap[$nextDayNum][$slot['key']] ?? null;
                                                        if ($nextEntry && $nextEntry->is_break && strcasecmp(trim($nextEntry->break_text ?? 'BREAK'), $targetText) === 0) {
                                                            $rowspan++;
                                                        } else {
                                                            break;
                                                        }
                                                    }
                                                    for ($offset = 1; $offset < $rowspan; $offset++) {
                                                        $rendered[$days[$currentIdx + $offset]['day']][$slot['key']] = true;
                                                    }
                                                @endphp

                                                {{-- Vertical Breakfast / Break Column --}}
                                                <td rowspan="{{ $rowspan }}" class="p-1 sm:p-1.5 align-middle text-center">
                                                    <div class="h-full min-h-[95px] w-full rounded-2xl bg-gradient-to-b from-[#fffbeb] via-[#fef08a]/80 to-[#fef08a] border-2 border-amber-300 p-3 flex flex-col items-center justify-center gap-1.5 shadow-sm text-amber-950 relative overflow-hidden group">
                                                        <!-- Decorative sparkles -->
                                                        <span class="absolute top-2 left-2 text-amber-400 text-xs">✦</span>
                                                        <span class="absolute bottom-2 right-2 text-amber-400 text-xs">★</span>
                                                        
                                                        <!-- Steaming Coffee/Tea Mug -->
                                                        <div class="relative transform group-hover:scale-110 transition-transform">
                                                            <svg class="h-8 w-8 sm:h-9 sm:w-9 text-amber-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                                                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                                                <line x1="6" y1="1" x2="6" y2="4"/>
                                                                <line x1="10" y1="1" x2="10" y2="4"/>
                                                                <line x1="14" y1="1" x2="14" y2="4"/>
                                                            </svg>
                                                        </div>

                                                        <!-- Playful Break Title -->
                                                        @if($isAdmin)
                                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="font-black text-sm sm:text-base text-amber-950 tracking-wider hover:underline hover:text-amber-800 transition-colors uppercase leading-tight font-display">
                                                                {{ $targetText }}
                                                            </button>
                                                        @else
                                                            <span class="font-black text-sm sm:text-base text-amber-950 tracking-wider uppercase leading-tight font-display">
                                                                {{ $targetText }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            @else
                                                <td class="p-1 sm:p-1.5 align-middle">
                                                    @if($entry)
                                                        @php
                                                            $c = $entry->color;
                                                            $hasCustomColor = ($c && $c !== 'slate');
                                                            $cardBg = $hasCustomColor ? match($c) {
                                                                'blue'    => 'bg-blue-50 text-blue-950 border-blue-200',
                                                                'indigo'  => 'bg-indigo-50 text-indigo-950 border-indigo-200',
                                                                'violet'  => 'bg-violet-50 text-violet-950 border-violet-200',
                                                                'purple'  => 'bg-purple-50 text-purple-950 border-purple-200',
                                                                'pink'    => 'bg-pink-50 text-pink-950 border-pink-200',
                                                                'red'     => 'bg-red-50 text-red-950 border-red-200',
                                                                'orange'  => 'bg-orange-50 text-orange-950 border-orange-200',
                                                                'amber'   => 'bg-amber-50 text-amber-950 border-amber-200',
                                                                'yellow'  => 'bg-yellow-50 text-yellow-950 border-yellow-200',
                                                                'green'   => 'bg-green-50 text-green-950 border-green-200',
                                                                'emerald' => 'bg-emerald-50 text-emerald-950 border-emerald-200',
                                                                'teal'    => 'bg-teal-50 text-teal-950 border-teal-200',
                                                                'cyan'    => 'bg-cyan-50 text-cyan-950 border-cyan-200',
                                                                'sky'     => 'bg-sky-50 text-sky-950 border-sky-200',
                                                                default   => "{$rowPastelTheme['bg']} {$rowPastelTheme['text']} {$rowPastelTheme['border']}",
                                                            } : "{$rowPastelTheme['bg']} {$rowPastelTheme['text']} {$rowPastelTheme['border']}";
                                                        @endphp

                                                        @if($isAdmin)
                                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full h-full min-h-[58px] rounded-2xl border {{ $cardBg }} px-3 py-2.5 text-center transition-all hover:scale-[1.02] hover:shadow-md flex flex-col items-center justify-center">
                                                                <div class="text-xs sm:text-sm font-black truncate max-w-full leading-tight font-display">
                                                                    {{ $entry->subject?->name ?? 'Subject' }}
                                                                </div>
                                                                @if($entry->teacher?->name)
                                                                    <div class="mt-1 text-[10px] font-bold opacity-75 truncate max-w-full">
                                                                        {{ $entry->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($entry->room)
                                                                    <div class="mt-0.5 text-[9px] font-bold opacity-65 truncate max-w-full">
                                                                        📍 {{ $entry->room }}
                                                                    </div>
                                                                @endif
                                                            </button>
                                                        @else
                                                            <div class="w-full h-full min-h-[58px] rounded-2xl border {{ $cardBg }} px-3 py-2.5 text-center flex flex-col items-center justify-center shadow-xs">
                                                                <div class="text-xs sm:text-sm font-black truncate max-w-full leading-tight font-display">
                                                                    {{ $entry->subject?->name ?? 'Subject' }}
                                                                </div>
                                                                @if($entry->teacher?->name)
                                                                    <div class="mt-1 text-[10px] font-bold opacity-75 truncate max-w-full">
                                                                        {{ $entry->teacher->name }}
                                                                    </div>
                                                                @endif
                                                                @if($entry->room)
                                                                    <div class="mt-0.5 text-[9px] font-bold opacity-65 truncate max-w-full">
                                                                        📍 {{ $entry->room }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @else
                                                        {{-- Empty Slot Cell --}}
                                                        @if($isAdmin)
                                                            <button type="button" wire:click="selectSlot({{ $dayNum }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full h-full min-h-[58px] rounded-2xl border-2 border-dashed border-slate-200 bg-white/80 px-2 py-2.5 text-center text-xs font-bold text-slate-400 hover:border-sky-400 hover:bg-sky-50/50 hover:text-sky-600 transition-all flex items-center justify-center gap-1" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                                                <span>+</span> Add
                                                            </button>
                                                        @else
                                                            <div class="w-full h-full min-h-[58px] flex items-center justify-center text-slate-300 font-black text-sm">
                                                                —
                                                            </div>
                                                        @endif
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Poster Footer (Learn Grow Succeed, Navy Values Capsule, Globe & Plant) --}}
                    @include('partials.timetable.footer')

                </div>
            @endif
        </div>

        {{-- Admin Form Modal --}}
        @if($isAdmin)
            <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'timetable-form') open = true" x-on:close.window="open = false" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" role="dialog" aria-modal="true">
                <div class="flex min-h-screen items-center justify-center px-4 py-6">
                    <div x-on:click="open = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                    
                    <div x-on:click.stop class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl border border-slate-100">
                        <h3 class="text-lg font-black text-slate-900">{{ $editingId ? 'Edit Entry' : 'Add Entry' }}</h3>
                        <p class="mt-1 text-sm text-slate-600">Fill in the details below</p>

                        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-xs font-bold uppercase text-slate-700">Day</label>
                                <select wire:model.live="entryDay" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm">
                                    <option value="1">Monday</option>
                                    <option value="2">Tuesday</option>
                                    <option value="3">Wednesday</option>
                                    <option value="4">Thursday</option>
                                    <option value="5">Friday</option>
                                    <option value="6">Saturday</option>
                                </select>
                                @error('entryDay') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-bold uppercase text-slate-700">Start Time</label>
                                <input wire:model="startsAt" type="time" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm">
                                @error('startsAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-bold uppercase text-slate-700">End Time</label>
                                <input wire:model="endsAt" type="time" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm">
                                @error('endsAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="checkbox" wire:model.live="isBreak" class="h-4 w-4 rounded border-slate-350 text-blue-600 focus:ring-blue-500/20">
                                    <span class="text-sm font-bold text-slate-700">Is this a Break / Interval Slot?</span>
                                </label>
                            </div>

                            @if($isBreak)
                                <div class="md:col-span-2">
                                    <label class="text-xs font-bold uppercase text-slate-700">Break Label / Text</label>
                                    <input wire:model="breakText" type="text" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm" placeholder="e.g. BREAK, ZUHR - BREAK, Lunch, Jumat Prayer">
                                    @error('breakText') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @else
                                <div>
                                    <label class="text-xs font-bold uppercase text-slate-700">Subject</label>
                                    <select wire:model.live="subjectId" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm">
                                        <option value="">Select Subject</option>
                                        @foreach($this->subjects as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('subjectId') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-bold uppercase text-slate-700">Teacher</label>
                                    <select wire:model.live="teacherId" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm">
                                        <option value="">Select Teacher</option>
                                        @foreach($this->teachers as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('teacherId') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="text-xs font-bold uppercase text-slate-700">Room (Optional)</label>
                                    <input wire:model="room" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm" placeholder="e.g. Lab 1">
                                    @error('room') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="md:col-span-2">
                                <label class="text-xs font-bold uppercase text-slate-700 block mb-2">Color Theme</label>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $colorsList = [
                                            'slate' => 'bg-slate-500',
                                            'blue' => 'bg-blue-500',
                                            'indigo' => 'bg-indigo-500',
                                            'violet' => 'bg-violet-500',
                                            'purple' => 'bg-purple-500',
                                            'pink' => 'bg-pink-500',
                                            'red' => 'bg-red-500',
                                            'orange' => 'bg-orange-500',
                                            'amber' => 'bg-amber-500',
                                            'yellow' => 'bg-yellow-400',
                                            'green' => 'bg-green-500',
                                            'emerald' => 'bg-emerald-500',
                                            'teal' => 'bg-teal-500',
                                            'cyan' => 'bg-cyan-500',
                                            'sky' => 'bg-sky-500',
                                        ];
                                    @endphp
                                    @foreach($colorsList as $colorKey => $colorClass)
                                        <button type="button" wire:click="$set('color', '{{ $colorKey }}')" 
                                                class="h-7 w-7 rounded-full {{ $colorClass }} transition-all focus:outline-none {{ $color === $colorKey ? 'ring-2 ring-offset-2 ring-slate-800 scale-110' : 'opacity-85 hover:opacity-100' }}"
                                                title="{{ ucfirst($colorKey) }}" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait"></button>
                                    @endforeach
                                </div>
                                @error('color') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            @if(!$editingId)
                                <div class="md:col-span-2 mt-2">
                                    <label class="flex items-center gap-3 cursor-pointer select-none">
                                        <input type="checkbox" wire:model="applyToAllDays" class="h-4 w-4 rounded border-slate-350 text-blue-600 focus:ring-blue-500/20">
                                        <span class="text-xs font-bold text-slate-650">Apply this slot/break to all days of the week (Mon-Sat)</span>
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                            @if($editingId)
                                <button type="button" wire:click="delete({{ $editingId }})" x-on:click="open = false" onclick="return confirm('Delete this entry?')" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-sm transition-colors" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait" aria-label="Delete">
                                    Delete
                                </button>
                            @endif
                            <button type="button" x-on:click="open = false" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                                Cancel
                            </button>
                            <button type="button" wire:click="save" x-on:click="open = false" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-sm transition-colors" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait">
                                {{ $editingId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div wire:key="timetable-empty" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 sm:p-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm text-slate-400 mb-3 border border-slate-100">
                <svg class="h-7 w-7 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-700">No Class Selected</h3>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-sm mx-auto">Please select a class from the list above to view or modify its weekly schedule.</p>
        </div>
    @endif
</div>
