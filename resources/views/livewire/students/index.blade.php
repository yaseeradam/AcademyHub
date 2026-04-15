<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Total Students --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $this->stats['total'] }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Students</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 10 12 5 2 10l10 5 10-5z"/>
                        <path d="M6 12v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-5"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Boys --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $this->stats['boys'] }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Boys</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="10" cy="14" r="5"/>
                        <path d="M13.5 10.5 21 3"/><path d="M16 3h5v5"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Girls --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-400 to-teal-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $this->stats['girls'] }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Girls</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="9" r="5"/>
                        <path d="M12 14v7"/><path d="M9 18h6"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Alumni --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-pink-400 to-rose-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $this->stats['alumni'] }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Alumni</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 10 12 5 2 10l10 5 10-5z"/>
                        <path d="M6 12v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-5"/>
                        <path d="M2 10v6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('status'))
        <x-alert type="success" :message="session('status')" />
    @endif
    @if (session('error'))
        <x-alert type="error" :message="session('error')" />
    @endif

    {{-- Table Card --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">

        {{-- Card Header --}}
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-base font-bold text-slate-800">All Students</div>
                <div class="mt-0.5 text-xs text-slate-400">Manage, search and filter student records</div>
            </div>
            <div class="flex items-center gap-2">
                <x-export type="students" :filters="[
                    'class'   => $this->classFilter,
                    'section' => $this->sectionFilter,
                    'status'  => $this->statusFilter,
                    'search'  => $this->search,
                ]" />
                @if (auth()->user()?->role === 'admin')
                    <a href="{{ route('students.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:from-orange-500 hover:to-amber-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Student
                    </a>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <select wire:model.live="classFilter" class="select rounded-xl border-slate-200 text-sm">
                    <option value="all">All Classes</option>
                    @foreach ($this->classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="sectionFilter" class="select rounded-xl border-slate-200 text-sm">
                    <option value="all">All Sections</option>
                    @foreach ($this->sections as $section)
                        @if ($this->classFilter === 'all')
                            <option value="{{ $section }}">{{ $section }}</option>
                        @else
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endif
                    @endforeach
                </select>

                <select wire:model.live="statusFilter" class="select rounded-xl border-slate-200 text-sm">
                    <option value="all">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Graduated">Graduated</option>
                    <option value="Expelled">Expelled</option>
                </select>

                <div class="relative">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Search students..."
                        class="input rounded-xl border-slate-200 pl-9 text-sm" />
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 text-left">
                            <input type="checkbox" class="checkbox-custom" value="all" />
                        </th>
                        <th class="px-5 py-3 text-left cursor-pointer hover:text-slate-700" wire:click="sortBy('last_name')">
                            <div class="flex items-center gap-1">
                                Student
                                @if($sortBy === 'last_name')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        @if($sortDir === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3 text-left cursor-pointer hover:text-slate-700" wire:click="sortBy('admission_number')">
                            <div class="flex items-center gap-1">
                                Admission No
                                @if($sortBy === 'admission_number')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        @if($sortDir === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3 text-left">Class / Section</th>
                        <th class="px-5 py-3 text-left cursor-pointer hover:text-slate-700" wire:click="sortBy('gender')">
                            <div class="flex items-center gap-1">
                                Gender
                                @if($sortBy === 'gender')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        @if($sortDir === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3 text-left">Guardian</th>
                        <th class="px-5 py-3 text-left cursor-pointer hover:text-slate-700" wire:click="sortBy('status')">
                            <div class="flex items-center gap-1">
                                Status
                                @if($sortBy === 'status')
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        @if($sortDir === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        @endif
                                    </svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->students as $student)
                        @php
                            $initials = collect(explode(' ', $student->full_name))
                                ->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                            $avatarColors = ['from-orange-400 to-amber-500', 'from-violet-500 to-purple-600', 'from-cyan-400 to-teal-500', 'from-pink-400 to-rose-500'];
                            $colorClass = $avatarColors[$student->id % 4];
                        @endphp
                        <tr class="group bg-white transition hover:bg-slate-50/80" wire:loading.class="opacity-50">
                            <td class="px-5 py-4">
                                <input type="checkbox" class="checkbox-custom" value="{{ $student->id }}" />
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($student->passport_photo_url)
                                        <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}"
                                            class="h-10 w-10 rounded-full object-cover ring-2 ring-slate-100">
                                    @else
                                        <div class="grid h-10 w-10 flex-shrink-0 place-items-center rounded-full bg-gradient-to-br {{ $colorClass }} text-sm font-bold text-white">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-800">{{ $student->full_name }}</div>
                                        <div class="truncate text-xs text-slate-400">{{ $student->schoolClass?->name }} • {{ $student->section?->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    {{ $student->admission_number }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                {{ $student->schoolClass?->name }} / {{ $student->section?->name }}
                            </td>
                            <td class="px-5 py-4">
                                @if($student->gender === 'Male')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span> Male
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-pink-50 px-2.5 py-1 text-xs font-semibold text-pink-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-pink-500"></span> Female
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-700">{{ $student->guardian_name ?: '—' }}</div>
                                <div class="text-xs text-slate-400">{{ $student->guardian_phone ?: '' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusStyle = match ($student->status) {
                                        'Active'    => 'bg-emerald-50 text-emerald-600',
                                        'Graduated' => 'bg-blue-50 text-blue-600',
                                        default     => 'bg-amber-50 text-amber-600',
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyle }}">
                                    {{ $student->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('students.show', ['student' => $student]) }}"
                                    class="inline-flex items-center gap-1 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 transition hover:bg-orange-100">
                                    View
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-600">No students found</div>
                                    <div class="text-xs text-slate-400">Try adjusting your filters or add a new student</div>
                                    @if(auth()->user()?->role === 'admin')
                                        <a href="{{ route('students.create') }}"
                                            class="mt-1 inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 px-4 py-2 text-sm font-bold text-white shadow-sm">
                                            Add Student
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $this->students->links() }}
        </div>
    </div>

</div>
