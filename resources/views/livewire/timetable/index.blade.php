<div class="space-y-6">
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
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
                <h2 class="text-4xl font-bold text-white tracking-tight">Timetable</h2>
                <p class="mt-2 text-base font-medium" style="color:#93c5fd;">Manage class schedules and time slots</p>
            </div>
            <a href="{{ route('more-features') }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10"
               style="background:rgba(255,255,255,0.12);">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
        </div>
    </div>

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
        <div wire:key="timetable-grid-{{ $classId }}" class="rounded-2xl bg-white p-6 shadow-lg">
            <h3 class="text-lg font-black text-slate-900">Weekly Schedule</h3>
            <p class="mt-1 text-sm text-slate-600">Click a time slot to add or edit an entry</p>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full border-2 border-slate-200">
                    <thead>
                        <tr class="bg-blue-600">
                            <th class="border-r-2 border-white px-4 py-3 text-left text-xs font-bold uppercase text-white">Time</th>
                            @foreach($days as $d)
                                <th class="border-r-2 border-white px-4 py-3 text-center text-xs font-bold uppercase text-white">{{ $d['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $slot)
                            <tr class="border-b-2 border-slate-200">
                                <td class="border-r-2 border-slate-200 bg-slate-50 px-4 py-3 text-xs font-bold text-slate-700">
                                    {{ $slot['label'] }}
                                </td>
                                @foreach($days as $d)
                                    @php
                                        $entry = $slotMap[$d['day']][$slot['key']] ?? null;
                                    @endphp
                                    <td class="border-r-2 border-slate-200 p-2">
                                        @if($entry)
                                            @php
                                                $c = $entry->color ?? 'slate';
                                                $colorClasses = match($c) {
                                                    'blue'     => 'bg-blue-50 border-blue-200 hover:bg-blue-100/70 text-blue-900 border-l-4 border-l-blue-500',
                                                    'indigo'   => 'bg-indigo-50 border-indigo-200 hover:bg-indigo-100/70 text-indigo-900 border-l-4 border-l-indigo-500',
                                                    'violet'   => 'bg-violet-50 border-violet-200 hover:bg-violet-100/70 text-violet-900 border-l-4 border-l-violet-500',
                                                    'purple'   => 'bg-purple-50 border-purple-200 hover:bg-purple-100/70 text-purple-900 border-l-4 border-l-purple-500',
                                                    'pink'     => 'bg-pink-50 border-pink-200 hover:bg-pink-100/70 text-pink-900 border-l-4 border-l-pink-500',
                                                    'red'      => 'bg-red-50 border-red-200 hover:bg-red-100/70 text-red-900 border-l-4 border-l-red-500',
                                                    'orange'   => 'bg-orange-50 border-orange-200 hover:bg-orange-100/70 text-orange-900 border-l-4 border-l-orange-500',
                                                    'amber'    => 'bg-amber-50 border-amber-200 hover:bg-amber-100/70 text-amber-900 border-l-4 border-l-amber-500',
                                                    'yellow'   => 'bg-yellow-50 border-yellow-200 hover:bg-yellow-100/70 text-yellow-900 border-l-4 border-l-yellow-500',
                                                    'green'    => 'bg-green-50 border-green-200 hover:bg-green-100/70 text-green-900 border-l-4 border-l-green-500',
                                                    'emerald'  => 'bg-emerald-50 border-emerald-200 hover:bg-emerald-100/70 text-emerald-900 border-l-4 border-l-emerald-500',
                                                    'teal'     => 'bg-teal-50 border-teal-200 hover:bg-teal-100/70 text-teal-900 border-l-4 border-l-teal-500',
                                                    'cyan'     => 'bg-cyan-50 border-cyan-200 hover:bg-cyan-100/70 text-cyan-900 border-l-4 border-l-cyan-500',
                                                    'sky'      => 'bg-sky-50 border-sky-200 hover:bg-sky-100/70 text-sky-900 border-l-4 border-l-sky-500',
                                                    default    => 'bg-slate-100 border-slate-200 hover:bg-slate-200/70 text-slate-900 border-l-4 border-l-slate-400',
                                                };
                                            @endphp

                                            @if($isAdmin)
                                                <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg border-2 p-3 text-left transition-all {{ $colorClasses }}">
                                                    @if($entry->is_break)
                                                        <div class="flex flex-col items-center justify-center py-2 text-center uppercase tracking-wider text-[11px] font-black leading-tight">
                                                            <span>{{ $entry->break_text ?? 'BREAK' }}</span>
                                                        </div>
                                                    @else
                                                        <div class="text-sm font-bold leading-tight">{{ $entry->subject?->name }}</div>
                                                        <div class="mt-1 text-xs opacity-80 leading-none">{{ $entry->teacher?->name ?? 'No teacher' }}</div>
                                                        @if($entry->room)
                                                            <div class="mt-1 text-[11px] opacity-75 font-semibold">Room: {{ $entry->room }}</div>
                                                        @endif
                                                    @endif
                                                </button>
                                            @else
                                                <div class="rounded-lg border-2 p-3 {{ $colorClasses }}">
                                                    @if($entry->is_break)
                                                        <div class="flex flex-col items-center justify-center py-2 text-center uppercase tracking-wider text-[11px] font-black leading-tight">
                                                            <span>{{ $entry->break_text ?? 'BREAK' }}</span>
                                                        </div>
                                                    @else
                                                        <div class="text-sm font-bold leading-tight">{{ $entry->subject?->name }}</div>
                                                        <div class="mt-1 text-xs opacity-80 leading-none">{{ $entry->teacher?->name ?? 'No teacher' }}</div>
                                                        @if($entry->room)
                                                            <div class="mt-1 text-[11px] opacity-75 font-semibold">Room: {{ $entry->room }}</div>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            @if($isAdmin)
                                                <button type="button" wire:click="selectSlot({{ $d['day'] }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-3 text-xs text-slate-400 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                                    + Add
                                                </button>
                                            @else
                                                <div class="p-3 text-center text-xs text-slate-300">—</div>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

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
                                        <span class="text-sm font-semibold text-slate-600">Apply this slot/break to all days of the week (Mon-Fri)</span>
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
