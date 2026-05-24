@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */
    $user = auth()->user();
    $total = $classes->count();
    $totalStudents = $classes->sum('students_count');
    $totalSubjects = $classes->sum('subjects_count');
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Header --}}
    <x-page-header title="Classes" subtitle="Manage class levels and enrollment structure." accent="classes">
        <x-slot:actions>
            @if ($user?->role === 'admin')
                <a href="{{ route('classes.manage') }}" class="btn-outline transition-all hover:bg-slate-100 hover:shadow-sm">
                    <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Manage Sections
                </a>
            @endif
            <a href="{{ route('subjects.index') }}" class="btn-outline transition-all hover:bg-slate-100 hover:shadow-sm">
                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
                Manage Subjects
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        {{-- Total Classes --}}
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-500 to-violet-600 p-6 text-white shadow-md shadow-indigo-100 transition-all hover:-translate-y-1 hover:shadow-xl duration-300">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black tracking-tight">{{ $total }}</div>
                    <div class="mt-1.5 text-sm font-bold text-indigo-100 uppercase tracking-wider">Total Classes</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Students --}}
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-400 to-amber-500 p-6 text-white shadow-md shadow-orange-100 transition-all hover:-translate-y-1 hover:shadow-xl duration-300">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black tracking-tight">{{ number_format($totalStudents) }}</div>
                    <div class="mt-1.5 text-sm font-bold text-orange-100 uppercase tracking-wider">Total Students</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Subject Allocations --}}
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-400 to-teal-500 p-6 text-white shadow-md shadow-emerald-100 transition-all hover:-translate-y-1 hover:shadow-xl duration-300">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black tracking-tight">{{ $totalSubjects }}</div>
                    <div class="mt-1.5 text-sm font-bold text-emerald-100 uppercase tracking-wider">Subject Allocations</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Save Notifications --}}
    @if (session('modal'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] animate-fadeIn" x-transition>
            <div class="rounded-3xl bg-white p-6 shadow-2xl max-w-sm w-full mx-4 transform transition-all animate-slideUp">
                <div class="flex flex-col items-center text-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 shadow-md shadow-emerald-50">
                        <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Success</h3>
                        <p class="mt-2 text-sm text-gray-600 font-semibold">{{ session('modal')['message'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <div class="text-sm font-bold text-rose-900">Please fix the following validation errors:</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-rose-800 font-semibold">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Form --}}
    @if ($user?->role === 'admin')
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-100">
            <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md shadow-indigo-50">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Add New Class</h2>
                    <p class="text-xs text-slate-400">Set up a new grade level and class structure</p>
                </div>
            </div>
            <form method="POST" action="{{ route('classes.store') }}" class="flex flex-col md:flex-row items-stretch md:items-end gap-5">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Class Name *</label>
                    <input 
                        name="name" 
                        class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300" 
                        value="{{ old('name') }}" 
                        placeholder="e.g., JSS 1A" 
                        required 
                    />
                </div>
                <div class="w-full md:w-44">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Academic Level *</label>
                    <input 
                        name="level" 
                        type="number" 
                        class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300" 
                        value="{{ old('level', 1) }}" 
                        min="1" 
                        max="30" 
                        required 
                    />
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-indigo-100 transition-all hover:from-indigo-700 hover:to-violet-700 hover:shadow-lg active:scale-[0.98] w-full md:w-auto h-[48px] self-end">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Class
                </button>
            </form>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="rounded-3xl bg-white shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5 bg-white">
            <div>
                <h3 class="text-base font-bold text-slate-800">All Registered Classes</h3>
                <p class="mt-0.5 text-xs text-slate-400 font-medium">Manage existing academic grade levels, student capacities, and subjects</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500">
                <span class="font-bold text-slate-800">{{ $total }}</span> class{{ $total !== 1 ? 'es' : '' }} total
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4 text-left w-1/3">Class Name</th>
                        <th class="px-6 py-4 text-center">Academic Level</th>
                        <th class="px-6 py-4 text-center">Associated Sections</th>
                        <th class="px-6 py-4 text-center">Subjects</th>
                        <th class="px-6 py-4 text-center">Active Students</th>
                        @if ($user?->role === 'admin')
                            <th class="px-6 py-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $gradients = [
                            'from-indigo-400 to-blue-500 shadow-indigo-100',
                            'from-violet-500 to-purple-600 shadow-purple-100',
                            'from-emerald-400 to-teal-500 shadow-emerald-100',
                            'from-orange-400 to-amber-500 shadow-orange-100',
                            'from-pink-400 to-rose-500 shadow-rose-100',
                        ];
                    @endphp
                    @forelse ($classes as $class)
                        @php $grad = $gradients[$class->id % count($gradients)]; @endphp
                        <tr class="group bg-white transition hover:bg-slate-50/40">
                            {{-- Class Name with Section Badges list --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white text-xs font-black shadow-md">
                                        {{ mb_substr($class->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 text-sm sm:text-base leading-snug">{{ $class->name }}</div>
                                        
                                        {{-- Inline Section Preview Badges --}}
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                            @forelse($class->sections as $sec)
                                                <span class="inline-flex items-center rounded-lg bg-indigo-50 border border-indigo-100/50 px-2 py-0.5 text-[9px] font-black text-indigo-600 tracking-wide uppercase shadow-sm">
                                                    {{ $sec->name }}
                                                </span>
                                            @empty
                                                <span class="text-[10px] text-slate-400 font-medium italic">No sections registered yet</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Level Badge --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-xl bg-slate-100 border border-slate-200/50 px-3 py-1 text-xs font-bold text-slate-700 shadow-inner">
                                    Level {{ $class->level }}
                                </span>
                            </td>

                            {{-- Sections Count --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-xl bg-sky-50 border border-sky-100/60 px-3 py-1 text-xs font-bold text-sky-700">
                                    {{ number_format((int) $class->sections_count) }} sections
                                </span>
                            </td>

                            {{-- Subjects Count --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-xl bg-violet-50 border border-violet-100/60 px-3 py-1 text-xs font-bold text-violet-700">
                                    {{ number_format((int) $class->subjects_count) }} allocated
                                </span>
                            </td>

                            {{-- Student Count --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-xl bg-orange-50 border border-orange-100/60 px-3 py-1 text-xs font-bold text-orange-700">
                                    {{ number_format((int) $class->students_count) }} active
                                </span>
                            </td>

                            {{-- Actions --}}
                            @if ($user?->role === 'admin')
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <a href="{{ route('classes.subjects', $class) }}" class="inline-flex items-center gap-1 rounded-xl bg-indigo-50 border border-indigo-100 px-3.5 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 hover:text-indigo-800 transition-all shadow-sm">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                            </svg>
                                            Subjects
                                        </a>
                                        <form method="POST" action="{{ route('classes.destroy', $class) }}" class="inline-block" onsubmit="return confirm('Delete this class? This will also remove any section associated with it.')">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-xl bg-rose-50 border border-rose-100 px-3.5 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 hover:text-rose-800 transition-all shadow-sm">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $user?->role === 'admin' ? 6 : 5 }}" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-700">No classes found</h4>
                                    <p class="text-xs text-slate-400 max-w-[280px]">Begin building your school's enrollment structure by setting up your first class level.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
