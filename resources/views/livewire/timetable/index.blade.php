<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl shadow-md bg-slate-900">
        <div class="relative flex items-center justify-between px-8 py-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-400"></span>
                    <span class="text-xs font-semibold uppercase tracking-widest text-blue-300">Weekly Schedule</span>
                </div>
                <h2 class="text-3xl font-black text-white tracking-tight">School Timetable</h2>
                <p class="mt-1 text-sm text-slate-400">Manage and view the academic timetable and slots</p>
            </div>
            <a href="{{ route('more-features') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white transition bg-white/10 hover:bg-white/20">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- Class Selector --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-slate-100">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Select Class</h3>
                <p class="text-sm text-slate-500">Choose a class to load its weekly timetable</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="classId" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">Select Class ({{ collect($this->classes)->count() }} available)</option>
                    @foreach($this->classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @if($classId)
                    <a href="{{ route('timetable.pdf', ['class_id' => $classId]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-blue-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if($classId)
        <div wire:key="timetable-container-{{ $classId }}" class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">
            
            {{-- View Mode Switcher Header --}}
            <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/80 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center bg-slate-200/60 p-1 rounded-lg self-start">
                    <button wire:click="$set('viewMode', 'grid')" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all {{ $viewMode === 'grid' ? 'bg-white shadow text-slate-800' : 'text-slate-600 hover:text-slate-900' }}">
                        📅 Grid View
                    </button>
                    <button wire:click="$set('viewMode', 'daily')" class="px-4 py-1.5 text-xs font-bold rounded-md transition-all {{ $viewMode === 'daily' ? 'bg-white shadow text-slate-800' : 'text-slate-600 hover:text-slate-900' }}">
                        📋 Daily List View
                    </button>
                </div>
                
                @if($viewMode === 'daily')
                    <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0">
                        @foreach($days as $d)
                            <button wire:click="$set('activeDayTab', {{ $d['day'] }})" class="px-3 py-1.5 text-xs font-bold rounded-md transition-all whitespace-nowrap {{ $activeDayTab === $d['day'] ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-200/50 text-slate-700 hover:bg-slate-200' }}">
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
                <div class="p-6 space-y-4">
                    @if($dayEntries->isEmpty())
                        <div class="text-center py-16 text-slate-400">
                            <span class="text-3xl block mb-2">📅</span>
                            <p class="font-medium text-sm">No classes or breaks scheduled for {{ $this->dayLabel($activeDayTab) }}.</p>
                            @if($isAdmin)
                                <div class="mt-4">
                                    <button type="button" wire:click="selectSlot({{ $activeDayTab }}, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-blue-700">
                                        + Add Slot
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        @if($isAdmin)
                            <div class="flex justify-end mb-2">
                                <button type="button" wire:click="selectSlot({{ $activeDayTab }}, '08:00', '09:00')" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-blue-700">
                                    + Add Slot
                                </button>
                            </div>
                        @endif
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($dayEntries as $entry)
                                @if($entry->is_break)
                                    <div class="md:col-span-2 flex items-center justify-between p-4 rounded-xl border border-amber-200 bg-amber-50 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <span class="text-lg">☕</span>
                                            <div>
                                                <div class="text-xs font-black text-amber-800 uppercase tracking-wider">{{ $entry->break_text ?? 'BREAK' }}</div>
                                                <div class="text-xs text-amber-700 font-bold mt-0.5">{{ substr($entry->starts_at, 0, 5) }} – {{ substr($entry->ends_at, 0, 5) }}</div>
                                            </div>
                                        </div>
                                        @if($isAdmin)
                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-bold text-amber-700 hover:text-amber-900 hover:underline">
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
                                    <div class="flex flex-col justify-between p-5 rounded-xl border-l-4 {{ $borderColor }} {{ $bgColor }} border border-slate-200/60 shadow-sm relative">
                                        <div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ substr($entry->starts_at, 0, 5) }} – {{ substr($entry->ends_at, 0, 5) }}</span>
                                                @if($entry->room)
                                                    <span class="rounded bg-slate-200/70 px-2 py-0.5 text-[10px] font-bold text-slate-600">📍 {{ $entry->room }}</span>
                                                @endif
                                            </div>
                                            <h4 class="mt-2.5 text-lg font-black text-slate-900">{{ $entry->subject?->name }}</h4>
                                            @if($entry->teacher?->name)
                                                <div class="mt-2.5 flex items-center gap-2 text-sm text-slate-600 font-medium">
                                                    <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    {{ $entry->teacher->name }}
                                                </div>
                                            @endif
                                        </div>
                                        @if($isAdmin)
                                            <div class="mt-4 flex justify-end">
                                                <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">
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
                {{-- Clean & Professional Weekly Grid View --}}
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse" style="min-width: 800px;">
                        <thead>
                            <tr class="bg-slate-800 text-white">
                                <th class="px-5 py-4 text-xs font-black uppercase tracking-wider text-center border-r border-slate-700" style="min-width: 140px; background-color: #1e293b;">
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="h-4 w-4 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Time / Day
                                    </div>
                                </th>
                                @foreach($days as $d)
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-wider border-r border-slate-700 last:border-r-0" style="min-width: 120px; background-color: #1e293b;">
                                        {{ $d['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slotIndex => $slot)
                                @php
                                    // Check if this slot is a break across ALL days
                                    $allBreak = true;
                                    $breakText = null;
                                    $breakEntry = null;
                                    foreach ($days as $d) {
                                        $entry = $slotMap[$d['day']][$slot['key']] ?? null;
                                        if (!$entry || !$entry->is_break) {
                                            $allBreak = false;
                                            break;
                                        }
                                        if ($breakText === null) {
                                            $breakText = trim($entry->break_text ?? 'BREAK');
                                            $breakEntry = $entry;
                                        }
                                    }
                                @endphp

                                @if($allBreak && $breakText)
                                    {{-- Unified Break Row --}}
                                    <tr class="bg-amber-50/50">
                                        <td class="border-b border-slate-200 text-center py-4 bg-amber-50/40 border-r border-slate-200">
                                            <span class="text-xs font-bold text-slate-500">{{ $slot['start'] }} – {{ $slot['end'] }}</span>
                                        </td>
                                        <td colspan="{{ count($days) }}" class="border-b border-slate-200 text-center py-4 bg-amber-50/80">
                                            <div class="flex items-center justify-center gap-3">
                                                <span class="text-base">☕</span>
                                                @if($isAdmin)
                                                    <button type="button" wire:click="edit({{ $breakEntry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-xs font-black uppercase tracking-[0.2em] text-amber-800 hover:text-amber-900 transition-colors hover:underline">
                                                        {{ $breakText }}
                                                    </button>
                                                @else
                                                    <span class="text-xs font-black uppercase tracking-[0.2em] text-amber-800">{{ $breakText }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="group hover:bg-slate-50/30">
                                        {{-- Time label column --}}
                                        <td class="bg-slate-50/80 px-5 py-4 text-center border-r border-slate-200 border-b border-slate-200">
                                            <span class="text-xs font-bold text-slate-700">{{ $slot['start'] }} – {{ $slot['end'] }}</span>
                                        </td>

                                        {{-- Day cells --}}
                                        @foreach($days as $d)
                                            @php
                                                $entry = $slotMap[$d['day']][$slot['key']] ?? null;
                                            @endphp

                                            <td class="px-2 py-2 border-r border-slate-200 border-b border-slate-200 last:border-r-0">
                                                @if($entry)
                                                    @if($entry->is_break)
                                                        @if($isAdmin)
                                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg px-2 py-3 text-center bg-amber-100/70 border border-amber-200 transition-all hover:bg-amber-100">
                                                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-800">{{ trim($entry->break_text ?? 'BREAK') }}</span>
                                                            </button>
                                                        @else
                                                            <div class="w-full rounded-lg px-2 py-3 text-center bg-amber-100/70 border border-amber-200">
                                                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-800">{{ trim($entry->break_text ?? 'BREAK') }}</span>
                                                            </div>
                                                        @endif
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
                                                                'blue'    => 'bg-blue-50/20',
                                                                'indigo'  => 'bg-indigo-50/20',
                                                                'violet'  => 'bg-violet-50/20',
                                                                'purple'  => 'bg-purple-50/20',
                                                                'pink'    => 'bg-pink-50/20',
                                                                'red'     => 'bg-red-50/20',
                                                                'orange'  => 'bg-orange-50/20',
                                                                'amber'   => 'bg-amber-50/20',
                                                                'yellow'  => 'bg-yellow-50/20',
                                                                'green'   => 'bg-green-50/20',
                                                                'emerald' => 'bg-emerald-50/20',
                                                                'teal'    => 'bg-teal-50/20',
                                                                'cyan'    => 'bg-cyan-50/20',
                                                                'sky'     => 'bg-sky-50/20',
                                                                default   => 'bg-slate-50/50',
                                                            };
                                                        @endphp

                                                        @if($isAdmin)
                                                            <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-xl border-l-4 {{ $borderColor }} {{ $bgColor }} border border-slate-200/80 px-3 py-3 text-left transition-all hover:shadow hover:scale-[1.01]">
                                                                <div class="text-xs font-bold text-slate-800 truncate leading-tight">{{ $entry->subject?->name }}</div>
                                                                @if($entry->teacher?->name)
                                                                    <div class="mt-1 text-[10px] text-slate-500 truncate">{{ $entry->teacher->name }}</div>
                                                                @endif
                                                                @if($entry->room)
                                                                    <div class="mt-1 text-[9px] text-slate-400 truncate">📍 {{ $entry->room }}</div>
                                                                @endif
                                                            </button>
                                                        @else
                                                            <div class="w-full rounded-xl border-l-4 {{ $borderColor }} {{ $bgColor }} border border-slate-200/80 px-3 py-3 text-left">
                                                                <div class="text-xs font-bold text-slate-800 truncate leading-tight">{{ $entry->subject?->name }}</div>
                                                                @if($entry->teacher?->name)
                                                                    <div class="mt-1 text-[10px] text-slate-500 truncate">{{ $entry->teacher->name }}</div>
                                                                @endif
                                                                @if($entry->room)
                                                                    <div class="mt-1 text-[9px] text-slate-400 truncate">📍 {{ $entry->room }}</div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endif
                                                @else
                                                    {{-- Empty slot cell --}}
                                                    @if($isAdmin)
                                                        <button type="button" wire:click="selectSlot({{ $d['day'] }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg border border-dashed border-slate-200 bg-white px-2 py-3 text-[10px] font-semibold text-slate-400 hover:border-blue-400 hover:bg-blue-50/40 hover:text-blue-600 transition-colors">
                                                            + Add
                                                        </button>
                                                    @else
                                                        <div class="text-center text-[10px] text-slate-300 py-3">—</div>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Simple Accent Footer --}}
                <div class="bg-slate-50 px-6 py-4 text-center border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 tracking-wide">
                        Be on Time • Be Prepared • Be Your Best
                    </p>
                </div>
            @endif
        </div>

        {{-- Admin Form Modal --}}
        @if($isAdmin)
            <div x-data="{ open: false }" x-on:open-modal.window="if ($event.detail === 'timetable-form') open = true" x-on:close.window="open = false" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
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
                                                title="{{ ucfirst($colorKey) }}"></button>
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
                                <button type="button" wire:click="delete({{ $editingId }})" x-on:click="open = false" onclick="return confirm('Delete this entry?')" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-sm transition-colors">
                                    Delete
                                </button>
                            @endif
                            <button type="button" x-on:click="open = false" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                                Cancel
                            </button>
                            <button type="button" wire:click="save" x-on:click="open = false" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-sm transition-colors">
                                {{ $editingId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div wire:key="timetable-empty" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <span class="text-4xl block mb-2">🏫</span>
            <h3 class="text-base font-bold text-slate-700">No Class Selected</h3>
            <p class="text-sm text-slate-500 mt-1">Please select a class from the list above to view or modify its weekly schedule.</p>
        </div>
    @endif
</div>
