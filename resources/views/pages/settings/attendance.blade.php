@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="{{ route('settings.index') }}" class="hover:text-slate-800 transition">Settings</a>
        <span>/</span>
        <span class="text-slate-800">Attendance & Shifts</span>
    </div>

    {{-- Hero Card --}}
    <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background-color: #1a2e4a;">
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at top left, #1e3a5f 0%, transparent 60%);"></div>
        <div class="absolute right-0 top-0 bottom-0 w-64 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <circle cx="160" cy="100" r="130" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="white" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="white" stroke-width="0.5"/>
            </svg>
        </div>
        <div class="relative px-8 py-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-sm font-semibold uppercase tracking-widest" style="color: #93c5fd;">Dual-Shift Configuration</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">Attendance & Shift Settings</h2>
                <p class="mt-2 text-sm sm:text-base font-medium" style="color: #93c5fd;">
                    Customize sign-in windows, late arrival cutoffs, sign-out departure times, and section shift allocations.
                </p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('attendance.staff-timesheet') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 text-white px-4 py-2.5 text-xs font-bold transition backdrop-blur-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Staff Timesheet
                </a>
                <a href="{{ route('classes.manage') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 text-white px-4 py-2.5 text-xs font-bold transition backdrop-blur-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Manage Classes & Sections
                </a>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('status'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 px-5 py-4 ring-1 ring-emerald-100 shadow-xs">
            <svg class="h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm font-bold text-emerald-800">{{ session('status') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl bg-red-50 px-5 py-4 ring-1 ring-red-100 shadow-xs">
            <div class="text-sm font-bold text-red-800">Please correct the following errors:</div>
            <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs font-semibold text-red-700">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update-attendance') }}">
        @csrf

        {{-- Shift Cards Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 1. Western Shift Card --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 flex flex-col justify-between">
                <div>
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-sky-50/50 to-transparent">
                        <div class="flex items-center gap-3.5">
                            <div class="grid h-11 w-11 place-items-center rounded-xl bg-sky-100 text-sky-600 shadow-xs">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Western Shift (Morning)</h3>
                                <p class="text-xs font-medium text-slate-500">Regular school curriculum & morning classes</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 ring-1 ring-sky-200">
                            ☀️ Morning Section
                        </span>
                    </div>

                    {{-- Inputs --}}
                    <div class="p-6 space-y-5">
                        {{-- Sign In Start Time --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Sign-In Start Window <span class="text-slate-400 font-normal lowercase">(earliest check-in)</span>
                            </label>
                            <div class="relative">
                                <input type="time" name="western_start_time" required
                                    value="{{ old('western_start_time', substr($western['start_time'], 0, 5)) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100" />
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500">Earliest time students and teachers can punch in for Western shift.</p>
                        </div>

                        {{-- Late Arrival Cutoff --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Late Arrival Cutoff Time <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <div class="relative">
                                <input type="time" name="western_late_threshold" required
                                    value="{{ old('western_late_threshold', substr($western['late_threshold'], 0, 5)) }}"
                                    class="w-full rounded-xl border border-amber-300 bg-amber-50/30 px-4 py-2.5 text-sm font-bold text-slate-800 transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100" />
                            </div>
                            <p class="mt-1 text-[11px] text-amber-700 font-medium">
                                Check-ins <strong>after</strong> this time will be marked as <span class="text-amber-800 font-bold uppercase">Late</span> and accrue late minutes.
                            </p>
                        </div>

                        {{-- Sign Out Departure Time --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Sign-Out / Departure Time <span class="text-slate-400 font-normal lowercase">(shift closing)</span>
                            </label>
                            <div class="relative">
                                <input type="time" name="western_end_time" required
                                    value="{{ old('western_end_time', substr($western['end_time'], 0, 5)) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100" />
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500">Scheduled departure time when Western shift ends and sign-out scans occur.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50/80 border-t border-slate-100 px-6 py-3.5 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Current Schedule:</span>
                    <span class="font-bold text-sky-800">{{ $western['formatted_range'] }} (Late after {{ $western['formatted_late'] }})</span>
                </div>
            </div>

            {{-- 2. Islamic Shift Card --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 flex flex-col justify-between">
                <div>
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-gradient-to-r from-emerald-50/50 to-transparent">
                        <div class="flex items-center gap-3.5">
                            <div class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-emerald-600 shadow-xs">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Islamic Shift (Afternoon)</h3>
                                <p class="text-xs font-medium text-slate-500">Tahfeez, Arabic, and Islamic afternoon curriculum</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                            🌙 Afternoon Section
                        </span>
                    </div>

                    {{-- Inputs --}}
                    <div class="p-6 space-y-5">
                        {{-- Sign In Start Time --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Sign-In Start Window <span class="text-slate-400 font-normal lowercase">(earliest check-in)</span>
                            </label>
                            <div class="relative">
                                <input type="time" name="islamic_start_time" required
                                    value="{{ old('islamic_start_time', substr($islamic['start_time'], 0, 5)) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100" />
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500">Earliest time students and teachers can punch in for Islamic shift.</p>
                        </div>

                        {{-- Late Arrival Cutoff --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Late Arrival Cutoff Time <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <div class="relative">
                                <input type="time" name="islamic_late_threshold" required
                                    value="{{ old('islamic_late_threshold', substr($islamic['late_threshold'], 0, 5)) }}"
                                    class="w-full rounded-xl border border-amber-300 bg-amber-50/30 px-4 py-2.5 text-sm font-bold text-slate-800 transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100" />
                            </div>
                            <p class="mt-1 text-[11px] text-amber-700 font-medium">
                                Check-ins <strong>after</strong> this time will be marked as <span class="text-amber-800 font-bold uppercase">Late</span> and accrue late minutes.
                            </p>
                        </div>

                        {{-- Sign Out Departure Time --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Sign-Out / Departure Time <span class="text-slate-400 font-normal lowercase">(shift closing)</span>
                            </label>
                            <div class="relative">
                                <input type="time" name="islamic_end_time" required
                                    value="{{ old('islamic_end_time', substr($islamic['end_time'], 0, 5)) }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-800 transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100" />
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500">Scheduled departure time when Islamic shift ends and sign-out scans occur.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50/80 border-t border-slate-100 px-6 py-3.5 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Current Schedule:</span>
                    <span class="font-bold text-emerald-800">{{ $islamic['formatted_range'] }} (Late after {{ $islamic['formatted_late'] }})</span>
                </div>
            </div>

        </div>

        {{-- Section Shift Assignment Matrix --}}
        <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 px-6 py-5 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Section Shift Assignments</h3>
                        <p class="text-xs text-slate-500">Assign each class section to either the Western (Morning) or Islamic (Afternoon) shift.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-sky-100/70 text-sky-800 px-2.5 py-1 text-xs font-bold">
                        ☀️ Western: Morning
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100/70 text-emerald-800 px-2.5 py-1 text-xs font-bold">
                        🌙 Islamic: Afternoon
                    </span>
                </div>
            </div>

            <div class="p-6">
                @if($classes->isEmpty())
                    <div class="py-8 text-center text-sm font-semibold text-slate-400">
                        No classes or sections created yet. Create classes first to assign their shifts.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="pb-3 pl-2">Class Level</th>
                                    <th class="pb-3">Section Label</th>
                                    <th class="pb-3 text-center">Assigned Shift</th>
                                    <th class="pb-3 text-right pr-2">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($classes as $class)
                                    @forelse($class->sections as $section)
                                        @php
                                            $currentShift = $section->getShift();
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-3 pl-2 font-bold text-slate-800">
                                                {{ $class->name }}
                                                <span class="ml-1.5 text-[10px] text-slate-400 font-normal">(Level {{ $class->level }})</span>
                                            </td>
                                            <td class="py-3 font-semibold text-slate-700">
                                                <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-800">
                                                    Section {{ $section->name }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-center">
                                                <div class="inline-flex items-center gap-2">
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $currentShift === 'Western' ? 'bg-sky-100 text-sky-800 ring-1 ring-sky-300' : 'text-slate-500 hover:bg-slate-100' }}">
                                                        <input type="radio" name="section_shifts[{{ $section->id }}]" value="Western"
                                                            {{ $currentShift === 'Western' ? 'checked' : '' }} class="text-sky-600 focus:ring-sky-500" />
                                                        <span>Western (Morning)</span>
                                                    </label>
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $currentShift === 'Islamic' ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-300' : 'text-slate-500 hover:bg-slate-100' }}">
                                                        <input type="radio" name="section_shifts[{{ $section->id }}]" value="Islamic"
                                                            {{ $currentShift === 'Islamic' ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500" />
                                                        <span>Islamic (Afternoon)</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="py-3 pr-2 text-right">
                                                <span class="text-[11px] text-slate-400">
                                                    {{ $section->students()->count() }} students enrolled
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="py-2.5 pl-2 font-semibold text-slate-500">{{ $class->name }}</td>
                                            <td colspan="3" class="py-2.5 italic text-slate-400">No sections added yet for this class.</td>
                                        </tr>
                                    @endforelse
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Footer / Save Button --}}
            <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Changes take effect immediately on Biometric sync, Staff Timesheets, and Attendance sheets.
                </p>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:from-emerald-700 hover:to-teal-700 active:scale-95 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Save All Shift Parameters
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
