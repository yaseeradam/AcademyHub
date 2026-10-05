<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">

        {{-- Total Students --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 p-3.5 sm:p-5 lg:p-6 text-white shadow-md active:scale-[0.98] transition-transform">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="text-xl sm:text-3xl lg:text-4xl font-black tracking-tight truncate">{{ $this->stats['total'] }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/90 truncate">Total Students</div>
                </div>
                <div class="grid h-8 w-8 sm:h-11 sm:w-11 lg:h-12 lg:w-12 place-items-center rounded-lg sm:rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 lg:h-6 lg:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 10 12 5 2 10l10 5 10-5z"/>
                        <path d="M6 12v5a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-5"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Boys --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-3.5 sm:p-5 lg:p-6 text-white shadow-md active:scale-[0.98] transition-transform">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="text-xl sm:text-3xl lg:text-4xl font-black tracking-tight truncate">{{ $this->stats['boys'] }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/90 truncate">Boys</div>
                </div>
                <div class="grid h-8 w-8 sm:h-11 sm:w-11 lg:h-12 lg:w-12 place-items-center rounded-lg sm:rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 lg:h-6 lg:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="10" cy="14" r="5"/>
                        <path d="M13.5 10.5 21 3"/><path d="M16 3h5v5"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Girls --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-400 to-teal-500 p-3.5 sm:p-5 lg:p-6 text-white shadow-md active:scale-[0.98] transition-transform">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="text-xl sm:text-3xl lg:text-4xl font-black tracking-tight truncate">{{ $this->stats['girls'] }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/90 truncate">Girls</div>
                </div>
                <div class="grid h-8 w-8 sm:h-11 sm:w-11 lg:h-12 lg:w-12 place-items-center rounded-lg sm:rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 lg:h-6 lg:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="9" r="5"/>
                        <path d="M12 14v7"/><path d="M9 18h6"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Alumni --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-pink-400 to-rose-500 p-3.5 sm:p-5 lg:p-6 text-white shadow-md active:scale-[0.98] transition-transform">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                    <div class="text-xl sm:text-3xl lg:text-4xl font-black tracking-tight truncate">{{ $this->stats['alumni'] }}</div>
                    <div class="mt-1 text-xs sm:text-sm font-semibold text-white/90 truncate">Alumni</div>
                </div>
                <div class="grid h-8 w-8 sm:h-11 sm:w-11 lg:h-12 lg:w-12 place-items-center rounded-lg sm:rounded-xl bg-white/20 shrink-0">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5 lg:h-6 lg:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
        <div class="flex flex-col gap-3.5 border-b border-slate-100 p-3.5 sm:p-5 sm:flex-row sm:items-center sm:justify-between">
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
                        class="inline-flex items-center gap-1.5 sm:gap-2 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold text-white shadow-sm transition hover:from-orange-500 hover:to-amber-600 active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Student
                    </a>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <div class="border-b border-slate-100 bg-slate-50/50 p-3.5 sm:p-5">
            <div class="grid grid-cols-1 gap-2.5 sm:gap-3 sm:grid-cols-2 lg:grid-cols-4">
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

        {{-- Student Grid --}}
        <div class="p-3.5 sm:p-5">
            @if($this->students->count() > 0)
                <div class="grid grid-cols-2 gap-2.5 sm:gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    @foreach ($this->students as $student)
                        @php
                            $initials = collect(explode(' ', $student->full_name))
                                ->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                            $avatarColors = ['from-orange-400 to-amber-500', 'from-violet-500 to-purple-600', 'from-cyan-400 to-teal-500', 'from-pink-400 to-rose-500'];
                            $colorClass = $avatarColors[$student->id % 4];
                            $statusStyle = match ($student->status) {
                                'Active'    => ['bar' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700'],
                                'Graduated' => ['bar' => 'bg-blue-500',    'badge' => 'bg-blue-100 text-blue-700'],
                                default     => ['bar' => 'bg-amber-500',   'badge' => 'bg-amber-100 text-amber-700'],
                            };
                        @endphp
                        <div class="group relative flex flex-col rounded-2xl bg-white border border-slate-100 shadow-sm active:scale-[0.985] sm:hover:shadow-md sm:hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            {{-- Status bar --}}
                            <div class="h-1 w-full {{ $statusStyle['bar'] }}"></div>

                            {{-- Photo --}}
                            <div class="flex flex-col items-center px-2.5 sm:px-4 pt-3.5 sm:pt-5 pb-3 sm:pb-4 gap-2 sm:gap-3">
                                @if($student->passport_photo_url)
                                    <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}"
                                        class="h-16 w-16 sm:h-20 sm:w-20 rounded-xl sm:rounded-2xl object-cover ring-2 sm:ring-4 ring-slate-100 shadow-sm">
                                @else
                                    <div class="grid h-16 w-16 sm:h-20 sm:w-20 place-items-center rounded-xl sm:rounded-2xl bg-gradient-to-br {{ $colorClass }} text-lg sm:text-xl font-black text-white ring-2 sm:ring-4 ring-slate-100 shadow-sm">
                                        {{ $initials }}
                                    </div>
                                @endif

                                {{-- Name & ADM --}}
                                <div class="text-center w-full min-w-0">
                                    <div class="text-xs sm:text-sm font-extrabold text-slate-900 truncate leading-tight">{{ $student->full_name }}</div>
                                    <div class="text-[10px] sm:text-[11px] font-semibold text-slate-400 mt-0.5 truncate">{{ $student->schoolClass?->name }} &bull; {{ $student->section?->name }}</div>
                                </div>

                                {{-- Badges row --}}
                                <div class="flex items-center justify-center gap-1 sm:gap-1.5 flex-wrap">
                                    <span class="text-[8px] sm:text-[9px] font-black px-1.5 sm:px-2 py-0.5 rounded-full {{ $statusStyle['badge'] }} uppercase tracking-wide">
                                        {{ $student->status }}
                                    </span>
                                    @if($student->gender === 'Male')
                                        <span class="text-[8px] sm:text-[9px] font-black px-1.5 sm:px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 uppercase tracking-wide">Male</span>
                                    @else
                                        <span class="text-[8px] sm:text-[9px] font-black px-1.5 sm:px-2 py-0.5 rounded-full bg-pink-100 text-pink-700 uppercase tracking-wide">Female</span>
                                    @endif
                                </div>

                                {{-- ADM number --}}
                                <span class="rounded-lg bg-slate-100 px-2 sm:px-2.5 py-0.5 sm:py-1 text-[9px] sm:text-[10px] font-bold text-slate-500">
                                    {{ $student->admission_number }}
                                </span>
                            </div>

                            {{-- Guardian --}}
                            @if($student->guardian_name)
                                <div class="mx-2.5 sm:mx-4 mb-2.5 sm:mb-3 rounded-xl bg-slate-50 px-2.5 sm:px-3 py-1.5 sm:py-2 border border-slate-100">
                                    <div class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400">Guardian</div>
                                    <div class="text-[11px] sm:text-xs font-semibold text-slate-700 truncate mt-0.5">{{ $student->guardian_name }}</div>
                                    @if($student->guardian_phone)
                                        <div class="text-[9px] sm:text-[10px] text-slate-400 truncate">{{ $student->guardian_phone }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- View Button --}}
                            <div class="px-2.5 sm:px-4 pb-2.5 sm:pb-4 mt-auto">
                                <a href="{{ route('students.show', ['student' => $student]) }}"
                                    class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 py-2 sm:py-2.5 text-[11px] sm:text-xs font-extrabold text-white shadow-sm hover:from-orange-500 hover:to-amber-600 active:scale-95 transition-all">
                                    View Profile
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-16 text-center">
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
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $this->students->links() }}
        </div>
    </div>

</div>
