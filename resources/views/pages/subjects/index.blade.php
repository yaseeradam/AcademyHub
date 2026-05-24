@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Subject> $subjects */
    $user = auth()->user();
    $total = $subjects->count();
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Header --}}
    <x-page-header title="Subjects" subtitle="Create and manage academic curriculum subject codes." accent="subjects">
        <x-slot:actions>
            <a href="{{ route('classes.index') }}" class="btn-outline transition-all hover:bg-slate-100 hover:shadow-sm">
                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Manage Classes
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        {{-- Total Subjects --}}
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-md shadow-violet-100 transition-all hover:-translate-y-1 hover:shadow-xl duration-300">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black tracking-tight">{{ $total }}</div>
                    <div class="mt-1.5 text-sm font-bold text-violet-100 uppercase tracking-wider">Total Subjects</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- With Codes --}}
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-400 to-indigo-500 p-6 text-white shadow-md shadow-blue-100 transition-all hover:-translate-y-1 hover:shadow-xl duration-300">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black tracking-tight">{{ $subjects->whereNotNull('code')->count() }}</div>
                    <div class="mt-1.5 text-sm font-bold text-blue-100 uppercase tracking-wider">With Codes</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Allocated --}}
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-400 to-teal-500 p-6 text-white shadow-md shadow-emerald-100 transition-all hover:-translate-y-1 hover:shadow-xl duration-300">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10 transition-transform duration-500 group-hover:scale-110"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black tracking-tight">{{ \App\Models\SubjectAllocation::distinct('subject_id')->count('subject_id') }}</div>
                    <div class="mt-1.5 text-sm font-bold text-emerald-100 uppercase tracking-wider">Allocated</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/20 shadow-inner">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
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
                <div class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-md shadow-violet-50">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Add New Subject</h2>
                    <p class="text-xs text-slate-400">Set up a new curriculum subject level and assign a shorthand code</p>
                </div>
            </div>
            <form method="POST" action="{{ route('subjects.store') }}" class="flex flex-col md:flex-row items-stretch md:items-end gap-5">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Subject Name *</label>
                    <input 
                        name="name" 
                        class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 hover:border-slate-300" 
                        value="{{ old('name') }}" 
                        placeholder="e.g., Mathematics" 
                        required 
                    />
                </div>
                <div class="w-full md:w-44">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Subject Code *</label>
                    <input 
                        name="code" 
                        class="w-full rounded-xl border-2 border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-500/10 hover:border-slate-300 uppercase" 
                        value="{{ old('code') }}" 
                        placeholder="e.g., MATH" 
                        required 
                    />
                </div>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-violet-100 transition-all hover:from-violet-700 hover:to-purple-700 hover:shadow-lg active:scale-[0.98] w-full md:w-auto h-[48px] self-end">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Subject
                </button>
            </form>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="rounded-3xl bg-white shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5 bg-white">
            <div>
                <h3 class="text-base font-bold text-slate-800">All Curriculum Subjects</h3>
                <p class="mt-0.5 text-xs text-slate-400 font-medium">Configure existing subjects, edit reference codes, or delete orphaned topics</p>
            </div>
            <div class="rounded-xl bg-slate-50 border border-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500">
                <span class="font-bold text-slate-800">{{ $total }}</span> subject{{ $total !== 1 ? 's' : '' }} total
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4 text-left w-1/2">Subject Name</th>
                        <th class="px-6 py-4 text-left w-1/4">Subject Code</th>
                        @if ($user?->role === 'admin')
                            <th class="px-6 py-4 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                        $gradients = [
                            'from-violet-400 to-indigo-500 shadow-violet-100',
                            'from-purple-500 to-fuchsia-600 shadow-purple-100',
                            'from-emerald-400 to-teal-500 shadow-emerald-100',
                            'from-sky-400 to-blue-500 shadow-sky-100',
                            'from-pink-400 to-rose-500 shadow-rose-100',
                        ];
                    @endphp
                    @forelse ($subjects as $subject)
                        @php $grad = $gradients[$subject->id % count($gradients)]; @endphp
                        <tr class="group bg-white transition hover:bg-slate-50/40">
                            @if ($user?->role === 'admin')
                                <td class="px-6 py-4" colspan="3">
                                    <div x-data="{ editing: false }" class="flex items-center justify-between w-full">
                                        {{-- View State --}}
                                        <div x-show="!editing" class="flex items-center justify-between w-full animate-fadeIn">
                                            <div class="flex items-center gap-4">
                                                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white text-xs font-black shadow-md">
                                                    {{ mb_substr($subject->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 text-sm sm:text-base leading-snug">{{ $subject->name }}</div>
                                                    <span class="mt-1.5 inline-flex items-center rounded-lg bg-slate-100 border border-slate-200/50 px-2 py-0.5 text-[9px] font-black text-slate-600 tracking-wide uppercase shadow-sm">
                                                        {{ $subject->code }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <button @click="editing = true" type="button" class="inline-flex items-center gap-1 rounded-xl bg-indigo-50 border border-indigo-100 px-3.5 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 hover:text-indigo-800 transition-all shadow-sm">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('subjects.destroy', $subject) }}" class="inline-block" onsubmit="return confirm('Delete this subject? This action cannot be undone.')">
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
                                        </div>
                                        
                                        {{-- Edit State Form --}}
                                        <div x-show="editing" x-cloak class="w-full animate-slideUp" @keydown.escape.window="editing = false">
                                            <form method="POST" action="{{ route('subjects.update', $subject) }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full">
                                                @csrf 
                                                @method('PATCH')
                                                <div class="flex-1">
                                                    <input 
                                                        name="name" 
                                                        class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 hover:border-slate-300" 
                                                        value="{{ old('name', $subject->name) }}" 
                                                        required 
                                                    />
                                                </div>
                                                <div class="w-full sm:w-32">
                                                    <input 
                                                        name="code" 
                                                        class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 hover:border-slate-300 uppercase" 
                                                        value="{{ old('code', $subject->code) }}" 
                                                        required 
                                                    />
                                                </div>
                                                <div class="flex items-center gap-2 self-stretch sm:self-auto justify-end">
                                                    <button type="submit" class="inline-flex items-center justify-center gap-1 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-2.5 text-xs font-bold text-white shadow-md active:scale-[0.98]">
                                                        Save
                                                    </button>
                                                    <button @click="editing = false" type="button" class="inline-flex items-center justify-center gap-1 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white text-xs font-black shadow-md">
                                            {{ mb_substr($subject->name, 0, 1) }}
                                        </div>
                                        <div class="font-bold text-slate-800 text-sm sm:text-base leading-snug">{{ $subject->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-xl bg-slate-100 border border-slate-200/50 px-3 py-1 text-xs font-bold text-slate-700 shadow-inner">
                                        {{ $subject->code }}
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $user?->role === 'admin' ? 3 : 2 }}" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-slate-700">No subjects found</h4>
                                    <p class="text-xs text-slate-400 max-w-[280px]">Add your first curriculum subject to populate the system lists.</p>
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
