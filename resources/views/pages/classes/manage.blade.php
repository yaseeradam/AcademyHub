@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */
    $user = auth()->user();
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6 font-sans">
    
    {{-- Header --}}
    <x-page-header title="Manage Classes & Sections" subtitle="Configure grade levels, edit enrollment sections, and refine class parameters." accent="classes">
        <x-slot:actions>
            <a href="{{ route('classes.index') }}" class="btn-outline transition-all hover:bg-slate-100 hover:shadow-sm">
                <svg class="h-4 w-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Classes
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Status Alerts --}}
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 text-sm font-bold text-emerald-800 shadow-sm animate-fadeIn">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-rose-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <div class="text-sm font-bold text-rose-900">Please fix the following:</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-rose-800 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Classes detail list --}}
    @if ($classes->isNotEmpty())
        <div class="grid grid-cols-1 gap-6">
            @foreach ($classes as $class)
                @php
                    $palette = [
                        [
                            'bg' => 'from-indigo-50/50 to-indigo-100/20', 
                            'border' => 'border-indigo-100', 
                            'badge' => 'bg-indigo-100 text-indigo-700 ring-indigo-200', 
                            'button' => 'from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:ring-indigo-500/20 shadow-indigo-100/80',
                            'theme' => 'text-indigo-600',
                            'input' => 'focus:border-indigo-500 focus:ring-indigo-500/10'
                        ],
                        [
                            'bg' => 'from-emerald-50/50 to-emerald-100/20', 
                            'border' => 'border-emerald-100', 
                            'badge' => 'bg-emerald-100 text-emerald-700 ring-emerald-200', 
                            'button' => 'from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 focus:ring-emerald-500/20 shadow-emerald-100/80',
                            'theme' => 'text-emerald-600',
                            'input' => 'focus:border-emerald-500 focus:ring-emerald-500/10'
                        ],
                        [
                            'bg' => 'from-purple-50/50 to-purple-100/20', 
                            'border' => 'border-purple-100', 
                            'badge' => 'bg-purple-100 text-purple-700 ring-purple-200', 
                            'button' => 'from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 focus:ring-purple-500/20 shadow-purple-100/80',
                            'theme' => 'text-purple-600',
                            'input' => 'focus:border-purple-500 focus:ring-purple-500/10'
                        ],
                        [
                            'bg' => 'from-orange-50/50 to-orange-100/20', 
                            'border' => 'border-orange-100', 
                            'badge' => 'bg-orange-100 text-orange-700 ring-orange-200', 
                            'button' => 'from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 focus:ring-orange-500/20 shadow-orange-100/80',
                            'theme' => 'text-orange-600',
                            'input' => 'focus:border-orange-500 focus:ring-orange-500/10'
                        ],
                        [
                            'bg' => 'from-pink-50/50 to-pink-100/20', 
                            'border' => 'border-pink-100', 
                            'badge' => 'bg-pink-100 text-pink-700 ring-pink-200', 
                            'button' => 'from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 focus:ring-pink-500/20 shadow-pink-100/80',
                            'theme' => 'text-pink-600',
                            'input' => 'focus:border-pink-500 focus:ring-pink-500/10'
                        ],
                    ];
                    $scheme = $palette[$class->id % count($palette)];
                @endphp

                <div class="rounded-3xl border {{ $scheme['border'] }} bg-gradient-to-br {{ $scheme['bg'] }} p-6 shadow-sm shadow-slate-100 transition-all duration-300 hover:shadow-md">
                    {{-- Card Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-white/50 pb-5">
                        <div class="flex items-center gap-3.5">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white/80 shadow-sm border border-slate-100">
                                <svg class="h-6 w-6 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight">{{ $class->name }}</h3>
                                <div class="mt-1 flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-500">
                                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $scheme['badge'] }} px-2.5 py-0.5 text-[10px] font-black ring-1">
                                        Level {{ $class->level }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ number_format((int) $class->students_count) }} Active Students</span>
                                    <span>•</span>
                                    <span>{{ number_format((int) $class->sections_count) }} Sections</span>
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('classes.destroy', $class) }}" class="inline" onsubmit="return confirm('Delete this class? This will also remove any section associated with it.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center gap-1 rounded-xl bg-white border border-rose-100 px-4 py-2.5 text-xs font-bold text-rose-600 shadow-sm transition-all hover:bg-rose-50 hover:text-rose-700 active:scale-[0.98]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Class
                            </button>
                        </form>
                    </div>

                    {{-- Edit Class Meta Form --}}
                    <form method="POST" action="{{ route('classes.update', $class) }}" class="mb-6">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 items-end">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Class Name *</label>
                                <input 
                                    name="name" 
                                    class="w-full rounded-xl border-2 border-slate-200 bg-white/90 px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all hover:border-slate-300 {{ $scheme['input'] }}" 
                                    value="{{ old('name', $class->name) }}" 
                                    required 
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Level *</label>
                                <input 
                                    name="level" 
                                    type="number" 
                                    min="1" 
                                    max="30" 
                                    class="w-full rounded-xl border-2 border-slate-200 bg-white/90 px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all hover:border-slate-300 {{ $scheme['input'] }}" 
                                    value="{{ old('level', $class->level) }}" 
                                    required 
                                />
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r {{ $scheme['button'] }} px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all active:scale-[0.98]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>

                    {{-- Sections Subsection Card --}}
                    <div class="rounded-2xl border border-white/60 bg-white/50 p-5 shadow-sm backdrop-blur-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Class Sections</h4>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Click a section pill to remove it</p>
                            </div>
                            <div class="rounded-lg bg-white border border-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-600">
                                {{ $class->sections->count() }} total
                            </div>
                        </div>

                        {{-- Section tag bubbles --}}
                        <div class="flex flex-wrap gap-2 mb-5">
                            @forelse ($class->sections as $section)
                                <form method="POST" action="{{ route('sections.destroy', ['class' => $class, 'section' => $section]) }}" onsubmit="return confirm('Delete section {{ $section->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="group/section inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200/80 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition-all hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 active:scale-[0.97]">
                                        <span>{{ $section->name }}</span>
                                        <svg class="h-3.5 w-3.5 text-slate-400 group-hover/section:text-rose-500 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M18 6L6 18M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            @empty
                                <div class="text-xs font-semibold text-slate-400 italic py-2">No sections registered yet for this class level.</div>
                            @endforelse
                        </div>

                        {{-- Add Section form --}}
                        <form method="POST" action="{{ route('sections.store', $class) }}" class="flex flex-col gap-3 sm:flex-row items-stretch">
                            @csrf
                            <div class="flex-1">
                                <input 
                                    name="name" 
                                    class="w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300" 
                                    placeholder="Section label (e.g., A, B, C, GOLD, BLUE)" 
                                    required 
                                />
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r {{ $scheme['button'] }} px-5 py-3 text-sm font-bold text-white shadow-md transition-all active:scale-[0.98]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Section
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-3xl border border-slate-100 bg-white p-12 text-center shadow-sm">
            <div class="flex flex-col items-center gap-3">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h4 class="text-base font-bold text-slate-700">No classes registered yet</h4>
                <p class="text-xs text-slate-400 max-w-[280px]">Create classes first from the Class lists page before adding sections.</p>
                <a href="{{ route('classes.index') }}" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-xs font-bold text-white shadow-md">
                    Go to Class Editor
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
