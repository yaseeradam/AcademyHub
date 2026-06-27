<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background: linear-gradient(135deg, #1a2e4a 0%, #2a4a7f 50%, #1e3a5f 100%);">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, rgba(147,197,253,0.15) 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative flex items-center justify-between px-8 py-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color:#93c5fd;">Weekly Schedule</span>
                </div>
                <h2 class="text-4xl font-bold text-white tracking-tight">🏫 School Timetable</h2>
                <p class="mt-2 text-base font-medium" style="color:#93c5fd;">Stay Organized, Stay Successful!</p>
            </div>
            <a href="{{ route('more-features') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
               style="background:rgba(255,255,255,0.12);">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
        </div>
    </div>

    {{-- Class Selector --}}
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Select Class</h2>
                <p class="text-sm text-slate-600">Choose a class to view or edit its timetable</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="classId" class="rounded-lg border-2 border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    <option value="">Select Class ({{ collect($this->classes)->count() }} available)</option>
                    @foreach($this->classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @if($classId)
                    <a href="{{ route('timetable.pdf', ['class_id' => $classId]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-blue-700">
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
        <div wire:key="timetable-grid-{{ $classId }}" class="rounded-2xl bg-white shadow-lg overflow-hidden">
            {{-- Timetable Grid — Transposed: Time rows × Day columns --}}
            <div class="overflow-x-auto">
                @php
                    $dayColors = [
                        1 => ['bg' => 'bg-green-500',  'text' => 'text-white', 'lightBg' => 'bg-green-50',  'border' => 'border-green-200'],
                        2 => ['bg' => 'bg-blue-500',   'text' => 'text-white', 'lightBg' => 'bg-blue-50',   'border' => 'border-blue-200'],
                        3 => ['bg' => 'bg-purple-500',  'text' => 'text-white', 'lightBg' => 'bg-purple-50',  'border' => 'border-purple-200'],
                        4 => ['bg' => 'bg-pink-500',   'text' => 'text-white', 'lightBg' => 'bg-pink-50',   'border' => 'border-pink-200'],
                        5 => ['bg' => 'bg-indigo-500',  'text' => 'text-white', 'lightBg' => 'bg-indigo-50',  'border' => 'border-indigo-200'],
                        6 => ['bg' => 'bg-orange-500',  'text' => 'text-white', 'lightBg' => 'bg-orange-50',  'border' => 'border-orange-200'],
                    ];
                @endphp

                <table class="w-full border-collapse" style="min-width: 800px;">
                    {{-- Header Row: TIME / DAY + Day names --}}
                    <thead>
                        <tr>
                            <th class="bg-slate-700 text-white px-5 py-4 text-xs font-black uppercase tracking-wider text-center border-r border-slate-600" style="min-width: 130px;">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Time / Day
                                </div>
                            </th>
                            @foreach($days as $d)
                                @php $dc = $dayColors[$d['day']] ?? $dayColors[1]; @endphp
                                <td class="{{ $dc['bg'] }} {{ $dc['text'] }} px-4 py-4 text-center text-sm font-black uppercase tracking-wider border-r border-white/30 last:border-r-0" style="min-width: 120px;">
                                    {{ $d['label'] }}
                                </td>
                            @endforeach
                        </tr>
                    </thead>

                    {{-- Body: One row per time slot --}}
                    <tbody>
                        @php
                            // Group consecutive break slots to detect full-width break rows
                            $renderedBreakSlots = [];
                        @endphp

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
                                {{-- BREAK ROW spanning full table width --}}
                                <tr>
                                    <td colspan="{{ count($days) + 1 }}" class="border-b border-slate-200 text-center py-4" style="background: linear-gradient(135deg, #fef9c3 0%, #d9f99d 50%, #fef9c3 100%);">
                                        <div class="flex items-center justify-center gap-4">
                                            @if(str_contains(strtolower($breakText), 'lunch'))
                                                <span class="text-2xl">🍴</span>
                                            @else
                                                <span class="text-2xl">☕</span>
                                            @endif
                                            @if($isAdmin)
                                                <button type="button" wire:click="edit({{ $breakEntry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="text-base font-black uppercase tracking-[0.2em] text-slate-700 hover:text-slate-900 transition-colors">
                                                    {{ $breakText }}
                                                </button>
                                            @else
                                                <span class="text-base font-black uppercase tracking-[0.2em] text-slate-700">{{ $breakText }}</span>
                                            @endif
                                            @if(str_contains(strtolower($breakText), 'lunch'))
                                                <span class="text-2xl">🍴</span>
                                            @else
                                                <span class="text-2xl">☕</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @else
                                {{-- Normal subject row --}}
                                <tr class="group">
                                    {{-- Time label --}}
                                    <td class="bg-slate-50 px-5 py-4 text-center border-r border-slate-200 border-b border-b-slate-200">
                                        <span class="text-sm font-bold text-slate-700">{{ $slot['start'] }} – {{ $slot['end'] }}</span>
                                    </td>

                                    {{-- Each day cell --}}
                                    @foreach($days as $d)
                                        @php
                                            $entry = $slotMap[$d['day']][$slot['key']] ?? null;
                                            $dc = $dayColors[$d['day']] ?? $dayColors[1];
                                        @endphp

                                        <td class="px-2 py-2 border-r border-slate-100 border-b border-b-slate-200 last:border-r-0 {{ $dc['lightBg'] }}">
                                            @if($entry)
                                                @if($entry->is_break)
                                                    {{-- Individual break cell (not spanning full row) --}}
                                                    @if($isAdmin)
                                                        <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg px-3 py-3 text-center transition-all hover:shadow-md" style="background: linear-gradient(135deg, #fef9c3 0%, #d9f99d 100%);">
                                                            <span class="text-xs font-black uppercase tracking-wider text-slate-600">{{ trim($entry->break_text ?? 'BREAK') }}</span>
                                                        </button>
                                                    @else
                                                        <div class="w-full rounded-lg px-3 py-3 text-center" style="background: linear-gradient(135deg, #fef9c3 0%, #d9f99d 100%);">
                                                            <span class="text-xs font-black uppercase tracking-wider text-slate-600">{{ trim($entry->break_text ?? 'BREAK') }}</span>
                                                        </div>
                                                    @endif
                                                @else
                                                    {{-- Subject cell --}}
                                                    @php
                                                        $c = $entry->color ?? 'slate';
                                                        $cellStyles = match($c) {
                                                            'blue'    => 'background: linear-gradient(135deg, #dbeafe, #eff6ff); border-color: #93c5fd;',
                                                            'indigo'  => 'background: linear-gradient(135deg, #e0e7ff, #eef2ff); border-color: #a5b4fc;',
                                                            'violet'  => 'background: linear-gradient(135deg, #ede9fe, #f5f3ff); border-color: #c4b5fd;',
                                                            'purple'  => 'background: linear-gradient(135deg, #f3e8ff, #faf5ff); border-color: #d8b4fe;',
                                                            'pink'    => 'background: linear-gradient(135deg, #fce7f3, #fdf2f8); border-color: #f9a8d4;',
                                                            'red'     => 'background: linear-gradient(135deg, #fee2e2, #fef2f2); border-color: #fca5a5;',
                                                            'orange'  => 'background: linear-gradient(135deg, #ffedd5, #fff7ed); border-color: #fdba74;',
                                                            'amber'   => 'background: linear-gradient(135deg, #fef3c7, #fffbeb); border-color: #fcd34d;',
                                                            'yellow'  => 'background: linear-gradient(135deg, #fef9c3, #fefce8); border-color: #fde047;',
                                                            'green'   => 'background: linear-gradient(135deg, #dcfce7, #f0fdf4); border-color: #86efac;',
                                                            'emerald' => 'background: linear-gradient(135deg, #d1fae5, #ecfdf5); border-color: #6ee7b7;',
                                                            'teal'    => 'background: linear-gradient(135deg, #ccfbf1, #f0fdfa); border-color: #5eead4;',
                                                            'cyan'    => 'background: linear-gradient(135deg, #cffafe, #ecfeff); border-color: #67e8f9;',
                                                            'sky'     => 'background: linear-gradient(135deg, #e0f2fe, #f0f9ff); border-color: #7dd3fc;',
                                                            default   => 'background: linear-gradient(135deg, #f1f5f9, #f8fafc); border-color: #cbd5e1;',
                                                        };
                                                    @endphp

                                                    @if($isAdmin)
                                                        <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-xl border-2 px-3 py-3 text-center transition-all hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]" style="{{ $cellStyles }}">
                                                            <div class="text-sm font-bold text-slate-800 leading-tight">{{ $entry->subject?->name }}</div>
                                                            @if($entry->teacher?->name)
                                                                <div class="mt-1.5 text-[11px] text-slate-500 font-medium">{{ $entry->teacher->name }}</div>
                                                            @endif
                                                            @if($entry->room)
                                                                <div class="mt-1 text-[10px] text-slate-400 font-semibold">📍 {{ $entry->room }}</div>
                                                            @endif
                                                        </button>
                                                    @else
                                                        <div class="w-full rounded-xl border-2 px-3 py-3 text-center" style="{{ $cellStyles }}">
                                                            <div class="text-sm font-bold text-slate-800 leading-tight">{{ $entry->subject?->name }}</div>
                                                            @if($entry->teacher?->name)
                                                                <div class="mt-1.5 text-[11px] text-slate-500 font-medium">{{ $entry->teacher->name }}</div>
                                                            @endif
                                                            @if($entry->room)
                                                                <div class="mt-1 text-[10px] text-slate-400 font-semibold">📍 {{ $entry->room }}</div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            @else
                                                {{-- Empty cell --}}
                                                @if($isAdmin)
                                                    <button type="button" wire:click="selectSlot({{ $d['day'] }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-xl border-2 border-dashed border-slate-200/80 bg-white/50 px-3 py-4 text-xs text-slate-400 hover:border-blue-400 hover:bg-blue-50/50 hover:text-blue-600 transition-all hover:shadow-sm">
                                                        <svg class="mx-auto h-5 w-5 opacity-40 mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                        Add
                                                    </button>
                                                @else
                                                    <div class="px-3 py-4 text-center text-xs text-slate-300">—</div>
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

            {{-- Motivational Footer --}}
            <div class="px-6 py-4 text-center border-t border-slate-100" style="background: linear-gradient(135deg, #fef9c3 0%, #fde68a 50%, #fed7aa 100%);">
                <p class="text-sm font-bold text-slate-700 tracking-wide">
                    ⭐ Be on Time, Be Prepared, Be Your Best! ⭐
                </p>
            </div>
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
                                <select wire:model.live="entryDay" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
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
                                <input wire:model="startsAt" type="time" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
                                @error('startsAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-bold uppercase text-slate-700">End Time</label>
                                <input wire:model="endsAt" type="time" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
                                @error('endsAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-3 cursor-pointer select-none">
                                    <input type="checkbox" wire:model.live="isBreak" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                    <span class="text-sm font-bold text-slate-700">Is this a Break / Interval Slot?</span>
                                </label>
                            </div>

                            @if($isBreak)
                                <div class="md:col-span-2">
                                    <label class="text-xs font-bold uppercase text-slate-700">Break Label / Text</label>
                                    <input wire:model="breakText" type="text" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm" placeholder="e.g. BREAK, ZUHR - BREAK, Lunch, Jumat Prayer">
                                    @error('breakText') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @else
                                <div>
                                    <label class="text-xs font-bold uppercase text-slate-700">Subject</label>
                                    <select wire:model.live="subjectId" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
                                        <option value="">Select Subject</option>
                                        @foreach($this->subjects as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('subjectId') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-bold uppercase text-slate-700">Teacher</label>
                                    <select wire:model.live="teacherId" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
                                        <option value="">Select Teacher</option>
                                        @foreach($this->teachers as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('teacherId') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="text-xs font-bold uppercase text-slate-700">Room (Optional)</label>
                                    <input wire:model="room" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm" placeholder="e.g. Lab 1">
                                    @error('room') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="md:col-span-2">
                                <label class="text-xs font-bold uppercase text-slate-700 block mb-2">Color Theme</label>
                                <div class="flex flex-wrap gap-2.5">
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
                                            'yellow' => 'bg-yellow-500',
                                            'green' => 'bg-green-500',
                                            'emerald' => 'bg-emerald-500',
                                            'teal' => 'bg-teal-500',
                                            'cyan' => 'bg-cyan-500',
                                            'sky' => 'bg-sky-500',
                                        ];
                                    @endphp
                                    @foreach($colorsList as $colorKey => $colorClass)
                                        <button type="button" wire:click="$set('color', '{{ $colorKey }}')" 
                                                class="h-8 w-8 rounded-full {{ $colorClass }} transition-all focus:outline-none focus:ring-4 focus:ring-offset-2 {{ $color === $colorKey ? 'ring-4 ring-offset-2 ring-slate-800 scale-110 shadow-md' : 'opacity-85 hover:opacity-100 hover:scale-105' }}"
                                                title="{{ ucfirst($colorKey) }}"></button>
                                    @endforeach
                                </div>
                                @error('color') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            @if(!$editingId)
                                <div class="md:col-span-2 mt-2">
                                    <label class="flex items-center gap-3 cursor-pointer select-none">
                                        <input type="checkbox" wire:model="applyToAllDays" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                        <span class="text-sm font-semibold text-slate-600">Apply this slot/break to all days of the week (Mon-Sat)</span>
                                    </label>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            @if($editingId)
                                <button type="button" wire:click="delete({{ $editingId }})" x-on:click="open = false" onclick="return confirm('Delete this entry?')" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700 shadow-md transition-all">
                                    Delete
                                </button>
                            @endif
                            <button type="button" x-on:click="open = false" class="rounded-lg border-2 border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                                Cancel
                            </button>
                            <button type="button" wire:click="save" x-on:click="open = false" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-700 shadow-md transition-all">
                                {{ $editingId ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div wire:key="timetable-empty" class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <h3 class="mt-4 text-lg font-bold text-slate-900">No Class Selected</h3>
            <p class="mt-2 text-sm text-slate-600">Select a class from the dropdown above to view or manage its timetable</p>
        </div>
    @endif
</div>
