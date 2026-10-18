<div class="space-y-6 pb-12">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Attendance & Medical Leave</span>
                </span>
                <span class="text-xs text-slate-400 font-bold">&bull; Formal Roll Call Notes</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>Excuse Requests</span>
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 font-medium">
                {{ $this->isParent ? 'Notify school administration and class teachers of planned, emergency, or medical absences for your children.' : 'Review and acknowledge student absence and excuse notes submitted by parents.' }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            @if($this->isParent)
                <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-200/50 active:scale-95 transition"
                        style="background-color: #4f46e5; color: #ffffff;">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Submit Excuse Request</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Pastel KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- 1. Pending Approvals --}}
        <div class="rounded-3xl border border-amber-200 bg-white bg-gradient-to-br from-amber-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-amber-300 transition flex items-center justify-between"
             style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(254,243,199,0.85) 0%, #ffffff 100%);">
            <div class="flex items-center gap-3.5">
                <div class="h-12 w-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-600 leading-tight">
                        {{ $counts['pending'] }}
                    </div>
                    <span class="text-xs font-bold text-slate-700 block">Pending Review</span>
                    <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Awaiting school acknowledgement</span>
                </div>
            </div>
            @if($counts['pending'] > 0)
                <span class="h-3 w-3 rounded-full bg-amber-400 animate-ping"></span>
            @endif
        </div>

        {{-- 2. Approved Excuses --}}
        <div class="rounded-3xl border border-emerald-200 bg-white bg-gradient-to-br from-emerald-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-emerald-300 transition flex items-center justify-between"
             style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(236,253,245,0.85) 0%, #ffffff 100%);">
            <div class="flex items-center gap-3.5">
                <div class="h-12 w-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-600 leading-tight">
                        {{ $counts['approved'] }}
                    </div>
                    <span class="text-xs font-bold text-slate-700 block">Approved Excuses</span>
                    <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Exempted from attendance penalty</span>
                </div>
            </div>
        </div>

        {{-- 3. Total Requests --}}
        <div class="rounded-3xl border border-purple-200 bg-white bg-gradient-to-br from-purple-50 to-white p-5 shadow-xs hover:shadow-sm hover:border-purple-300 transition flex items-center justify-between"
             style="background-color: #ffffff; background-image: linear-gradient(135deg, rgba(250,245,255,0.85) 0%, #ffffff 100%);">
            <div class="flex items-center gap-3.5">
                <div class="h-12 w-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl sm:text-3xl font-black text-purple-600 leading-tight">
                        {{ $counts['all'] }}
                    </div>
                    <span class="text-xs font-bold text-slate-700 block">Total Requests Filed</span>
                    <span class="text-[10px] font-semibold text-slate-400 mt-0.5 block">Recorded this academic year</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Controls Bar --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 p-4 sm:p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button type="button" wire:click="$set('statusFilter', 'all')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <span>All Requests ({{ $counts['all'] }})</span>
            </button>
            <button type="button" wire:click="$set('statusFilter', 'pending')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <span>Pending ({{ $counts['pending'] }})</span>
            </button>
            <button type="button" wire:click="$set('statusFilter', 'approved')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <span>Approved ({{ $counts['approved'] }})</span>
            </button>
            <button type="button" wire:click="$set('statusFilter', 'rejected')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                <span>Rejected ({{ $counts['rejected'] }})</span>
            </button>
        </div>

        {{-- Right Controls: Child Selector & Search --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            @if($this->children->count() > 1)
                <select wire:model.live="selectedStudentId"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 focus:border-indigo-500 focus:ring-0">
                    <option value="">All Children</option>
                    @foreach($this->children as $ch)
                        <option value="{{ $ch->id }}">{{ $ch->full_name }} ({{ $ch->schoolClass?->name ?? 'Class' }})</option>
                    @endforeach
                </select>
            @endif

            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search excuses..."
                       class="rounded-xl border border-slate-200 bg-slate-50 pl-8 pr-3 py-1.5 text-xs font-medium text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-0 w-44 sm:w-56" />
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Excuse Requests List --}}
    @if($excuses->isEmpty())
        <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white py-16 px-4 text-center shadow-xs">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="mt-4 text-base font-black text-slate-800">No excuse requests found</h3>
            <p class="mt-1 text-xs text-slate-500 max-w-md mx-auto">
                {{ $statusFilter !== 'all' || $search ? 'Try adjusting your filter or search query.' : 'Submit a request to inform class teachers of medical or planned absences for your children.' }}
            </p>
            @if($this->isParent)
                <button type="button" wire:click="openCreateModal"
                        class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition"
                        style="background-color: #4f46e5; color: #ffffff;">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Submit New Request</span>
                </button>
            @endif
        </div>
    @else
        <div class="space-y-3.5">
            @foreach($excuses as $req)
                @php
                    $startDate = \Illuminate\Support\Carbon::parse($req->start_date);
                    $endDate   = \Illuminate\Support\Carbon::parse($req->end_date);
                    $daysDiff  = $startDate->diffInDays($endDate) + 1;

                    $reasonColor = match(true) {
                        str_contains(strtolower($req->reason), 'medical') || str_contains(strtolower($req->reason), 'illness') => 'bg-rose-50 text-rose-700 border-rose-200',
                        str_contains(strtolower($req->reason), 'doctor') || str_contains(strtolower($req->reason), 'hospital') => 'bg-sky-50 text-sky-700 border-sky-200',
                        str_contains(strtolower($req->reason), 'emergency') => 'bg-amber-50 text-amber-700 border-amber-200',
                        str_contains(strtolower($req->reason), 'religious') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        default => 'bg-purple-50 text-purple-700 border-purple-200',
                    };
                @endphp

                <div class="rounded-3xl border border-slate-200/90 bg-white p-5 shadow-xs hover:shadow-md transition duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        {{-- Left: Student Details & Info --}}
                        <div class="flex items-start gap-3.5 min-w-0">
                            @if($req->student?->passport_photo_url)
                                <img src="{{ $req->student->passport_photo_url }}" alt="{{ $req->student->full_name }}"
                                     class="h-12 w-12 rounded-2xl object-cover ring-2 ring-slate-100 bg-slate-50 shrink-0"
                                     style="width: 48px; height: 48px; max-width: 48px; max-height: 48px; object-fit: cover;" />
                            @else
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center font-black text-lg shadow-2xs ring-2 ring-indigo-50 shrink-0"
                                     style="width: 48px; height: 48px;">
                                    {{ mb_substr($req->student?->first_name ?? 'S', 0, 1) }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">
                                        {{ $req->student?->full_name ?? 'Student' }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $reasonColor }} border">
                                        {{ $req->reason }}
                                    </span>
                                </div>

                                <p class="text-xs font-semibold text-slate-400 mt-0.5">
                                    ADM: <span class="text-slate-600 font-bold">{{ $req->student?->admission_number ?? 'N/A' }}</span>
                                    &bull; Class: <span class="text-slate-600 font-bold">{{ $req->student?->schoolClass?->name ?? 'Unassigned' }}</span>
                                    @if($req->student?->section)
                                        <span class="text-slate-500">({{ $req->student->section->name }})</span>
                                    @endif
                                </p>

                                {{-- Date Duration pill --}}
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ $startDate->format('d M Y') }}</span>
                                        @if($startDate->ne($endDate))
                                            <span class="text-slate-400">&rarr;</span>
                                            <span>{{ $endDate->format('d M Y') }}</span>
                                        @endif
                                        <span class="text-[10px] text-slate-400 font-semibold">({{ $daysDiff }} {{ \Illuminate\Support\Str::plural('day', $daysDiff) }})</span>
                                    </span>
                                </div>

                                {{-- Notes / Explanation --}}
                                @if($req->notes)
                                    <div class="mt-2.5 p-3 rounded-2xl bg-slate-50/80 border border-slate-100 text-xs text-slate-600 font-medium leading-relaxed">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-0.5">Parent Note:</span>
                                        {{ $req->notes }}
                                    </div>
                                @endif

                                {{-- Reviewer stamp --}}
                                @if($req->status !== 'pending' && $req->reviewed_at)
                                    <div class="mt-2 text-[11px] font-semibold text-slate-400 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 {{ $req->status === 'approved' ? 'text-emerald-500' : 'text-rose-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ ucfirst($req->status) }} on {{ $req->reviewed_at->format('d M Y, g:i A') }} {{ $req->reviewer ? 'by ' . $req->reviewer->name : '' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Status & Action Buttons --}}
                        <div class="flex items-center sm:flex-col sm:items-end justify-between gap-2 shrink-0">
                            {{-- Status Badge --}}
                            @if($req->status === 'approved')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" style="background-color: #def7ec; color: #03543f;">
                                    <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Approved</span>
                                </span>
                            @elseif($req->status === 'rejected')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200" style="background-color: #fde8e8; color: #9b1c1c;">
                                    <svg class="w-3.5 h-3.5 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Rejected</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Pending Approval</span>
                                </span>
                            @endif

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 mt-2">
                                @if($this->isParent && $req->status === 'pending')
                                    <button type="button" wire:click="cancelRequest({{ $req->id }})"
                                            wire:confirm="Are you sure you want to cancel this excuse request?"
                                            class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition">
                                        Cancel Request
                                    </button>
                                @endif

                                @if($this->isStaff && $req->status === 'pending')
                                    <button type="button" wire:click="approveRequest({{ $req->id }})"
                                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs">
                                        Approve
                                    </button>
                                    <button type="button" wire:click="rejectRequest({{ $req->id }})"
                                            class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition">
                                        Reject
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Submit Excuse Request Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 sm:p-7 shadow-2xl border border-slate-100" @click.away="$wire.closeModal()">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-slate-900 leading-tight">Submit Excuse Request</h3>
                            <p class="text-xs text-slate-400 font-medium mt-0.5">Formal note for planned or medical absence</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeModal" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-50 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form wire:submit="submitRequest" class="mt-5 space-y-4">
                    {{-- 1. Select Child --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Child <span class="text-rose-500">*</span></label>
                        <select wire:model="student_id" required
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Choose student ward --</option>
                            @foreach($this->children as $ch)
                                <option value="{{ $ch->id }}">{{ $ch->full_name }} — {{ $ch->schoolClass?->name ?? 'Class' }}{{ $ch->section ? ' (' . $ch->section->name . ')' : '' }}</option>
                            @endforeach
                        </select>
                        @error('student_id') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- 2. Date Range --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Absence Start Date <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="start_date" required
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                            @error('start_date') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Absence End Date <span class="text-rose-500">*</span></label>
                            <input type="date" wire:model="end_date" required
                                   class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" />
                            @error('end_date') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- 3. Reason Dropdown --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Reason for Absence <span class="text-rose-500">*</span></label>
                        <select wire:model="reason" required
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-bold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            @foreach($reasonOptions as $rOpt)
                                <option value="{{ $rOpt }}">{{ $rOpt }}</option>
                            @endforeach
                        </select>
                        @error('reason') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- 4. Notes / Details --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Explanation / Note to School (Optional)</label>
                        <textarea wire:model="notes" rows="3" placeholder="Provide any additional details, doctor instructions, or context for the class teacher..."
                                  class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                        @error('notes') <span class="text-[11px] font-bold text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Buttons --}}
                    <div class="pt-3 flex items-center justify-end gap-2.5 border-t border-slate-100">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs active:scale-95 transition"
                                style="background-color: #4f46e5; color: #ffffff;">
                            <svg wire:loading wire:target="submitRequest" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Submit Excuse</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
