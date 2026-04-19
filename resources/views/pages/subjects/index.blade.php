@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Subject> $subjects */
    $user = auth()->user();
    $total = $subjects->count();
@endphp

@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <x-page-header title="Subjects" subtitle="Create and manage subject codes used for results and allocations." accent="subjects">
        <x-slot:actions>
            <a href="{{ route('classes.index') }}" class="btn-outline">Manage Classes</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $total }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Total Subjects</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ $subjects->whereNotNull('code')->count() }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">With Codes</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-6 text-white shadow-lg">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <div class="text-4xl font-black">{{ \App\Models\SubjectAllocation::distinct('subject_id')->count('subject_id') }}</div>
                    <div class="mt-1 text-sm font-semibold text-white/80">Allocated</div>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
            </div>
        </div>
    </div>

    @if (session('modal'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20" x-transition>
            <div class="rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ session('modal')['message'] }}</div>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-orange-200 bg-orange-50/60 p-5">
            <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Add Form --}}
    @if ($user?->role === 'admin')
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
            <div class="mb-5 flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="text-sm font-bold text-slate-800">Add New Subject</div>
            </div>
            <form method="POST" action="{{ route('subjects.store') }}" class="flex flex-col sm:flex-row items-end gap-4">
                @csrf
                <div class="flex-1">
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Subject Name</label>
                    <input name="name" class="mt-2 input-compact w-full" value="{{ old('name') }}" placeholder="e.g., Mathematics" required />
                </div>
                <div class="w-32">
                    <label class="text-xs font-semibold uppercase tracking-wider text-slate-500">Code</label>
                    <input name="code" class="mt-2 input-compact w-full uppercase" value="{{ old('code') }}" placeholder="MATH" required />
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:from-violet-600 hover:to-purple-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Subject
                </button>
            </form>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <div class="text-base font-bold text-slate-800">All Subjects</div>
                <div class="mt-0.5 text-xs text-slate-400">{{ $total }} subject{{ $total !== 1 ? 's' : '' }} registered</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 text-left">Subject Name</th>
                        <th class="px-5 py-3 text-left">Code</th>
                        @if ($user?->role === 'admin')
                            <th class="px-5 py-3 text-right">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($subjects as $subject)
                        <tr class="group bg-white transition hover:bg-slate-50/80">
                            @if ($user?->role === 'admin')
                                <td class="px-5 py-4" colspan="3">
                                    <div x-data="{ editing: false }" class="flex items-center justify-between w-full">
                                        <div x-show="!editing" class="flex items-center justify-between w-full">
                                            <div class="flex items-center gap-4">
                                                <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 text-white text-xs font-black shadow-sm">
                                                    {{ mb_substr($subject->name, 0, 1) }}
                                                </div>
                                                <div class="font-semibold text-slate-800">{{ $subject->name }}</div>
                                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $subject->code }}</span>
                                            </div>
                                            <div class="flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="editing = true" type="button" class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-600 transition hover:bg-blue-100">Edit</button>
                                                <form method="POST" action="{{ route('subjects.destroy', $subject) }}" class="inline-block" onsubmit="return confirm('Delete this subject?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-100">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div x-show="editing" x-cloak class="w-full">
                                            <form method="POST" action="{{ route('subjects.update', $subject) }}" class="flex items-center gap-4 w-full">
                                                @csrf @method('PATCH')
                                                <input name="name" class="input-compact flex-1" value="{{ old('name', $subject->name) }}" required />
                                                <input name="code" class="input-compact w-32 uppercase" value="{{ old('code', $subject->code) }}" required />
                                                <div class="flex items-center gap-2">
                                                    <button type="submit" class="rounded-lg bg-violet-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-violet-700">Save</button>
                                                    <button @click="editing = false" type="button" class="btn-outline text-xs h-8 px-3">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 text-white text-xs font-black shadow-sm">
                                            {{ mb_substr($subject->name, 0, 1) }}
                                        </div>
                                        <div class="font-semibold text-slate-800">{{ $subject->name }}</div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $subject->code }}</span>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $user?->role === 'admin' ? 3 : 2 }}" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-600">No subjects found</div>
                                    <div class="text-xs text-slate-400">Add your first subject to get started</div>
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
