<div class="space-y-6">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-500 via-blue-500 to-cyan-600 p-8 shadow-2xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-white">Timetable</h1>
                <p class="mt-2 text-sky-100">Weekly class schedule management</p>
            </div>
            <a href="{{ route('more-features') }}" class="rounded-xl bg-white/20 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/30">
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
                                            @if($isAdmin)
                                                <button type="button" wire:click="edit({{ $entry->id }})" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg border-2 border-blue-200 bg-blue-50 p-3 text-left hover:border-blue-400 hover:bg-blue-100">
                                                    <div class="text-sm font-bold text-blue-900">{{ $entry->subject?->name }}</div>
                                                    <div class="mt-1 text-xs text-blue-700">{{ $entry->teacher?->name ?? 'No teacher' }}</div>
                                                    @if($entry->room)
                                                        <div class="mt-1 text-xs text-blue-600">Room: {{ $entry->room }}</div>
                                                    @endif
                                                </button>
                                            @else
                                                <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-3">
                                                    <div class="text-sm font-bold text-blue-900">{{ $entry->subject?->name }}</div>
                                                    <div class="mt-1 text-xs text-blue-700">{{ $entry->teacher?->name ?? 'No teacher' }}</div>
                                                    @if($entry->room)
                                                        <div class="mt-1 text-xs text-blue-600">Room: {{ $entry->room }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            @if($isAdmin)
                                                <button type="button" wire:click="selectSlot({{ $d['day'] }}, @js($slot['start']), @js($slot['end']))" x-data x-on:click="$dispatch('open-modal', 'timetable-form')" class="w-full rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-3 text-xs text-slate-400 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600">
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
                    <div x-on:click="open = false" class="fixed inset-0 bg-black/50 transition-opacity"></div>
                    
                    <div x-on:click.stop class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl">
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
                                <label class="text-xs font-bold uppercase text-slate-700">Start Time</label>
                                <input wire:model="startsAt" type="time" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
                                @error('startsAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-bold uppercase text-slate-700">End Time</label>
                                <input wire:model="endsAt" type="time" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm">
                                @error('endsAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
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

                            <div>
                                <label class="text-xs font-bold uppercase text-slate-700">Room (Optional)</label>
                                <input wire:model="room" class="mt-2 w-full rounded-lg border-2 border-slate-200 px-4 py-2.5 text-sm" placeholder="e.g. Lab 1">
                                @error('room') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            @if($editingId)
                                <button type="button" wire:click="delete({{ $editingId }})" x-on:click="open = false" onclick="return confirm('Delete this entry?')" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">
                                    Delete
                                </button>
                            @endif
                            <button type="button" x-on:click="open = false" class="rounded-lg border-2 border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button type="button" wire:click="save" x-on:click="open = false" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
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
