<div class="space-y-6">
    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-3xl shadow-2xl" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-blue-500/10 via-transparent to-transparent"></div>
        <div class="absolute right-0 top-0 bottom-0 w-64 opacity-5 pointer-events-none">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="1"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="1"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="1"/>
            </svg>
        </div>
        <div class="relative flex flex-col gap-6 px-8 py-8 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-400">Real-Time Registry</span>
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Daily Attendance</h1>
                <p class="text-sm font-medium text-slate-400">Track and manage student presence with absolute efficiency.</p>
            </div>
            
            @if($classId && $sectionId && $students->count() > 0)
                <div class="flex flex-wrap items-center gap-3">
                    <button wire:click="markAll('Present')"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500/10 px-4 py-2.5 text-xs font-bold text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all active:scale-95 shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark All Present
                    </button>
                    <button wire:click="markAll('Absent')"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-500/10 px-4 py-2.5 text-xs font-bold text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-all active:scale-95 shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark All Absent
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="rounded-3xl bg-white p-6 shadow-md border border-slate-100">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
            {{-- Class --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">School Class</label>
                <div class="relative">
                    <select wire:model.live="classId" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Section --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Section</label>
                <div class="relative">
                    <select wire:model.live="sectionId" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none" @disabled(!$classId)>
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Date --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tracking Date</label>
                <input wire:model.live="date" type="date" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
            </div>

            {{-- Term --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Term</label>
                <div class="relative">
                    <select wire:model.live="term" class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50 appearance-none">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            {{-- Session --}}
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Academic Session</label>
                <input wire:model.live="session" type="text" placeholder="e.g. 2026/2027" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-semibold text-slate-700 bg-slate-50/50" />
            </div>
        </div>
    </div>

    {{-- Live Sheet UI --}}
    @if($classId && $sectionId)
        {{-- Real-Time Stats Bar --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            {{-- Total Active Students --}}
            <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-5 shadow-lg border border-slate-800 text-white">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Students</span>
                    <span class="rounded-full bg-slate-800 px-2.5 py-0.5 text-[10px] font-bold text-slate-300">Total</span>
                </div>
                <div class="mt-4 text-3xl font-black">{{ $students->count() }}</div>
                <div class="mt-1 text-xs font-medium text-slate-400">Enrolled in section</div>
            </div>

            {{-- Present Counter --}}
            <div class="relative overflow-hidden rounded-3xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Present</span>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-600">P</span>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-850">{{ $markCounts['Present'] ?? 0 }}</div>
                <div class="mt-1 text-xs font-semibold text-emerald-500 flex items-center gap-1">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Present'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
                    <span class="text-slate-400 font-medium">attendance rate</span>
                </div>
            </div>

            {{-- Absent Counter --}}
            <div class="relative overflow-hidden rounded-3xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="absolute top-0 left-0 right-0 h-1 bg-red-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Absent</span>
                    <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-[10px] font-bold text-red-600">A</span>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-850">{{ $markCounts['Absent'] ?? 0 }}</div>
                <div class="mt-1 text-xs font-semibold text-red-500">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Absent'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
                    <span class="text-slate-400 font-medium">truancy rate</span>
                </div>
            </div>

            {{-- Late Counter --}}
            <div class="relative overflow-hidden rounded-3xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Late</span>
                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-600">L</span>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-850">{{ $markCounts['Late'] ?? 0 }}</div>
                <div class="mt-1 text-xs font-semibold text-amber-500">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Late'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
                    <span class="text-slate-400 font-medium">tardiness rate</span>
                </div>
            </div>

            {{-- Excused Counter --}}
            <div class="relative overflow-hidden rounded-3xl bg-white p-5 shadow-sm border border-slate-100 transition-all hover:shadow-md">
                <div class="absolute top-0 left-0 right-0 h-1 bg-purple-500"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Excused</span>
                    <span class="rounded-full bg-purple-50 px-2.5 py-0.5 text-[10px] font-bold text-purple-600">E</span>
                </div>
                <div class="mt-4 text-3xl font-black text-slate-850">{{ $markCounts['Excused'] ?? 0 }}</div>
                <div class="mt-1 text-xs font-semibold text-purple-500">
                    <span>{{ $students->count() > 0 ? round((($markCounts['Excused'] ?? 0) / $students->count()) * 100) : 0 }}%</span>
                    <span class="text-slate-400 font-medium">excuse rate</span>
                </div>
            </div>
        </div>

        {{-- Interactive Marking Sheet Card --}}
        <div class="rounded-3xl bg-white shadow-md border border-slate-100 overflow-hidden">
            {{-- Header Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="relative max-w-xs">
                        <input wire:model.live="search" type="text" placeholder="Search by name or ID..."
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 font-medium text-slate-700 bg-white" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input wire:model.live="onlyExceptions" type="checkbox" class="h-4 w-4 rounded text-blue-600 focus:ring-blue-500/20 border-slate-300" />
                        <span class="text-xs font-bold text-slate-650">Exceptions Only</span>
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400">Marking Date:</span>
                    <span class="text-xs font-extrabold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">
                        {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
                    </span>
                </div>
            </div>

            {{-- Student List --}}
            @if($visibleStudents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="px-6 py-4 text-left">Student Profile</th>
                                <th class="px-6 py-4 text-center">Status Control (1-Click)</th>
                                <th class="px-6 py-4 text-left">Internal Note (Autosaved on Save)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($visibleStudents as $student)
                                @php 
                                    $status = $marks[$student->id]['status'] ?? 'Present'; 
                                @endphp
                                <tr class="hover:bg-slate-50/30 transition-colors group">
                                    {{-- Student Info --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if($student->passport_photo_url)
                                                <img src="{{ $student->passport_photo_url }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-slate-100 flex-shrink-0" />
                                            @else
                                                <div class="grid h-10 w-10 flex-shrink-0 place-items-center rounded-full bg-slate-800 text-xs font-extrabold text-white ring-2 ring-slate-100">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $student->full_name }}</div>
                                                <div class="text-[11px] font-semibold text-slate-400">ADM: {{ $student->admission_number }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Segmented Controls --}}
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center">
                                            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/50 shadow-inner">
                                                {{-- Present --}}
                                                <button wire:click="setMark({{ $student->id }}, 'Present')"
                                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-extrabold transition-all duration-200 {{ $status === 'Present' ? 'bg-emerald-500 text-white shadow-md ring-2 ring-emerald-500/20 scale-[1.02]' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Present' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                                    Present
                                                </button>

                                                {{-- Absent --}}
                                                <button wire:click="setMark({{ $student->id }}, 'Absent')"
                                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-extrabold transition-all duration-200 {{ $status === 'Absent' ? 'bg-red-500 text-white shadow-md ring-2 ring-red-500/20 scale-[1.02]' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Absent' ? 'bg-white' : 'bg-red-500' }}"></span>
                                                    Absent
                                                </button>

                                                {{-- Late --}}
                                                <button wire:click="setMark({{ $student->id }}, 'Late')"
                                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-extrabold transition-all duration-200 {{ $status === 'Late' ? 'bg-amber-500 text-white shadow-md ring-2 ring-amber-500/20 scale-[1.02]' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Late' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                                    Late
                                                </button>

                                                {{-- Excused --}}
                                                <button wire:click="setMark({{ $student->id }}, 'Excused')"
                                                        class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-extrabold transition-all duration-200 {{ $status === 'Excused' ? 'bg-purple-500 text-white shadow-md ring-2 ring-purple-500/20 scale-[1.02]' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $status === 'Excused' ? 'bg-white' : 'bg-purple-500' }}"></span>
                                                    Excused
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Note Input --}}
                                    <td class="px-6 py-4">
                                        <div class="relative">
                                            <input wire:model.blur="marks.{{ $student->id }}.note" type="text" placeholder="Add custom notes (e.g. sick leave)..."
                                                   class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 font-semibold text-slate-700 bg-slate-50/30" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Bottom Sheet Controls --}}
                <div class="border-t border-slate-100 bg-slate-50 px-8 py-5 flex items-center justify-between">
                    <div class="text-xs font-semibold text-slate-400 flex items-center gap-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        All entries are stored temporarily in browser memory until saved.
                    </div>

                    <button wire:click="save"
                            class="inline-flex items-center gap-2 rounded-2xl bg-amber-500 hover:bg-amber-600 px-7 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-amber-500/25 hover:shadow-xl hover:shadow-amber-500/35 transition-all duration-200 active:scale-95 group"
                            wire:loading.attr="disabled">
                        <svg class="h-4 w-4 text-white group-hover:rotate-12 transition-transform" wire:loading.remove wire:target="save" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span wire:loading.remove wire:target="save">Commit & Save Attendance</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving Sheets...
                        </span>
                    </button>
                </div>
            @else
                <div class="px-6 py-16 text-center bg-white">
                    <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                    </div>
                    <div class="text-sm font-bold text-slate-700">No active students found</div>
                    <p class="mt-1.5 text-xs text-slate-400 max-w-sm mx-auto">There are no active students matching the search criteria or enrolled in the selected class section.</p>
                </div>
            @endif
        </div>
    @else
        {{-- Selector Empty State --}}
        <div class="rounded-3xl bg-white border border-slate-100 shadow-md p-16 text-center">
            <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-2xl bg-blue-50/50 text-blue-500 ring-4 ring-blue-500/5">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-.621-.504-1.125-1.125-1.125H9.75M8.25 21h8.25c.621 0 1.125-.504 1.125-1.125V4.125c0-.621-.504-1.125-1.125-1.125H8.25c-.621 0-1.125.504-1.125 1.125v15.75c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Select Class & Section to Begin</h3>
            <p class="mt-1.5 text-xs text-slate-400 max-w-xs mx-auto">Please choose a school class and section from the control filter card above to load the student registry and start tracking daily attendance.</p>
        </div>
    @endif
</div>
